import { ITEMS, type ItemDef } from './items'
import { calcDamage } from './battleMath'
import type {
  Battler,
  BattleLogEntry,
  BattleMove,
  BattleState,
  ItemId,
} from './types'

let logSeq = 0
function log(text: string): BattleLogEntry {
  logSeq += 1
  return { id: `log-${logSeq}`, text }
}

function cloneParty(party: Battler[]): Battler[] {
  return party.map((b) => ({
    ...b,
    moves: b.moves.map((m) => ({ ...m })),
    stages: { ...b.stages },
  }))
}

export function createBattleState(
  partial: Omit<BattleState, 'phase' | 'turn' | 'log' | 'activePlayer' | 'activeEnemy'> & {
    logIntro?: string
  },
): BattleState {
  return {
    mode: partial.mode,
    phase: 'command',
    turn: 1,
    playerParty: cloneParty(partial.playerParty),
    enemyParty: cloneParty(partial.enemyParty),
    activePlayer: 0,
    activeEnemy: 0,
    log: [
      log(
        partial.logIntro ??
          (partial.mode === 'elite'
            ? `${partial.eliteMemberName} wants to battle!`
            : 'A wild challenger appeared!'),
      ),
    ],
    eliteMemberName: partial.eliteMemberName,
    eliteMemberTitle: partial.eliteMemberTitle,
  }
}

function livingIndex(party: Battler[], start = 0): number {
  for (let i = 0; i < party.length; i++) {
    const idx = (start + i) % party.length
    if (party[idx].hp > 0) return idx
  }
  return -1
}

function pickEnemyMove(enemy: Battler, player: Battler): BattleMove {
  const usable = enemy.moves.filter((m) => m.pp > 0)
  const pool = usable.length ? usable : enemy.moves
  let best = pool[0]
  let bestScore = -1
  for (const move of pool) {
    const { damage, missed } = calcDamage(enemy, player, { ...move, accuracy: 100 })
    const score = missed ? 0 : damage + Math.random() * 8
    if (score > bestScore) {
      bestScore = score
      best = move
    }
  }
  return best
}

function applyMove(
  state: BattleState,
  attackerSide: 'player' | 'enemy',
  moveIndex: number,
): BattleState {
  const next: BattleState = {
    ...state,
    playerParty: cloneParty(state.playerParty),
    enemyParty: cloneParty(state.enemyParty),
    log: [...state.log],
  }

  const attacker =
    attackerSide === 'player'
      ? next.playerParty[next.activePlayer]
      : next.enemyParty[next.activeEnemy]
  const defender =
    attackerSide === 'player'
      ? next.enemyParty[next.activeEnemy]
      : next.playerParty[next.activePlayer]

  if (!attacker || !defender) return next

  let move = attacker.moves[moveIndex]
  const hasPp = attacker.moves.some((m) => m.pp > 0)
  if (!move || move.pp <= 0) {
    if (!hasPp) {
      move = {
        id: 'struggle',
        name: 'Struggle',
        type: 'normal',
        category: 'physical',
        power: 50,
        accuracy: 100,
        pp: 1,
        maxPp: 1,
      }
      next.log.push(log(`${attacker.name} has no PP left and used Struggle!`))
    } else {
      next.log.push(log(`${attacker.name} can't use that move!`))
      return next
    }
  } else {
    move.pp -= 1
    next.log.push(log(`${attacker.name} used ${move.name}!`))
  }

  const result = calcDamage(attacker, defender, move)
  if (result.missed) {
    next.log.push(log('The attack missed!'))
    return next
  }

  defender.hp = Math.max(0, defender.hp - result.damage)
  if (result.effectiveness === 0) {
    next.log.push(log('It had no effect…'))
  } else if (result.effectiveness >= 2) {
    next.log.push(log(`It's super effective! (${result.damage} dmg)`))
  } else if (result.effectiveness <= 0.5) {
    next.log.push(log(`It's not very effective… (${result.damage} dmg)`))
  } else {
    next.log.push(log(`Dealt ${result.damage} damage.`))
  }

  if (move.id === 'struggle') {
    const recoil = Math.max(1, Math.floor(attacker.maxHp / 8))
    attacker.hp = Math.max(0, attacker.hp - recoil)
    next.log.push(log(`${attacker.name} was hurt by recoil!`))
  }

  if (defender.hp <= 0) {
    next.log.push(log(`${defender.name} fainted!`))
  }

  return next
}

function afterFaint(state: BattleState): BattleState {
  const next = { ...state, log: [...state.log] }

  if (next.enemyParty[next.activeEnemy].hp <= 0) {
    const idx = livingIndex(next.enemyParty, next.activeEnemy + 1)
    if (idx === -1) {
      next.phase = 'won'
      next.log.push(log('You won the battle!'))
      return next
    }
    next.activeEnemy = idx
    next.log.push(log(`Enemy sent out ${next.enemyParty[idx].name}!`))
  }

  if (next.playerParty[next.activePlayer].hp <= 0) {
    const idx = livingIndex(next.playerParty, next.activePlayer + 1)
    if (idx === -1) {
      next.phase = 'lost'
      next.log.push(log('You blacked out…'))
      return next
    }
    next.phase = 'switch'
    next.log.push(log('Choose your next Pokémon.'))
    return next
  }

  next.phase = 'command'
  return next
}

