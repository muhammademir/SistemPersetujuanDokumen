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

    <!-- Alert / Feedback Notification -->
    <div v-if="alertMessage" class="flex items-center justify-between gap-3 p-4 bg-primary/10 border border-primary/30 rounded-sm text-sm text-success-deep animate-fade-in">
      <div class="flex items-center gap-2">
        <Icon icon="mdi:check-circle" class="text-lg text-primary flex-shrink-0" />
        <span>{{ alertMessage }}</span>
      </div>
      <button @click="alertMessage = ''" class="bg-transparent border-none text-ink cursor-pointer hover:opacity-70">
        <Icon icon="mdi:close" />
      </button>
    </div>

    <!-- Grid List -->
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

    <!-- Review Decision Modal -->
    <Teleport to="body">
      <transition name="modal">
        <div v-if="showReviewModal && reviewTargetApp" class="fixed inset-0 bg-black/60 flex items-center justify-center p-4 sm:p-6 z-[100] backdrop-blur-xs" @click.self="closeReviewModal">
          <div class="bg-canvas rounded-sm w-full max-w-[580px] max-h-[92vh] overflow-y-auto border border-hairline shadow-2xl animate-fade-in-up">
            <div class="flex items-center justify-between px-6 py-4 border-b border-hairline bg-surface-soft">
              <div class="flex items-center gap-2.5">
                <div class="w-3 h-3 bg-primary"></div>
                <h2 class="text-base font-bold text-ink">Verifikasi & Keputusan Permohonan</h2>
              </div>
              <button @click="closeReviewModal" class="w-8 h-8 flex items-center justify-center bg-transparent border border-hairline rounded-sm text-mute cursor-pointer hover:bg-canvas transition-colors">
                <Icon icon="mdi:close" />
              </button>
            </div>

            <div class="p-6 flex flex-col gap-5">
              <div class="p-4 bg-surface-soft/60 rounded-sm border border-hairline flex flex-col gap-1.5 text-xs">
                <div class="flex items-center justify-between gap-2">
                  <span class="font-mono text-mute font-bold">{{ reviewTargetApp.code }}</span>
                  <span class="px-2 py-0.5 bg-primary/10 text-primary rounded font-bold uppercase">{{ reviewTargetApp.document_type }}</span>
                </div>
                <h3 class="text-sm font-bold text-ink">{{ reviewTargetApp.title }}</h3>
                <p class="text-body text-xs line-clamp-2">{{ reviewTargetApp.description }}</p>
              </div>

              <!-- Decision Buttons -->
              <div class="flex flex-col gap-2">
                <label class="text-xs font-bold uppercase tracking-wider text-ink">Pilih Keputusan Penilaian *</label>
                <div class="grid grid-cols-3 gap-2.5">
                  <button
                    type="button"
                    @click="reviewForm.decision = 'approved'"
                    class="p-3 flex flex-col items-center gap-1.5 rounded-sm border cursor-pointer font-brand text-xs font-bold transition-all"
                    :class="reviewForm.decision === 'approved' ? 'bg-emerald-500/10 border-emerald-500 border-2 text-emerald-700' : 'border-hairline hover:border-emerald-500'"
                  >
                    <Icon icon="mdi:check-circle" class="text-xl text-emerald-600" />
                    <span>Disetujui</span>
                  </button>

                  <button
                    type="button"
                    @click="reviewForm.decision = 'revision_required'"
                    class="p-3 flex flex-col items-center gap-1.5 rounded-sm border cursor-pointer font-brand text-xs font-bold transition-all"
                    :class="reviewForm.decision === 'revision_required' ? 'bg-orange-500/10 border-orange-500 border-2 text-orange-600' : 'border-hairline hover:border-orange-500'"
                  >
                    <Icon icon="mdi:pencil-outline" class="text-xl text-orange-500" />
                    <span>Perlu Revisi</span>
                  </button>

                  <button
                    type="button"
                    @click="reviewForm.decision = 'rejected'"
                    class="p-3 flex flex-col items-center gap-1.5 rounded-sm border cursor-pointer font-brand text-xs font-bold transition-all"
                    :class="reviewForm.decision === 'rejected' ? 'bg-error/10 border-error border-2 text-error' : 'border-hairline hover:border-error'"
                  >
                    <Icon icon="mdi:close-circle" class="text-xl text-error" />
                    <span>Ditolak</span>
                  </button>
                </div>
              </div>

              <!-- Review Notes -->
              <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold uppercase tracking-wider text-ink">
                  Catatan / Keterangan Penilaian {{ reviewForm.decision === 'revision_required' ? '(Wajib Diisi)' : '(Opsional)' }}
                </label>
                <textarea
                  v-model="reviewForm.note"
                  rows="4"
                  :required="reviewForm.decision === 'revision_required'"
                  placeholder="Berikan catatan penilaian atau instruksi revisi..."
                  class="w-full px-4 py-3 bg-canvas border border-hairline rounded-sm font-brand text-xs text-ink outline-none resize-y min-h-[90px] transition-colors focus:border-primary focus:border-2 focus:px-[15px] placeholder:text-ash"
                ></textarea>
              </div>
            </div>

            <div class="flex items-center justify-end gap-2 px-6 py-4 border-t border-hairline bg-surface-soft">
              <button @click="closeReviewModal" class="px-4 py-2 bg-transparent border border-hairline rounded-sm font-brand text-xs font-bold text-ink hover:bg-canvas cursor-pointer">
                Batal
              </button>
              <button
                @click="handleSubmitReview"
                :disabled="!reviewForm.decision || docStore.loading || (reviewForm.decision === 'revision_required' && !reviewForm.note.trim())"
                class="inline-flex items-center gap-2 px-5 py-2 border-none rounded-sm font-brand text-xs font-bold cursor-pointer transition-all disabled:bg-surface-soft disabled:text-ash text-ink bg-primary hover:bg-primary-dark"
              >
                <span v-if="docStore.loading" class="w-3.5 h-3.5 border-2 border-transparent border-t-current rounded-full animate-spin"></span>
                <span>Kirim Keputusan</span>
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>

    <!-- Detail Modal -->
    <ApplicationDetailModal
      v-model="showDetailModal"
      :application="selectedApplication"
      @open-review="handleOpenReviewFromDetail"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { Icon } from '@iconify/vue'
