<template>
  <div class="max-w-[1240px] mx-auto flex flex-col gap-6">
    <!-- Header Hero Banner Card -->
    <Card class="bg-surface-900 text-surface-0 border border-surface-800 shadow-md">
      <template #content>
        <div class="flex items-start justify-between gap-6 flex-wrap">
          <div>
            <div class="w-8 h-8 rounded-lg bg-primary-500 flex items-center justify-center text-surface-900 font-bold mb-3">
              <i class="pi pi-shield text-lg"></i>
            </div>
            <h1 class="font-brand text-2xl sm:text-3xl font-bold leading-tight mb-2">
              Panel Penilai Persetujuan Dokumen
            </h1>
            <p class="text-xs sm:text-sm text-surface-300 max-w-[540px] leading-relaxed">
              Verifikasi kelayakan administratif dan teknis permohonan dokumen. Berikan keputusan Disetujui, Permintaan Revisi, atau Penolakan secara akurat.
            </p>
          </div>

          <div class="flex items-center gap-3">
            <div class="text-center px-4 py-3 border border-surface-700 rounded-lg bg-surface-800/80">
              <span class="block text-2xl font-bold text-amber-400 leading-tight">
                {{ docStore.stats.submitted + docStore.stats.under_review }}
              </span>
              <span class="block text-[10px] font-bold uppercase text-surface-400 tracking-wider mt-0.5">Antrean Perlu Review</span>
            </div>
            <div class="text-center px-4 py-3 border border-surface-700 rounded-lg bg-surface-800/80">
              <span class="block text-2xl font-bold text-emerald-400 leading-tight">
                {{ docStore.stats.approved }}
              </span>
              <span class="block text-[10px] font-bold uppercase text-surface-400 tracking-wider mt-0.5">Total Disetujui</span>
            </div>
          </div>
        </div>
      </template>
    </Card>

    <!-- Feedback Notification -->
    <Message v-if="alertMessage" severity="success" :closable="true" @close="alertMessage = ''" class="text-xs">
      {{ alertMessage }}
    </Message>

    <!-- 6 KPI Stat Summary Cards -->
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

    <!-- Workload Distribution & Priority Queue -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Status Distribution Chart Card -->
      <Card class="border border-surface-200 dark:border-surface-700 shadow-sm flex flex-col">
        <template #title>
          <div class="flex items-center justify-between">
            <h2 class="font-brand text-base font-bold text-surface-900 dark:text-surface-0">
              Status Beban Permohonan
            </h2>
            <Tag :value="`${chartDataTotal} Berkas`" severity="secondary" rounded />
          </div>
        </template>
        <template #subtitle>
          <span class="text-xs text-surface-500">Statistik permohonan yang masuk ke sistem</span>
        </template>
        <template #content>
          <div class="flex-1 flex items-center justify-center min-h-[220px]">
            <apexchart
              v-if="chartReady && chartDataTotal > 0"
              type="bar"
              width="100%"
              height="240"
              :options="barChartOptions"
              :series="barChartSeries"
            />
            <div v-else class="flex flex-col items-center justify-center text-surface-400 text-xs gap-2 py-8">
              <i class="pi pi-chart-bar text-3xl"></i>
              <span>Memuat grafik statistik...</span>
            </div>
          </div>
        </template>
      </Card>

      <!-- Quick Pending Review List Card -->
      <Card class="lg:col-span-2 border border-surface-200 dark:border-surface-700 shadow-sm flex flex-col">
        <template #title>
          <div class="flex items-center justify-between">
            <div>
              <h2 class="font-brand text-base font-bold text-surface-900 dark:text-surface-0">
                Antrean Menunggu Verifikasi
              </h2>
            </div>
            <Tag
              :value="`${docStore.pendingApplications.length} Menunggu`"
              severity="warn"
              class="font-bold text-xs"
            />
          </div>
        </template>
        <template #subtitle>
          <span class="text-xs text-surface-500">Daftar permohonan prioritas yang membutuhkan tindakan penilaian</span>
        </template>
        <template #content>
          <div class="flex-1 flex flex-col divide-y divide-surface-200 dark:divide-surface-700">
            <div
              v-for="app in docStore.pendingApplications.slice(0, 6)"
              :key="app.id"
              class="py-3 flex items-center justify-between gap-3 hover:bg-surface-50 dark:hover:bg-surface-800/50 px-2 -mx-2 rounded-lg transition-colors"
            >
              <div class="flex items-center gap-3 min-w-0">
                <Tag :value="app.document_type" severity="info" class="text-[10px] font-bold uppercase" />
                <div class="flex flex-col min-w-0">
                  <span
                    class="text-xs font-bold text-surface-900 dark:text-surface-0 truncate cursor-pointer hover:text-primary-600 transition-colors"
                    @click="openDetailModal(app)"
                  >
                    {{ app.title }}
                  </span>
                  <span class="text-[11px] text-surface-500 flex items-center gap-1.5 mt-0.5">
                    <span class="font-mono">{{ app.code }}</span>
                    · Pemohon: {{ app.applicant?.name || '-' }}
                    · {{ formatDate(app.submitted_at || app.created_at) }}
                  </span>
                </div>
              </div>

              <div class="flex items-center gap-2 flex-shrink-0">
                <Button
                  label="Tinjau"
                  icon="pi pi-check-square"
                  size="small"
                  severity="primary"
                  class="text-xs font-bold"
                  @click="openReviewModal(app)"
                />
              </div>
            </div>

            <div v-if="!docStore.pendingApplications.length" class="flex flex-col items-center justify-center py-12 text-surface-400 text-xs gap-2">
              <i class="pi pi-check-circle text-3xl text-emerald-500"></i>
              <span class="font-semibold text-surface-800 dark:text-surface-200">Semua permohonan sudah selesai ditinjau</span>
              <span>Tidak ada permohonan yang menunggu verifikasi saat ini.</span>
            </div>
          </div>
        </template>
      </Card>
    </div>

    <!-- Comprehensive Applications Table with PrimeVue DataTable -->
    <Card class="border border-surface-200 dark:border-surface-700 shadow-sm">
      <template #title>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h2 class="font-brand text-lg font-bold text-surface-900 dark:text-surface-0">Seluruh Data Permohonan</h2>
            <p class="text-xs text-surface-500 font-normal">Tinjau, cari, filter, dan telusuri seluruh riwayat pengajuan dokumen</p>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <IconField>
              <InputIcon class="pi pi-search" />
              <InputText
                v-model="searchQuery"
                placeholder="Cari kode atau judul..."
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
        <!-- Filter Tabs / Buttons -->
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

        <!-- PrimeVue DataTable -->
        <DataTable
          :value="filteredApplications"
          paginator
          :rows="10"
          :rowsPerPageOptions="[5, 10, 20, 50]"
          responsiveLayout="scroll"
          class="text-xs"
          stripedRows
        >
          <template #empty>
            <div class="flex flex-col items-center justify-center py-12 text-surface-400 text-xs gap-2">
              <i class="pi pi-folder-open text-4xl"></i>
              <span>Tidak ada permohonan yang sesuai filter atau kata kunci.</span>
            </div>
          </template>

          <Column header="Kode / Dokumen" style="min-width: 260px">
            <template #body="{ data }">
              <div class="flex flex-col">
                <div class="flex items-center gap-2 mb-1">
                  <span class="font-mono text-surface-500 text-[11px]">{{ data.code }}</span>
                  <Tag :value="data.document_type" severity="info" class="text-[9px] font-bold uppercase" />
                  <Tag v-if="data.revision_count > 0" :value="`Rev #${data.revision_count}`" severity="warn" class="text-[9px] font-bold" />
                </div>
                <span class="font-bold text-surface-900 dark:text-surface-0 text-xs hover:text-primary-600 cursor-pointer" @click="openDetailModal(data)">
                  {{ data.title }}
                </span>
              </div>
            </template>
          </Column>

          <Column header="Pemohon" style="min-width: 180px">
            <template #body="{ data }">
              <div class="flex flex-col">
                <span class="font-semibold text-surface-800 dark:text-surface-200">{{ data.applicant?.name || '-' }}</span>
                <span class="text-surface-500 text-[11px]">{{ data.applicant?.email || '' }}</span>
              </div>
            </template>
          </Column>

          <Column header="Tanggal Masuk" style="min-width: 140px">
            <template #body="{ data }">
              <span class="text-surface-500 whitespace-nowrap">
                {{ formatDate(data.submitted_at || data.created_at) }}
              </span>
            </template>
          </Column>

          <Column header="Status" style="min-width: 150px">
            <template #body="{ data }">
              <StatusBadge :status="data.status" />
            </template>
          </Column>

          <Column header="Aksi" style="min-width: 160px; text-align: right">
            <template #body="{ data }">
              <div class="flex items-center justify-end gap-1.5">
                <Button
                  label="Detail"
                  icon="pi pi-eye"
                  severity="secondary"
                  size="small"
                  text
                  @click="openDetailModal(data)"
                />
                <Button
                  v-if="['submitted', 'under_review'].includes(getStatusValue(data.status))"
                  label="Tinjau"
                  icon="pi pi-check"
                  severity="primary"
                  size="small"
                  @click="openReviewModal(data)"
                />
              </div>
            </template>
          </Column>
        </DataTable>
      </template>
    </Card>

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
import Card from 'primevue/card'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Message from 'primevue/message'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import { useDocumentStore } from '@/stores/document'
import StatCard from '@/components/StatCard.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import ApplicationDetailModal from '@/components/ApplicationDetailModal.vue'
import ReviewDecisionModal from '@/components/ReviewDecisionModal.vue'
import { formatDate, getStatusValue, formatDecision } from '@/utils/formatters'
import type { Application } from '@/types'

