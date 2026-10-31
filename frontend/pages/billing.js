import Head from 'next/head'
import Link from 'next/link'
import { useState, useEffect, useCallback } from 'react'
import { useAuth } from '../context/AuthContext'
import { useRouter } from 'next/router'
import api from '../lib/api'

const s = {
  container: { minHeight: '100vh', background: '#0a0a1a', color: '#fff' },
  header: {
    background: 'rgba(15,15,35,0.95)', borderBottom: '1px solid rgba(255,255,255,0.06)',
    padding: '0 2rem', display: 'flex', alignItems: 'center', justifyContent: 'space-between', height: 64,
  },
  main: { maxWidth: 900, margin: '0 auto', padding: '3rem 2rem' },
  pageTitle: { fontSize: '1.75rem', fontWeight: 700, margin: '0 0 0.25rem' },
  pageSubtitle: { color: '#a0aec0', fontSize: '0.9rem', marginBottom: '2rem' },
  subscriptionCard: {
    background: 'linear-gradient(135deg, rgba(0,212,255,0.06), rgba(153,69,255,0.06))',
    border: '1px solid rgba(0,212,255,0.2)', borderRadius: 16, padding: '1.5rem', marginBottom: '1.5rem',
  },
  cardTitle: { fontSize: '1.1rem', fontWeight: 600, marginBottom: '1rem' },
  subInfo: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: '1rem' },
  subName: { fontSize: '1.2rem', fontWeight: 600, color: '#00d4ff' },
  subStatus: (status) => ({
    padding: '0.3rem 0.75rem', borderRadius: 20, fontSize: '0.75rem', fontWeight: 600,
    background: status === 'active' ? 'rgba(0,255,136,0.15)' : status === 'pending' ? 'rgba(255,170,0,0.15)' : 'rgba(255,71,87,0.15)',
    color: status === 'active' ? '#00ff88' : status === 'pending' ? '#ffaa00' : '#ff4757',
  }),
  subDetail: { color: '#a0aec0', fontSize: '0.85rem', margin: '0.25rem 0' },
  cancelBtn: {
    padding: '0.6rem 1.25rem', border: '1px solid #ff4757', borderRadius: 8,
    background: 'transparent', color: '#ff4757', cursor: 'pointer', fontSize: '0.85rem', fontWeight: 500,
  },
  tableCard: {
    background: 'rgba(255,255,255,0.03)', border: '1px solid rgba(255,255,255,0.08)',
    borderRadius: 16, overflow: 'hidden',
  },
  table: { width: '100%', borderCollapse: 'collapse' },
  th: { padding: '0.75rem 1rem', textAlign: 'left', borderBottom: '1px solid rgba(255,255,255,0.06)', color: '#718096', fontSize: '0.8rem', fontWeight: 600 },
  td: { padding: '0.75rem 1rem', borderBottom: '1px solid rgba(255,255,255,0.04)', fontSize: '0.85rem' },
  paymentBadge: (status) => ({
    padding: '0.2rem 0.5rem', borderRadius: 4, fontSize: '0.75rem', fontWeight: 600,
    background: status === 'completed' ? 'rgba(0,255,136,0.15)' : status === 'pending' ? 'rgba(255,170,0,0.15)' : 'rgba(255,71,87,0.15)',
    color: status === 'completed' ? '#00ff88' : status === 'pending' ? '#ffaa00' : '#ff4757',
  }),
  noData: { textAlign: 'center', padding: '3rem', color: '#718096' },
  loadingState: { display: 'flex', alignItems: 'center', justifyContent: 'center', minHeight: 400 },
  spinner: { width: 32, height: 32, border: '3px solid rgba(0,212,255,0.2)', borderTopColor: '#00d4ff', borderRadius: '50%', animation: 'spin 0.6s linear infinite' },
  nav: { display: 'flex', gap: '1.5rem', alignItems: 'center' },
  navLink: { color: '#a0aec0', textDecoration: 'none', fontSize: '0.9rem' },
  link: { color: '#00d4ff', textDecoration: 'none' },
}

