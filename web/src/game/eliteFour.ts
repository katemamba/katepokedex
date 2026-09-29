export interface EliteMember {
  id: string
  name: string
  title: string
  flavor: string
  levelBoost: number
  team: number[]
}

/** Indigo-inspired Elite Four + Champion (simplified fan roster). */
export const ELITE_FOUR: EliteMember[] = [
  {
    id: 'lorelei',
    name: 'Lorelei',
    title: 'Elite Four',
    flavor: 'Ice-type specialist. Freeze them out.',
    levelBoost: 0,
    team: [131, 91, 80, 124, 87],
  },
  {
    id: 'bruno',
    name: 'Bruno',
    title: 'Elite Four',
    flavor: 'Fighting powerhouse. Hit hard or fall.',
    levelBoost: 2,
    team: [95, 107, 106, 68, 208],
  },
  {
    id: 'agatha',
    name: 'Agatha',
    title: 'Elite Four',
    flavor: 'Ghost tricks from the shadows.',
    levelBoost: 4,
    team: [94, 42, 93, 24, 200],
  },
  {
    id: 'lance',
    name: 'Lance',
    title: 'Elite Four',
    flavor: 'Dragon master of the Indigo Plateau.',
    levelBoost: 6,
    team: [149, 130, 142, 148, 230],
  },
  {
    id: 'champion',
    name: 'Blue',
    title: 'Champion',
    flavor: 'The final trial. Bring your best six.',
    levelBoost: 10,
    team: [18, 65, 112, 103, 59, 9],
  },
]
