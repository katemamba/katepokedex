import './StatBar.css'

const STAT_LABELS: Record<string, string> = {
  hp: 'HP',
  attack: 'Attack',
  defense: 'Defense',
  'special-attack': 'Sp. Atk',
  'special-defense': 'Sp. Def',
  speed: 'Speed',
}

interface Props {
  name: string
  value: number
  max?: number
}

export function StatBar({ name, value, max = 255 }: Props) {
  const pct = Math.min(100, (value / max) * 100)
  const tone =
    value >= 120 ? 'high' : value >= 80 ? 'mid' : value >= 50 ? 'ok' : 'low'

  return (
    <div className="stat-bar">
      <div className="stat-bar__meta">
        <span>{STAT_LABELS[name] ?? name}</span>
        <strong>{value}</strong>
      </div>
      <div className="stat-bar__track">
        <div
          className={`stat-bar__fill stat-bar__fill--${tone}`}
          style={{ width: `${pct}%` }}
        />
      </div>
    </div>
  )
}
