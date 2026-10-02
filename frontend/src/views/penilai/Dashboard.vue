<template>
  <div class="max-w-[1240px] mx-auto flex flex-col gap-6">
    <!-- Header Hero Banner -->
    <header class="bg-surface-dark text-on-dark p-6 sm:p-8 rounded-sm flex items-start justify-between gap-6 flex-wrap animate-fade-in-up border border-hairline-strong">
      <div>
        <div class="w-3 h-3 bg-primary mb-3"></div>
        <h1 class="font-brand text-2xl sm:text-3xl font-bold leading-tight mb-2">
          Panel Penilai Persetujuan Dokumen
        </h1>
        <p class="text-sm text-on-dark-mute max-w-[540px] leading-relaxed">
          Verifikasi kelayakan administratif dan teknis permohonan dokumen. Berikan keputusan Disetujui, Permintaan Revisi, atau Penolakan secara akurat.
        </p>
      </div>

      <div class="flex items-center gap-4">
        <div class="text-center px-5 py-3 border border-hairline-strong rounded-sm bg-surface-elevated/40">
          <span class="block text-2xl font-bold text-warning leading-tight">
            {{ docStore.stats.submitted + docStore.stats.under_review }}
          </span>
          <span class="block text-[10px] font-bold uppercase text-on-dark-mute tracking-wider mt-0.5">Antrean Perlu Review</span>
        </div>
        <div class="text-center px-5 py-3 border border-hairline-strong rounded-sm bg-surface-elevated/40">
          <span class="block text-2xl font-bold text-emerald-400 leading-tight">
            {{ docStore.stats.approved }}
          </span>
          <span class="block text-[10px] font-bold uppercase text-on-dark-mute tracking-wider mt-0.5">Total Disetujui</span>
        </div>
      </div>
    </header>

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

    <!-- 6 KPI Stat Summary Cards -->
    <section class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      <div
        v-for="card in statCards"
        :key="card.label"
        class="bg-canvas border border-hairline rounded-sm p-4 flex flex-col justify-between hover:border-primary transition-all relative overflow-hidden"
      >
        <div class="flex items-center justify-between mb-2">
          <span class="text-[11px] font-bold uppercase tracking-wider text-mute">{{ card.label }}</span>
          <Icon :icon="card.icon" class="text-lg" :class="card.colorClass" />
        </div>
        <div class="font-brand text-2xl font-bold" :class="card.colorClass">
          {{ card.value }}
        </div>
      </div>
    </section>

    <!-- Workload Distribution & Priority Queue -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Status Distribution Chart -->
      <div class="bg-canvas border border-hairline rounded-sm p-6 relative overflow-hidden flex flex-col">
        <div class="absolute top-0 left-0 w-3 h-3 bg-primary"></div>
        <h2 class="font-brand text-base font-bold text-ink mb-1">Status Beban Permohonan</h2>
        <p class="text-xs text-mute mb-4">Statistik permohonan yang masuk ke sistem</p>

        <div class="flex-1 flex items-center justify-center min-h-[220px]">
          <apexchart
            v-if="chartReady && chartDataTotal > 0"
            type="bar"
            width="100%"
            height="240"
            :options="barChartOptions"
            :series="barChartSeries"
          />
          <div v-else class="flex flex-col items-center justify-center text-mute text-xs gap-2 py-8">
            <Icon icon="mdi:chart-bar" class="text-3xl" />
            <span>Memuat grafik statistik...</span>
          </div>
        </div>
      </div>

      <!-- Quick Pending Review List -->
      <div class="lg:col-span-2 bg-canvas border border-hairline rounded-sm p-6 relative overflow-hidden flex flex-col">
        <div class="absolute top-0 right-0 w-3 h-3 bg-primary"></div>
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-brand text-base font-bold text-ink mb-0.5">Antrean Menunggu Verifikasi</h2>
            <p class="text-xs text-mute">Daftar permohonan prioritas yang membutuhkan tindakan penilaian</p>
          </div>
          <span class="text-xs font-bold text-warning bg-warning/10 px-2.5 py-1 rounded-sm border border-warning/20">
            {{ docStore.pendingApplications.length }} Menunggu
          </span>
        </div>

        <div class="flex-1 flex flex-col divide-y divide-hairline">
          <div
            v-for="app in docStore.pendingApplications.slice(0, 6)"
            :key="app.id"
            class="py-3 flex items-center justify-between gap-3 hover:bg-surface-soft/60 px-2 -mx-2 rounded transition-colors"
          >
            <div class="flex items-center gap-3 min-w-0">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-primary/10 text-primary border border-primary/20">
                {{ app.document_type }}
              </span>
              <div class="flex flex-col min-w-0">
                <span
                  class="text-xs font-bold text-ink truncate cursor-pointer hover:text-primary transition-colors"
                  @click="openDetailModal(app)"
                >
                  {{ app.title }}
                </span>
                <span class="text-[11px] text-mute flex items-center gap-1.5 mt-0.5">
                  <span class="font-mono">{{ app.code }}</span>
                  · Pemohon: {{ app.applicant?.name || '-' }}
                  · {{ formatDate(app.submitted_at || app.created_at) }}
                </span>
              </div>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
              <button
                @click="openReviewModal(app)"
                class="px-3.5 py-1.5 bg-primary text-ink text-xs font-bold rounded-sm border-none cursor-pointer hover:bg-primary-dark transition-colors shadow-xs"
              >
                Tinjau Sekarang
              </button>
            </div>
          </div>

          <div v-if="!docStore.pendingApplications.length" class="flex flex-col items-center justify-center py-12 text-mute text-xs gap-2">
            <Icon icon="mdi:check-all" class="text-3xl text-emerald-500" />
            <span class="font-semibold text-ink">Semua permohonan sudah selesai ditinjau</span>
            <span>Tidak ada permohonan yang menunggu verifikasi saat ini.</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Comprehensive Applications Table -->
    <section class="bg-canvas border border-hairline rounded-sm p-6 flex flex-col gap-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="font-brand text-lg font-bold text-ink">Seluruh Data Permohonan</h2>
          <p class="text-xs text-mute">Tinjau, cari, filter, dan telusuri seluruh riwayat pengajuan dokumen</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <div class="relative min-w-[220px]">
            <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 text-mute text-base" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari kode atau judul..."
              class="w-full h-9 pl-9 pr-3 bg-surface-soft border border-hairline rounded-sm text-xs text-ink outline-none focus:border-primary transition-colors"
            />
          </div>

          <select
            v-model="selectedType"
            class="h-9 px-3 bg-surface-soft border border-hairline rounded-sm text-xs text-ink outline-none focus:border-primary transition-colors"
          >
            <option value="">Semua Jenis</option>
            <option value="SLF">SLF</option>
            <option value="AMDAL">AMDAL</option>
            <option value="IMB">IMB</option>
            <option value="UKL-UPL">UKL-UPL</option>
            <option value="SIUP">SIUP</option>
          </select>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="flex gap-1.5 overflow-x-auto pb-1 border-b border-hairline text-xs">
        <button
          v-for="tab in filterTabs"
          :key="tab.value"
          @click="activeStatusFilter = tab.value"
          class="inline-flex items-center gap-1.5 px-3 py-2 border-b-2 font-bold cursor-pointer transition-all whitespace-nowrap"
          :class="activeStatusFilter === tab.value ? 'border-primary text-primary bg-primary/5' : 'border-transparent text-mute hover:text-ink hover:bg-surface-soft'"
        >
          <span>{{ tab.label }}</span>
          <span
            class="px-1.5 py-0.2 rounded-full text-[10px]"
            :class="activeStatusFilter === tab.value ? 'bg-primary text-ink' : 'bg-surface-soft text-mute'"
          >
            {{ tab.count }}
          </span>
        </button>
      </div>

      <!-- Data Table -->
      <div class="overflow-x-auto">
        <table class="w-full border-collapse text-left text-xs">
          <thead>
            <tr class="bg-surface-soft/60 border-b border-hairline">
              <th class="py-3 px-3 font-bold uppercase tracking-wider text-mute">Kode / Dokumen</th>
              <th class="py-3 px-3 font-bold uppercase tracking-wider text-mute">Pemohon</th>
              <th class="py-3 px-3 font-bold uppercase tracking-wider text-mute">Tanggal Masuk</th>
              <th class="py-3 px-3 font-bold uppercase tracking-wider text-mute">Status</th>
              <th class="py-3 px-3 font-bold uppercase tracking-wider text-mute text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-hairline">
            <tr
              v-for="app in filteredApplications"
              :key="app.id"
              class="hover:bg-surface-soft/40 transition-colors"
            >
              <td class="py-3 px-3">
                <div class="flex flex-col">
                  <div class="flex items-center gap-2">
                    <span class="font-mono text-mute text-[11px]">{{ app.code }}</span>
                    <span class="px-1.5 py-0.2 bg-primary/10 text-primary border border-primary/20 rounded text-[9px] font-bold uppercase">
                      {{ app.document_type }}
                    </span>
                  </div>
                  <span class="font-bold text-ink text-sm mt-0.5 max-w-[320px] truncate">{{ app.title }}</span>
                </div>
              </td>
              <td class="py-3 px-3">
                <div class="flex flex-col">
                  <span class="font-semibold text-ink">{{ app.applicant?.name || '-' }}</span>
                  <span class="text-mute text-[11px]">{{ app.applicant?.email || '' }}</span>
                </div>
              </td>
              <td class="py-3 px-3 text-mute whitespace-nowrap">
                {{ formatDate(app.submitted_at || app.created_at) }}
              </td>
              <td class="py-3 px-3 whitespace-nowrap">
                <StatusBadge :status="app.status" />
              </td>
              <td class="py-3 px-3 text-right whitespace-nowrap">
                <div class="flex items-center justify-end gap-2">
                  <button
                    @click="openDetailModal(app)"
                    class="px-2.5 py-1.5 bg-surface-soft border border-hairline rounded-sm text-ink font-bold hover:bg-hairline cursor-pointer transition-colors"
                  >
                    Detail
                  </button>
                  <button
                    v-if="['submitted', 'under_review'].includes(getStatusValue(app.status))"
                    @click="openReviewModal(app)"
                    class="px-3 py-1.5 bg-primary text-ink border-none rounded-sm font-bold hover:bg-primary-dark cursor-pointer transition-colors shadow-xs"
                  >
                    Tinjau
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="!filteredApplications.length" class="flex flex-col items-center justify-center py-12 text-mute text-xs gap-2">
        <Icon icon="mdi:folder-open-outline" class="text-3xl" />
        <span>Tidak ada permohonan yang sesuai filter atau kata kunci.</span>
      </div>
    </section>

    <!-- Modular Review Decision Modal -->
    <ReviewDecisionModal
      v-model="showReviewModal"
      :application="reviewTargetApp"
      :loading="docStore.loading"
      @submit="handleReviewSubmit"
    />

    <!-- Application Detail Modal -->
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
import DocumentCard from '@/components/DocumentCard.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import ApplicationDetailModal from '@/components/ApplicationDetailModal.vue'
import ReviewDecisionModal from '@/components/ReviewDecisionModal.vue'
import { formatDate, getStatusValue, formatDecision } from '@/utils/formatters'
import type { Application } from '@/types'

