<template>
  <div class="max-w-[1200px] mx-auto flex flex-col gap-5">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h1 class="text-xl font-bold text-gray-900 leading-tight">Persetujuan Dokumen</h1>
        <p class="text-[13px] text-gray-500 mt-0.5">
          Tinjau dan verifikasi permohonan dari semua pemohon
        </p>
      </div>
      <div class="flex items-center gap-2">
        <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-50 border border-amber-200">
          <Clock class="w-4 h-4 text-amber-500" />
          <span class="text-[13px] font-semibold text-amber-700">
            {{ docStore.stats.submitted + docStore.stats.under_review }}
          </span>
          <span class="text-[12px] text-amber-600">Menunggu Review</span>
        </div>
      </div>
    </div>

    <!-- Feedback Notification -->
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

    <!-- Chart + Queue Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Status Distribution Chart Card -->
      <Card class="flex flex-col border-gray-200">
        <CardHeader class="pb-2 px-5 pt-5">
          <div class="flex items-center justify-between">
            <CardTitle class="text-[14px] font-bold text-gray-900">
              Statistik Permohonan
            </CardTitle>
            <Badge variant="secondary" class="text-[11px] font-medium bg-gray-100 text-gray-600">
              {{ chartDataTotal }} Berkas
            </Badge>
          </div>
          <CardDescription class="text-[12px] text-gray-400 mt-0.5">
            Distribusi status permohonan yang masuk
          </CardDescription>
        </CardHeader>
        <CardContent class="flex-1 flex items-center justify-center min-h-[220px] p-4">
          <apexchart
            v-if="chartReady && chartDataTotal > 0"
            type="bar"
            width="100%"
            height="240"
            :options="barChartOptions"
            :series="barChartSeries"
          />
          <div
            v-else
            class="flex flex-col items-center justify-center text-gray-400 text-xs gap-2 py-8"
          >
            <BarChart3 class="w-8 h-8" />
            <span>Memuat grafik statistik...</span>
          </div>
        </CardContent>
      </Card>

      <!-- Quick Pending Review List Card -->
      <Card class="lg:col-span-2 flex flex-col border-gray-200">
        <CardHeader class="pb-2 px-5 pt-5">
          <div class="flex items-center justify-between">
            <CardTitle class="text-[14px] font-bold text-gray-900">
              Antrean Menunggu Verifikasi
            </CardTitle>
            <Badge
              variant="secondary"
              class="text-[11px] font-semibold bg-amber-50 text-amber-600 border border-amber-200"
            >
              {{ docStore.pendingApplications.length }} Menunggu
            </Badge>
          </div>
          <CardDescription class="text-[12px] text-gray-400 mt-0.5">
            Daftar permohonan prioritas yang membutuhkan penilaian
          </CardDescription>
        </CardHeader>
        <CardContent class="flex-1 p-4 pt-0">
          <div class="flex-1 flex flex-col divide-y divide-gray-100">
            <div
              v-for="app in docStore.pendingApplications.slice(0, 6)"
              :key="app.id"
              class="py-3 flex items-center justify-between gap-3 hover:bg-gray-50 px-2 -mx-2 rounded-lg transition-colors"
            >
              <div class="flex items-center gap-3 min-w-0">
                <Badge
                  variant="outline"
                  class="text-[10px] font-semibold uppercase shrink-0 text-gray-600 border-gray-300"
                >
                  {{ app.document_type }}
                </Badge>
                <div class="flex flex-col min-w-0">
                  <span
                    class="text-[13px] font-semibold text-gray-900 truncate cursor-pointer hover:text-[#3b49f5] transition-colors"
                    @click="openDetailModal(app)"
                  >
                    {{ app.title }}
                  </span>
                  <span class="text-[11px] text-gray-400 flex items-center gap-1.5 mt-0.5">
                    <span class="font-mono">{{ app.code }}</span>
                    · Pemohon: {{ app.applicant?.name || '-' }}
                    · {{ formatDate(app.submitted_at || app.created_at) }}
                  </span>
                </div>
              </div>

              <div class="flex items-center gap-2 shrink-0">
                <Button
                  size="sm"
                  class="text-[12px] font-semibold gap-1.5 h-8 bg-[#3b49f5] hover:bg-[#2f3ce0] text-white"
                  @click="openReviewModal(app)"
                >
                  <CheckSquare class="w-3.5 h-3.5" />
                  <span>Tinjau</span>
                </Button>
              </div>
            </div>

            <div
              v-if="!docStore.pendingApplications.length"
              class="flex flex-col items-center justify-center py-12 text-gray-400 text-xs gap-2"
            >
              <CheckCircle2 class="w-8 h-8 text-emerald-400" />
              <span class="font-semibold text-gray-700">
                Semua permohonan sudah selesai ditinjau
              </span>
              <span>Tidak ada permohonan yang menunggu verifikasi saat ini.</span>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Comprehensive Applications Table -->
    <Card class="border-gray-200">
      <CardHeader class="px-5 pt-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <CardTitle class="text-[15px] font-bold text-gray-900">
              Seluruh Data Permohonan
            </CardTitle>
            <CardDescription class="text-[12px] text-gray-400 mt-0.5">
              Tinjau, cari, dan telusuri seluruh riwayat pengajuan dokumen
            </CardDescription>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <div class="relative">
              <Search
                class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
              />
              <Input
                v-model="searchQuery"
                placeholder="Cari kode atau judul..."
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
        <!-- Filter Tabs -->
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

        <!-- Table -->
        <div class="rounded-lg border border-gray-200 overflow-hidden">
          <Table>
            <TableHeader>
              <TableRow class="bg-gray-50 hover:bg-gray-50">
                <TableHead class="text-[12px] font-semibold text-gray-500 min-w-[260px]">
                  Kode / Dokumen
                </TableHead>
                <TableHead class="text-[12px] font-semibold text-gray-500 min-w-[180px]">
                  Pemohon
                </TableHead>
                <TableHead class="text-[12px] font-semibold text-gray-500 min-w-[140px]">
                  Tanggal Masuk
                </TableHead>
                <TableHead class="text-[12px] font-semibold text-gray-500 min-w-[150px]">
                  Status
                </TableHead>
                <TableHead class="text-[12px] font-semibold text-gray-500 text-right min-w-[160px]">
                  Aksi
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-if="tableLoading">
                <TableCell colspan="5" class="h-32 text-center">
                  <div class="flex flex-col items-center justify-center text-gray-400 text-xs gap-2 py-4">
                    <Loader2 class="w-6 h-6 animate-spin text-[#3b49f5]" />
                    <span>Memuat data permohonan...</span>
                  </div>
                </TableCell>
              </TableRow>
              <template v-else-if="tableApplications.length">
                <TableRow
                  v-for="data in tableApplications"
                  :key="data.id"
                  class="hover:bg-gray-50/50"
                >
                  <TableCell>
                    <div class="flex flex-col">
                      <div class="flex items-center gap-1.5 mb-1">
                        <span class="font-mono text-gray-400 text-[11px]">{{ data.code }}</span>
                        <Badge
                          variant="outline"
                          class="text-[9px] font-semibold uppercase text-gray-500 border-gray-300"
                        >
                          {{ data.document_type }}
                        </Badge>
                        <Badge
                          v-if="data.revision_count > 0"
                          variant="secondary"
                          class="text-[9px] font-semibold text-amber-600 bg-amber-50 border border-amber-200"
                        >
                          Rev #{{ data.revision_count }}
                        </Badge>
                      </div>
                      <span
                        class="font-semibold text-gray-900 text-[13px] hover:text-[#3b49f5] cursor-pointer transition-colors"
                        @click="openDetailModal(data)"
                      >
                        {{ data.title }}
                      </span>
                    </div>
                  </TableCell>

                  <TableCell>
                    <div class="flex flex-col">
                      <span class="font-medium text-gray-900 text-[13px]">
                        {{ data.applicant?.name || '-' }}
                      </span>
                      <span class="text-gray-400 text-[11px]">
                        {{ data.applicant?.email || '' }}
                      </span>
                    </div>
                  </TableCell>

                  <TableCell class="text-gray-500 text-[12px] whitespace-nowrap">
                    {{ formatDate(data.submitted_at || data.created_at) }}
                  </TableCell>

                  <TableCell>
                    <StatusBadge :status="data.status" />
                  </TableCell>

                  <TableCell class="text-right">
                    <div class="flex items-center justify-end gap-1.5">
                      <Button
                        variant="ghost"
                        size="sm"
                        class="h-7 px-2 text-[12px] font-medium gap-1 text-gray-500 hover:text-gray-900 hover:bg-gray-100"
                        @click="openDetailModal(data)"
                      >
                        <Eye class="w-3.5 h-3.5" />
                        <span>Detail</span>
                      </Button>
                      <Button
                        v-if="
                          ['submitted', 'under_review'].includes(getStatusValue(data.status))
                        "
                        size="sm"
                        class="h-7 px-2.5 text-[12px] font-semibold gap-1 bg-[#3b49f5] hover:bg-[#2f3ce0] text-white"
                        @click="openReviewModal(data)"
                      >
                        <CheckSquare class="w-3.5 h-3.5" />
                        <span>Tinjau</span>
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </template>
              <TableRow v-else>
                <TableCell colspan="5" class="h-32 text-center">
                  <div
                    class="flex flex-col items-center justify-center text-gray-400 text-xs gap-2 py-4"
                  >
                    <FolderOpen class="w-8 h-8" />
                    <span>Tidak ada permohonan yang sesuai filter atau kata kunci.</span>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Pagination Controls -->
        <div
          v-if="tableTotal > 0"
          class="flex items-center justify-between pt-4 text-[12px] text-gray-500"
        >
          <span>
            Menampilkan
            {{ (tableCurrentPage - 1) * itemsPerPage + 1 }} -
            {{ Math.min(tableCurrentPage * itemsPerPage, tableTotal) }} dari
            {{ tableTotal }} permohonan
          </span>
          <div class="flex items-center gap-1.5">
            <Button
              variant="outline"
              size="icon"
              class="h-7 w-7 border-gray-200"
              :disabled="tableCurrentPage <= 1 || tableLoading"
              @click="goToPage(tableCurrentPage - 1)"
            >
              <ChevronLeft class="w-3.5 h-3.5" />
            </Button>
            <span class="px-2 font-medium">{{ tableCurrentPage }} / {{ tableLastPage }}</span>
            <Button
              variant="outline"
              size="icon"
              class="h-7 w-7 border-gray-200"
              :disabled="tableCurrentPage >= tableLastPage || tableLoading"
              @click="goToPage(tableCurrentPage + 1)"
            >
              <ChevronRight class="w-3.5 h-3.5" />
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Review Decision Modal -->
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
import { ref, computed, onMounted, watch } from 'vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Alert, AlertDescription } from '@/components/ui/alert'
import {
  Table,
  TableHeader,
  TableBody,
  TableHead,
  TableRow,
  TableCell,
} from '@/components/ui/table'
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
} from '@/components/ui/select'
import {
  X,
  BarChart3,
  CheckSquare,
  CheckCircle2,
  Search,
  Eye,
  FolderOpen,
  ChevronLeft,
  ChevronRight,
  Clock,
  Loader2,
} from 'lucide-vue-next'
import api from '@/plugins/axios'
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
const selectedType = ref('all')
const chartReady = ref(false)
const alertMessage = ref('')

