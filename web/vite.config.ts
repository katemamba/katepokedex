import react from '@vitejs/plugin-react'
import { defineConfig } from 'vite'

// Project site: https://katemamba.github.io/katepokedex/
export default defineConfig({
  plugins: [react()],
  base: '/katepokedex/',
})