const docStore = useDocumentStore()

// State
const activeStatusFilter = ref('all')
const searchQuery = ref('')
const selectedType = ref('')
const chartReady = ref(false)
const alertMessage = ref('')

const showDetailModal = ref(false)
const selectedApplication = ref<Application | null>(null)

const showReviewModal = ref(false)
const reviewTargetApp = ref<Application | null>(null)

// KPI Stat Cards configuration
const statCards = computed(() => [
  { label: 'Total', value: docStore.stats.total, icon: 'mdi:folder-outline', colorClass: 'text-ink' },
  { label: 'Menunggu', value: docStore.stats.submitted, icon: 'mdi:clock-outline', colorClass: 'text-warning' },
  { label: 'Ditinjau', value: docStore.stats.under_review, icon: 'mdi:file-search-outline', colorClass: 'text-info' },
  { label: 'Revisi', value: docStore.stats.revision_required, icon: 'mdi:pencil-ruler', colorClass: 'text-orange-500' },
  { label: 'Disetujui', value: docStore.stats.approved, icon: 'mdi:check-circle-outline', colorClass: 'text-emerald-600' },
  { label: 'Ditolak', value: docStore.stats.rejected, icon: 'mdi:close-circle-outline', colorClass: 'text-error' },
])

// Filter Tabs configuration
const filterTabs = computed(() => [
  { label: 'Semua', value: 'all', count: docStore.stats.total },
  { label: 'Menunggu Verifikasi', value: 'submitted', count: docStore.stats.submitted },
  { label: 'Sedang Ditinjau', value: 'under_review', count: docStore.stats.under_review },
  { label: 'Perlu Revisi', value: 'revision_required', count: docStore.stats.revision_required },
  { label: 'Disetujui', value: 'approved', count: docStore.stats.approved },
  { label: 'Ditolak', value: 'rejected', count: docStore.stats.rejected },
])

