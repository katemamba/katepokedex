import { config } from '../config'
import type {
  EvolutionNode,
  PokemonAbility,
  PokemonDetail,
  PokemonListItem,
  PokemonTypeName,
} from '../types/pokemon'
import { ALL_TYPES } from '../data/typeChart'

function capitalize(name: string): string {
  return name
    .split('-')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ')
}

export function displayName(name: string): string {
  return capitalize(name)
}

export function padId(id: number): string {
  return `#${String(id).padStart(4, '0')}`
}

export function spriteUrl(id: number): string {
  return `${config.spriteBase}/${id}.png`
}

export function fallbackSpriteUrl(id: number): string {
  return `${config.spriteFallback}/${id}.png`
}

async function fetchJson<T>(url: string): Promise<T> {
  const res = await fetch(url)
  if (!res.ok) throw new Error(`Request failed: ${res.status} ${url}`)
  return res.json() as Promise<T>
}

interface NamedResource {
  name: string
  url: string
}

interface PokemonApi {
  id: number
  name: string
  height: number
  weight: number
  types: { slot: number; type: NamedResource }[]
  stats: { base_stat: number; stat: NamedResource }[]
  abilities: { ability: NamedResource; is_hidden: boolean }[]
  cries?: { latest?: string; legacy?: string }
  sprites: {
    front_default: string | null
    other?: {
      'official-artwork'?: { front_default: string | null }
    }
  }
  moves: { move: NamedResource }[]
}

interface SpeciesApi {
  genera: { genus: string; language: NamedResource }[]
  flavor_text_entries: {
    flavor_text: string
    language: NamedResource
    version: NamedResource
  }[]
  generation: NamedResource
  habitat: NamedResource | null
  is_legendary: boolean
  is_mythical: boolean
  evolution_chain: { url: string }
}

interface EvolutionChainApi {
  chain: {
    species: NamedResource
    evolution_details: {
      min_level: number | null
      trigger: NamedResource | null
      item: NamedResource | null
    }[]
    evolves_to: EvolutionChainApi['chain'][]
  }
}

interface AbilityApi {
  effect_entries: {
    short_effect: string
    language: NamedResource
  }[]
}

function idFromUrl(url: string): number {
  const parts = url.split('/').filter(Boolean)
  return Number(parts[parts.length - 1])
}

function mapEvolution(node: EvolutionChainApi['chain']): EvolutionNode {
  const detail = node.evolution_details[0]
  return {
    id: idFromUrl(node.species.url),
    name: node.species.name,
    minLevel: detail?.min_level ?? null,
    trigger: detail?.trigger?.name ?? null,
    item: detail?.item?.name ?? null,
    evolvesTo: node.evolves_to.map(mapEvolution),
  }
}

export async function buildPokemonIndex(): Promise<PokemonListItem[]> {
  const cached = localStorage.getItem(config.storageKeys.typeIndex)
  if (cached) {
    try {
      return JSON.parse(cached) as PokemonListItem[]
    } catch {
      /* rebuild */
    }
  }

  const list = await fetchJson<{
    results: NamedResource[]
  }>(`${config.pokeApiBase}/pokemon?limit=${config.nationalDexCount}`)

  const typeMap = new Map<number, PokemonTypeName[]>()

  await Promise.all(
    ALL_TYPES.map(async (type) => {
      const data = await fetchJson<{
        pokemon: { pokemon: NamedResource; slot: number }[]
      }>(`${config.pokeApiBase}/type/${type}`)

      for (const entry of data.pokemon) {
        const id = idFromUrl(entry.pokemon.url)
        if (id > config.nationalDexCount) continue
        const existing = typeMap.get(id) ?? []
        if (!existing.includes(type)) {
          existing.push(type)
          typeMap.set(id, existing)
        }
      }
    }),
  )

  const index: PokemonListItem[] = list.results.map((item, i) => {
    const id = i + 1
    return {
      id,
      name: item.name,
      types: typeMap.get(id) ?? [],
    }
  })

  localStorage.setItem(config.storageKeys.typeIndex, JSON.stringify(index))
  return index
}

