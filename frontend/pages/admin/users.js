import Head from 'next/head'
import { useState, useEffect, useCallback } from 'react'
import { useAuth } from '../../context/AuthContext'
import api from '../../lib/api'
import styles from '../../styles/Admin.module.css'
import { Sidebar, LoadingSkeleton, AuthPrompt, AccessDenied } from '../../components/AdminLayout'

export default function AdminUsers() {
  const { user, isAuthenticated, loading: authLoading } = useAuth()
  const [users, setUsers] = useState([])
  const [meta, setMeta] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [search, setSearch] = useState('')
  const [page, setPage] = useState(1)
  const [roleFilter, setRoleFilter] = useState('')
  const [statusFilter, setStatusFilter] = useState('')
  const [selectedUser, setSelectedUser] = useState(null)
  const [showModal, setShowModal] = useState(null) // 'view' | 'ban' | 'role' | null
  const [banReason, setBanReason] = useState('')
  const [actionLoading, setActionLoading] = useState(false)
  const [successMsg, setSuccessMsg] = useState(null)

  const fetchUsers = useCallback(async () => {
    setLoading(true)
    setError(null)
    try {
      const params = { page, per_page: 15 }
      if (search) params.search = search
      if (roleFilter) params.role = roleFilter
      if (statusFilter === 'active') params.is_active = true
      else if (statusFilter === 'banned') params.is_banned = true
      else if (statusFilter === 'inactive') params.is_active = false

      const res = await api.getAdminUsers(params)
      if (res.success) {
        setUsers(res.data || [])
        setMeta(res.meta)
      }
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }, [page, search, roleFilter, statusFilter])

  useEffect(() => {
    if (!authLoading && isAuthenticated) fetchUsers()
    else if (!authLoading) setLoading(false)
  }, [authLoading, isAuthenticated, fetchUsers])

  const handleBan = async () => {
    if (!selectedUser) return
    setActionLoading(true)
    try {
      const res = await api.banUser(selectedUser.id, banReason)
      if (res.success) {
        setSuccessMsg(`User "${selectedUser.name}" banned successfully`)
        setShowModal(null)
        setBanReason('')
        fetchUsers()
      }
    } catch (err) {
      setError(err.message)
    } finally {
      setActionLoading(false)
    }
  }

  const handleUnban = async (user) => {
    setActionLoading(true)
    try {
      const res = await api.unbanUser(user.id)
      if (res.success) {
        setSuccessMsg(`User "${user.name}" unbanned successfully`)
        fetchUsers()
      }
    } catch (err) {
      setError(err.message)
    } finally {
      setActionLoading(false)
    }
  }

  const handleAssignRole = async (role) => {
    if (!selectedUser) return
    setActionLoading(true)
    try {
      const res = await api.assignUserRole(selectedUser.id, role)
      if (res.success) {
        setSuccessMsg(`Role assigned to "${selectedUser.name}"`)
        setShowModal(null)
        fetchUsers()
      }
    } catch (err) {
      setError(err.message)
    } finally {
      setActionLoading(false)
    }
  }

  useEffect(() => {
    if (successMsg) {
      const timer = setTimeout(() => setSuccessMsg(null), 3000)
      return () => clearTimeout(timer)
    }
  }, [successMsg])

  if (authLoading) return <LoadingSkeleton />
  if (!isAuthenticated) return <AuthPrompt />
  if (!user?.is_admin) return <AccessDenied />

  return (
    <div className={styles.adminContainer}>
      <Head><title>User Management - CyberLab Academy</title></Head>
      <Sidebar currentPath="/admin/users" />
      
      <main className={styles.mainContent}>
        {error && <div className={`${styles.alert} ${styles.alertError}`}>⚠️ {error}</div>}
        {successMsg && <div className={`${styles.alert} ${styles.alertSuccess}`}>✅ {successMsg}</div>}

        <div className={styles.pageHeader}>
          <div>
            <h1 className={styles.pageTitle}>User Management</h1>
            <p className={styles.pageSubtitle}>Manage all platform users</p>
          </div>
          <div className={styles.tableActions}>
            <input type="text" className={styles.searchInput} placeholder="Search users..." 
              value={search} onChange={e => { setSearch(e.target.value); setPage(1) }} />
            <select className={styles.filterSelect} value={roleFilter}
              onChange={e => { setRoleFilter(e.target.value); setPage(1) }}>
              <option value="">All Roles</option>
              <option value="admin">Admin</option>
              <option value="instructor">Instructor</option>
              <option value="student">Student</option>
            </select>
            <select className={styles.filterSelect} value={statusFilter}
              onChange={e => { setStatusFilter(e.target.value); setPage(1) }}>
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="banned">Banned</option>
            </select>
          </div>
        </div>

        <div className={styles.tableCard}>
          <div className={styles.tableHeader}>
            <h3 className={styles.tableTitle}>Users ({meta?.total || 0})</h3>
          </div>
          {loading ? (
            <div className={styles.loadingState} style={{ minHeight: '200px' }}>
              <div className={styles.spinner}></div>
            </div>
          ) : users.length === 0 ? (
            <div className={styles.emptyState}>
              <div className={styles.emptyIcon}>👥</div>
              <h3>No users found</h3>
              <p>Try adjusting your search or filters.</p>
            </div>
          ) : (
            <>
              <table className={styles.table}>
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  {users.map(u => (
                    <tr key={u.id}>
                      <td style={{ fontWeight: 600 }}>{u.name}</td>
                      <td style={{ color: '#a0aec0' }}>{u.email}</td>
                      <td style={{ color: '#a0aec0' }}>{u.username || '-'}</td>
                      <td>
                        {u.is_banned ? (
                          <span className={`${styles.statusBadge} ${styles.statusBanned}`}>Banned</span>
                        ) : u.is_active ? (
                          <span className={`${styles.statusBadge} ${styles.statusActive}`}>Active</span>
                        ) : (
                          <span className={`${styles.statusBadge} ${styles.statusInactive}`}>Inactive</span>
                        )}
                      </td>
                      <td style={{ color: '#718096', fontSize: '0.8rem' }}>
                        {new Date(u.created_at).toLocaleDateString()}
                      </td>
                      <td>
                        <button className={`${styles.actionBtn} ${styles.btnPrimary}`}
                          onClick={() => { setSelectedUser(u); setShowModal('view') }}>
                          View
                        </button>
                        <button className={`${styles.actionBtn} ${styles.btnSecondary}`}
                          onClick={() => { setSelectedUser(u); setShowModal('role') }}>
                          Role
                        </button>
                        {u.is_banned ? (
                          <button className={`${styles.actionBtn} ${styles.btnSuccess}`}
                            onClick={() => handleUnban(u)} disabled={actionLoading}>
                            Unban
                          </button>
                        ) : (
                          <button className={`${styles.actionBtn} ${styles.btnDanger}`}
                            onClick={() => { setSelectedUser(u); setShowModal('ban') }}
                            disabled={u.roles?.includes('admin') || u.roles?.includes('super-admin')}>
                            Ban
                          </button>
                        )}
                      </td>
                    </tr>
                  ))}
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

      {/* View User Modal */}
      {showModal === 'view' && selectedUser && (
        <UserViewModal user={selectedUser} onClose={() => { setShowModal(null); setSelectedUser(null) }} />
      )}

      {/* Ban User Modal */}
      {showModal === 'ban' && selectedUser && (
        <div className={styles.modalOverlay} onClick={() => setShowModal(null)}>
          <div className={styles.modal} onClick={e => e.stopPropagation()}>
            <div className={styles.modalHeader}>
              <h3 className={styles.modalTitle}>Ban User</h3>
              <button className={styles.modalClose} onClick={() => setShowModal(null)}>×</button>
            </div>
            <p style={{ color: '#a0aec0', marginBottom: '1rem', fontSize: '0.9rem' }}>
              Are you sure you want to ban <strong style={{ color: '#fff' }}>{selectedUser.name}</strong>?
              This will revoke all their tokens and prevent login.
            </p>
            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Reason (optional)</label>
              <textarea className={styles.formInput} placeholder="Enter ban reason..."
                value={banReason} onChange={e => setBanReason(e.target.value)} />
            </div>
            <div className={styles.formActions}>
              <button className={styles.btnSecondary} onClick={() => { setShowModal(null); setBanReason('') }}>Cancel</button>
              <button className={`${styles.btnPrimary}`} style={{ background: 'linear-gradient(90deg, #ff4757, #ff6b81)' }}
                onClick={handleBan} disabled={actionLoading}>
                {actionLoading ? 'Processing...' : 'Ban User'}
              </button>
            </div>
          </div>
        </div>
      )}

      {/* Assign Role Modal */}
      {showModal === 'role' && selectedUser && (
        <div className={styles.modalOverlay} onClick={() => setShowModal(null)}>
          <div className={styles.modal} onClick={e => e.stopPropagation()}>
            <div className={styles.modalHeader}>
              <h3 className={styles.modalTitle}>Assign Role</h3>
              <button className={styles.modalClose} onClick={() => setShowModal(null)}>×</button>
            </div>
            <p style={{ color: '#a0aec0', marginBottom: '1rem', fontSize: '0.9rem' }}>
              Assign role to <strong style={{ color: '#fff' }}>{selectedUser.name}</strong>
            </p>
            <div style={{ display: 'flex', flexDirection: 'column', gap: '0.75rem' }}>
              {['student', 'instructor', 'admin'].map(role => (
                <button key={role} className={styles.btnSecondary}
                  style={{ textAlign: 'left', padding: '0.75rem 1rem', fontSize: '0.9rem' }}
                  onClick={() => handleAssignRole(role)} disabled={actionLoading}>
                  {role === 'student' ? '🎓' : role === 'instructor' ? '👨‍🏫' : '👑'} {role.charAt(0).toUpperCase() + role.slice(1)}
                </button>
              ))}
            </div>
            <div className={styles.formActions} style={{ borderTop: 'none', marginTop: '1rem' }}>
              <button className={styles.btnSecondary} onClick={() => { setShowModal(null); setSelectedUser(null) }}>Cancel</button>
            </div>
          </div>
        </div>
      )}
    </div>
  )
}

function UserViewModal({ user, onClose }) {
  return (
    <div className={styles.modalOverlay} onClick={onClose}>
      <div className={styles.modal} onClick={e => e.stopPropagation()}>
        <div className={styles.modalHeader}>
          <h3 className={styles.modalTitle}>User Details</h3>
          <button className={styles.modalClose} onClick={onClose}>×</button>
        </div>
        <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '1rem', paddingBottom: '1rem', borderBottom: '1px solid rgba(255,255,255,0.08)' }}>
            <div style={{ width: 56, height: 56, borderRadius: '50%', background: 'linear-gradient(135deg, #00d4ff, #9945ff)', display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: '1.5rem', fontWeight: 700 }}>
              {user.name?.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)}
            </div>
            <div>
              <div style={{ fontWeight: 600, fontSize: '1.1rem' }}>{user.name}</div>
              <div style={{ color: '#a0aec0', fontSize: '0.85rem' }}>{user.email}</div>
              {user.username && <div style={{ color: '#718096', fontSize: '0.8rem' }}>@{user.username}</div>}
            </div>
          </div>
          <DetailRow label="Status" value={user.is_banned ? 'Banned' : user.is_active ? 'Active' : 'Inactive'} />
          <DetailRow label="Roles" value={user.roles?.join(', ') || 'student'} />
          {user.bio && <DetailRow label="Bio" value={user.bio} />}
          <DetailRow label="Joined" value={new Date(user.created_at).toLocaleDateString()} />
        </div>
        <div className={styles.formActions}>
          <button className={styles.btnSecondary} onClick={onClose}>Close</button>
        </div>
      </div>
    </div>
  )
}

function DetailRow({ label, value }) {
  return (
    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
      <span style={{ color: '#a0aec0', fontSize: '0.85rem' }}>{label}</span>
      <span style={{ color: '#ffffff', fontSize: '0.9rem', fontWeight: 500 }}>{value}</span>
    </div>
  )
}


