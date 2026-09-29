import { Link } from 'react-router-dom'
import type { PokemonListItem } from '../types/pokemon'
import { displayName, fallbackSpriteUrl, padId, spriteUrl } from '../api/pokeapi'
import { TypeBadge } from './TypeBadge'
import { useFavorites, useTeam } from '../hooks/useStorage'
import { config } from '../config'
import './PokemonCard.css'

interface Props {
  pokemon: PokemonListItem
}

export function PokemonCard({ pokemon }: Props) {
  const favorites = useFavorites()
  const team = useTeam()
  const fav = favorites.has(pokemon.id)
  const onTeam = team.has(pokemon.id)

  return (
    <article className="poke-card">
      <Link to={`/pokemon/${pokemon.id}`} className="poke-card__link">
        <div className="poke-card__media">
          <img
            src={spriteUrl(pokemon.id)}
            alt={displayName(pokemon.name)}
            loading="lazy"
            onError={(e) => {
              e.currentTarget.src = fallbackSpriteUrl(pokemon.id)
            }}
          />
        </div>
        <p className="poke-card__id">{padId(pokemon.id)}</p>
        <h3 className="poke-card__name">{displayName(pokemon.name)}</h3>
        <div className="poke-card__types">
          {pokemon.types.map((t) => (
            <TypeBadge key={t} type={t} size="sm" />
          ))}
        </div>
      </Link>
      <div className="poke-card__actions">
        <button
          type="button"
          className={fav ? 'is-active' : ''}
          aria-label={fav ? 'Remove favorite' : 'Add favorite'}
          onClick={() => favorites.toggle(pokemon.id)}
        >
          {fav ? '★' : '☆'}
        </button>
        <button
          type="button"
          className={onTeam ? 'is-active' : ''}
          aria-label={onTeam ? 'Remove from team' : 'Add to team'}
          title={
            !onTeam && team.count >= config.maxTeamSize
              ? 'Team is full'
              : undefined
          }
          onClick={() => {
            if (onTeam) {
              team.remove(pokemon.id)
              return
            }
            const ok = team.toggle(pokemon.id)
            if (ok === false) {
              window.alert('Team is full (max 6). Remove one first.')
            }
          }}
        >
          {onTeam ? '●' : '○'}
        </button>
      </div>
    </article>
  )
}
