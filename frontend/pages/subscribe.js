import Head from 'next/head'
import Link from 'next/link'
import { useRouter } from 'next/router'
import { useState, useEffect } from 'react'
import { useAuth } from '../context/AuthContext'
import api from '../lib/api'

const s = {
  container: { minHeight: '100vh', background: '#0a0a1a', color: '#fff' },
  main: { maxWidth: 600, margin: '0 auto', padding: '3rem 2rem' },
  header: {
    background: 'rgba(15,15,35,0.95)', borderBottom: '1px solid rgba(255,255,255,0.06)',
    padding: '0 2rem', display: 'flex', alignItems: 'center', height: 64,
  },
  card: { background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.08)', borderRadius: 16, padding: '2rem' },
  title: { fontSize: '1.5rem', fontWeight: 700, marginBottom: '0.75rem' },
  planInfo: { background: 'rgba(0,212,255,0.05)', border: '1px solid rgba(0,212,255,0.15)', borderRadius: 12, padding: '1.25rem', marginBottom: '1.5rem' },
  planName: { fontSize: '1.1rem', fontWeight: 600, marginBottom: '0.25rem' },
  planPrice: { fontSize: '1.5rem', fontWeight: 700, color: '#00d4ff' },
  label: { color: '#a0aec0', fontSize: '0.85rem', fontWeight: 500, marginBottom: '0.5rem', display: 'block' },
  methodBtn: (active) => ({
    width: '100%', padding: '1rem', marginBottom: '0.75rem', border: active ? '2px solid #00d4ff' : '1px solid rgba(255,255,255,0.1)',
    borderRadius: 12, background: active ? 'rgba(0,212,255,0.08)' : 'rgba(255,255,255,0.02)',
    color: '#fff', fontSize: '1rem', cursor: 'pointer', textAlign: 'left',
    display: 'flex', alignItems: 'center', gap: '1rem', transition: 'all 0.2s',
  }),
  methodIcon: { fontSize: '1.5rem', width: 40, textAlign: 'center' },
  methodName: { fontWeight: 600 },
  methodDesc: { color: '#a0aec0', fontSize: '0.8rem' },
  submitBtn: {
    width: '100%', padding: '1rem', border: 'none', borderRadius: 12,
    background: 'linear-gradient(90deg, #00d4ff, #9945ff)', color: '#fff',
    fontSize: '1rem', fontWeight: 600, cursor: 'pointer', marginTop: '1rem',
  },
  submitBtnDisabled: { opacity: 0.5, cursor: 'not-allowed' },
  backLink: { color: '#00d4ff', textDecoration: 'none', fontSize: '0.9rem', display: 'inline-block', marginBottom: '2rem' },
  alert: (type) => ({
    padding: '0.75rem 1rem', borderRadius: 8, marginBottom: '1rem',
    background: type === 'success' ? 'rgba(0,255,136,0.15)' : 'rgba(255,71,87,0.15)',
    border: `1px solid ${type === 'success' ? 'rgba(0,255,136,0.3)' : 'rgba(255,71,87,0.3)'}`,
    color: type === 'success' ? '#00ff88' : '#ff4757', fontSize: '0.9rem',
  }),
  loadingState: { display: 'flex', alignItems: 'center', justifyContent: 'center', minHeight: 400 },
  spinner: { width: 32, height: 32, border: '3px solid rgba(0,212,255,0.2)', borderTopColor: '#00d4ff', borderRadius: '50%', animation: 'spin 0.6s linear infinite' },
}

