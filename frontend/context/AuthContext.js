import { createContext, useContext, useState, useEffect, useCallback } from 'react';
import api from '../lib/api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  // Load user from localStorage on mount
  useEffect(() => {
    const token = localStorage.getItem('token');
    const savedUser = localStorage.getItem('user');

    if (token && savedUser) {
      try {
        setUser(JSON.parse(savedUser));
        // Verify token is still valid
        api.getMe()
          .then(res => {
            if (res.success) {
              setUser(res.data);
              localStorage.setItem('user', JSON.stringify(res.data));
            }
          })
          .catch(() => {
            // Token expired, clear
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            setUser(null);
          })
          .finally(() => setLoading(false));
      } catch {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        setLoading(false);
      }
    } else {
      setLoading(false);
    }
  }, []);

  const login = useCallback(async (email, password) => {
    setError(null);
    try {
      const res = await api.login(email, password);
      if (res.success) {
        localStorage.setItem('token', res.data.token);
        localStorage.setItem('user', JSON.stringify(res.data.user));
        setUser(res.data.user);
        return { success: true };
      }
      setError(res.message || 'Login failed');
      return { success: false, error: res.message };
    } catch (err) {
      const msg = err.message || 'Login failed';
      setError(msg);
      return { success: false, error: msg };
    }
  }, []);

  const register = useCallback(async (data) => {
    setError(null);
    try {
      const res = await api.register(data);
      if (res.success) {
        localStorage.setItem('token', res.data.token);
        localStorage.setItem('user', JSON.stringify(res.data.user));
        setUser(res.data.user);
        return { success: true };
      }
      setError(res.message || 'Registration failed');
      return { success: false, error: res.message };
    } catch (err) {
      const msg = err.message || 'Registration failed';
      setError(msg);
      return { success: false, error: msg };
    }
  }, []);

  const logout = useCallback(async () => {
    try {
      await api.logout();
    } catch {
      // Ignore logout errors
    }
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    setUser(null);
    setError(null);
  }, []);

  const updateUser = useCallback((userData) => {
    setUser(prev => ({ ...prev, ...userData }));
    localStorage.setItem('user', JSON.stringify({ ...user, ...userData }));
  }, [user]);

  const sendVerificationEmail = useCallback(async () => {
    try {
      const res = await api.sendVerificationEmail();
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  }, []);

  const verifyEmail = useCallback(async (token) => {
    try {
      const res = await api.verifyEmail(token);
      if (res.success && user) {
        setUser(prev => ({ ...prev, email_verified_at: new Date().toISOString() }));
        const saved = JSON.parse(localStorage.getItem('user') || '{}');
        saved.email_verified_at = new Date().toISOString();
        localStorage.setItem('user', JSON.stringify(saved));
      }
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  }, [user]);

  const sendPasswordResetLink = useCallback(async (email) => {
    try {
      const res = await api.sendPasswordResetLink(email);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  }, []);

  const resetPassword = useCallback(async (data) => {
    try {
      const res = await api.resetPassword(data);
      return res;
    } catch (err) {
      return { success: false, message: err.message };
    }
  }, []);

  const clearError = useCallback(() => setError(null), []);

  const value = {
    user,
    loading,
    error,
    isAuthenticated: !!user,
    login,
    register,
    logout,
    updateUser,
    sendVerificationEmail,
    verifyEmail,
    sendPasswordResetLink,
    resetPassword,
    clearError,
  };

  return (
    <AuthContext.Provider value={value}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}

export default AuthContext;