// Filtered Applications
const filteredApplications = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  return docStore.applications.filter((app) => {
    const statusVal = getStatusValue(app.status)
    const matchesStatus = activeStatusFilter.value === 'all' || statusVal === activeStatusFilter.value
    const matchesType = !selectedType.value || app.document_type === selectedType.value
    const matchesSearch =
      !query ||
      app.title.toLowerCase().includes(query) ||
      app.code.toLowerCase().includes(query) ||
      (app.applicant?.name && app.applicant.name.toLowerCase().includes(query))
    return matchesStatus && matchesSearch && matchesType
  })
})

// Workload Chart Data
const chartDataTotal = computed(() => {
  return (
    docStore.stats.submitted +
    docStore.stats.under_review +
    docStore.stats.revision_required +
    docStore.stats.approved +
    docStore.stats.rejected
  )
})

const barChartSeries = computed(() => [
  {
    name: 'Jumlah Dokumen',
    data: [
      docStore.stats.submitted,
      docStore.stats.under_review,
      docStore.stats.revision_required,
      docStore.stats.approved,
      docStore.stats.rejected,
    ],
  },
])

const barChartOptions = computed(() => ({
  chart: {
    type: 'bar',
    fontFamily: 'Inter, Arial, sans-serif',
    toolbar: { show: false },
    background: 'transparent',
  },
  plotOptions: {
    bar: {
      borderRadius: 2,
      columnWidth: '45%',
      distributed: true,
    },
  },
  dataLabels: { enabled: false },
  colors: ['#df6500', '#0284c7', '#ea580c', '#10b981', '#ef4444'],
  xaxis: {
    categories: ['Menunggu', 'Ditinjau', 'Revisi', 'Disetujui', 'Ditolak'],
    labels: { style: { fontSize: '11px', fontWeight: 600 } },
  },
  yaxis: { labels: { style: { fontSize: '11px' } } },
  legend: { show: false },
  grid: { borderColor: '#e5e5e5', strokeDashArray: 3 },
}))

// Modal Handlers
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
    const targetCode = reviewTargetApp.value.code
    await docStore.reviewApplication(reviewTargetApp.value.id, payload)

    const label = formatDecision(payload.decision).toLowerCase()
    alertMessage.value = `Permohonan "${targetTitle}" (${targetCode}) berhasil ${label}!`
    showReviewModal.value = false
    reviewTargetApp.value = null

    await Promise.all([docStore.fetchDashboard(), docStore.fetchApplications()])
  } catch {
    // Error notification handled by docStore
  }
}

onMounted(async () => {
  await Promise.all([docStore.fetchDashboard(), docStore.fetchApplications()])
  setTimeout(() => {
    chartReady.value = true
  }, 200)
})
</script>
