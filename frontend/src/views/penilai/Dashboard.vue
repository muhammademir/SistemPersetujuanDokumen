<template>
  <div class="max-w-[1240px] mx-auto flex flex-col gap-6">
    <!-- Header Hero Banner Card -->
    <Card class="bg-neutral-950 text-white border-neutral-800 shadow-md">
      <CardContent class="p-6">
        <div class="flex items-start justify-between gap-6 flex-wrap">
          <div>
            <div class="w-8 h-8 rounded-lg bg-primary text-primary-foreground flex items-center justify-center font-bold mb-3">
              <Shield class="w-4 h-4" />
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold leading-tight mb-2">
              Panel Penilai Persetujuan Dokumen
            </h1>
            <p class="text-xs sm:text-sm text-neutral-300 max-w-[540px] leading-relaxed">
              Verifikasi kelayakan administratif dan teknis permohonan dokumen. Berikan keputusan Disetujui, Permintaan Revisi, atau Penolakan secara akurat.
            </p>
          </div>

          <div class="flex items-center gap-3">
            <div class="text-center px-4 py-3 border border-neutral-800 rounded-lg bg-neutral-900/80">
              <span class="block text-2xl font-bold text-amber-400 leading-tight">
                {{ docStore.stats.submitted + docStore.stats.under_review }}
              </span>
              <span class="block text-[10px] font-bold uppercase text-neutral-400 tracking-wider mt-0.5">Antrean Perlu Review</span>
            </div>
            <div class="text-center px-4 py-3 border border-neutral-800 rounded-lg bg-neutral-900/80">
              <span class="block text-2xl font-bold text-emerald-400 leading-tight">
                {{ docStore.stats.approved }}
              </span>
              <span class="block text-[10px] font-bold uppercase text-neutral-400 tracking-wider mt-0.5">Total Disetujui</span>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Feedback Notification -->
    <Alert v-if="alertMessage" class="bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/30 flex items-center justify-between py-2.5">
      <AlertDescription class="text-xs font-medium">
        {{ alertMessage }}
      </AlertDescription>
      <Button variant="ghost" size="icon" class="h-6 w-6 text-emerald-600 hover:text-emerald-800 p-0" @click="alertMessage = ''">
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

    <!-- Workload Distribution & Priority Queue -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Status Distribution Chart Card -->
      <Card class="flex flex-col shadow-sm border">
        <CardHeader class="pb-2">
          <div class="flex items-center justify-between">
            <CardTitle class="text-base font-bold">
              Status Beban Permohonan
            </CardTitle>
            <Badge variant="secondary" class="text-xs font-semibold">
              {{ chartDataTotal }} Berkas
            </Badge>
          </div>
          <CardDescription class="text-xs">Statistik permohonan yang masuk ke sistem</CardDescription>
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
          <div v-else class="flex flex-col items-center justify-center text-muted-foreground text-xs gap-2 py-8">
            <BarChart3 class="w-8 h-8" />
            <span>Memuat grafik statistik...</span>
          </div>
        </CardContent>
      </Card>

      <!-- Quick Pending Review List Card -->
      <Card class="lg:col-span-2 flex flex-col shadow-sm border">
        <CardHeader class="pb-2">
          <div class="flex items-center justify-between">
            <CardTitle class="text-base font-bold">
              Antrean Menunggu Verifikasi
            </CardTitle>
            <Badge variant="secondary" class="bg-amber-500/10 text-amber-600 border-amber-500/30 text-xs font-bold">
              {{ docStore.pendingApplications.length }} Menunggu
            </Badge>
          </div>
          <CardDescription class="text-xs">Daftar permohonan prioritas yang membutuhkan tindakan penilaian</CardDescription>
        </CardHeader>
        <CardContent class="flex-1 p-4 pt-0">
          <div class="flex-1 flex flex-col divide-y">
            <div
              v-for="app in docStore.pendingApplications.slice(0, 6)"
              :key="app.id"
              class="py-3 flex items-center justify-between gap-3 hover:bg-muted/50 px-2 -mx-2 rounded-lg transition-colors"
            >
              <div class="flex items-center gap-3 min-w-0">
                <Badge variant="secondary" class="text-[10px] font-bold uppercase shrink-0">
                  {{ app.document_type }}
                </Badge>
                <div class="flex flex-col min-w-0">
                  <span
                    class="text-xs font-bold text-foreground truncate cursor-pointer hover:text-primary transition-colors"
                    @click="openDetailModal(app)"
                  >
                    {{ app.title }}
                  </span>
                  <span class="text-[11px] text-muted-foreground flex items-center gap-1.5 mt-0.5">
                    <span class="font-mono">{{ app.code }}</span>
                    · Pemohon: {{ app.applicant?.name || '-' }}
                    · {{ formatDate(app.submitted_at || app.created_at) }}
                  </span>
                </div>
              </div>

              <div class="flex items-center gap-2 shrink-0">
                <Button
                  size="sm"
                  class="text-xs font-bold gap-1.5 h-8"
                  @click="openReviewModal(app)"
                >
                  <CheckSquare class="w-3.5 h-3.5" />
                  <span>Tinjau</span>
                </Button>
              </div>
            </div>

            <div v-if="!docStore.pendingApplications.length" class="flex flex-col items-center justify-center py-12 text-muted-foreground text-xs gap-2">
              <CheckCircle2 class="w-8 h-8 text-emerald-500" />
              <span class="font-semibold text-foreground">Semua permohonan sudah selesai ditinjau</span>
              <span>Tidak ada permohonan yang menunggu verifikasi saat ini.</span>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Comprehensive Applications Table -->
    <Card class="shadow-sm border">
      <CardHeader>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <CardTitle class="text-lg font-bold">Seluruh Data Permohonan</CardTitle>
            <CardDescription class="text-xs">Tinjau, cari, filter, dan telusuri seluruh riwayat pengajuan dokumen</CardDescription>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <div class="relative">
              <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground pointer-events-none" />
              <Input
                v-model="searchQuery"
                placeholder="Cari kode atau judul..."
                class="w-56 h-8 text-xs pl-8"
              />
            </div>

            <Select v-model="selectedType">
              <SelectTrigger class="w-40 h-8 text-xs">
                <SelectValue placeholder="Semua Jenis" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="opt in documentTypeOptions" :key="opt.value" :value="opt.value">
                  {{ opt.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </CardHeader>

      <CardContent class="pt-0">
        <!-- Filter Tabs / Buttons -->
        <div class="flex gap-2 overflow-x-auto pb-3 mb-4 border-b text-xs">
          <Button
            v-for="tab in filterTabs"
            :key="tab.value"
            size="sm"
            :variant="activeStatusFilter === tab.value ? 'default' : 'outline'"
            class="text-xs whitespace-nowrap h-7 px-2.5 gap-1.5"
            @click="activeStatusFilter = tab.value"
          >
            <span>{{ tab.label }}</span>
            <Badge
              :variant="activeStatusFilter === tab.value ? 'secondary' : 'outline'"
              class="text-[10px] h-4 px-1"
            >
              {{ tab.count }}
            </Badge>
          </Button>
        </div>

        <!-- shadcn-vue Table -->
        <div class="rounded-md border">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="text-xs font-bold min-w-[260px]">Kode / Dokumen</TableHead>
                <TableHead class="text-xs font-bold min-w-[180px]">Pemohon</TableHead>
                <TableHead class="text-xs font-bold min-w-[140px]">Tanggal Masuk</TableHead>
                <TableHead class="text-xs font-bold min-w-[150px]">Status</TableHead>
                <TableHead class="text-xs font-bold text-right min-w-[160px]">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <template v-if="paginatedApplications.length">
                <TableRow
                  v-for="data in paginatedApplications"
                  :key="data.id"
                  class="hover:bg-muted/50"
                >
                  <TableCell>
                    <div class="flex flex-col">
                      <div class="flex items-center gap-1.5 mb-1">
                        <span class="font-mono text-muted-foreground text-[11px]">{{ data.code }}</span>
                        <Badge variant="outline" class="text-[9px] font-bold uppercase">
                          {{ data.document_type }}
                        </Badge>
                        <Badge v-if="data.revision_count > 0" variant="secondary" class="text-[9px] font-bold text-amber-600 bg-amber-500/10">
                          Rev #{{ data.revision_count }}
                        </Badge>
                      </div>
                      <span
                        class="font-bold text-foreground text-xs hover:text-primary cursor-pointer transition-colors"
                        @click="openDetailModal(data)"
                      >
                        {{ data.title }}
                      </span>
                    </div>
                  </TableCell>

                  <TableCell>
                    <div class="flex flex-col">
                      <span class="font-semibold text-foreground text-xs">{{ data.applicant?.name || '-' }}</span>
                      <span class="text-muted-foreground text-[11px]">{{ data.applicant?.email || '' }}</span>
                    </div>
                  </TableCell>

                  <TableCell class="text-muted-foreground text-xs whitespace-nowrap">
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
                        class="h-7 px-2 text-xs font-medium gap-1 text-muted-foreground hover:text-foreground"
                        @click="openDetailModal(data)"
                      >
                        <Eye class="w-3.5 h-3.5" />
                        <span>Detail</span>
                      </Button>
                      <Button
                        v-if="['submitted', 'under_review'].includes(getStatusValue(data.status))"
                        size="sm"
                        class="h-7 px-2.5 text-xs font-semibold gap-1"
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
                  <div class="flex flex-col items-center justify-center text-muted-foreground text-xs gap-2 py-4">
                    <FolderOpen class="w-8 h-8" />
                    <span>Tidak ada permohonan yang sesuai filter atau kata kunci.</span>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Pagination Controls -->
        <div v-if="filteredApplications.length > itemsPerPage" class="flex items-center justify-between pt-4 text-xs text-muted-foreground">
          <span>Menampilkan {{ (currentPage - 1) * itemsPerPage + 1 }} - {{ Math.min(currentPage * itemsPerPage, filteredApplications.length) }} dari {{ filteredApplications.length }} permohonan</span>
          <div class="flex items-center gap-1.5">
            <Button
              variant="outline"
              size="icon"
              class="h-7 w-7"
              :disabled="currentPage <= 1"
              @click="currentPage--"
            >
              <ChevronLeft class="w-3.5 h-3.5" />
            </Button>
            <span class="px-2 font-medium">{{ currentPage }} / {{ totalPages }}</span>
            <Button
              variant="outline"
              size="icon"
              class="h-7 w-7"
              :disabled="currentPage >= totalPages"
              @click="currentPage++"
            >
              <ChevronRight class="w-3.5 h-3.5" />
            </Button>
          </div>
        </div>
      </CardContent>
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
  Shield,
  X,
  BarChart3,
  CheckSquare,
  CheckCircle2,
  Search,
  Eye,
  FolderOpen,
  ChevronLeft,
  ChevronRight,
} from 'lucide-vue-next'
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

const filteredApplications = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  return docStore.applications.filter((app) => {
    const statusVal = getStatusValue(app.status)
    const matchesStatus = activeStatusFilter.value === 'all' || statusVal === activeStatusFilter.value
    const matchesType = selectedType.value === 'all' || !selectedType.value || app.document_type === selectedType.value
    const matchesSearch =
      !query ||
      app.title.toLowerCase().includes(query) ||
      app.code.toLowerCase().includes(query) ||
      (app.applicant?.name && app.applicant.name.toLowerCase().includes(query))
    return matchesStatus && matchesSearch && matchesType
  })
})

const totalPages = computed(() => Math.ceil(filteredApplications.value.length / itemsPerPage.value) || 1)

const paginatedApplications = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredApplications.value.slice(start, start + itemsPerPage.value)
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
