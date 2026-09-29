import { useMemo, useRef, useState } from 'react'
import {
  playerAttack,
  playerSwitch,
  playerUseItem,
} from '../game/battleEngine'
import { ITEMS } from '../game/items'
import type { BattleState, ItemId } from '../game/types'
import { TypeBadge } from './TypeBadge'
import './BattleArena.css'

interface Props {
  initial: BattleState
  bag: Partial<Record<ItemId, number>>
  onConsumeItem: (id: ItemId) => boolean
  onWin: (state: BattleState) => void
  onLose: (state: BattleState) => void
  onExit: () => void
}

function HpBar({ hp, maxHp }: { hp: number; maxHp: number }) {
  const pct = Math.max(0, Math.min(100, (hp / Math.max(1, maxHp)) * 100))
  const tone = pct > 50 ? 'ok' : pct > 20 ? 'mid' : 'low'
  return (
    <div className="hp-bar">
      <div className="hp-bar__track">
        <div
          className={`hp-bar__fill hp-bar__fill--${tone}`}
          style={{ width: `${pct}%` }}
        />
      </div>
      <span>
        {hp}/{maxHp}
      </span>
    </div>
  )
}

export function BattleArena({
  initial,
  bag,
  onConsumeItem,
  onWin,
  onLose,
  onExit,
}: Props) {
  const [state, setState] = useState(initial)
  const [menu, setMenu] = useState<'command' | 'fight' | 'bag' | 'switch'>(
    'command',
  )
  const [itemTarget, setItemTarget] = useState<ItemId | null>(null)
  const finishedRef = useRef(false)
  const busyRef = useRef(false)

  const player = state.playerParty[state.activePlayer]
  const enemy = state.enemyParty[state.activeEnemy]
  const hasPp = player?.moves.some((m) => m.pp > 0) ?? false

  const bagList = useMemo(
    () =>
      (Object.keys(ITEMS) as ItemId[])
        .map((id) => ({ id, count: bag[id] ?? 0, def: ITEMS[id] }))
        .filter((x) => x.count > 0),
    [bag],
  )

  function goCommand() {
    setItemTarget(null)
    setMenu('command')
    setState((s) =>
      s.phase === 'won' || s.phase === 'lost' || s.phase === 'switch'
        ? s
        : { ...s, phase: 'command' },
    )
  }

  function applyTurn(next: BattleState) {
    if (busyRef.current) return
    busyRef.current = true
    try {
      setState(next)
      if (next.phase === 'won' && !finishedRef.current) {
        finishedRef.current = true
        onWin(next)
        setMenu('command')
      } else if (next.phase === 'lost' && !finishedRef.current) {
        finishedRef.current = true
        onLose(next)
        setMenu('command')
      } else if (next.phase === 'switch') {
        setMenu('switch')
      } else {
        setMenu('command')
        setItemTarget(null)
      }
    } finally {
      busyRef.current = false
    }
  }

  if (!player || !enemy) {
    return (
      <div className="battle-arena panel">
        <p className="status status--error">Battle data missing. Please restart.</p>
        <button type="button" className="btn btn--primary" onClick={onExit}>
          Back to Arena
        </button>
      </div>
    )
  }

  const ended = state.phase === 'won' || state.phase === 'lost'

  return (
    <div className="battle-arena">
      <div className="battle-stage panel">
        <div className="battle-fighter battle-fighter--enemy">
          <div className="battle-fighter__meta">
            <strong>
              {enemy.name}{' '}
              <span className="battle-lv">Lv{enemy.level}</span>
            </strong>
            <HpBar hp={enemy.hp} maxHp={enemy.maxHp} />
            <div className="poke-card__types">
              {enemy.types.map((t) => (
                <TypeBadge key={t} type={t} size="sm" />
              ))}
            </div>
          </div>
          <img src={enemy.sprite} alt={enemy.name} />
        </div>

        <div className="battle-fighter battle-fighter--player">
          <img src={player.sprite} alt={player.name} />
          <div className="battle-fighter__meta">
            <strong>
              {player.name}{' '}
              <span className="battle-lv">Lv{player.level}</span>
            </strong>
            <HpBar hp={player.hp} maxHp={player.maxHp} />
            <div className="poke-card__types">
              {player.types.map((t) => (
                <TypeBadge key={t} type={t} size="sm" />
              ))}
            </div>
          </div>
        </div>

        <p className="battle-turn">Turn {state.turn}</p>
        {state.eliteMemberName ? (
          <p className="battle-opponent-tag">
            {state.eliteMemberTitle} {state.eliteMemberName}
          </p>
        ) : null}
      </div>

      <div className="battle-log panel">
        {state.log.slice(-8).map((entry) => (
          <p key={entry.id}>{entry.text}</p>
        ))}
      </div>

      {ended ? (
        <div className="battle-menu panel">
          <p className="status">
            {state.phase === 'won' ? 'Victory!' : 'Defeat…'}
          </p>
          <button type="button" className="btn btn--primary" onClick={onExit}>
            Back to Arena
          </button>
        </div>
      ) : (
        <div className="battle-menu panel">
          {menu === 'command' && state.phase !== 'switch' ? (
            <div className="battle-commands">
              <button
                type="button"
                className="btn btn--primary"
                onClick={() => setMenu('fight')}
              >
                Fight
              </button>
              <button
                type="button"
                className="btn btn--ghost"
                onClick={() => {
                  setItemTarget(null)
                  setMenu('bag')
                }}
              >
                Bag
              </button>
              <button
                type="button"
                className="btn btn--ghost"
                onClick={() => setMenu('switch')}
              >
                Pokémon
              </button>
              {state.mode === 'train' ? (
                <button type="button" className="btn btn--ghost" onClick={onExit}>
                  Run
                </button>
              ) : null}
            </div>
          ) : null}

          {menu === 'fight' ? (
            <div className="battle-moves">
              {player.moves.map((move, index) => (
                <button
                  key={move.id}
                  type="button"
                  className="move-btn"
                  disabled={player.hp <= 0 || (move.pp <= 0 && hasPp)}
                  onClick={() => applyTurn(playerAttack(state, index))}
                >
                  <span>{move.name}</span>
                  <small>
                    {move.type} · {move.power} · PP {move.pp}/{move.maxPp}
                  </small>
                </button>
              ))}
              {!hasPp ? (
                <button
                  type="button"
                  className="move-btn"
                  onClick={() => applyTurn(playerAttack(state, 0))}
                >
                  <span>Struggle</span>
                  <small>normal · last resort</small>
                </button>
              ) : null}
              <button type="button" className="btn btn--ghost" onClick={goCommand}>
                Back
              </button>
            </div>
          ) : null}

          {menu === 'bag' ? (
            <div className="battle-bag">
              {!itemTarget ? (
                <>
                  {bagList.length === 0 ? (
                    <p className="status">Bag is empty.</p>
                  ) : (
                    bagList.map((item) => (
                      <button
                        key={item.id}
                        type="button"
                        className="bag-btn"
                        onClick={() => setItemTarget(item.id)}
                      >
                        <strong>
                          {item.def.name} ×{item.count}
                        </strong>
                        <small>{item.def.description}</small>
                      </button>
                    ))
                  )}
                  <button type="button" className="btn btn--ghost" onClick={goCommand}>
                    Back
                  </button>
                </>
              ) : (
                <>
                  <p className="muted">Use {ITEMS[itemTarget].name} on:</p>
                  {state.playerParty.map((ally, index) => (
                    <button
                      key={ally.key}
                      type="button"
                      className="bag-btn"
                      onClick={() => {
                        applyTurn(
                          playerUseItem(state, itemTarget, index, onConsumeItem),
                        )
                      }}
                    >
                      {ally.name} ({ally.hp}/{ally.maxHp})
                    </button>
                  ))}
                  <button
                    type="button"
                    className="btn btn--ghost"
                    onClick={() => setItemTarget(null)}
                  >
                    Back
                  </button>
                </>
              )}
            </div>
          ) : null}

          {menu === 'switch' || state.phase === 'switch' ? (
            <div className="battle-switch">
              {state.phase === 'switch' ? (
                <p className="muted">Your Pokémon fainted — choose a replacement.</p>
              ) : null}
              {state.playerParty.map((ally, index) => (
                <button
                  key={ally.key}
                  type="button"
                  className="bag-btn"
                  disabled={ally.hp <= 0 || index === state.activePlayer}
                  onClick={() => applyTurn(playerSwitch(state, index))}
                >
                  {ally.name} · Lv{ally.level} · {ally.hp}/{ally.maxHp}
                </button>
              ))}
              {state.phase !== 'switch' ? (
                <button type="button" className="btn btn--ghost" onClick={goCommand}>
                  Back
                </button>
              ) : null}
            </div>
          ) : null}
        </div>
      )}
    </div>
  )
}
