import { useCallback, useEffect, useRef, useState, useSyncExternalStore } from 'react'
import { config } from '../config'

const cache = new Map<string, number[]>()

function readList(key: string): number[] {
  if (cache.has(key)) return cache.get(key)!
  try {
    const raw = localStorage.getItem(key)
    if (!raw) {
      cache.set(key, [])
      return cache.get(key)!
    }
    const parsed = JSON.parse(raw) as unknown
    const list = Array.isArray(parsed)
      ? parsed.filter((n): n is number => typeof n === 'number')
      : []
    cache.set(key, list)
    return list
  } catch {
    cache.set(key, [])
    return cache.get(key)!
  }
}

function writeList(key: string, ids: number[]) {
  cache.set(key, ids)
  localStorage.setItem(key, JSON.stringify(ids))
  window.dispatchEvent(new Event(`katepokedex-storage:${key}`))
}

function subscribe(key: string, onStoreChange: () => void) {
  const handler = () => {
    cache.delete(key)
    onStoreChange()
  }
  window.addEventListener(`katepokedex-storage:${key}`, handler)
  window.addEventListener('storage', handler)
  return () => {
    window.removeEventListener(`katepokedex-storage:${key}`, handler)
    window.removeEventListener('storage', handler)
  }
}

function useIdList(key: string, max?: number) {
  const ids = useSyncExternalStore(
    (cb) => subscribe(key, cb),
    () => readList(key),
    () => [] as number[],
  )

  const toggle = useCallback(
    (id: number) => {
      const current = readList(key)
      if (current.includes(id)) {
        writeList(
          key,
          current.filter((x) => x !== id),
        )
        return true
      }
      if (max && current.length >= max) return false
      writeList(key, [...current, id])
      return true
    },
    [key, max],
  )

  const has = useCallback((id: number) => ids.includes(id), [ids])

  const clear = useCallback(() => writeList(key, []), [key])

  const remove = useCallback(
    (id: number) => {
      writeList(
        key,
        readList(key).filter((x) => x !== id),
      )
    },
    [key],
  )

  return { ids, toggle, has, clear, remove, count: ids.length }
}

export function useFavorites() {
  return useIdList(config.storageKeys.favorites)
}

export function useTeam() {
  const base = useIdList(config.storageKeys.team, config.maxTeamSize)

  const move = useCallback(
    (id: number, direction: -1 | 1) => {
      const current = readList(config.storageKeys.team)
      const index = current.indexOf(id)
      if (index < 0) return
      const nextIndex = index + direction
      if (nextIndex < 0 || nextIndex >= current.length) return
      const copy = [...current]
      const [item] = copy.splice(index, 1)
      copy.splice(nextIndex, 0, item)
      writeList(config.storageKeys.team, copy)
    },
    [],
  )

  const setOrder = useCallback((ids: number[]) => {
    writeList(
      config.storageKeys.team,
      ids.slice(0, config.maxTeamSize),
    )
  }, [])

  return { ...base, move, setOrder }
}

export function useTrainer() {
  const [trainer, setTrainerState] = useState(() => {
    try {
      const raw = localStorage.getItem(config.storageKeys.trainer)
      return raw
        ? (JSON.parse(raw) as {
            name: string
            hometown: string
            gender: string
          })
        : null
    } catch {
      return null
    }
  })

  const hydrated = useRef(false)
  useEffect(() => {
    if (!hydrated.current) {
      hydrated.current = true
      return
    }
    if (trainer) {
      localStorage.setItem(config.storageKeys.trainer, JSON.stringify(trainer))
    }
  }, [trainer])

  const setTrainer = useCallback(
    (next: { name: string; hometown: string; gender: string } | null) => {
      if (!next) {
        localStorage.removeItem(config.storageKeys.trainer)
        setTrainerState(null)
        return
      }
      setTrainerState(next)
    },
    [],
  )

  return { trainer, setTrainer }
}
