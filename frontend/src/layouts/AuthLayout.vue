<template>
  <div class="flex min-h-screen w-full">
    <!-- Left panel: dark branded -->
    <div class="hidden lg:flex w-[48%] bg-surface-900 text-surface-0 p-12 flex-col justify-between relative overflow-hidden border-r border-surface-800">
      <div class="relative z-10 animate-fade-in-up">
        <div class="w-10 h-10 rounded-lg bg-primary-500 flex items-center justify-center text-surface-900 font-bold mb-8">
          <i class="pi pi-file-check text-2xl"></i>
        </div>
        <h1 class="font-brand text-[38px] font-bold leading-tight tracking-tight mb-4">
          Sistem Persetujuan<br/>Dokumen Kelayakan
        </h1>
        <p class="text-sm text-surface-300 max-w-[420px] mb-10 leading-relaxed">
          Platform digital terpadu untuk pengajuan, verifikasi administrasi, penilaian teknis, dan pengesahan dokumen perizinan secara transparan dan akuntabel.
        </p>

        <div class="flex flex-col gap-4">
          <div v-for="(f, i) in features" :key="i" class="flex items-center gap-3">
            <span class="flex items-center justify-center w-9 h-9 bg-surface-800 border border-surface-700 rounded-lg flex-shrink-0 text-primary-400">
              <i :class="f.icon" class="text-base"></i>
            </span>
            <span class="text-xs text-surface-300">{{ f.text }}</span>
          </div>
        </div>
      </div>

      <div class="relative z-10 flex items-center justify-between text-xs text-surface-500 pt-6 border-t border-surface-800">
        <span>&copy; {{ currentYear }} Sistem Persetujuan Dokumen</span>
        <Tag value="v2.0 · PrimeVue" severity="secondary" rounded class="text-[10px]" />
      </div>

      <!-- Decorative shapes -->
      <div class="absolute -top-[20%] -right-[10%] w-[500px] h-[500px] border border-surface-800 rounded-full opacity-30 pointer-events-none"></div>
      <div class="absolute -bottom-[30%] -left-[15%] w-[600px] h-[600px] border border-surface-800 rounded-full opacity-20 pointer-events-none"></div>
    </div>

    <!-- Right panel: form area -->
    <div class="flex-1 flex items-center justify-center p-6 sm:p-10 bg-surface-50 dark:bg-surface-950">
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
import Tag from 'primevue/tag'

const currentYear = new Date().getFullYear()

const features = [
  { icon: 'pi pi-file-edit', text: 'Pengajuan dokumen digital cepat dengan pelacakan riwayat' },
  { icon: 'pi pi-check-circle', text: 'Proses persetujuan dan verifikasi bertahap secara real-time' },
  { icon: 'pi pi-shield', text: 'Keamanan data dan audit trail tercatat rapi' },
  { icon: 'pi pi-chart-bar', text: 'Dashboard analitik interaktif untuk monitoring beban' },
]
</script>
