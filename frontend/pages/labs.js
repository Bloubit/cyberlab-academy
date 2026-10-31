import Head from 'next/head'
import Link from 'next/link'
import { useState, useEffect } from 'react'
import Navbar from '../components/Navbar'
import { useAuth } from '../context/AuthContext'
import api from '../lib/api'
import styles from '../styles/Labs.module.css'

const LAB_DIFFICULTY_MAP = {
  beginner: { label: 'Beginner', color: '#00ff88' },
  easy: { label: 'Easy', color: '#00ff88' },
  intermediate: { label: 'Intermediate', color: '#00d4ff' },
  medium: { label: 'Medium', color: '#00d4ff' },
  advanced: { label: 'Advanced', color: '#ffaa00' },
  hard: { label: 'Hard', color: '#ffaa00' },
  expert: { label: 'Expert', color: '#ff4757' },
  insane: { label: 'Insane', color: '#ff4757' },
}

const LAB_ICONS = ['💻', '🖥️', '🔬', '🧪', '🌐', '🔍', '🛡️', '⚔️']

export default function Labs() {
  const { isAuthenticated, loading: authLoading } = useAuth()
  const [labs, setLabs] = useState([])
  const [loading, setLoading] = useState(true)
  const [filter, setFilter] = useState('all')

  useEffect(() => {
    fetchLabs()
  }, [filter])

  const fetchLabs = async () => {
    setLoading(true)
    try {
      const params = {}
      if (filter !== 'all') {
        params.difficulty_level = filter
      }
      const res = await api.getLabs(params)
      if (res.success) {
        setLabs(res.data?.data || res.data || [])
      } else {
        setLabs([])
      }
    } catch (err) {
      console.error('Failed to fetch labs:', err)
    } finally {
      setLoading(false)
    }
  }

  const getBadgeClass = (level) => {
    const map = {
      beginner: styles.badgeBeginner,
      easy: styles.badgeBeginner,
      intermediate: styles.badgeIntermediate,
      medium: styles.badgeIntermediate,
      advanced: styles.badgeAdvanced,
      hard: styles.badgeAdvanced,
      expert: styles.badgeExpert,
      insane: styles.badgeExpert,
    }
    return map[level] || styles.badgeBeginner
  }

  const getRandomIcon = (id) => LAB_ICONS[id % LAB_ICONS.length]

  if (authLoading) {
    return (
      <div className={styles.container}>
        <Navbar />
        <div className={styles.loading}>
          <div className={styles.spinner} />
        </div>
      </div>
    )
  }

  if (!isAuthenticated) {
    return (
      <div className={styles.container}>
        <Navbar />
        <main className={styles.main}>
          <div className={styles.pageHeader}>
            <h1>Cybersecurity Labs</h1>
            <p>Sign in to access hands-on lab environments</p>
          </div>
          <div className={styles.authPrompt}>
            <span className={styles.authIcon}>🔒</span>
            <h2>Authentication Required</h2>
            <p>Please sign in or create an account to access practice labs.</p>
            <div className={styles.authButtons}>
              <Link href="/login" className={styles.loginBtn}>Sign In</Link>
              <Link href="/register" className={styles.signupBtn}>Create Account</Link>
            </div>
          </div>
        </main>
      </div>
    )
  }

  if (loading) {
    return (
      <div className={styles.container}>
        <Navbar />
        <div className={styles.loading}>
          <div className={styles.spinner} />
        </div>
      </div>
    )
  }

  return (
    <div className={styles.container}>
      <Head>
        <title>Labs - CyberLab Academy</title>
      </Head>
      <Navbar />

      <main className={styles.main}>
        <div className={styles.pageHeader}>
          <h1>Cybersecurity Labs</h1>
          <p>Practice your skills in real virtual environments</p>
        </div>

        <div className={styles.filters}>
          {['all', 'beginner', 'intermediate', 'advanced', 'expert'].map(f => (
            <button
              key={f}
              className={`${styles.filterBtn} ${filter === f ? styles.filterActive : ''}`}
              onClick={() => setFilter(f)}
            >
              {f === 'all' ? 'All' : (LAB_DIFFICULTY_MAP[f]?.label || f.charAt(0).toUpperCase() + f.slice(1))}
            </button>
          ))}
        </div>

        <div className={styles.labsGrid}>
          {labs.map((lab, idx) => (
            <div key={lab.id} className={styles.labCard}>
              <div className={styles.labHeader}>
                <span className={styles.labIcon}>{getRandomIcon(lab.id)}</span>
              </div>
              <div className={styles.labBody}>
                <div className={styles.labMeta}>
                  <span className={`${styles.badge} ${getBadgeClass(lab.difficulty_level || lab.difficulty)}`}
                    style={{ border: `1px solid ${LAB_DIFFICULTY_MAP[lab.difficulty_level || lab.difficulty]?.color || '#a0aec0'}` }}>
                    {LAB_DIFFICULTY_MAP[lab.difficulty_level || lab.difficulty]?.label || lab.difficulty_level || lab.difficulty}
                  </span>
                  {(lab.duration_minutes || lab.estimated_time_minutes) && (
                    <span className={styles.badge} style={{ background: 'rgba(255,255,255,0.1)', color: '#a0aec0' }}>
                      ⏱ {lab.duration_minutes || lab.estimated_time_minutes} min
                    </span>
                  )}
                </div>
                <h3 className={styles.labTitle}>{lab.title}</h3>
                <p className={styles.labDescription}>{lab.description}</p>
                <div className={styles.labStats}>
                  <span>⭐ {lab.xp_reward || lab.points || 0} XP</span>
                </div>
                <div className={styles.labActions}>
                  <button className={styles.startBtn}>Start Lab</button>
                  <a href={`/labs/${lab.id}`} className={styles.detailBtn}>Details</a>
                </div>
              </div>
            </div>
          ))}
        </div>
      </main>
    </div>
  )
}
