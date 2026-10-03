<template>
  <div class="max-w-[1200px] mx-auto flex flex-col gap-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h1 class="text-xl font-bold text-gray-900 leading-tight">
          Riwayat Hasil Penilaian
        </h1>
        <p class="text-[13px] text-gray-500 mt-0.5">
          Daftar seluruh keputusan verifikasi dan review yang telah Anda berikan
        </p>
      </div>
      <Badge
        variant="secondary"
        class="text-[12px] font-semibold px-3 py-1.5 self-start sm:self-auto bg-gray-100 text-gray-600"
      >
        Total Riwayat: {{ historyReviews.length }}
      </Badge>
    </div>

    <!-- Table of Reviews -->
    <Card class="border-gray-200">
      <CardContent class="p-0">
        <div class="rounded-md">
          <Table>
            <TableHeader>
              <TableRow class="bg-gray-50 hover:bg-gray-50">
                <TableHead class="text-[12px] font-semibold text-gray-500 min-w-[150px]">
                  Waktu Penilaian
                </TableHead>
                <TableHead class="text-[12px] font-semibold text-gray-500 min-w-[260px]">
                  Permohonan Dokumen
                </TableHead>
                <TableHead class="text-[12px] font-semibold text-gray-500 min-w-[140px]">
                  Keputusan
                </TableHead>
                <TableHead class="text-[12px] font-semibold text-gray-500 min-w-[280px]">
                  Catatan Reviewer
                </TableHead>
                <TableHead class="text-[12px] font-semibold text-gray-500 text-right min-w-[100px]">
                  Aksi
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <template v-if="paginatedReviews.length">
                <TableRow
                  v-for="data in paginatedReviews"
                  :key="data.id"
                  class="hover:bg-gray-50/50"
                >
                  <TableCell class="text-[12px] text-gray-500 font-medium whitespace-nowrap">
                    {{ formatDateTime(data.reviewed_at || data.created_at) }}
                  </TableCell>

                  <TableCell>
                    <div class="flex flex-col">
                      <div class="flex items-center gap-1.5 mb-1">
                        <span class="font-mono text-gray-400 text-[11px]">
                          {{ data.application?.code || '-' }}
                        </span>
                        <Badge
                          v-if="data.application?.document_type"
                          variant="outline"
                          class="text-[9px] font-semibold uppercase text-gray-500 border-gray-300"
                        >
                          {{ data.application.document_type }}
                        </Badge>
                      </div>
                      <span class="font-semibold text-gray-900 text-[13px] truncate max-w-[280px]">
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
                    <p class="text-[12px] text-gray-600 line-clamp-2 max-w-[320px]">
                      {{ data.note || '-' }}
                    </p>
                  </TableCell>

                  <TableCell class="text-right">
                    <Button
                      v-if="data.application_id"
                      variant="ghost"
                      size="sm"
                      class="h-7 px-2 text-[12px] font-medium gap-1 text-gray-500 hover:text-gray-900 hover:bg-gray-100"
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
                  <div
                    class="flex flex-col items-center justify-center text-gray-400 text-xs gap-2 py-8"
                  >
                    <History class="w-10 h-10 mb-1" />
                    <h3 class="text-[14px] font-bold text-gray-700">
                      Belum Ada Riwayat Penilaian
                    </h3>
                    <p class="text-[12px] text-gray-400 max-w-[320px] text-center">
                      Setiap permohonan yang Anda setujui, tolak, atau minta revisi akan tercatat
                      di halaman ini.
                    </p>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Pagination Controls -->
        <div
          v-if="historyReviews.length > itemsPerPage"
          class="flex items-center justify-between p-4 border-t border-gray-100 text-[12px] text-gray-500"
        >
          <span>
            Menampilkan
            {{ (currentPage - 1) * itemsPerPage + 1 }} -
            {{ Math.min(currentPage * itemsPerPage, historyReviews.length) }} dari
            {{ historyReviews.length }} riwayat
          </span>
          <div class="flex items-center gap-1.5">
            <Button
              variant="outline"
              size="icon"
              class="h-7 w-7 border-gray-200"
              :disabled="currentPage <= 1"
              @click="currentPage--"
            >
              <ChevronLeft class="w-3.5 h-3.5" />
            </Button>
            <span class="px-2 font-medium">{{ currentPage }} / {{ totalPages }}</span>
            <Button
              variant="outline"
              size="icon"
              class="h-7 w-7 border-gray-200"
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

const totalPages = computed(
  () => Math.ceil(historyReviews.value.length / itemsPerPage.value) || 1
)

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
      return 'bg-emerald-50 text-emerald-600 border-emerald-200'
    case 'revision_required':
      return 'bg-amber-50 text-amber-600 border-amber-200'
    case 'rejected':
      return 'bg-red-50 text-red-600 border-red-200'
    default:
      return 'bg-gray-100 text-gray-500'
  }
}

onMounted(() => {
  fetchHistory()
})
</script>
