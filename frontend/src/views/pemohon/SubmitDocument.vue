<template>
  <div class="max-w-[720px] mx-auto">
    <div class="mb-6 animate-fade-in-up">
      <router-link to="/pemohon/dashboard" class="inline-flex items-center gap-1 text-sm font-bold text-primary no-underline mb-4 hover:text-primary-dark transition-colors">
        <Icon icon="mdi:arrow-left" class="text-base" /> Kembali ke Dashboard
      </router-link>
      <h1 class="font-brand text-2xl font-bold text-ink mb-1.5">{{ isEdit ? 'Perbaiki Dokumen' : 'Ajukan Dokumen Baru' }}</h1>
      <p class="text-sm text-mute">{{ isEdit ? 'Perbarui dokumen Anda sesuai catatan revisi dari penilai.' : 'Lengkapi form berikut untuk mengajukan dokumen baru.' }}</p>
    </div>

    <div class="relative overflow-hidden bg-canvas border border-hairline rounded-sm p-8 animate-fade-in-up delay-100">
      <div class="absolute top-0 left-0 w-3 h-3 bg-primary"></div>

      <!-- Error -->
      <div v-if="docStore.error" class="flex items-center gap-2.5 px-4 py-3 bg-error/10 border border-error/20 rounded-sm mb-6 text-sm text-error">
        <Icon icon="mdi:alert-circle-outline" class="flex-shrink-0" />
        <span class="flex-1">{{ docStore.error }}</span>
        <button class="bg-transparent border-none text-error cursor-pointer" @click="docStore.clearError"><Icon icon="mdi:close" /></button>
      </div>

      <!-- Success -->
      <div v-if="successMessage" class="flex items-center gap-2.5 px-4 py-3 bg-primary/10 border border-primary/30 rounded-sm mb-6 text-sm text-success-deep animate-fade-in">
        <Icon icon="mdi:check-circle-outline" class="flex-shrink-0" />
        <span>{{ successMessage }}</span>
      </div>

      <form @submit.prevent="handleSubmit" class="flex flex-col gap-5">
        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-bold text-ink" for="doc-title">Judul Dokumen *</label>
          <input id="doc-title" v-model="form.title" type="text" required
            class="w-full h-11 px-4 bg-canvas border border-hairline rounded-sm font-brand text-base text-ink outline-none transition-colors focus:border-primary focus:border-2 focus:px-[15px] placeholder:text-ash"
            placeholder="Contoh: Proposal Penelitian AI Generatif" />
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-bold text-ink" for="doc-desc">Deskripsi *</label>
          <textarea id="doc-desc" v-model="form.description" rows="4" required
            class="w-full px-4 py-3 bg-canvas border border-hairline rounded-sm font-brand text-base text-ink outline-none resize-y min-h-[100px] transition-colors focus:border-primary focus:border-2 focus:px-[15px] placeholder:text-ash"
            placeholder="Jelaskan isi dan tujuan dokumen secara singkat..."></textarea>
          <span class="text-xs text-mute text-right">{{ form.description.length }}/500 karakter</span>
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-sm font-bold text-ink">File Dokumen *</label>
          <div
            class="border-2 border-dashed rounded-sm text-center cursor-pointer transition-all"
            :class="selectedFile ? 'border-primary bg-primary/5 p-4 border-solid' : isDragging ? 'border-primary bg-primary/5 p-8' : 'border-hairline p-8 hover:border-primary'"
            @drop.prevent="handleDrop" @dragover.prevent="isDragging = true" @dragleave="isDragging = false" @click="fileInputRef?.click()"
          >
            <input ref="fileInputRef" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" class="hidden" @change="handleFileSelect" />
            <div v-if="!selectedFile" class="flex flex-col items-center gap-2">
              <Icon icon="mdi:cloud-upload-outline" class="text-3xl text-mute" />
              <span class="text-sm font-semibold text-ink">Klik atau drag & drop file di sini</span>
              <span class="text-xs text-mute">PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX (Maks. 10MB)</span>
            </div>
            <div v-else class="flex items-center gap-3">
              <Icon icon="mdi:file-document-outline" class="text-2xl text-primary" />
              <div class="flex-1 text-left flex flex-col">
                <span class="text-sm font-semibold text-ink">{{ selectedFile.name }}</span>
                <span class="text-xs text-mute">{{ formatFileSize(selectedFile.size) }}</span>
              </div>
              <button type="button" @click.stop="removeFile"
                class="w-7 h-7 flex items-center justify-center bg-transparent border border-hairline rounded-sm text-mute text-xs cursor-pointer hover:bg-error hover:border-error hover:text-white transition-all">
                <Icon icon="mdi:close" />
              </button>
            </div>
          </div>
        </div>

        <!-- Revision notes -->
        <div v-if="isEdit && reviewNotes" class="bg-accent-yellow-pale border border-warning/20 rounded-sm px-5 py-4">
          <h3 class="text-sm font-bold text-warning mb-2 flex items-center gap-2">
            <Icon icon="mdi:pencil-outline" /> Catatan Revisi dari Penilai
          </h3>
          <p class="text-sm text-body leading-relaxed">{{ reviewNotes }}</p>
        </div>

        <div class="flex justify-end gap-3 pt-3 border-t border-hairline">
          <router-link to="/pemohon/dashboard"
            class="inline-flex items-center h-11 px-6 bg-transparent border border-hairline rounded-sm font-brand text-sm font-bold text-ink no-underline hover:bg-surface-soft transition-all">
            Batal
          </router-link>
          <button type="submit" :disabled="docStore.loading || !isFormValid"
            class="inline-flex items-center gap-2 h-11 px-6 bg-primary text-ink border-none rounded-sm font-brand text-sm font-bold cursor-pointer transition-colors hover:bg-primary-dark disabled:bg-surface-soft disabled:text-ash disabled:cursor-not-allowed">
            <span v-if="docStore.loading" class="w-4 h-4 border-2 border-transparent border-t-current rounded-full animate-spin"></span>
            <span>{{ docStore.loading ? 'Mengirim...' : (isEdit ? 'Kirim Ulang' : 'Kirim Pengajuan') }}</span>
          </button>
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
const selectedFile = ref<File | null>(null)
const successMessage = ref('')

