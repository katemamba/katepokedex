import { useState, type FormEvent } from 'react'
import { useTrainer } from '../hooks/useStorage'
import './pages.css'

export function TrainerPage() {
  const { trainer, setTrainer } = useTrainer()
  const [name, setName] = useState(trainer?.name ?? '')
  const [hometown, setHometown] = useState(trainer?.hometown ?? '')
  const [gender, setGender] = useState(trainer?.gender ?? '')
  const [saved, setSaved] = useState(false)

  function onSubmit(e: FormEvent) {
    e.preventDefault()
    if (!name.trim() || !hometown.trim() || !gender.trim()) return
    setTrainer({
      name: name.trim(),
      hometown: hometown.trim(),
      gender: gender.trim(),
    })
    setSaved(true)
  }

  return (
    <section className="page">
      <header className="section-head">
        <h1>Trainer profile</h1>
        <p className="lede">
          Local trainer card — no server required. Replaces the old MySQL
          registration flow.
        </p>
      </header>

      <form className="panel trainer-form" onSubmit={onSubmit}>
        <label className="field">
          <span>Name</span>
          <input
            value={name}
            onChange={(e) => setName(e.target.value)}
            required
            placeholder="Ash"
          />
        </label>
        <label className="field">
          <span>Hometown</span>
          <input
            value={hometown}
            onChange={(e) => setHometown(e.target.value)}
            required
            placeholder="Pallet Town"
          />
        </label>
        <label className="field">
          <span>Gender / pronouns</span>
          <input
            value={gender}
            onChange={(e) => setGender(e.target.value)}
            required
            placeholder="Any"
          />
        </label>
        <div className="detail-actions">
          <button type="submit" className="btn btn--primary">
            Save trainer
          </button>
          {trainer ? (
            <button
              type="button"
              className="btn btn--ghost"
              onClick={() => {
                setTrainer(null)
                setName('')
                setHometown('')
                setGender('')
                setSaved(false)
              }}
            >
              Clear
            </button>
          ) : null}
        </div>
        {saved ? <p className="status">Profile saved on this device.</p> : null}
      </form>

      {trainer ? (
        <article className="panel trainer-card">
          <h2>{trainer.name}</h2>
          <p>
            From <strong>{trainer.hometown}</strong> · {trainer.gender}
          </p>
        </article>
      ) : null}
    </section>
  )
}
