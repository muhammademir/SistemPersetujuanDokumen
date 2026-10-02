<template>
  <div class="max-w-[1200px] mx-auto">
    <!-- Hero -->
    <div class="bg-surface-dark text-on-dark p-8 rounded-sm flex items-start justify-between gap-6 flex-wrap mb-6 animate-fade-in-up">
      <div>
        <div class="w-3 h-3 bg-primary mb-4"></div>
        <h1 class="font-brand text-2xl font-bold mb-2">Panel Penilai</h1>
        <p class="text-sm text-on-dark-mute max-w-[500px]">Tinjau dan berikan keputusan untuk dokumen yang diajukan oleh pemohon.</p>
      </div>
      <div class="text-center px-6 py-4 border border-hairline-strong rounded-sm">
        <span class="block text-3xl font-bold text-primary leading-tight">{{ docStore.stats.pending }}</span>
        <span class="block text-[11px] font-bold uppercase text-on-dark-mute tracking-wider mt-1">Perlu Ditinjau</span>
      </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <StatCard v-for="(s, i) in statCards" :key="s.label" v-bind="s" :class="['animate-fade-in-up', `delay-${(i+1)*100}`]" />
    </div>

    <!-- Chart + Pending -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <div class="relative overflow-hidden bg-canvas border border-hairline rounded-sm p-6">
        <div class="absolute top-0 left-0 w-3 h-3 bg-primary"></div>
        <h2 class="font-brand text-lg font-bold text-ink">Ringkasan Penilaian</h2>
        <div class="mt-4">
          <apexchart v-if="chartReady" type="bar" height="260" :options="chartOptions" :series="chartSeries" />
          <div v-else class="flex flex-col items-center justify-center h-[200px] text-mute text-sm gap-2">
            <Icon icon="mdi:chart-bar" class="text-3xl" /><span>Memuat grafik...</span>
          </div>
        </div>
      </div>

      <div class="relative overflow-hidden bg-canvas border border-hairline rounded-sm p-6">
        <div class="absolute bottom-0 right-0 w-3 h-3 bg-primary"></div>
        <h2 class="font-brand text-lg font-bold text-ink flex items-center gap-2">
          Menunggu Penilaian
          <span v-if="docStore.stats.pending" class="bg-primary text-ink text-xs font-bold px-2 py-0.5 rounded-full">{{ docStore.stats.pending }}</span>
        </h2>
        <div class="mt-4 flex flex-col">
          <div v-for="doc in docStore.pendingDocuments.slice(0,5)" :key="doc.id"
            class="flex items-center justify-between gap-3 py-3 border-b border-hairline last:border-b-0 cursor-pointer hover:bg-surface-soft hover:-mx-6 hover:px-6 transition-all"
            @click="openReviewModal(doc)">
            <div class="flex items-center gap-2.5 flex-1 min-w-0">
              <Icon icon="mdi:file-document-outline" class="text-xl text-mute flex-shrink-0" />
              <div class="flex flex-col min-w-0">
                <span class="text-xs font-semibold text-ink truncate">{{ doc.title }}</span>
                <span class="text-[11px] text-mute mt-0.5">{{ doc.user?.name ?? 'Pemohon' }} · {{ formatDate(doc.created_at) }}</span>
              </div>
            </div>
            <button class="bg-transparent border-none text-primary text-xs font-bold cursor-pointer whitespace-nowrap">Tinjau →</button>
          </div>
          <div v-if="!docStore.pendingDocuments.length" class="flex flex-col items-center gap-2 py-8 text-mute text-sm">
            <Icon icon="mdi:check-all" class="text-2xl" /><span>Semua dokumen sudah ditinjau</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-canvas border border-hairline rounded-sm p-6 overflow-hidden">
      <div class="flex items-center justify-between flex-wrap gap-4 mb-4">
        <h2 class="font-brand text-lg font-bold text-ink">Semua Dokumen</h2>
        <div class="flex gap-1 overflow-x-auto">
          <button v-for="tab in filterTabs" :key="tab.value" @click="activeFilter = tab.value"
            class="px-4 py-2 border-none rounded-sm font-brand text-xs font-bold cursor-pointer transition-all whitespace-nowrap"
            :class="activeFilter === tab.value ? 'bg-ink text-on-dark' : 'bg-transparent text-ink hover:bg-surface-soft'">
            {{ tab.label }}
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
          <thead>
            <tr>
              <th class="text-left px-3 py-2.5 text-xs font-bold uppercase tracking-wider text-mute border-b border-hairline whitespace-nowrap">Dokumen</th>
              <th class="text-left px-3 py-2.5 text-xs font-bold uppercase tracking-wider text-mute border-b border-hairline whitespace-nowrap">Pemohon</th>
              <th class="text-left px-3 py-2.5 text-xs font-bold uppercase tracking-wider text-mute border-b border-hairline whitespace-nowrap">Tanggal</th>
              <th class="text-left px-3 py-2.5 text-xs font-bold uppercase tracking-wider text-mute border-b border-hairline whitespace-nowrap">Status</th>
              <th class="text-left px-3 py-2.5 text-xs font-bold uppercase tracking-wider text-mute border-b border-hairline whitespace-nowrap">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="doc in filteredDocuments" :key="doc.id" class="hover:bg-surface-soft transition-colors">
              <td class="px-3 py-3 border-b border-hairline">
                <div class="flex flex-col">
                  <span class="font-semibold text-ink truncate max-w-[250px]">{{ doc.title }}</span>
                  <span class="text-xs text-mute mt-0.5 flex items-center gap-1"><Icon icon="mdi:paperclip" class="text-xs" /> {{ doc.file_name }}</span>
                </div>
              </td>
              <td class="px-3 py-3 border-b border-hairline">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full bg-surface-soft border border-hairline flex items-center justify-center text-[10px] font-bold text-ink flex-shrink-0">{{ getInitials(doc.user?.name) }}</div>
                  <span>{{ doc.user?.name ?? '-' }}</span>
                </div>
              </td>
              <td class="px-3 py-3 border-b border-hairline text-mute text-xs whitespace-nowrap">{{ formatDate(doc.created_at) }}</td>
              <td class="px-3 py-3 border-b border-hairline"><StatusBadge :status="doc.status" /></td>
              <td class="px-3 py-3 border-b border-hairline">
                <button v-if="doc.status === 'pending'" @click="openReviewModal(doc)"
                  class="px-4 py-1.5 bg-primary text-ink border-none rounded-sm text-xs font-bold cursor-pointer hover:bg-primary-dark transition-colors">Tinjau</button>
                <span v-else class="text-xs text-mute">Selesai</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="!filteredDocuments.length" class="text-center py-8 text-mute text-sm">
        <Icon icon="mdi:inbox-outline" class="text-xl" /> Tidak ada dokumen
      </div>
    </div>

    <!-- Review Modal -->
    <Teleport to="body">
      <transition name="modal">
        <div v-if="showReviewModal && selectedDoc" class="fixed inset-0 bg-black/60 flex items-center justify-center p-6 z-[100]" @click.self="closeReviewModal">
          <div class="bg-canvas rounded-sm w-full max-w-[560px] max-h-[90vh] overflow-y-auto animate-fade-in-up">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-hairline">
              <h2 class="text-lg font-bold text-ink">Tinjauan Dokumen</h2>
              <button @click="closeReviewModal" class="w-8 h-8 flex items-center justify-center bg-transparent border border-hairline rounded-sm text-mute cursor-pointer hover:bg-surface-soft transition-colors">
                <Icon icon="mdi:close" />
              </button>
            </div>

            <!-- Body -->
            <div class="px-6 py-5 flex flex-col gap-5">
              <!-- Doc info -->
              <div class="relative bg-surface-soft rounded-sm p-5">
                <div class="absolute top-0 left-0 w-3 h-3 bg-primary"></div>
                <h3 class="text-base font-bold text-ink mb-1.5">{{ selectedDoc.title }}</h3>
                <p class="text-sm text-body leading-relaxed mb-3">{{ selectedDoc.description }}</p>
                <div class="flex flex-wrap gap-4 text-xs text-mute">
                  <span class="flex items-center gap-1"><Icon icon="mdi:paperclip" /> {{ selectedDoc.file_name }}</span>
                  <span class="flex items-center gap-1"><Icon icon="mdi:account-outline" /> {{ selectedDoc.user?.name }}</span>
                  <span class="flex items-center gap-1"><Icon icon="mdi:calendar-outline" /> {{ formatDate(selectedDoc.created_at) }}</span>
                </div>
              </div>

              <!-- Decision -->
              <div class="flex flex-col gap-2">
                <label class="text-sm font-bold text-ink">Keputusan *</label>
                <div class="grid grid-cols-3 gap-2">
                  <button v-for="opt in decisionOptions" :key="opt.value" type="button" @click="reviewForm.status = opt.value"
                    class="flex flex-col items-center gap-1 px-3 py-4 bg-transparent border rounded-sm cursor-pointer transition-all font-brand text-xs font-bold"
                    :class="reviewForm.status === opt.value ? opt.activeClass : 'border-hairline hover:border-primary'">
                    <Icon :icon="opt.icon" class="text-xl" />
                    <span>{{ opt.label }}</span>
                  </button>
                </div>
              </div>

              <!-- Notes -->
              <div class="flex flex-col gap-1.5">
                <label class="text-sm font-bold text-ink">Catatan {{ reviewForm.status === 'revision' ? '(Wajib)' : '(Opsional)' }}</label>
                <textarea v-model="reviewForm.notes" rows="4" :required="reviewForm.status === 'revision'"
                  class="w-full px-4 py-3 bg-canvas border border-hairline rounded-sm font-brand text-sm text-ink outline-none resize-y min-h-[80px] transition-colors focus:border-primary focus:border-2 focus:px-[15px] placeholder:text-ash"
                  placeholder="Berikan catatan atau alasan keputusan Anda..."></textarea>
              </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-hairline">
              <button @click="closeReviewModal"
                class="px-5 py-2.5 bg-transparent border border-hairline rounded-sm font-brand text-sm font-bold text-ink cursor-pointer hover:bg-surface-soft transition-all">Batal</button>
              <button @click="handleSubmitReview"
                :disabled="!reviewForm.status || docStore.loading || (reviewForm.status === 'revision' && !reviewForm.notes.trim())"
                class="inline-flex items-center gap-2 px-5 py-2.5 border-none rounded-sm font-brand text-sm font-bold cursor-pointer transition-all disabled:bg-surface-soft disabled:text-ash disabled:cursor-not-allowed"
                :class="submitBtnClass">
                <span v-if="docStore.loading" class="w-3.5 h-3.5 border-2 border-transparent border-t-current rounded-full animate-spin"></span>
                <span>{{ submitBtnLabel }}</span>
              </button>
            </div>
          </div>
        </div>
      </transition>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { Icon } from '@iconify/vue'
