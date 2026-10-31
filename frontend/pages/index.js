import Head from 'next/head'
import Link from 'next/link'
import { useState, useEffect } from 'react'
import api from '../lib/api'
import styles from '../styles/Home.module.css'

const DIFFICULTY_COLORS = {
  beginner: '#00ff88',
  intermediate: '#00d4ff',
  advanced: '#ffaa00',
  expert: '#ff4757',
}

const LAB_ICONS = ['💻', '🖥️', '🔬', '🧪', '🌐', '🔍', '🛡️', '⚔️']

export default function Home() {
  const [data, setData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    const fetchData = async () => {
      try {
        const res = await api.getHomeData()
        if (res.success) {
          setData(res.data)
        }
      } catch (err) {
        setError(err.message || 'Failed to load data')
        console.error('Home fetch error:', err)
      } finally {
        setLoading(false)
      }
    }
    fetchData()
  }, [])

  const courses = data?.featured_courses || []
  const labs = data?.labs || []
  const stats = data?.stats || {}

  return (
    <div className={styles.container}>
      <Head>
        <title>CyberLab Academy - Cybersecurity Training Platform</title>
        <meta name="description" content="Master cybersecurity through hands-on labs, real-world challenges, and expert-led courses." />
      </Head>

      {/* Navigation */}
      <header className={styles.header}>
        <div className={styles.headerContent}>
          <Link href="/" className={styles.logo}>🛡️ CyberLab Academy</Link>
          <nav className={styles.nav}>
            <Link href="/courses">Courses</Link>
            <Link href="/labs">Labs</Link>
            <Link href="/leaderboard">Leaderboard</Link>
          </nav>
          <div className={styles.headerButtons}>
            <Link href="/login" className={styles.loginBtn}>Sign In</Link>
            <Link href="/register" className={styles.signupBtn}>Get Started</Link>
          </div>
        </div>
      </header>

      <main className={styles.main}>
        {/* Hero Section */}
        <section className={styles.hero}>
          <div className={styles.heroContent}>
            <h1 className={styles.heroTitle}>
              Master <span className={styles.gradient}>Cybersecurity</span>
              <br />Through Hands-On Labs
            </h1>
            <p className={styles.heroDesc}>
              Practice in real virtual environments, solve CTF challenges, and earn certificates 
              recognized by industry leaders. Start your cybersecurity journey today.
            </p>
            <div className={styles.heroButtons}>
              <Link href="/register" className={styles.primaryBtn}>Start Free Trial</Link>
              <Link href="/courses" className={styles.secondaryBtn}>View Courses</Link>
            </div>
            <div className={styles.heroTrust}>
              <span>⚡ No credit card required</span>
              <span>🎓 100% online</span>
              <span>🏆 Industry recognized</span>
            </div>
          </div>
          <div className={styles.heroVisual}>
            <div className={styles.heroCard}>
              <div className={styles.heroCardIcon}>🛡️</div>
              <div className={styles.heroCardText}>
                <strong>Interactive Labs</strong>
                <span>Real-time VM environments</span>
              </div>
            </div>
            <div className={styles.heroCard}>
              <div className={styles.heroCardIcon}>🎯</div>
              <div className={styles.heroCardText}>
                <strong>CTF Challenges</strong>
                <span>Capture The Flag competitions</span>
              </div>
            </div>
            <div className={styles.heroCard}>
              <div className={styles.heroCardIcon}>📜</div>
              <div className={styles.heroCardText}>
                <strong>Certificates</strong>
                <span>Share your achievements</span>
              </div>
            </div>
          </div>
        </section>

        {/* Live Stats */}
        <section className={styles.statsSection}>
          <div className={styles.statsGrid}>
            <div className={styles.statItem}>
              <span className={styles.statValue}>{loading ? '...' : stats.active_students?.toLocaleString() || '5K+'}</span>
              <span className={styles.statLabel}>Active Students</span>
            </div>
            <div className={styles.statItem}>
              <span className={styles.statValue}>{loading ? '...' : stats.total_courses || '200+'}</span>
              <span className={styles.statLabel}>Courses</span>
            </div>
            <div className={styles.statItem}>
              <span className={styles.statValue}>{loading ? '...' : stats.labs_completed?.toLocaleString() || '1M+'}</span>
              <span className={styles.statLabel}>Labs Completed</span>
            </div>
            <div className={styles.statItem}>
              <span className={styles.statValue}>{loading ? '...' : stats.total_labs || '50+'}</span>
              <span className={styles.statLabel}>Hands-On Labs</span>
            </div>
          </div>
        </section>

        {/* Featured Courses */}
        <section className={styles.section}>
          <div className={styles.sectionHeader}>
            <h2 className={styles.sectionTitle}>Featured Courses</h2>
            <p className={styles.sectionSubtitle}>Start learning from industry experts</p>
            <Link href="/courses" className={styles.viewAll}>View All Courses →</Link>
          </div>

          {loading ? (
            <div className={styles.loadingGrid}>
              {[1,2,3].map(i => (
                <div key={i} className={styles.skeletonCard}>
                  <div className={styles.skeletonIcon}></div>
                  <div className={styles.skeletonLine}></div>
                  <div className={styles.skeletonLineShort}></div>
                </div>
              ))}
            </div>
          ) : (
            <div className={styles.coursesGrid}>
              {courses.map(course => (
                <Link key={course.id} href={`/courses/${course.id}`} className={styles.courseCard}>
                  <div className={styles.courseCardTop}>
                    <span className={styles.courseIcon}>{course.icon || '📚'}</span>
                    <span className={styles.priceBadge}>
                      {course.is_free ? 'Free' : `$${course.price}`}
                    </span>
                  </div>
                  <span className={styles.difficultyBadge} style={{ color: DIFFICULTY_COLORS[course.difficulty_level] || '#a0aec0' }}>
                    {course.difficulty_level}
                  </span>
                  <h3 className={styles.courseTitle}>{course.title}</h3>
                  <p className={styles.courseDesc}>{course.description?.slice(0, 80)}...</p>
                  <div className={styles.courseMeta}>
                    <span>📚 {course.total_lessons || '?'} lessons</span>
                    <span>👥 {course.enrollment_count || 0}</span>
                  </div>
                </Link>
              ))}
            </div>
          )}

          {!loading && courses.length === 0 && (
            <div className={styles.emptyState}>
              <p>No courses available yet. Check back soon!</p>
            </div>
          )}
        </section>

        {/* Labs Section */}
        <section className={`${styles.section} ${styles.labsSection}`}>
          <div className={styles.sectionHeader}>
            <h2 className={styles.sectionTitle}>Hands-On Labs</h2>
            <p className={styles.sectionSubtitle}>Practice in real virtual environments</p>
            <Link href="/labs" className={styles.viewAll}>Explore All Labs →</Link>
          </div>

          <div className={styles.labsGrid}>
            {loading ? (
              [1,2,3].map(i => (
                <div key={i} className={styles.skeletonCard}>
                  <div className={styles.skeletonIcon}></div>
                  <div className={styles.skeletonLine}></div>
                  <div className={styles.skeletonLineShort}></div>
                </div>
              ))
            ) : (
              labs.map((lab, idx) => (
                <Link key={lab.id} href={`/labs/${lab.id}`} className={styles.labCard}>
                  <div className={styles.labCardHeader}>
                    <span className={styles.labIcon}>{LAB_ICONS[idx % LAB_ICONS.length]}</span>
                    <span className={styles.labType}>{lab.type || lab.lab_type}</span>
                  </div>
                  <h3 className={styles.labTitle}>{lab.title}</h3>
                  <p className={styles.labDesc}>{lab.description?.slice(0, 70)}...</p>
                  <div className={styles.labMeta}>
                    <span style={{ color: DIFFICULTY_COLORS[lab.difficulty_level] || '#a0aec0', fontWeight: 600 }}>
                      {lab.difficulty_level}
                    </span>
                    <span>⏱ {lab.duration_minutes || lab.estimated_time_minutes || '?'} min</span>
                    <span>⭐ {lab.xp_reward || lab.points || 0} XP</span>
                  </div>
                </Link>
              ))
            )}
          </div>

          {!loading && labs.length === 0 && (
            <div className={styles.emptyState}>
              <p>No labs available yet. Check back soon!</p>
            </div>
          )}
        </section>

        {/* Features */}
        <section className={styles.section}>
          <div className={styles.sectionHeader}>
            <h2 className={styles.sectionTitle}>Why CyberLab Academy?</h2>
            <p className={styles.sectionSubtitle}>Everything you need to master cybersecurity</p>
          </div>
          <div className={styles.featuresGrid}>
            <div className={styles.featureCard}>
              <div className={styles.featureIcon}>🎯</div>
              <h3 className={styles.featureTitle}>Hands-on Labs</h3>
              <p className={styles.featureDesc}>Practice in real virtual machine environments with guided challenges and instant feedback.</p>
            </div>
            <div className={styles.featureCard}>
              <div className={styles.featureIcon}>📚</div>
              <h3 className={styles.featureTitle}>Expert Courses</h3>
              <p className={styles.featureDesc}>Learn from industry professionals with structured learning paths and real-world scenarios.</p>
            </div>
            <div className={styles.featureCard}>
              <div className={styles.featureIcon}>🏆</div>
              <h3 className={styles.featureTitle}>CTF Challenges</h3>
              <p className={styles.featureDesc}>Test your skills with Capture The Flag competitions and climb the leaderboard.</p>
            </div>
            <div className={styles.featureCard}>
              <div className={styles.featureIcon}>🎓</div>
              <h3 className={styles.featureTitle}>Certificates</h3>
              <p className={styles.featureDesc}>Earn recognized certificates to showcase your cybersecurity expertise.</p>
            </div>
            <div className={styles.featureCard}>
              <div className={styles.featureIcon}>💪</div>
              <h3 className={styles.featureTitle}>Community</h3>
              <p className={styles.featureDesc}>Join a community of cybersecurity enthusiasts. Learn together, grow together.</p>
            </div>
            <div className={styles.featureCard}>
              <div className={styles.featureIcon}>⚡</div>
              <h3 className={styles.featureTitle}>Real-time Feedback</h3>
              <p className={styles.featureDesc}>Get instant feedback on your submissions and track your progress over time.</p>
            </div>
          </div>
        </section>

        {/* CTA */}
        <section className={styles.ctaSection}>
          <div className={styles.ctaContent}>
            <h2 className={styles.ctaTitle}>Ready to Start Your Journey?</h2>
            <p className={styles.ctaDesc}>Join thousands of students mastering cybersecurity. Start for free, no credit card required.</p>
            <div className={styles.ctaButtons}>
              <Link href="/register" className={styles.primaryBtn}>Create Free Account</Link>
              <Link href="/courses" className={styles.secondaryBtn}>Browse Courses</Link>
            </div>
          </div>
        </section>
      </main>

      <footer className={styles.footer}>
        <div className={styles.footerContent}>
          <div className={styles.footerBrand}>
            <span className={styles.footerLogo}>🛡️ CyberLab Academy</span>
            <p className={styles.footerDesc}>Master cybersecurity through hands-on practice.</p>
          </div>
          <div className={styles.footerLinks}>
            <div className={styles.footerCol}>
              <h4>Platform</h4>
              <Link href="/courses">Courses</Link>
              <Link href="/labs">Labs</Link>
              <Link href="/leaderboard">Leaderboard</Link>
            </div>
            <div className={styles.footerCol}>
              <h4>Company</h4>
              <span>About</span>
              <span>Privacy</span>
              <span>Terms</span>
            </div>
          </div>
        </div>
        <div className={styles.footerBottom}>
          <p>© 2026 CyberLab Academy. All rights reserved.</p>
        </div>
      </footer>
    </div>
  )
}
