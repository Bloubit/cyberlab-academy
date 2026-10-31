import Head from 'next/head'
import Link from 'next/link'
import { useState, useEffect } from 'react'
import { useAuth } from '../context/AuthContext'
import api from '../lib/api'

const styles = {
  container: { minHeight: '100vh', background: '#0a0a1a', color: '#fff' },
  header: {
    background: 'rgba(15, 15, 35, 0.95)', borderBottom: '1px solid rgba(255,255,255,0.06)',
    padding: '0 2rem', position: 'sticky', top: 0, zIndex: 100,
  },
  headerContent: {
    maxWidth: 1200, margin: '0 auto', display: 'flex', alignItems: 'center',
    justifyContent: 'space-between', height: 64,
  },
  logo: { fontSize: '1.25rem', fontWeight: 700, color: '#00d4ff', textDecoration: 'none', display: 'flex', alignItems: 'center', gap: '0.5rem' },
  nav: { display: 'flex', gap: '1.5rem', alignItems: 'center' },
  navLink: { color: '#a0aec0', textDecoration: 'none', fontSize: '0.9rem', fontWeight: 500 },
  main: { maxWidth: 1200, margin: '0 auto', padding: '3rem 2rem' },
  title: { fontSize: '2.5rem', fontWeight: 700, textAlign: 'center', margin: '0 0 0.5rem' },
  subtitle: { color: '#a0aec0', textAlign: 'center', fontSize: '1.1rem', marginBottom: '3rem' },
  plansGrid: { display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '1.5rem' },
  planCard: (isPopular) => ({
    background: isPopular ? 'linear-gradient(135deg, rgba(0,212,255,0.08), rgba(153,69,255,0.08))' : 'rgba(255,255,255,0.03)',
    border: isPopular ? '1px solid rgba(0,212,255,0.3)' : '1px solid rgba(255,255,255,0.08)',
    borderRadius: 16, padding: '2rem', position: 'relative', transition: 'all 0.2s',
  }),
  popularBadge: {
    position: 'absolute', top: -12, left: '50%', transform: 'translateX(-50%)',
    background: 'linear-gradient(90deg, #00d4ff, #9945ff)', padding: '0.3rem 1rem',
    borderRadius: 20, fontSize: '0.75rem', fontWeight: 600, color: '#fff',
  },
  planName: { fontSize: '1.25rem', fontWeight: 600, marginBottom: '0.5rem' },
  planPrice: { fontSize: '2.5rem', fontWeight: 700, color: '#00d4ff', marginBottom: '0.25rem' },
  planPeriod: { color: '#718096', fontSize: '0.85rem', marginBottom: '1.5rem' },
  planDesc: { color: '#a0aec0', fontSize: '0.9rem', marginBottom: '1.5rem', lineHeight: 1.5 },
  featuresList: { listStyle: 'none', padding: 0, margin: '0 0 1.5rem', display: 'flex', flexDirection: 'column', gap: '0.75rem' },
  featureItem: { display: 'flex', alignItems: 'center', gap: '0.5rem', color: '#e2e8f0', fontSize: '0.9rem' },
  ctaBtn: (isPopular) => ({
    width: '100%', padding: '0.85rem', border: 'none', borderRadius: 8,
    background: isPopular ? 'linear-gradient(90deg, #00d4ff, #9945ff)' : 'rgba(255,255,255,0.1)',
    color: '#fff', fontSize: '1rem', fontWeight: 600, cursor: 'pointer',
    transition: 'all 0.2s',
  }),
  loadingState: { display: 'flex', alignItems: 'center', justifyContent: 'center', minHeight: 400 },
  spinner: { width: 32, height: 32, border: '3px solid rgba(0,212,255,0.2)', borderTopColor: '#00d4ff', borderRadius: '50%', animation: 'spin 0.6s linear infinite' },
}

export default function Pricing() {
  const { isAuthenticated } = useAuth()
  const [plans, setPlans] = useState([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const fetchPlans = async () => {
      try {
        const res = await api.getPlans()
        if (res.success) setPlans(res.data || [])
      } catch (e) {
        console.error('Failed to fetch plans:', e)
      } finally {
        setLoading(false)
      }
    }
    fetchPlans()
  }, [])

  const handleSubscribe = (plan) => {
    if (!isAuthenticated) {
      window.location.href = `/login?redirect=/pricing`
      return
    }
    window.location.href = `/subscribe?planId=${plan.id}&slug=${plan.slug}`
  }

  if (loading) {
    return (
      <div style={styles.container}>
        <div style={styles.loadingState}>
          <div style={styles.spinner} />
        </div>
      </div>
    )
  }

  return (
    <div style={styles.container}>
      <Head><title>Pricing - CyberLab Academy</title></Head>
      
      <header style={styles.header}>
        <div style={styles.headerContent}>
          <Link href="/" style={styles.logo}>🛡️ CyberLab Academy</Link>
          <nav style={styles.nav}>
            <Link href="/courses" style={styles.navLink}>Courses</Link>
            <Link href="/labs" style={styles.navLink}>Labs</Link>
            <Link href="/leaderboard" style={styles.navLink}>Leaderboard</Link>
            {isAuthenticated ? (
              <Link href="/dashboard" style={{ ...styles.navLink, color: '#00d4ff' }}>Dashboard</Link>
            ) : (
              <Link href="/login" style={{ ...styles.navLink, color: '#00d4ff' }}>Sign In</Link>
            )}
          </nav>
        </div>
      </header>

      <main style={styles.main}>
        <h1 style={styles.title}>Choose Your Plan</h1>
        <p style={styles.subtitle}>Unlock your full cybersecurity learning potential</p>

        <div style={styles.plansGrid}>
          {plans.map(plan => {
            const isPopular = plan.is_popular
            const isFree = plan.price === 0 || plan.slug === 'free'
            const features = Array.isArray(plan.features)
              ? plan.features
              : typeof plan.features === 'object'
                ? Object.values(plan.features).map(v => typeof v === 'string' ? v : JSON.stringify(v))
                : []

            return (
              <div key={plan.id} style={styles.planCard(isPopular)}>
                {isPopular && <div style={styles.popularBadge}>Most Popular</div>}
                <div style={styles.planName}>{plan.name}</div>
                <div style={styles.planPrice}>
                  {isFree ? 'Free' : `$${plan.price}`}
                </div>
                {!isFree && <div style={styles.planPeriod}>per {plan.billing_period}</div>}
                <div style={styles.planDesc}>{plan.description}</div>

                <ul style={styles.featuresList}>
                  {features.map((feature, i) => (
                    <li key={i} style={styles.featureItem}>
                      <span style={{ color: '#00ff88' }}>✓</span> {feature}
                    </li>
                  ))}
                  {plan.trial_days > 0 && (
                    <li style={styles.featureItem}>
                      <span style={{ color: '#00ff88' }}>✓</span> {plan.trial_days}-day free trial
                    </li>
                  )}
                </ul>

                <button style={styles.ctaBtn(isPopular)} onClick={() => handleSubscribe(plan)}>
                  {isFree ? 'Get Started Free' : 'Subscribe Now'}
                </button>
              </div>
            )
          })}
        </div>
      </main>
    </div>
  )
}
