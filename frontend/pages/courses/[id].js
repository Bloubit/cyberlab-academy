import Head from 'next/head'
import Link from 'next/link'
import { useRouter } from 'next/router'
import { useState, useEffect } from 'react'
import { useAuth } from '../../context/AuthContext'
import api from '../../lib/api'
import styles from '../../styles/CourseDetail.module.css'

const DIFFICULTY_COLORS = {
  beginner: '#00ff88',
  intermediate: '#00d4ff',
  advanced: '#ffaa00',
  expert: '#ff4757',
}

const COURSE_ICONS = ['🌐', '🔍', '🦠', '🐍', '🔬', '☁️', '🛡️', '🤖', '📡', '🔑']

export default function CourseDetail() {
  const router = useRouter()
  const { id } = router.query
  const { user, isAuthenticated, loading: authLoading } = useAuth()

  const [course, setCourse] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [enrolling, setEnrolling] = useState(false)
  const [enrolled, setEnrolled] = useState(false)
  const [enrollMsg, setEnrollMsg] = useState(null)

  useEffect(() => {
    if (!id || authLoading) return

    const fetchCourse = async () => {
      setLoading(true)
      setError(null)
      try {
        const res = await api.getCourse(id)
        if (res.success) {
          setCourse(res.data)
        } else {
          setError(res.message || 'Failed to load course')
        }
      } catch (err) {
        if (err.status === 404) {
          setError('Course not found')
        } else {
          setError(err.message || 'Failed to load course')
        }
      } finally {
        setLoading(false)
      }
    }

    fetchCourse()
  }, [id, authLoading])

  useEffect(() => {
    if (enrollMsg) {
      const t = setTimeout(() => setEnrollMsg(null), 4000)
      return () => clearTimeout(t)
    }
  }, [enrollMsg])

  const handleEnroll = async () => {
    if (!isAuthenticated) {
      router.push(`/login?redirect=/courses/${id}`)
      return
    }

    setEnrolling(true)
    setEnrollMsg(null)
    try {
      const res = await api.enrollCourse(id)
      if (res.success) {
        setEnrolled(true)
        setEnrollMsg({ type: 'success', text: '🎉 Successfully enrolled!' })
      } else {
        if (res.message?.includes('already enrolled')) {
          setEnrolled(true)
          setEnrollMsg({ type: 'info', text: '✅ You are already enrolled in this course' })
        } else {
          setEnrollMsg({ type: 'error', text: res.message || 'Enrollment failed' })
        }
      }
    } catch (err) {
      setEnrollMsg({ type: 'error', text: err.message || 'Enrollment failed' })
    } finally {
      setEnrolling(false)
    }
  }

  // Loading state
  if (loading || authLoading) {
    return (
      <div className={styles.container}>
        <header className={styles.header}>
          <div className={styles.headerContent}>
            <Link href="/" className={styles.logo}>🛡️ CyberLab Academy</Link>
            <nav className={styles.nav}>
              <Link href="/courses" className={styles.active}>Courses</Link>
              <Link href="/labs">Labs</Link>
              <Link href="/leaderboard">Leaderboard</Link>
            </nav>
          </div>
        </header>
        <div className={styles.loading}>
          <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: '1rem' }}>
            <div className="loading-spinner"></div>
            <p style={{ color: '#a0aec0', fontSize: '1rem' }}>Loading course...</p>
          </div>
        </div>
      </div>
    )
  }

  // Error / Not found state
  if (error || !course) {
    return (
      <div className={styles.container}>
        <header className={styles.header}>
          <div className={styles.headerContent}>
            <Link href="/" className={styles.logo}>🛡️ CyberLab Academy</Link>
            <nav className={styles.nav}>
              <Link href="/courses">Courses</Link>
              <Link href="/labs">Labs</Link>
            </nav>
          </div>
        </header>
        <div className={styles.loading}>
          <div style={{ textAlign: 'center' }}>
            <div style={{ fontSize: '4rem', marginBottom: '1rem' }}>🔍</div>
            <h2 style={{ marginBottom: '0.5rem' }}>Course Not Found</h2>
            <p style={{ color: '#a0aec0', marginBottom: '1.5rem' }}>{error || 'This course does not exist or has been removed.'}</p>
            <Link href="/courses" style={{ color: '#00d4ff', textDecoration: 'none', fontWeight: 600 }}>
              ← Back to Courses
            </Link>
          </div>
        </div>
      </div>
    )
  }

  const difficultyLevel = course.difficulty_level || 'beginner'
  const courseIcon = course.icon || COURSE_ICONS[course.id % COURSE_ICONS.length]
  const modules = course.modules || []
  const isFree = course.is_free !== false
  const displayName = user?.name || 'Student'

  return (
    <div className={styles.container}>
      <Head>
        <title>{course.title} - CyberLab Academy</title>
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
            <Link href="/profile" style={{
              width: 36, height: 36, borderRadius: '50%',
              background: 'linear-gradient(135deg, #00d4ff, #9945ff)',
              display: 'flex', alignItems: 'center', justifyContent: 'center',
              fontWeight: 600, fontSize: '0.875rem', textDecoration: 'none', color: '#fff'
            }}>
              {displayName.charAt(0).toUpperCase()}
            </Link>
          ) : (
            <Link href="/login" style={{
              color: '#00d4ff', textDecoration: 'none', fontWeight: 600, fontSize: '0.9rem'
            }}>Sign In</Link>
          )}
        </div>
      </header>

      <main className={styles.main}>
        {/* Notification */}
        {enrollMsg && (
          <div style={{
            padding: '0.75rem 1rem', borderRadius: '8px', marginBottom: '1.5rem',
            background: enrollMsg.type === 'success' ? 'rgba(0,255,136,0.15)' :
                       enrollMsg.type === 'info' ? 'rgba(0,212,255,0.15)' : 'rgba(255,71,87,0.15)',
            border: `1px solid ${
              enrollMsg.type === 'success' ? 'rgba(0,255,136,0.3)' :
              enrollMsg.type === 'info' ? 'rgba(0,212,255,0.3)' : 'rgba(255,71,87,0.3)'
            }`,
            color: enrollMsg.type === 'success' ? '#00ff88' :
                   enrollMsg.type === 'info' ? '#00d4ff' : '#ff4757',
            fontSize: '0.9rem',
          }}>
            {enrollMsg.text}
          </div>
        )}

        {/* Back Link */}
        <Link href="/courses" className={styles.backLink}>← Back to Courses</Link>

        {/* Course Header */}
        <div className={styles.courseHeader}>
          <div className={styles.courseInfo}>
            <span className={`${styles.badge} ${styles[difficultyLevel] || styles.beginner}`}>
              {difficultyLevel}
            </span>
            <h1>{courseIcon} {course.title}</h1>
            <p>{course.description || 'No description available.'}</p>

            <div className={styles.courseMeta}>
              <span>📚 {course.total_lessons || course.modules?.length || 0} lessons</span>
              {course.estimated_hours && <span>⏱️ {course.estimated_hours}h</span>}
              <span>👥 {(course.enrollment_count || 0).toLocaleString()} students</span>
              {course.subtitle && <span>🏷️ {course.subtitle}</span>}
            </div>

            {/* Enrollment Section */}
            <div style={{ display: 'flex', alignItems: 'center', gap: '1rem', flexWrap: 'wrap' }}>
              <button
                className={styles.enrollBtn}
                onClick={handleEnroll}
                disabled={enrolling || enrolled}
              >
                {enrolling ? 'Enrolling...' :
                 enrolled ? '✅ Enrolled' :
                 isFree ? 'Enroll Now - Free' :
                 `Enroll Now - $${course.price || 0}`}
              </button>
              {!isAuthenticated && !enrolled && (
                <span style={{ color: '#a0aec0', fontSize: '0.85rem' }}>
                  Sign in to enroll in this course
                </span>
              )}
            </div>

            {/* Learning Outcomes */}
            {course.learning_outcomes?.length > 0 && (
              <div style={{ marginTop: '2rem' }}>
                <h3 style={{ color: '#00d4ff', marginBottom: '0.75rem', fontSize: '1.1rem' }}>
                  🎯 What You'll Learn
                </h3>
                <div style={{ display: 'flex', flexWrap: 'wrap', gap: '0.5rem' }}>
                  {course.learning_outcomes.map((outcome, i) => (
                    <span key={i} style={{
                      padding: '0.4rem 0.75rem', background: 'rgba(0,212,255,0.1)',
                      border: '1px solid rgba(0,212,255,0.2)', borderRadius: '6px',
                      fontSize: '0.85rem', color: '#e2e8f0'
                    }}>
                      ✓ {outcome}
                    </span>
                  ))}
                </div>
              </div>
            )}

            {/* Requirements */}
            {course.requirements?.length > 0 && (
              <div style={{ marginTop: '1.5rem' }}>
                <h3 style={{ color: '#ffaa00', marginBottom: '0.75rem', fontSize: '1.1rem' }}>
                  📋 Prerequisites
                </h3>
                <ul style={{ paddingLeft: '1.5rem', color: '#a0aec0', fontSize: '0.9rem', lineHeight: 2 }}>
                  {course.requirements.map((req, i) => (
                    <li key={i}>{req}</li>
                  ))}
                </ul>
              </div>
            )}
          </div>
        </div>

        {/* Course Content - Modules with Lessons */}
        {modules.length > 0 && (
          <div className={styles.courseContent}>
            <h2>📖 Course Content</h2>
            <p style={{ color: '#a0aec0', fontSize: '0.85rem', marginTop: '-0.5rem', marginBottom: '1.25rem' }}>
              {!isFree && !enrolled && '🔓 Free preview lessons available. Enroll to access all content.'}
            </p>
            <div className={styles.modulesList}>
              {modules.map((module, index) => (
                <div key={module.id} className={styles.moduleItem}>
                  <div className={styles.moduleHeader}>
                    <span className={styles.moduleNumber}>{index + 1}</span>
                    <div className={styles.moduleInfo}>
                      <h3>{module.title}</h3>
                      <p>
                        {module.lessons?.length || module.total_lessons || 0} lessons
                        {module.total_duration && ` • ${module.total_duration}`}
                      </p>
                    </div>
                  </div>
                  {/* Lessons inside module */}
                  {module.lessons?.length > 0 && (
                    <div style={{
                      borderTop: '1px solid rgba(255,255,255,0.06)',
                      marginTop: '0.75rem', paddingTop: '0.5rem',
                    }}>
                      {module.lessons.map((lesson, lIndex) => {
                        const isLessonFree = lesson.is_free || course.is_free
                        const isLessonAccessible = isLessonFree || enrolled

                        return (
                          <Link
                            key={lesson.id}
                            href={isLessonFree ? `/lessons/${lesson.id}` : isAuthenticated ? '#' : `/login?redirect=/courses/${id}`}
                            onClick={e => {
                              if (!isLessonAccessible && isAuthenticated) {
                                e.preventDefault()
                                handleEnroll()
                              }
                            }}
                            style={{
                              textDecoration: 'none', display: 'block',
                              padding: '0.6rem 0.75rem', margin: '0.15rem 0',
                              borderRadius: '8px',
                              background: isLessonFree ? 'rgba(0,255,136,0.03)' : 'transparent',
                              border: `1px solid ${isLessonFree ? 'rgba(0,255,136,0.1)' : 'transparent'}`,
                              cursor: isLessonAccessible ? 'pointer' : 'default',
                              transition: 'all 0.15s',
                            }}
                            onMouseEnter={e => {
                              if (isLessonAccessible) {
                                e.currentTarget.style.background = isLessonFree ? 'rgba(0,255,136,0.08)' : 'rgba(255,255,255,0.05)'
                                e.currentTarget.style.borderColor = isLessonFree ? 'rgba(0,255,136,0.2)' : 'rgba(255,255,255,0.15)'
                              }
                            }}
                            onMouseLeave={e => {
                              e.currentTarget.style.background = isLessonFree ? 'rgba(0,255,136,0.03)' : 'transparent'
                              e.currentTarget.style.borderColor = isLessonFree ? 'rgba(0,255,136,0.1)' : 'transparent'
                            }}
                          >
                            <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                              <span style={{ 
                                width: 24, textAlign: 'center',
                                color: isLessonFree ? '#00ff88' : isLessonAccessible ? '#e2e8f0' : '#4a5568',
                                fontSize: '0.85rem'
                              }}>
                                {isLessonFree ? '📺' : isLessonAccessible ? '📹' : '🔒'}
                              </span>
                              <div style={{ flex: 1 }}>
                                <span style={{
                                  fontSize: '0.88rem', fontWeight: 500,
                                  color: isLessonFree ? '#e2e8f0' : isLessonAccessible ? '#e2e8f0' : '#718096',
                                }}>
                                  {`${lIndex + 1}. ${lesson.title}`}
                                </span>
                              </div>
                              {lesson.duration_minutes && (
                                <span style={{ color: '#718096', fontSize: '0.75rem' }}>
                                  {lesson.duration_minutes}m
                                </span>
                              )}
                              {isLessonFree && (
                                <span style={{
                                  fontSize: '0.65rem', padding: '0.15rem 0.5rem',
                                  borderRadius: '4px',
                                  background: 'rgba(0,255,136,0.15)',
                                  color: '#00ff88', fontWeight: 600,
                                }}>
                                  FREE
                                </span>
                              )}
                              {!isLessonAccessible && isAuthenticated && (
                                <span style={{ fontSize: '0.7rem', color: '#ffaa00' }}>
                                  Enroll
                                </span>
                              )}
                            </div>
                          </Link>
                        )
                      })}
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>
        )}

        {/* Instructor Info */}
        {/* Related Labs */}
        {course.labs?.length > 0 && (
          <div className={styles.courseContent} style={{ marginTop: '2rem' }}>
            <h2>💻 Related Labs</h2>
            <p style={{ color: '#a0aec0', fontSize: '0.9rem', marginBottom: '1.25rem' }}>
              Practice your skills with these hands-on labs related to this course.
            </p>
            <div className={styles.labsGrid} style={{
              display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))',
              gap: '1rem',
            }}>
              {course.labs.map(lab => {
                const diffColor = DIFFICULTY_COLORS[lab.difficulty_level] || '#a0aec0'

                return (
                  <Link key={lab.id} href={`/labs/${lab.id}`} style={{ textDecoration: 'none' }}>
                    <div style={{
                      background: 'rgba(255,255,255,0.03)',
                      border: '1px solid rgba(255,255,255,0.08)',
                      borderRadius: '12px',
                      padding: '1.25rem',
                      transition: 'all 0.2s ease',
                      cursor: 'pointer',
                    }}
                    onMouseEnter={e => {
                      e.currentTarget.style.borderColor = 'rgba(0,212,255,0.3)'
                      e.currentTarget.style.background = 'rgba(0,212,255,0.05)'
                    }}
                    onMouseLeave={e => {
                      e.currentTarget.style.borderColor = 'rgba(255,255,255,0.08)'
                      e.currentTarget.style.background = 'rgba(255,255,255,0.03)'
                    }}>
                      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: '0.5rem' }}>
                        <h4 style={{ margin: 0, fontSize: '0.95rem', color: '#e2e8f0' }}>
                          {lab.title}
                        </h4>
                        <span style={{
                          fontSize: '0.7rem', padding: '0.2rem 0.5rem',
                          borderRadius: '4px', background: `${diffColor}20`,
                          color: diffColor, fontWeight: 600, textTransform: 'capitalize',
                        }}>
                          {lab.difficulty_level}
                        </span>
                      </div>
                      {lab.description && (
                        <p style={{ color: '#a0aec0', fontSize: '0.8rem', margin: '0.5rem 0', lineHeight: 1.4 }}>
                          {lab.description.length > 100
                            ? lab.description.substring(0, 100) + '...'
                            : lab.description}
                        </p>
                      )}
                      <div style={{ display: 'flex', gap: '1rem', marginTop: '0.75rem', fontSize: '0.75rem', color: '#718096' }}>
                        <span>⚡ {lab.xp_reward || 0} XP</span>
                        <span>⏱️ {lab.duration_minutes || '-'}m</span>
                        <span>🔬 {lab.type || 'lab'}</span>
                      </div>
                    </div>
                  </Link>
                )
              })}
            </div>
          </div>
        )}

        {/* Instructor Info */}
        {course.instructor && (
          <div className={styles.courseContent} style={{ marginTop: '2rem' }}>
            <h2>👨‍🏫 Instructor</h2>
            <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
              <div style={{
                width: 48, height: 48, borderRadius: '50%',
                background: 'linear-gradient(135deg, #9945ff, #ff00ff)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                fontWeight: 700, fontSize: '1.1rem'
              }}>
                {course.instructor.name?.charAt(0) || 'I'}
              </div>
              <div>
                <h4 style={{ marginBottom: '0.25rem' }}>{course.instructor.name || 'Instructor'}</h4>
                <p style={{ color: '#a0aec0', fontSize: '0.85rem', margin: 0 }}>
                  {course.instructor.email || ''}
                </p>
              </div>
            </div>
          </div>
        )}

        {/* Category Tags */}
        {course.category && (
          <div style={{ marginTop: '2rem', textAlign: 'center' }}>
            <span style={{
              display: 'inline-block', padding: '0.5rem 1rem',
              background: 'rgba(255,255,255,0.05)', borderRadius: '8px',
              border: '1px solid rgba(255,255,255,0.1)', color: '#a0aec0',
              fontSize: '0.85rem'
            }}>
              📂 Category: {course.category.name || course.category}
            </span>
          </div>
        )}
      </main>
    </div>
  )
}
