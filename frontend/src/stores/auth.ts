import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/plugins/axios'
import type { User, LoginCredentials, RegisterData } from '@/types'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const isReady = ref(false)

  const isAuthenticated = computed(() => !!user.value)
  const isPemohon = computed(() => user.value?.role === 'pemohon')
  const isPenilai = computed(() => user.value?.role === 'penilai')
  const userRole = computed(() => user.value?.role ?? 'pemohon')
  const userName = computed(() => user.value?.name ?? '')
  const userInitials = computed(() => {
    if (!user.value?.name) return '??'
    const parts = user.value.name.trim().split(/\s+/)
    return parts.length >= 2
      ? (parts[0][0] + parts[1][0]).toUpperCase()
      : parts[0].substring(0, 2).toUpperCase()
  })

  function extractUser(data: any): User {
    const raw = data?.user?.data ?? data?.user ?? data?.data ?? data
    return {
      id: raw.id,
      name: raw.name,
      email: raw.email,
      role: raw.role ?? (Array.isArray(raw.roles) && raw.roles[0]?.name ? raw.roles[0].name : raw.roles?.[0]) ?? 'pemohon',
      roles: raw.roles ?? [],
      is_active: raw.is_active,
    }
  }

  async function initAuth() {
    try {
      const response = await api.get('/me')
      user.value = extractUser(response.data)
    } catch {
      user.value = null
    } finally {
      isReady.value = true
    }
  }

  async function login(credentials: LoginCredentials) {
    loading.value = true
    error.value = null
    try {
      // CSRF cookie initialization for Sanctum
      await api.get('/sanctum/csrf-cookie', {
        baseURL: 'http://localhost:8000',
      })
      const loginRes = await api.post('/login', credentials)
      if (loginRes.data?.user) {
        user.value = extractUser(loginRes.data)
      } else {
        const response = await api.get('/me')
        user.value = extractUser(response.data)
      }

      if (user.value?.role === 'penilai') {
        await router.push('/penilai/dashboard')
      } else {
        await router.push('/pemohon/dashboard')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message
        ?? err.response?.data?.errors?.email?.[0]
        ?? 'Login gagal. Periksa email dan kata sandi Anda.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function register(data: RegisterData) {
    loading.value = true
    error.value = null
    try {
      await api.get('/sanctum/csrf-cookie', {
        baseURL: 'http://localhost:8000',
      })
      const regRes = await api.post('/register', data)
      if (regRes.data?.user) {
        user.value = extractUser(regRes.data)
      } else {
        const response = await api.get('/me')
        user.value = extractUser(response.data)
      }

      if (user.value?.role === 'penilai') {
        await router.push('/penilai/dashboard')
      } else {
        await router.push('/pemohon/dashboard')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Registrasi gagal. Silakan coba lagi.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function logout() {
    try {
      await api.post('/logout')
    } catch {
      // Ignore logout API errors
    } finally {
      user.value = null
      await router.push('/login')
    }
  }

  function clearError() {
    error.value = null
  }

  return {
    user,
    loading,
    error,
    isReady,
    isAuthenticated,
    isPemohon,
    isPenilai,
    userRole,
    userName,
    userInitials,
    initAuth,
    login,
    register,
    logout,
    clearError,
  }
})
