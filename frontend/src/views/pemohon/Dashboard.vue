<template>
  <div class="max-w-[1240px] mx-auto flex flex-col gap-6">
    <!-- Hero Banner -->
    <div class="bg-surface-dark text-on-dark p-6 sm:p-8 rounded-sm flex items-start justify-between gap-6 flex-wrap animate-fade-in-up border border-hairline-strong">
      <div>
        <div class="w-3 h-3 bg-primary mb-3"></div>
        <h1 class="font-brand text-2xl sm:text-3xl font-bold leading-tight mb-2">
          Selamat Datang, <span class="text-primary">{{ authStore.userName }}</span>
        </h1>
        <p class="text-sm text-on-dark-mute max-w-[540px] leading-relaxed">
          Sistem Terpadu Permohonan Dokumen Kelayakan. Pantau status pengajuan Anda dari proses verifikasi administrasi hingga persetujuan akhir.
        </p>
      </div>
      <router-link
        to="/pemohon/submit"
        class="inline-flex items-center gap-2 h-11 px-6 bg-primary text-ink border-none rounded-sm font-brand text-sm font-bold no-underline transition-all hover:bg-primary-dark whitespace-nowrap flex-shrink-0 shadow-md hover:scale-[1.02]"
      >
        <Icon icon="mdi:plus-circle" class="text-lg" />
        <span>Buat Permohonan Baru</span>
      </router-link>
    </div>

    <!-- Feedback Alerts -->
    <div v-if="alertMessage" class="flex items-center justify-between gap-3 p-4 bg-primary/10 border border-primary/30 rounded-sm text-sm text-success-deep animate-fade-in">
      <div class="flex items-center gap-2">
        <Icon icon="mdi:check-circle" class="text-lg text-primary flex-shrink-0" />
        <span>{{ alertMessage }}</span>
      </div>
      <button @click="alertMessage = ''" class="bg-transparent border-none text-ink cursor-pointer hover:opacity-70">
        <Icon icon="mdi:close" />
      </button>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      <div
        v-for="(card, i) in statCards"
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
    </div>

    <!-- Chart + Activity Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Donut Chart -->
      <div class="bg-canvas border border-hairline rounded-sm p-6 relative overflow-hidden flex flex-col">
        <div class="absolute top-0 left-0 w-3 h-3 bg-primary"></div>
        <h2 class="font-brand text-base font-bold text-ink mb-1">Distribusi Status Permohonan</h2>
        <p class="text-xs text-mute mb-4">Proporsi status pengajuan aktif Anda</p>
        
        <div class="flex-1 flex items-center justify-center min-h-[220px]">
          <apexchart
            v-if="chartReady && docStore.stats.total > 0"
            type="donut"
            width="100%"
            height="240"
            :options="chartOptions"
            :series="chartSeries"
          />
          <div v-else class="flex flex-col items-center justify-center text-mute text-xs gap-2 py-8">
            <Icon icon="mdi:chart-arc" class="text-3xl" />
            <span>Belum ada data untuk ditampilkan</span>
          </div>
        </div>
      </div>

      <!-- Recent Status Activity -->
      <div class="lg:col-span-2 bg-canvas border border-hairline rounded-sm p-6 relative overflow-hidden flex flex-col">
        <div class="absolute top-0 right-0 w-3 h-3 bg-primary"></div>
        <div class="flex items-center justify-between mb-4">
          <div>
            <h2 class="font-brand text-base font-bold text-ink mb-0.5">Permohonan Terbaru & Riwayat</h2>
            <p class="text-xs text-mute">Aktivitas pembaharuan dokumen permohonan Anda</p>
          </div>
          <span class="text-xs text-mute font-medium">{{ docStore.applications.length }} Total</span>
        </div>

        <div class="flex-1 flex flex-col divide-y divide-hairline">
          <div
            v-for="app in docStore.recentApplications.slice(0, 5)"
            :key="app.id"
            class="py-3 flex items-center justify-between gap-3 hover:bg-surface-soft/60 px-2 -mx-2 rounded transition-colors cursor-pointer"
            @click="openDetail(app)"
          >
            <div class="flex items-center gap-3 min-w-0">
              <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-surface-soft border border-hairline text-ink">
                {{ app.document_type }}
              </span>
              <div class="flex flex-col min-w-0">
                <span class="text-xs font-bold text-ink truncate">{{ app.title }}</span>
                <span class="text-[11px] text-mute flex items-center gap-1">
                  <span class="font-mono">{{ app.code }}</span> · {{ formatRelativeTime(app.updated_at || app.created_at) }}
                </span>
              </div>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
              <StatusBadge :status="app.status" />
              <button class="text-primary text-xs font-bold bg-transparent border-none cursor-pointer">
                Detail →
              </button>
            </div>
          </div>

          <div v-if="!docStore.applications.length" class="flex flex-col items-center justify-center py-12 text-mute text-xs gap-2">
            <Icon icon="mdi:inbox-outline" class="text-3xl" />
            <span>Belum ada permohonan yang diajukan</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Application List with Filter and Search -->
    <div class="bg-canvas border border-hairline rounded-sm p-6 flex flex-col gap-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h2 class="font-brand text-lg font-bold text-ink">Daftar Semua Permohonan</h2>
          <p class="text-xs text-mute">Kelola dan pantau seluruh permohonan dokumen yang Anda daftarkan</p>
        </div>

        <!-- Search & Filter Controls -->
        <div class="flex items-center gap-2 flex-wrap">
          <div class="relative min-w-[220px]">
            <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 text-mute text-base" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari judul permohonan..."
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

      <!-- Status Tabs -->
      <div class="flex gap-1.5 overflow-x-auto pb-1 border-b border-hairline text-xs">
        <button
          v-for="tab in filterTabs"
          :key="tab.value"
          @click="activeStatusFilter = tab.value"
          class="inline-flex items-center gap-1.5 px-3 py-2 border-b-2 font-bold cursor-pointer transition-all whitespace-nowrap"
          :class="activeStatusFilter === tab.value ? 'border-primary text-primary bg-primary/5' : 'border-transparent text-mute hover:text-ink hover:bg-surface-soft'"
        >
          <span>{{ tab.label }}</span>
          <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="activeStatusFilter === tab.value ? 'bg-primary text-ink' : 'bg-surface-soft text-mute'">
            {{ tab.count }}
          </span>
        </button>
      </div>

      <!-- Grid Cards -->
      <div v-if="filteredApplications.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-2">
        <DocumentCard
          v-for="app in filteredApplications"
          :key="app.id"
          :application="app"
          @click="openDetail(app)"
          @action="handleCardAction(app)"
        />
      </div>

      <!-- Empty Filter State -->
      <div v-else class="flex flex-col items-center justify-center py-16 px-4 bg-surface-soft/40 border border-dashed border-hairline rounded-sm text-center">
        <Icon icon="mdi:folder-open-outline" class="text-4xl text-mute mb-2" />
        <h3 class="text-sm font-bold text-ink">Tidak ada permohonan yang sesuai filter</h3>
        <p class="text-xs text-mute max-w-[340px] mt-1 mb-4">Coba sesuaikan kata kunci pencarian atau pilih tab status yang lain.</p>
        <router-link
          to="/pemohon/submit"
          class="inline-flex items-center gap-1.5 h-9 px-4 bg-primary text-ink font-bold text-xs rounded-sm no-underline hover:bg-primary-dark transition-colors"
        >
          <Icon icon="mdi:plus" /> Buat Permohonan Baru
        </router-link>
      </div>
    </div>

    <!-- Application Detail Modal -->
    <ApplicationDetailModal
      v-model="showDetailModal"
      :application="selectedApplication"
      @submit-app="handleSubmitApplication"
      @edit-app="handleEditApplication"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Icon } from '@iconify/vue'
