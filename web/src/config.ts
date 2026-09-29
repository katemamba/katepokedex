/** App configuration — swap endpoints or limits here, no secrets required. */
export const config = {
  pokeApiBase: 'https://pokeapi.co/api/v2',
  spriteBase:
    'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork',
  spriteFallback:
    'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon',
  /** National Dex through Gen 9 (Paldea + DLC). */
  nationalDexCount: 1025,
  maxTeamSize: 6,
  storageKeys: {
    favorites: 'katepokedex:favorites',
    team: 'katepokedex:team',
    trainer: 'katepokedex:trainer',
    typeIndex: 'katepokedex:type-index-v1',
    game: 'katepokedex:game-v1',
  },
} as const