import { useDocumentStore } from '@/stores/document'
import StatCard from '@/components/StatCard.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import type { Document } from '@/types'

const docStore = useDocumentStore()
const activeFilter = ref('all')
const chartReady = ref(false)
const showReviewModal = ref(false)
const selectedDoc = ref<Document | null>(null)
const reviewForm = reactive({ status: '' as string, notes: '' })

const statCards = computed(() => [
  { value: docStore.stats.total, label: 'Total Dokumen', icon: 'mdi:folder-outline', variant: 'primary' as const },
  { value: docStore.stats.pending, label: 'Menunggu', icon: 'mdi:clock-outline', variant: 'warning' as const },
  { value: docStore.stats.approved, label: 'Disetujui', icon: 'mdi:check-circle-outline', variant: 'success' as const },
  { value: docStore.stats.revision + docStore.stats.rejected, label: 'Revisi/Tolak', icon: 'mdi:undo-variant', variant: 'danger' as const },
])

const filterTabs = [
  { label: 'Semua', value: 'all' }, { label: 'Menunggu', value: 'pending' },
  { label: 'Disetujui', value: 'approved' }, { label: 'Revisi', value: 'revision' }, { label: 'Ditolak', value: 'rejected' },
]

const decisionOptions = [
  { value: 'approved', label: 'Setuju', icon: 'mdi:check-circle-outline', activeClass: 'border-primary border-2 bg-primary/10 text-success-deep px-[11px] py-[15px]' },
  { value: 'revision', label: 'Revisi', icon: 'mdi:pencil-outline', activeClass: 'border-warning border-2 bg-warning/10 text-warning px-[11px] py-[15px]' },
  { value: 'rejected', label: 'Tolak', icon: 'mdi:close-circle-outline', activeClass: 'border-error border-2 bg-error/10 text-error px-[11px] py-[15px]' },
]

