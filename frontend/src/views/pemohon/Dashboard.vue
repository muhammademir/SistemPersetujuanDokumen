<template>
  <div class="max-w-[1240px] mx-auto flex flex-col gap-6">
    <!-- Hero Banner Card -->
    <Card class="bg-surface-900 text-surface-0 border border-surface-800 shadow-md">
      <template #content>
        <div class="flex items-start justify-between gap-6 flex-wrap">
          <div>
            <div class="w-8 h-8 rounded-lg bg-primary-500 flex items-center justify-center text-surface-900 font-bold mb-3">
              <i class="pi pi-file-edit text-lg"></i>
            </div>
            <h1 class="font-brand text-2xl sm:text-3xl font-bold leading-tight mb-2">
              Selamat Datang, <span class="text-primary-400">{{ authStore.userName }}</span>
            </h1>
            <p class="text-xs sm:text-sm text-surface-300 max-w-[540px] leading-relaxed">
              Sistem Terpadu Permohonan Dokumen Kelayakan. Pantau status pengajuan Anda dari proses verifikasi administrasi hingga persetujuan akhir.
            </p>
          </div>

          <Button
            as="router-link"
            to="/pemohon/submit"
            label="Buat Permohonan Baru"
            icon="pi pi-plus"
            severity="primary"
            class="font-bold shadow-sm"
          />
        </div>
      </template>
    </Card>

    <!-- Feedback Alerts with PrimeVue Message -->
    <Message v-if="alertMessage" severity="success" :closable="true" @close="alertMessage = ''" class="text-xs">
      {{ alertMessage }}
    </Message>

    <!-- Quick Stats Cards (6 KPI Cards using PrimeVue StatCard) -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      <StatCard
        v-for="card in statCards"
        :key="card.label"
        :label="card.label"
        :value="card.value"
        :icon="card.icon"
        :variant="card.variant"
      />
    </div>

    <!-- Chart + Activity Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Donut Chart Card -->
      <Card class="border border-surface-200 dark:border-surface-700 shadow-sm flex flex-col">
        <template #title>
          <div class="flex items-center justify-between">
            <h2 class="font-brand text-base font-bold text-surface-900 dark:text-surface-0">
              Distribusi Status
            </h2>
            <Tag :value="`${docStore.stats.total} Total`" severity="secondary" rounded />
          </div>
        </template>
        <template #subtitle>
          <span class="text-xs text-surface-500">Proporsi status pengajuan aktif Anda</span>
        </template>
        <template #content>
          <div class="flex-1 flex items-center justify-center min-h-[220px]">
            <apexchart
              v-if="chartReady && docStore.stats.total > 0"
              type="donut"
              width="100%"
              height="240"
              :options="chartOptions"
              :series="chartSeries"
            />
            <div v-else class="flex flex-col items-center justify-center text-surface-400 text-xs gap-2 py-8">
              <i class="pi pi-chart-pie text-3xl"></i>
              <span>Belum ada data untuk ditampilkan</span>
            </div>
          </div>
        </template>
      </Card>

      <!-- Recent Status Activity Card -->
      <Card class="lg:col-span-2 border border-surface-200 dark:border-surface-700 shadow-sm flex flex-col">
        <template #title>
          <div class="flex items-center justify-between">
            <div>
              <h2 class="font-brand text-base font-bold text-surface-900 dark:text-surface-0">
                Permohonan Terbaru & Riwayat
              </h2>
            </div>
            <span class="text-xs text-surface-500 font-medium">{{ docStore.applications.length }} Total</span>
          </div>
        </template>
        <template #subtitle>
          <span class="text-xs text-surface-500">Aktivitas pembaruan dokumen permohonan Anda</span>
        </template>
        <template #content>
          <div class="flex-1 flex flex-col divide-y divide-surface-200 dark:divide-surface-700">
            <div
              v-for="app in docStore.recentApplications.slice(0, 5)"
              :key="app.id"
              class="py-3 flex items-center justify-between gap-3 hover:bg-surface-50 dark:hover:bg-surface-800/50 px-2 -mx-2 rounded-lg transition-colors cursor-pointer"
              @click="openDetail(app)"
            >
              <div class="flex items-center gap-3 min-w-0">
                <Tag :value="app.document_type" severity="secondary" class="text-[10px] font-bold uppercase" />
                <div class="flex flex-col min-w-0">
                  <span class="text-xs font-bold text-surface-900 dark:text-surface-0 truncate">{{ app.title }}</span>
                  <span class="text-[11px] text-surface-500 flex items-center gap-1">
                    <span class="font-mono">{{ app.code }}</span> · {{ formatRelativeTime(app.updated_at || app.created_at) }}
                  </span>
                </div>
              </div>
              <div class="flex items-center gap-3 flex-shrink-0">
                <StatusBadge :status="app.status" />
                <Button label="Detail" icon="pi pi-arrow-right" iconPos="right" text size="small" class="text-xs p-0 font-bold" />
              </div>
            </div>

            <div v-if="!docStore.applications.length" class="flex flex-col items-center justify-center py-12 text-surface-400 text-xs gap-2">
              <i class="pi pi-inbox text-3xl"></i>
              <span>Belum ada permohonan yang diajukan</span>
            </div>
          </div>
        </template>
      </Card>
    </div>

    <!-- Application List with Filter and Search -->
    <Card class="border border-surface-200 dark:border-surface-700 shadow-sm">
      <template #title>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h2 class="font-brand text-lg font-bold text-surface-900 dark:text-surface-0">Daftar Semua Permohonan</h2>
            <p class="text-xs text-surface-500 font-normal">Kelola dan pantau seluruh permohonan dokumen yang Anda daftarkan</p>
          </div>

          <!-- Search & Filter Controls -->
          <div class="flex items-center gap-2 flex-wrap">
            <IconField>
              <InputIcon class="pi pi-search" />
              <InputText
                v-model="searchQuery"
                placeholder="Cari judul permohonan..."
                class="w-56 text-xs"
              />
            </IconField>

            <Select
              v-model="selectedType"
              :options="documentTypeOptions"
              optionLabel="label"
              optionValue="value"
              placeholder="Semua Jenis"
              class="w-40 text-xs"
            />
          </div>
        </div>
      </template>

      <template #content>
        <!-- Status Tabs / Filter Chips -->
        <div class="flex gap-2 overflow-x-auto pb-3 mb-4 border-b border-surface-200 dark:border-surface-700 text-xs">
          <Button
            v-for="tab in filterTabs"
            :key="tab.value"
            :label="tab.label"
            :badge="String(tab.count)"
            :badgeSeverity="activeStatusFilter === tab.value ? 'primary' : 'secondary'"
            :severity="activeStatusFilter === tab.value ? 'primary' : 'secondary'"
            :outlined="activeStatusFilter !== tab.value"
            size="small"
            class="text-xs whitespace-nowrap"
            @click="activeStatusFilter = tab.value"
          />
        </div>

        <!-- Grid Cards -->
        <div v-if="filteredApplications.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-1">
          <DocumentCard
            v-for="app in filteredApplications"
            :key="app.id"
            :application="app"
            @click="openDetail(app)"
            @action="handleCardAction(app)"
          />
        </div>

        <!-- Empty Filter State -->
        <div v-else class="flex flex-col items-center justify-center py-16 px-4 bg-surface-50 dark:bg-surface-800/40 border border-dashed border-surface-300 dark:border-surface-700 rounded-lg text-center">
          <i class="pi pi-folder-open text-4xl text-surface-400 mb-2"></i>
          <h3 class="text-sm font-bold text-surface-900 dark:text-surface-0">Tidak ada permohonan yang sesuai filter</h3>
          <p class="text-xs text-surface-500 max-w-[340px] mt-1 mb-4">Coba sesuaikan kata kunci pencarian atau pilih tab status yang lain.</p>
          <Button
            as="router-link"
            to="/pemohon/submit"
            label="Buat Permohonan Baru"
            icon="pi pi-plus"
            size="small"
          />
        </div>
      </template>
    </Card>

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
import Card from 'primevue/card'
import Button from 'primevue/button'
import Message from 'primevue/message'
import Tag from 'primevue/tag'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import { useAuthStore } from '@/stores/auth'
import { useDocumentStore } from '@/stores/document'
import DocumentCard from '@/components/DocumentCard.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import StatCard from '@/components/StatCard.vue'
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

