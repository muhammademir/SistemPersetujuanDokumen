import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/plugins/axios'
import type { User, LoginCredentials, RegisterData } from '@/types'
import router from '@/router'

export const useAuthStore = defineStore('auth', () => {
  // State hanya di memory (Pinia), BUKAN di localStorage
  const user = ref<User | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const isReady = ref(false) // apakah sudah cek session

  const isAuthenticated = computed(() => !!user.value)
  const isPemohon = computed(() => user.value?.role === 'pemohon')
  const isPenilai = computed(() => user.value?.role === 'penilai')
  const userName = computed(() => user.value?.name ?? '')
  const userInitials = computed(() => {
    if (!user.value?.name) return '??'
    const parts = user.value.name.split(' ')
    return parts.length >= 2
      ? (parts[0][0] + parts[1][0]).toUpperCase()
      : parts[0].substring(0, 2).toUpperCase()
  })

  /**
   * Cek session saat app init — panggil /api/user
   * Jika cookie session masih valid, user akan terisi
   */
  async function initAuth() {
    try {
      const response = await api.get('/user')
      user.value = response.data?.data ?? response.data
    } catch {
      user.value = null
    } finally {
      isReady.value = true
    }
  }

  /**
   * Login via Sanctum SPA cookie-based auth
   * 1. GET /sanctum/csrf-cookie (set XSRF-TOKEN cookie)
   * 2. POST /login (session di-set via httpOnly cookie)
   * 3. GET /api/user (ambil data user)
   */
  async function login(credentials: LoginCredentials) {
    loading.value = true
    error.value = null
    try {
      // Step 1: CSRF cookie
      await api.get('/sanctum/csrf-cookie', {
        baseURL: import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000',
      })
      // Step 2: Login (session cookie set by server)
      await api.post('/auth/login', credentials)
      // Step 3: Fetch user data
      const response = await api.get('/user')
      user.value = response.data?.data ?? response.data

      // Redirect berdasarkan role
      if (user.value?.role === 'penilai') {
        await router.push('/penilai/dashboard')
      } else {
        await router.push('/pemohon/dashboard')
      }
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Login gagal. Periksa email dan password Anda.'
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
        baseURL: import.meta.env.VITE_API_BASE_URL?.replace('/api', '') || 'http://localhost:8000',
      })
      await api.post('/auth/register', data)
      const response = await api.get('/user')
      user.value = response.data?.data ?? response.data

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
      await api.post('/auth/logout')
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
    userName,
    userInitials,
    initAuth,
    login,
    register,
    logout,
    clearError,
  }
})
