import Head from 'next/head'
import Link from 'next/link'
import { useState, useEffect } from 'react'
import { useAuth } from '../../context/AuthContext'
import api from '../../lib/api'
import styles from '../../styles/Admin.module.css'
import { Sidebar, AccessDenied } from '../../components/AdminLayout'

export default function AdminDashboard() {
  const { user, isAuthenticated, loading: authLoading } = useAuth()
  const [data, setData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    if (authLoading) return
    if (!isAuthenticated) { setLoading(false); return }

    const fetchData = async () => {
      try {
        const res = await api.getAdminDashboard()
        if (res.success) setData(res.data)
      } catch (err) {
        setError(err.message || 'Failed to load dashboard')
      } finally {
        setLoading(false)
      }
    }
    fetchData()
  }, [authLoading, isAuthenticated])

  if (authLoading || loading) {
    return (
      <div className={styles.adminContainer}>
        <Sidebar currentPath="/admin" />
        <main className={styles.mainContent}>
          <div className={styles.loadingState}>
            <div className={styles.spinner}></div>
            <p>Loading dashboard...</p>
          </div>
        </main>
      </div>
    )
  }

  if (!isAuthenticated) {
    return (
      <div className={styles.adminContainer}>
        <Sidebar currentPath="/admin" />
        <main className={styles.mainContent}>
          <div className={styles.emptyState}>
            <div className={styles.emptyIcon}>🔐</div>
            <h3>Please Sign In</h3>
            <p>You need admin access to view this page.</p>
            <Link href="/login" className={styles.btnPrimary}>Sign In</Link>
          </div>
        </main>
      </div>
    )
  }

  if (!user?.is_admin) {
    return (
      <div className={styles.adminContainer}>
        <Head><title>Access Denied - CyberLab Academy</title></Head>
        <AccessDenied />
      </div>
    )
  }

  const stats = data?.stats
  const recentUsers = data?.recent_users || []
  const recentTransactions = data?.recent_transactions || []

  return (
    <div className={styles.adminContainer}>
      <Head><title>Admin Dashboard - CyberLab Academy</title></Head>
      <Sidebar currentPath="/admin" />
      
      <main className={styles.mainContent}>
        {error && (
          <div className={`${styles.alert} ${styles.alertError}`}>
            <span>⚠️</span> {error}
          </div>
        )}

        <div className={styles.pageHeader}>
          <div>
            <h1 className={styles.pageTitle}>Admin Dashboard</h1>
            <p className={styles.pageSubtitle}>Overview of your platform</p>
          </div>
          <div style={{ display: 'flex', gap: '0.75rem' }}>
            <span className={`${styles.statusBadge} ${styles.statusActive}`}>🟢 Live</span>
          </div>
        </div>

        {/* Stats Grid */}
        <div className={styles.statsGrid}>
          <StatCard icon="👥" value={stats?.users?.total || 0} label="Total Users" 
            meta={`${stats?.users?.active || 0} active`} />
          <StatCard icon="📚" value={stats?.courses?.total || 0} label="Total Courses"
            meta={`${stats?.courses?.published || 0} published`} />
          <StatCard icon="💻" value={stats?.labs?.total || 0} label="Total Labs"
            meta={`${stats?.labs?.published || 0} published`} />
          <StatCard icon="🎖️" value={stats?.badges?.active || 0} label="Active Badges"
            meta={`${stats?.badges?.total || 0} total`} />
          <StatCard icon="📝" value={stats?.content?.quizzes || 0} label="Quizzes" />
          <StatCard icon="✅" value={`${stats?.content?.success_rate || 0}%`} label="Success Rate"
            meta={`${stats?.content?.correct_submissions || 0}/${stats?.content?.submissions || 0} correct`} />
          <StatCard icon="🚫" value={stats?.users?.banned || 0} label="Banned Users" />
          <StatCard icon="💰" value={stats?.revenue?.total ? `$${stats.revenue.total}` : '$0'} label="Revenue" />
        </div>

        {/* Recent Activity */}
        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1.5rem' }}>
          <div className={styles.tableCard}>
            <div className={styles.tableHeader}>
              <h3 className={styles.tableTitle}>Recent Registrations</h3>
            </div>
            {recentUsers.length > 0 ? (
              <div className={styles.recentList} style={{ padding: '0.75rem' }}>
                {recentUsers.map(u => (
                  <div key={u.id} className={styles.recentItem}>
                    <span className={styles.recentIcon}>👤</span>
                    <div className={styles.recentInfo}>
                      <div className={styles.recentName}>{u.name}</div>
                      <div className={styles.recentMeta}>{u.email}</div>
                    </div>
                    <span className={`${styles.statusBadge} ${u.is_active ? styles.statusActive : u.is_banned ? styles.statusBanned : styles.statusInactive}`}>
                      {u.is_active ? 'Active' : u.is_banned ? 'Banned' : 'Inactive'}
                    </span>
                    <span className={styles.recentDate}>{new Date(u.created_at).toLocaleDateString()}</span>
                  </div>
                ))}
              </div>
            ) : (
              <div className={styles.emptyState}><p>No recent users</p></div>
            )}
          </div>

          <div className={styles.tableCard}>
            <div className={styles.tableHeader}>
              <h3 className={styles.tableTitle}>Recent Transactions</h3>
            </div>
            {recentTransactions.length > 0 ? (
              <div className={styles.recentList} style={{ padding: '0.75rem' }}>
                {recentTransactions.map(tx => (
                  <div key={tx.id} className={styles.recentItem}>
                    <span className={styles.recentIcon}>💳</span>
                    <div className={styles.recentInfo}>
                      <div className={styles.recentName}>{tx.user_name}</div>
                      <div className={styles.recentMeta}>{tx.currency} {tx.amount}</div>
                    </div>
                    <span className={`${styles.statusBadge} ${tx.status === 'completed' ? styles.statusActive : styles.statusDraft}`}>
                      {tx.status}
                    </span>
                    <span className={styles.recentDate}>{new Date(tx.created_at).toLocaleDateString()}</span>
                  </div>
                ))}
              </div>
            ) : (
              <div className={styles.emptyState}><p>No recent transactions</p></div>
            )}
          </div>
        </div>
      </main>
    </div>
  )
}function StatCard({ icon, value, label, meta }) {
  return (
    <div className={styles.statCard}>
      <div className={styles.statIcon}>{icon}</div>
      <div className={styles.statInfo}>
        <h3>{value}</h3>
        <p>{label}</p>
        {meta && <span className={styles.statMuted}>{meta}</span>}
      </div>
    </div>
  )
}
