<template>
  <div class="max-w-[1240px] mx-auto flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-foreground mb-1">
          Riwayat Hasil Penilaian
        </h1>
        <p class="text-xs text-muted-foreground">
          Daftar seluruh keputusan verifikasi dan review yang telah Anda berikan
        </p>
      </div>
      <Badge
        variant="secondary"
        class="text-xs font-semibold px-3 py-1.5 self-start sm:self-auto"
      >
        Total Riwayat: {{ historyReviews.length }}
      </Badge>
    </div>

    <!-- Table of Reviews using shadcn Table -->
    <Card class="shadow-sm border">
      <CardContent class="p-0">
        <div class="rounded-md">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="text-xs font-bold min-w-[150px]">Waktu Penilaian</TableHead>
                <TableHead class="text-xs font-bold min-w-[260px]">Permohonan Dokumen</TableHead>
                <TableHead class="text-xs font-bold min-w-[140px]">Keputusan</TableHead>
                <TableHead class="text-xs font-bold min-w-[280px]">Catatan Reviewer</TableHead>
                <TableHead class="text-xs font-bold text-right min-w-[100px]">Aksi</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <template v-if="paginatedReviews.length">
                <TableRow
                  v-for="data in paginatedReviews"
                  :key="data.id"
                  class="hover:bg-muted/50"
                >
                  <TableCell class="text-xs text-muted-foreground font-medium whitespace-nowrap">
                    {{ formatDateTime(data.reviewed_at || data.created_at) }}
                  </TableCell>

                  <TableCell>
                    <div class="flex flex-col">
                      <div class="flex items-center gap-1.5 mb-1">
                        <span class="font-mono text-muted-foreground text-[10px]">{{ data.application?.code || '-' }}</span>
                        <Badge
                          v-if="data.application?.document_type"
                          variant="outline"
                          class="text-[9px] font-bold uppercase"
                        >
                          {{ data.application.document_type }}
                        </Badge>
                      </div>
                      <span class="font-bold text-foreground text-xs truncate max-w-[280px]">
                        {{ data.application?.title || 'Permohonan' }}
                      </span>
                    </div>
                  </TableCell>

                  <TableCell>
                    <Badge
                      :class="getDecisionBadgeClass(data.decision)"
                      class="text-[10px] font-bold uppercase"
                    >
                      {{ formatDecision(data.decision) }}
                    </Badge>
                  </TableCell>

                  <TableCell>
                    <p class="text-xs text-foreground line-clamp-2 max-w-[320px]">
                      {{ data.note || '-' }}
                    </p>
                  </TableCell>

                  <TableCell class="text-right">
                    <Button
                      v-if="data.application_id"
                      variant="ghost"
                      size="sm"
                      class="h-7 px-2 text-xs font-medium gap-1 text-muted-foreground hover:text-foreground"
                      @click="viewAppDetail(data.application_id)"
                    >
                      <Eye class="w-3.5 h-3.5" />
                      <span>Detail</span>
                    </Button>
                  </TableCell>
                </TableRow>
              </template>
              <TableRow v-else>
                <TableCell colspan="5" class="h-44 text-center">
                  <div class="flex flex-col items-center justify-center text-muted-foreground text-xs gap-2 py-8">
                    <History class="w-10 h-10 mb-1" />
                    <h3 class="text-sm font-bold text-foreground">Belum Ada Riwayat Penilaian</h3>
                    <p class="text-xs text-muted-foreground max-w-[320px] text-center">
                      Setiap permohonan yang Anda setujui, tolak, atau minta revisi akan tercatat di halaman ini.
                    </p>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Pagination Controls -->
        <div v-if="historyReviews.length > itemsPerPage" class="flex items-center justify-between p-4 border-t text-xs text-muted-foreground">
          <span>Menampilkan {{ (currentPage - 1) * itemsPerPage + 1 }} - {{ Math.min(currentPage * itemsPerPage, historyReviews.length) }} dari {{ historyReviews.length }} riwayat</span>
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

    <!-- Detail Modal -->
    <ApplicationDetailModal
      v-model="showDetailModal"
      :application="selectedApplication"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
  Table,
  TableHeader,
  TableBody,
  TableHead,
  TableRow,
  TableCell,
} from '@/components/ui/table'
import { Eye, History, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import api from '@/plugins/axios'
import { useDocumentStore } from '@/stores/document'
import ApplicationDetailModal from '@/components/ApplicationDetailModal.vue'
import { formatDateTime, formatDecision } from '@/utils/formatters'
import type { Application } from '@/types'

const docStore = useDocumentStore()

const historyReviews = ref<any[]>([])
const loading = ref(false)
const showDetailModal = ref(false)
const selectedApplication = ref<Application | null>(null)

const currentPage = ref(1)
const itemsPerPage = ref(10)

const totalPages = computed(() => Math.ceil(historyReviews.value.length / itemsPerPage.value) || 1)

const paginatedReviews = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return historyReviews.value.slice(start, start + itemsPerPage.value)
})

async function fetchHistory() {
  loading.value = true
  try {
    const res = await api.get('/reviews/history')
    historyReviews.value = res.data?.data ?? res.data ?? []
  } catch (e) {
    console.error('Failed to load history', e)
  } finally {
    loading.value = false
  }
}

async function viewAppDetail(appId: number) {
  await docStore.fetchApplication(appId)
  selectedApplication.value = docStore.currentApplication
  showDetailModal.value = true
}

function getDecisionBadgeClass(dec: string): string {
  switch (dec) {
    case 'approved':
      return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20'
    case 'revision_required':
      return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20'
    case 'rejected':
      return 'bg-red-500/10 text-red-600 dark:text-red-400 border-red-500/20'
    default:
      return 'bg-muted text-muted-foreground'
  }
}

onMounted(() => {
  fetchHistory()
})
</script>
