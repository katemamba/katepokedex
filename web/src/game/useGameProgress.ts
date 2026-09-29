import { useCallback, useEffect, useState } from 'react'
import { config } from '../config'
import { defaultBag } from './items'
import { syncFightersWithTeam, applyXp } from './battleMath'
import type { FighterProgress, GameProgress, ItemId } from './types'
import { useTeam } from '../hooks/useStorage'

const emptyProgress = (): GameProgress => ({
  fighters: [],
  bag: defaultBag(),
  eliteFourIndex: 0,
  trainingWins: 0,
  championBeaten: false,
})

function readProgress(): GameProgress {
  try {
    const raw = localStorage.getItem(config.storageKeys.game)
    if (!raw) return emptyProgress()
    const parsed = JSON.parse(raw) as GameProgress
    return {
      ...emptyProgress(),
      ...parsed,
      bag: { ...defaultBag(), ...parsed.bag },
      fighters: Array.isArray(parsed.fighters) ? parsed.fighters : [],
    }
  } catch {
    return emptyProgress()
  }
}

function writeProgress(progress: GameProgress) {
  localStorage.setItem(config.storageKeys.game, JSON.stringify(progress))
}

export function useGameProgress() {
  const team = useTeam()
  const [progress, setProgress] = useState<GameProgress>(() => readProgress())

  useEffect(() => {
    setProgress((prev) => {
      const fighters = syncFightersWithTeam(team.ids, prev.fighters)
      const unchanged =
        fighters.length === prev.fighters.length &&
        fighters.every(
          (f, i) =>
            f.pokemonId === prev.fighters[i]?.pokemonId &&
            f.level === prev.fighters[i]?.level &&
            f.xp === prev.fighters[i]?.xp,
        )
      if (unchanged) return prev
      const next = { ...prev, fighters }
      writeProgress(next)
      return next
    })
  }, [team.ids])

  const persist = useCallback((next: GameProgress) => {
    writeProgress(next)
    setProgress(next)
  }, [])

  const getFighter = useCallback(
    (pokemonId: number): FighterProgress => {
      return (
        progress.fighters.find((f) => f.pokemonId === pokemonId) ?? {
          pokemonId,
          level: 12,
          xp: 0,
        }
      )
    },
    [progress.fighters],
  )

  const updateFighters = useCallback(
    (fighters: FighterProgress[]) => {
      const current = readProgress()
      persist({ ...current, fighters })
    },
    [persist],
  )

  const gainXpFor = useCallback(
    (pokemonId: number, amount: number) => {
      const current = readProgress()
      const fighters = current.fighters.map((f) => {
        if (f.pokemonId !== pokemonId) return f
        return applyXp(f, amount).fighter
      })
      const leveled =
        applyXp(
          current.fighters.find((f) => f.pokemonId === pokemonId) ?? {
            pokemonId,
            level: 12,
            xp: 0,
          },
          amount,
        ).leveled
      persist({ ...current, fighters })
      return leveled
    },
    [persist],
  )

  const consumeItem = useCallback(
    (id: ItemId): boolean => {
      const current = readProgress()
      const count = current.bag[id] ?? 0
      if (count <= 0) return false
      persist({
        ...current,
        bag: { ...current.bag, [id]: count - 1 },
      })
      return true
    },
    [persist],
  )

  const addItems = useCallback(
    (gains: Partial<Record<ItemId, number>>) => {
      const current = readProgress()
      const bag = { ...current.bag }
      for (const [key, amount] of Object.entries(gains)) {
        const id = key as ItemId
        bag[id] = (bag[id] ?? 0) + (amount ?? 0)
      }
      persist({ ...current, bag })
    },
    [persist],
  )

  const recordTrainingWin = useCallback(() => {
    const current = readProgress()
    persist({ ...current, trainingWins: current.trainingWins + 1 })
  }, [persist])

  const advanceEliteFour = useCallback(() => {
    const current = readProgress()
    const nextIndex = Math.min(5, current.eliteFourIndex + 1)
    persist({
      ...current,
      eliteFourIndex: nextIndex,
      championBeaten: nextIndex >= 5 ? true : current.championBeaten,
    })
  }, [persist])

  const resetEliteFour = useCallback(() => {
    const current = readProgress()
    persist({
      ...current,
      eliteFourIndex: 0,
      championBeaten: false,
    })
  }, [persist])

  const resetBag = useCallback(() => {
    const current = readProgress()
    persist({ ...current, bag: defaultBag() })
  }, [persist])

  return {
    progress,
    getFighter,
    updateFighters,
    gainXpFor,
    consumeItem,
    addItems,
    recordTrainingWin,
    advanceEliteFour,
    resetEliteFour,
    resetBag,
    team,
  }
}
