import Link from 'next/link'
import { useAuth } from '../context/AuthContext'
import { useState } from 'react'
import styles from '../styles/Navbar.module.css'

export default function Navbar() {
  const { user, isAuthenticated, logout } = useAuth()
  const [mobileOpen, setMobileOpen] = useState(false)
  const [dropdownOpen, setDropdownOpen] = useState(false)

  const handleLogout = async () => {
    setDropdownOpen(false)
    await logout()
    window.location.href = '/'
  }

  return (
    <header className={styles.header}>
      <div className={styles.container}>
        <Link href="/" className={styles.logo}>
          <span className={styles.logoIcon}>🛡️</span>
          <span className={styles.logoText}>CyberLab Academy</span>
        </Link>

        <nav className={`${styles.nav} ${mobileOpen ? styles.navOpen : ''}`}>
          <Link href="/courses" className={styles.navLink}>
            Courses
          </Link>
          <Link href="/labs" className={styles.navLink}>
            Labs
          </Link>
          <Link href="/pricing" className={styles.navLink}>
            Pricing
          </Link>
          <Link href="/leaderboard" className={styles.navLink}>
            Leaderboard
          </Link>
        </nav>

        <div className={styles.actions}>
          {isAuthenticated ? (
            <div className={styles.userMenu}>
              <Link href="/dashboard" className={styles.dashboardBtn}>
                Dashboard
              </Link>
              <div 
                className={styles.avatarWrapper}
                onClick={() => setDropdownOpen(!dropdownOpen)}
              >
                <div className={styles.avatar}>
                  {user?.name?.charAt(0)?.toUpperCase() || 'U'}
                </div>
              </div>
              
              {dropdownOpen && (
                <div className={styles.dropdown}>
                  <Link href="/profile" className={styles.dropdownItem}>
                    <span>👤</span> Profile
                  </Link>
                  <Link href="/dashboard" className={styles.dropdownItem}>
                    <span>📊</span> Dashboard
                  </Link>
                  {user?.is_admin && (
                    <Link href="/admin" className={styles.dropdownItem}>
                      <span>⚙️</span> Admin Panel
                    </Link>
                  )}
                  <Link href="/billing" className={styles.dropdownItem}>
                    <span>💳</span> Billing
                  </Link>
                  <Link href="/certificates" className={styles.dropdownItem}>
                    <span>🎓</span> Certificates
                  </Link>
                  <div className={styles.dropdownDivider} />
                  <button onClick={handleLogout} className={styles.dropdownItem}>
                    <span>🚪</span> Sign Out
                  </button>
                </div>
              )}
            </div>
          ) : (
            <div className={styles.authBtns}>
              <Link href="/login" className={styles.loginBtn}>
                Sign In
              </Link>
              <Link href="/register" className={styles.signupBtn}>
                Get Started
              </Link>
            </div>
          )}

          <button 
            className={styles.mobileToggle}
            onClick={() => setMobileOpen(!mobileOpen)}
          >
            <span className={mobileOpen ? styles.hamburgerOpen : styles.hamburger}>
              {mobileOpen ? '✕' : '☰'}
            </span>
          </button>
        </div>
      </div>
    </header>
  )
}
