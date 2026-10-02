<template>
  <div class="max-w-[760px] mx-auto">
    <!-- Header -->
    <div class="mb-6 animate-fade-in-up">
      <router-link
        to="/pemohon/dashboard"
        class="inline-flex items-center gap-1.5 text-xs font-bold text-primary no-underline mb-3 hover:text-primary-dark transition-colors"
      >
        <Icon icon="mdi:arrow-left" class="text-base" /> Kembali ke Dashboard
      </router-link>
      <h1 class="font-brand text-2xl font-bold text-ink mb-1">
        {{ isEdit ? 'Perbaiki Permohonan (Revisi)' : 'Pengajuan Permohonan Dokumen Kelayakan' }}
      </h1>
      <p class="text-xs text-mute leading-relaxed">
        {{ isEdit ? 'Perbarui informasi dan unggah berkas perbaikan sesuai catatan penilai untuk diajukan ulang.' : 'Lengkapi data administrasi dan lampirkan dokumen permohonan yang valid untuk diproses oleh penilai.' }}
      </p>
    </div>

    <!-- Main Form Container -->
    <div class="relative overflow-hidden bg-canvas border border-hairline rounded-sm p-6 sm:p-8 animate-fade-in-up delay-100 shadow-sm">
      <div class="absolute top-0 left-0 w-3 h-3 bg-primary"></div>

      <!-- Error message -->
      <div v-if="docStore.error" class="flex items-center gap-2.5 px-4 py-3 bg-error/10 border border-error/20 rounded-sm mb-6 text-xs text-error">
        <Icon icon="mdi:alert-circle" class="text-base flex-shrink-0" />
        <span class="flex-1">{{ docStore.error }}</span>
        <button class="bg-transparent border-none text-error cursor-pointer" @click="docStore.clearError">
          <Icon icon="mdi:close" />
        </button>
      </div>

      <!-- Success Alert -->
      <div v-if="successMessage" class="flex items-center gap-2.5 px-4 py-3 bg-primary/10 border border-primary/30 rounded-sm mb-6 text-xs text-success-deep animate-fade-in">
        <Icon icon="mdi:check-circle" class="text-base flex-shrink-0 text-primary" />
        <span>{{ successMessage }}</span>
      </div>

      <!-- Revision Alert if in Edit Mode -->
      <div v-if="isEdit && latestReviewNote" class="bg-orange-500/10 border border-orange-500/30 rounded-sm p-4 mb-6 text-xs">
        <div class="flex items-center gap-2 font-bold text-orange-600 mb-1.5">
          <Icon icon="mdi:alert-circle-outline" class="text-base" />
          <span>Catatan Permintaan Revisi dari Penilai:</span>
        </div>
        <p class="text-body leading-relaxed pl-6 whitespace-pre-line">{{ latestReviewNote }}</p>
      </div>

      <form @submit.prevent="handleSubmit(true)" class="flex flex-col gap-5">
        <!-- Document Type Selector -->
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-bold uppercase tracking-wider text-ink" for="doc-type">
            Jenis Dokumen Kelayakan *
          </label>
          <select
            id="doc-type"
            v-model="form.document_type"
            required
            class="w-full h-11 px-4 bg-canvas border border-hairline rounded-sm font-brand text-sm text-ink outline-none transition-colors focus:border-primary focus:border-2 focus:px-[15px]"
          >
            <option value="" disabled>Pilih jenis dokumen...</option>
            <option value="SLF">SLF - Sertifikat Laik Fungsi</option>
            <option value="AMDAL">AMDAL - Analisis Mengenai Dampak Lingkungan</option>
            <option value="IMB">IMB - Izin Mendirikan Bangunan</option>
            <option value="UKL-UPL">UKL-UPL - Upaya Pengelolaan & Pemantauan Lingkungan</option>
            <option value="SIUP">SIUP - Surat Izin Usaha Perdagangan</option>
          </select>
        </div>

        <!-- Title -->
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-bold uppercase tracking-wider text-ink" for="doc-title">
            Judul / Perihal Permohonan *
          </label>
          <input
            id="doc-title"
            v-model="form.title"
            type="text"
            required
            placeholder="Contoh: Permohonan AMDAL Pembangunan Gedung Graha Medika"
            class="w-full h-11 px-4 bg-canvas border border-hairline rounded-sm font-brand text-sm text-ink outline-none transition-colors focus:border-primary focus:border-2 focus:px-[15px] placeholder:text-ash"
          />
        </div>

        <!-- Description -->
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-bold uppercase tracking-wider text-ink" for="doc-desc">
            Keterangan & Rincian Permohonan *
          </label>
          <textarea
            id="doc-desc"
            v-model="form.description"
            rows="4"
            required
            placeholder="Jelaskan detail permohonan, lokasi kegiatan, atau kelengkapan berkas..."
            class="w-full px-4 py-3 bg-canvas border border-hairline rounded-sm font-brand text-sm text-ink outline-none resize-y min-h-[100px] transition-colors focus:border-primary focus:border-2 focus:px-[15px] placeholder:text-ash"
          ></textarea>
        </div>

        <!-- File Upload Area -->
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-bold uppercase tracking-wider text-ink">
            Berkas Lampiran Dokumen {{ isEdit ? '(Opsional jika tidak ada berkas baru)' : '*' }}
          </label>
          
          <div
            class="border-2 border-dashed rounded-sm text-center cursor-pointer transition-all p-6"
            :class="isDragging ? 'border-primary bg-primary/5' : 'border-hairline hover:border-primary bg-surface-soft/40'"
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
              <Icon icon="mdi:cloud-upload-outline" class="text-3xl text-primary" />
              <span class="text-xs font-bold text-ink">Klik atau tarik file dokumen ke area ini</span>
              <span class="text-[11px] text-mute">Mendukung format PDF, DOC, DOCX, XLS, XLSX (Maks. 20MB per file)</span>
            </div>
          </div>

          <!-- Selected Files List -->
          <div v-if="selectedFiles.length" class="flex flex-col gap-2 mt-2">
            <span class="text-xs font-bold text-ink">Berkas yang akan diunggah ({{ selectedFiles.length }}):</span>
            <div
              v-for="(file, idx) in selectedFiles"
              :key="idx"
              class="flex items-center justify-between p-2.5 bg-canvas border border-hairline rounded-sm text-xs"
            >
              <div class="flex items-center gap-2 min-w-0">
                <Icon icon="mdi:file-check-outline" class="text-primary text-base flex-shrink-0" />
                <span class="font-medium text-ink truncate">{{ file.name }}</span>
                <span class="text-mute text-[10px]">({{ formatFileSize(file.size) }})</span>
              </div>
              <button
                type="button"
                @click.stop="removeFile(idx)"
                class="w-6 h-6 flex items-center justify-center bg-transparent border-none text-mute hover:text-error cursor-pointer transition-colors"
              >
                <Icon icon="mdi:close" />
              </button>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-hairline mt-2">
          <router-link
            to="/pemohon/dashboard"
            class="px-5 py-2.5 bg-transparent border border-hairline rounded-sm font-brand text-xs font-bold text-ink no-underline hover:bg-surface-soft transition-colors"
          >
            Batal
          </router-link>

          <div class="flex items-center gap-2">
            <button
              v-if="!isEdit"
              type="button"
              :disabled="docStore.loading || !form.title.trim()"
              @click="handleSubmit(false)"
              class="px-5 py-2.5 bg-surface-soft border border-hairline rounded-sm font-brand text-xs font-bold text-ink cursor-pointer hover:bg-hairline transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Simpan Draft Saja
            </button>

            <button
              type="submit"
              :disabled="docStore.loading || !isFormValid"
              class="inline-flex items-center gap-2 px-6 py-2.5 bg-primary text-ink border-none rounded-sm font-brand text-xs font-bold cursor-pointer hover:bg-primary-dark transition-all shadow-sm disabled:bg-surface-soft disabled:text-ash disabled:cursor-not-allowed"
            >
              <span v-if="docStore.loading" class="w-3.5 h-3.5 border-2 border-transparent border-t-current rounded-full animate-spin"></span>
              <Icon v-else icon="mdi:send" />
              <span>{{ submitButtonLabel }}</span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Icon } from '@iconify/vue'
import { useDocumentStore } from '@/stores/document'

const route = useRoute()
const router = useRouter()
const docStore = useDocumentStore()

const fileInputRef = ref<HTMLInputElement>()
const isDragging = ref(false)
const selectedFiles = ref<File[]>([])
const successMessage = ref('')
const latestReviewNote = ref('')

const isEdit = computed(() => !!route.query.edit)

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

function formatFileSize(bytes: number) {
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1048576) return `${(bytes / 1024).toFixed(1)} KB`
  return `${(bytes / 1048576).toFixed(1)} MB`
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
