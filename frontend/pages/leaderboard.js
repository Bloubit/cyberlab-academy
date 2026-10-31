import Head from 'next/head'
import { useState, useEffect } from 'react'
import Link from 'next/link'
import { useAuth } from '../context/AuthContext'
import api from '../lib/api'
import styles from '../styles/Leaderboard.module.css'

const COLORS = ['#00d4ff', '#9945ff', '#ff00ff', '#00ff88', '#ffaa00', '#ff4757']

export default function Leaderboard() {
  const { user, isAuthenticated } = useAuth()
  const [leaders, setLeaders] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [period, setPeriod] = useState('all_time')

  useEffect(() => {
    fetchLeaderboard()
  }, [period])

  const fetchLeaderboard = async () => {
    setLoading(true)
    setError(null)
    try {
      const res = await api.getLeaderboard({ period, limit: 100 })
      if (res.success) {
        setLeaders(res.data || [])
      }
    } catch (err) {
      setError(err.message || 'Failed to load leaderboard')
      console.error('Leaderboard error:', err)
    } finally {
      setLoading(false)
    }
  }

  const currentUserId = user?.id
  const topThree = leaders.slice(0, 3)
  const restLeaders = leaders.slice(3)
  const currentUserEntry = leaders.find(l => l.id === currentUserId)

  return (
    <div className={styles.container}>
      <Head>
        <title>Leaderboard - CyberLab Academy</title>
      </Head>

      <header className={styles.header}>
        <div className={styles.headerContent}>
          <Link href="/" className={styles.logo}>🛡️ CyberLab Academy</Link>
          <nav className={styles.nav}>
            <Link href="/dashboard">Dashboard</Link>
            <Link href="/courses">Courses</Link>
            <Link href="/labs">Labs</Link>
            <Link href="/leaderboard" className={styles.active}>Leaderboard</Link>
          </nav>
          {isAuthenticated ? (
            <Link href="/profile" className={styles.avatar}>
              {user?.name?.charAt(0)?.toUpperCase() || 'U'}
            </Link>
          ) : (
            <div className={styles.authBtns}>
              <Link href="/login" className={styles.loginBtn}>Sign In</Link>
              <Link href="/register" className={styles.signupBtn}>Join</Link>
            </div>
          )}
        </div>
      </header>

      <main className={styles.main}>
        <div className={styles.pageHeader}>
          <h1>🏆 Leaderboard</h1>
          <p>Top cybersecurity learners worldwide</p>
          {error && <div className={styles.errorBanner}>⚠️ {error}</div>}
        </div>

        {loading ? (
          <div className={styles.loading}>
            <div className={styles.spinner} />
            <p className={styles.loadingText}>Loading rankings...</p>
          </div>
        ) : (
          <>
            {/* Podium - Top 3 */}
            {topThree.length > 0 && (
              <div className={styles.podium}>
                {[1, 0, 2].map(orderIdx => {
                  const idx = [1, 0, 2][orderIdx]
                  const u = topThree[idx]
                  if (!u) return null
                  const medals = ['🥇', '🥈', '🥉']
                  const classes = ['gold', 'silver', 'bronze']
                  return (
                    <div key={u.rank} className={styles.podiumItem}>
                      <span className={styles.podiumPosition}>{medals[idx]}</span>
                      <div className={`${styles.podiumAvatar} ${styles[classes[idx]]}`}
                        style={u.avatar ? { backgroundImage: `url(${u.avatar})`, backgroundSize: 'cover' } : 
                          { background: COLORS[idx] || '#00d4ff' }}>
                        {!u.avatar && (u.name?.charAt(0) || '?')}
                      </div>
                      <div className={`${styles.podiumBar} ${styles[classes[idx]]}`}>
                        <span className={styles.podiumName}>{u.name}</span>
                        <span className={styles.podiumXp}>{(u.xp || 0).toLocaleString()} XP</span>
                        <span className={styles.podiumLevel}>Lv.{u.level || 1}</span>
                      </div>
                    </div>
                  )
                })}
              </div>
            )}

            {/* Period Filters */}
            <div className={styles.filters}>
              {[
                { value: 'all_time', label: 'All Time' },
                { value: 'monthly', label: 'Monthly' },
                { value: 'weekly', label: 'Weekly' },
              ].map(p => (
                <button
                  key={p.value}
                  className={`${styles.filterBtn} ${period === p.value ? styles.filterActive : ''}`}
                  onClick={() => setPeriod(p.value)}
                >
                  {p.label}
                </button>
              ))}
            </div>

            {/* Leaderboard List */}
            {leaders.length === 0 ? (
              <div className={styles.emptyState}>
                <span className={styles.emptyIcon}>🏆</span>
                <h3>No rankings yet</h3>
                <p>Start solving challenges to earn XP and climb the leaderboard!</p>
                <Link href="/labs" className={styles.startBtn}>Try a Lab →</Link>
              </div>
            ) : (
              <div className={styles.leaderboard}>
                <div className={styles.listHeader}>
                  <span className={styles.rankCol}>Rank</span>
                  <span className={styles.avatarCol}></span>
                  <span className={styles.nameCol}>User</span>
                  <span className={styles.xpCol}>XP</span>
                  <span className={styles.levelCol}>Level</span>
                  <span className={styles.streakCol}>Streak</span>
                </div>

                {restLeaders.map((u) => {
                  const isCurrentUser = u.id === currentUserId
                  return (
                    <div key={u.rank} className={`${styles.listItem} ${isCurrentUser ? styles.currentUser : ''}`}>
                      <span className={`${styles.rank} ${u.rank <= 3 ? [styles.rankGold, styles.rankSilver, styles.rankBronze][u.rank - 1] : ''}`}>
                        #{u.rank}
                      </span>
                      <div className={styles.userAvatar}
                        style={u.avatar ? { backgroundImage: `url(${u.avatar})`, backgroundSize: 'cover' } : 
                          { background: COLORS[(u.rank - 1) % COLORS.length] }}>
                        {!u.avatar && (u.name?.charAt(0) || '?')}
                      </div>
                      <div className={styles.userInfo}>
                        <div className={styles.userName}>
                          {u.name}
                          {isCurrentUser && <span className={styles.youBadge}>(You)</span>}
                        </div>
                        <div className={styles.userTitle}>@{u.username || 'user'}</div>
                      </div>
                      <span className={styles.xp}>{(u.xp || 0).toLocaleString()} XP</span>
                      <span className={styles.level}>Lv.{u.level || 1}</span>
                      <span className={styles.streak}>
                        {u.streak > 0 ? `🔥 ${u.streak}` : '-'}
                      </span>
                    </div>
                  )
                })}
              </div>
            )}

            {/* Current user position if not in top 100 */}
            {currentUserEntry && currentUserEntry.rank > restLeaders.length + 3 && (
              <div className={styles.currentUserPosition}>
                <p>Your position: #{currentUserEntry.rank} • {(currentUserEntry.xp || 0).toLocaleString()} XP</p>
              </div>
            )}
          </>
        )}
      </main>
    </div>
  )
}
