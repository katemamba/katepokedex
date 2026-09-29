import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { displayName, getPokemonDetail, padId } from '../api/pokeapi'
import type { PokemonDetail } from '../types/pokemon'
import { TypeBadge } from '../components/TypeBadge'
import { StatBar } from '../components/StatBar'
import { EvolutionChain } from '../components/EvolutionChain'
import { useFavorites, useTeam } from '../hooks/useStorage'
import { defensiveMultipliers } from '../data/typeChart'
import { config } from '../config'
import { generationForId } from '../data/generations'
import './pages.css'

export function PokemonDetailPage() {
  const { id } = useParams()
  const [data, setData] = useState<PokemonDetail | null>(null)
  const [error, setError] = useState<string | null>(null)
  const favorites = useFavorites()
  const team = useTeam()

  useEffect(() => {
    if (!id) return
    let cancelled = false
    setData(null)
    setError(null)
    getPokemonDetail(id)
      .then((detail) => {
        if (!cancelled) setData(detail)
      })
      .catch((err: unknown) => {
        if (!cancelled) {
          setError(err instanceof Error ? err.message : 'Not found')
        }
      })
    return () => {
      cancelled = true
    }
  }, [id])

  if (error) {
    return (
      <section className="page">
        <p className="status status--error">{error}</p>
        <Link to="/">Back to Pokédex</Link>
      </section>
    )
  }

  if (!data) {
    return (
      <section className="page">
        <p className="status">Loading Pokémon…</p>
      </section>
    )
  }

  const total = data.stats.reduce((sum, s) => sum + s.value, 0)
  const gen = generationForId(data.id)
  const defense = defensiveMultipliers(data.types)
  const fav = favorites.has(data.id)
  const onTeam = team.has(data.id)

  return (
    <section className="page detail-page">
      <Link className="back-link" to="/">
        ← Back to catalog
      </Link>

      <div className="detail-hero panel">
        <div className="detail-hero__art">
          <img src={data.sprite} alt={displayName(data.name)} />
        </div>
        <div className="detail-hero__copy">
          <p className="detail-id">{padId(data.id)}</p>
          <h1>{displayName(data.name)}</h1>
          <p className="genus">{data.genus}</p>
          <div className="poke-card__types">
            {data.types.map((t) => (
              <TypeBadge key={t} type={t} />
            ))}
          </div>
          <p className="flavor">{data.flavorText}</p>
          <div className="meta-row">
            {gen ? (
              <span>
                {gen.label} · {gen.region}
              </span>
            ) : null}
            {data.habitat ? <span>Habitat: {displayName(data.habitat)}</span> : null}
            {data.isLegendary ? <span className="tag">Legendary</span> : null}
            {data.isMythical ? <span className="tag">Mythical</span> : null}
          </div>
          <div className="detail-actions">
            <button
              type="button"
              className={`btn ${fav ? 'btn--primary' : 'btn--ghost'}`}
              onClick={() => favorites.toggle(data.id)}
            >
              {fav ? '★ Favorited' : '☆ Favorite'}
            </button>
            <button
              type="button"
              className={`btn ${onTeam ? 'btn--primary' : 'btn--ghost'}`}
              onClick={() => {
                if (onTeam) {
                  team.remove(data.id)
                  return
                }
                if (team.toggle(data.id) === false) {
                  window.alert('Team is full (max 6).')
                }
              }}
            >
              {onTeam ? '● On team' : '○ Add to team'}
            </button>
            {data.cry ? (
              <button
                type="button"
                className="btn btn--ghost"
                onClick={() => {
                  const audio = new Audio(data.cry!)
                  void audio.play()
                }}
              >
                Play cry
              </button>
            ) : null}
            <Link className="btn btn--ghost" to={`/compare?a=${data.id}`}>
              Compare
            </Link>
          </div>
        </div>
      </div>

      <div className="detail-grid">
        <article className="panel">
          <h2>Base stats</h2>
          <p className="muted">BST {total}</p>
          <div className="stat-list">
            {data.stats.map((s) => (
              <StatBar key={s.name} name={s.name} value={s.value} />
            ))}
          </div>
        </article>

        <article className="panel">
          <h2>Profile</h2>
          <dl className="profile-dl">
            <div>
              <dt>Height</dt>
              <dd>{(data.height / 10).toFixed(1)} m</dd>
            </div>
            <div>
              <dt>Weight</dt>
              <dd>{(data.weight / 10).toFixed(1)} kg</dd>
            </div>
            <div>
              <dt>Generation</dt>
              <dd>{displayName(data.generation)}</dd>
            </div>
          </dl>
          <h3>Abilities</h3>
          <ul className="ability-list">
            {data.abilities.map((a) => (
              <li key={a.name}>
                <strong>
                  {displayName(a.name)}
                  {a.isHidden ? ' (Hidden)' : ''}
                </strong>
                <p>{a.shortEffect}</p>
              </li>
            ))}
          </ul>
        </article>

        <article className="panel panel--wide">
          <h2>Type defense</h2>
          <div className="defense-grid">
            {Object.entries(defense).map(([type, mult]) => (
              <div
                key={type}
                className={`defense-cell defense-cell--x${String(mult).replace('.', '_')}`}
              >
                <TypeBadge type={type as never} size="sm" />
                <span>×{mult}</span>
              </div>
            ))}
          </div>
        </article>

        {data.evolution ? (
          <article className="panel panel--wide">
            <h2>Evolution</h2>
            <EvolutionChain root={data.evolution} />
          </article>
        ) : null}

        <article className="panel panel--wide">
          <h2>Sample moves</h2>
          <div className="move-cloud">
            {data.moves.map((m) => (
              <span key={m}>{displayName(m)}</span>
            ))}
          </div>
          <p className="muted">Showing a sample of learnable moves.</p>
        </article>
      </div>

      <div className="pager">
        {data.id > 1 ? (
          <Link to={`/pokemon/${data.id - 1}`}>← Prev</Link>
        ) : (
          <span />
        )}
        {data.id < config.nationalDexCount ? (
          <Link to={`/pokemon/${data.id + 1}`}>Next →</Link>
        ) : (
          <span />
        )}
      </div>
    </section>
  )
}
