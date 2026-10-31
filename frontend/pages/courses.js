import Head from 'next/head'
import Link from 'next/link'
import { useState, useEffect } from 'react'
import { useAuth } from '../context/AuthContext'
import api from '../lib/api'
import styles from '../styles/Courses.module.css'

const DIFFICULTY_COLORS = {
  beginner: '#00ff88',
  intermediate: '#00d4ff',
  advanced: '#ffaa00',
  expert: '#ff4757',
}

const COURSE_ICONS = ['🌐', '🔍', '🦠', '🐍', '🔬', '☁️', '🛡️', '🤖', '📡', '🔑']

export default function Courses() {
  const { user, isAuthenticated } = useAuth()
  const [courses, setCourses] = useState([])
  const [meta, setMeta] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [filter, setFilter] = useState('all')
  const [page, setPage] = useState(1)

  useEffect(() => {
    fetchCourses()
  }, [filter, page])

  const fetchCourses = async () => {
    setLoading(true)
    try {
      const params = { page, per_page: 12, sort_by: 'created_at', sort_order: 'desc' }
      if (filter !== 'all') params.difficulty = filter

      const res = await api.getPublishedCourses(params)
      if (res.success) {
        setCourses(res.data?.data || res.data || [])
        setMeta(res.meta)
      } else {
        setCourses([])
      }
    } catch (err) {
      setError(err.message || 'Failed to load courses')
      console.error('Courses fetch error:', err)
    } finally {
      setLoading(false)
    }
  }

  const displayName = user?.name || 'Student'
  const initials = displayName.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)

  return (
    <div className={styles.container}>
      <Head>
        <title>Courses - CyberLab Academy</title>
      </Head>

      {/* Header */}
      <header className={styles.header}>
        <div className={styles.headerContent}>
          <Link href="/" className={styles.logo}>🛡️ CyberLab Academy</Link>
          <nav className={styles.nav}>
            <Link href="/dashboard">Dashboard</Link>
            <Link href="/courses" className={styles.active}>Courses</Link>
            <Link href="/labs">Labs</Link>
            <Link href="/leaderboard">Leaderboard</Link>
          </nav>
          {isAuthenticated ? (
            <div className={styles.userMenu}>
              <Link href="/profile" className={styles.avatar}>{initials}</Link>
            </div>
          ) : (
            <div className={styles.authBtns}>
              <Link href="/login" className={styles.loginBtn}>Sign In</Link>
              <Link href="/register" className={styles.signupBtn}>Get Started</Link>
            </div>
          )}
        </div>
      </header>

      <main className={styles.main}>
        <div className={styles.pageHeader}>
          <h1>Courses</h1>
          <p>Master cybersecurity with expert-led courses</p>
          {error && <div className={styles.errorBanner}>⚠️ {error}</div>}
        </div>

        {/* Filters */}
        <div className={styles.filters}>
          {['all', 'beginner', 'intermediate', 'advanced', 'expert'].map(f => (
            <button
              key={f}
              className={`${styles.filterBtn} ${filter === f ? styles.active : ''}`}
              onClick={() => { setFilter(f); setPage(1) }}
            >
              {f === 'all' ? 'All' : f.charAt(0).toUpperCase() + f.slice(1)}
            </button>
          ))}
        </div>

        {/* Courses Grid */}
        {loading ? (
          <div className={styles.loadingGrid}>
            {[1, 2, 3, 4, 5, 6].map(i => (
              <div key={i} className={styles.skeletonCard}>
                <div className={styles.skeletonImage}></div>
                <div className={styles.skeletonContent}>
                  <div className={styles.skeletonBadge}></div>
                  <div className={styles.skeletonLine}></div>
                  <div className={styles.skeletonLineShort}></div>
                  <div className={styles.skeletonMeta}></div>
                </div>
              </div>
            ))}
          </div>
        ) : courses.length === 0 ? (
          <div className={styles.emptyState}>
            <span className={styles.emptyIcon}>📚</span>
            <h2>No courses found</h2>
            <p>Try adjusting your filters or check back later for new courses.</p>
          </div>
        ) : (
          <>
            <div className={styles.coursesGrid}>
              {courses.map((course, idx) => (
                <Link key={course.id} href={`/courses/${course.id}`} className={styles.courseCard}>
                  <div className={styles.courseImage}>
                    <span>{course.icon || COURSE_ICONS[idx % COURSE_ICONS.length]}</span>
                  </div>
                  <div className={styles.courseContent}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '0.5rem' }}>
                      <span className={`${styles.badge} ${styles[course.difficulty_level || 'beginner']}`}>
                        {course.difficulty_level || 'Beginner'}
                      </span>
                      <span className={styles.priceTag}>
                        {course.is_free ? 'Free' : `$${course.price || 0}`}
                      </span>
                    </div>
                    <h3>{course.title}</h3>
                    <p>{course.description?.slice(0, 100)}...</p>
                    <div className={styles.courseMeta}>
                      <span>📚 {course.total_lessons || '?'} lessons</span>
                      <span>👥 {(course.enrollment_count || 0).toLocaleString()}</span>
                      {course.estimated_hours && <span>⏱ {course.estimated_hours}h</span>}
                    </div>
                    <span className={styles.enrollBtn}>
                      {isAuthenticated ? 'Enroll Now' : 'View Course →'}
                    </span>
                  </div>
                </Link>
              ))}
            </div>

            {/* Pagination */}
            {meta && meta.last_page > 1 && (
              <div className={styles.pagination}>
                <button className={styles.pageBtn} disabled={page === 1}
                  onClick={() => setPage(p => p - 1)}>← Prev</button>
                <span className={styles.pageInfo}>
                  Page {meta.current_page} of {meta.last_page} ({meta.total} courses)
                </span>
                <button className={styles.pageBtn} disabled={page === meta.last_page}
                  onClick={() => setPage(p => p + 1)}>Next →</button>
              </div>
            )}
          </>
        )}
      </main>
    </div>
  )
}
