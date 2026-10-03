<template>
  <div class="max-w-[760px] mx-auto">
    <!-- Header -->
    <div class="mb-6 animate-fade-in-up">
      <Button
        as-child
        variant="ghost"
        size="sm"
        class="mb-2 p-0 text-xs font-semibold gap-1.5 text-muted-foreground hover:text-foreground h-auto"
      >
        <router-link to="/pemohon/dashboard">
          <ArrowLeft class="w-3.5 h-3.5" />
          <span>Kembali ke Dashboard</span>
        </router-link>
      </Button>
      <h1 class="text-2xl font-bold text-foreground mb-1">
        {{ isEdit ? 'Perbaiki Permohonan (Revisi)' : 'Pengajuan Permohonan Dokumen Kelayakan' }}
      </h1>
      <p class="text-xs text-muted-foreground leading-relaxed">
        {{ isEdit ? 'Perbarui informasi dan unggah berkas perbaikan sesuai catatan penilai untuk diajukan ulang.' : 'Lengkapi data administrasi dan lampirkan dokumen permohonan yang valid untuk diproses oleh penilai.' }}
      </p>
    </div>

    <!-- Main Form Card -->
    <Card class="shadow-sm border animate-fade-in-up delay-100">
      <CardContent class="p-6 space-y-5">
        <!-- Error message -->
        <Alert v-if="docStore.error" variant="destructive" class="py-2.5">
          <AlertDescription class="text-xs">
            {{ docStore.error }}
          </AlertDescription>
        </Alert>

        <!-- Success Alert -->
        <Alert v-if="successMessage" class="bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/30 py-2.5">
          <AlertDescription class="text-xs">
            {{ successMessage }}
          </AlertDescription>
        </Alert>

        <!-- Revision Alert if in Edit Mode -->
        <Alert v-if="isEdit && latestReviewNote" class="bg-amber-500/10 text-amber-800 dark:text-amber-200 border-amber-500/30 py-3">
          <AlertTriangle class="w-4 h-4 text-amber-600" />
          <AlertDescription class="text-xs">
            <span class="font-bold block mb-1">Catatan Permintaan Revisi dari Penilai:</span>
            <span class="whitespace-pre-line leading-relaxed">{{ latestReviewNote }}</span>
          </AlertDescription>
        </Alert>

        <form @submit.prevent="handleSubmit(true)" class="space-y-5">
          <!-- Document Type Selector -->
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-foreground" for="doc-type">
              Jenis Dokumen Kelayakan *
            </label>
            <Select v-model="form.document_type">
              <SelectTrigger class="w-full text-sm">
                <SelectValue placeholder="Pilih jenis dokumen..." />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="t in documentTypes" :key="t.value" :value="t.value">
                  {{ t.label }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Title -->
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-foreground" for="doc-title">
              Judul / Perihal Permohonan *
            </label>
            <Input
              id="doc-title"
              v-model="form.title"
              placeholder="Contoh: Permohonan AMDAL Pembangunan Gedung Graha Medika"
              class="w-full text-sm"
              required
            />
          </div>

          <!-- Description -->
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-foreground" for="doc-desc">
              Keterangan & Rincian Permohonan *
            </label>
            <Textarea
              id="doc-desc"
              v-model="form.description"
              rows="4"
              placeholder="Jelaskan detail permohonan, lokasi kegiatan, atau kelengkapan berkas..."
              class="w-full text-sm resize-none"
              required
            />
          </div>

          <!-- File Upload Area -->
          <div class="space-y-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-foreground">
              Berkas Lampiran Dokumen {{ isEdit ? '(Opsional jika tidak ada berkas baru)' : '*' }}
            </label>

            <div
              class="border-2 border-dashed rounded-lg text-center cursor-pointer transition-all p-6"
              :class="isDragging ? 'border-primary bg-primary/10' : 'border-border hover:border-primary/50 bg-muted/20'"
              @drop.prevent="handleDrop"
              @dragover.prevent="isDragging = true"
              @dragleave="isDragging = false"
              @click="fileInputRef?.click()"
            >
              <input
                ref="fileInputRef"
                type="file"
                multiple
                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx"
                class="hidden"
                @change="handleFileSelect"
              />
              <div class="flex flex-col items-center gap-2">
                <UploadCloud class="w-8 h-8 text-primary" />
                <span class="text-xs font-bold text-foreground">Klik atau seret file dokumen ke area ini</span>
                <span class="text-[11px] text-muted-foreground">Mendukung format PDF, DOC, DOCX, XLS, XLSX (Maks. 20MB per file)</span>
              </div>
            </div>

            <!-- Selected Files List -->
            <div v-if="selectedFiles.length" class="space-y-2 mt-2">
              <span class="text-xs font-bold text-foreground">
                Berkas yang akan diunggah ({{ selectedFiles.length }}):
              </span>
              <div
                v-for="(file, idx) in selectedFiles"
                :key="idx"
                class="flex items-center justify-between p-3 bg-muted/40 border rounded-lg text-xs"
              >
                <div class="flex items-center gap-2.5 min-w-0">
                  <FileCheck class="w-4 h-4 text-primary shrink-0" />
                  <span class="font-medium text-foreground truncate">{{ file.name }}</span>
                  <Badge variant="secondary" class="text-[10px]">
                    {{ formatFileSize(file.size) }}
                  </Badge>
                </div>
                <Button
                  type="button"
                  variant="ghost"
                  size="icon"
                  class="h-6 w-6 text-destructive hover:bg-destructive/10"
                  @click.stop="removeFile(idx)"
                >
                  <X class="w-3.5 h-3.5" />
                </Button>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t mt-4">
            <Button
              as-child
              variant="outline"
              size="sm"
            >
              <router-link to="/pemohon/dashboard">
                Batal
              </router-link>
            </Button>

            <div class="flex items-center gap-2">
              <Button
                v-if="!isEdit"
                type="button"
                variant="outline"
                size="sm"
                class="gap-1.5"
                :disabled="docStore.loading || !form.title.trim()"
                @click="handleSubmit(false)"
              >
                <Save class="w-3.5 h-3.5" />
                <span>Simpan Draft Saja</span>
              </Button>

              <Button
                type="submit"
                size="sm"
                class="gap-1.5"
                :disabled="docStore.loading || !isFormValid"
              >
                <Loader2 v-if="docStore.loading" class="w-3.5 h-3.5 animate-spin" />
                <RefreshCw v-else-if="isEdit" class="w-3.5 h-3.5" />
                <Send v-else class="w-3.5 h-3.5" />
                <span>{{ submitButtonLabel }}</span>
              </Button>
            </div>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
} from '@/components/ui/select'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import {
  ArrowLeft,
  AlertTriangle,
  UploadCloud,
  FileCheck,
  X,
  Save,
  Send,
  RefreshCw,
  Loader2,
} from 'lucide-vue-next'
import { useDocumentStore } from '@/stores/document'
import { formatFileSize } from '@/utils/formatters'

