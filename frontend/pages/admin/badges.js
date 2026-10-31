import Head from 'next/head'
import { useState, useEffect, useCallback } from 'react'
import { useAuth } from '../../context/AuthContext'
import api from '../../lib/api'
import styles from '../../styles/Admin.module.css'
import { Sidebar, LoadingSkeleton, AuthPrompt, AccessDenied } from '../../components/AdminLayout'

export default function AdminBadges() {
  const { user, isAuthenticated, loading: authLoading } = useAuth()
  const [badges, setBadges] = useState([])
  const [meta, setMeta] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [search, setSearch] = useState('')
  const [filterActive, setFilterActive] = useState('')
  const [showModal, setShowModal] = useState(false)
  const [editBadge, setEditBadge] = useState(null)
  const [formData, setFormData] = useState({
    name: '', description: '', icon: '🎖️', category: 'achievement',
    rarity: 'common', xp_reward: 50, is_active: true, is_hidden: false,
    requirements: { type: 'total_xp', count: 100 }
  })
  const [actionLoading, setActionLoading] = useState(false)
  const [successMsg, setSuccessMsg] = useState(null)

  const fetchBadges = useCallback(async () => {
    setLoading(true); setError(null)
    try {
      const params = { per_page: 50 }
      if (search) params.search = search
      if (filterActive === 'active') params.is_active = true
      else if (filterActive === 'inactive') params.is_active = false

      const res = await api.getAdminBadges(params)
      if (res.success) {
        setBadges(res.data || [])
        setMeta(res.meta)
      }
    } catch (err) { setError(err.message) }
    finally { setLoading(false) }
  }, [search, filterActive])

  useEffect(() => {
    if (!authLoading && isAuthenticated) fetchBadges()
    else if (!authLoading) setLoading(false)
  }, [authLoading, isAuthenticated, fetchBadges])

  const openCreate = () => {
    setEditBadge(null)
    setFormData({ name: '', description: '', icon: '🎖️', category: 'achievement', rarity: 'common', xp_reward: 50, is_active: true, is_hidden: false, requirements: { type: 'total_xp', count: 100 } })
    setShowModal(true)
  }

  const openEdit = (badge) => {
    setEditBadge(badge)
    setFormData({
      name: badge.name || '',
      description: badge.description || '',
      icon: badge.icon || '🎖️',
      category: badge.category || 'achievement',
      rarity: badge.rarity || 'common',
      xp_reward: badge.xp_reward || 0,
      is_active: badge.is_active !== false,
      is_hidden: badge.is_hidden || false,
      requirements: badge.requirements || { type: 'total_xp', count: 100 },
    })
    setShowModal(true)
  }

  const handleSave = async () => {
    if (!formData.name) return
    setActionLoading(true)
    try {
      const res = editBadge
        ? await api.updateAdminBadge(editBadge.id, formData)
        : await api.createAdminBadge(formData)
      if (res.success) {
        setSuccessMsg(editBadge ? 'Badge updated!' : 'Badge created!')
        setShowModal(false)
        fetchBadges()
      }
    } catch (err) { setError(err.message) }
    finally { setActionLoading(false) }
  }

  const handleDelete = async (badge) => {
    if (!confirm(`Delete badge "${badge.name}"?`)) return
    try {
      const res = await api.deleteAdminBadge(badge.id)
      if (res.success) {
        setSuccessMsg('Badge deleted!')
        fetchBadges()
      }
    } catch (err) { setError(err.message) }
  }

  useEffect(() => {
    if (successMsg) { const t = setTimeout(() => setSuccessMsg(null), 3000); return () => clearTimeout(t) }
  }, [successMsg])

  if (authLoading) return <LoadingSkeleton />
  if (!isAuthenticated) return <AuthPrompt />
  if (!user?.is_admin) return <AccessDenied />

  const iconOptions = ['🎖️', '🏆', '⭐', '💎', '🔥', '⚡', '🛡️', '🔱', '👑', '🌍', '🧩', '🎯', '🏅', '🥇', '🥈', '🥉']
  const rarityColors = { common: '#a0aec0', uncommon: '#00ff88', rare: '#00d4ff', epic: '#9945ff', legendary: '#ffaa00' }

  return (
    <div className={styles.adminContainer}>
      <Head><title>Badges - CyberLab Academy Admin</title></Head>
      <Sidebar currentPath="/admin/badges" />

      <main className={styles.mainContent}>
        {error && <div className={`${styles.alert} ${styles.alertError}`}>⚠️ {error}</div>}
        {successMsg && <div className={`${styles.alert} ${styles.alertSuccess}`}>✅ {successMsg}</div>}

        <div className={styles.pageHeader}>
          <div>
            <h1 className={styles.pageTitle}>Badges</h1>
            <p className={styles.pageSubtitle}>Manage achievement badges</p>
          </div>
          <button className={styles.btnPrimary} onClick={openCreate}>+ New Badge</button>
        </div>

        <div className={styles.tableCard}>
          <div className={styles.tableHeader}>
            <h3 className={styles.tableTitle}>All Badges ({meta?.total || badges.length})</h3>
            <div className={styles.tableActions}>
              <input type="text" className={styles.searchInput} placeholder="Search badges..."
                value={search} onChange={e => { setSearch(e.target.value) }} />
              <select className={styles.filterSelect} value={filterActive}
                onChange={e => setFilterActive(e.target.value)}>
                <option value="">All</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>

          {loading ? (
            <div className={styles.loadingState} style={{ minHeight: '200px' }}>
              <div className={styles.spinner}></div>
            </div>
          ) : badges.length === 0 ? (
            <div className={styles.emptyState}>
              <div className={styles.emptyIcon}>🎖️</div>
              <h3>No badges yet</h3>
              <p>Create achievement badges for students.</p>
              <button className={styles.btnPrimary} onClick={openCreate}>+ Create Badge</button>
            </div>
          ) : (
            <table className={styles.table}>
              <thead>
                <tr><th>Icon</th><th>Name</th><th>Category</th><th>Rarity</th><th>XP</th><th>Earned</th><th>Status</th><th>Actions</th></tr>
              </thead>
              <tbody>
                {badges.map(b => (
                  <tr key={b.id}>
                    <td style={{ fontSize: '1.5rem' }}>{b.icon || '🎖️'}</td>
                    <td style={{ fontWeight: 600 }}>{b.name}</td>
                    <td style={{ color: '#a0aec0', fontSize: '0.85rem' }}>{b.category || '-'}</td>
                    <td>
                      <span style={{ color: rarityColors[b.rarity] || '#a0aec0', fontWeight: 600, fontSize: '0.8rem' }}>
                        {b.rarity?.charAt(0).toUpperCase() + b.rarity?.slice(1) || 'Common'}
                      </span>
                    </td>
                    <td style={{ color: '#ffaa00' }}>{b.xp_reward || 0}</td>
                    <td style={{ color: '#00d4ff' }}>{b.earned_count || 0}</td>
                    <td>
                      <span className={`${styles.statusBadge} ${b.is_active ? styles.statusActive : styles.statusInactive}`}>
                        {b.is_active ? 'Active' : 'Inactive'}
                      </span>
                    </td>
                    <td>
                      <button className={`${styles.actionBtn} ${styles.btnSecondary}`} onClick={() => openEdit(b)}>Edit</button>
                      <button className={`${styles.actionBtn} ${styles.btnDanger}`} onClick={() => handleDelete(b)}>Del</button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          )}
        </div>
      </main>

      {/* Create/Edit Modal */}
      {showModal && (
        <div className={styles.modalOverlay} onClick={() => setShowModal(false)}>
          <div className={styles.modal} style={{ maxWidth: '600px' }} onClick={e => e.stopPropagation()}>
            <div className={styles.modalHeader}>
              <h3 className={styles.modalTitle}>{editBadge ? 'Edit Badge' : 'Create Badge'}</h3>
              <button className={styles.modalClose} onClick={() => setShowModal(false)}>×</button>
            </div>
            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Name *</label>
              <input className={styles.formInput} placeholder="Badge name"
                value={formData.name} onChange={e => setFormData({ ...formData, name: e.target.value })} />
            </div>
            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Description</label>
              <textarea className={styles.formInput} placeholder="Badge description..."
                value={formData.description} onChange={e => setFormData({ ...formData, description: e.target.value })} />
            </div>
            <div className={styles.formRow}>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Icon</label>
                <select className={styles.formSelect} value={formData.icon}
                  onChange={e => setFormData({ ...formData, icon: e.target.value })}>
                  {iconOptions.map(icon => <option key={icon} value={icon}>{icon}</option>)}
                </select>
              </div>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Category</label>
                <select className={styles.formSelect} value={formData.category}
                  onChange={e => setFormData({ ...formData, category: e.target.value })}>
                  <option value="achievement">Achievement</option>
                  <option value="skill">Skill</option>
                  <option value="competition">Competition</option>
                  <option value="course">Course</option>
                  <option value="special">Special</option>
                </select>
              </div>
            </div>
            <div className={styles.formRow}>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Rarity</label>
                <select className={styles.formSelect} value={formData.rarity}
                  onChange={e => setFormData({ ...formData, rarity: e.target.value })}>
                  <option value="common">Common</option>
                  <option value="uncommon">Uncommon</option>
                  <option value="rare">Rare</option>
                  <option value="epic">Epic</option>
                  <option value="legendary">Legendary</option>
                </select>
              </div>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>XP Reward</label>
                <input type="number" className={styles.formInput} value={formData.xp_reward}
                  onChange={e => setFormData({ ...formData, xp_reward: Number(e.target.value) })} />
              </div>
            </div>

            {/* Requirements */}
            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Requirement Type</label>
              <select className={styles.formSelect} value={formData.requirements?.type || 'total_xp'}
                onChange={e => setFormData({ ...formData, requirements: { ...formData.requirements, type: e.target.value } })}>
                <option value="total_xp">Total XP</option>
                <option value="labs_completed">Labs Completed</option>
                <option value="courses_completed">Courses Completed</option>
                <option value="streak">Daily Streak</option>
                <option value="flags_found">Flags Found</option>
              </select>
            </div>
            <div className={styles.formGroup}>
              <label className={styles.formLabel}>
                {formData.requirements?.type === 'total_xp' ? 'Amount (XP)' :
                 formData.requirements?.type === 'streak' ? 'Days' : 'Count'}
              </label>
              <input type="number" className={styles.formInput}
                value={formData.requirements?.count || formData.requirements?.amount || 0}
                onChange={e => {
                  const key = formData.requirements?.type === 'total_xp' ? 'amount' : 'count'
                  setFormData({ ...formData, requirements: { ...formData.requirements, [key]: Number(e.target.value) } })
                }} />
            </div>

            <div className={styles.formCheckbox}>
              <input type="checkbox" checked={formData.is_active}
                onChange={e => setFormData({ ...formData, is_active: e.target.checked })} />
              <span>Active</span>
            </div>
            <div className={styles.formCheckbox}>
              <input type="checkbox" checked={formData.is_hidden}
                onChange={e => setFormData({ ...formData, is_hidden: e.target.checked })} />
              <span>Hidden (not shown until earned)</span>
            </div>

            <div className={styles.formActions}>
              <button className={styles.btnSecondary} onClick={() => setShowModal(false)}>Cancel</button>
              <button className={styles.btnPrimary} onClick={handleSave} disabled={actionLoading || !formData.name}>
                {actionLoading ? 'Saving...' : editBadge ? 'Update Badge' : 'Create Badge'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}


