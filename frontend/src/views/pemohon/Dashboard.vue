<template>
  <div class="max-w-[1240px] mx-auto flex flex-col gap-6">
    <!-- Hero Banner Card -->
    <Card class="bg-neutral-950 text-white border-neutral-800 shadow-md">
      <CardContent class="p-6">
        <div class="flex items-start justify-between gap-6 flex-wrap">
          <div>
            <div class="w-8 h-8 rounded-lg bg-primary text-primary-foreground flex items-center justify-center font-bold mb-3">
              <FileEdit class="w-4 h-4" />
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold leading-tight mb-2">
              Selamat Datang, <span class="text-primary">{{ authStore.userName }}</span>
            </h1>
            <p class="text-xs sm:text-sm text-neutral-300 max-w-[540px] leading-relaxed">
              Sistem Terpadu Permohonan Dokumen Kelayakan. Pantau status pengajuan Anda dari proses verifikasi administrasi hingga persetujuan akhir.
            </p>
          </div>

          <Button
            as-child
            class="font-bold shadow-sm gap-1.5"
          >
            <router-link to="/pemohon/submit">
              <Plus class="w-4 h-4" />
              <span>Buat Permohonan Baru</span>
            </router-link>
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Feedback Alerts -->
    <Alert v-if="alertMessage" class="bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/30 flex items-center justify-between py-2.5">
      <AlertDescription class="text-xs font-medium">
        {{ alertMessage }}
      </AlertDescription>
      <Button variant="ghost" size="icon" class="h-6 w-6 text-emerald-600 hover:text-emerald-800 p-0" @click="alertMessage = ''">
        <X class="w-3.5 h-3.5" />
      </Button>
    </Alert>

    <!-- Quick Stats Cards (6 KPI Cards) -->
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
      <Card class="flex flex-col shadow-sm border">
        <CardHeader class="pb-2">
          <div class="flex items-center justify-between">
            <CardTitle class="text-base font-bold">
              Distribusi Status
            </CardTitle>
            <Badge variant="secondary" class="text-xs font-semibold">
              {{ docStore.stats.total }} Total
            </Badge>
          </div>
          <CardDescription class="text-xs">Proporsi status pengajuan aktif Anda</CardDescription>
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
          <div v-else class="flex flex-col items-center justify-center text-muted-foreground text-xs gap-2 py-8">
            <PieChart class="w-8 h-8" />
            <span>Belum ada data untuk ditampilkan</span>
          </div>
        </CardContent>
      </Card>

      <!-- Recent Status Activity Card -->
      <Card class="lg:col-span-2 flex flex-col shadow-sm border">
        <CardHeader class="pb-2">
          <div class="flex items-center justify-between">
            <CardTitle class="text-base font-bold">
              Permohonan Terbaru & Riwayat
            </CardTitle>
            <span class="text-xs text-muted-foreground font-medium">{{ docStore.applications.length }} Total</span>
          </div>
          <CardDescription class="text-xs">Aktivitas pembaruan dokumen permohonan Anda</CardDescription>
        </CardHeader>
        <CardContent class="flex-1 p-4 pt-0">
          <div class="flex-1 flex flex-col divide-y">
            <div
              v-for="app in docStore.recentApplications.slice(0, 5)"
              :key="app.id"
              class="py-3 flex items-center justify-between gap-3 hover:bg-muted/50 px-2 -mx-2 rounded-lg transition-colors cursor-pointer"
              @click="openDetail(app)"
            >
              <div class="flex items-center gap-3 min-w-0">
                <Badge variant="secondary" class="text-[10px] font-bold uppercase shrink-0">
                  {{ app.document_type }}
                </Badge>
                <div class="flex flex-col min-w-0">
                  <span class="text-xs font-bold text-foreground truncate">{{ app.title }}</span>
                  <span class="text-[11px] text-muted-foreground flex items-center gap-1">
                    <span class="font-mono">{{ app.code }}</span> · {{ formatRelativeTime(app.updated_at || app.created_at) }}
                  </span>
                </div>
              </div>
              <div class="flex items-center gap-3 shrink-0">
                <StatusBadge :status="app.status" />
                <Button variant="ghost" size="sm" class="text-xs p-0 font-bold gap-1 text-primary hover:text-primary h-auto">
                  <span>Detail</span>
                  <ArrowRight class="w-3.5 h-3.5" />
                </Button>
              </div>
            </div>

            <div v-if="!docStore.applications.length" class="flex flex-col items-center justify-center py-12 text-muted-foreground text-xs gap-2">
              <Inbox class="w-8 h-8" />
              <span>Belum ada permohonan yang diajukan</span>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Application List with Filter and Search -->
    <Card class="shadow-sm border">
      <CardHeader>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <CardTitle class="text-lg font-bold">Daftar Semua Permohonan</CardTitle>
            <CardDescription class="text-xs">Kelola dan pantau seluruh permohonan dokumen yang Anda daftarkan</CardDescription>
          </div>

          <!-- Search & Filter Controls -->
          <div class="flex items-center gap-2 flex-wrap">
            <div class="relative">
              <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground pointer-events-none" />
              <Input
                v-model="searchQuery"
                placeholder="Cari judul permohonan..."
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
        <!-- Status Tabs / Filter Chips -->
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
        <div v-else class="flex flex-col items-center justify-center py-16 px-4 bg-muted/20 border border-dashed rounded-lg text-center">
          <FolderOpen class="w-10 h-10 text-muted-foreground mb-2" />
          <h3 class="text-sm font-bold text-foreground">Tidak ada permohonan yang sesuai filter</h3>
          <p class="text-xs text-muted-foreground max-w-[340px] mt-1 mb-4">Coba sesuaikan kata kunci pencarian atau pilih tab status yang lain.</p>
          <Button
            as-child
            size="sm"
            class="gap-1.5"
          >
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
import { ref, computed, onMounted } from 'vue'
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
  FileEdit,
  Plus,
  X,
  PieChart,
  ArrowRight,
  Inbox,
  Search,
  FolderOpen,
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
  return docStore.applications.filter(app => {
    const statusVal = getStatusValue(app.status)
    const matchesStatus = activeStatusFilter.value === 'all' || statusVal === activeStatusFilter.value
    const matchesSearch = !searchQuery.value.trim() ||
      app.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      app.code.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesType = selectedType.value === 'all' || !selectedType.value || app.document_type === selectedType.value
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