const filteredDocuments = computed(() => activeFilter.value === 'all' ? docStore.documents : docStore.documents.filter(d => d.status === activeFilter.value))

const submitBtnLabel = computed(() => {
  if (docStore.loading) return 'Memproses...'
  return { approved: 'Setujui Dokumen', revision: 'Kirim Revisi', rejected: 'Tolak Dokumen' }[reviewForm.status] ?? 'Pilih Keputusan'
})

const submitBtnClass = computed(() => ({
  'bg-primary text-ink hover:bg-primary-dark': reviewForm.status === 'approved',
  'bg-warning text-white': reviewForm.status === 'revision',
  'bg-error text-white': reviewForm.status === 'rejected',
  'bg-surface-soft text-ash': !reviewForm.status,
}))

const chartSeries = computed(() => [{ name: 'Jumlah', data: [docStore.stats.pending, docStore.stats.approved, docStore.stats.revision, docStore.stats.rejected] }])
const chartOptions = computed(() => ({
  chart: { type: 'bar', fontFamily: 'Inter, Arial, sans-serif', toolbar: { show: false }, background: 'transparent' },
  plotOptions: { bar: { borderRadius: 2, columnWidth: '50%', distributed: true } },
  dataLabels: { enabled: false },
  colors: ['#df6500', '#76b900', '#ef9100', '#e52020'],
  xaxis: { categories: ['Menunggu', 'Disetujui', 'Revisi', 'Ditolak'], labels: { style: { fontSize: '12px', fontWeight: 600 } } },
  yaxis: { labels: { style: { fontSize: '12px' } } },
  legend: { show: false },
  grid: { borderColor: '#cccccc', strokeDashArray: 4 },
}))

function formatDate(d: string) { return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }
function getInitials(n?: string) { if (!n) return '??'; const p = n.split(' '); return p.length >= 2 ? (p[0][0] + p[1][0]).toUpperCase() : p[0].substring(0, 2).toUpperCase() }

function openReviewModal(doc: Document) { selectedDoc.value = doc; reviewForm.status = ''; reviewForm.notes = ''; showReviewModal.value = true }
function closeReviewModal() { showReviewModal.value = false; selectedDoc.value = null }

async function handleSubmitReview() {
  if (!selectedDoc.value || !reviewForm.status) return
  try { await docStore.reviewDocument(selectedDoc.value.id, { status: reviewForm.status, notes: reviewForm.notes }); closeReviewModal() } catch { /* handled */ }
}

onMounted(() => { docStore.fetchDocuments(); setTimeout(() => { chartReady.value = true }, 300) })
</script>
