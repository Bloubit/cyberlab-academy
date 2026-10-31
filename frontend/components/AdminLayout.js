import Link from 'next/link'
import styles from '../styles/Admin.module.css'

export const NAV_ITEMS = [
  { section: 'Main', items: [
    { href: '/admin', label: 'Dashboard', icon: '📊' },
    { href: '/admin/users', label: 'Users', icon: '👥' },
  ]},
  { section: 'Content', items: [
    { href: '/admin/courses', label: 'Courses', icon: '📚' },
    { href: '/admin/labs', label: 'Labs', icon: '💻' },
    { href: '/admin/instances', label: 'Instances', icon: '🖥️' },
    { href: '/admin/categories', label: 'Categories', icon: '🏷️' },
    { href: '/admin/badges', label: 'Badges', icon: '🎖️' },
  ]},
]

export function Sidebar({ currentPath }) {
  return (
    <aside className={styles.sidebar}>
      <div className={styles.sidebarBrand}>
        <Link href="/" className={styles.sidebarLogo}>🛡️ CyberLab</Link>
        <div className={styles.sidebarSubtitle}>Admin Panel</div>
      </div>
      <nav className={styles.sidebarNav}>
        {NAV_ITEMS.map((section, i) => (
          <div key={i} className={styles.navSection}>
            <div className={styles.navSectionLabel}>{section.section}</div>
            {section.items.map(item => (
              <Link key={item.href} href={item.href}
                className={`${styles.navLink} ${currentPath === item.href ? styles.navLinkActive : ''}`}>
                <span className={styles.navIcon}>{item.icon}</span>
                {item.label}
              </Link>
            ))}
          </div>
        ))}
      </nav>
      <div className={styles.sidebarFooter}>
        <Link href="/dashboard" className={styles.backLink}>← Back to Dashboard</Link>
      </div>
    </aside>
  )
}

export function LoadingSkeleton() {
  return (
    <div className={styles.adminContainer}>
      <div className={styles.mainContent}>
        <div className={styles.loadingState}>
          <div className={styles.spinner}></div>
          <p>Loading...</p>
        </div>
      </div>
    </div>
  )
}

export function AuthPrompt() {
  return (
    <div className={styles.adminContainer}>
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

export function AccessDenied() {
  return (
    <div className={styles.adminContainer}>
      <main className={styles.mainContent}>
        <div className={styles.emptyState}>
          <div className={styles.emptyIcon}>🚫</div>
          <h3>Access Denied</h3>
          <p>You do not have admin privileges to access this page.</p>
          <Link href="/dashboard" className={styles.btnPrimary}>Back to Dashboard</Link>
        </div>
      </main>
    </div>
  )
}

export function DifficultyBadge({ level }) {
  const colors = { beginner: '#00ff88', intermediate: '#00d4ff', advanced: '#ffaa00', expert: '#ff4757' }
  return <span style={{ color: colors[level] || '#a0aec0', fontSize: '0.8rem', fontWeight: 600 }}>{level || 'N/A'}</span>
}
