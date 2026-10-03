<template>
  <div class="flex min-h-screen w-full">
    <!-- Left panel: dark branded -->
    <div class="hidden lg:flex w-[48%] bg-neutral-950 text-white p-12 flex-col justify-between relative overflow-hidden border-r border-neutral-800">
      <div class="relative z-10 animate-fade-in-up">
        <div class="w-10 h-10 rounded-lg bg-primary text-primary-foreground flex items-center justify-center font-bold mb-8 shadow-sm">
          <FileCheck class="w-5 h-5" />
        </div>
        <h1 class="text-3xl font-bold leading-tight tracking-tight mb-4">
          Sistem Persetujuan<br/>Dokumen Kelayakan
        </h1>
        <p class="text-sm text-neutral-400 max-w-[420px] mb-10 leading-relaxed">
          Platform digital terpadu untuk pengajuan, verifikasi administrasi, penilaian teknis, dan pengesahan dokumen perizinan secara transparan dan akuntabel.
        </p>

        <div class="flex flex-col gap-4">
          <div v-for="(f, i) in features" :key="i" class="flex items-center gap-3">
            <span class="flex items-center justify-center w-9 h-9 bg-neutral-900 border border-neutral-800 rounded-lg shrink-0 text-primary">
              <component :is="f.icon" class="w-4 h-4" />
            </span>
            <span class="text-xs text-neutral-300">{{ f.text }}</span>
          </div>
        </div>
      </div>

      <div class="relative z-10 flex items-center justify-between text-xs text-neutral-500 pt-6 border-t border-neutral-800">
        <span>&copy; {{ currentYear }} Sistem Persetujuan Dokumen</span>
        <Badge variant="outline" class="text-[10px] text-neutral-400 border-neutral-700 bg-neutral-900">
          v2.0 · shadcn-vue
        </Badge>
      </div>

      <!-- Decorative shapes -->
      <div class="absolute -top-[20%] -right-[10%] w-[500px] h-[500px] border border-neutral-800 rounded-full opacity-30 pointer-events-none"></div>
      <div class="absolute -bottom-[30%] -left-[15%] w-[600px] h-[600px] border border-neutral-800 rounded-full opacity-20 pointer-events-none"></div>
    </div>

    <!-- Right panel: form area -->
    <div class="flex-1 flex items-center justify-center p-6 sm:p-10 bg-background">
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
import { Badge } from '@/components/ui/badge'
import {
  FileCheck,
  FileEdit,
  CheckCircle2,
  Shield,
  BarChart3
} from 'lucide-vue-next'

const currentYear = new Date().getFullYear()

const features = [
  { icon: FileEdit, text: 'Pengajuan dokumen digital cepat dengan pelacakan riwayat' },
  { icon: CheckCircle2, text: 'Proses persetujuan dan verifikasi bertahap secara real-time' },
  { icon: Shield, text: 'Keamanan data dan audit trail tercatat rapi' },
  { icon: BarChart3, text: 'Dashboard analitik interaktif untuk monitoring beban' },
]
</script>