const route = useRoute()
const router = useRouter()
const docStore = useDocumentStore()

const fileInputRef = ref<HTMLInputElement>()
const isDragging = ref(false)
const selectedFiles = ref<File[]>([])
const successMessage = ref('')
const latestReviewNote = ref('')

const isEdit = computed(() => !!route.query.edit)

const documentTypes = [
  { label: 'SLF - Sertifikat Laik Fungsi', value: 'SLF' },
  { label: 'AMDAL - Analisis Mengenai Dampak Lingkungan', value: 'AMDAL' },
  { label: 'IMB - Izin Mendirikan Bangunan', value: 'IMB' },
  { label: 'UKL-UPL - Upaya Pengelolaan & Pemantauan Lingkungan', value: 'UKL-UPL' },
  { label: 'SIUP - Surat Izin Usaha Perdagangan', value: 'SIUP' },
]

const form = reactive({
  title: '',
  description: '',
  document_type: 'SLF',
})

const isFormValid = computed(() => {
  return form.title.trim().length > 0 &&
    form.description.trim().length > 0 &&
    form.document_type.length > 0 &&
    (isEdit.value || selectedFiles.value.length > 0)
})

const submitButtonLabel = computed(() => {
  if (docStore.loading) return 'Memproses...'
  if (isEdit.value) return 'Kirim Ulang Permohonan'
  return 'Ajukan Permohonan Sekarang'
})

function handleFileSelect(e: Event) {
  const files = (e.target as HTMLInputElement).files
  if (files) {
    for (let i = 0; i < files.length; i++) {
      selectedFiles.value.push(files[i])
    }
  }
}

function handleDrop(e: DragEvent) {
  isDragging.value = false
  if (e.dataTransfer?.files) {
    for (let i = 0; i < e.dataTransfer.files.length; i++) {
      selectedFiles.value.push(e.dataTransfer.files[i])
    }
  }
}

function removeFile(index: number) {
  selectedFiles.value.splice(index, 1)
  if (fileInputRef.value) fileInputRef.value.value = ''
}

async function handleSubmit(autoSubmit: boolean) {
  if (!form.title.trim() || !form.description.trim()) return

  try {
    if (isEdit.value) {
      const editId = Number(route.query.edit)
      await docStore.updateApplication(editId, {
        title: form.title,
        description: form.description,
        document_type: form.document_type,
      })

      if (selectedFiles.value.length > 0) {
        await docStore.uploadDocuments(editId, selectedFiles.value)
      }

      if (autoSubmit) {
        await docStore.submitApplication(editId)
      }

      successMessage.value = 'Permohonan berhasil diperbaiki dan diajukan ulang!'
    } else {
      const createdApp = await docStore.createApplication({
        title: form.title,
        description: form.description,
        document_type: form.document_type,
      })

      if (selectedFiles.value.length > 0) {
        await docStore.uploadDocuments(createdApp.id, selectedFiles.value)
      }

      if (autoSubmit) {
        await docStore.submitApplication(createdApp.id)
        successMessage.value = 'Permohonan berhasil dibuat dan diajukan ke penilai!'
      } else {
        successMessage.value = 'Draft permohonan berhasil disimpan!'
      }
    }

    setTimeout(() => {
      router.push('/pemohon/dashboard')
    }, 1200)
  } catch {
    // Error is handled in store
  }
}

onMounted(async () => {
  if (isEdit.value) {
    const editId = Number(route.query.edit)
    await docStore.fetchApplication(editId)
    if (docStore.currentApplication) {
      const app = docStore.currentApplication
      form.title = app.title
      form.description = app.description
      form.document_type = app.document_type || 'SLF'
      if (app.reviews && app.reviews.length > 0) {
        latestReviewNote.value = app.reviews[0].note || ''
      }
    }
  }
})
</script>
