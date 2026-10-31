import Head from 'next/head'
import Link from 'next/link'
import { useState, useEffect } from 'react'
import { useRouter } from 'next/router'
import api from '../lib/api'
import styles from '../styles/Auth.module.css'

export default function ResetPassword() {
  const router = useRouter()
  const { token, email } = router.query

  const [formData, setFormData] = useState({
    password: '',
    password_confirmation: '',
  })
  const [errors, setErrors] = useState({})
  const [loading, setLoading] = useState(false)
  const [success, setSuccess] = useState(false)
  const [showPassword, setShowPassword] = useState(false)

  useEffect(() => {
    if (router.isReady && (!token || !email)) {
      setErrors({ general: 'Invalid reset link. Please request a new one.' })
    }
  }, [router.isReady, token, email])

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    })
    if (errors[e.target.name]) {
      setErrors({ ...errors, [e.target.name]: '' })
    }
  }

  const validateForm = () => {
    const newErrors = {}

    if (!formData.password) {
      newErrors.password = 'Password is required'
    } else if (formData.password.length < 8) {
      newErrors.password = 'Password must be at least 8 characters'
    } else if (!/[A-Z]/.test(formData.password)) {
      newErrors.password = 'Must contain an uppercase letter'
    } else if (!/[a-z]/.test(formData.password)) {
      newErrors.password = 'Must contain a lowercase letter'
    } else if (!/[0-9]/.test(formData.password)) {
      newErrors.password = 'Must contain a number'
    }

    if (formData.password !== formData.password_confirmation) {
      newErrors.password_confirmation = 'Passwords do not match'
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e) => {
    e.preventDefault()

    if (!validateForm()) return

    setLoading(true)
    setErrors({})

    try {
      const res = await api.resetPassword({
        token,
        email,
        password: formData.password,
        password_confirmation: formData.password_confirmation,
      })

      if (res.success) {
        setSuccess(true)
      } else {
        setErrors({ general: res.message || 'Failed to reset password' })
      }
    } catch (err) {
      setErrors({ general: err.message || 'Network error' })
    }

    setLoading(false)
  }

  if (success) {
    return (
      <div className={styles.container}>
        <Head>
          <title>Password Reset - CyberLab Academy</title>
        </Head>
        <div className={styles.card}>
          <div className={styles.successIcon}>✓</div>
          <h1 className={styles.title}>Password Reset!</h1>
          <p className={styles.subtitle}>
            Your password has been successfully reset. You can now sign in with your new password.
          </p>
          <Link href="/login" className={styles.btnPrimary} style={{ display: 'block', textAlign: 'center', marginTop: '1.5rem', textDecoration: 'none' }}>
            Sign In
          </Link>
        </div>
      </div>
    )
  }

  return (
    <div className={styles.container}>
      <Head>
        <title>Reset Password - CyberLab Academy</title>
      </Head>

      <div className={styles.card}>
        <h1 className={styles.title}>Reset Password</h1>
        <p className={styles.subtitle}>Enter your new password</p>

        {errors.general && (
          <div className={styles.error}>{errors.general}</div>
        )}

        <form onSubmit={handleSubmit} className={styles.form}>
          <div className={styles.inputGroup}>
            <label>New Password</label>
            <div className={styles.passwordWrapper}>
              <input
                type={showPassword ? 'text' : 'password'}
                name="password"
                placeholder="Enter new password"
                className={styles.input}
                value={formData.password}
                onChange={handleChange}
              />
              <button
                type="button"
                className={styles.togglePassword}
                onClick={() => setShowPassword(!showPassword)}
              >
                {showPassword ? '👁️' : '👁️‍🗨️'}
              </button>
            </div>
            {errors.password && <span className={styles.errorText}>{errors.password}</span>}

            {formData.password && (
              <div className={styles.passwordStrength}>
                <div className={styles.strengthBar}>
                  <div
                    className={styles.strengthFill}
                    style={{
                      width: `${Math.min(100, (formData.password.length / 12) * 100)}%`,
                      backgroundColor: formData.password.length < 8 ? '#ff4444' :
                                      formData.password.length < 12 ? '#ffaa00' : '#00ff88',
                    }}
                  />
                </div>
                <span className={styles.strengthText}>
                  {formData.password.length < 8 ? 'Weak' :
                   formData.password.length < 12 ? 'Medium' : 'Strong'}
                </span>
              </div>
            )}
          </div>

          <div className={styles.inputGroup}>
            <label>Confirm New Password</label>
            <input
              type="password"
              name="password_confirmation"
              placeholder="Confirm new password"
              className={styles.input}
              value={formData.password_confirmation}
              onChange={handleChange}
            />
            {errors.password_confirmation && <span className={styles.errorText}>{errors.password_confirmation}</span>}
          </div>

          <button type="submit" className={styles.btnPrimary} disabled={loading || !token || !email}>
            {loading ? 'Resetting...' : 'Reset Password'}
          </button>
        </form>

        <p className={styles.footer}>
          <Link href="/login">Back to Login</Link>
        </p>
      </div>
    </div>
  )
}
