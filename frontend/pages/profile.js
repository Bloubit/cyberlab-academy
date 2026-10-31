import Head from 'next/head'
import { useState, useEffect, useCallback } from 'react'
import { useAuth } from '../context/AuthContext'
import { useRouter } from 'next/router'
import Link from 'next/link'
import api from '../lib/api'
import ImageUploader from '../components/ImageUploader'
import styles from '../styles/Profile.module.css'

const LANGUAGE_OPTIONS = [
  { code: 'en', name: 'English' },
  { code: 'uz', name: 'Uzbek' },
  { code: 'ru', name: 'Russian' },
  { code: 'tr', name: 'Turkish' },
]

const TIMEZONE_OPTIONS = [
  'UTC', 'Asia/Tashkent', 'Asia/Tokyo', 'Europe/London', 
  'Europe/Berlin', 'America/New_York', 'America/Chicago', 'America/Los_Angeles'
]

export default function Profile() {
  const { user, isAuthenticated, loading: authLoading, updateUser } = useAuth()
  const router = useRouter()

  const [profileData, setProfileData] = useState(null)
  const [loadingData, setLoadingData] = useState(true)
  const [error, setError] = useState(null)
  const [successMsg, setSuccessMsg] = useState(null)

  // Settings modal
  const [showSettings, setShowSettings] = useState(false)
  const [showPassword, setShowPassword] = useState(false)
  const [formData, setFormData] = useState({ name: '', bio: '', phone: '', language: 'en', timezone: 'UTC' })
  const [saving, setSaving] = useState(false)

  // Password form
  const [passwordForm, setPasswordForm] = useState({ current_password: '', new_password: '', new_password_confirmation: '' })
  const [changingPassword, setChangingPassword] = useState(false)

  // Avatar
  const [avatarFile, setAvatarFile] = useState(null)
  const [avatarPath, setAvatarPath] = useState(null)

  // Tab
  const [activeTab, setActiveTab] = useState('overview')

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push('/login')
    }
  }, [authLoading, isAuthenticated, router])

  const fetchProfile = useCallback(async () => {
    if (!isAuthenticated) return
    setLoadingData(true)
    try {
      // Fetch user dashboard data for real stats
      const [meRes, dashboardRes] = await Promise.allSettled([
        api.getMe(),
        api.getDashboard(),
      ])

      if (meRes.status === 'fulfilled' && meRes.value?.success) {
        setProfileData(prev => ({ ...prev, ...meRes.value.data }))
      }

      if (dashboardRes.status === 'fulfilled' && dashboardRes.value?.success) {
        setProfileData(prev => ({ ...prev, ...dashboardRes.value.data }))
      }
    } catch (err) {
      setError(err.message)
    } finally {
      setLoadingData(false)
    }
  }, [isAuthenticated])

  useEffect(() => {
    if (!authLoading && isAuthenticated) fetchProfile()
    else if (!authLoading) setLoadingData(false)
  }, [authLoading, isAuthenticated, fetchProfile])

  useEffect(() => {
    if (user) {
      setFormData({
        name: user.name || '',
        bio: user.bio || '',
        phone: user.phone || '',
        language: user.language || 'en',
        timezone: user.timezone || 'UTC',
      })
    }
  }, [user])

  useEffect(() => {
    if (successMsg) {
      const t = setTimeout(() => setSuccessMsg(null), 3000)
      return () => clearTimeout(t)
    }
  }, [successMsg])

  const openSettings = () => {
    setFormData({
      name: user?.name || '',
      bio: user?.bio || '',
      phone: user?.phone || '',
      language: user?.language || 'en',
      timezone: user?.timezone || 'UTC',
      country_code: user?.country_code || '',
    })
    setAvatarFile(null)
    setActiveTab('settings')
  }

  const handleAvatarUpload = (url, path) => {
    setAvatarFile(url)
    setAvatarPath(path)
  }

  const handleAvatarRemove = () => {
    setAvatarFile(null)
    setAvatarPath(null)
  }

  const handleSaveProfile = async () => {
    setSaving(true)
    setError(null)
    try {
      const updateData = { ...formData }
      if (avatarFile) updateData.avatar = avatarFile

      const res = await api.updateProfile(updateData)
      if (res.success) {
        updateUser(res.data)
        setProfileData(prev => ({ ...prev, ...res.data }))
        setSuccessMsg('✅ Profile updated successfully!')
        setShowSettings(false)
      }
    } catch (err) {
      setError(err.message || 'Failed to save')
    } finally {
      setSaving(false)
    }
  }

  const handleChangePassword = async () => {
    if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
      setError('Passwords do not match')
      return
    }
    setChangingPassword(true)
    setError(null)
    try {
      const res = await api.changePassword(passwordForm)
      if (res.success) {
        setSuccessMsg('✅ Password changed successfully!')
        setShowPassword(false)
        setPasswordForm({ current_password: '', new_password: '', new_password_confirmation: '' })
      }
    } catch (err) {
      setError(err.message || 'Failed to change password')
    } finally {
      setChangingPassword(false)
    }
  }

  if (authLoading || loadingData) {
    return (
      <div className={styles.container}>
        <header className={styles.header}>
          <div className={styles.headerContent}>
            <Link href="/" className={styles.logo}>🛡️ CyberLab Academy</Link>
            <nav className={styles.nav}>
              <Link href="/dashboard">Dashboard</Link>
              <Link href="/courses">Courses</Link>
              <Link href="/labs">Labs</Link>
            </nav>
          </div>
        </header>
        <div className={styles.loading}><div className={styles.spinner} /></div>
      </div>
    )
  }

  const displayName = user?.name || 'User'
  const initials = displayName.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
  const avatarUrl = user?.avatar || null
  const totalXp = profileData?.total_xp || user?.total_xp || 0
  const level = profileData?.level || user?.level || 1
  const enrolledCount = profileData?.enrolled_courses_count || 0
  const completedCount = profileData?.completed_courses_count || 0
  const activeLabs = profileData?.active_labs_count || 0
  const badgesCount = profileData?.badges_count || 0
  const certCount = profileData?.certificates_count || 0

  return (
    <div className={styles.container}>
      <Head><title>Profile - CyberLab Academy</title></Head>

      <header className={styles.header}>
        <div className={styles.headerContent}>
          <Link href="/" className={styles.logo}>🛡️ CyberLab Academy</Link>
          <nav className={styles.nav}>
            <Link href="/dashboard">Dashboard</Link>
            <Link href="/courses">Courses</Link>
            <Link href="/labs">Labs</Link>
            <Link href="/leaderboard">Leaderboard</Link>
          </nav>
          <Link href="/profile" className={styles.avatar}>{initials}</Link>
        </div>
      </header>

      <main className={styles.main}>
        {error && <div className={styles.alertError}>⚠️ {error}</div>}
        {successMsg && <div className={styles.alertSuccess}>{successMsg}</div>}

        {/* Tabs */}
        <div className={styles.tabs}>
          <button className={`${styles.tab} ${activeTab === 'overview' ? styles.tabActive : ''}`}
            onClick={() => setActiveTab('overview')}>📊 Overview</button>
          <button className={`${styles.tab} ${activeTab === 'settings' ? styles.tabActive : ''}`}
            onClick={() => setActiveTab('settings')}>⚙️ Settings</button>
          <button className={`${styles.tab} ${activeTab === 'security' ? styles.tabActive : ''}`}
            onClick={() => setActiveTab('security')}>🔒 Security</button>
        </div>

        {activeTab === 'overview' && (
          <>
            {/* Profile Header */}
            <div className={styles.profileHeader}>
              <div className={styles.avatarSection}>
                {avatarUrl ? (
                  <img src={avatarUrl} alt={displayName} className={styles.avatarImg} />
                ) : (
                  <div className={styles.avatar}>{initials}</div>
                )}
                <button className={styles.editAvatarBtn} onClick={openSettings}>📷</button>
              </div>
              <div className={styles.profileInfo}>
                <h1 className={styles.profileName}>{displayName}</h1>
                <p className={styles.profileUsername}>@{user?.username || 'username'}</p>
                <p className={styles.profileBio}>
                  {user?.bio || 'No bio yet. Click settings to add one.'}
                </p>
                <div className={styles.profileLevel}>
                  <span className={styles.levelBadge}>Level {level}</span>
                  <span className={styles.xpBar}>
                    <span className={styles.xpFill} style={{ width: `${Math.min((totalXp / (level * 10000)) * 100, 100)}%` }}></span>
                  </span>
                  <span className={styles.xpText}>{totalXp.toLocaleString()} / {level * 10000} XP</span>
                </div>
                <button className={styles.editProfileBtn} onClick={openSettings}>
                  ✏️ Edit Profile
                </button>
              </div>
            </div>

            {/* Stats Grid */}
            <div className={styles.statsGrid}>
              <div className={styles.statCard}>
                <div className={styles.statIcon}>📚</div>
                <div className={styles.statValue}>{enrolledCount}</div>
                <div className={styles.statLabel}>Enrolled</div>
              </div>
              <div className={styles.statCard}>
                <div className={styles.statIcon}>🎓</div>
                <div className={styles.statValue}>{completedCount}</div>
                <div className={styles.statLabel}>Completed</div>
              </div>
              <div className={styles.statCard}>
                <div className={styles.statIcon}>💻</div>
                <div className={styles.statValue}>{activeLabs}</div>
                <div className={styles.statLabel}>Active Labs</div>
              </div>
              <div className={styles.statCard}>
                <div className={styles.statIcon}>🏆</div>
                <div className={styles.statValue}>{badgesCount}</div>
                <div className={styles.statLabel}>Badges</div>
              </div>
              <div className={styles.statCard}>
                <div className={styles.statIcon}>📜</div>
                <div className={styles.statValue}>{certCount}</div>
                <div className={styles.statLabel}>Certificates</div>
              </div>
              <div className={styles.statCard}>
                <div className={styles.statIcon}>⚡</div>
                <div className={styles.statValue}>{totalXp.toLocaleString()}</div>
                <div className={styles.statLabel}>Total XP</div>
              </div>
            </div>
          </>
        )}

        {activeTab === 'settings' && (
          <div className={styles.settingsPanel}>
            <h2 className={styles.panelTitle}>Profile Settings</h2>
            <p className={styles.panelDesc}>Update your personal information</p>

            {/* Avatar Upload */}
            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Profile Avatar</label>
              <ImageUploader
                type="avatar"
                currentImage={avatarFile || avatarUrl}
                onUploadComplete={handleAvatarUpload}
                onRemove={handleAvatarRemove}
                size="large"
                label="Upload Profile Picture"
              />
            </div>

            <div className={styles.formRow}>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Name</label>
                <input className={styles.formInput} value={formData.name}
                  onChange={e => setFormData({ ...formData, name: e.target.value })} />
              </div>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Email</label>
                <input className={styles.formInput} value={user?.email || ''} disabled style={{ opacity: 0.5 }} />
              </div>
            </div>

            <div className={styles.formGroup}>
              <label className={styles.formLabel}>Bio</label>
              <textarea className={styles.formTextarea} placeholder="Tell us about yourself..."
                value={formData.bio} onChange={e => setFormData({ ...formData, bio: e.target.value })} />
            </div>

            <div className={styles.formRow}>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Phone</label>
                <input className={styles.formInput} placeholder="+1234567890"
                  value={formData.phone} onChange={e => setFormData({ ...formData, phone: e.target.value })} />
              </div>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Language</label>
                <select className={styles.formSelect} value={formData.language}
                  onChange={e => setFormData({ ...formData, language: e.target.value })}>
                  {LANGUAGE_OPTIONS.map(l => <option key={l.code} value={l.code}>{l.name}</option>)}
                </select>
              </div>
            </div>

            <div className={styles.formRow}>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Timezone</label>
                <select className={styles.formSelect} value={formData.timezone}
                  onChange={e => setFormData({ ...formData, timezone: e.target.value })}>
                  {TIMEZONE_OPTIONS.map(tz => <option key={tz} value={tz}>{tz}</option>)}
                </select>
              </div>
              <div className={styles.formGroup}>
                <label className={styles.formLabel}>Country Code</label>
                <input className={styles.formInput} placeholder="UZ"
                  value={formData.country_code || ''}
                  onChange={e => setFormData({ ...formData, country_code: e.target.value })} />
              </div>
            </div>

            <div className={styles.formActions}>
              <button className={styles.cancelBtn} onClick={() => setActiveTab('overview')}>Cancel</button>
              <button className={styles.saveBtn} onClick={handleSaveProfile} disabled={saving || !formData.name}>
                {saving ? 'Saving...' : '💾 Save Changes'}
              </button>
            </div>
          </div>
        )}

        {activeTab === 'security' && (
          <div className={styles.settingsPanel}>
            <h2 className={styles.panelTitle}>Security</h2>
            <p className={styles.panelDesc}>Manage your password and account security</p>

            <div className={styles.securityCard}>
              <div className={styles.securityRow}>
                <div>
                  <h3 className={styles.securityTitle}>Password</h3>
                  <p className={styles.securityDesc}>Change your account password</p>
                </div>
                {!showPassword ? (
                  <button className={styles.changeBtn} onClick={() => setShowPassword(true)}>
                    Change Password
                  </button>
                ) : (
                  <button className={styles.cancelBtn} onClick={() => { setShowPassword(false); setError(null) }}>
                    Cancel
                  </button>
                )}
              </div>

              {showPassword && (
                <div className={styles.passwordForm}>
                  <div className={styles.formGroup}>
                    <label className={styles.formLabel}>Current Password</label>
                    <input type="password" className={styles.formInput}
                      value={passwordForm.current_password}
                      onChange={e => setPasswordForm({ ...passwordForm, current_password: e.target.value })} />
                  </div>
                  <div className={styles.formRow}>
                    <div className={styles.formGroup}>
                      <label className={styles.formLabel}>New Password</label>
                      <input type="password" className={styles.formInput}
                        value={passwordForm.new_password}
                        onChange={e => setPasswordForm({ ...passwordForm, new_password: e.target.value })} />
                    </div>
                    <div className={styles.formGroup}>
                      <label className={styles.formLabel}>Confirm New Password</label>
                      <input type="password" className={styles.formInput}
                        value={passwordForm.new_password_confirmation}
                        onChange={e => setPasswordForm({ ...passwordForm, new_password_confirmation: e.target.value })} />
                    </div>
                  </div>
                  <button className={styles.saveBtn} onClick={handleChangePassword}
                    disabled={changingPassword || !passwordForm.current_password || !passwordForm.new_password}>
                    {changingPassword ? 'Changing...' : '🔒 Update Password'}
                  </button>
                </div>
              )}
            </div>

            <div className={styles.securityCard}>
              <div className={styles.securityRow}>
                <div>
                  <h3 className={styles.securityTitle}>Account Status</h3>
                  <p className={styles.securityDesc}>{user?.is_active ? '✅ Active' : '❌ Inactive'}</p>
                </div>
                <span className={styles.roleBadge}>{user?.is_admin ? '👑 Admin' : '🎓 Student'}</span>
              </div>
            </div>
          </div>
        )}
      </main>
    </div>
  )
}
