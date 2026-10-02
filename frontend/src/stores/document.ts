import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/plugins/axios'
import type { Document, DashboardStats, Review } from '@/types'

export const useDocumentStore = defineStore('document', () => {
  const documents = ref<Document[]>([])
  const currentDocument = ref<Document | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const stats = computed<DashboardStats>(() => {
    const docs = documents.value
    return {
      total: docs.length,
      pending: docs.filter(d => d.status === 'pending').length,
      approved: docs.filter(d => d.status === 'approved').length,
      revision: docs.filter(d => d.status === 'revision').length,
      rejected: docs.filter(d => d.status === 'rejected').length,
    }
  })

  const pendingDocuments = computed(() =>
    documents.value.filter(d => d.status === 'pending')
  )

  const recentDocuments = computed(() =>
    [...documents.value]
      .sort((a, b) => new Date(b.updated_at).getTime() - new Date(a.updated_at).getTime())
      .slice(0, 10)
  )

  async function fetchDocuments() {
    loading.value = true
    error.value = null
    try {
      const response = await api.get('/documents')
      documents.value = response.data?.data ?? response.data ?? []
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Gagal memuat dokumen.'
      // Use mock data for development if API not ready
      if (!documents.value.length) {
        documents.value = generateMockDocuments()
      }
    } finally {
      loading.value = false
    }
  }

  async function fetchDocument(id: number) {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(`/documents/${id}`)
      currentDocument.value = response.data?.data ?? response.data
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Gagal memuat detail dokumen.'
    } finally {
      loading.value = false
    }
  }

  async function submitDocument(formData: FormData) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/documents', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      const newDoc = response.data?.data ?? response.data
      documents.value.unshift(newDoc)
      return newDoc
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Gagal mengirim dokumen.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateDocument(id: number, formData: FormData) {
    loading.value = true
    error.value = null
    try {
      formData.append('_method', 'PUT')
      const response = await api.post(`/documents/${id}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      const updated = response.data?.data ?? response.data
      const index = documents.value.findIndex(d => d.id === id)
      if (index !== -1) documents.value[index] = updated
      return updated
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Gagal memperbarui dokumen.'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function reviewDocument(id: number, review: { status: string; notes: string }) {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(`/documents/${id}/review`, review)
      const updated = response.data?.data ?? response.data
      const index = documents.value.findIndex(d => d.id === id)
      if (index !== -1) documents.value[index] = updated
      return updated
    } catch (err: any) {
      error.value = err.response?.data?.message ?? 'Gagal mengirim review.'
      throw err
    } finally {
      loading.value = false
    }
  }

  function clearError() {
    error.value = null
  }

  // Mock data for development before backend API is ready
  function generateMockDocuments(): Document[] {
    const statuses: Document['status'][] = ['pending', 'approved', 'revision', 'rejected', 'draft']
    const titles = [
      'Proposal Penelitian AI Generatif',
      'Laporan Keuangan Q3 2026',
      'Dokumen Perizinan Usaha',
      'Surat Perjanjian Kerjasama',
      'Rencana Anggaran Biaya 2027',
      'Laporan Audit Internal',
      'Proposal Pengadaan Server',
      'Dokumen SOP Departemen IT',
    ]
    return titles.map((title, i) => ({
      id: i + 1,
      title,
      description: `Deskripsi lengkap untuk ${title.toLowerCase()}. Dokumen ini memerlukan persetujuan dari penilai sebelum dapat diproses lebih lanjut.`,
      file_path: `/storage/documents/doc-${i + 1}.pdf`,
      file_name: `${title.toLowerCase().replace(/\s/g, '-')}.pdf`,
      status: statuses[i % statuses.length],
      user_id: 1,
      user: {
        id: 1,
        name: 'Ahmad Fauzan',
        email: 'ahmad@example.com',
        role: 'pemohon' as const,
      },
      reviews: i % 2 === 0 ? [{
        id: i + 100,
        document_id: i + 1,
        reviewer_id: 2,
        reviewer: { id: 2, name: 'Dr. Siti Aminah', email: 'siti@example.com', role: 'penilai' as const },
        status: statuses[i % 3] === 'pending' ? 'approved' : statuses[i % 3] as any,
        notes: 'Dokumen sudah diperiksa dan sesuai ketentuan.',
        created_at: new Date(Date.now() - i * 86400000).toISOString(),
        updated_at: new Date(Date.now() - i * 86400000).toISOString(),
      }] : [],
      created_at: new Date(Date.now() - (i + 3) * 86400000).toISOString(),
      updated_at: new Date(Date.now() - i * 86400000).toISOString(),
    }))
  }

  return {
    documents,
    currentDocument,
    loading,
    error,
    stats,
    pendingDocuments,
    recentDocuments,
    fetchDocuments,
    fetchDocument,
    submitDocument,
    updateDocument,
    reviewDocument,
    clearError,
  }
})
