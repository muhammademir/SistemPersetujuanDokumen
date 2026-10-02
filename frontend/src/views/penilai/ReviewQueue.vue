<template>
  <div class="max-w-[1240px] mx-auto flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="font-brand text-2xl font-bold text-ink mb-1">Antrean Penilaian Dokumen</h1>
        <p class="text-xs text-mute">Daftar permohonan yang berstatus Menunggu Verifikasi atau Sedang Ditinjau</p>
      </div>
      <span class="text-xs font-bold text-warning bg-warning/10 border border-warning/30 px-3 py-1.5 rounded-sm">
        {{ pendingList.length }} Dokumen Menunggu Tindakan
      </span>
    </div>

    <!-- Feedback Notification -->
    <div
      v-if="alertMessage"
      class="flex items-center justify-between gap-3 p-4 bg-primary/10 border border-primary/30 rounded-sm text-sm text-success-deep animate-fade-in"
    >
      <div class="flex items-center gap-2">
        <Icon icon="mdi:check-circle" class="text-lg text-primary flex-shrink-0" />
        <span>{{ alertMessage }}</span>
      </div>
      <button @click="alertMessage = ''" class="bg-transparent border-none text-ink cursor-pointer hover:opacity-70">
        <Icon icon="mdi:close" />
      </button>
    </div>

    <!-- Priority Review Cards -->
    <div v-if="pendingList.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="app in pendingList"
        :key="app.id"
        class="bg-canvas border border-hairline rounded-sm p-5 flex flex-col justify-between hover:border-primary transition-all relative overflow-hidden group shadow-xs"
      >
        <div class="absolute top-0 left-0 w-3 h-3 bg-primary"></div>

        <div>
          <div class="flex items-center justify-between gap-2 mb-2">
            <StatusBadge :status="app.status" />
            <span class="font-mono text-mute text-xs">{{ app.code }}</span>
          </div>

          <div class="flex items-center gap-2 mb-2">
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-primary/10 text-primary border border-primary/20">
              {{ app.document_type }}
            </span>
            <span v-if="app.revision_count > 0" class="text-[10px] font-bold text-orange-600">
              (Revisi ke-{{ app.revision_count }})
            </span>
          </div>

          <h3 class="font-brand text-base font-bold text-ink leading-snug mb-2 group-hover:text-primary transition-colors">
            {{ app.title }}
          </h3>

          <p class="text-xs text-body line-clamp-2 mb-3">
            {{ app.description || 'Tidak ada catatan tambahan.' }}
          </p>

          <div class="text-[11px] text-mute mb-2">
            Pemohon: <span class="font-semibold text-ink">{{ app.applicant?.name || '-' }}</span>
          </div>
        </div>

        <div class="flex items-center justify-between border-t border-hairline pt-3 mt-2">
          <button
            @click="openDetailModal(app)"
            class="px-3 py-1.5 bg-surface-soft border border-hairline rounded-sm text-xs font-bold text-ink hover:bg-hairline cursor-pointer transition-colors"
          >
            Lihat Berkas
          </button>
          <button
            @click="openReviewModal(app)"
            class="px-4 py-1.5 bg-primary text-ink text-xs font-bold rounded-sm border-none cursor-pointer hover:bg-primary-dark transition-colors shadow-xs"
          >
            Beri Penilaian →
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="flex flex-col items-center justify-center py-20 bg-canvas border border-hairline rounded-sm text-center">
      <Icon icon="mdi:check-all" class="text-5xl text-emerald-500 mb-3" />
      <h2 class="text-base font-bold text-ink">Antrean Kosong</h2>
      <p class="text-xs text-mute max-w-[360px] mt-1">Saat ini tidak ada permohonan dokumen yang menunggu verifikasi atau penilaian.</p>
    </div>

    <!-- Modular Review Modal -->
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
import { ref, computed, onMounted } from 'vue'
import { Icon } from '@iconify/vue'
import { useDocumentStore } from '@/stores/document'
import StatusBadge from '@/components/StatusBadge.vue'
import ApplicationDetailModal from '@/components/ApplicationDetailModal.vue'
import ReviewDecisionModal from '@/components/ReviewDecisionModal.vue'
import { getStatusValue, formatDecision } from '@/utils/formatters'
import type { Application } from '@/types'

const docStore = useDocumentStore()

const alertMessage = ref('')
const showDetailModal = ref(false)
const selectedApplication = ref<Application | null>(null)

const showReviewModal = ref(false)
const reviewTargetApp = ref<Application | null>(null)

const pendingList = computed(() => {
  return docStore.applications.filter((app) => {
    const statusVal = getStatusValue(app.status)
    return ['submitted', 'under_review'].includes(statusVal)
  })
})

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

    await docStore.fetchApplications()
  } catch {
    // Error notification handled by docStore
  }
}

onMounted(() => {
  docStore.fetchApplications()
})
</script>
