import Head from 'next/head'
import Link from 'next/link'
import { useState } from 'react'
import api from '../lib/api'
import styles from '../styles/Auth.module.css'

export default function ForgotPassword() {
  const [email, setEmail] = useState('')
  const [errors, setErrors] = useState({})
  const [loading, setLoading] = useState(false)
  const [sent, setSent] = useState(false)
  const [resetToken, setResetToken] = useState(null)

  const handleSubmit = async (e) => {
    e.preventDefault()

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!email || !emailRegex.test(email)) {
      setErrors({ email: 'Please enter a valid email address' })
      return
    }

    setLoading(true)
    setErrors({})

    try {
      const res = await api.sendPasswordResetLink(email)
      if (res.success) {
        setSent(true)
        setResetToken(res.data?.token || null)
      } else {
        setErrors({ general: res.message || 'Failed to send reset link' })
      }
    } catch (err) {
      setErrors({ general: err.message || 'Network error' })
    }

    setLoading(false)
  }

  if (sent) {
    return (
      <div className={styles.container}>
        <Head>
          <title>Check Your Email - CyberLab Academy</title>
        </Head>
        <div className={styles.card}>
          <div className={styles.successIcon}>📧</div>
          <h1 className={styles.title}>Check Your Email</h1>
          <p className={styles.subtitle}>
            If an account exists with <strong>{email}</strong>, we've sent a password reset link.
          </p>

          {resetToken && (
            <div className={styles.tokenBox}>
              <p className={styles.tokenLabel}>Development Mode — Reset Link:</p>
              <Link href={`/reset-password?token=${resetToken}&email=${encodeURIComponent(email)}`} className={styles.tokenLink}>
                Click here to reset your password
              </Link>
            </div>
          )}

          <div className={styles.options} style={{ marginTop: '1.5rem' }}>
            <Link href="/login" className={styles.forgot}>Back to Login</Link>
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className={styles.container}>
      <Head>
        <title>Forgot Password - CyberLab Academy</title>
      </Head>

      <div className={styles.card}>
        <h1 className={styles.title}>Forgot Password?</h1>
        <p className={styles.subtitle}>
          Enter your email and we'll send you a reset link
        </p>

        {errors.general && (
          <div className={styles.error}>{errors.general}</div>
        )}

        <form onSubmit={handleSubmit} className={styles.form}>
          <div className={styles.inputGroup}>
            <label>Email Address</label>
            <input
              type="email"
              name="email"
              placeholder="you@example.com"
              className={styles.input}
              value={email}
              onChange={(e) => {
                setEmail(e.target.value)
                if (errors.email) setErrors({})
              }}
            />
            {errors.email && <span className={styles.errorText}>{errors.email}</span>}
          </div>

          <button type="submit" className={styles.btnPrimary} disabled={loading}>
            {loading ? 'Sending...' : 'Send Reset Link'}
          </button>
        </form>

        <p className={styles.footer}>
          Remember your password? <Link href="/login">Sign in</Link>
        </p>
      </div>
    </div>
  )
}
