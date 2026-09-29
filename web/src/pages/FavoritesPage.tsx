import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { getPokemonSummaries } from '../api/pokeapi'
import type { PokemonListItem } from '../types/pokemon'
import { PokemonCard } from '../components/PokemonCard'
import { useFavorites } from '../hooks/useStorage'
import './pages.css'

export function FavoritesPage() {
  const favorites = useFavorites()
  const [items, setItems] = useState<PokemonListItem[]>([])
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    if (favorites.ids.length === 0) {
      setItems([])
      return
    }
    let cancelled = false
    setLoading(true)
    getPokemonSummaries(favorites.ids)
      .then((data) => {
        if (!cancelled) setItems(data)
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })
    return () => {
      cancelled = true
    }
  }, [favorites.ids])

  return (
    <section className="page">
      <header className="section-head">
        <h1>Favorites</h1>
        <p className="lede">Your starred Pokémon, saved in this browser.</p>
        {favorites.count > 0 ? (
          <button type="button" className="btn btn--ghost" onClick={favorites.clear}>
            Clear all
          </button>
        ) : null}
      </header>

      {favorites.count === 0 ? (
        <p className="status">
          No favorites yet. Star Pokémon from the{' '}
          <Link to="/">catalog</Link>.
        </p>
      ) : null}
      {loading ? <p className="status">Loading favorites…</p> : null}
      <div className="poke-grid">
        {items.map((p) => (
          <PokemonCard key={p.id} pokemon={p} />
        ))}
      </div>
    </section>
  )
}
