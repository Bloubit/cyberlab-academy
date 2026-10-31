import Head from 'next/head'
import Link from 'next/link'
import { useRouter } from 'next/router'
import { useState, useEffect } from 'react'
import { useAuth } from '../../context/AuthContext'
import api from '../../lib/api'

const s = {
  container: { minHeight: '100vh', background: '#0a0a1a', color: '#fff' },
  header: {
    background: 'rgba(15,15,35,0.95)', borderBottom: '1px solid rgba(255,255,255,0.06)',
    padding: '0 2rem', display: 'flex', alignItems: 'center', justifyContent: 'space-between', height: 64,
  },
  main: { maxWidth: 800, margin: '0 auto', padding: '3rem 2rem' },
  loading: { display: 'flex', alignItems: 'center', justifyContent: 'center', minHeight: 400 },
  spinner: { width: 32, height: 32, border: '3px solid rgba(0,212,255,0.2)', borderTopColor: '#00d4ff', borderRadius: '50%', animation: 'spin 0.6s linear infinite' },
  backLink: { color: '#00d4ff', textDecoration: 'none', fontSize: '0.9rem', marginBottom: '2rem', display: 'inline-block' },
  lessonCard: { background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.08)', borderRadius: 16, padding: '2rem' },
  badge: { display: 'inline-block', padding: '0.25rem 0.75rem', borderRadius: 20, fontSize: '0.75rem', fontWeight: 600, marginBottom: '1rem' },
  title: { fontSize: '1.75rem', fontWeight: 700, margin: '0 0 1rem' },
  meta: { display: 'flex', gap: '1.5rem', marginBottom: '1.5rem', color: '#a0aec0', fontSize: '0.85rem' },
  content: { lineHeight: 1.8, fontSize: '0.95rem', color: '#e2e8f0' },
  lockedOverlay: {
    background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.08)',
    borderRadius: 16, padding: '3rem 2rem', textAlign: 'center',
  },
  enrollBtn: {
    marginTop: '1rem', padding: '0.75rem 2rem', border: 'none', borderRadius: 8,
    background: 'linear-gradient(90deg, #00d4ff, #9945ff)', color: '#fff',
    fontSize: '1rem', fontWeight: 600, cursor: 'pointer',
  },
}

