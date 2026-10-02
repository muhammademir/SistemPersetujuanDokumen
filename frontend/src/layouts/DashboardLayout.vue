<template>
  <div class="flex min-h-screen bg-surface-soft">
    <!-- Sidebar -->
    <aside
      class="fixed top-0 left-0 bottom-0 z-40 w-[260px] bg-surface-dark text-on-dark flex flex-col transition-transform duration-300"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <!-- Brand -->
      <div class="flex items-center gap-3 px-5 py-5 border-b border-hairline-strong">
        <div class="w-3 h-3 bg-primary flex-shrink-0"></div>
        <div class="flex flex-col flex-1">
          <span class="text-lg font-bold leading-tight tracking-tight">SiPerDok</span>
          <span class="text-[11px] font-bold uppercase text-primary tracking-wider mt-0.5">{{ roleLabel }}</span>
        </div>
        <button
          class="lg:hidden flex items-center justify-center w-8 h-8 bg-transparent border border-hairline-strong rounded-sm text-on-dark text-sm cursor-pointer hover:bg-surface-elevated transition-colors"
          @click="sidebarOpen = false"
        >
          <Icon icon="mdi:close" />
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-3 py-4">
        <span class="block px-3 pb-3 text-[10px] font-bold uppercase text-mute tracking-widest">MENU UTAMA</span>
        <router-link
          v-for="item in navItems"
          :key="item.path"
          :to="item.path"
          class="group relative flex items-center gap-3 px-3 py-2.5 rounded-sm text-on-dark-mute text-sm font-medium no-underline transition-all hover:bg-surface-elevated hover:text-on-dark"
          active-class="!bg-surface-elevated !text-on-dark"
          @click="sidebarOpen = false"
        >
          <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[3px] h-5 bg-primary rounded-r-sm opacity-0 group-[.router-link-active]:opacity-100 transition-opacity"></div>
          <Icon :icon="item.icon" class="text-lg w-6 text-center flex-shrink-0" />
          <span class="flex-1">{{ item.label }}</span>
          <span v-if="item.badge" class="bg-primary text-ink text-[11px] font-bold px-2 py-0.5 rounded-full leading-tight">{{ item.badge }}</span>
        </router-link>
      </nav>

      <!-- Sidebar footer -->
      <div class="flex items-center justify-between px-5 py-4 border-t border-hairline-strong">
        <div class="flex items-center gap-2.5 flex-1 min-w-0">
          <div class="w-9 h-9 rounded-full bg-primary text-ink flex items-center justify-center text-xs font-bold flex-shrink-0">{{ authStore.userInitials }}</div>
          <div class="flex flex-col min-w-0">
            <span class="text-xs font-semibold truncate">{{ authStore.userName }}</span>
            <span class="text-[11px] text-mute truncate">{{ authStore.user?.email }}</span>
          </div>
        </div>
        <button
          class="flex-shrink-0 w-8 h-8 flex items-center justify-center bg-transparent border border-hairline-strong rounded-sm text-on-dark-mute cursor-pointer hover:bg-error hover:border-error hover:text-on-dark transition-all"
          @click="handleLogout"
          title="Keluar"
        >
          <Icon icon="mdi:power" class="text-base" />
        </button>
      </div>
    </aside>

    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 bg-black/60 z-35 lg:hidden animate-fade-in"
      @click="sidebarOpen = false"
    ></div>

    <!-- Main content -->
    <div class="flex-1 flex flex-col min-h-screen ml-0 lg:ml-[260px] transition-[margin] duration-300">
      <!-- Topbar -->
      <header class="sticky top-0 z-30 flex items-center h-14 px-5 bg-canvas border-b border-hairline">
        <button
          class="lg:hidden flex items-center justify-center w-9 h-9 bg-transparent border border-hairline rounded-sm cursor-pointer mr-3 hover:bg-surface-soft transition-colors"
          @click="sidebarOpen = true"
        >
          <Icon icon="mdi:menu" class="text-lg" />
        </button>
        <span class="flex-1 text-sm font-bold text-ink">{{ currentPageTitle }}</span>
        <div class="relative">
          <button
            class="flex items-center gap-2 px-2.5 py-1.5 border border-hairline rounded-sm cursor-pointer hover:bg-surface-soft transition-colors"
            @click="showUserMenu = !showUserMenu"
          >
            <div class="w-7 h-7 rounded-full bg-primary text-ink flex items-center justify-center text-[11px] font-bold">{{ authStore.userInitials }}</div>
            <span class="hidden sm:block text-xs font-semibold text-ink">{{ authStore.userName }}</span>
            <Icon icon="mdi:chevron-down" class="text-xs text-mute" />
          </button>

          <!-- Dropdown -->
          <div v-if="showUserMenu" class="absolute top-full right-0 mt-2 w-60 bg-canvas border border-hairline rounded-sm overflow-hidden shadow-md z-50 animate-fade-in-up">
            <div class="px-4 py-3">
              <div class="text-sm font-bold text-ink">{{ authStore.userName }}</div>
              <div class="text-xs text-mute mt-0.5">{{ authStore.user?.email }}</div>
            </div>
            <div class="h-px bg-hairline"></div>
            <button
              class="w-full flex items-center gap-2.5 px-4 py-2.5 bg-transparent border-none text-sm text-body cursor-pointer hover:bg-surface-soft hover:text-error transition-colors text-left"
              @click="handleLogout"
            >
              <Icon icon="mdi:power" />
              <span>Keluar</span>
            </button>
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="flex-1 p-5 md:p-8">
        <router-view v-slot="{ Component }">
          <transition name="page" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import { Icon } from '@iconify/vue'
import { useAuthStore } from '@/stores/auth'
import { useDocumentStore } from '@/stores/document'

const authStore = useAuthStore()
const docStore = useDocumentStore()
const route = useRoute()

const sidebarOpen = ref(false)
const showUserMenu = ref(false)

const roleLabel = computed(() => authStore.isPenilai ? 'Panel Penilai' : 'Panel Pemohon')

const currentPageTitle = computed(() => {
  const map: Record<string, string> = {
    DashboardPemohon: 'Dashboard',
    SubmitDocument: 'Ajukan Dokumen',
    DashboardPenilai: 'Dashboard',
    ReviewDocument: 'Review Dokumen',
  }
  return map[route.name?.toString() ?? ''] ?? 'Dashboard'
})

const navItems = computed(() => {
  if (authStore.isPenilai) {
    return [
      { path: '/penilai/dashboard', label: 'Dashboard', icon: 'mdi:view-dashboard-outline', badge: null },
      { path: '/penilai/review', label: 'Review Dokumen', icon: 'mdi:file-search-outline', badge: docStore.pendingDocuments.length || null },
    ]
  }
  return [
    { path: '/pemohon/dashboard', label: 'Dashboard', icon: 'mdi:view-dashboard-outline', badge: null },
    { path: '/pemohon/submit', label: 'Ajukan Dokumen', icon: 'mdi:file-plus-outline', badge: null },
  ]
})

function handleLogout() {
  showUserMenu.value = false
  authStore.logout()
}

function handleClickOutside(e: Event) {
  if (!(e.target as HTMLElement).closest('.relative')) showUserMenu.value = false
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  docStore.fetchDocuments()
})
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
watch(() => route.path, () => { sidebarOpen.value = false })
</script>
