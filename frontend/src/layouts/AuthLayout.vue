<template>
  <div class="min-h-screen w-full bg-white flex flex-col justify-between">
    <!-- Top Header -->
    <header class="w-full px-6 sm:px-10 py-6 flex items-center justify-between">
      <div class="flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-md bg-[#3b49f5] text-white flex items-center justify-center font-bold shadow-xs">
          <FileCheck class="w-4 h-4" />
        </div>
        <span class="text-base sm:text-lg font-bold text-neutral-900 tracking-tight">
          Sistem Persetujuan Dokumen
        </span>
      </div>
    </header>

    <!-- Main Content (Centered) -->
    <main class="flex-1 flex items-center justify-center px-4 py-8">
      <div class="w-full max-w-[420px]">
        <router-view v-slot="{ Component }">
          <transition name="page" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </div>
    </main>

    <!-- Subtle Footer -->
    <footer class="py-4 text-center text-xs text-neutral-400">
      &copy; {{ currentYear }} Sistem Persetujuan Dokumen
    </footer>
  </div>
</template>

<script setup lang="ts">
import { FileCheck } from 'lucide-vue-next'

const currentYear = new Date().getFullYear()
</script>
