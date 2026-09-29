import { useEffect, useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import {
  displayName,
  fallbackSpriteUrl,
  getPokemonDetail,
  getPokemonSummaries,
  padId,
  spriteUrl,
} from '../api/pokeapi'
import type { PokemonDetail, PokemonListItem } from '../types/pokemon'
import { TypeBadge } from '../components/TypeBadge'
import { StatBar } from '../components/StatBar'
import { useTeam } from '../hooks/useStorage'
import { useGameProgress } from '../game/useGameProgress'
import { xpToNext } from '../game/battleMath'
import { config } from '../config'
import './pages.css'

export function TeamPage() {
  const team = useTeam()
  const game = useGameProgress()
  const [items, setItems] = useState<PokemonListItem[]>([])
  const [details, setDetails] = useState<PokemonDetail[]>([])

  useEffect(() => {
    if (team.ids.length === 0) {
      setItems([])
      setDetails([])
      return
    }
    let cancelled = false
    void Promise.all([
      getPokemonSummaries(team.ids),
      Promise.all(team.ids.map((id) => getPokemonDetail(id))),
    ]).then(([summaries, full]) => {
      if (!cancelled) {
        setItems(summaries)
        setDetails(full)
      }
    })
    return () => {
      cancelled = true
    }
  }, [team.ids])

  const teamBst = useMemo(
    () =>
      details.reduce(
        (sum, p) => sum + p.stats.reduce((s, st) => s + st.value, 0),
        0,
      ),
    [details],
  )

  const avgStats = useMemo(() => {
    if (!details.length) return []
    const names = details[0].stats.map((s) => s.name)
    return names.map((name) => {
      const total = details.reduce(
        (sum, p) => sum + (p.stats.find((s) => s.name === name)?.value ?? 0),
        0,
      )
      return { name, value: Math.round(total / details.length) }
    })
  }, [details])

  return (
    <section className="page">
      <header className="section-head">
        <h1>Team builder</h1>
        <p className="lede">
          Create a battle party of up to {config.maxTeamSize} Pokémon. Reorder
          leads, then train or challenge the Elite Four in the Arena.
        </p>
        <div className="detail-actions">
          <Link className="btn btn--primary" to="/arena">
            Open Battle Arena
          </Link>
          {team.count > 0 ? (
            <button type="button" className="btn btn--ghost" onClick={team.clear}>
              Clear team
            </button>
          ) : null}
        </div>
      </header>

      {team.count === 0 ? (
        <p className="status">
          Empty party. Add Pokémon with the ○ button in the{' '}
          <Link to="/">catalog</Link> (max {config.maxTeamSize}).
        </p>
      ) : (
        <p className="result-count">
          Party {team.count}/{config.maxTeamSize} — lead fights first in battle.
        </p>
      )}

      <div className="team-slots">
        {items.map((p, index) => {
          const fighter = game.getFighter(p.id)
          return (
            <article key={p.id} className="panel team-slot">
              <div className="team-slot__top">
                <img
                  src={spriteUrl(p.id)}
                  alt={displayName(p.name)}
                  onError={(e) => {
                    e.currentTarget.src = fallbackSpriteUrl(p.id)
                  }}
                />
                <div>
                  <p className="detail-id">{padId(p.id)} · Slot {index + 1}</p>
                  <h3>
                    <Link to={`/pokemon/${p.id}`}>{displayName(p.name)}</Link>
                  </h3>
                  <div className="poke-card__types">
                    {p.types.map((t) => (
                      <TypeBadge key={t} type={t} size="sm" />
                    ))}
                  </div>
                  <p className="muted">
                    Battle Lv {fighter.level} · XP {fighter.xp}/
                    {xpToNext(fighter.level)}
                  </p>
                </div>
              </div>
              <div className="detail-actions">
                <button
                  type="button"
                  className="btn btn--ghost"
                  disabled={index === 0}
                  onClick={() => team.move(p.id, -1)}
                >
                  ↑
                </button>
                <button
                  type="button"
                  className="btn btn--ghost"
                  disabled={index === items.length - 1}
                  onClick={() => team.move(p.id, 1)}
                >
                  ↓
                </button>
                <button
                  type="button"
                  className="btn btn--ghost"
                  onClick={() => team.remove(p.id)}
                >
                  Remove
                </button>
              </div>
            </article>
          )
        })}
        {Array.from({ length: config.maxTeamSize - items.length }).map((_, i) => (
          <div key={`empty-${i}`} className="empty-slot panel">
            <span>Open slot {items.length + i + 1}</span>
            <Link to="/">Add from Pokédex</Link>
          </div>
        ))}
      </div>

      {details.length > 0 ? (
        <div className="detail-grid" style={{ marginTop: '1.5rem' }}>
          <article className="panel">
            <h2>Party overview</h2>
            <p className="muted">Combined BST {teamBst}</p>
            <ul className="ability-list">
              {details.map((p) => (
                <li key={p.id}>
                  <strong>
                    <Link to={`/pokemon/${p.id}`}>{displayName(p.name)}</Link>
                  </strong>
                  <p>
                    BST {p.stats.reduce((s, st) => s + st.value, 0)} ·{' '}
                    {p.types.map(displayName).join(' / ')}
                  </p>
                </li>
              ))}
            </ul>
          </article>
          <article className="panel">
            <h2>Average base stats</h2>
            <div className="stat-list">
              {avgStats.map((s) => (
                <StatBar key={s.name} name={s.name} value={s.value} />
              ))}
            </div>
          </article>
        </div>
      ) : null}
    </section>
  )
}
