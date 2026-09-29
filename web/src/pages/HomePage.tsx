import { useDeferredValue, useEffect, useMemo, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { buildPokemonIndex } from '../api/pokeapi'
import type { PokemonListItem, PokemonTypeName } from '../types/pokemon'
import { ALL_TYPES } from '../data/typeChart'
import { generations } from '../data/generations'
import { PokemonCard } from '../components/PokemonCard'
import './pages.css'

const PAGE_SIZE = 48

export function HomePage() {
  const navigate = useNavigate()
  const [index, setIndex] = useState<PokemonListItem[] | null>(null)
  const [error, setError] = useState<string | null>(null)
  const [query, setQuery] = useState('')
  const [type, setType] = useState<PokemonTypeName | ''>('')
  const [gen, setGen] = useState<number | ''>('')
  const [sort, setSort] = useState<'id' | 'name' | 'random'>('id')
  const [visible, setVisible] = useState(PAGE_SIZE)
  const deferredQuery = useDeferredValue(query.trim().toLowerCase())

  useEffect(() => {
    let cancelled = false
    buildPokemonIndex()
      .then((data) => {
        if (!cancelled) setIndex(data)
      })
      .catch((err: unknown) => {
        if (!cancelled) {
          setError(err instanceof Error ? err.message : 'Failed to load Pokédex')
        }
      })
    return () => {
      cancelled = true
    }
  }, [])

  const filtered = useMemo(() => {
    if (!index) return []
    let list = index

    if (deferredQuery) {
      list = list.filter(
        (p) =>
          p.name.includes(deferredQuery) ||
          String(p.id) === deferredQuery ||
          String(p.id).padStart(4, '0').includes(deferredQuery),
      )
    }

    if (type) {
      list = list.filter((p) => p.types.includes(type))
    }

    if (gen) {
      const range = generations.find((g) => g.id === gen)
      if (range) {
        list = list.filter((p) => p.id >= range.start && p.id <= range.end)
      }
    }

    if (sort === 'name') {
      list = [...list].sort((a, b) => a.name.localeCompare(b.name))
    } else if (sort === 'random') {
      list = [...list].sort(() => Math.random() - 0.5)
    } else {
      list = [...list].sort((a, b) => a.id - b.id)
    }

    return list
  }, [index, deferredQuery, type, gen, sort])

  useEffect(() => {
    setVisible(PAGE_SIZE)
  }, [deferredQuery, type, gen, sort])

  const shown = filtered.slice(0, visible)

  return (
    <section className="page home-page">
      <header className="hero">
        <p className="eyebrow">Field research journal</p>
        <h1>Kate Pokédex</h1>
        <p className="lede">
          Search every Pokémon across nine generations — stats, evolutions,
          types, teams, and more.
        </p>
        <div className="hero__cta">
          <a className="btn btn--primary" href="#catalog">
            Open catalog
          </a>
          <button
            type="button"
            className="btn btn--ghost"
            onClick={() => {
              if (!index?.length) return
              const pick = index[Math.floor(Math.random() * index.length)]
              navigate(`/pokemon/${pick.id}`)
            }}
          >
            Surprise me
          </button>
        </div>
      </header>

      <div id="catalog" className="filters panel">
        <label className="field field--grow">
          <span>Search</span>
          <input
            type="search"
            placeholder="Name or number…"
            value={query}
            onChange={(e) => setQuery(e.target.value)}
          />
        </label>
        <label className="field">
          <span>Type</span>
          <select
            value={type}
            onChange={(e) => setType(e.target.value as PokemonTypeName | '')}
          >
            <option value="">All types</option>
            {ALL_TYPES.map((t) => (
              <option key={t} value={t}>
                {t}
              </option>
            ))}
          </select>
        </label>
        <label className="field">
          <span>Generation</span>
          <select
            value={gen}
            onChange={(e) =>
              setGen(e.target.value ? Number(e.target.value) : '')
            }
          >
            <option value="">All gens</option>
            {generations.map((g) => (
              <option key={g.id} value={g.id}>
                {g.label} · {g.region}
              </option>
            ))}
          </select>
        </label>
        <label className="field">
          <span>Sort</span>
          <select
            value={sort}
            onChange={(e) =>
              setSort(e.target.value as 'id' | 'name' | 'random')
            }
          >
            <option value="id">Number</option>
            <option value="name">Name</option>
            <option value="random">Shuffle</option>
          </select>
        </label>
      </div>

      {!index && !error ? (
        <p className="status">Loading national Pokédex… first visit builds a local index.</p>
      ) : null}
      {error ? <p className="status status--error">{error}</p> : null}

      {index ? (
        <>
          <p className="result-count">
            Showing {shown.length} of {filtered.length} Pokémon
          </p>
          <div className="poke-grid">
            {shown.map((p) => (
              <PokemonCard key={p.id} pokemon={p} />
            ))}
          </div>
          {visible < filtered.length ? (
            <div className="load-more">
              <button
                type="button"
                className="btn btn--primary"
                onClick={() => setVisible((v) => v + PAGE_SIZE)}
              >
                Load more
              </button>
            </div>
          ) : null}
          {filtered.length === 0 ? (
            <p className="status">No Pokémon match those filters.</p>
          ) : null}
        </>
      ) : null}
    </section>
  )
}