export async function getPokemonDetail(
  idOrName: string | number,
): Promise<PokemonDetail> {
  const pokemon = await fetchJson<PokemonApi>(
    `${config.pokeApiBase}/pokemon/${idOrName}`,
  )
  const species = await fetchJson<SpeciesApi>(
    `${config.pokeApiBase}/pokemon-species/${pokemon.id}`,
  )

  const abilityDetails = await Promise.all(
    pokemon.abilities.map(async (a): Promise<PokemonAbility> => {
      try {
        const ability = await fetchJson<AbilityApi>(a.ability.url)
        const en = ability.effect_entries.find((e) => e.language.name === 'en')
        return {
          name: a.ability.name,
          isHidden: a.is_hidden,
          shortEffect: en?.short_effect ?? 'No description available.',
        }
      } catch {
        return {
          name: a.ability.name,
          isHidden: a.is_hidden,
          shortEffect: 'No description available.',
        }
      }
    }),
  )

  let evolution: EvolutionNode | null = null
  try {
    const chain = await fetchJson<EvolutionChainApi>(
      species.evolution_chain.url,
    )
    evolution = mapEvolution(chain.chain)
  } catch {
    evolution = null
  }

  const genus =
    species.genera.find((g) => g.language.name === 'en')?.genus ?? 'Pokémon'
  const flavor =
    [...species.flavor_text_entries]
      .reverse()
      .find((f) => f.language.name === 'en')
      ?.flavor_text.replace(/\f|\n|\r/g, ' ') ?? ''

  const artwork =
    pokemon.sprites.other?.['official-artwork']?.front_default ??
    pokemon.sprites.front_default ??
    spriteUrl(pokemon.id)

  return {
    id: pokemon.id,
    name: pokemon.name,
    height: pokemon.height,
    weight: pokemon.weight,
    types: pokemon.types
      .sort((a, b) => a.slot - b.slot)
      .map((t) => t.type.name as PokemonTypeName),
    stats: pokemon.stats.map((s) => ({
      name: s.stat.name,
      value: s.base_stat,
    })),
    abilities: abilityDetails,
    sprite: artwork,
    cry: pokemon.cries?.latest ?? pokemon.cries?.legacy ?? null,
    genus,
    flavorText: flavor,
    generation: species.generation.name,
    habitat: species.habitat?.name ?? null,
    isLegendary: species.is_legendary,
    isMythical: species.is_mythical,
    evolution,
    moves: pokemon.moves.slice(0, 24).map((m) => m.move.name),
  }
}

export async function getPokemonSummaries(
  ids: number[],
): Promise<PokemonListItem[]> {
  const results = await Promise.all(
    ids.map(async (id) => {
      const data = await fetchJson<PokemonApi>(
        `${config.pokeApiBase}/pokemon/${id}`,
      )
      return {
        id: data.id,
        name: data.name,
        types: data.types.map((t) => t.type.name as PokemonTypeName),
      } satisfies PokemonListItem
    }),
  )
  return results
}

/** Lightweight fetch for battles — one request, no species/abilities/evolution. */
export async function getBattleProfile(idOrName: string | number): Promise<{
  id: number
  name: string
  types: PokemonTypeName[]
  stats: { name: string; value: number }[]
}> {
  const pokemon = await fetchJson<PokemonApi>(
    `${config.pokeApiBase}/pokemon/${idOrName}`,
  )
  return {
    id: pokemon.id,
    name: pokemon.name,
    types: pokemon.types
      .sort((a, b) => a.slot - b.slot)
      .map((t) => t.type.name as PokemonTypeName),
    stats: pokemon.stats.map((s) => ({
      name: s.stat.name,
      value: s.base_stat,
    })),
  }
}
