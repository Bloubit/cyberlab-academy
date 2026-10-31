import Head from 'next/head'
import { useState, useEffect, useCallback } from 'react'
import { useAuth } from '../../context/AuthContext'
import api from '../../lib/api'
import styles from '../../styles/Admin.module.css'
import { Sidebar, LoadingSkeleton, AuthPrompt, DifficultyBadge, AccessDenied } from '../../components/AdminLayout'

export default function AdminLabs() {
  const { user, isAuthenticated, loading: authLoading } = useAuth()
  const [labs, setLabs] = useState([])
  const [meta, setMeta] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(1)
  const [filterPublished, setFilterPublished] = useState('')
  const [showModal, setShowModal] = useState(null)
  const [editLab, setEditLab] = useState(null)
  const [viewLab, setViewLab] = useState(null)
  const [challenges, setChallenges] = useState([])
  const [formData, setFormData] = useState({
    title: '', description: '', difficulty: 'beginner', lab_type: 'challenge',
    estimated_time_minutes: 60, points: 100, is_published: false, is_featured: false, os_type: ''
  })
  const [challengeForm, setChallengeForm] = useState({ title: '', description: '', difficulty: 'beginner', points: 100, flag: '' })
  const [showChallengeModal, setShowChallengeModal] = useState(false)
  const [actionLoading, setActionLoading] = useState(false)
  const [successMsg, setSuccessMsg] = useState(null)

  const fetchLabs = useCallback(async () => {
    setLoading(true); setError(null)
    try {
      const params = { page, per_page: 15, sort_by: 'created_at', sort_order: 'desc' }
      if (search) params.search = search
      if (filterPublished === 'published') params.is_published = true
      else if (filterPublished === 'draft') params.is_published = false
      const res = await api.getAdminLabs(params)
      if (res.success) { setLabs(res.data || []); setMeta(res.meta) }
    } catch (err) { setError(err.message) }
    finally { setLoading(false) }
  }, [page, search, filterPublished])

  useEffect(() => {
    if (!authLoading && isAuthenticated) fetchLabs()
    else if (!authLoading) setLoading(false)
  }, [authLoading, isAuthenticated, fetchLabs])

  const openCreateModal = () => {
    setEditLab(null)
    setFormData({ title: '', description: '', difficulty: 'beginner', lab_type: 'challenge', estimated_time_minutes: 60, points: 100, is_published: false, is_featured: false, os_type: '' })
    setShowModal('edit')
  }

  const openEditModal = (lab) => {
    setEditLab(lab)
    setFormData({
      title: lab.title || '',
      description: lab.description || '',
      difficulty: lab.difficulty || 'beginner',
      lab_type: lab.lab_type || 'challenge',
      estimated_time_minutes: lab.estimated_time_minutes || 60,
      points: lab.points || 100,
      is_published: lab.is_published || false,
      is_featured: lab.is_featured || false,
      os_type: lab.os_type || '',
    })
    setShowModal('edit')
  }

  const openViewModal = async (lab) => {
    setViewLab(lab)
    try {
      const res = await api.getAdminChallenges(lab.id)
      if (res.success) setChallenges(res.data || [])
    } catch (err) {
      setChallenges([])
    }
    setShowModal('view')
  }

  const handleSave = async () => {
    setActionLoading(true)
    try {
      const res = editLab ? await api.updateAdminLab(editLab.id, formData) : await api.createAdminLab(formData)
      if (res.success) {
        setSuccessMsg(editLab ? 'Lab updated!' : 'Lab created!')
        setShowModal(null); fetchLabs()
      }
    } catch (err) { setError(err.message) }
    finally { setActionLoading(false) }
  }

  const handleTogglePublish = async (lab) => {
    try {
      const res = await api.toggleLabPublish(lab.id)
      if (res.success) { setSuccessMsg(res.message); fetchLabs() }
    } catch (err) { setError(err.message) }
  }

  const handleDelete = async (lab) => {
    if (!confirm(`Delete "${lab.title}"?`)) return
    try {
      const res = await api.deleteAdminLab(lab.id)
      if (res.success) { setSuccessMsg('Lab deleted!'); fetchLabs() }
    } catch (err) { setError(err.message) }
  }

  const handleCreateChallenge = async () => {
    if (!viewLab || !challengeForm.title) return
    setActionLoading(true)
    try {
      const res = await api.createAdminChallenge(viewLab.id, challengeForm)
      if (res.success) {
        setSuccessMsg('Challenge created!')
        setChallengeForm({ title: '', description: '', difficulty: 'beginner', points: 100, flag: '' })
        setShowChallengeModal(false)
        const updated = await api.getAdminChallenges(viewLab.id)
        if (updated.success) setChallenges(updated.data || [])
      }
    } catch (err) { setError(err.message) }
    finally { setActionLoading(false) }
  }

  useEffect(() => {
    if (successMsg) { const t = setTimeout(() => setSuccessMsg(null), 3000); return () => clearTimeout(t) }
  }, [successMsg])

  if (authLoading) return <LoadingSkeleton />
  if (!isAuthenticated) return <AuthPrompt />
  if (!user?.is_admin) return <AccessDenied />

  return (
    <div className={styles.adminContainer}>
      <Head><title>Lab Management - CyberLab Academy</title></Head>
      <Sidebar currentPath="/admin/labs" />

      <main className={styles.mainContent}>
        {error && <div className={`${styles.alert} ${styles.alertError}`}>⚠️ {error}</div>}
        {successMsg && <div className={`${styles.alert} ${styles.alertSuccess}`}>✅ {successMsg}</div>}

        <div className={styles.pageHeader}>
          <div>
            <h1 className={styles.pageTitle}>Lab Management</h1>
            <p className={styles.pageSubtitle}>Create and manage cybersecurity labs</p>
          </div>
          <button className={styles.btnPrimary} onClick={openCreateModal}>+ New Lab</button>
        </div>

        <div className={styles.tableCard}>
          <div className={styles.tableHeader}>
            <h3 className={styles.tableTitle}>Labs ({meta?.total || 0})</h3>
            <div className={styles.tableActions}>
              <input type="text" className={styles.searchInput} placeholder="Search labs..."
                value={search} onChange={e => { setSearch(e.target.value); setPage(1) }} />
              <select className={styles.filterSelect} value={filterPublished}
                onChange={e => { setFilterPublished(e.target.value); setPage(1) }}>
                <option value="">All</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
              </select>
            </div>
          </div>

          {loading ? (
            <div className={styles.loadingState} style={{ minHeight: '200px' }}>
              <div className={styles.spinner}></div>
            </div>
          ) : labs.length === 0 ? (
            <div className={styles.emptyState}>
              <div className={styles.emptyIcon}>💻</div>
              <h3>No labs yet</h3>
              <p>Create your first lab to get started.</p>
              <button className={styles.btnPrimary} onClick={openCreateModal}>+ Create Lab</button>
            </div>
          ) : (
            <>
              <table className={styles.table}>
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Difficulty</th>
                    <th>Type</th>
                    <th>Points</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {labs.map(l => (
                    <tr key={l.id}>
                      <td style={{ fontWeight: 600 }}>{l.title}</td>
                      <td><DifficultyBadge level={l.difficulty} /></td>
                      <td style={{ color: '#a0aec0', fontSize: '0.8rem' }}>{l.lab_type}</td>
                      <td style={{ color: '#ffaa00' }}>{l.points} XP</td>
                      <td style={{ color: '#a0aec0', fontSize: '0.8rem' }}>{l.estimated_time_minutes || '-'}m</td>
                      <td>
                        <span className={`${styles.statusBadge} ${l.is_published ? styles.statusPublished : styles.statusDraft}`}>
                          {l.is_published ? 'Published' : 'Draft'}
                        </span>
                      </td>
                      <td>
                        <button className={`${styles.actionBtn} ${styles.btnPrimary}`} onClick={() => openViewModal(l)}>View</button>
                        <button className={`${styles.actionBtn} ${styles.btnSecondary}`} onClick={() => openEditModal(l)}>Edit</button>
                        <button className={`${styles.actionBtn} ${styles.btnSuccess}`} onClick={() => handleTogglePublish(l)}>
                          {l.is_published ? 'Unpub' : 'Pub'}
                        </button>
                        <button className={`${styles.actionBtn} ${styles.btnDanger}`} onClick={() => handleDelete(l)}>Del</button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>

              {meta && meta.last_page > 1 && (
                <div className={styles.pagination}>
                  <button className={styles.pageBtn} disabled={page === 1} onClick={() => setPage(p => p - 1)}>←</button>
                  <span className={styles.pageInfo}>Page {meta.current_page} of {meta.last_page}</span>
                  <button className={styles.pageBtn} disabled={page === meta.last_page} onClick={() => setPage(p => p + 1)}>→</button>
                </div>
              )}
            </>
          )}
        </div>
      </main>

      {/* Create/Edit Lab Modal */}
      {showModal === 'edit' && (
        <div className={styles.modalOverlay} onClick={() => setShowModal(null)}>
          <div className={styles.modal} style={{ maxWidth: '650px' }} onClick={e => e.stopPropagation()}>
            <div className={styles.modalHeader}>
              <h3 className={styles.modalTitle}>{editLab ? 'Edit Lab' : 'Create Lab'}</h3>
              <button className={styles.modalClose} onClick={() => setShowModal(null)}>×</button>
            </div>
            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Title *</label>
              <input className={styles.formInput} placeholder="Lab title"
                value={formData.title} onChange={e => setFormData({ ...formData, title: e.target.value })} />
            </div>
            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Description</label>
              <textarea className={styles.formInput} placeholder="Lab description..."
                value={formData.description} onChange={e => setFormData({ ...formData, description: e.target.value })} />
            </div>
            <div className={styles.formRow}>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Difficulty</label>
                <select className={styles.formSelect} value={formData.difficulty}
                  onChange={e => setFormData({ ...formData, difficulty: e.target.value })}>
                  <option value="beginner">Beginner</option>
                  <option value="intermediate">Intermediate</option>
                  <option value="advanced">Advanced</option>
                  <option value="expert">Expert</option>
                </select>
              </div>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Type</label>
                <select className={styles.formSelect} value={formData.lab_type}
                  onChange={e => setFormData({ ...formData, lab_type: e.target.value })}>
                  <option value="challenge">Challenge</option>
                  <option value="sandbox">Sandbox</option>
                  <option value="ctf">CTF</option>
                  <option value="practice">Practice</option>
                </select>
              </div>
            </div>
            <div className={styles.formRow}>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Points</label>
                <input type="number" className={styles.formInput}
                  value={formData.points} onChange={e => setFormData({ ...formData, points: Number(e.target.value) })} />
              </div>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Est. Time (min)</label>
                <input type="number" className={styles.formInput}
                  value={formData.estimated_time_minutes} onChange={e => setFormData({ ...formData, estimated_time_minutes: Number(e.target.value) })} />
              </div>
            </div>
            <div className={styles.formGroup}>
              <label className={styles.formLabel}>OS Type</label>
              <input className={styles.formInput} placeholder="linux, windows, etc."
                value={formData.os_type} onChange={e => setFormData({ ...formData, os_type: e.target.value })} />
            </div>
            <div className={styles.formCheckbox}>
              <input type="checkbox" checked={formData.is_published}
                onChange={e => setFormData({ ...formData, is_published: e.target.checked })} />
              <span>Published</span>
            </div>
            <div className={styles.formCheckbox}>
              <input type="checkbox" checked={formData.is_featured}
                onChange={e => setFormData({ ...formData, is_featured: e.target.checked })} />
              <span>Featured</span>
            </div>
            <div className={styles.formActions}>
              <button className={styles.btnSecondary} onClick={() => setShowModal(null)}>Cancel</button>
              <button className={styles.btnPrimary} onClick={handleSave} disabled={actionLoading || !formData.title}>
                {actionLoading ? 'Saving...' : editLab ? 'Update Lab' : 'Create Lab'}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* View Lab Modal */}
      {showModal === 'view' && viewLab && (
        <div className={styles.modalOverlay} onClick={() => setShowModal(null)}>
          <div className={styles.modal} style={{ maxWidth: '700px' }} onClick={e => e.stopPropagation()}>
            <div className={styles.modalHeader}>
              <h3 className={styles.modalTitle}>{viewLab.title}</h3>
              <button className={styles.modalClose} onClick={() => setShowModal(null)}>×</button>
            </div>
            <div style={{ marginBottom: '1.5rem', paddingBottom: '1rem', borderBottom: '1px solid rgba(255,255,255,0.08)' }}>
              <div style={{ display: 'flex', gap: '1.5rem', flexWrap: 'wrap' }}>
                <span style={{ color: '#a0aec0', fontSize: '0.85rem' }}>Difficulty: <DifficultyBadge level={viewLab.difficulty} /></span>
                <span style={{ color: '#a0aec0', fontSize: '0.85rem' }}>Points: <strong style={{ color: '#ffaa00' }}>{viewLab.points}</strong></span>
                <span style={{ color: '#a0aec0', fontSize: '0.85rem' }}>Time: <strong>{viewLab.estimated_time_minutes || '-'}m</strong></span>
                <span style={{ color: '#a0aec0', fontSize: '0.85rem' }}>Type: <strong>{viewLab.lab_type}</strong></span>
              </div>
              {viewLab.description && <p style={{ color: '#a0aec0', fontSize: '0.85rem', marginTop: '0.75rem' }}>{viewLab.description}</p>}
            </div>

            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '1rem' }}>
              <h4 style={{ color: '#ffffff', fontSize: '1rem' }}>Challenges ({challenges.length})</h4>
              <button className={styles.btnPrimary} onClick={() => setShowChallengeModal(true)}>+ Add Challenge</button>
            </div>

            {challenges.length === 0 ? (
              <div className={styles.emptyState} style={{ padding: '1rem' }}>
                <p style={{ fontSize: '0.85rem' }}>No challenges yet. Add one!</p>
              </div>
            ) : (
              <table className={styles.table}>
                <thead>
                  <tr><th>Title</th><th>Difficulty</th><th>Points</th><th>Active</th></tr>
                </thead>
                <tbody>
                  {challenges.map(ch => (
                    <tr key={ch.id}>
                      <td style={{ fontWeight: 500, fontSize: '0.85rem' }}>{ch.title}</td>
                      <td><DifficultyBadge level={ch.difficulty} /></td>
                      <td style={{ color: '#ffaa00' }}>{ch.points}</td>
                      <td>
                        <span className={`${styles.statusBadge} ${ch.is_active ? styles.statusActive : styles.statusInactive}`}>
                          {ch.is_active ? 'Active' : 'Inactive'}
                        </span>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            )}
            <div className={styles.formActions}>
              <button className={styles.btnSecondary} onClick={() => setShowModal(null)}>Close</button>
            </div>
          </div>
        </div>
      )}

      {/* Add Challenge Modal */}
      {showChallengeModal && (
        <div className={styles.modalOverlay} onClick={() => setShowChallengeModal(false)}>
          <div className={styles.modal} onClick={e => e.stopPropagation()}>
            <div className={styles.modalHeader}>
              <h3 className={styles.modalTitle}>Add Challenge</h3>
              <button className={styles.modalClose} onClick={() => setShowChallengeModal(false)}>×</button>
            </div>
            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Title *</label>
              <input className={styles.formInput} placeholder="Challenge title"
                value={challengeForm.title} onChange={e => setChallengeForm({ ...challengeForm, title: e.target.value })} />
            </div>
            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Description</label>
              <textarea className={styles.formInput} placeholder="Challenge description..."
                value={challengeForm.description} onChange={e => setChallengeForm({ ...challengeForm, description: e.target.value })} />
            </div>
            <div className={styles.formRow}>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Difficulty</label>
                <select className={styles.formSelect} value={challengeForm.difficulty}
                  onChange={e => setChallengeForm({ ...challengeForm, difficulty: e.target.value })}>
                  <option value="beginner">Beginner</option>
                  <option value="intermediate">Intermediate</option>
                  <option value="advanced">Advanced</option>
                  <option value="expert">Expert</option>
                </select>
              </div>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Points</label>
                <input type="number" className={styles.formInput} value={challengeForm.points}
                  onChange={e => setChallengeForm({ ...challengeForm, points: Number(e.target.value) })} />
              </div>
            </div>
            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Flag</label>
              <input className={styles.formInput} placeholder="e.g., FLAG{...}"
                value={challengeForm.flag} onChange={e => setChallengeForm({ ...challengeForm, flag: e.target.value })} />
            </div>
            <div className={styles.formActions}>
              <button className={styles.btnSecondary} onClick={() => setShowChallengeModal(false)}>Cancel</button>
              <button className={styles.btnPrimary} onClick={handleCreateChallenge}
                disabled={actionLoading || !challengeForm.title}>
                {actionLoading ? 'Creating...' : 'Create Challenge'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}


