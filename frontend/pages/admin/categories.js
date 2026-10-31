import Head from 'next/head'
import { useState, useEffect, useCallback } from 'react'
import { useAuth } from '../../context/AuthContext'
import api from '../../lib/api'
import styles from '../../styles/Admin.module.css'
import { Sidebar, LoadingSkeleton, AuthPrompt, AccessDenied } from '../../components/AdminLayout'

export default function AdminCategories() {
  const { user, isAuthenticated, loading: authLoading } = useAuth()
  const [categories, setCategories] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [showModal, setShowModal] = useState(false)
  const [editCat, setEditCat] = useState(null)
  const [formData, setFormData] = useState({ name: '', description: '', icon: '', color: '#00d4ff', is_active: true })
  const [actionLoading, setActionLoading] = useState(false)
  const [successMsg, setSuccessMsg] = useState(null)

  const fetchCategories = useCallback(async () => {
    setLoading(true)
    try {
      const res = await api.getAdminCategories()
      if (res.success) setCategories(res.data || [])
    } catch (err) { setError(err.message) }
    finally { setLoading(false) }
  }, [])

  useEffect(() => {
    if (!authLoading && isAuthenticated) fetchCategories()
    else if (!authLoading) setLoading(false)
  }, [authLoading, isAuthenticated, fetchCategories])

  const openCreate = () => {
    setEditCat(null)
    setFormData({ name: '', description: '', icon: '📁', color: '#00d4ff', is_active: true })
    setShowModal(true)
  }

  const openEdit = (cat) => {
    setEditCat(cat)
    setFormData({
      name: cat.name || '',
      description: cat.description || '',
      icon: cat.icon || '📁',
      color: cat.color || '#00d4ff',
      is_active: cat.is_active !== false,
    })
    setShowModal(true)
  }

  const handleSave = async () => {
    if (!formData.name) return
    setActionLoading(true)
    try {
      const res = editCat
        ? await api.updateAdminCategory(editCat.id, formData)
        : await api.createAdminCategory(formData)
      if (res.success) {
        setSuccessMsg(editCat ? 'Category updated!' : 'Category created!')
        setShowModal(false)
        fetchCategories()
      }
    } catch (err) { setError(err.message) }
    finally { setActionLoading(false) }
  }

  const handleDelete = async (cat) => {
    if (!confirm(`Delete category "${cat.name}"?`)) return
    try {
      const res = await api.deleteAdminCategory(cat.id)
      if (res.success) {
        setSuccessMsg('Category deleted!')
        fetchCategories()
      } else {
        setError(res.message || 'Cannot delete')
      }
    } catch (err) { setError(err.message) }
  }

  useEffect(() => {
    if (successMsg) { const t = setTimeout(() => setSuccessMsg(null), 3000); return () => clearTimeout(t) }
  }, [successMsg])

  if (authLoading) return <LoadingSkeleton />
  if (!isAuthenticated) return <AuthPrompt />
  if (!user?.is_admin) return <AccessDenied />

  const iconOptions = ['📁', '🔒', '🌐', '🛡️', '🔍', '💻', '📡', '🔑', '🕸️', '🤖', '📱', '☁️', '🧠', '⚡', '🎯']

  return (
    <div className={styles.adminContainer}>
      <Head><title>Categories - CyberLab Academy Admin</title></Head>
      <Sidebar currentPath="/admin/categories" />

      <main className={styles.mainContent}>
        {error && <div className={`${styles.alert} ${styles.alertError}`}>⚠️ {error}</div>}
        {successMsg && <div className={`${styles.alert} ${styles.alertSuccess}`}>✅ {successMsg}</div>}

        <div className={styles.pageHeader}>
          <div>
            <h1 className={styles.pageTitle}>Categories</h1>
            <p className={styles.pageSubtitle}>Manage course categories</p>
          </div>
          <button className={styles.btnPrimary} onClick={openCreate}>+ New Category</button>
        </div>

        <div className={styles.tableCard}>
          <div className={styles.tableHeader}>
            <h3 className={styles.tableTitle}>All Categories ({categories.length})</h3>
          </div>

          {loading ? (
            <div className={styles.loadingState} style={{ minHeight: '200px' }}>
              <div className={styles.spinner}></div>
            </div>
          ) : categories.length === 0 ? (
            <div className={styles.emptyState}>
              <div className={styles.emptyIcon}>🏷️</div>
              <h3>No categories yet</h3>
              <p>Create your first category.</p>
              <button className={styles.btnPrimary} onClick={openCreate}>+ Create Category</button>
            </div>
          ) : (
            <table className={styles.table}>
              <thead>
                <tr><th>Icon</th><th>Name</th><th>Slug</th><th>Description</th><th>Courses</th><th>Status</th><th>Actions</th></tr>
              </thead>
              <tbody>
                {categories.map(c => (
                  <tr key={c.id}>
                    <td style={{ fontSize: '1.5rem' }}>{c.icon || '📁'}</td>
                    <td style={{ fontWeight: 600 }}>{c.name}</td>
                    <td style={{ color: '#a0aec0', fontSize: '0.8rem' }}>{c.slug}</td>
                    <td style={{ color: '#a0aec0', fontSize: '0.85rem', maxWidth: '200px', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                      {c.description || '-'}
                    </td>
                    <td style={{ color: '#00d4ff' }}>{c.courses_count || 0}</td>
                    <td>
                      <span className={`${styles.statusBadge} ${c.is_active !== false ? styles.statusActive : styles.statusInactive}`}>
                        {c.is_active !== false ? 'Active' : 'Inactive'}
                      </span>
                    </td>
                    <td>
                      <button className={`${styles.actionBtn} ${styles.btnSecondary}`} onClick={() => openEdit(c)}>Edit</button>
                      <button className={`${styles.actionBtn} ${styles.btnDanger}`} onClick={() => handleDelete(c)}>Delete</button>
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
          <div className={styles.modal} onClick={e => e.stopPropagation()}>
            <div className={styles.modalHeader}>
              <h3 className={styles.modalTitle}>{editCat ? 'Edit Category' : 'Create Category'}</h3>
              <button className={styles.modalClose} onClick={() => setShowModal(false)}>×</button>
            </div>
            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Name *</label>
              <input className={styles.formInput} placeholder="Category name"
                value={formData.name} onChange={e => setFormData({ ...formData, name: e.target.value })} />
            </div>
            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Description</label>
              <textarea className={styles.formInput} placeholder="Category description..."
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
                <label className={styles.formLabel}>Color</label>
                <div style={{ display: 'flex', gap: '0.5rem', alignItems: 'center' }}>
                  <input type="color" value={formData.color}
                    onChange={e => setFormData({ ...formData, color: e.target.value })}
                    style={{ width: 40, height: 40, borderRadius: 8, border: 'none', cursor: 'pointer', background: 'transparent' }} />
                  <input className={styles.formInput} value={formData.color}
                    onChange={e => setFormData({ ...formData, color: e.target.value })} style={{ flex: 1 }} />
                </div>
              </div>
            </div>
            <div className={styles.formCheckbox}>
              <input type="checkbox" checked={formData.is_active}
                onChange={e => setFormData({ ...formData, is_active: e.target.checked })} />
              <span>Active</span>
            </div>
            <div className={styles.formActions}>
              <button className={styles.btnSecondary} onClick={() => setShowModal(false)}>Cancel</button>
              <button className={styles.btnPrimary} onClick={handleSave} disabled={actionLoading || !formData.name}>
                {actionLoading ? 'Saving...' : editCat ? 'Update' : 'Create'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}


