import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { Layout } from './components/Layout'
import { HomePage } from './pages/HomePage'
import { PokemonDetailPage } from './pages/PokemonDetailPage'
import { FavoritesPage } from './pages/FavoritesPage'
import { TeamPage } from './pages/TeamPage'
import { ComparePage } from './pages/ComparePage'
import { TypeChartPage } from './pages/TypeChartPage'
import { TrainerPage } from './pages/TrainerPage'
import { ArenaPage } from './pages/ArenaPage'

export default function App() {
  return (
    <BrowserRouter basename={import.meta.env.BASE_URL.replace(/\/$/, '')}>
      <Routes>
        <Route element={<Layout />}>
          <Route index element={<HomePage />} />
          <Route path="pokemon/:id" element={<PokemonDetailPage />} />
          <Route path="favorites" element={<FavoritesPage />} />
          <Route path="team" element={<TeamPage />} />
          <Route path="arena" element={<ArenaPage />} />
          <Route path="compare" element={<ComparePage />} />
          <Route path="types" element={<TypeChartPage />} />
          <Route path="trainer" element={<TrainerPage />} />
          <Route path="*" element={<Navigate to="/" replace />} />
        </Route>
      </Routes>
    </BrowserRouter>
  )
}