const currentPage = ref(1)
const itemsPerPage = ref(10)

const tableApplications = ref<Application[]>([])
const tableTotal = ref(0)
const tableCurrentPage = ref(1)
const tableLastPage = ref(1)
const tableLoading = ref(false)

const showDetailModal = ref(false)
const selectedApplication = ref<Application | null>(null)

const showReviewModal = ref(false)
const reviewTargetApp = ref<Application | null>(null)

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

async function loadTableApplications() {
  tableLoading.value = true
  try {
    const params: Record<string, any> = {
      page: currentPage.value,
      per_page: itemsPerPage.value,
    }
    if (activeStatusFilter.value && activeStatusFilter.value !== 'all') {
      params.status = activeStatusFilter.value
    }
    if (selectedType.value && selectedType.value !== 'all') {
      params.document_type = selectedType.value
    }
    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim()
    }

    const response = await api.get('/applications', { params })
    const data = response.data
    tableApplications.value = data.data ?? data ?? []
    if (data.meta) {
      tableTotal.value = data.meta.total
      tableCurrentPage.value = data.meta.current_page
      tableLastPage.value = data.meta.last_page
    } else {
      tableTotal.value = tableApplications.value.length
      tableLastPage.value = 1
    }
  } catch (err) {
    console.error('Failed to load table applications', err)
  } finally {
    tableLoading.value = false
  }
}