const docStore = useDocumentStore()

const activeStatusFilter = ref('all')
const searchQuery = ref('')
const selectedType = ref('')
const chartReady = ref(false)
const alertMessage = ref('')

const showDetailModal = ref(false)
const selectedApplication = ref<Application | null>(null)

const showReviewModal = ref(false)
const reviewTargetApp = ref<Application | null>(null)

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
  { label: 'Menunggu', value: docStore.stats.submitted, icon: 'mdi:clock-outline', variant: 'warning' as const },
  { label: 'Ditinjau', value: docStore.stats.under_review, icon: 'mdi:file-search-outline', variant: 'primary' as const },
  { label: 'Revisi', value: docStore.stats.revision_required, icon: 'mdi:pencil-ruler', variant: 'warning' as const },
  { label: 'Disetujui', value: docStore.stats.approved, icon: 'mdi:check-circle-outline', variant: 'success' as const },
  { label: 'Ditolak', value: docStore.stats.rejected, icon: 'mdi:close-circle-outline', variant: 'danger' as const },
])

const filterTabs = computed(() => [
  { label: 'Semua', value: 'all', count: docStore.stats.total },
  { label: 'Menunggu', value: 'submitted', count: docStore.stats.submitted },
  { label: 'Ditinjau', value: 'under_review', count: docStore.stats.under_review },
  { label: 'Perlu Revisi', value: 'revision_required', count: docStore.stats.revision_required },
  { label: 'Disetujui', value: 'approved', count: docStore.stats.approved },
  { label: 'Ditolak', value: 'rejected', count: docStore.stats.rejected },
])

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
      borderRadius: 4,
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
    // Handled in store
  }
}

onMounted(async () => {
  await Promise.all([docStore.fetchDashboard(), docStore.fetchApplications()])
  setTimeout(() => {
    chartReady.value = true
  }, 200)
})
</script>
