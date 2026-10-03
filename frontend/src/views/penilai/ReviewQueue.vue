<template>
  <div class="max-w-[1240px] mx-auto flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="font-brand text-2xl font-bold text-surface-900 dark:text-surface-0 mb-1">
          Antrean Penilaian Dokumen
        </h1>
        <p class="text-xs text-surface-500">
          Daftar permohonan yang berstatus Menunggu Verifikasi atau Sedang Ditinjau
        </p>
      </div>
      <Tag
        :value="`${pendingList.length} Dokumen Menunggu Tindakan`"
        severity="warn"
        class="text-xs font-bold px-3 py-1.5"
      />
    </div>

    <!-- Feedback Notification -->
    <Message v-if="alertMessage" severity="success" :closable="true" @close="alertMessage = ''" class="text-xs">
      {{ alertMessage }}
    </Message>

    <!-- Priority Review Cards -->
    <div v-if="pendingList.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <Card
        v-for="app in pendingList"
        :key="app.id"
        class="border border-surface-200 dark:border-surface-700 hover:shadow-md hover:border-primary-400 transition-all flex flex-col justify-between"
      >
        <template #header>
          <div class="p-4 pb-0 flex items-center justify-between gap-2">
            <StatusBadge :status="app.status" />
            <span class="font-mono text-surface-500 text-xs">{{ app.code }}</span>
          </div>
        </template>

        <template #content>
          <div class="space-y-2.5">
            <div class="flex items-center gap-2">
              <Tag :value="app.document_type" severity="info" class="text-[10px] font-bold uppercase" />
              <Tag
                v-if="app.revision_count > 0"
                :value="`Revisi ke-${app.revision_count}`"
                severity="warn"
                class="text-[10px] font-bold"
              />
            </div>

            <h3 class="font-brand text-base font-bold text-surface-900 dark:text-surface-0 leading-snug line-clamp-1">
              {{ app.title }}
            </h3>

            <p class="text-xs text-surface-600 dark:text-surface-400 line-clamp-2 leading-relaxed">
              {{ app.description || 'Tidak ada catatan tambahan.' }}
            </p>

            <div class="text-[11px] text-surface-500">
              Pemohon: <span class="font-semibold text-surface-800 dark:text-surface-200">{{ app.applicant?.name || '-' }}</span>
            </div>
          </div>
        </template>

        <template #footer>
          <div class="flex items-center justify-between border-t border-surface-200 dark:border-surface-700 pt-3">
            <Button
              label="Lihat Berkas"
              icon="pi pi-file"
              severity="secondary"
              size="small"
              text
              class="text-xs font-bold"
              @click="openDetailModal(app)"
            />
            <Button
              label="Beri Penilaian"
              icon="pi pi-check-square"
              severity="primary"
              size="small"
              class="text-xs font-bold"
              @click="openReviewModal(app)"
            />
          </div>
        </template>
      </Card>
    </div>

    <!-- Empty State -->
    <Card v-else class="border border-surface-200 dark:border-surface-700 shadow-sm text-center py-12">
      <template #content>
        <div class="flex flex-col items-center justify-center gap-3">
          <i class="pi pi-check-circle text-5xl text-emerald-500"></i>
          <h2 class="text-base font-bold text-surface-900 dark:text-surface-0">Antrean Kosong</h2>
          <p class="text-xs text-surface-500 max-w-[360px]">
            Saat ini tidak ada permohonan dokumen yang menunggu verifikasi atau penilaian.
          </p>
        </div>
      </template>
    </Card>

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
import Card from 'primevue/card'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
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
    // Handled in store
  }
}

onMounted(() => {
  docStore.fetchApplications()
})
</script>
