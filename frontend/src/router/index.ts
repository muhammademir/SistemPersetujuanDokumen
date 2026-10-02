import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    component: () => import('@/layouts/AuthLayout.vue'),
    children: [
      { path: '', redirect: '/login' },
      {
        path: 'login',
        name: 'Login',
        component: () => import('@/views/auth/Login.vue'),
        meta: { guest: true },
      },
      {
        path: 'register',
        name: 'Register',
        component: () => import('@/views/auth/Register.vue'),
        meta: { guest: true },
      },
    ],
  },
  {
    path: '/pemohon',
    component: () => import('@/layouts/DashboardLayout.vue'),
    meta: { requiresAuth: true, role: 'pemohon' },
    children: [
      {
        path: 'dashboard',
        name: 'DashboardPemohon',
        component: () => import('@/views/pemohon/Dashboard.vue'),
      },
      {
        path: 'submit',
        name: 'SubmitDocument',
        component: () => import('@/views/pemohon/SubmitDocument.vue'),
      },
    ],
  },
  {
    path: '/penilai',
    component: () => import('@/layouts/DashboardLayout.vue'),
    meta: { requiresAuth: true, role: 'penilai' },
    children: [
      {
        path: 'dashboard',
        name: 'DashboardPenilai',
        component: () => import('@/views/penilai/Dashboard.vue'),
      },
      {
        path: 'review',
        name: 'ReviewList',
        component: () => import('@/views/penilai/Dashboard.vue'),
      },
    ],
  },
  { path: '/:pathMatch(.*)*', redirect: '/login' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Navigation guard — cek dari Pinia store, bukan localStorage
router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore()

  // Tunggu session check selesai (hanya sekali saat app load)
  if (!authStore.isReady) {
    await authStore.initAuth()
  }

  const isAuthenticated = authStore.isAuthenticated

  if (to.matched.some(r => r.meta.requiresAuth) && !isAuthenticated) {
    return next({ name: 'Login' })
  }

  if (to.matched.some(r => r.meta.guest) && isAuthenticated) {
    if (authStore.isPenilai) return next('/penilai/dashboard')
    return next('/pemohon/dashboard')
  }

  next()
})

export default router