export function playerAttack(state: BattleState, moveIndex: number): BattleState {
  if (
    state.phase === 'won' ||
    state.phase === 'lost' ||
    state.phase === 'switch'
  ) {
    return state
  }
  let next = applyMove({ ...state, phase: 'animating' }, 'player', moveIndex)

  if (next.enemyParty[next.activeEnemy]?.hp > 0) {
    const enemy = next.enemyParty[next.activeEnemy]
    const player = next.playerParty[next.activePlayer]
    if (enemy && player && player.hp > 0) {
      const enemyMove = pickEnemyMove(enemy, player)
      const enemyMoveIndex = Math.max(
        0,
        enemy.moves.findIndex((m) => m.id === enemyMove.id),
      )
      next = applyMove(next, 'enemy', enemyMoveIndex)
    }
  }

  next.turn += 1
  return afterFaint(next)
}

export function playerUseItem(
  state: BattleState,
  itemId: ItemId,
  targetIndex: number,
  consume: (id: ItemId) => boolean,
): BattleState {
  const def: ItemDef = ITEMS[itemId]
  const next: BattleState = {
    ...state,
    playerParty: cloneParty(state.playerParty),
    enemyParty: cloneParty(state.enemyParty),
    log: [...state.log],
    phase: 'animating',
  }

  const target = next.playerParty[targetIndex]
  if (!target) return state

  if (def.revive) {
    if (target.hp > 0) {
      next.log.push(log('That Pokémon is still standing.'))
      next.phase = 'command'
      return next
    }
    if (!consume(itemId)) return { ...state, phase: 'command' }
    target.hp = Math.floor(target.maxHp / 2)
    next.log.push(log(`Used ${def.name} on ${target.name}!`))
  } else if (def.fullHeal || def.heal) {
    if (target.hp <= 0) {
      next.log.push(log('It has no effect on a fainted Pokémon.'))
      next.phase = 'command'
      return next
    }
    if (target.hp >= target.maxHp) {
      next.log.push(log(`${target.name}'s HP is already full.`))
      next.phase = 'command'
      return next
    }
    if (!consume(itemId)) return { ...state, phase: 'command' }
    const before = target.hp
    target.hp = def.fullHeal
      ? target.maxHp
      : Math.min(target.maxHp, target.hp + (def.heal ?? 0))
    next.log.push(
      log(`Used ${def.name}! ${target.name} recovered ${target.hp - before} HP.`),
    )
  } else if (def.boostAttack || def.boostDefense) {
    if (targetIndex !== next.activePlayer) {
      next.log.push(log('Boost items only work on the active Pokémon.'))
      next.phase = 'command'
      return next
    }
    if (!consume(itemId)) return { ...state, phase: 'command' }
    if (def.boostAttack) {
      target.stages.attack = Math.min(6, target.stages.attack + def.boostAttack)
      next.log.push(log(`${target.name}'s Attack rose!`))
    }
    if (def.boostDefense) {
      target.stages.defense = Math.min(6, target.stages.defense + def.boostDefense)
      next.log.push(log(`${target.name}'s Defense rose!`))
    }
  } else {
    return { ...state, phase: 'command' }
  }

  if (next.enemyParty[next.activeEnemy].hp > 0 && next.playerParty[next.activePlayer].hp > 0) {
    const enemy = next.enemyParty[next.activeEnemy]
    const player = next.playerParty[next.activePlayer]
    const enemyMove = pickEnemyMove(enemy, player)
    const enemyMoveIndex = enemy.moves.findIndex((m) => m.id === enemyMove.id)
    const afterEnemy = applyMove(next, 'enemy', Math.max(0, enemyMoveIndex))
    afterEnemy.turn += 1
    return afterFaint(afterEnemy)
  }

  next.turn += 1
  return afterFaint(next)
}

export function playerSwitch(state: BattleState, index: number): BattleState {
  if (index === state.activePlayer) return state
  const target = state.playerParty[index]
  if (!target || target.hp <= 0) return state

  const forced = state.phase === 'switch'
  const next: BattleState = {
    ...state,
    playerParty: cloneParty(state.playerParty),
    enemyParty: cloneParty(state.enemyParty),
    log: [...state.log],
    activePlayer: index,
    phase: 'animating',
  }
  next.log.push(log(`Go! ${target.name}!`))

  if (forced) {
    next.phase = 'command'
    return next
  }

  const enemy = next.enemyParty[next.activeEnemy]
  const player = next.playerParty[next.activePlayer]
  const enemyMove = pickEnemyMove(enemy, player)
  const enemyMoveIndex = enemy.moves.findIndex((m) => m.id === enemyMove.id)
  const afterEnemy = applyMove(next, 'enemy', Math.max(0, enemyMoveIndex))
  afterEnemy.turn += 1
  return afterFaint(afterEnemy)
}

export function partyAlive(party: Battler[]): boolean {
  return party.some((b) => b.hp > 0)
}
