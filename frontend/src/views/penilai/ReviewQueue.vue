<template>
  <div class="max-w-[1240px] mx-auto flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-foreground mb-1">
          Antrean Penilaian Dokumen
        </h1>
        <p class="text-xs text-muted-foreground">
          Daftar permohonan yang berstatus Menunggu Verifikasi atau Sedang Ditinjau
        </p>
      </div>
      <Badge
        variant="secondary"
        class="bg-amber-500/10 text-amber-600 border-amber-500/30 text-xs font-bold px-3 py-1.5 self-start sm:self-auto"
      >
        {{ pendingList.length }} Dokumen Menunggu Tindakan
      </Badge>
    </div>

    <!-- Feedback Notification -->
    <Alert v-if="alertMessage" class="bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/30 flex items-center justify-between py-2.5">
      <AlertDescription class="text-xs font-medium">
        {{ alertMessage }}
      </AlertDescription>
      <Button variant="ghost" size="icon" class="h-6 w-6 text-emerald-600 hover:text-emerald-800 p-0" @click="alertMessage = ''">
        <X class="w-3.5 h-3.5" />
      </Button>
    </Alert>

    <!-- Priority Review Cards -->
    <div v-if="pendingList.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <Card
        v-for="app in pendingList"
        :key="app.id"
        class="hover:shadow-md hover:border-primary/50 transition-all flex flex-col justify-between border"
      >
        <CardHeader class="p-4 pb-0 flex flex-row items-center justify-between gap-2 space-y-0">
          <StatusBadge :status="app.status" />
          <span class="font-mono text-muted-foreground text-xs">{{ app.code }}</span>
        </CardHeader>

        <CardContent class="p-4 space-y-2.5">
          <div class="flex items-center gap-1.5 flex-wrap">
            <Badge variant="secondary" class="text-[10px] font-bold uppercase">
              {{ app.document_type }}
            </Badge>
            <Badge
              v-if="app.revision_count > 0"
              variant="outline"
              class="text-[10px] font-bold text-amber-600 bg-amber-500/10 border-amber-500/30"
            >
              Revisi ke-{{ app.revision_count }}
            </Badge>
          </div>

          <h3 class="text-base font-bold text-foreground leading-snug line-clamp-1">
            {{ app.title }}
          </h3>

          <p class="text-xs text-muted-foreground line-clamp-2 leading-relaxed">
            {{ app.description || 'Tidak ada catatan tambahan.' }}
          </p>

          <div class="text-[11px] text-muted-foreground">
            Pemohon: <span class="font-semibold text-foreground">{{ app.applicant?.name || '-' }}</span>
          </div>
        </CardContent>

        <CardFooter class="p-4 pt-3 flex items-center justify-between border-t mt-auto">
          <Button
            variant="ghost"
            size="sm"
            class="text-xs font-semibold gap-1 text-muted-foreground hover:text-foreground h-8"
            @click="openDetailModal(app)"
          >
            <FileText class="w-3.5 h-3.5" />
            <span>Lihat Berkas</span>
          </Button>
          <Button
            size="sm"
            class="text-xs font-semibold gap-1 h-8"
            @click="openReviewModal(app)"
          >
            <CheckSquare class="w-3.5 h-3.5" />
            <span>Beri Penilaian</span>
          </Button>
        </CardFooter>
      </Card>
    </div>

    <!-- Empty State -->
    <Card v-else class="shadow-sm text-center py-12 border">
      <CardContent class="flex flex-col items-center justify-center gap-3">
        <CheckCircle2 class="w-12 h-12 text-emerald-500" />
        <h2 class="text-base font-bold text-foreground">Antrean Kosong</h2>
        <p class="text-xs text-muted-foreground max-w-[360px]">
          Saat ini tidak ada permohonan dokumen yang menunggu verifikasi atau penilaian.
        </p>
      </CardContent>
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
import { Card, CardHeader, CardContent, CardFooter } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { CheckCircle2, FileText, CheckSquare, X } from 'lucide-vue-next'
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