import { useAuthStore } from '@/stores/auth'
import { useDocumentStore } from '@/stores/document'
import DocumentCard from '@/components/DocumentCard.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import ApplicationDetailModal from '@/components/ApplicationDetailModal.vue'
import { formatRelativeTime, getStatusValue } from '@/utils/formatters'
import type { Application } from '@/types'

const authStore = useAuthStore()
const docStore = useDocumentStore()
const router = useRouter()

const activeStatusFilter = ref('all')
const searchQuery = ref('')
const selectedType = ref('')
const chartReady = ref(false)
const alertMessage = ref('')

const showDetailModal = ref(false)
const selectedApplication = ref<Application | null>(null)

const statCards = computed(() => [
  { label: 'Total', value: docStore.stats.total, icon: 'mdi:folder-outline', colorClass: 'text-ink' },
  { label: 'Draft', value: docStore.stats.draft, icon: 'mdi:file-edit-outline', colorClass: 'text-stone' },
  { label: 'Menunggu', value: docStore.stats.submitted, icon: 'mdi:clock-outline', colorClass: 'text-warning' },
  { label: 'Ditinjau', value: docStore.stats.under_review, icon: 'mdi:file-search-outline', colorClass: 'text-info' },
  { label: 'Revisi', value: docStore.stats.revision_required, icon: 'mdi:pencil-ruler', colorClass: 'text-orange-500' },
  { label: 'Disetujui', value: docStore.stats.approved, icon: 'mdi:check-circle-outline', colorClass: 'text-emerald-600' },
])