const isEdit = computed(() => !!route.query.edit)
const reviewNotes = ref('')
const form = reactive({ title: '', description: '' })
const isFormValid = computed(() => form.title.trim().length > 0 && form.description.trim().length > 0 && selectedFile.value !== null)

function handleFileSelect(e: Event) { const f = (e.target as HTMLInputElement).files?.[0]; if (f) selectedFile.value = f }
function handleDrop(e: DragEvent) { isDragging.value = false; if (e.dataTransfer?.files?.[0]) selectedFile.value = e.dataTransfer.files[0] }
function removeFile() { selectedFile.value = null; if (fileInputRef.value) fileInputRef.value.value = '' }
function formatFileSize(bytes: number) { if (bytes < 1024) return `${bytes} B`; if (bytes < 1048576) return `${(bytes / 1024).toFixed(1)} KB`; return `${(bytes / 1048576).toFixed(1)} MB` }

async function handleSubmit() {
  if (!isFormValid.value) return
  const fd = new FormData()
  fd.append('title', form.title); fd.append('description', form.description)
  if (selectedFile.value) fd.append('file', selectedFile.value)
  try {
    if (isEdit.value) { await docStore.updateDocument(Number(route.query.edit), fd); successMessage.value = 'Dokumen berhasil diperbarui!' }
    else { await docStore.submitDocument(fd); successMessage.value = 'Dokumen berhasil diajukan!' }
    setTimeout(() => router.push('/pemohon/dashboard'), 1500)
  } catch { /* handled */ }
}

onMounted(async () => {
  if (isEdit.value) {
    await docStore.fetchDocument(Number(route.query.edit))
    if (docStore.currentDocument) {
      form.title = docStore.currentDocument.title; form.description = docStore.currentDocument.description
      const r = docStore.currentDocument.reviews?.slice(-1)[0]; if (r) reviewNotes.value = r.notes
    }
  }
})
</script>
