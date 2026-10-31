import Head from 'next/head'
import Link from 'next/link'
import { useState, useEffect } from 'react'
import { useAuth } from '../context/AuthContext'
import api from '../lib/api'
import Navbar from '../components/Navbar'
import styles from '../styles/Dashboard.module.css'

export default function Dashboard() {
  const { user, isAuthenticated, loading: authLoading } = useAuth()
  const [enrolledCourses, setEnrolledCourses] = useState([])
  const [labs, setLabs] = useState([])
  const [dashboard, setDashboard] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    if (authLoading) return
    if (!isAuthenticated) {
      setLoading(false)
      return
    }

    const fetchDashboard = async () => {
      try {
        // Fetch all dashboard data in parallel
        const [coursesRes, labsRes, dashboardRes] = await Promise.allSettled([
          api.getEnrolledCourses(),
          api.getMyLabs(),
          api.getDashboard(),
        ])

        if (coursesRes.status === 'fulfilled' && coursesRes.value?.success) {
          const data = coursesRes.value.data
          setEnrolledCourses(data?.data || data || [])
        }

        if (labsRes.status === 'fulfilled' && labsRes.value?.success) {
          const data = labsRes.value.data
          setLabs(data?.data || data || [])
        }

        if (dashboardRes.status === 'fulfilled' && dashboardRes.value?.success) {
          setDashboard(dashboardRes.value.data)
        }
      } catch (err) {
        setError(err.message || 'Failed to load dashboard')
      } finally {
        setLoading(false)
      }
    }

    fetchDashboard()
  }, [authLoading, isAuthenticated])

  // Not authenticated - show login prompt
  if (!authLoading && !isAuthenticated) {
    return (
      <div className={styles.container}>
        <Head>
          <title>Dashboard - CyberLab Academy</title>
        </Head>
        <Navbar />
        <main className={styles.main}>
          <div className={styles.authPrompt}>
            <div className={styles.authIcon}>🔐</div>
            <h2>Please Sign In</h2>
            <p>You need to be logged in to view your dashboard.</p>
            <div className={styles.authButtons}>
              <Link href="/login" className={styles.loginBtn}>Sign In</Link>
              <Link href="/register" className={styles.signupBtn}>Create Account</Link>
            </div>
          </div>
        </main>
      </div>
    )
  }

  // Loading state
  if (authLoading || loading) {
    return (
      <div className={styles.container}>
        <Head>
          <title>Dashboard - CyberLab Academy</title>
        </Head>
        <header className={styles.header}>
          <div className={styles.headerContent}>
            <Link href="/" className={styles.logo}>🛡️ CyberLab Academy</Link>
          </div>
        </header>
        <main className={styles.main}>
          <div className={styles.loadingState}>
            <div className={styles.spinner}></div>
            <p>Loading dashboard...</p>
          </div>
        </main>
      </div>
    )
  }

  const displayName = user?.name || 'Cybersecurity Student'
  const initials = displayName.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
  const xp = user?.total_xp || dashboard?.total_xp || 0
  const level = user?.level || dashboard?.level || 1
  const badgesCount = dashboard?.badges_count || (user?.badges?.length || 0)
  const recentBadges = dashboard?.recent_badges || []

  // Stats from dashboard API
  const activeLabsCount = dashboard?.active_labs_count || 0
  const completedLabsCount = dashboard?.completed_labs_count || 0
  const labXpEarned = dashboard?.lab_xp_earned || 0
  const challengesSolved = dashboard?.challenges_solved || 0
  const enrolledCoursesCount = dashboard?.enrolled_courses_count || enrolledCourses.length || 0
  const completedCoursesCount = dashboard?.completed_courses_count || 0
  const certificatesCount = dashboard?.certificates_count || 0

  return (
    <div className={styles.container}>
      <Head>
        <title>Dashboard - CyberLab Academy</title>
      </Head>
      
      <Navbar />
      
      <main className={styles.main}>
        {/* Error Banner */}
        {error && (
          <div className={styles.errorBanner}>
            <span>⚠️</span> {error}
            <button onClick={() => setError(null)} className={styles.dismissBtn}>×</button>
          </div>
        )}

        {/* Welcome Section */}
        <div className={styles.welcome}>
          <h1>Welcome back, {displayName.split(' ')[0]}! 👋</h1>
          <p>Continue your cybersecurity journey</p>
        </div>
        
        {/* Stats Cards */}
        <div className={styles.statsGrid}>
          <div className={styles.statCard}>
            <div className={styles.statIcon}>⚡</div>
            <div className={styles.statInfo}>
              <h3>{xp.toLocaleString()}</h3>
              <p>Total XP</p>
            </div>
          </div>
          
          <div className={styles.statCard}>
            <div className={styles.statIcon}>🎯</div>
            <div className={styles.statInfo}>
              <h3>Level {level}</h3>
              <p>{completedCoursesCount} courses completed</p>
            </div>
          </div>
          
          <div className={styles.statCard}>
            <div className={styles.statIcon}>💻</div>
            <div className={styles.statInfo}>
              <h3>{activeLabsCount}</h3>
              <p>Active Labs • {completedLabsCount} completed</p>
            </div>
          </div>
          
          <div className={styles.statCard}>
            <div className={styles.statIcon}>🏆</div>
            <div className={styles.statInfo}>
              <h3>{badgesCount}</h3>
              <p>{certificatesCount} certificates</p>
            </div>
          </div>
        </div>
        
        {/* Main Content Grid */}
        <div className={styles.grid}>
          {/* Continue Learning */}
          <div className={styles.section}>
            <h2>Continue Learning</h2>
            {enrolledCourses.length > 0 ? (
              <div className={styles.courseList}>
                {enrolledCourses.map(course => (
                  <div key={course.id} className={styles.courseItem}>
                    <div className={styles.courseIcon}>{course.icon || '📚'}</div>
                    <div className={styles.courseInfo}>
                      <h4>{course.title}</h4>
                      <p>{course.category_name || (typeof course.category === 'string' ? course.category : course.category?.name) || 'General'}</p>
                      <div className={styles.progressBar}>
                        <div 
                          className={styles.progressFill} 
                          style={{width: `${course.progress || 0}%`}}
                        ></div>
                      </div>
                      <span className={styles.progressText}>
                        {course.progress || 0}% Complete
                      </span>
                    </div>
                    <Link href={`/courses/${course.id}`} className={styles.continueBtn}>
                      Continue
                    </Link>
                  </div>
                ))}
              </div>
            ) : (
              <div className={styles.emptyState}>
                <p>No courses enrolled yet</p>
                <Link href="/courses" className={styles.browseBtn}>Browse Courses</Link>
              </div>
            )}
          </div>
          
          {/* Active Labs */}
          <div className={styles.section}>
            <h2>Labs & Challenges</h2>
            
            {/* Lab stats summary */}
            {(labXpEarned > 0 || challengesSolved > 0) && (
              <div className={styles.labStatsSummary}>
                <div className={styles.labStatChip}>
                  <span className={styles.labStatValue}>{labXpEarned.toLocaleString()}</span>
                  <span className={styles.labStatLabel}>XP earned</span>
                </div>
                <div className={styles.labStatChip}>
                  <span className={styles.labStatValue}>{challengesSolved}</span>
                  <span className={styles.labStatLabel}>Flags captured</span>
                </div>
                <div className={styles.labStatChip}>
                  <span className={styles.labStatValue}>{completedLabsCount}</span>
                  <span className={styles.labStatLabel}>Labs done</span>
                </div>
              </div>
            )}

            {labs.length > 0 ? (
              <div className={styles.labList}>
                {labs.slice(0, 5).map(lab => (
                  <div key={lab.id} className={styles.labItem}>
                    <div className={styles.labIcon}>{lab.icon || '💻'}</div>
                    <div className={styles.labInfo}>
                      <h4>{lab.lab?.title || lab.title || 'Lab'}</h4>
                      <p>{lab.status === 'running' ? '🟢 Running' : lab.status === 'completed' ? '✅ Completed' : 'Not started'}</p>
                    </div>
                    <Link href={`/labs/${lab.lab_id || lab.id}`} className={lab.status === 'running' ? styles.accessBtn : lab.status === 'completed' ? styles.completedBtn : styles.startBtn}>
                      {lab.status === 'running' ? 'Access' : lab.status === 'completed' ? 'Review' : 'Start'}
                    </Link>
                  </div>
                ))}
              </div>
            ) : (
              <div className={styles.emptyState}>
                {labXpEarned > 0 ? (
                  <p>All caught up! Check out new labs.</p>
                ) : (
                  <p>No labs yet. Start your first challenge!</p>
                )}
                <Link href="/labs" className={styles.browseBtn}>Browse Labs</Link>
              </div>
            )}
          </div>
          
          {/* Recent Badges */}
          <div className={styles.section}>
            <h2>Recent Badges</h2>
            {recentBadges.length > 0 ? (
              <div className={styles.badgeList}>
                {recentBadges.map((badge, index) => (
                  <div key={badge.id || index} className={styles.badgeItem}>
                    <span className={styles.badgeIcon}>{badge.icon || '🎖️'}</span>
                    <div className={styles.badgeInfo}>
                      <h4>{badge.name}</h4>
                      <p>{badge.description}</p>
                    </div>
                  </div>
                ))}
              </div>
            ) : (
              <div className={styles.badgePlaceholder}>
                <p>Complete challenges to earn badges! 🏆</p>
              </div>
            )}
          </div>
          
          {/* Leaderboard Preview */}
          <div className={styles.section}>
            <h2>Leaderboard</h2>
            <div className={styles.leaderboardList}>
              <div className={styles.leaderboardItem}>
                <span className={styles.rank}>#1</span>
                <span className={styles.leaderAvatar}>🏆</span>
                <span className={styles.leaderName}>CyberMaster</span>
                <span className={styles.leaderXp}>125,000 XP</span>
              </div>
              
              <div className={styles.leaderboardItem}>
                <span className={styles.rank}>#2</span>
                <span className={styles.leaderAvatar}>🥈</span>
                <span className={styles.leaderName}>SecurityNinja</span>
                <span className={styles.leaderXp}>98,500 XP</span>
              </div>
              
              <div className={styles.leaderboardItem}>
                <span className={styles.rank}>#3</span>
                <span className={styles.leaderAvatar}>🥉</span>
                <span className={styles.leaderName}>HackerPro</span>
                <span className={styles.leaderXp}>87,200 XP</span>
              </div>
            </div>
            <Link href="/leaderboard" className={styles.viewAll}>View Full Leaderboard →</Link>
          </div>
        </div>
      </main>
    </div>
  )
}
