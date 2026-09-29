import { NavLink, Outlet } from 'react-router-dom'
import { useFavorites, useTeam, useTrainer } from '../hooks/useStorage'
import './Layout.css'

const links = [
  { to: '/', label: 'Pokédex', end: true },
  { to: '/favorites', label: 'Favorites' },
  { to: '/team', label: 'Team' },
  { to: '/arena', label: 'Arena' },
  { to: '/compare', label: 'Compare' },
  { to: '/types', label: 'Type Chart' },
  { to: '/trainer', label: 'Trainer' },
]

export function Layout() {
  const favorites = useFavorites()
  const team = useTeam()
  const { trainer } = useTrainer()

  return (
    <div className="app-shell">
      <div className="atmosphere" aria-hidden />
      <header className="site-header">
        <div className="site-header__brand">
          <NavLink to="/" className="brand">
            <span className="brand__mark" aria-hidden />
            <span className="brand__text">
              <strong>Kate</strong> Pokédex
            </span>
          </NavLink>
          {trainer ? (
            <p className="trainer-chip">Trainer {trainer.name}</p>
          ) : null}
        </div>
        <nav className="site-nav" aria-label="Primary">
          {links.map((link) => (
            <NavLink
              key={link.to}
              to={link.to}
              end={link.end}
              className={({ isActive }) =>
                isActive ? 'site-nav__link is-active' : 'site-nav__link'
              }
            >
              {link.label}
              {link.to === '/favorites' && favorites.count > 0
                ? ` (${favorites.count})`
                : ''}
              {link.to === '/team' && team.count > 0
                ? ` (${team.count}/6)`
                : ''}
            </NavLink>
          ))}
        </nav>
      </header>
      <main className="site-main">
        <Outlet />
      </main>
      <footer className="site-footer">
        <p>Kate Pokédex · Fan project, not affiliated with Nintendo.</p>
      </footer>
    </div>
  )
}