function goToPage(page: number) {
  if (page < 1 || page > tableLastPage.value) return
  currentPage.value = page
  loadTableApplications()
}

let searchTimeout: any = null
watch(searchQuery, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    currentPage.value = 1
    loadTableApplications()
  }, 300)
})

watch([activeStatusFilter, selectedType], () => {
  currentPage.value = 1
  loadTableApplications()
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
      borderRadius: 6,
      columnWidth: '45%',
      distributed: true,
    },
  },
  dataLabels: { enabled: false },
  colors: ['#f59e0b', '#3b82f6', '#f97316', '#10b981', '#ef4444'],
  xaxis: {
    categories: ['Menunggu', 'Ditinjau', 'Revisi', 'Disetujui', 'Ditolak'],
    labels: { style: { fontSize: '11px', fontWeight: 500, colors: '#9ca3af' } },
  },
  yaxis: { labels: { style: { fontSize: '11px', colors: '#9ca3af' } } },
  legend: { show: false },
  grid: { borderColor: '#f3f4f6', strokeDashArray: 3 },
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

    await Promise.all([
      docStore.fetchDashboard(),
      docStore.fetchApplications(),
      loadTableApplications(),
    ])
  } catch {
    // Handled in store
  }
}

onMounted(async () => {
  await Promise.all([
    docStore.fetchDashboard(),
    docStore.fetchApplications(),
    loadTableApplications(),
  ])
  setTimeout(() => {
    chartReady.value = true
  }, 200)
})
</script>
