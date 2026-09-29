import { Link } from 'react-router-dom'
import type { EvolutionNode } from '../types/pokemon'
import { displayName, padId, spriteUrl, fallbackSpriteUrl } from '../api/pokeapi'
import './EvolutionChain.css'

interface Props {
  root: EvolutionNode
}

function NodeView({ node }: { node: EvolutionNode }) {
  const condition = [
    node.minLevel ? `Lv ${node.minLevel}` : null,
    node.item ? displayName(node.item) : null,
    node.trigger && node.trigger !== 'level-up'
      ? displayName(node.trigger)
      : null,
  ]
    .filter(Boolean)
    .join(' · ')

  return (
    <div className="evo-branch">
      <Link to={`/pokemon/${node.id}`} className="evo-node">
        <img
          src={spriteUrl(node.id)}
          alt={displayName(node.name)}
          onError={(e) => {
            e.currentTarget.src = fallbackSpriteUrl(node.id)
          }}
        />
        <span className="evo-node__id">{padId(node.id)}</span>
        <strong>{displayName(node.name)}</strong>
        {condition ? <em>{condition}</em> : null}
      </Link>
      {node.evolvesTo.length > 0 ? (
        <div className="evo-children">
          {node.evolvesTo.map((child) => (
            <div key={child.id} className="evo-step">
              <span className="evo-arrow" aria-hidden>
                →
              </span>
              <NodeView node={child} />
            </div>
          ))}
        </div>
      ) : null}
    </div>
  )
}

export function EvolutionChain({ root }: Props) {
  return (
    <div className="evo-chain">
      <NodeView node={root} />
    </div>
  )
}
