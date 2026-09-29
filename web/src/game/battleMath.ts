import type { PokemonTypeName } from '../types/pokemon'
import { getMultiplier } from '../data/typeChart'
import { displayName, spriteUrl } from '../api/pokeapi'
import { buildMoveset } from './moves'
import type { Battler, BattleMove, FighterProgress } from './types'

function stageMult(stage: number): number {
  const s = Math.max(-6, Math.min(6, stage))
  return s >= 0 ? (2 + s) / 2 : 2 / (2 - s)
}

export function calcStat(base: number, level: number): number {
  return Math.floor(((2 * base * level) / 100) + 5)
}

export function calcHp(base: number, level: number): number {
  return Math.floor(((2 * base * level) / 100) + level + 10)
}

export function xpToNext(level: number): number {
  return Math.floor(20 + level * 12)
}

export function createBattler(input: {
  pokemonId: number
  name: string
  types: PokemonTypeName[]
  stats: { name: string; value: number }[]
  level: number
  keyPrefix?: string
}): Battler {
  const get = (n: string) =>
    input.stats.find((s) => s.name === n)?.value ?? 50
  const attack = get('attack')
  const spAtk = get('special-attack')
  const prefersPhysical = attack >= spAtk
  const level = Math.max(1, Math.min(100, input.level))

  return {
    key: `${input.keyPrefix ?? 'p'}-${input.pokemonId}-${Math.random().toString(36).slice(2, 7)}`,
    pokemonId: input.pokemonId,
    name: displayName(input.name),
    types: input.types,
    sprite: spriteUrl(input.pokemonId),
    level,
    maxHp: calcHp(get('hp'), level),
    hp: calcHp(get('hp'), level),
    attack: calcStat(attack, level),
    defense: calcStat(get('defense'), level),
    spAtk: calcStat(spAtk, level),
    spDef: calcStat(get('special-defense'), level),
    speed: calcStat(get('speed'), level),
    moves: buildMoveset(input.types, prefersPhysical),
    stages: { attack: 0, defense: 0 },
  }
}

export function effectiveAttack(b: Battler, move: BattleMove): number {
  const base = move.category === 'physical' ? b.attack : b.spAtk
  return Math.floor(base * stageMult(b.stages.attack))
}

export function effectiveDefense(b: Battler, move: BattleMove): number {
  const base = move.category === 'physical' ? b.defense : b.spDef
  return Math.floor(base * stageMult(b.stages.defense))
}

export function typeEffectiveness(
  moveType: PokemonTypeName,
  defenderTypes: PokemonTypeName[],
): number {
  return defenderTypes.reduce(
    (acc, t) => acc * getMultiplier(moveType, t),
    1,
  )
}

export function calcDamage(
  attacker: Battler,
  defender: Battler,
  move: BattleMove,
): { damage: number; effectiveness: number; stab: boolean; missed: boolean } {
  if (Math.random() * 100 > move.accuracy) {
    return { damage: 0, effectiveness: 1, stab: false, missed: true }
  }

  const stab = attacker.types.includes(move.type)
  const effectiveness = typeEffectiveness(move.type, defender.types)
  const atk = effectiveAttack(attacker, move)
  const def = Math.max(1, effectiveDefense(defender, move))
  const base =
    (((2 * attacker.level) / 5 + 2) * move.power * (atk / def)) / 50 + 2
  const random = 0.85 + Math.random() * 0.15
  const damage = Math.max(
    1,
    Math.floor(base * (stab ? 1.5 : 1) * effectiveness * random),
  )
  return { damage, effectiveness, stab, missed: false }
}

export function syncFightersWithTeam(
  teamIds: number[],
  fighters: FighterProgress[],
): FighterProgress[] {
  const map = new Map(fighters.map((f) => [f.pokemonId, f]))
  return teamIds.map((id) => {
    const existing = map.get(id)
    return existing ?? { pokemonId: id, level: 12, xp: 0 }
  })
}

export function applyXp(
  fighter: FighterProgress,
  gained: number,
): { fighter: FighterProgress; leveled: number } {
  let level = fighter.level
  let xp = fighter.xp + gained
  let leveled = 0
  while (level < 100 && xp >= xpToNext(level)) {
    xp -= xpToNext(level)
    level += 1
    leveled += 1
  }
  if (level >= 100) {
    level = 100
    xp = 0
  }
  return { fighter: { pokemonId: fighter.pokemonId, level, xp }, leveled }
}

export function trainRewardXp(playerLevel: number, enemyLevel: number): number {
  return Math.max(8, Math.floor(12 + enemyLevel * 2 + (enemyLevel - playerLevel)))
}
