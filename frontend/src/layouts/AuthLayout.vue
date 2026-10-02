<template>
  <div class="flex min-h-screen w-full">
    <!-- Left panel: dark branded -->
    <div class="hidden lg:flex w-[48%] bg-surface-dark text-on-dark p-12 flex-col justify-between relative overflow-hidden">
      <div class="relative z-10 animate-fade-in-up">
        <div class="w-4 h-4 bg-primary mb-8"></div>
        <h1 class="font-brand text-[40px] font-bold leading-tight tracking-tight mb-4">
          Sistem Persetujuan<br/>Dokumen
        </h1>
        <p class="text-base text-on-dark-mute max-w-[400px] mb-12 leading-relaxed">
          Platform digital untuk pengajuan, penilaian, dan pengesahan dokumen secara efisien dan transparan.
        </p>
        <div class="flex flex-col gap-5">
          <div v-for="(f, i) in features" :key="i" class="flex items-center gap-3">
            <span class="flex items-center justify-center w-10 h-10 bg-surface-elevated border border-hairline-strong rounded-sm flex-shrink-0">
              <Icon :icon="f.icon" class="text-lg text-primary" />
            </span>
            <span class="text-sm text-on-dark-mute">{{ f.text }}</span>
          </div>
        </div>
      </div>
      <span class="relative z-10 text-xs text-mute">&copy; {{ currentYear }} Sistem Persetujuan Dokumen</span>
      <!-- Decorative circles -->
      <div class="absolute -top-[20%] -right-[10%] w-[500px] h-[500px] border border-hairline-strong rounded-full opacity-15"></div>
      <div class="absolute -bottom-[30%] -left-[15%] w-[600px] h-[600px] border border-hairline-strong rounded-full opacity-10"></div>
    </div>

    <!-- Right panel: form area -->
    <div class="flex-1 flex items-center justify-center p-8 bg-canvas">
      <div class="w-full max-w-[440px]">
        <router-view v-slot="{ Component }">
          <transition name="page" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Icon } from '@iconify/vue'

const currentYear = new Date().getFullYear()

const features = [
  { icon: 'mdi:file-document-edit-outline', text: 'Pengajuan dokumen digital yang mudah dan cepat' },
  { icon: 'mdi:check-decagram', text: 'Proses persetujuan real-time dengan notifikasi' },
  { icon: 'mdi:shield-lock-outline', text: 'Keamanan data terjamin dengan enkripsi' },
  { icon: 'mdi:chart-box-outline', text: 'Dashboard analitik untuk monitoring progress' },
]
</script>
