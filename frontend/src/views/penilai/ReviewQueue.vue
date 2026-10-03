<template>
  <div class="max-w-[1200px] mx-auto flex flex-col gap-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h1 class="text-xl font-bold text-gray-900 leading-tight">
          Antrean Penilaian Dokumen
        </h1>
        <p class="text-[13px] text-gray-500 mt-0.5">
          Daftar permohonan yang berstatus Menunggu Verifikasi atau Sedang Ditinjau
        </p>
      </div>
      <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-50 border border-amber-200">
        <Clock class="w-4 h-4 text-amber-500" />
        <span class="text-[13px] font-semibold text-amber-700">
          {{ queueItems.length }}
        </span>
        <span class="text-[12px] text-amber-600">Menunggu Tindakan</span>
      </div>
    </div>

    <!-- Feedback Notification -->
    <Alert
      v-if="alertMessage"
      class="bg-emerald-50 text-emerald-700 border-emerald-200 flex items-center justify-between py-2.5"
    >
      <AlertDescription class="text-xs font-medium">
        {{ alertMessage }}
      </AlertDescription>
      <Button
        variant="ghost"
        size="icon"
        class="h-6 w-6 text-emerald-600 hover:text-emerald-800 p-0"
        @click="alertMessage = ''"
      >
        <X class="w-3.5 h-3.5" />
      </Button>
    </Alert>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-gray-400 gap-3">
      <Loader2 class="w-8 h-8 animate-spin text-[#3b49f5]" />
      <span class="text-xs">Memuat antrean penilaian dokumen...</span>
    </div>

    <!-- Priority Review Cards -->
    <div v-else-if="queueItems.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <Card
        v-for="app in queueItems"
        :key="app.id"
        class="hover:shadow-md hover:border-[#3b49f5]/30 transition-all flex flex-col justify-between border-gray-200"
      >
        <CardHeader class="p-4 pb-0 flex flex-row items-center justify-between gap-2 space-y-0">
          <StatusBadge :status="app.status" />
          <span class="font-mono text-gray-400 text-[11px]">{{ app.code }}</span>
        </CardHeader>

        <CardContent class="p-4 space-y-2.5">
          <div class="flex items-center gap-1.5 flex-wrap">
            <Badge
              variant="outline"
              class="text-[10px] font-semibold uppercase text-gray-500 border-gray-300"
            >
              {{ app.document_type }}
            </Badge>
            <Badge
              v-if="app.revision_count > 0"
              variant="secondary"
              class="text-[10px] font-semibold text-amber-600 bg-amber-50 border border-amber-200"
            >
              Revisi ke-{{ app.revision_count }}
            </Badge>
          </div>

          <h3 class="text-[14px] font-bold text-gray-900 leading-snug line-clamp-1">
            {{ app.title }}
          </h3>

          <p class="text-[12px] text-gray-400 line-clamp-2 leading-relaxed">
            {{ app.description || 'Tidak ada catatan tambahan.' }}
          </p>

          <div class="text-[11px] text-gray-400">
            Pemohon: <span class="font-semibold text-gray-700">{{ app.applicant?.name || '-' }}</span>
          </div>
        </CardContent>

        <CardFooter class="p-4 pt-3 flex items-center justify-between border-t border-gray-100 mt-auto">
          <Button
            variant="ghost"
            size="sm"
            class="text-[12px] font-medium gap-1 text-gray-500 hover:text-gray-900 hover:bg-gray-100 h-8"
            @click="openDetailModal(app)"
          >
            <FileText class="w-3.5 h-3.5" />
            <span>Lihat Berkas</span>
          </Button>
          <Button
            size="sm"
            class="text-[12px] font-semibold gap-1 h-8 bg-[#3b49f5] hover:bg-[#2f3ce0] text-white"
            @click="openReviewModal(app)"
          >
            <CheckSquare class="w-3.5 h-3.5" />
            <span>Beri Penilaian</span>
          </Button>
        </CardFooter>
      </Card>
    </div>

    <!-- Empty State -->
    <Card v-else class="text-center py-12 border-gray-200">
      <CardContent class="flex flex-col items-center justify-center gap-3">
        <CheckCircle2 class="w-12 h-12 text-emerald-400" />
        <h2 class="text-[15px] font-bold text-gray-900">Antrean Kosong</h2>
        <p class="text-[12px] text-gray-400 max-w-[360px]">
          Saat ini tidak ada permohonan dokumen yang menunggu verifikasi atau penilaian.
        </p>
      </CardContent>
    </Card>

    <!-- Review Modal -->
    <ReviewDecisionModal
      v-model="showReviewModal"
      :application="reviewTargetApp"
      :loading="docStore.loading"
      @submit="handleReviewSubmit"
    />

    <!-- Detail Modal -->
    <ApplicationDetailModal
      v-model="showDetailModal"
      :application="selectedApplication"
      @open-review="handleOpenReviewFromDetail"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Card, CardHeader, CardContent, CardFooter } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { CheckCircle2, FileText, CheckSquare, X, Clock, Loader2 } from 'lucide-vue-next'
import api from '@/plugins/axios'
import { useDocumentStore } from '@/stores/document'
import StatusBadge from '@/components/StatusBadge.vue'
import ApplicationDetailModal from '@/components/ApplicationDetailModal.vue'
import ReviewDecisionModal from '@/components/ReviewDecisionModal.vue'
import { formatDecision } from '@/utils/formatters'
import type { Application } from '@/types'

const docStore = useDocumentStore()

const alertMessage = ref('')
const loading = ref(false)
const queueItems = ref<Application[]>([])
const showDetailModal = ref(false)
const selectedApplication = ref<Application | null>(null)

const showReviewModal = ref(false)
const reviewTargetApp = ref<Application | null>(null)

async function fetchQueue() {
  loading.value = true
  try {
    const res = await api.get('/applications', {
      params: { status: 'submitted,under_review', per_page: 50 },
    })
    queueItems.value = res.data?.data ?? res.data ?? []
  } catch (e) {
    console.error('Failed to load queue items', e)
  } finally {
    loading.value = false
  }
}

async function openDetailModal(app: Application) {
  await docStore.fetchApplication(app.id)
  selectedApplication.value = docStore.currentApplication || app
  showDetailModal.value = true
}

function openReviewModal(app: Application) {
  reviewTargetApp.value = app
  showReviewModal.value = true
}

function handleOpenReviewFromDetail(app: Application) {
  showDetailModal.value = false
  openReviewModal(app)
}

async function handleReviewSubmit(payload: { decision: string; note: string }) {
  if (!reviewTargetApp.value) return

  try {
    const targetTitle = reviewTargetApp.value.title
    await docStore.reviewApplication(reviewTargetApp.value.id, payload)

    const label = formatDecision(payload.decision).toLowerCase()
    alertMessage.value = `Permohonan "${targetTitle}" berhasil ${label}!`
    showReviewModal.value = false
    reviewTargetApp.value = null

    await Promise.all([fetchQueue(), docStore.fetchDashboard()])
  } catch {
    // Handled in store
  }
}

onMounted(() => {
  fetchQueue()
  docStore.fetchDashboard()
})
</script>