export default function LessonDetail() {
  const router = useRouter()
  const { id } = router.query
  const { user, isAuthenticated, loading: authLoading } = useAuth()

  const [lesson, setLesson] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [isLocked, setIsLocked] = useState(false)

  useEffect(() => {
    if (!id || authLoading) return

    const fetchLesson = async () => {
      setLoading(true)
      setError(null)
      setIsLocked(false)
      try {
        const res = await api.getLesson(id)
        if (res.success) {
          setLesson(res.data)
        }
      } catch (err) {
        if (err.status === 403) {
          setIsLocked(true)
          setLesson(err.data?.data || null)
        } else if (err.status === 404) {
          setError('Lesson not found')
        } else {
          setError(err.message || 'Failed to load lesson')
        }
      } finally {
        setLoading(false)
      }
    }

    fetchLesson()
  }, [id, authLoading])

  const handleEnroll = () => {
    const courseId = lesson?.module_id
    if (courseId) {
      router.push(`/courses/${courseId}`)
    } else {
      router.push('/courses')
    }
  }

  if (loading || authLoading) {
    return (
      <div style={s.container}>
        <header style={s.header}>
          <Link href="/" style={{ color: '#00d4ff', fontSize: '1.2rem', fontWeight: 700, textDecoration: 'none' }}>🛡️ CyberLab</Link>
        </header>
        <div style={s.loading}><div style={s.spinner} /></div>
      </div>
    )
  }

  if (error || !lesson) {
    return (
      <div style={s.container}>
        <header style={s.header}>
          <Link href="/" style={{ color: '#00d4ff', fontSize: '1.2rem', fontWeight: 700, textDecoration: 'none' }}>🛡️ CyberLab</Link>
        </header>
        <main style={s.main}>
          <div style={{ textAlign: 'center', padding: '3rem' }}>
            <div style={{ fontSize: '3rem', marginBottom: '1rem' }}>🔍</div>
            <h2>Lesson Not Found</h2>
            <p style={{ color: '#a0aec0' }}>{error || 'This lesson does not exist.'}</p>
            <Link href="/courses" style={s.backLink}>← Back to Courses</Link>
          </div>
        </main>
      </div>
    )
  }

  return (
    <div style={s.container}>
      <Head><title>{lesson.title} - CyberLab Academy</title></Head>
      <header style={s.header}>
        <Link href="/" style={{ color: '#00d4ff', fontSize: '1.2rem', fontWeight: 700, textDecoration: 'none' }}>🛡️ CyberLab</Link>
        <nav style={{ display: 'flex', gap: '1rem' }}>
          <Link href="/courses" style={{ color: '#a0aec0', textDecoration: 'none', fontSize: '0.9rem' }}>Courses</Link>
          {isAuthenticated && (
            <Link href="/dashboard" style={{ color: '#a0aec0', textDecoration: 'none', fontSize: '0.9rem' }}>Dashboard</Link>
          )}
        </nav>
      </header>

      <main style={s.main}>
        <Link href="/courses" style={s.backLink}>← Back to Courses</Link>

        {isLocked ? (
          <div style={s.lockedOverlay}>
            <div style={{ fontSize: '3rem', marginBottom: '1rem' }}>🔒</div>
            <h2 style={{ marginBottom: '0.5rem' }}>Lesson Locked</h2>
            <p style={{ color: '#a0aec0', marginBottom: '1rem' }}>
              This lesson requires enrollment. Please enroll in the course to access this content.
            </p>
            {lesson.is_free && (
              <p style={{ color: '#00ff88', fontSize: '0.9rem' }}>
                ✅ This lesson is marked as free. Please sign in to access it.
              </p>
            )}
            <button style={s.enrollBtn} onClick={handleEnroll}>
              {isAuthenticated ? 'Enroll Now' : 'Sign In to Enroll'}
            </button>
          </div>
        ) : (
          <div>
            <div style={s.lessonCard}>
              <div style={s.badge}>
                {lesson.content_type === 'video' ? '🎬 Video' :
                 lesson.content_type === 'quiz' ? '📝 Quiz' :
                 lesson.content_type === 'lab' ? '💻 Lab' :
                 lesson.content_type === 'exam' ? '📋 Exam' : '📄 Text'}
              </div>
              <h1 style={s.title}>{lesson.title}</h1>
              <div style={s.meta}>
                {lesson.duration_minutes && <span>⏱️ {lesson.duration_minutes} min</span>}
                {lesson.xp_reward > 0 && <span>⚡ {lesson.xp_reward} XP</span>}
                {lesson.is_free && <span style={{ color: '#00ff88' }}>🔓 Free</span>}
              </div>
              <div style={s.content}>
                {lesson.content_type === 'video' && lesson.video?.url ? (
                  <div style={{ marginBottom: '1.5rem' }}>
                    <div style={{
                      background: '#000', borderRadius: 12, overflow: 'hidden',
                      aspectRatio: '16/9', display: 'flex', alignItems: 'center', justifyContent: 'center',
                    }}>
                      <video controls src={lesson.video.url} style={{ width: '100%', height: '100%' }} />
                    </div>
                  </div>
                ) : null}
                {lesson.content ? (
                  <div dangerouslySetInnerHTML={{ __html: lesson.content.replace(/\n/g, '<br/>') }} />
                ) : (
                  <p style={{ color: '#718096' }}>No content available for this lesson.</p>
                )}
              </div>
            </div>

            {lesson.resources?.length > 0 && (
              <div style={{ marginTop: '1.5rem' }}>
                <h3 style={{ fontSize: '1rem', marginBottom: '0.75rem' }}>📎 Resources</h3>
                <div style={{ display: 'flex', flexDirection: 'column', gap: '0.5rem' }}>
                  {lesson.resources.map(r => (
                    <a key={r.id} href={r.url} target="_blank" rel="noopener noreferrer"
                      style={{
                        display: 'flex', alignItems: 'center', gap: '0.5rem',
                        padding: '0.75rem 1rem', background: 'rgba(255,255,255,0.03)',
                        border: '1px solid rgba(255,255,255,0.08)', borderRadius: 8,
                        color: '#00d4ff', textDecoration: 'none', fontSize: '0.9rem',
                      }}>
                      <span>📄</span> {r.title}
                    </a>
                  ))}
                </div>
              </div>
            )}
          </div>
        )}
      </main>
    </div>
  )
}
