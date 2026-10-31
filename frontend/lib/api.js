const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';

class ApiClient {
  constructor() {
    this.baseUrl = API_BASE;
  }

  getToken() {
    if (typeof window !== 'undefined') {
      return localStorage.getItem('token');
    }
    return null;
  }

  async request(endpoint, options = {}) {
    const token = this.getToken();
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...options.headers,
    };

    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }

    const url = `${this.baseUrl}${endpoint}`;

    try {
      const response = await fetch(url, {
        ...options,
        headers,
      });

      const data = await response.json();

      if (!response.ok) {
        throw new ApiError(
          data.message || `HTTP ${response.status}`,
          response.status,
          data
        );
      }

      return data;
    } catch (error) {
      if (error instanceof ApiError) {
        throw error;
      }
      throw new ApiError('Network error. Please check your connection.', 0);
    }
  }

  // Auth
  login(email, password) {
    return this.request('/auth/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    });
  }

  register(data) {
    return this.request('/auth/register', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  logout() {
    return this.request('/auth/logout', { method: 'POST' });
  }

  getMe() {
    return this.request('/auth/me');
  }

  updateProfile(data) {
    return this.request('/auth/profile', {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  changePassword(data) {
    return this.request('/auth/change-password', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  // Email Verification
  sendVerificationEmail() {
    return this.request('/auth/email/verify/send', { method: 'POST' });
  }

  verifyEmail(token) {
    return this.request('/auth/email/verify', {
      method: 'POST',
      body: JSON.stringify({ token }),
    });
  }

  // Password Reset
  sendPasswordResetLink(email) {
    return this.request('/auth/password/forgot', {
      method: 'POST',
      body: JSON.stringify({ email }),
    });
  }

  resetPassword(data) {
    return this.request('/auth/password/reset', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  // Lessons
  getLesson(id) {
    return this.request(`/lessons/${id}`);
  }

  // Courses
  getCourses(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/courses${query ? `?${query}` : ''}`);
  }

  getFeaturedCourses() {
    return this.request('/courses/featured');
  }

  getEnrolledCourses() {
    return this.request('/courses/enrolled');
  }

  getCourse(id) {
    return this.request(`/courses/${id}`);
  }

  enrollCourse(id) {
    return this.request(`/courses/${id}/enroll`, { method: 'POST' });
  }

  getCourseProgress(id) {
    return this.request(`/courses/${id}/progress`);
  }

  // Labs
  getLabs(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/labs${query ? `?${query}` : ''}`);
  }

  getLab(id) {
    return this.request(`/labs/${id}`);
  }

  startLab(id) {
    return this.request(`/labs/${id}/start`, { method: 'POST' });
  }

  stopLab(id) {
    return this.request(`/labs/${id}/stop`, { method: 'POST' });
  }

  submitFlag(labId, challengeId, flag) {
    return this.request(`/labs/${labId}/challenges/${challengeId}/submit`, {
      method: 'POST',
      body: JSON.stringify({ flag }),
    });
  }

  getMyLabs() {
    return this.request('/labs/mine');
  }

  resetLab(id) {
    return this.request(`/labs/${id}/reset`, { method: 'POST' });
  }

  getLabInstanceStatus(id) {
    return this.request(`/labs/${id}/instance`);
  }

  getInstanceDetails(instanceId) {
    return this.request(`/instances/${instanceId}`);
  }

  // Quizzes
  getQuizzes(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/quizzes${query ? `?${query}` : ''}`);
  }

  getQuiz(id) {
    return this.request(`/quizzes/${id}`);
  }

  startQuiz(id) {
    return this.request(`/quizzes/${id}/start`, { method: 'POST' });
  }

  submitQuiz(id, data) {
    return this.request(`/quizzes/${id}/submit`, {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  getQuizResults(id, attemptId) {
    return this.request(`/quizzes/${id}/results/${attemptId}`);
  }

  // Certificates
  getCertificates() {
    return this.request('/certificates');
  }

  getCertificate(id) {
    return this.request(`/certificates/${id}`);
  }

  verifyCertificate(number) {
    return this.request(`/certificates/verify/${number}`);
  }

  // Payments
  getPlans() {
    return this.request('/payments/plans');
  }

  getSubscriptions() {
    return this.request('/payments/subscriptions');
  }

  subscribe(planId, paymentMethod = 'click') {
    return this.request(`/payments/subscriptions/${planId}/subscribe`, {
      method: 'POST',
      body: JSON.stringify({ payment_method: paymentMethod }),
    });
  }

  cancelSubscription() {
    return this.request('/payments/subscriptions/cancel', { method: 'POST' });
  }

  getPaymentHistory() {
    return this.request('/payments/history');
  }

  // Notifications
  getNotifications() {
    return this.request('/notifications');
  }

  getUnreadCount() {
    return this.request('/notifications/unread-count');
  }

  markAsRead(id) {
    return this.request(`/notifications/${id}/read`, { method: 'POST' });
  }

  markAllAsRead() {
    return this.request('/notifications/read-all', { method: 'POST' });
  }

  // User
  getDashboard() {
    return this.request('/user/dashboard');
  }

  // ============ UPLOADS ============

  /**
   * Upload a single image file.
   * Uses FormData for multipart upload.
   */
  async uploadImage(file, type = 'general') {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('type', type)

    const token = this.getToken()
    const headers = {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
    }

    // Don't set Content-Type for FormData - browser sets it with boundary
    const response = await fetch(`${this.baseUrl}/uploads/image`, {
      method: 'POST',
      headers,
      body: formData,
    })

    const data = await response.json()
    if (!response.ok) {
      throw new ApiError(
        data.message || `Upload failed: HTTP ${response.status}`,
        response.status,
        data
      )
    }
    return data
  }

  /**
   * Upload multiple images.
   */
  async uploadImages(files, type = 'general') {
    const formData = new FormData()
    files.forEach(file => formData.append('files[]', file))
    formData.append('type', type)

    const token = this.getToken()
    const response = await fetch(`${this.baseUrl}/uploads/images`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
      body: formData,
    })

    const data = await response.json()
    if (!response.ok) {
      throw new ApiError(
        data.message || `Upload failed: HTTP ${response.status}`,
        response.status,
        data
      )
    }
    return data
  }

  /**
   * Delete an uploaded file.
   */
  deleteUpload(path) {
    return this.request('/uploads/delete', {
      method: 'DELETE',
      body: JSON.stringify({ path }),
    })
  }

  // ============ ADMIN ============

  // Admin Dashboard
  getAdminDashboard() {
    return this.request('/admin/dashboard');
  }

  // Admin Users
  getAdminUsers(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/admin/users${query ? `?${query}` : ''}`);
  }

  getAdminUser(id) {
    return this.request(`/admin/users/${id}`);
  }

  updateAdminUser(id, data) {
    return this.request(`/admin/users/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  banUser(id, reason) {
    return this.request(`/admin/users/${id}/ban`, {
      method: 'POST',
      body: JSON.stringify({ reason }),
    });
  }

  unbanUser(id) {
    return this.request(`/admin/users/${id}/unban`, { method: 'POST' });
  }

  assignUserRole(id, role) {
    return this.request(`/admin/users/${id}/assign-role`, {
      method: 'POST',
      body: JSON.stringify({ role }),
    });
  }

  getAdminRoles() {
    return this.request('/admin/users/roles');
  }

  // Admin Courses
  getAdminCourses(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/admin/courses${query ? `?${query}` : ''}`);
  }

  getAdminCourse(id) {
    return this.request(`/admin/courses/${id}`);
  }

  createAdminCourse(data) {
    return this.request('/admin/courses', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  updateAdminCourse(id, data) {
    return this.request(`/admin/courses/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  deleteAdminCourse(id) {
    return this.request(`/admin/courses/${id}`, { method: 'DELETE' });
  }

  toggleCoursePublish(id) {
    return this.request(`/admin/courses/${id}/toggle-publish`, { method: 'POST' });
  }

  // Admin Categories
  getAdminCategories() {
    return this.request('/admin/courses/categories/list');
  }

  createAdminCategory(data) {
    return this.request('/admin/courses/categories', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  updateAdminCategory(id, data) {
    return this.request(`/admin/courses/categories/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  deleteAdminCategory(id) {
    return this.request(`/admin/courses/categories/${id}`, { method: 'DELETE' });
  }

  // Admin Labs
  getAdminLabs(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/admin/labs${query ? `?${query}` : ''}`);
  }

  getAdminLab(id) {
    return this.request(`/admin/labs/${id}`);
  }

  createAdminLab(data) {
    return this.request('/admin/labs', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  updateAdminLab(id, data) {
    return this.request(`/admin/labs/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  deleteAdminLab(id) {
    return this.request(`/admin/labs/${id}`, { method: 'DELETE' });
  }

  toggleLabPublish(id) {
    return this.request(`/admin/labs/${id}/toggle-publish`, { method: 'POST' });
  }

  // Admin Challenges
  getAdminChallenges(labId) {
    return this.request(`/admin/labs/${labId}/challenges`);
  }

  createAdminChallenge(labId, data) {
    return this.request(`/admin/labs/${labId}/challenges`, {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  // Admin Badges
  getAdminBadges(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/admin/content/badges${query ? `?${query}` : ''}`);
  }

  createAdminBadge(data) {
    return this.request('/admin/content/badges', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  updateAdminBadge(id, data) {
    return this.request(`/admin/content/badges/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  deleteAdminBadge(id) {
    return this.request(`/admin/content/badges/${id}`, { method: 'DELETE' });
  }

  // Admin Lab Instances
  getAdminInstances(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/admin/instances${query ? `?${query}` : ''}`);
  }

  stopAdminInstance(id) {
    return this.request(`/admin/instances/${id}/stop`, { method: 'POST' });
  }

  // Admin Content
  getAdminContentStats() {
    return this.request('/admin/content/stats');
  }

  getAdminQuizzes(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/admin/content/quizzes${query ? `?${query}` : ''}`);
  }

  getCourseModules(courseId) {
    return this.request(`/admin/content/courses/${courseId}/modules`);
  }

  updateAdminLesson(lessonId, data) {
    return this.request(`/admin/content/lessons/${lessonId}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  // ============ PUBLIC ============

  // Public - Leaderboard
  getLeaderboard(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/leaderboard${query ? `?${query}` : ''}`);
  }

  // Public - Home Page
  getHomeData() {
    return this.request('/home');
  }

  // Public courses
  getPublishedCourses(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/courses${query ? `?${query}` : ''}`);
  }
}

class ApiError extends Error {
  constructor(message, status, data) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.data = data;
  }
}

const api = new ApiClient();
export default api;
