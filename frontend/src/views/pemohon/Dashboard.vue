<template>
  <div class="max-w-[1200px] mx-auto flex flex-col gap-5">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h1 class="text-xl font-bold text-gray-900 leading-tight">Permohonan Dokumen</h1>
        <p class="text-[13px] text-gray-500 mt-0.5">
          Kelola dan pantau seluruh pengajuan dokumen kelayakan Anda
        </p>
      </div>
      <Button as-child class="font-semibold gap-1.5 bg-[#3b49f5] hover:bg-[#2f3ce0] text-white shadow-sm h-9 px-4 text-[13px]">
        <router-link to="/pemohon/submit">
          <Plus class="w-4 h-4" />
          <span>Buat Permohonan Baru</span>
        </router-link>
      </Button>
    </div>

    <!-- Feedback Alerts -->
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

    <!-- Quick Stats Cards -->
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

    <!-- Chart + Recent Activity Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Donut Chart Card -->
      <Card class="flex flex-col border-gray-200">
        <CardHeader class="pb-2 px-5 pt-5">
          <div class="flex items-center justify-between">
            <CardTitle class="text-[14px] font-bold text-gray-900">Distribusi Status</CardTitle>
            <Badge variant="secondary" class="text-[11px] font-medium bg-gray-100 text-gray-600">
              {{ docStore.stats.total }} Total
            </Badge>
          </div>
          <CardDescription class="text-[12px] text-gray-400 mt-0.5">
            Proporsi status pengajuan aktif Anda
          </CardDescription>
        </CardHeader>
        <CardContent class="flex-1 flex items-center justify-center min-h-[220px] p-4">
          <apexchart
            v-if="chartReady && docStore.stats.total > 0"
            type="donut"
            width="100%"
            height="240"
            :options="chartOptions"
            :series="chartSeries"
          />
          <div
            v-else
            class="flex flex-col items-center justify-center text-gray-400 text-xs gap-2 py-8"
          >
            <PieChart class="w-8 h-8" />
            <span>Belum ada data untuk ditampilkan</span>
          </div>
        </CardContent>
      </Card>

      <!-- Recent Activity Card -->
      <Card class="lg:col-span-2 flex flex-col border-gray-200">
        <CardHeader class="pb-2 px-5 pt-5">
          <div class="flex items-center justify-between">
            <CardTitle class="text-[14px] font-bold text-gray-900">
              Permohonan Terbaru
            </CardTitle>
            <span class="text-[12px] text-gray-400 font-medium">
              {{ docStore.applications.length }} Total
            </span>
          </div>
          <CardDescription class="text-[12px] text-gray-400 mt-0.5">
            Aktivitas pembaruan dokumen permohonan Anda
          </CardDescription>
        </CardHeader>
        <CardContent class="flex-1 p-4 pt-0">
          <div class="flex-1 flex flex-col divide-y divide-gray-100">
            <div
              v-for="app in docStore.recentApplications.slice(0, 5)"
              :key="app.id"
              class="py-3 flex items-center justify-between gap-3 hover:bg-gray-50 px-2 -mx-2 rounded-lg transition-colors cursor-pointer"
              @click="openDetail(app)"
            >
              <div class="flex items-center gap-3 min-w-0">
                <Badge variant="outline" class="text-[10px] font-semibold uppercase shrink-0 text-gray-600 border-gray-300">
                  {{ app.document_type }}
                </Badge>
                <div class="flex flex-col min-w-0">
                  <span class="text-[13px] font-semibold text-gray-900 truncate">
                    {{ app.title }}
                  </span>
                  <span class="text-[11px] text-gray-400 flex items-center gap-1">
                    <span class="font-mono">{{ app.code }}</span>
                    · {{ formatRelativeTime(app.updated_at || app.created_at) }}
                  </span>
                </div>
              </div>
              <div class="flex items-center gap-2.5 shrink-0">
                <StatusBadge :status="app.status" />
                <ChevronRight class="w-4 h-4 text-gray-300" />
              </div>
            </div>

            <div
              v-if="!docStore.applications.length"
              class="flex flex-col items-center justify-center py-12 text-gray-400 text-xs gap-2"
            >
              <Inbox class="w-8 h-8" />
              <span>Belum ada permohonan yang diajukan</span>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Application List with Filter and Search -->
    <Card class="border-gray-200">
      <CardHeader class="px-5 pt-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <CardTitle class="text-[15px] font-bold text-gray-900">
              Daftar Semua Permohonan
            </CardTitle>
            <CardDescription class="text-[12px] text-gray-400 mt-0.5">
              Kelola dan pantau seluruh permohonan dokumen yang Anda daftarkan
            </CardDescription>
          </div>

          <!-- Search & Filter Controls -->
          <div class="flex items-center gap-2 flex-wrap">
            <div class="relative">
              <Search
                class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
              />
              <Input
                v-model="searchQuery"
                placeholder="Cari judul permohonan..."
                class="w-56 h-8 text-[12px] pl-8 border-gray-200"
              />
            </div>

            <Select v-model="selectedType">
              <SelectTrigger class="w-36 h-8 text-[12px] border-gray-200">
                <SelectValue placeholder="Semua Jenis" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="opt in documentTypeOptions"
                  :key="opt.value"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </CardHeader>

      <CardContent class="pt-0 px-5 pb-5">
        <!-- Status Filter Tabs -->
        <div class="flex gap-1.5 overflow-x-auto pb-3 mb-4 border-b border-gray-100 text-xs">
          <Button
            v-for="tab in filterTabs"
            :key="tab.value"
            size="sm"
            :variant="activeStatusFilter === tab.value ? 'default' : 'ghost'"
            :class="[
              'text-[12px] whitespace-nowrap h-7 px-2.5 gap-1.5',
              activeStatusFilter === tab.value
                ? 'bg-[#3b49f5] hover:bg-[#2f3ce0] text-white'
                : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100'
            ]"
            @click="activeStatusFilter = tab.value"
          >
            <span>{{ tab.label }}</span>
            <span
              :class="[
                'inline-flex items-center justify-center min-w-[18px] h-4 px-1 rounded text-[10px] font-semibold',
                activeStatusFilter === tab.value
                  ? 'bg-white/20 text-white'
                  : 'bg-gray-100 text-gray-500'
              ]"
            >
              {{ tab.count }}
            </span>
          </Button>
        </div>

        <!-- Grid Cards -->
        <div
          v-if="filteredApplications.length"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pt-1"
        >
          <DocumentCard
            v-for="app in filteredApplications"
            :key="app.id"
            :application="app"
            @click="openDetail(app)"
            @action="handleCardAction(app)"
          />
        </div>

        <!-- Empty Filter State -->
        <div
          v-else
          class="flex flex-col items-center justify-center py-16 px-4 bg-gray-50 border border-dashed border-gray-200 rounded-lg text-center"
        >
          <FolderOpen class="w-10 h-10 text-gray-300 mb-2" />
          <h3 class="text-[13px] font-semibold text-gray-700">
            Tidak ada permohonan yang sesuai filter
          </h3>
          <p class="text-[12px] text-gray-400 max-w-[340px] mt-1 mb-4">
            Coba sesuaikan kata kunci pencarian atau pilih tab status yang lain.
          </p>
          <Button as-child size="sm" class="gap-1.5 bg-[#3b49f5] hover:bg-[#2f3ce0] text-white">
            <router-link to="/pemohon/submit">
              <Plus class="w-3.5 h-3.5" />
              <span>Buat Permohonan Baru</span>
            </router-link>
          </Button>
        </div>
      </CardContent>
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
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Alert, AlertDescription } from '@/components/ui/alert'
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
} from '@/components/ui/select'
import {
  Plus,
  X,
  PieChart,
  Inbox,
  Search,
  FolderOpen,
  ChevronRight,
} from 'lucide-vue-next'
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
const selectedType = ref('all')
const chartReady = ref(false)
const alertMessage = ref('')

