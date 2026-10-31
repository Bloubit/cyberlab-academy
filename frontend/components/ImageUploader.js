import { useState, useRef, useCallback } from 'react'
import api from '../lib/api'

const UPLOAD_STYLES = {
  container: {
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    gap: '0.75rem',
  },
  dropzone: {
    width: '100%',
    minHeight: 140,
    border: '2px dashed rgba(0, 212, 255, 0.3)',
    borderRadius: '12px',
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
    justifyContent: 'center',
    gap: '0.5rem',
    padding: '1.5rem',
    cursor: 'pointer',
    transition: 'all 0.2s ease',
    background: 'rgba(0, 212, 255, 0.03)',
  },
  dropzoneActive: {
    borderColor: '#00d4ff',
    background: 'rgba(0, 212, 255, 0.08)',
    boxShadow: '0 0 20px rgba(0, 212, 255, 0.15)',
  },
  dropzoneError: {
    borderColor: '#ff4757',
    background: 'rgba(255, 71, 87, 0.05)',
  },
  previewContainer: {
    position: 'relative',
    display: 'inline-block',
  },
  preview: {
    width: 120,
    height: 120,
    borderRadius: '12px',
    objectFit: 'cover',
    border: '2px solid rgba(0, 212, 255, 0.2)',
  },
  previewLarge: {
    width: 200,
    height: 200,
    borderRadius: '16px',
    objectFit: 'cover',
    border: '3px solid rgba(0, 212, 255, 0.2)',
  },
  removeBtn: {
    position: 'absolute',
    top: -8,
    right: -8,
    width: 24,
    height: 24,
    borderRadius: '50%',
    border: 'none',
    background: '#ff4757',
    color: '#fff',
    fontSize: '14px',
    cursor: 'pointer',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    fontWeight: 700,
    boxShadow: '0 2px 8px rgba(255, 71, 87, 0.4)',
  },
  progressBar: {
    width: '100%',
    height: 4,
    background: 'rgba(255, 255, 255, 0.1)',
    borderRadius: 2,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    background: 'linear-gradient(90deg, #00d4ff, #9945ff)',
    borderRadius: 2,
    transition: 'width 0.3s ease',
  },
  uploadIcon: {
    fontSize: '2rem',
    opacity: 0.6,
  },
  uploadText: {
    color: '#a0aec0',
    fontSize: '0.85rem',
    textAlign: 'center',
    margin: 0,
  },
  uploadTextHighlight: {
    color: '#00d4ff',
    fontWeight: 600,
  },
  errorMsg: {
    color: '#ff4757',
    fontSize: '0.8rem',
    margin: 0,
  },
}

