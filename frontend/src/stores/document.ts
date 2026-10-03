import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/plugins/axios'
import type { Application, DashboardStats, DashboardSummary } from '@/types'

export const useDocumentStore = defineStore('document', () => {
  const applications = ref<Application[]>([])
  const currentApplication = ref<Application | null>(null)
  const dashboardSummary = ref<DashboardSummary | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const pagination = ref({ current_page: 1, last_page: 1, total: 0 })

  const stats = computed<DashboardStats>(() => {
    const s = dashboardSummary.value?.by_status
    if (s && typeof s === 'object') {
      const getNum = (key: string) => {
        const val = s[key]
        return typeof val === 'number' ? val : (parseInt(String(val), 10) || 0)
      }

      const draft = getNum('draft')
      const submitted = getNum('submitted')
      const under_review = getNum('under_review')
      const revision_required = getNum('revision_required')
      const approved = getNum('approved')
      const rejected = getNum('rejected')
      const sum = draft + submitted + under_review + revision_required + approved + rejected

      return {
        total: sum,
        draft,
        submitted,
        under_review,
        revision_required,
        approved,
        rejected,
      }
    }

    // Fallback: count from loaded applications
    const apps = applications.value
    const getStatusVal = (app: Application) => (typeof app.status === 'object' ? app.status.value : app.status)

    const draft = apps.filter(a => getStatusVal(a) === 'draft').length
    const submitted = apps.filter(a => getStatusVal(a) === 'submitted').length
    const under_review = apps.filter(a => getStatusVal(a) === 'under_review').length
    const revision_required = apps.filter(a => getStatusVal(a) === 'revision_required').length
    const approved = apps.filter(a => getStatusVal(a) === 'approved').length
    const rejected = apps.filter(a => getStatusVal(a) === 'rejected').length

    return {
      total: apps.length,
      draft,
      submitted,
      under_review,
      revision_required,
      approved,
      rejected,
    }
  })

  const pendingList = ref<Application[]>([])

  const pendingApplications = computed(() => {
    if (pendingList.value.length > 0) return pendingList.value
    const getStatusVal = (app: Application) =>
      typeof app.status === 'object' && app.status ? app.status.value : (app.status as any)
    return applications.value.filter(a => ['submitted', 'under_review'].includes(getStatusVal(a)))
  })

  const recentApplications = computed(() =>
    [...applications.value]
      .sort((a, b) => new Date(b.updated_at).getTime() - new Date(a.updated_at).getTime())
      .slice(0, 10)
  )

  async function fetchPendingApplications() {
    try {
      const response = await api.get('/applications', {
        params: { status: 'submitted,under_review', per_page: 20 },
      })
      pendingList.value = response.data?.data ?? response.data ?? []
    } catch {
      // ignore
    }
  }

  async function fetchDashboard() {
    try {
      const response = await api.get('/dashboard/summary')
      dashboardSummary.value = response.data
    } catch {
      // Silently fail, stats will use fallback
    }
    fetchPendingApplications()
  }

  async function fetchApplications(params?: Record<string, string>) {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/applications', { params })
      const data = response.data
      applications.value = data.data ?? data ?? []
      if (data.meta) {
        pagination.value = {
          current_page: data.meta.current_page,
          last_page: data.meta.last_page,
          total: data.meta.total,
        }
      }
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Gagal memuat data permohonan.'
    } finally {
      loading.value = false
    }
  }

  async function fetchApplication(id: number) {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(`/applications/${id}`)
      currentApplication.value = response.data?.data ?? response.data
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Gagal memuat detail permohonan.'
    } finally {
      loading.value = false
    }
  }

  async function createApplication(data: { title: string; description: string; document_type: string }) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/applications', data)
      const newApp = response.data?.data ?? response.data
      applications.value.unshift(newApp)
      return newApp
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Gagal membuat permohonan.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateApplication(id: number, data: { title?: string; description?: string; document_type?: string }) {
    loading.value = true
    error.value = null
    try {
      const response = await api.put(`/applications/${id}`, data)
      const updated = response.data?.data ?? response.data
      const index = applications.value.findIndex(a => a.id === id)
      if (index !== -1) applications.value[index] = updated
      return updated
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Gagal memperbarui permohonan.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function submitApplication(id: number) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(`/applications/${id}/submit`)
      const updated = response.data?.data ?? response.data
      const index = applications.value.findIndex(a => a.id === id)
      if (index !== -1) applications.value[index] = updated
      return updated
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Gagal mengirim permohonan.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function uploadDocuments(applicationId: number, files: File[]) {
    loading.value = true
    error.value = null
    try {
      const formData = new FormData()
      files.forEach(file => formData.append('files[]', file))
      const response = await api.post(`/applications/${applicationId}/documents`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      return response.data?.data ?? response.data
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Gagal mengunggah dokumen.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function reviewApplication(id: number, review: { decision: string; note: string }) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(`/applications/${id}/review`, review)
      const updated = response.data?.data ?? response.data
      const index = applications.value.findIndex(a => a.id === id)
      if (index !== -1) applications.value[index] = updated
      return updated
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Gagal mengirim penilaian.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function deleteApplication(id: number) {
    loading.value = true
    error.value = null
    try {
      await api.delete(`/applications/${id}`)
      applications.value = applications.value.filter(a => a.id !== id)
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Gagal menghapus permohonan.'
      throw err
    } finally {
      loading.value = false
    }
  }

  function clearError() {
    error.value = null
  }

  return {
    applications,
    currentApplication,
    dashboardSummary,
    loading,
    error,
    pagination,
    stats,
    pendingApplications,
    recentApplications,
    fetchDashboard,
    fetchApplications,
    fetchApplication,
    createApplication,
    updateApplication,
    submitApplication,
    uploadDocuments,
    reviewApplication,
    deleteApplication,
    clearError,
  }
})
