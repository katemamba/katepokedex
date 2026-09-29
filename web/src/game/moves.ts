import type { PokemonTypeName } from '../types/pokemon'
import type { BattleMove } from './types'

const MOVE_BANK: Record<
  PokemonTypeName,
  { physical: string; special: string; power: number }
> = {
  normal: { physical: 'Tackle', special: 'Swift', power: 50 },
  fire: { physical: 'Fire Fang', special: 'Flamethrower', power: 70 },
  water: { physical: 'Aqua Tail', special: 'Surf', power: 70 },
  electric: { physical: 'Spark', special: 'Thunderbolt', power: 70 },
  grass: { physical: 'Seed Bomb', special: 'Energy Ball', power: 70 },
  ice: { physical: 'Ice Fang', special: 'Ice Beam', power: 70 },
  fighting: { physical: 'Brick Break', special: 'Aura Sphere', power: 75 },
  poison: { physical: 'Poison Jab', special: 'Sludge Bomb', power: 70 },
  ground: { physical: 'Earthquake', special: 'Earth Power', power: 80 },
  flying: { physical: 'Aerial Ace', special: 'Air Slash', power: 70 },
  psychic: { physical: 'Zen Headbutt', special: 'Psychic', power: 75 },
  bug: { physical: 'X-Scissor', special: 'Bug Buzz', power: 70 },
  rock: { physical: 'Rock Slide', special: 'Power Gem', power: 70 },
  ghost: { physical: 'Shadow Claw', special: 'Shadow Ball', power: 70 },
  dragon: { physical: 'Dragon Claw', special: 'Dragon Pulse', power: 75 },
  dark: { physical: 'Crunch', special: 'Dark Pulse', power: 70 },
  steel: { physical: 'Iron Head', special: 'Flash Cannon', power: 70 },
  fairy: { physical: 'Play Rough', special: 'Moonblast', power: 75 },
}

function slug(name: string): string {
  return name.toLowerCase().replace(/\s+/g, '-')
}

export function buildMoveset(
  types: PokemonTypeName[],
  prefersPhysical: boolean,
): BattleMove[] {
  const primary = types[0] ?? 'normal'
  const secondary = types[1] ?? primary
  const moves: BattleMove[] = []

  const push = (
    type: PokemonTypeName,
    category: 'physical' | 'special',
    powerBoost = 0,
  ) => {
    const bank = MOVE_BANK[type]
    const name = category === 'physical' ? bank.physical : bank.special
    const id = `${slug(name)}-${type}-${category}`
    if (moves.some((m) => m.id === id)) return
    moves.push({
      id,
      name,
      type,
      category,
      power: bank.power + powerBoost,
      accuracy: 95,
      pp: 12,
      maxPp: 12,
    })
  }

  push(primary, prefersPhysical ? 'physical' : 'special', 10)
  push(primary, prefersPhysical ? 'special' : 'physical')
  push(secondary, prefersPhysical ? 'physical' : 'special')
  push('normal', 'physical', -10)

  while (moves.length < 4) {
    push('normal', 'special', -15)
  }

  return moves.slice(0, 4)
}
