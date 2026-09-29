import { ALL_TYPES, getMultiplier, TYPE_COLORS } from '../data/typeChart'
import { displayName } from '../api/pokeapi'
import './pages.css'

export function TypeChartPage() {
  return (
    <section className="page">
      <header className="section-head">
        <h1>Type chart</h1>
        <p className="lede">
          Attacking type across the top-left labels; defending type across the
          columns. ×2 super effective, ×½ resisted, ×0 immune.
        </p>
      </header>

      <div className="type-chart-wrap panel">
        <table className="type-chart">
          <thead>
            <tr>
              <th scope="col">Atk \\ Def</th>
              {ALL_TYPES.map((t) => (
                <th key={t} scope="col" title={t}>
                  <span
                    className="type-chart__chip"
                    style={{ background: TYPE_COLORS[t] }}
                  >
                    {t.slice(0, 3)}
                  </span>
                </th>
              ))}
            </tr>
          </thead>
          <tbody>
            {ALL_TYPES.map((atk) => (
              <tr key={atk}>
                <th scope="row">
                  <span
                    className="type-chart__chip"
                    style={{ background: TYPE_COLORS[atk] }}
                  >
                    {displayName(atk)}
                  </span>
                </th>
                {ALL_TYPES.map((def) => {
                  const m = getMultiplier(atk, def)
                  return (
                    <td
                      key={def}
                      className={`type-chart__cell type-chart__cell--x${String(m).replace('.', '_')}`}
                    >
                      {m === 1 ? '' : m}
                    </td>
                  )
                })}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </section>
  )
}
