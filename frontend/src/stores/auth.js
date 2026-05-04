import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(JSON.parse(localStorage.getItem('auth_user') || 'null'))
  const token = ref(localStorage.getItem('auth_token') || null)

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const userRoles = computed(() => user.value?.roles || [])
  const userPermissions = computed(() => user.value?.permissions || [])

  function hasRole(role) {
    if (Array.isArray(role)) {
      return role.some((r) => userRoles.value.includes(r))
    }
    return userRoles.value.includes(role)
  }

  function hasPermission(permission) {
    return userPermissions.value.includes(permission)
  }

  async function login(email, password) {
    const response = await api.post('/auth/login', { email, password })
    const data = response.data.data

    token.value = data.token
    user.value = data.user

    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('auth_user', JSON.stringify(data.user))

    return data.user
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
    }
  }

  async function fetchMe() {
    const response = await api.get('/auth/me')
    user.value = response.data.data
    localStorage.setItem('auth_user', JSON.stringify(user.value))
    return user.value
  }

  return { user, token, isAuthenticated, userRoles, userPermissions, hasRole, hasPermission, login, logout, fetchMe }
})
