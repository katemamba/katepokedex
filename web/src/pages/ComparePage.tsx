import { useEffect, useMemo, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { displayName, getPokemonDetail, padId } from '../api/pokeapi'
import type { PokemonDetail } from '../types/pokemon'
import { TypeBadge } from '../components/TypeBadge'
import { StatBar } from '../components/StatBar'
import './pages.css'

function Slot({
  label,
  value,
  onChange,
  data,
}: {
  label: string
  value: string
  onChange: (v: string) => void
  data: PokemonDetail | null
}) {
  return (
    <article className="panel compare-slot">
      <label className="field">
        <span>{label} (id or name)</span>
        <input
          value={value}
          onChange={(e) => onChange(e.target.value)}
          placeholder="e.g. 25 or pikachu"
        />
      </label>
      {data ? (
        <div className="compare-body">
          <Link to={`/pokemon/${data.id}`}>
            <img src={data.sprite} alt={displayName(data.name)} />
          </Link>
          <p className="detail-id">{padId(data.id)}</p>
          <h2>{displayName(data.name)}</h2>
          <div className="poke-card__types">
            {data.types.map((t) => (
              <TypeBadge key={t} type={t} size="sm" />
            ))}
          </div>
          <p className="muted">
            BST {data.stats.reduce((s, st) => s + st.value, 0)}
          </p>
          <div className="stat-list">
            {data.stats.map((s) => (
              <StatBar key={s.name} name={s.name} value={s.value} />
            ))}
          </div>
        </div>
      ) : (
        <p className="status">Enter a Pokémon to load.</p>
      )}
    </article>
  )
}

export function ComparePage() {
  const [params, setParams] = useSearchParams()
  const [a, setA] = useState(params.get('a') ?? '1')
  const [b, setB] = useState(params.get('b') ?? '4')
  const [left, setLeft] = useState<PokemonDetail | null>(null)
  const [right, setRight] = useState<PokemonDetail | null>(null)

  useEffect(() => {
    const next = new URLSearchParams()
    if (a) next.set('a', a)
    if (b) next.set('b', b)
    setParams(next, { replace: true })
  }, [a, b, setParams])

  useEffect(() => {
    let cancelled = false
    if (!a.trim()) {
      setLeft(null)
      return
    }
    getPokemonDetail(a.trim().toLowerCase())
      .then((d) => {
        if (!cancelled) setLeft(d)
      })
      .catch(() => {
        if (!cancelled) setLeft(null)
      })
    return () => {
      cancelled = true
    }
  }, [a])

  useEffect(() => {
    let cancelled = false
    if (!b.trim()) {
      setRight(null)
      return
    }
    getPokemonDetail(b.trim().toLowerCase())
      .then((d) => {
        if (!cancelled) setRight(d)
      })
      .catch(() => {
        if (!cancelled) setRight(null)
      })
    return () => {
      cancelled = true
    }
  }, [b])

  const winners = useMemo(() => {
    if (!left || !right) return null
    return left.stats.map((s, i) => {
      const other = right.stats[i]?.value ?? 0
      if (s.value > other) return 'a'
      if (s.value < other) return 'b'
      return 'tie'
    })
  }, [left, right])

  return (
    <section className="page">
      <header className="section-head">
        <h1>Compare</h1>
        <p className="lede">Side-by-side base stats for any two Pokémon.</p>
      </header>

      <div className="compare-grid">
        <Slot label="Pokémon A" value={a} onChange={setA} data={left} />
        <Slot label="Pokémon B" value={b} onChange={setB} data={right} />
      </div>

      {winners && left && right ? (
        <article className="panel" style={{ marginTop: '1rem' }}>
          <h2>Stat edge</h2>
          <ul className="ability-list">
            {left.stats.map((s, i) => (
              <li key={s.name}>
                <strong>{displayName(s.name)}</strong>
                <p>
                  {displayName(left.name)} {s.value} vs{' '}
                  {displayName(right.name)} {right.stats[i]?.value} —{' '}
                  {winners[i] === 'tie'
                    ? 'tie'
                    : winners[i] === 'a'
                      ? `${displayName(left.name)} leads`
                      : `${displayName(right.name)} leads`}
                </p>
              </li>
            ))}
          </ul>
        </article>
      ) : null}
    </section>
  )
}
