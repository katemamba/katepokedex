export type PokemonTypeName =
  | 'normal'
  | 'fire'
  | 'water'
  | 'electric'
  | 'grass'
  | 'ice'
  | 'fighting'
  | 'poison'
  | 'ground'
  | 'flying'
  | 'psychic'
  | 'bug'
  | 'rock'
  | 'ghost'
  | 'dragon'
  | 'dark'
  | 'steel'
  | 'fairy'

export interface PokemonListItem {
  id: number
  name: string
  types: PokemonTypeName[]
}

export interface PokemonStat {
  name: string
  value: number
}

export interface PokemonAbility {
  name: string
  isHidden: boolean
  shortEffect: string
}

export interface EvolutionNode {
  id: number
  name: string
  minLevel: number | null
  trigger: string | null
  item: string | null
  evolvesTo: EvolutionNode[]
}

export interface PokemonDetail {
  id: number
  name: string
  height: number
  weight: number
  types: PokemonTypeName[]
  stats: PokemonStat[]
  abilities: PokemonAbility[]
  sprite: string
  cry: string | null
  genus: string
  flavorText: string
  generation: string
  habitat: string | null
  isLegendary: boolean
  isMythical: boolean
  evolution: EvolutionNode | null
  moves: string[]
}

export interface TrainerProfile {
  name: string
  hometown: string
  gender: string
}