export default function Subscribe() {
  const router = useRouter()
  const { isAuthenticated, loading: authLoading } = useAuth()
  const { planId, slug } = router.query

  const [plan, setPlan] = useState(null)
  const [loading, setLoading] = useState(true)
  const [paymentMethod, setPaymentMethod] = useState('click')
  const [submitting, setSubmitting] = useState(false)
  const [error, setError] = useState(null)
  const [successUrl, setSuccessUrl] = useState(null)

  useEffect(() => {
    if (!planId || authLoading) return

    const fetchPlan = async () => {
      try {
        const res = await api.getPlans()
        if (res.success) {
          const found = (res.data || []).find(p => String(p.id) === String(planId))
          setPlan(found || null)
        }
      } catch (e) {
        setError('Failed to load plan')
      } finally {
        setLoading(false)
      }
    }
    fetchPlan()
  }, [planId, authLoading])

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push(`/login?redirect=/subscribe?planId=${planId}`)
    }
  }, [authLoading, isAuthenticated, planId])

  const handleSubscribe = async () => {
    if (!plan || !isAuthenticated) return

    setSubmitting(true)
    setError(null)
    try {
      const res = await api.subscribe(plan.id, paymentMethod)
      if (res.success) {
        const checkoutUrl = res.data?.checkout_url
        if (checkoutUrl) {
          // Redirect to payment gateway
          window.location.href = checkoutUrl
        } else {
          setSuccessUrl('/billing')
        }
      } else {
        setError(res.message || 'Subscription failed')
      }
    } catch (err) {
      setError(err.message || 'Subscription failed')
    } finally {
      setSubmitting(false)
    }
  }

  if (loading || authLoading) {
    return <div style={s.container}><div style={s.loadingState}><div style={s.spinner} /></div></div>
  }

  if (!plan) {
    return (
      <div style={s.container}>
        <div style={s.main}>
          <h1 style={s.title}>Plan Not Found</h1>
          <Link href="/pricing" style={s.backLink}>← Back to Pricing</Link>
        </div>
      </div>
    )
  }

  return (
    <div style={s.container}>
      <Head><title>Subscribe - {plan.name} - CyberLab Academy</title></Head>
      <header style={s.header}>
        <Link href="/" style={{ color: '#00d4ff', fontSize: '1.2rem', fontWeight: 700, textDecoration: 'none' }}>
          🛡️ CyberLab Academy
        </Link>
      </header>

      <main style={s.main}>
        <Link href="/pricing" style={s.backLink}>← Back to Plans</Link>

        {error && <div style={s.alert('error')}>⚠️ {error}</div>}
        {successUrl && <div style={s.alert('success')}>✅ Subscription created! <Link href={successUrl}>View billing</Link></div>}

        <div style={s.card}>
          <h1 style={s.title}>Complete Your Subscription</h1>

          <div style={s.planInfo}>
            <div style={s.planName}>{plan.name}</div>
            <div style={s.planPrice}>
              {plan.price === 0 ? 'Free' : `$${plan.price}`}
              {plan.price > 0 && <span style={{ fontSize: '0.9rem', color: '#a0aec0', fontWeight: 400 }}>/{plan.billing_period}</span>}
            </div>
            <div style={{ color: '#a0aec0', fontSize: '0.85rem', marginTop: '0.5rem' }}>{plan.description}</div>
          </div>

          {plan.price > 0 && (
            <>
              <label style={s.label}>Choose Payment Method</label>

              <button style={s.methodBtn(paymentMethod === 'click')} onClick={() => setPaymentMethod('click')}>
                <span style={s.methodIcon}>💳</span>
                <div>
                  <div style={s.methodName}>Click.uz</div>
                  <div style={s.methodDesc}>Pay with Uzcard / Humo cards</div>
                </div>
              </button>

              <button style={s.methodBtn(paymentMethod === 'payze')} onClick={() => setPaymentMethod('payze')}>
                <span style={s.methodIcon}>🌐</span>
                <div>
                  <div style={s.methodName}>Payze.io</div>
                  <div style={s.methodDesc}>Pay with Visa / Mastercard (International)</div>
                </div>
              </button>
            </>
          )}

          <button
            style={{ ...s.submitBtn, ...(submitting ? s.submitBtnDisabled : {}) }}
            onClick={handleSubscribe}
            disabled={submitting}
          >
            {submitting ? 'Processing...' : plan.price === 0 ? 'Get Started Free' : `Pay $${plan.price}`}
          </button>
        </div>
      </main>
    </div>
  )
}
