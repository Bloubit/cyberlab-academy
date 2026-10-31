import Head from 'next/head'
import { useState, useEffect, useCallback } from 'react'
import { useAuth } from '../../context/AuthContext'
import api from '../../lib/api'
import ImageUploader from '../../components/ImageUploader'
import styles from '../../styles/Admin.module.css'
import { Sidebar, LoadingSkeleton, AuthPrompt, DifficultyBadge, AccessDenied } from '../../components/AdminLayout'

export default function AdminCourses() {
  const { user, isAuthenticated, loading: authLoading } = useAuth()
  const [courses, setCourses] = useState([])
  const [meta, setMeta] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(1)
  const [filterPublished, setFilterPublished] = useState('')
  const [showModal, setShowModal] = useState(null)
  const [editCourse, setEditCourse] = useState(null)
  const [thumbnailUrl, setThumbnailUrl] = useState(null)
  const [formData, setFormData] = useState({ title: '', description: '', difficulty_level: 'beginner', is_published: false, is_free: true, is_featured: false, price: 0, estimated_hours: 0, category_id: '' })
  const [categories, setCategories] = useState([])
  const [actionLoading, setActionLoading] = useState(false)
  const [successMsg, setSuccessMsg] = useState(null)

  const fetchCourses = useCallback(async () => {
    setLoading(true); setError(null)
    try {
      const params = { page, per_page: 15, sort_by: 'created_at', sort_order: 'desc' }
      if (search) params.search = search
      if (filterPublished === 'published') params.is_published = true
      else if (filterPublished === 'draft') params.is_published = false

      const res = await api.getAdminCourses(params)
      if (res.success) { setCourses(res.data || []); setMeta(res.meta) }
    } catch (err) { setError(err.message) }
    finally { setLoading(false) }
  }, [page, search, filterPublished])

  useEffect(() => {
    if (!authLoading && isAuthenticated) fetchCourses()
    else if (!authLoading) setLoading(false)
  }, [authLoading, isAuthenticated, fetchCourses])

  const fetchCategories = async () => {
    const res = await api.getAdminCategories()
    if (res.success) setCategories(res.data || [])
  }

  const openCreateModal = async () => {
    await fetchCategories()
    setEditCourse(null)
    setThumbnailUrl(null)
    setFormData({ title: '', description: '', difficulty_level: 'beginner', is_published: false, is_free: true, is_featured: false, price: 0, estimated_hours: 0, category_id: '' })
    setShowModal('edit')
  }

  const openEditModal = async (course) => {
    await fetchCategories()
    setEditCourse(course)
    setThumbnailUrl(course.thumbnail || null)
    setFormData({
      title: course.title || '',
      description: course.description || '',
      difficulty_level: course.difficulty_level || 'beginner',
      is_published: course.is_published || false,
      is_free: course.is_free ?? true,
      is_featured: course.is_featured || false,
      price: course.price || 0,
      estimated_hours: course.estimated_hours || 0,
      category_id: course.category_id || '',
    })
    setShowModal('edit')
  }

  const handleSave = async () => {
    setActionLoading(true)
    try {
      const saveData = { ...formData }
      if (thumbnailUrl) saveData.thumbnail = thumbnailUrl
      const res = editCourse
        ? await api.updateAdminCourse(editCourse.id, saveData)
        : await api.createAdminCourse(saveData)
      if (res.success) {
        setSuccessMsg(editCourse ? 'Course updated!' : 'Course created!')
        setShowModal(null)
        fetchCourses()
      }
    } catch (err) { setError(err.message) }
    finally { setActionLoading(false) }
  }

  const handleTogglePublish = async (course) => {
    try {
      const res = await api.toggleCoursePublish(course.id)
      if (res.success) {
        setSuccessMsg(res.message)
        fetchCourses()
      }
    } catch (err) { setError(err.message) }
  }

  const handleDelete = async (course) => {
    if (!confirm(`Delete "${course.title}"? This action cannot be undone.`)) return
    try {
      const res = await api.deleteAdminCourse(course.id)
      if (res.success) {
        setSuccessMsg('Course deleted!')
        fetchCourses()
      }
    } catch (err) { setError(err.message) }
  }

  useEffect(() => {
    if (successMsg) { const t = setTimeout(() => setSuccessMsg(null), 3000); return () => clearTimeout(t) }
  }, [successMsg])

  if (authLoading) return <LoadingSkeleton />
  if (!isAuthenticated) return <AuthPrompt />
  if (!user?.is_admin) return <AccessDenied />

  return (
    <div className={styles.adminContainer}>
      <Head><title>Course Management - CyberLab Academy</title></Head>
      <Sidebar currentPath="/admin/courses" />
      
      <main className={styles.mainContent}>
        {error && <div className={`${styles.alert} ${styles.alertError}`}>⚠️ {error}</div>}
        {successMsg && <div className={`${styles.alert} ${styles.alertSuccess}`}>✅ {successMsg}</div>}

        <div className={styles.pageHeader}>
          <div>
            <h1 className={styles.pageTitle}>Course Management</h1>
            <p className={styles.pageSubtitle}>Create and manage courses</p>
          </div>
          <button className={styles.btnPrimary} onClick={openCreateModal}>+ New Course</button>
        </div>

        <div className={styles.tableCard}>
          <div className={styles.tableHeader}>
            <h3 className={styles.tableTitle}>Courses ({meta?.total || 0})</h3>
            <div className={styles.tableActions}>
              <input type="text" className={styles.searchInput} placeholder="Search courses..."
                value={search} onChange={e => { setSearch(e.target.value); setPage(1) }} />
              <select className={styles.filterSelect} value={filterPublished}
                onChange={e => { setFilterPublished(e.target.value); setPage(1) }}>
                <option value="">All Status</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
              </select>
            </div>
          </div>

          {loading ? (
            <div className={styles.loadingState} style={{ minHeight: '200px' }}>
              <div className={styles.spinner}></div>
            </div>
          ) : courses.length === 0 ? (
            <div className={styles.emptyState}>
              <div className={styles.emptyIcon}>📚</div>
              <h3>No courses yet</h3>
              <p>Create your first course to get started.</p>
              <button className={styles.btnPrimary} onClick={openCreateModal}>+ Create Course</button>
            </div>
          ) : (
            <>
              <table className={styles.table}>
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Difficulty</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Enrolled</th>
                    <th>Created</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {courses.map(c => (
                    <tr key={c.id}>
                      <td style={{ fontWeight: 600 }}>{c.title}</td>
                      <td><DifficultyBadge level={c.difficulty_level} /></td>
                      <td style={{ color: '#a0aec0' }}>{c.is_free ? 'Free' : `$${c.price}`}</td>
                      <td>
                        <span className={`${styles.statusBadge} ${c.is_published ? styles.statusPublished : styles.statusDraft}`}>
                          {c.is_published ? 'Published' : 'Draft'}
                        </span>
                      </td>
                      <td style={{ color: '#a0aec0' }}>{c.enrollment_count || 0}</td>
                      <td style={{ color: '#718096', fontSize: '0.8rem' }}>{new Date(c.created_at).toLocaleDateString()}</td>
                      <td>
                        <button className={`${styles.actionBtn} ${styles.btnSecondary}`} onClick={() => openEditModal(c)}>Edit</button>
                        <button className={`${styles.actionBtn} ${styles.btnSuccess}`} onClick={() => handleTogglePublish(c)}>
                          {c.is_published ? 'Unpublish' : 'Publish'}
                        </button>
                        <button className={`${styles.actionBtn} ${styles.btnDanger}`} onClick={() => handleDelete(c)}>Del</button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>

              {meta && meta.last_page > 1 && (
                <div className={styles.pagination}>
                  <button className={styles.pageBtn} disabled={page === 1} onClick={() => setPage(p => p - 1)}>← Prev</button>
                  <span className={styles.pageInfo}>Page {meta.current_page} of {meta.last_page}</span>
                  <button className={styles.pageBtn} disabled={page === meta.last_page} onClick={() => setPage(p => p + 1)}>Next →</button>
                </div>
              )}
            </>
          )}
        </div>
      </main>

      {/* Create/Edit Modal */}
      {showModal === 'edit' && (
        <div className={styles.modalOverlay} onClick={() => setShowModal(null)}>
          <div className={styles.modal} style={{ maxWidth: '650px' }} onClick={e => e.stopPropagation()}>
            <div className={styles.modalHeader}>
              <h3 className={styles.modalTitle}>{editCourse ? 'Edit Course' : 'Create Course'}</h3>
              <button className={styles.modalClose} onClick={() => setShowModal(null)}>×</button>
            </div>

            {/* Course Thumbnail */}
            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Course Thumbnail</label>
              <ImageUploader
                type="thumbnail"
                currentImage={thumbnailUrl}
                onUploadComplete={(url) => setThumbnailUrl(url)}
                onRemove={() => setThumbnailUrl(null)}
                size="large"
                label="Upload Course Thumbnail"
              />
            </div>

            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Title *</label>
              <input className={styles.formInput} placeholder="Course title"
                value={formData.title} onChange={e => setFormData({ ...formData, title: e.target.value })} />
            </div>

            <div className={styles.formRow}>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Difficulty</label>
                <select className={styles.formSelect} value={formData.difficulty_level}
                  onChange={e => setFormData({ ...formData, difficulty_level: e.target.value })}>
                  <option value="beginner">Beginner</option>
                  <option value="intermediate">Intermediate</option>
                  <option value="advanced">Advanced</option>
                  <option value="expert">Expert</option>
                </select>
              </div>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Category</label>
                <select className={styles.formSelect} value={formData.category_id}
                  onChange={e => setFormData({ ...formData, category_id: e.target.value })}>
                  <option value="">None</option>
                  {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                </select>
              </div>
            </div>

            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Description</label>
              <textarea className={styles.formInput} placeholder="Course description..."
                value={formData.description} onChange={e => setFormData({ ...formData, description: e.target.value })} />
            </div>

            <div className={styles.formRow}>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Price ($)</label>
                <input type="number" className={styles.formInput} placeholder="0"
                  value={formData.price} onChange={e => setFormData({ ...formData, price: Number(e.target.value) })} />
              </div>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Est. Hours</label>
                <input type="number" className={styles.formInput} placeholder="0"
                  value={formData.estimated_hours} onChange={e => setFormData({ ...formData, estimated_hours: Number(e.target.value) })} />
              </div>
            </div>

            <div className={styles.formCheckbox}>
              <input type="checkbox" checked={formData.is_free}
                onChange={e => setFormData({ ...formData, is_free: e.target.checked })} />
              <span>Free Course</span>
            </div>
            <div className={styles.formCheckbox}>
              <input type="checkbox" checked={formData.is_featured}
                onChange={e => setFormData({ ...formData, is_featured: e.target.checked })} />
              <span>Featured</span>
            </div>
            <div className={styles.formCheckbox}>
              <input type="checkbox" checked={formData.is_published}
                onChange={e => setFormData({ ...formData, is_published: e.target.checked })} />
              <span>Published</span>
            </div>

            <div className={styles.formActions}>
              <button className={styles.btnSecondary} onClick={() => setShowModal(null)}>Cancel</button>
              <button className={styles.btnPrimary} onClick={handleSave} disabled={actionLoading || !formData.title}>
                {actionLoading ? 'Saving...' : editCourse ? 'Update Course' : 'Create Course'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}