const documentTypeOptions = [
  { label: 'Semua Jenis', value: '' },
  { label: 'SLF', value: 'SLF' },
  { label: 'AMDAL', value: 'AMDAL' },
  { label: 'IMB', value: 'IMB' },
  { label: 'UKL-UPL', value: 'UKL-UPL' },
  { label: 'SIUP', value: 'SIUP' },
]

const statCards = computed(() => [
  { label: 'Total', value: docStore.stats.total, icon: 'mdi:folder-outline', variant: 'default' as const },
  { label: 'Draft', value: docStore.stats.draft, icon: 'mdi:file-edit-outline', variant: 'default' as const },
  { label: 'Menunggu', value: docStore.stats.submitted, icon: 'mdi:clock-outline', variant: 'warning' as const },
  { label: 'Ditinjau', value: docStore.stats.under_review, icon: 'mdi:file-search-outline', variant: 'primary' as const },
  { label: 'Revisi', value: docStore.stats.revision_required, icon: 'mdi:pencil-ruler', variant: 'warning' as const },
  { label: 'Disetujui', value: docStore.stats.approved, icon: 'mdi:check-circle-outline', variant: 'success' as const },
])

const filterTabs = computed(() => [
  { label: 'Semua', value: 'all', count: docStore.stats.total },
  { label: 'Draft', value: 'draft', count: docStore.stats.draft },
  { label: 'Menunggu', value: 'submitted', count: docStore.stats.submitted },
  { label: 'Ditinjau', value: 'under_review', count: docStore.stats.under_review },
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
    // Handled in store
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
