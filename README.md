# Kate Pokédex

Modern Pokédex for browsing, searching, and building teams across **nine generations** of Pokémon. Data comes from [PokéAPI](https://pokeapi.co) — no API key required.

## What this repo was

The original project was a student-era PHP + MySQL trainer login with a static Gen I–VI Pokédex HTML dump (`kateindex.html`), plus unrelated XAMPP dashboard and dating-site leftovers. That code still lives at the repo root / `pokemon reg` / `dashboard` as legacy reference.

## What’s new (`web/`)

A Vite + React + TypeScript app with:

- National Dex browse (1–1025) with official artwork
- Search by name or number
- Filter by type and generation (Kanto → Paldea)
- Detail pages: stats, abilities, height/weight, cries, flavor text, evolution chain, type defense, moves
- Favorites and 6-Pokémon team builder with reorder (localStorage)
- **Battle Arena** minigame: turn-based training battles + Elite Four gauntlet with bag items
- Side-by-side compare
- Full type effectiveness chart
- Local trainer profile (replaces the old MySQL registration)

## Run locally

```bash
cd web
npm install
npm run dev
```

Open the URL Vite prints (usually `http://localhost:5173`).

Production build:

```bash
cd web
npm run build
npm run preview
```

## Configuration

Edit `web/src/config.ts` for API base URL, sprite URLs, dex count, team size, and storage keys.

## Deploy

### GitHub Pages (this repo)

Live site after Actions deploy:

**https://katemamba.github.io/katepokedex/**

Pushes to `master` build `web/` and publish via `.github/workflows/deploy-pages.yml`.

In the repo: **Settings → Pages → Source: GitHub Actions**.

### Other hosts

Build output is `web/dist`. For Vercel/Netlify set root directory to `web`.

## Notes

- First visit builds a local type index (cached in `localStorage`) so filters stay fast.
- Fan project — not affiliated with Nintendo / The Pokémon Company.
