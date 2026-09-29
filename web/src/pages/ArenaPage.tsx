import { useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import { getBattleProfile } from '../api/pokeapi'
import { createBattleState } from '../game/battleEngine'
import {
  createBattler,
  trainRewardXp,
  xpToNext,
} from '../game/battleMath'
import { ELITE_FOUR } from '../game/eliteFour'
import { ITEMS } from '../game/items'
import { useGameProgress } from '../game/useGameProgress'
import type { BattleState, ItemId } from '../game/types'
import { BattleArena } from '../components/BattleArena'
import { config } from '../config'
import './pages.css'
import './ArenaPage.css'

type Mode = 'hub' | 'battle'

export function ArenaPage() {
  const game = useGameProgress()
  const [mode, setMode] = useState<Mode>('hub')
  const [battle, setBattle] = useState<BattleState | null>(null)
  const [status, setStatus] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)

  const avgLevel = useMemo(() => {
    if (!game.progress.fighters.length) return 12
    const sum = game.progress.fighters.reduce((s, f) => s + f.level, 0)
    return Math.round(sum / game.progress.fighters.length)
  }, [game.progress.fighters])

  const currentElite = ELITE_FOUR[Math.min(game.progress.eliteFourIndex, ELITE_FOUR.length - 1)]

  async function loadPartyBattlers() {
    if (game.team.ids.length === 0) {
      throw new Error('Build a team first (up to 6 Pokémon).')
    }
    return Promise.all(
      game.team.ids.map(async (id) => {
        const detail = await getBattleProfile(id)
        const fighter = game.getFighter(id)
        return createBattler({
          pokemonId: id,
          name: detail.name,
          types: detail.types,
          stats: detail.stats,
          level: fighter.level,
          keyPrefix: 'you',
        })
      }),
    )
  }

  async function startTraining() {
    if (loading) return
    setLoading(true)
    setStatus(null)
    try {
      const party = await loadPartyBattlers()
      const wildId = 1 + Math.floor(Math.random() * 151)
      const wildDetail = await getBattleProfile(wildId)
      const wildLevel = Math.max(
        5,
        Math.min(100, avgLevel + Math.floor(Math.random() * 7) - 2),
      )
      const enemy = createBattler({
        pokemonId: wildId,
        name: wildDetail.name,
        types: wildDetail.types,
        stats: wildDetail.stats,
        level: wildLevel,
        keyPrefix: 'wild',
      })
      setBattle(
        createBattleState({
          mode: 'train',
          playerParty: party,
          enemyParty: [enemy],
          logIntro: `A wild ${enemy.name} (Lv${wildLevel}) appeared!`,
        }),
      )
      setMode('battle')
    } catch (err) {
      setStatus(err instanceof Error ? err.message : 'Could not start training.')
    } finally {
      setLoading(false)
    }
  }

  async function startElite() {
    if (loading) return
    if (game.progress.championBeaten) {
      setStatus('You already beat the Champion. Reset the challenge to fight again.')
      return
    }
    if (game.team.ids.length < 1) {
      setStatus('You need at least one Pokémon on your team.')
      return
    }
    setLoading(true)
    setStatus(null)
    try {
      const member = ELITE_FOUR[game.progress.eliteFourIndex]
      if (!member) throw new Error('Elite Four challenge is complete.')
      const party = await loadPartyBattlers()
      const enemyLevel = Math.min(100, avgLevel + 4 + member.levelBoost)
      const enemies = await Promise.all(
        member.team.map(async (id) => {
          const detail = await getBattleProfile(id)
          return createBattler({
            pokemonId: id,
            name: detail.name,
            types: detail.types,
            stats: detail.stats,
            level: enemyLevel,
            keyPrefix: member.id,
          })
        }),
      )
      setBattle(
        createBattleState({
          mode: 'elite',
          playerParty: party,
          enemyParty: enemies,
          eliteMemberName: member.name,
          eliteMemberTitle: member.title,
          logIntro: `${member.title} ${member.name} wants to battle!`,
        }),
      )
      setMode('battle')
    } catch (err) {
      setStatus(err instanceof Error ? err.message : 'Could not start Elite Four.')
    } finally {
      setLoading(false)
    }
  }

  function handleWin(state: BattleState) {
    const active = state.playerParty[state.activePlayer]
    if (!active) return
    if (state.mode === 'train') {
      const enemy = state.enemyParty[0]
      const xp = trainRewardXp(active.level, enemy?.level ?? avgLevel)
      const leveled = game.gainXpFor(active.pokemonId, xp)
      game.recordTrainingWin()

      const dropRoll = Math.random()
      const drop: Partial<Record<ItemId, number>> = {}
      if (dropRoll > 0.55) drop.potion = 1
      if (dropRoll > 0.8) drop['super-potion'] = 1
      if (dropRoll > 0.92) drop.revive = 1
      if (dropRoll > 0.97) drop['x-attack'] = 1
      if (Object.keys(drop).length) game.addItems(drop)

      const dropText = Object.entries(drop)
        .map(([id, n]) => `${ITEMS[id as ItemId].name} ×${n}`)
        .join(', ')

      setStatus(
        `Training win! ${active.name} gained ${xp} XP` +
          (leveled ? ` and grew ${leveled} level(s)!` : '.') +
          (dropText ? ` Found: ${dropText}.` : ''),
      )
    } else {
      const clearedIndex = game.progress.eliteFourIndex
      game.advanceEliteFour()
      const rewards: Partial<Record<ItemId, number>> = { 'hyper-potion': 1 }
      if (clearedIndex >= 2) rewards.revive = 1
      if (clearedIndex >= 3) rewards['full-restore'] = 1
      game.addItems(rewards)
      const nextIdx = clearedIndex + 1
      if (nextIdx >= ELITE_FOUR.length) {
        setStatus('Champion defeated! You cleared the Elite Four challenge.')
      } else {
        setStatus(
          `Defeated ${state.eliteMemberName}! Next up: ${ELITE_FOUR[nextIdx].name}.`,
        )
      }
    }
  }

  function handleLose() {
    setStatus(
      battle?.mode === 'elite'
        ? 'You lost to the Elite Four. Heal up, train, and try again — progress is kept.'
        : 'Your party was wiped. Stock potions and try another training bout.',
    )
  }

  if (mode === 'battle' && battle) {
    return (
      <section className="page arena-page">
        <header className="section-head">
          <h1>{battle.mode === 'elite' ? 'Elite Four' : 'Training'}</h1>
          <p className="lede">Turn-based battle — Fight, Bag, or switch Pokémon.</p>
        </header>
        <BattleArena
          key={battle.enemyParty.map((p) => p.key).join('-')}
          initial={battle}
          bag={game.progress.bag}
          onConsumeItem={game.consumeItem}
          onWin={handleWin}
          onLose={handleLose}
          onExit={() => {
            setBattle(null)
            setMode('hub')
          }}
        />
      </section>
    )
  }

  return (
    <section className="page arena-page">
      <header className="section-head">
        <h1>Battle Arena</h1>
        <p className="lede">
          Create a team of up to {config.maxTeamSize}, train with turns and items,
          then challenge the Elite Four.
        </p>
      </header>

      {status ? <p className="status arena-status">{status}</p> : null}
      {loading ? <p className="status">Preparing battle…</p> : null}

      <div className="arena-grid">
        <article className="panel">
          <h2>Your party</h2>
          {game.team.count === 0 ? (
            <p className="status">
              No team yet. Build one on the <Link to="/team">Team</Link> page
              (max {config.maxTeamSize}).
            </p>
          ) : (
            <ul className="ability-list">
              {game.progress.fighters.map((f) => (
                <li key={f.pokemonId}>
                  <strong>
                    <Link to={`/pokemon/${f.pokemonId}`}>#{f.pokemonId}</Link>
                    {' · '}Lv {f.level}
                  </strong>
                  <p>
                    XP {f.xp}/{xpToNext(f.level)}
                  </p>
                </li>
              ))}
            </ul>
          )}
          <div className="detail-actions">
            <Link className="btn btn--ghost" to="/team">
              Edit team
            </Link>
          </div>
        </article>

        <article className="panel">
          <h2>Bag</h2>
          <ul className="ability-list">
            {(Object.keys(ITEMS) as ItemId[]).map((id) => (
              <li key={id}>
                <strong>
                  {ITEMS[id].name} ×{game.progress.bag[id] ?? 0}
                </strong>
                <p>{ITEMS[id].description}</p>
              </li>
            ))}
          </ul>
          <button type="button" className="btn btn--ghost" onClick={game.resetBag}>
            Restock starter bag
          </button>
        </article>

        <article className="panel arena-card">
          <h2>Training</h2>
          <p>
            Fight a wild Kanto Pokémon near your average level ({avgLevel}).
            Earn XP and item drops each win. Turns use moves and bag items.
          </p>
          <p className="muted">Training wins: {game.progress.trainingWins}</p>
          <button
            type="button"
            className="btn btn--primary"
            disabled={loading || game.team.count === 0}
            onClick={() => void startTraining()}
          >
            {loading ? 'Loading…' : 'Start training battle'}
          </button>
        </article>

        <article className="panel arena-card">
          <h2>Elite Four</h2>
          {game.progress.championBeaten ? (
            <p>
              Challenge cleared. Reset to run the gauntlet again from Lorelei.
            </p>
          ) : (
            <>
              <p>
                Next: <strong>{currentElite.title} {currentElite.name}</strong>
              </p>
              <p className="muted">{currentElite.flavor}</p>
              <p className="muted">
                Progress {game.progress.eliteFourIndex}/{ELITE_FOUR.length}
              </p>
            </>
          )}
          <div className="elite-track">
            {ELITE_FOUR.map((m, i) => (
              <span
                key={m.id}
                className={
                  i < game.progress.eliteFourIndex
                    ? 'elite-pip is-done'
                    : i === game.progress.eliteFourIndex && !game.progress.championBeaten
                      ? 'elite-pip is-current'
                      : 'elite-pip'
                }
              >
                {m.name}
              </span>
            ))}
          </div>
          <div className="detail-actions">
            <button
              type="button"
              className="btn btn--primary"
              disabled={loading || game.team.count === 0 || game.progress.championBeaten}
              onClick={() => void startElite()}
            >
              {loading ? 'Loading…' : 'Challenge next member'}
            </button>
            <button
              type="button"
              className="btn btn--ghost"
              onClick={game.resetEliteFour}
            >
              Reset challenge
            </button>
          </div>
        </article>
      </div>
    </section>
  )
}
