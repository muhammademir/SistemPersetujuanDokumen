<template>
  <div class="max-w-[1200px] mx-auto">
    <!-- Hero -->
    <div class="bg-surface-dark text-on-dark p-8 rounded-sm flex items-start justify-between gap-6 flex-wrap mb-6 animate-fade-in-up">
      <div>
        <div class="w-3 h-3 bg-primary mb-4"></div>
        <h1 class="font-brand text-2xl font-bold leading-snug mb-2">
          Selamat Datang, <span class="text-primary">{{ authStore.userName }}</span>
        </h1>
        <p class="text-sm text-on-dark-mute max-w-[500px] leading-relaxed">
          Kelola pengajuan dokumen Anda dari dashboard ini. Pantau status persetujuan secara real-time.
        </p>
      </div>
      <router-link to="/pemohon/submit"
        class="inline-flex items-center gap-2 h-11 px-6 bg-primary text-ink border-none rounded-sm font-brand text-sm font-bold no-underline transition-colors hover:bg-primary-dark whitespace-nowrap flex-shrink-0">
        <Icon icon="mdi:plus" class="text-lg" />
        <span>Ajukan Dokumen Baru</span>
      </router-link>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <StatCard v-for="(s, i) in statCards" :key="s.label" v-bind="s" :class="['animate-fade-in-up', `delay-${(i+1)*100}`]" />
    </div>

    <!-- Chart + Activity -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <!-- Chart -->
      <div class="relative overflow-hidden bg-canvas border border-hairline rounded-sm p-6">
        <div class="absolute top-0 left-0 w-3 h-3 bg-primary"></div>
        <h2 class="font-brand text-lg font-bold text-ink">Statistik Dokumen</h2>
        <div class="mt-4">
          <apexchart v-if="chartReady" type="donut" height="280" :options="chartOptions" :series="chartSeries" />
          <div v-else class="flex flex-col items-center justify-center h-[200px] text-mute text-sm gap-2">
            <Icon icon="mdi:chart-donut" class="text-3xl" />
            <span>Memuat grafik...</span>
          </div>
        </div>
      </div>

      <!-- Activity -->
      <div class="relative overflow-hidden bg-canvas border border-hairline rounded-sm p-6">
        <div class="absolute bottom-0 right-0 w-3 h-3 bg-primary"></div>
        <h2 class="font-brand text-lg font-bold text-ink">Aktivitas Terbaru</h2>
        <div class="mt-4 flex flex-col">
          <div v-for="doc in recentActivity" :key="doc.id" class="flex items-center gap-3 py-3 border-b border-hairline last:border-b-0">
            <span class="w-2 h-2 rounded-full flex-shrink-0" :class="dotColor(doc.status)"></span>
            <div class="flex-1 min-w-0 flex flex-col">
              <span class="text-xs font-semibold text-ink truncate">{{ doc.title }}</span>
              <span class="text-[11px] text-mute mt-0.5">{{ formatRelativeTime(doc.updated_at) }}</span>
            </div>
            <StatusBadge :status="doc.status" />
          </div>
          <div v-if="!recentActivity.length" class="flex flex-col items-center gap-2 py-8 text-mute text-sm">
            <Icon icon="mdi:inbox-outline" class="text-2xl" />
            <span>Belum ada aktivitas</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Document list -->
    <div>
      <div class="flex items-center justify-between flex-wrap gap-4 mb-4">
        <h2 class="font-brand text-lg font-bold text-ink">Dokumen Saya</h2>
        <div class="flex gap-1 overflow-x-auto">
          <button v-for="tab in filterTabs" :key="tab.value" @click="activeFilter = tab.value"
            class="inline-flex items-center gap-1.5 px-4 py-2 border-none rounded-sm font-brand text-xs font-bold cursor-pointer transition-all whitespace-nowrap"
            :class="activeFilter === tab.value ? 'bg-ink text-on-dark' : 'bg-transparent text-ink hover:bg-surface-soft'">
            {{ tab.label }}
            <span v-if="tab.count" class="text-[11px] px-1.5 py-0.5 rounded-full" :class="activeFilter === tab.value ? 'bg-white/20' : 'bg-black/10'">{{ tab.count }}</span>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <DocumentCard v-for="(doc, i) in filteredDocuments" :key="doc.id" :document="doc"
          :action-label="doc.status === 'revision' ? 'Perbaiki' : 'Lihat Detail'"
          :class="['animate-fade-in-up', `delay-${(i%4+1)*100}`]"
          @action="handleDocAction(doc)" />
      </div>

      <!-- Empty state -->
      <div v-if="!filteredDocuments.length" class="flex flex-col items-center gap-3 py-16 px-6 bg-canvas border border-dashed border-hairline rounded-sm text-center">
        <Icon icon="mdi:file-document-outline" class="text-5xl text-mute" />
        <h3 class="text-lg font-bold text-ink">Belum ada dokumen</h3>
        <p class="text-sm text-mute max-w-[360px]">Mulai ajukan dokumen baru untuk memulai proses persetujuan.</p>
        <router-link to="/pemohon/submit"
          class="inline-flex items-center h-11 px-6 mt-2 bg-transparent border-2 border-primary rounded-sm font-brand text-sm font-bold text-ink no-underline transition-all hover:bg-primary">
          + Ajukan Dokumen
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Icon } from '@iconify/vue'
import { useAuthStore } from '@/stores/auth'
import { useDocumentStore } from '@/stores/document'
import StatCard from '@/components/StatCard.vue'
import StatusBadge from '@/components/StatusBadge.vue'
import DocumentCard from '@/components/DocumentCard.vue'
import type { Document } from '@/types'