const filterTabs = computed(() => [
  { label: 'Semua', value: 'all', count: docStore.stats.total },
  { label: 'Draft', value: 'draft', count: docStore.stats.draft },
  { label: 'Menunggu Verifikasi', value: 'submitted', count: docStore.stats.submitted },
  { label: 'Sedang Ditinjau', value: 'under_review', count: docStore.stats.under_review },
  { label: 'Perlu Revisi', value: 'revision_required', count: docStore.stats.revision_required },
  { label: 'Disetujui', value: 'approved', count: docStore.stats.approved },
  { label: 'Ditolak', value: 'rejected', count: docStore.stats.rejected },
])

const filteredApplications = computed(() => {
  return docStore.applications.filter(app => {
    const statusVal = getStatusValue(app.status)
    const matchesStatus = activeStatusFilter.value === 'all' || statusVal === activeStatusFilter.value
    const matchesSearch = !searchQuery.value.trim() ||
      app.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      app.code.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesType = !selectedType.value || app.document_type === selectedType.value
    return matchesStatus && matchesSearch && matchesType
  })
})

const chartSeries = computed(() => [
  docStore.stats.draft,
  docStore.stats.submitted,
  docStore.stats.under_review,
  docStore.stats.revision_required,
  docStore.stats.approved,
  docStore.stats.rejected,
])

const chartOptions = computed(() => ({
  chart: { type: 'donut', fontFamily: 'Inter, Arial, sans-serif', background: 'transparent' },
  labels: ['Draft', 'Menunggu Verifikasi', 'Sedang Ditinjau', 'Perlu Revisi', 'Disetujui', 'Ditolak'],
  colors: ['#78716c', '#df6500', '#0284c7', '#ea580c', '#10b981', '#ef4444'],
  stroke: { width: 1.5, colors: ['#ffffff'] },
  plotOptions: {
    pie: {
      donut: {
        size: '65%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total',
            fontSize: '13px',
            fontWeight: 700,
            color: '#757575',
          },
        },
      },
    },
  },
  dataLabels: { enabled: false },
  legend: { position: 'bottom', fontSize: '11px', itemMargin: { horizontal: 6, vertical: 3 } },
}))


async function openDetail(app: Application) {
  await docStore.fetchApplication(app.id)
  selectedApplication.value = docStore.currentApplication || app
  showDetailModal.value = true
}

function handleCardAction(app: Application) {
  const statusVal = typeof app.status === 'object' ? app.status.value : app.status
  if (statusVal === 'revision_required') {
    handleEditApplication(app)
  } else if (statusVal === 'draft') {
    handleSubmitApplication(app)
  } else {
    openDetail(app)
  }
}

async function handleSubmitApplication(app: Application) {
  try {
    await docStore.submitApplication(app.id)
    alertMessage.value = `Permohonan "${app.title}" (${app.code}) berhasil diajukan ke penilai!`
    showDetailModal.value = false
    await docStore.fetchDashboard()
    await docStore.fetchApplications()
  } catch {
    // handled by store
  }
}

function handleEditApplication(app: Application) {
  showDetailModal.value = false
  router.push(`/pemohon/submit?edit=${app.id}`)
}

onMounted(async () => {
  await Promise.all([
    docStore.fetchDashboard(),
    docStore.fetchApplications(),
  ])
  setTimeout(() => { chartReady.value = true }, 200)
})
</script>