export default function Billing() {
  const { user, isAuthenticated, loading: authLoading } = useAuth()
  const router = useRouter()

  const [subscriptions, setSubscriptions] = useState([])
  const [payments, setPayments] = useState([])
  const [loading, setLoading] = useState(true)
  const [cancelling, setCancelling] = useState(false)
  const [msg, setMsg] = useState(null)

  useEffect(() => {
    if (!authLoading && !isAuthenticated) router.push('/login?redirect=/billing')
  }, [authLoading, isAuthenticated])

  const fetchData = useCallback(async () => {
    if (!isAuthenticated) return
    setLoading(true)
    try {
      const [subRes, payRes] = await Promise.allSettled([
        api.getSubscriptions(),
        api.getPaymentHistory(),
      ])
      if (subRes.status === 'fulfilled' && subRes.value?.success) setSubscriptions(subRes.value.data || [])
      if (payRes.status === 'fulfilled' && payRes.value?.success) setPayments(Array.isArray(payRes.value.data) ? payRes.value.data : payRes.value.data?.data || [])
    } catch (e) {
      console.error(e)
    } finally {
      setLoading(false)
    }
  }, [isAuthenticated])

  useEffect(() => {
    if (!authLoading) fetchData()
  }, [authLoading, fetchData])

  const handleCancel = async () => {
    if (!confirm('Are you sure you want to cancel your subscription?')) return
    setCancelling(true)
    try {
      const res = await api.cancelSubscription()
      if (res.success) {
        setMsg({ type: 'success', text: 'Subscription cancelled' })
        fetchData()
      } else {
        setMsg({ type: 'error', text: res.message })
      }
    } catch (err) {
      setMsg({ type: 'error', text: err.message })
    } finally {
      setCancelling(false)
    }
  }

  useEffect(() => {
    if (msg) { const t = setTimeout(() => setMsg(null), 4000); return () => clearTimeout(t) }
  }, [msg])

  if (authLoading || loading) {
    return <div style={s.container}><div style={s.loadingState}><div style={s.spinner} /></div></div>
  }

  const activeSub = subscriptions.find(s => s.status === 'active')
  const recentPayments = payments.slice(0, 10)

  return (
    <div style={s.container}>
      <Head><title>Billing - CyberLab Academy</title></Head>
      <header style={s.header}>
        <Link href="/" style={{ color: '#00d4ff', fontSize: '1.2rem', fontWeight: 700, textDecoration: 'none' }}>🛡️ CyberLab</Link>
        <nav style={s.nav}>
          <Link href="/dashboard" style={s.navLink}>Dashboard</Link>
          <Link href="/profile" style={s.navLink}>Profile</Link>
        </nav>
      </header>

      <main style={s.main}>
        <h1 style={s.pageTitle}>Billing & Subscription</h1>
        <p style={s.pageSubtitle}>Manage your subscription and payment history</p>

        {msg && (
          <div style={{
            padding: '0.75rem 1rem', borderRadius: 8, marginBottom: '1rem',
            background: msg.type === 'success' ? 'rgba(0,255,136,0.15)' : 'rgba(255,71,87,0.15)',
            border: `1px solid ${msg.type === 'success' ? 'rgba(0,255,136,0.3)' : 'rgba(255,71,87,0.3)'}`,
            color: msg.type === 'success' ? '#00ff88' : '#ff4757', fontSize: '0.9rem',
          }}>
            {msg.type === 'success' ? '✅ ' : '⚠️ '}{msg.text}
          </div>
        )}

        {activeSub ? (
          <div style={s.subscriptionCard}>
            <div style={s.cardTitle}>Current Subscription</div>
            <div style={s.subInfo}>
              <div>
                <div style={s.subName}>{activeSub.plan?.name || 'Active Plan'}</div>
                <div style={s.subDetail}>
                  {activeSub.current_period_start && `Started: ${new Date(activeSub.current_period_start).toLocaleDateString()}`}
                  {activeSub.current_period_end && ` • Renews: ${new Date(activeSub.current_period_end).toLocaleDateString()}`}
                </div>
                <div style={s.subDetail}>
                  Billed via: {activeSub.payment_method === 'click' ? 'Click.uz' : activeSub.payment_method === 'payze' ? 'Payze.io' : 'Card'}
                </div>
              </div>
              <div style={{ display: 'flex', alignItems: 'center', gap: '1rem' }}>
                <span style={s.subStatus('active')}>Active</span>
                <button style={s.cancelBtn} onClick={handleCancel} disabled={cancelling}>
                  {cancelling ? 'Cancelling...' : 'Cancel'}
                </button>
              </div>
            </div>
          </div>
        ) : (
          <div style={{ ...s.subscriptionCard, border: '1px solid rgba(255,255,255,0.08)', background: 'rgba(255,255,255,0.02)' }}>
            <div style={s.cardTitle}>No Active Subscription</div>
            <p style={{ color: '#a0aec0', fontSize: '0.9rem', margin: 0 }}>
              You don't have an active subscription.{' '}
              <Link href="/pricing" style={s.link}>View plans →</Link>
            </p>
          </div>
        )}

        <h2 style={{ fontSize: '1.2rem', fontWeight: 600, marginBottom: '1rem', marginTop: '2rem' }}>Payment History</h2>

        <div style={s.tableCard}>
          {recentPayments.length > 0 ? (
            <table style={s.table}>
              <thead>
                <tr>
                  <th style={s.th}>Date</th>
                  <th style={s.th}>Description</th>
                  <th style={s.th}>Amount</th>
                  <th style={s.th}>Method</th>
                  <th style={s.th}>Status</th>
                </tr>
              </thead>
              <tbody>
                {recentPayments.map(p => (
                  <tr key={p.id}>
                    <td style={s.td}>{new Date(p.created_at).toLocaleDateString()}</td>
                    <td style={s.td}>{p.description || 'Subscription payment'}</td>
                    <td style={s.td}>{p.currency} {p.amount}</td>
                    <td style={s.td}>
                      {p.payment_method === 'click' ? 'Click.uz' :
                       p.payment_method === 'payze' ? 'Payze.io' :
                       p.payment_method || 'Card'}
                    </td>
                    <td style={s.td}><span style={s.paymentBadge(p.status)}>{p.status}</span></td>
                  </tr>
                ))}
              </tbody>
            </table>
          ) : (
            <div style={s.noData}>
              <div style={{ fontSize: '2rem', marginBottom: '0.5rem' }}>💳</div>
              <p>No payment history yet</p>
            </div>
          )}
        </div>
      </main>
    </div>
  )
}
