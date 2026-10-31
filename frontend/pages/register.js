import Head from 'next/head'
import Link from 'next/link'
import { useState } from 'react'
import { useAuth } from '../context/AuthContext'
import { useRouter } from 'next/router'
import api from '../lib/api'
import styles from '../styles/Auth.module.css'

export default function Register() {
  const { register: authRegister } = useAuth()
  const router = useRouter()

  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
  })
  const [errors, setErrors] = useState({})
  const [loading, setLoading] = useState(false)
  const [success, setSuccess] = useState(false)
  const [showPassword, setShowPassword] = useState(false)

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    })
    // Clear error when user types
    if (errors[e.target.name]) {
      setErrors({
        ...errors,
        [e.target.name]: ''
      })
    }
  }

  const validateForm = () => {
    const newErrors = {}

    // Name validation
    if (!formData.name || formData.name.length < 2) {
      newErrors.name = 'Name must be at least 2 characters'
    }

    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    if (!formData.email || !emailRegex.test(formData.email)) {
      newErrors.email = 'Please enter a valid email address'
    }

    // Password validation
    if (!formData.password) {
      newErrors.password = 'Password is required'
    } else if (formData.password.length < 8) {
      newErrors.password = 'Password must be at least 8 characters'
    } else if (!/[A-Z]/.test(formData.password)) {
      newErrors.password = 'Password must contain at least one uppercase letter'
    } else if (!/[a-z]/.test(formData.password)) {
      newErrors.password = 'Password must contain at least one lowercase letter'
    } else if (!/[0-9]/.test(formData.password)) {
      newErrors.password = 'Password must contain at least one number'
    }

    // Confirm password
    if (formData.password !== formData.password_confirmation) {
      newErrors.password_confirmation = 'Passwords do not match'
    }

    setErrors(newErrors)
    return Object.keys(newErrors).length === 0
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    
    if (!validateForm()) {
      return
    }

    setLoading(true)
    setErrors({})

    const result = await authRegister({
      name: formData.name,
      email: formData.email,
      password: formData.password,
      password_confirmation: formData.password_confirmation,
    })

    if (result.success) {
      setSuccess(true)
      // Try to send verification email automatically
      try {
        api.sendVerificationEmail().catch(() => {});
      } catch {}
      setTimeout(() => {
        router.push('/dashboard')
      }, 2000)
    } else {
      setErrors({ general: result.error || 'Registration failed' })
    }
    setLoading(false)
  }

  if (success) {
    return (
      <div className={styles.container}>
        <Head>
          <title>Registration Successful - CyberLab Academy</title>
        </Head>
        <div className={styles.card}>
          <div className={styles.successIcon}>✓</div>
          <h1 className={styles.title}>Registration Successful!</h1>
          <p className={styles.subtitle}>Redirecting to dashboard...</p>
          <div className={styles.verifyNotice}>
            📧 A verification email has been sent to your email address.
            Please verify your email to access all features.
          </div>
        </div>
      </div>
    )
  }

  return (
    <div className={styles.container}>
      <Head>
        <title>Register - CyberLab Academy</title>
      </Head>
      
      <div className={styles.card}>
        <h1 className={styles.title}>Create Account</h1>
        <p className={styles.subtitle}>Start your cybersecurity journey today</p>
        
        {errors.general && (
          <div className={styles.error}>{errors.general}</div>
        )}
        
        <form onSubmit={handleSubmit} className={styles.form}>
          <div className={styles.inputGroup}>
            <label>Full Name</label>
            <input 
              type="text" 
              name="name"
              placeholder="John Doe" 
              className={styles.input}
              value={formData.name}
              onChange={handleChange}
            />
            {errors.name && <span className={styles.errorText}>{errors.name}</span>}
          </div>
          
          <div className={styles.inputGroup}>
            <label>Email</label>
            <input 
              type="email" 
              name="email"
              placeholder="you@example.com" 
              className={styles.input}
              value={formData.email}
              onChange={handleChange}
            />
            {errors.email && <span className={styles.errorText}>{errors.email}</span>}
          </div>
          
          <div className={styles.inputGroup}>
            <label>Password</label>
            <div className={styles.passwordWrapper}>
              <input 
                type={showPassword ? "text" : "password"}
                name="password"
                placeholder="Create a strong password" 
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
            
            {/* Password strength indicator */}
            {formData.password && (
              <div className={styles.passwordStrength}>
                <div className={styles.strengthBar}>
                  <div 
                    className={styles.strengthFill}
                    style={{
                      width: `${Math.min(100, (formData.password.length / 12) * 100)}%`,
                      backgroundColor: formData.password.length < 8 ? '#ff4444' : 
                                      formData.password.length < 12 ? '#ffaa00' : '#00ff88'
                    }}
                  />
                </div>
                <span className={styles.strengthText}>
                  {formData.password.length < 8 ? 'Weak' : 
                   formData.password.length < 12 ? 'Medium' : 'Strong'}
                </span>
              </div>
            )}
            
            <div className={styles.passwordHints}>
              <span className={/[A-Z]/.test(formData.password) ? styles.hintMet : ''}>✓ Uppercase</span>
              <span className={/[a-z]/.test(formData.password) ? styles.hintMet : ''}>✓ Lowercase</span>
              <span className={/[0-9]/.test(formData.password) ? styles.hintMet : ''}>✓ Number</span>
              <span className={formData.password.length >= 8 ? styles.hintMet : ''}>✓ 8+ characters</span>
            </div>
          </div>
          
          <div className={styles.inputGroup}>
            <label>Confirm Password</label>
            <input 
              type="password"
              name="password_confirmation"
              placeholder="Confirm your password" 
              className={styles.input}
              value={formData.password_confirmation}
              onChange={handleChange}
            />
            {errors.confirmPassword && <span className={styles.errorText}>{errors.confirmPassword}</span>}
          </div>
          
          <div className={styles.terms}>
            <label className={styles.checkbox}>
              <input type="checkbox" required /> I agree to the <a href="#">Terms</a> and <a href="#">Privacy Policy</a>
            </label>
          </div>
          
          <button 
            type="submit" 
            className={styles.btnPrimary}
            disabled={loading}
          >
            {loading ? 'Creating Account...' : 'Create Account'}
          </button>
        </form>
        
        <p className={styles.footer}>
          Already have an account? <Link href="/login">Sign in</Link>
        </p>
      </div>
    </div>
  )
}
