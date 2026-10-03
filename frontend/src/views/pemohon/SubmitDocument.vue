<template>
  <div class="max-w-[760px] mx-auto">
    <!-- Header -->
    <div class="mb-6 animate-fade-in-up">
      <Button
        as="router-link"
        to="/pemohon/dashboard"
        label="Kembali ke Dashboard"
        icon="pi pi-arrow-left"
        severity="secondary"
        text
        size="small"
        class="mb-2 p-0 text-xs font-bold"
      />
      <h1 class="font-brand text-2xl font-bold text-surface-900 dark:text-surface-0 mb-1">
        {{ isEdit ? 'Perbaiki Permohonan (Revisi)' : 'Pengajuan Permohonan Dokumen Kelayakan' }}
      </h1>
      <p class="text-xs text-surface-500 leading-relaxed">
        {{ isEdit ? 'Perbarui informasi dan unggah berkas perbaikan sesuai catatan penilai untuk diajukan ulang.' : 'Lengkapi data administrasi dan lampirkan dokumen permohonan yang valid untuk diproses oleh penilai.' }}
      </p>
    </div>

    <!-- Main Form Card -->
    <Card class="border border-surface-200 dark:border-surface-700 shadow-sm animate-fade-in-up delay-100">
      <template #content>
        <!-- Error message -->
        <Message v-if="docStore.error" severity="error" :closable="true" @close="docStore.clearError" class="mb-5 text-xs">
          {{ docStore.error }}
        </Message>

        <!-- Success Alert -->
        <Message v-if="successMessage" severity="success" class="mb-5 text-xs">
          {{ successMessage }}
        </Message>

        <!-- Revision Alert if in Edit Mode -->
        <Message v-if="isEdit && latestReviewNote" severity="warn" icon="pi pi-exclamation-triangle" class="mb-5 text-xs">
          <div>
            <span class="font-bold block mb-1">Catatan Permintaan Revisi dari Penilai:</span>
            <span class="whitespace-pre-line leading-relaxed">{{ latestReviewNote }}</span>
          </div>
        </Message>

        <form @submit.prevent="handleSubmit(true)" class="space-y-5">
          <!-- Document Type Selector -->
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-surface-700 dark:text-surface-300" for="doc-type">
              Jenis Dokumen Kelayakan *
            </label>
            <Select
              id="doc-type"
              v-model="form.document_type"
              :options="documentTypes"
              optionLabel="label"
              optionValue="value"
              placeholder="Pilih jenis dokumen..."
              class="w-full text-sm"
              required
            />
          </div>

          <!-- Title -->
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-surface-700 dark:text-surface-300" for="doc-title">
              Judul / Perihal Permohonan *
            </label>
            <InputText
              id="doc-title"
              v-model="form.title"
              placeholder="Contoh: Permohonan AMDAL Pembangunan Gedung Graha Medika"
              class="w-full text-sm"
              required
            />
          </div>

          <!-- Description -->
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-surface-700 dark:text-surface-300" for="doc-desc">
              Keterangan & Rincian Permohonan *
            </label>
            <Textarea
              id="doc-desc"
              v-model="form.description"
              rows="4"
              placeholder="Jelaskan detail permohonan, lokasi kegiatan, atau kelengkapan berkas..."
              class="w-full text-sm"
              autoResize
              required
            />
          </div>

          <!-- File Upload Area -->
          <div class="flex flex-col gap-1.5">
            <label class="text-xs font-bold uppercase tracking-wider text-surface-700 dark:text-surface-300">
              Berkas Lampiran Dokumen {{ isEdit ? '(Opsional jika tidak ada berkas baru)' : '*' }}
            </label>

            <div
              class="border-2 border-dashed rounded-lg text-center cursor-pointer transition-all p-6"
              :class="isDragging ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/20' : 'border-surface-300 dark:border-surface-700 hover:border-primary-500 bg-surface-50 dark:bg-surface-800/40'"
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
                <i class="pi pi-cloud-upload text-3xl text-primary-500"></i>
                <span class="text-xs font-bold text-surface-900 dark:text-surface-0">Klik atau seret file dokumen ke area ini</span>
                <span class="text-[11px] text-surface-500">Mendukung format PDF, DOC, DOCX, XLS, XLSX (Maks. 20MB per file)</span>
              </div>
            </div>

            <!-- Selected Files List -->
            <div v-if="selectedFiles.length" class="space-y-2 mt-2">
              <span class="text-xs font-bold text-surface-800 dark:text-surface-200">
                Berkas yang akan diunggah ({{ selectedFiles.length }}):
              </span>
              <div
                v-for="(file, idx) in selectedFiles"
                :key="idx"
                class="flex items-center justify-between p-3 bg-surface-50 dark:bg-surface-800/60 border border-surface-200 dark:border-surface-700 rounded-lg text-xs"
              >
                <div class="flex items-center gap-2.5 min-w-0">
                  <i class="pi pi-file-check text-primary-500 text-lg flex-shrink-0"></i>
                  <span class="font-medium text-surface-900 dark:text-surface-0 truncate">{{ file.name }}</span>
                  <Tag :value="formatFileSize(file.size)" severity="secondary" class="text-[10px]" />
                </div>
                <Button
                  icon="pi pi-times"
                  severity="danger"
                  text
                  rounded
                  size="small"
                  @click.stop="removeFile(idx)"
                />
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-surface-200 dark:border-surface-700 mt-4">
            <Button
              as="router-link"
              to="/pemohon/dashboard"
              label="Batal"
              severity="secondary"
              text
              size="small"
            />

            <div class="flex items-center gap-2">
              <Button
                v-if="!isEdit"
                type="button"
                label="Simpan Draft Saja"
                icon="pi pi-save"
                severity="secondary"
                outlined
                size="small"
                :disabled="docStore.loading || !form.title.trim()"
                @click="handleSubmit(false)"
              />

              <Button
                type="submit"
                :label="submitButtonLabel"
                :icon="isEdit ? 'pi pi-refresh' : 'pi pi-send'"
                severity="primary"
                size="small"
                :loading="docStore.loading"
                :disabled="docStore.loading || !isFormValid"
              />
            </div>
          </div>
        </form>
      </template>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Card from 'primevue/card'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Select from 'primevue/select'
import Message from 'primevue/message'
import Tag from 'primevue/tag'
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