export default function ImageUploader({
  type = 'general',
  currentImage = null,
  onUploadComplete,
  onRemove,
  size = 'default', // 'default' | 'large'
  label = 'Upload Image',
  aspectRatio = 'any',
}) {
  const [uploading, setUploading] = useState(false)
  const [progress, setProgress] = useState(0)
  const [preview, setPreview] = useState(currentImage)
  const [error, setError] = useState(null)
  const [dragActive, setDragActive] = useState(false)
  const inputRef = useRef(null)

  const simulateProgress = useCallback(() => {
    setProgress(0)
    const interval = setInterval(() => {
      setProgress(prev => {
        if (prev >= 90) {
          clearInterval(interval)
          return 90
        }
        return prev + Math.random() * 15
      })
    }, 200)
    return interval
  }, [])

  const handleFile = useCallback(async (file) => {
    if (!file) return

    // Validate
    const maxSize = 5 * 1024 * 1024 // 5MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml', 'image/webp']

    if (!allowedTypes.includes(file.type)) {
      setError('Invalid file type. Please upload an image.')
      return
    }

    if (file.size > maxSize) {
      setError('File too large. Maximum size is 5MB.')
      return
    }

    setError(null)
    setUploading(true)

    // Show local preview
    const localUrl = URL.createObjectURL(file)
    setPreview(localUrl)

    const progressInterval = simulateProgress()

    try {
      const formData = new FormData()
      formData.append('file', file)
      formData.append('type', type)

      // Use raw fetch for multipart upload
      const token = localStorage.getItem('token')
      const baseUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api'

      const xhr = new XMLHttpRequest()
      
      const result = await new Promise((resolve, reject) => {
        xhr.upload.addEventListener('progress', (e) => {
          if (e.lengthComputable) {
            setProgress(Math.round((e.loaded / e.total) * 100))
          }
        })

        xhr.addEventListener('load', () => {
          try {
            const data = JSON.parse(xhr.responseText)
            if (xhr.status >= 200 && xhr.status < 300 && data.success) {
              resolve(data)
            } else {
              reject(new Error(data.message || 'Upload failed'))
            }
          } catch (e) {
            reject(new Error('Invalid response from server'))
          }
        })

        xhr.addEventListener('error', () => reject(new Error('Network error')))
        xhr.addEventListener('abort', () => reject(new Error('Upload cancelled')))

        xhr.open('POST', `${baseUrl}/uploads/image`)
        xhr.setRequestHeader('Authorization', `Bearer ${token}`)
        xhr.send(formData)
      })

      clearInterval(progressInterval)
      setProgress(100)

      if (result.success) {
        setPreview(result.data.url)
        onUploadComplete?.(result.data.url, result.data.path)
      }
    } catch (err) {
      clearInterval(progressInterval)
      setProgress(0)
      setError(err.message || 'Upload failed')
      // Revert to previous image
      setPreview(currentImage)
    } finally {
      setTimeout(() => {
        setUploading(false)
        setProgress(0)
      }, 500)
    }
  }, [type, currentImage, onUploadComplete, simulateProgress])

  const handleDrop = useCallback((e) => {
    e.preventDefault()
    e.stopPropagation()
    setDragActive(false)
    const file = e.dataTransfer?.files?.[0]
    if (file) handleFile(file)
  }, [handleFile])

  const handleDragOver = useCallback((e) => {
    e.preventDefault()
    e.stopPropagation()
    setDragActive(true)
  }, [])

  const handleDragLeave = useCallback((e) => {
    e.preventDefault()
    e.stopPropagation()
    setDragActive(false)
  }, [])

  const handleClick = () => {
    inputRef.current?.click()
  }

  const handleInputChange = (e) => {
    const file = e.target.files?.[0]
    if (file) handleFile(file)
    e.target.value = ''
  }

  const handleRemove = () => {
    setPreview(null)
    URL.revokeObjectURL(preview)
    onRemove?.()
  }

  const previewStyle = size === 'large' ? UPLOAD_STYLES.previewLarge : UPLOAD_STYLES.preview

  return (
    <div style={UPLOAD_STYLES.container}>
      <input
        ref={inputRef}
        type="file"
        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
        style={{ display: 'none' }}
        onChange={handleInputChange}
      />

      {preview ? (
        <div style={UPLOAD_STYLES.previewContainer}>
          <img
            src={preview}
            alt="Preview"
            style={previewStyle}
            onError={() => setPreview(null)}
          />
          {!uploading && (
            <button
              style={UPLOAD_STYLES.removeBtn}
              onClick={handleRemove}
              title="Remove image"
            >
              ×
            </button>
          )}
          {uploading && (
            <div style={{
              position: 'absolute',
              inset: 0,
              background: 'rgba(0,0,0,0.5)',
              borderRadius: '12px',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
              fontSize: '0.8rem',
              color: '#fff',
            }}>
              {Math.round(progress)}%
            </div>
          )}
        </div>
      ) : (
        <div
          style={{
            ...UPLOAD_STYLES.dropzone,
            ...(dragActive ? UPLOAD_STYLES.dropzoneActive : {}),
            ...(error ? UPLOAD_STYLES.dropzoneError : {}),
          }}
          onClick={handleClick}
          onDrop={handleDrop}
          onDragOver={handleDragOver}
          onDragLeave={handleDragLeave}
        >
          {uploading ? (
            <>
              <div style={UPLOAD_STYLES.progressBar}>
                <div style={{ ...UPLOAD_STYLES.progressFill, width: `${progress}%` }} />
              </div>
              <p style={UPLOAD_STYLES.uploadText}>Uploading... {Math.round(progress)}%</p>
            </>
          ) : (
            <>
              <div style={UPLOAD_STYLES.uploadIcon}>📷</div>
              <p style={UPLOAD_STYLES.uploadText}>
                <span style={UPLOAD_STYLES.uploadTextHighlight}>Click to upload</span> or drag and drop
              </p>
              <p style={UPLOAD_STYLES.uploadText}>PNG, JPG, GIF, WebP up to 5MB</p>
            </>
          )}
        </div>
      )}

      {error && <p style={UPLOAD_STYLES.errorMsg}>⚠️ {error}</p>}
      {!error && label && preview && (
        <p style={{ color: '#718096', fontSize: '0.75rem', margin: 0 }}>
          {uploading ? 'Uploading...' : 'Click × to remove'}
        </p>
      )}
    </div>
  )
}