const showDetailModal = ref(false)
const selectedApplication = ref<Application | null>(null)

const documentTypeOptions = [
  { label: 'Semua Jenis', value: 'all' },
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
  return docStore.applications.filter((app) => {
    const statusVal = getStatusValue(app.status)
    const matchesStatus =
      activeStatusFilter.value === 'all' || statusVal === activeStatusFilter.value
    const matchesSearch =
      !searchQuery.value.trim() ||
      app.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      app.code.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesType =
      selectedType.value === 'all' ||
      !selectedType.value ||
      app.document_type === selectedType.value
    return matchesStatus && matchesSearch && matchesType
  })
})

async function loadPemohonApplications() {
  const params: Record<string, string> = { per_page: '50' }
  if (activeStatusFilter.value && activeStatusFilter.value !== 'all') {
    params.status = activeStatusFilter.value
  }
  if (selectedType.value && selectedType.value !== 'all') {
    params.document_type = selectedType.value
  }
  if (searchQuery.value.trim()) {
    params.search = searchQuery.value.trim()
  }
  await docStore.fetchApplications(params)
}

let searchTimeout: any = null
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    loadPemohonApplications()
  }, 300)
})

watch([activeStatusFilter, selectedType], () => {
  loadPemohonApplications()
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
  labels: [
    'Draft',
    'Menunggu Verifikasi',
    'Sedang Ditinjau',
    'Perlu Revisi',
    'Disetujui',
    'Ditolak',
  ],
  colors: ['#9ca3af', '#f59e0b', '#3b82f6', '#f97316', '#10b981', '#ef4444'],
  stroke: { width: 2, colors: ['#ffffff'] },
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
            color: '#6b7280',
          },
        },
      },
    },
  },
  dataLabels: { enabled: false },
  legend: {
    position: 'bottom',
    fontSize: '11px',
    fontFamily: 'Inter, Arial, sans-serif',
    itemMargin: { horizontal: 6, vertical: 3 },
  },
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
  await Promise.all([docStore.fetchDashboard(), docStore.fetchApplications()])
  setTimeout(() => {
    chartReady.value = true
  }, 200)
})
</script>
