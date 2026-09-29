import type { PokemonTypeName } from '../types/pokemon'

export type ItemId =
  | 'potion'
  | 'super-potion'
  | 'hyper-potion'
  | 'full-restore'
  | 'x-attack'
  | 'x-defense'
  | 'revive'

export interface BattleMove {
  id: string
  name: string
  type: PokemonTypeName
  category: 'physical' | 'special'
  power: number
  accuracy: number
  pp: number
  maxPp: number
}

export interface Battler {
  key: string
  pokemonId: number
  name: string
  types: PokemonTypeName[]
  sprite: string
  level: number
  maxHp: number
  hp: number
  attack: number
  defense: number
  spAtk: number
  spDef: number
  speed: number
  moves: BattleMove[]
  stages: { attack: number; defense: number }
}

export interface FighterProgress {
  pokemonId: number
  level: number
  xp: number
}

export interface GameProgress {
  fighters: FighterProgress[]
  bag: Partial<Record<ItemId, number>>
  eliteFourIndex: number
  trainingWins: number
  championBeaten: boolean
}

export type BattleMode = 'train' | 'elite'

export type BattlePhase =
  | 'command'
  | 'fight'
  | 'bag'
  | 'switch'
  | 'animating'
  | 'won'
  | 'lost'

export interface BattleLogEntry {
  id: string
  text: string
}

export interface BattleState {
  mode: BattleMode
  phase: BattlePhase
  turn: number
  playerParty: Battler[]
  enemyParty: Battler[]
  activePlayer: number
  activeEnemy: number
  log: BattleLogEntry[]
  eliteMemberName?: string
  eliteMemberTitle?: string
}
