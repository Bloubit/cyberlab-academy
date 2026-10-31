import Head from 'next/head'
import { useState, useEffect, useCallback } from 'react'
import { useAuth } from '../../context/AuthContext'
import api from '../../lib/api'
import styles from '../../styles/Admin.module.css'
import { Sidebar, LoadingSkeleton, AuthPrompt, AccessDenied } from '../../components/AdminLayout'

const STATUS_CONFIG = {
  running: { label: 'Running', color: '#00ff88', bg: 'rgba(0,255,136,0.15)' },
  stopped: { label: 'Stopped', color: '#ff4757', bg: 'rgba(255,71,87,0.15)' },
  completed: { label: 'Completed', color: '#00d4ff', bg: 'rgba(0,212,255,0.15)' },
  expired: { label: 'Expired', color: '#ffaa00', bg: 'rgba(255,170,0,0.15)' },
  pending: { label: 'Pending', color: '#ffaa00', bg: 'rgba(255,170,0,0.15)' },
  error: { label: 'Error', color: '#ff4757', bg: 'rgba(255,71,87,0.15)' },
}

export default function AdminInstances() {
  const { user, isAuthenticated, loading: authLoading } = useAuth()
  const [instances, setInstances] = useState([])
  const [meta, setMeta] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [statusFilter, setStatusFilter] = useState('')
  const [page, setPage] = useState(1)
  const [autoRefresh, setAutoRefresh] = useState(true)
  const [selectedInstance, setSelectedInstance] = useState(null)
  const [showModal, setShowModal] = useState(false)
  const [actionLoading, setActionLoading] = useState(null)
  const [feedback, setFeedback] = useState(null)

  // Auto-dismiss feedback
  useEffect(() => {
    if (feedback) {
      const t = setTimeout(() => setFeedback(null), 4000)
      return () => clearTimeout(t)
    }
  }, [feedback])

  const fetchInstances = useCallback(async () => {
    setError(null)
    try {
      const params = { page, per_page: 20, sort_by: 'created_at', sort_order: 'desc' }
      if (statusFilter) params.status = statusFilter
      const res = await api.getAdminInstances(params)
      if (res.success) {
        setInstances(res.data?.data || res.data || [])
        setMeta(res.meta)
      }
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }, [page, statusFilter])

  useEffect(() => {
    if (!authLoading && isAuthenticated) fetchInstances()
    else if (!authLoading) setLoading(false)
  }, [authLoading, isAuthenticated, fetchInstances])

  // Auto-refresh every 10 seconds
  useEffect(() => {
    if (!autoRefresh || !isAuthenticated) return
    const interval = setInterval(() => { fetchInstances() }, 10000)
    return () => clearInterval(interval)
  }, [autoRefresh, isAuthenticated, fetchInstances])

  // Stop instance with confirmation
  const handleStop = async (inst, fromModal = false) => {
    const userName = inst.user?.name || inst.user?.email || 'this user'
    if (!confirm(`Are you sure you want to stop instance #${inst.id} for "${userName}"?\n\nThe user will lose access to the lab environment.`)) return

    setActionLoading(inst.id)
    setError(null)
    try {
      const res = await api.stopAdminInstance(inst.id)
      if (res.success) {
        setFeedback({ type: 'success', message: `✅ Instance #${inst.id} stopped successfully` })
        // Update local state
        setInstances(prev => prev.map(i =>
          i.id === inst.id ? { ...i, status: 'stopped', stopped_at: new Date().toISOString() } : i
        ))
        if (selectedInstance?.id === inst.id) {
          setSelectedInstance(prev => ({ ...prev, status: 'stopped', stopped_at: new Date().toISOString() }))
        }
      }
    } catch (err) {
      setFeedback({ type: 'error', message: `⚠️ Failed to stop: ${err.message}` })
    } finally {
      setActionLoading(null)
      if (fromModal) setShowModal(false)
    }
  }

  const viewInstance = (inst) => {
    setSelectedInstance(inst)
    setShowModal(true)
  }

  const formatDuration = (started, stopped) => {
    if (!started) return '-'
    const start = new Date(started)
    const end = stopped ? new Date(stopped) : new Date()
    const hours = Math.floor((end - start) / 3600000)
    const minutes = Math.floor(((end - start) % 3600000) / 60000)
    if (hours > 0) return `${hours}h ${minutes}m`
    return `${minutes}m`
  }

  const isExpired = (expiresAt) => {
    if (!expiresAt) return false
    return new Date(expiresAt) < new Date()
  }

  const isRunning = (inst) => inst.status === 'running' || inst.status === 'pending'

  if (authLoading) return <LoadingSkeleton />
  if (!isAuthenticated) return <AuthPrompt />
  if (!user?.is_admin) return <AccessDenied />

  const statuses = ['', 'running', 'stopped', 'completed', 'expired', 'pending', 'error']

  return (
    <div className={styles.adminContainer}>
      <Head><title>Lab Instances - CyberLab Academy Admin</title></Head>
      <Sidebar currentPath="/admin/instances" />

      <main className={styles.mainContent}>
        {error && <div className={`${styles.alert} ${styles.alertError}`}>⚠️ {error}</div>}
        {feedback && (
          <div className={`${styles.alert} ${feedback.type === 'success' ? styles.alertSuccess : styles.alertError}`}>
            {feedback.message}
          </div>
        )}

        <div className={styles.pageHeader}>
          <div>
            <h1 className={styles.pageTitle}>Lab Instances</h1>
            <p className={styles.pageSubtitle}>Monitor and manage all lab environments</p>
          </div>
          <label className={styles.autoRefreshToggle}>
            <input type="checkbox" checked={autoRefresh}
              onChange={e => setAutoRefresh(e.target.checked)} />
            <span>Auto-refresh (10s)</span>
          </label>
        </div>

        {/* Summary Cards */}
        {!loading && instances.length > 0 && (
          <div className={styles.statsGrid} style={{ gridTemplateColumns: 'repeat(auto-fill, minmax(130px, 1fr))', marginBottom: '1.5rem' }}>
            {statuses.filter(s => s).map(status => {
              const count = instances.filter(i => i.status === status).length
              const cfg = STATUS_CONFIG[status] || {}
              return (
                <div key={status} className={styles.statCard} style={{ cursor: 'pointer', border: statusFilter === status ? `2px solid ${cfg.color}` : '' }}
                  onClick={() => { setStatusFilter(statusFilter === status ? '' : status); setPage(1) }}>
                  <div className={styles.statInfo} style={{ textAlign: 'center' }}>
                    <h3 style={{ color: cfg.color }}>{count}</h3>
                    <p style={{ color: cfg.color, opacity: 0.8, fontSize: '0.75rem' }}>{cfg.label}</p>
                  </div>
                </div>
              )
            })}
          </div>
        )}

        <div className={styles.tableCard}>
          <div className={styles.tableHeader}>
            <h3 className={styles.tableTitle}>
              Instances {meta?.total !== undefined ? `(${meta.total})` : ''}
              {autoRefresh && <span className={styles.liveDot}> 🟢 Live</span>}
            </h3>
            <div className={styles.tableActions}>
              <select className={styles.filterSelect} value={statusFilter}
                onChange={e => { setStatusFilter(e.target.value); setPage(1) }}>
                <option value="">All Status</option>
                <option value="running">Running</option>
                <option value="stopped">Stopped</option>
                <option value="completed">Completed</option>
                <option value="expired">Expired</option>
                <option value="pending">Pending</option>
                <option value="error">Error</option>
              </select>
            </div>
          </div>

          {loading ? (
            <div className={styles.loadingState} style={{ minHeight: '200px' }}>
              <div className={styles.spinner}></div>
            </div>
          ) : instances.length === 0 ? (
            <div className={styles.emptyState}>
              <div className={styles.emptyIcon}>🖥️</div>
              <h3>No instances found</h3>
              <p>No lab instances match your filters.</p>
            </div>
          ) : (
            <>
              <table className={styles.table}>
                <thead>
                  <tr>
                    <th>User</th>
                    <th>Lab</th>
                    <th>Status</th>
                    <th>Duration</th>
                    <th>Expires</th>
                    <th>Started</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {instances.map(inst => {
                    const cfg = STATUS_CONFIG[inst.status] || STATUS_CONFIG.pending
                    const expired = isExpired(inst.expires_at)
                    const canStop = isRunning(inst)
                    return (
                      <tr key={inst.id}>
                        <td style={{ fontWeight: 600 }}>
                          {inst.user?.name || 'Unknown'}
                          <div style={{ color: '#718096', fontSize: '0.75rem' }}>{inst.user?.email}</div>
                        </td>
                        <td style={{ color: '#a0aec0' }}>{inst.lab?.title || 'N/A'}</td>
                        <td>
                          <span className={styles.statusBadge}
                            style={{ background: cfg.bg, color: cfg.color, border: `1px solid ${cfg.color}40` }}>
                            {expired && inst.status === 'running' ? '⚠️ ' : ''}{cfg.label}
                          </span>
                        </td>
                        <td style={{ color: '#a0aec0', fontSize: '0.85rem' }}>
                          {formatDuration(inst.started_at, inst.stopped_at)}
                        </td>
                        <td style={{ color: expired ? '#ff4757' : '#a0aec0', fontSize: '0.8rem' }}>
                          {inst.expires_at ? new Date(inst.expires_at).toLocaleTimeString() : '-'}
                          {expired && inst.status === 'running' && <span style={{ color: '#ff4757', marginLeft: '0.25rem' }}>(expired)</span>}
                        </td>
                        <td style={{ color: '#718096', fontSize: '0.8rem' }}>
                          {inst.created_at ? new Date(inst.created_at).toLocaleDateString() : '-'}
                        </td>
                        <td>
                          <div style={{ display: 'flex', gap: '0.25rem', flexWrap: 'wrap' }}>
                            <button className={`${styles.actionBtn} ${styles.btnPrimary}`}
                              onClick={() => viewInstance(inst)}>
                              View
                            </button>
                            {canStop && (
                              <button className={`${styles.actionBtn} ${styles.btnDanger}`}
                                onClick={() => handleStop(inst)}
                                disabled={actionLoading === inst.id}
                                title="Stop this instance">
                                {actionLoading === inst.id ? '...' : 'Stop'}
                              </button>
                            )}
                          </div>
                        </td>
                      </tr>
                    )
                  })}
                </tbody>
              </table>

              {meta && meta.last_page > 1 && (
                <div className={styles.pagination}>
                  <button className={styles.pageBtn} disabled={page === 1}
                    onClick={() => setPage(p => p - 1)}>← Prev</button>
                  <span className={styles.pageInfo}>Page {meta.current_page} of {meta.last_page}</span>
                  <button className={styles.pageBtn} disabled={page === meta.last_page}
                    onClick={() => setPage(p => p + 1)}>Next →</button>
                </div>
              )}
            </>
          )}
        </div>
      </main>

      {/* Instance Detail Modal */}
      {showModal && selectedInstance && (
        <div className={styles.modalOverlay} onClick={() => setShowModal(false)}>
          <div className={styles.modal} style={{ maxWidth: '600px' }} onClick={e => e.stopPropagation()}>
            <div className={styles.modalHeader}>
              <h3 className={styles.modalTitle}>
                Instance #{selectedInstance.id}
                {isRunning(selectedInstance) && (
                  <span className={styles.statusBadge}
                    style={{ marginLeft: '0.75rem', background: 'rgba(0,255,136,0.15)', color: '#00ff88', border: '1px solid rgba(0,255,136,0.4)', fontSize: '0.7rem', verticalAlign: 'middle' }}>
                    🟢 Active
                  </span>
                )}
              </h3>
              <button className={styles.modalClose} onClick={() => setShowModal(false)}>×</button>
            </div>

            <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
              <div className={styles.formRow}>
                <div className={styles.formGroup}>
                  <label className={styles.formLabel}>User</label>
                  <div style={{ color: '#fff', fontWeight: 600 }}>{selectedInstance.user?.name || 'Unknown'}</div>
                  <div style={{ color: '#718096', fontSize: '0.8rem' }}>{selectedInstance.user?.email}</div>
                </div>
                <div className={styles.formGroup}>
                  <label className={styles.formLabel}>Lab</label>
                  <div style={{ color: '#fff' }}>{selectedInstance.lab?.title || 'N/A'}</div>
                  <div style={{ color: '#718096', fontSize: '0.8rem' }}>{selectedInstance.lab?.type || selectedInstance.lab?.difficulty_level || ''}</div>
                </div>
              </div>

              <div className={styles.formRow}>
                <div className={styles.formGroup}>
                  <label className={styles.formLabel}>Status</label>
                  <div style={{ color: STATUS_CONFIG[selectedInstance.status]?.color || '#a0aec0', fontWeight: 600 }}>
                    {STATUS_CONFIG[selectedInstance.status]?.label || selectedInstance.status}
                  </div>
                </div>
                <div className={styles.formGroup}>
                  <label className={styles.formLabel}>Container ID</label>
                  <div style={{ color: '#a0aec0', fontFamily: 'monospace', fontSize: '0.8rem' }}>{selectedInstance.container_id || 'N/A'}</div>
                </div>
              </div>

              <div className={styles.formRow}>
                <div className={styles.formGroup}>
                  <label className={styles.formLabel}>Started</label>
                  <div style={{ color: '#a0aec0' }}>
                    {selectedInstance.started_at ? new Date(selectedInstance.started_at).toLocaleString() : '-'}
                  </div>
                </div>
                <div className={styles.formGroup}>
                  <label className={styles.formLabel}>Expires</label>
                  <div style={{ color: isExpired(selectedInstance.expires_at) ? '#ff4757' : '#a0aec0' }}>
                    {selectedInstance.expires_at ? new Date(selectedInstance.expires_at).toLocaleString() : '-'}
                  </div>
                </div>
              </div>

              <div className={styles.formRow}>
                <div className={styles.formGroup}>
                  <label className={styles.formLabel}>Duration</label>
                  <div style={{ color: '#a0aec0' }}>
                    {formatDuration(selectedInstance.started_at, selectedInstance.stopped_at)}
                  </div>
                </div>
                <div className={styles.formGroup}>
                  <label className={styles.formLabel}>Reset Count</label>
                  <div style={{ color: '#a0aec0' }}>{selectedInstance.reset_count || 0}</div>
                </div>
              </div>

              {selectedInstance.connection_info && (
                <div className={styles.formGroup}>
                  <label className={styles.formLabel}>Connection Info</label>
                  <div style={{ background: 'rgba(0,0,0,0.3)', borderRadius: 8, padding: '0.75rem', fontFamily: 'monospace', fontSize: '0.8rem', color: '#00d4ff', maxHeight: '150px', overflow: 'auto' }}>
                    {JSON.stringify(selectedInstance.connection_info, null, 2)}
                  </div>
                </div>
              )}
            </div>

            <div className={styles.formActions} style={{ justifyContent: 'space-between' }}>
              <div>
                {isRunning(selectedInstance) && (
                  <button className={`${styles.actionBtn} ${styles.btnDanger}`}
                    onClick={() => handleStop(selectedInstance, true)}
                    disabled={actionLoading === selectedInstance.id}
                    style={{ padding: '0.6rem 1.2rem', fontWeight: 600 }}>
                    {actionLoading === selectedInstance.id ? '⏳ Stopping...' : '⏹ Stop Instance'}
                  </button>
                )}
              </div>
              <button className={styles.btnSecondary} onClick={() => setShowModal(false)}>Close</button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}