const authStore = useAuthStore()
const docStore = useDocumentStore()
const router = useRouter()

const activeFilter = ref('all')
const chartReady = ref(false)

const statCards = computed(() => [
  { value: docStore.stats.total, label: 'Total Dokumen', icon: 'mdi:folder-outline', variant: 'primary' as const, subtitle: 'Semua dokumen' },
  { value: docStore.stats.pending, label: 'Menunggu', icon: 'mdi:clock-outline', variant: 'warning' as const, subtitle: 'Perlu ditinjau' },
  { value: docStore.stats.approved, label: 'Disetujui', icon: 'mdi:check-circle-outline', variant: 'success' as const, subtitle: 'Berhasil' },
  { value: docStore.stats.revision + docStore.stats.rejected, label: 'Perlu Tindakan', icon: 'mdi:flash-outline', variant: 'danger' as const, subtitle: 'Revisi & Ditolak' },
])

const filterTabs = computed(() => [
  { label: 'Semua', value: 'all', count: docStore.stats.total },
  { label: 'Menunggu', value: 'pending', count: docStore.stats.pending },
  { label: 'Disetujui', value: 'approved', count: docStore.stats.approved },
  { label: 'Revisi', value: 'revision', count: docStore.stats.revision },
  { label: 'Ditolak', value: 'rejected', count: docStore.stats.rejected },
])

const filteredDocuments = computed(() => {
  if (activeFilter.value === 'all') return docStore.documents
  return docStore.documents.filter(d => d.status === activeFilter.value)
})

const recentActivity = computed(() => docStore.recentDocuments.slice(0, 5))

const chartSeries = computed(() => [docStore.stats.pending, docStore.stats.approved, docStore.stats.revision, docStore.stats.rejected])
const chartOptions = computed(() => ({
  chart: { type: 'donut', fontFamily: 'Inter, Arial, sans-serif', background: 'transparent' },
  labels: ['Menunggu', 'Disetujui', 'Revisi', 'Ditolak'],
  colors: ['#df6500', '#76b900', '#ef9100', '#e52020'],
  stroke: { width: 2, colors: ['#ffffff'] },
  plotOptions: { pie: { donut: { size: '65%', labels: { show: true, name: { fontSize: '14px', fontWeight: 700 }, value: { fontSize: '24px', fontWeight: 700, color: '#1a1a1a' }, total: { show: true, label: 'Total', fontSize: '13px', fontWeight: 700, color: '#757575' } } } } },
  dataLabels: { enabled: false },
  legend: { position: 'bottom', fontSize: '13px', fontWeight: 600, markers: { size: 8, shape: 'square' }, itemMargin: { horizontal: 12, vertical: 4 } },
}))

function dotColor(status: string) {
  return { 'bg-warning': status === 'pending', 'bg-primary': status === 'approved', 'bg-warning-bright': status === 'revision', 'bg-error': status === 'rejected', 'bg-stone': status === 'draft' }
}

function formatRelativeTime(dateStr: string): string {
  const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000)
  if (diff < 60) return 'Baru saja'
  if (diff < 3600) return `${Math.floor(diff / 60)} menit lalu`
  if (diff < 86400) return `${Math.floor(diff / 3600)} jam lalu`
  if (diff < 604800) return `${Math.floor(diff / 86400)} hari lalu`
  return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })
}

function handleDocAction(doc: Document) {
  if (doc.status === 'revision') router.push(`/pemohon/submit?edit=${doc.id}`)
}

onMounted(() => {
  docStore.fetchDocuments()
  setTimeout(() => { chartReady.value = true }, 300)
})
</script>