import { useDocumentStore } from '@/stores/document'
import StatusBadge from '@/components/StatusBadge.vue'
import ApplicationDetailModal from '@/components/ApplicationDetailModal.vue'
import type { Application } from '@/types'

const docStore = useDocumentStore()

const alertMessage = ref('')
const showDetailModal = ref(false)
const selectedApplication = ref<Application | null>(null)

const showReviewModal = ref(false)
const reviewTargetApp = ref<Application | null>(null)
const reviewForm = reactive({ decision: 'approved', note: '' })

const pendingList = computed(() => {
  return docStore.applications.filter(app => {
    const val = typeof app.status === 'object' ? app.status.value : app.status
    return ['submitted', 'under_review'].includes(val)
  })
})

function formatDate(d?: string | null) {
  if (!d) return '-'
  return new Date(d).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric',
  })
}

async function openDetailModal(app: Application) {
  await docStore.fetchApplication(app.id)
  selectedApplication.value = docStore.currentApplication || app
  showDetailModal.value = true
}

function openReviewModal(app: Application) {
  reviewTargetApp.value = app
  reviewForm.decision = 'approved'
  reviewForm.note = ''
  showReviewModal.value = true
}

function closeReviewModal() {
  showReviewModal.value = false
  reviewTargetApp.value = null
}

function handleOpenReviewFromDetail(app: Application) {
  showDetailModal.value = false
  openReviewModal(app)
}

async function handleSubmitReview() {
  if (!reviewTargetApp.value || !reviewForm.decision) return
  try {
    await docStore.reviewApplication(reviewTargetApp.value.id, {
      decision: reviewForm.decision,
      note: reviewForm.note,
    })
    alertMessage.value = `Keputusan untuk "${reviewTargetApp.value.title}" berhasil disimpan!`
    closeReviewModal()
    await docStore.fetchApplications()
  } catch {}
}

onMounted(() => {
  docStore.fetchApplications()
})
</script>
