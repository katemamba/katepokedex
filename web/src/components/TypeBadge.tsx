import type { PokemonTypeName } from '../types/pokemon'
import { TYPE_COLORS } from '../data/typeChart'
import { displayName } from '../api/pokeapi'
import './TypeBadge.css'

interface Props {
  type: PokemonTypeName
  size?: 'sm' | 'md'
}

export function TypeBadge({ type, size = 'md' }: Props) {
  return (
    <span
      className={`type-badge type-badge--${size}`}
      style={{ backgroundColor: TYPE_COLORS[type] }}
    >
      {displayName(type)}
    </span>
  )
}
