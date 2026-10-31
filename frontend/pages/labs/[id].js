import Head from 'next/head'
import { useRouter } from 'next/router'
import { useState, useEffect, useCallback } from 'react'
import Link from 'next/link'
import { useAuth } from '../../context/AuthContext'
import api from '../../lib/api'
import styles from '../../styles/LabDetail.module.css'

const DIFFICULTY_COLORS = {
  beginner: '#00ff88',
  easy: '#00ff88',
  intermediate: '#00d4ff',
  medium: '#00d4ff',
  advanced: '#ffaa00',
  hard: '#ffaa00',
  expert: '#ff4757',
  insane: '#ff4757',
}

const LAB_ICONS = {
  challenge: '🏴',
  single_machine: '💻',
  multi_machine: '🌐',
  sandbox: '🖥️',
  ctf: '🏆',
  practice: '🎯',
}

export default function LabDetail() {
  const router = useRouter()
  const { id } = router.query
  const { user, isAuthenticated, loading: authLoading } = useAuth()

  const [lab, setLab] = useState(null)
  const [instance, setInstance] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [starting, setStarting] = useState(false)
  const [stopping, setStopping] = useState(false)
  const [resetting, setResetting] = useState(false)
  const [flagInputs, setFlagInputs] = useState({})
  const [submitting, setSubmitting] = useState(null)
  const [feedback, setFeedback] = useState(null)
  const [solvedChallenges, setSolvedChallenges] = useState(new Set())
  const [timeRemaining, setTimeRemaining] = useState(null)

  const fetchLab = useCallback(async () => {
    if (!id) return
    setLoading(true)
    try {
      const [labRes, statusRes] = await Promise.allSettled([
        api.getLab(id),
        isAuthenticated ? api.getLabInstanceStatus(id) : Promise.resolve(null),
      ])

      if (labRes.status === 'fulfilled' && labRes.value?.success) {
        setLab(labRes.value.data)
      } else {
        setError('Failed to load lab')
      }

      // Check instance status via dedicated endpoint
      if (statusRes.status === 'fulfilled' && statusRes.value?.success) {
        const instanceData = statusRes.value.data
        if (instanceData) {
          setInstance(instanceData)
          if (instanceData.solved_challenges?.length) {
            setSolvedChallenges(new Set(instanceData.solved_challenges))
          }
        }
      }
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }, [id, isAuthenticated])

  useEffect(() => {
    if (!authLoading) fetchLab()
  }, [authLoading, fetchLab])

  // Auto-poll instance status every 30 seconds when running
  useEffect(() => {
    if (!isAuthenticated || !instance || instance.status !== 'running') return

    const pollInterval = setInterval(async () => {
      try {
        const res = await api.getLabInstanceStatus(id)
        if (res.success && res.data) {
          if (res.data.status !== instance.status) {
            setInstance(res.data)
            if (res.data.status === 'expired') {
              setFeedback({ type: 'info', message: '⏰ Lab session expired. Start a new instance to continue.' })
              setTimeout(() => setFeedback(null), 6000)
            }
          } else {
            setInstance(res.data)
          }
          if (res.data.solved_challenges?.length) {
            setSolvedChallenges(new Set(res.data.solved_challenges))
          }
        } else {
          setInstance(null)
        }
      } catch (err) {
        // Silently fail on poll errors
      }
    }, 30000)

    return () => clearInterval(pollInterval)
  }, [id, isAuthenticated, instance?.status])

  // Live countdown timer for running instances
  useEffect(() => {
    if (!instance || instance.status !== 'running' || !instance.expires_at) return

    const updateTimer = () => {
      const now = new Date()
      const expires = new Date(instance.expires_at)
      const diff = expires - now
      if (diff <= 0) {
        setTimeRemaining('Expired')
        return
      }
      const hours = Math.floor(diff / 3600000)
      const minutes = Math.floor((diff % 3600000) / 60000)
      const seconds = Math.floor((diff % 60000) / 1000)
      setTimeRemaining(`${hours}h ${minutes}m ${seconds}s`)
    }

    updateTimer()
    const timer = setInterval(updateTimer, 1000)
    return () => clearInterval(timer)
  }, [instance?.expires_at, instance?.status])

  const handleStart = async () => {
    setStarting(true)
    setError(null)
    try {
      const res = await api.startLab(id)
      if (res.success) {
        setInstance(res.data)
        setFeedback({ type: 'success', message: 'Lab started! Your virtual environment is ready.' })
        setTimeout(() => setFeedback(null), 5000)
      }
    } catch (err) {
      setError(err.message || 'Failed to start lab')
    } finally {
      setStarting(false)
    }
  }

  const handleStop = async () => {
    setStopping(true)
    setError(null)
    try {
      const res = await api.stopLab(id)
      if (res.success) {
        setInstance(null)
        setFeedback({ type: 'info', message: 'Lab stopped.' })
        setTimeout(() => setFeedback(null), 3000)
      }
    } catch (err) {
      setError(err.message || 'Failed to stop lab')
    } finally {
      setStopping(false)
    }
  }

  const handleReset = async () => {
    if (!confirm('Reset lab environment? You will lose any unsaved progress on the VM.')) return
    setResetting(true)
    setError(null)
    try {
      const res = await api.resetLab(id)
      if (res.success) {
        if (res.data) setInstance(res.data)
        setFeedback({ type: 'info', message: '🔄 Lab environment reset successfully!' })
        setTimeout(() => setFeedback(null), 4000)
      }
    } catch (err) {
      setError(err.message || 'Failed to reset lab')
    } finally {
      setResetting(false)
    }
  }

  const handleSubmitFlag = async (challengeId) => {
    const flag = flagInputs[challengeId]
    if (!flag) return

    setSubmitting(challengeId)
    setError(null)
    try {
      const res = await api.submitFlag(id, challengeId, flag)
      if (res.success) {
        setSolvedChallenges(prev => new Set([...prev, challengeId]))
        setFlagInputs(prev => ({ ...prev, [challengeId]: '' }))
        setFeedback({ type: 'success', message: '🎉 Correct flag! +XP earned!' })
      } else {
        setFeedback({ type: 'error', message: res.message || 'Incorrect flag' })
      }
      setTimeout(() => setFeedback(null), 4000)
    } catch (err) {
      setError(err.message || 'Failed to submit flag')
    } finally {
      setSubmitting(null)
    }
  }

  const isRunning = instance?.status === 'running'
  const isCompleted = instance?.status === 'completed'

  if (authLoading || loading) {
    return (
      <div className={styles.container}>
        <header className={styles.header}>
          <div className={styles.headerContent}>
            <Link href="/" className={styles.logo}>🛡️ CyberLab</Link>
            <nav className={styles.nav}>
              <Link href="/courses">Courses</Link>
              <Link href="/labs">Labs</Link>
            </nav>
          </div>
        </header>
        <div className={styles.loading}><div className={styles.spinner}></div></div>
      </div>
    )
  }

  if (!lab) {
    return (
      <div className={styles.container}>
        <header className={styles.header}>
          <div className={styles.headerContent}>
            <Link href="/" className={styles.logo}>🛡️ CyberLab</Link>
          </div>
        </header>
        <div className={styles.errorState}>
          <div className={styles.errorIcon}>🔍</div>
          <h2>Lab Not Found</h2>
          <p>{error || 'This lab does not exist or has been removed.'}</p>
          <Link href="/labs" className={styles.backBtn}>← Back to Labs</Link>
        </div>
      </div>
    )
  }

  const challenges = lab.challenges?.filter(c => c.is_active !== false) || []
  const solvedCount = challenges.filter(c => solvedChallenges.has(c.id)).length

  return (
    <div className={styles.container}>
      <Head>
        <title>{lab.title} - CyberLab Academy</title>
      </Head>

      <header className={styles.header}>
        <div className={styles.headerContent}>
          <Link href="/" className={styles.logo}>🛡️ CyberLab</Link>
          <nav className={styles.nav}>
            <Link href="/courses">Courses</Link>
            <Link href="/labs">Labs</Link>
            <Link href="/leaderboard">Leaderboard</Link>
          </nav>
          {isAuthenticated ? (
            <div className={styles.userMenu}>
              <Link href="/profile" className={styles.avatar}>
                {user?.name?.charAt(0)?.toUpperCase() || 'U'}
              </Link>
            </div>
          ) : (
            <Link href="/login" className={styles.loginBtn}>Sign In</Link>
          )}
        </div>
      </header>

      <main className={styles.main}>
        {feedback && (
          <div className={`${styles.feedback} ${styles[feedback.type]}`}>
            {feedback.message}
          </div>
        )}

        {error && (
          <div className={styles.errorBanner}>
            ⚠️ {error}
            <button onClick={() => setError(null)} className={styles.dismissBtn}>×</button>
          </div>
        )}

        {/* Lab Header */}
        <div className={styles.labHeader}>
          <div className={styles.labMeta}>
            <Link href="/labs" className={styles.breadcrumb}>← Labs</Link>
            <div className={styles.labType}>{(lab.type || 'challenge').toUpperCase()}</div>
          </div>
          <div className={styles.labTitleRow}>
            <span className={styles.labIcon}>{LAB_ICONS[lab.type || lab.lab_type] || '💻'}</span>
            <div>
              <h1 className={styles.labTitle}>{lab.title}</h1>
              <div className={styles.labStats}>
                <span style={{ color: DIFFICULTY_COLORS[lab.difficulty_level] || '#a0aec0', fontWeight: 600 }}>
                  {lab.difficulty_level}
                </span>
                <span>⏱ {lab.duration_minutes || '?'} min</span>
                <span>⭐ {lab.xp_reward || 0} XP</span>
              </div>
            </div>
          </div>
        </div>

        {/* Lab Content Grid */}
        <div className={styles.contentGrid}>
          {/* Left - Lab Info & Challenges */}
          <div className={styles.leftCol}>
            {/* Description */}
            <div className={styles.card}>
              <h2 className={styles.cardTitle}>Description</h2>
              <p className={styles.description}>{lab.description || 'No description available.'}</p>

              {lab.learning_objectives && typeof lab.learning_objectives === 'string' && (
                <>
                  <h3 className={styles.cardSubtitle}>Learning Objectives</h3>
                  <ul className={styles.objectives}>
                    {lab.learning_objectives.split('\n').filter(Boolean).map((obj, i) => (
                      <li key={i}>{obj}</li>
                    ))}
                  </ul>
                </>
              )}
              {lab.learning_objectives && Array.isArray(lab.learning_objectives) && (
                <>
                  <h3 className={styles.cardSubtitle}>Learning Objectives</h3>
                  <ul className={styles.objectives}>
                    {lab.learning_objectives.map((obj, i) => (
                      <li key={i}>{obj}</li>
                    ))}
                  </ul>
                </>
              )}

              {lab.hints && typeof lab.hints === 'string' && (
                <>
                  <h3 className={styles.cardSubtitle}>Hints</h3>
                  <div className={styles.tools}>
                    {lab.hints.split('\n').filter(Boolean).map((hint, i) => (
                      <span key={i} className={styles.toolTag}>{hint}</span>
                    ))}
                  </div>
                </>
              )}
            </div>

            {/* Challenges */}
            <div className={styles.card}>
              <div className={styles.challengeHeader}>
                <h2 className={styles.cardTitle}>
                  Challenges ({solvedCount}/{challenges.length})
                </h2>
                {isRunning && (
                  <span className={styles.liveBadge}>🟢 LIVE</span>
                )}
              </div>
              {challenges.length === 0 ? (
                <p className={styles.noChallenges}>No challenges configured for this lab.</p>
              ) : (
                <div className={styles.challengeList}>
                  {challenges.map(ch => {
                    const solved = solvedChallenges.has(ch.id)
                    return (
                      <div key={ch.id} className={`${styles.challenge} ${solved ? styles.solved : ''}`}>
                        <div className={styles.challengeTop}>
                          <span className={styles.challengeIcon}>{solved ? '✅' : '🏴'}</span>
                          <div className={styles.challengeInfo}>
                            <h4 className={styles.challengeTitle}>{ch.title}</h4>
                            <div className={styles.challengeMeta}>
                              <span style={{ color: '#a0aec0', fontSize: '0.75rem' }}>
                                Challenge
                              </span>
                              <span style={{ color: '#ffaa00', fontWeight: 600 }}>+{ch.points || 0} XP</span>
                            </div>
                          </div>
                          <span className={styles.pointsBadge}>{ch.points || 0}pts</span>
                        </div>
                        {ch.description && (
                          <p className={styles.challengeDesc}>{ch.description}</p>
                        )}
                        {isRunning && !solved && (
                          <div className={styles.flagInput}>
                            <input
                              type="text"
                              className={styles.flagField}
                              placeholder="FLAG{...}"
                              value={flagInputs[ch.id] || ''}
                              onChange={e => setFlagInputs(prev => ({ ...prev, [ch.id]: e.target.value }))}
                              onKeyDown={e => e.key === 'Enter' && handleSubmitFlag(ch.id)}
                            />
                            <button
                              className={styles.submitBtn}
                              onClick={() => handleSubmitFlag(ch.id)}
                              disabled={submitting === ch.id || !flagInputs[ch.id]}
                            >
                              {submitting === ch.id ? '...' : 'Submit'}
                            </button>
                          </div>
                        )}
                        {solved && (
                          <div className={styles.solvedBadge}>✅ Solved! +{ch.points || 0} XP earned</div>
                        )}
                      </div>
                    )
                  })}
                </div>
              )}
            </div>
          </div>

          {/* Right - Lab Console / Instance */}
          <div className={styles.rightCol}>
            <div className={styles.card}>
              <h2 className={styles.cardTitle}>Lab Environment</h2>

              {!isAuthenticated ? (
                <div className={styles.authRequired}>
                  <p>Sign in to access this lab environment.</p>
                  <Link href="/login" className={styles.primaryBtn}>Sign In</Link>
                </div>
              ) : isRunning ? (
                <div className={styles.console}>
                  <div className={styles.consoleHeader}>
                    <span className={styles.statusDot}>🟢</span>
                    <span>Running</span>
                    <span className={styles.consoleTime}>
                      {timeRemaining ? `⏱ ${timeRemaining}` : ''}
                    </span>
                  </div>

                  {instance.connection_info && (
                    <div className={styles.connectionInfo}>
                      <h4>Connection Details</h4>
                      {instance.connection_info.access_url && (
                        <div className={styles.connectionRow}>
                          <span className={styles.connLabel}>Access URL</span>
                          <a href={instance.connection_info.access_url} target="_blank" rel="noopener noreferrer" className={styles.connValue}>
                            {instance.connection_info.access_url}
                          </a>
                        </div>
                      )}
                      {instance.connection_info.machines && instance.connection_info.machines.length > 0 ? (
                        instance.connection_info.machines.map((machine, idx) => (
                          <div key={idx} className={styles.machineInfo}>
                            <h5 className={styles.machineName}>{machine.name || `Machine ${idx + 1}`}</h5>
                            <div className={styles.connectionRow}>
                              <span className={styles.connLabel}>IP Address</span>
                              <code className={styles.connValue}>{machine.ip || 'Dynamic'}</code>
                            </div>
                            <div className={styles.connectionRow}>
                              <span className={styles.connLabel}>OS</span>
                              <span className={styles.connValue}>{machine.os || 'Linux'}</span>
                            </div>
                            <div className={styles.connectionRow}>
                              <span className={styles.connLabel}>Protocol</span>
                              <code className={styles.connValue}>{machine.protocol || 'ssh'}</code>
                            </div>
                            {machine.ports && machine.ports.length > 0 && (
                              <div className={styles.connectionRow}>
                                <span className={styles.connLabel}>Ports</span>
                                <code className={styles.connValue}>{machine.ports.join(', ')}</code>
                              </div>
                            )}
                            {machine.credentials && Object.keys(machine.credentials).length > 0 && (
                              <div className={styles.credentialsBox}>
                                <h5>Credentials</h5>
                                {Object.entries(machine.credentials).map(([key, val]) => (
                                  <div key={key} className={styles.connectionRow}>
                                    <span className={styles.connLabel}>{key}</span>
                                    <code className={styles.connValue} onClick={() => navigator.clipboard?.writeText(val)} title="Click to copy">
                                      {val}
                                    </code>
                                  </div>
                                ))}
                              </div>
                            )}
                          </div>
                        ))
                      ) : instance.connection_info.type === 'sandbox' ? (
                        <p className={styles.sandboxMsg}>
                          {instance.connection_info.message || 'Lab environment is ready. Use the web interface below.'}
                        </p>
                      ) : (
                        <>
                          <div className={styles.connectionRow}>
                            <span className={styles.connLabel}>IP Address</span>
                            <code className={styles.connValue}>{instance.connection_info.ip || 'N/A'}</code>
                          </div>
                          <div className={styles.connectionRow}>
                            <span className={styles.connLabel}>Port</span>
                            <code className={styles.connValue}>{instance.connection_info.port || 'N/A'}</code>
                          </div>
                          <div className={styles.connectionRow}>
                            <span className={styles.connLabel}>Protocol</span>
                            <code className={styles.connValue}>{instance.connection_info.protocol || 'ssh'}</code>
                          </div>
                        </>
                      )}
                    </div>
                  )}

                  <div className={styles.consoleActions}>
                    <button className={styles.stopBtn} onClick={handleStop} disabled={stopping}>
                      {stopping ? 'Stopping...' : '⏹ Stop Lab'}
                    </button>
                    <button className={styles.resetBtn} onClick={handleReset} disabled={resetting}>
                      {resetting ? 'Resetting...' : '🔄 Reset'}
                    </button>
                  </div>
                </div>
              ) : isCompleted ? (
                <div className={styles.completedState}>
                  <span className={styles.completedIcon}>🎉</span>
                  <h3>Lab Completed!</h3>
                  <p>You have completed all challenges.</p>
                </div>
              ) : (
                <div className={styles.startPrompt}>
                  <p>
                    Start a virtual environment to begin working on this lab.
                    You will get SSH access to a dedicated machine.
                  </p>
                  <button className={styles.startBtn} onClick={handleStart} disabled={starting}>
                    {starting ? 'Starting...' : '🚀 Start Lab'}
                  </button>
                  <p className={styles.startNote}>
                    Your session will last 4 hours. You can stop and restart anytime.
                  </p>
                </div>
              )}
            </div>

            {/* Lab Info Card */}
            <div className={styles.card}>
              <h2 className={styles.cardTitle}>Lab Info</h2>
              <div className={styles.infoGrid}>
                <div className={styles.infoRow}>
                  <span>Difficulty</span>
                  <span style={{ color: DIFFICULTY_COLORS[lab.difficulty_level] || '#a0aec0', fontWeight: 600 }}>
                    {lab.difficulty_level}
                  </span>
                </div>
                <div className={styles.infoRow}>
                  <span>Type</span>
                  <span style={{ textTransform: 'capitalize' }}>{lab.type || 'Challenge'}</span>
                </div>
                <div className={styles.infoRow}>
                  <span>Points</span>
                  <span style={{ color: '#ffaa00' }}>{lab.xp_reward || 0} XP</span>
                </div>
                <div className={styles.infoRow}>
                  <span>Est. Time</span>
                  <span>{lab.duration_minutes || '?'} min</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  )
}
