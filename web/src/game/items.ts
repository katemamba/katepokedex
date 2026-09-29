import type { ItemId } from './types'

export interface ItemDef {
  id: ItemId
  name: string
  description: string
  heal?: number
  fullHeal?: boolean
  revive?: boolean
  boostAttack?: number
  boostDefense?: number
  battleOnly?: boolean
}

export const ITEMS: Record<ItemId, ItemDef> = {
  potion: {
    id: 'potion',
    name: 'Potion',
    description: 'Restores 20 HP.',
    heal: 20,
  },
  'super-potion': {
    id: 'super-potion',
    name: 'Super Potion',
    description: 'Restores 50 HP.',
    heal: 50,
  },
  'hyper-potion': {
    id: 'hyper-potion',
    name: 'Hyper Potion',
    description: 'Restores 120 HP.',
    heal: 120,
  },
  'full-restore': {
    id: 'full-restore',
    name: 'Full Restore',
    description: 'Fully restores HP.',
    fullHeal: true,
  },
  'x-attack': {
    id: 'x-attack',
    name: 'X Attack',
    description: 'Raises Attack for this battle.',
    boostAttack: 1,
    battleOnly: true,
  },
  'x-defense': {
    id: 'x-defense',
    name: 'X Defense',
    description: 'Raises Defense for this battle.',
    boostDefense: 1,
    battleOnly: true,
  },
  revive: {
    id: 'revive',
    name: 'Revive',
    description: 'Revives a fainted ally to half HP.',
    revive: true,
  },
}

export const STARTER_BAG: Partial<Record<ItemId, number>> = {
  potion: 5,
  'super-potion': 2,
  'x-attack': 1,
  'x-defense': 1,
  revive: 1,
}

export function defaultBag(): Partial<Record<ItemId, number>> {
  return { ...STARTER_BAG }
}
