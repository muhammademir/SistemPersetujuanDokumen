<template>
  <div class="flex min-h-screen bg-surface-50 dark:bg-surface-950">
    <!-- Sidebar -->
    <aside
      class="fixed top-0 left-0 bottom-0 z-40 w-[260px] bg-surface-900 text-surface-0 flex flex-col transition-transform duration-300 border-r border-surface-800"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <!-- Brand Header -->
      <div class="flex items-center gap-3 px-5 py-5 border-b border-surface-800">
        <div class="w-8 h-8 rounded-lg bg-primary-500 flex items-center justify-center flex-shrink-0 text-surface-900 font-bold">
          <i class="pi pi-file-check text-lg"></i>
        </div>
        <div class="flex flex-col flex-1">
          <span class="text-base font-bold leading-tight tracking-tight font-brand">SiPerDok</span>
          <span class="text-[10px] font-bold uppercase text-primary-400 tracking-wider mt-0.5">{{ roleLabel }}</span>
        </div>
        <Button
          icon="pi pi-times"
          severity="secondary"
          text
          rounded
          class="lg:hidden text-surface-400 hover:text-surface-0"
          @click="sidebarOpen = false"
        />
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-3 py-4 flex flex-col gap-1.5 overflow-y-auto">
        <span class="block px-3 pb-2 text-[10px] font-bold uppercase text-surface-400 tracking-widest">MENU UTAMA</span>
        <router-link
          v-for="item in navItems"
          :key="item.path"
          :to="item.path"
          class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-surface-300 text-xs font-semibold no-underline transition-all hover:bg-surface-800 hover:text-surface-0"
          active-class="!bg-surface-800 !text-surface-0 font-bold"
          @click="sidebarOpen = false"
        >
          <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-primary-500 rounded-r-md opacity-0 group-[.router-link-active]:opacity-100 transition-opacity"></div>
          <i :class="item.icon" class="text-sm w-5 text-center flex-shrink-0"></i>
          <span class="flex-1">{{ item.label }}</span>
          <Badge
            v-if="item.badge"
            :value="item.badge"
            severity="warn"
            size="small"
            class="text-[10px]"
          />
        </router-link>
      </nav>

      <!-- Sidebar footer / user info -->
      <div class="flex items-center justify-between px-5 py-4 border-t border-surface-800 bg-surface-900">
        <div class="flex items-center gap-2.5 flex-1 min-w-0">
          <Avatar
            :label="authStore.userInitials"
            shape="circle"
            class="bg-primary-500 text-surface-900 font-bold text-xs"
          />
          <div class="flex flex-col min-w-0">
            <span class="text-xs font-bold truncate text-surface-0">{{ authStore.userName }}</span>
            <span class="text-[10px] text-surface-400 truncate capitalize">{{ authStore.userRole }}</span>
          </div>
        </div>
        <Button
          icon="pi pi-power-off"
          severity="danger"
          text
          rounded
          size="small"
          v-tooltip.top="'Keluar'"
          @click="handleLogout"
        />
      </div>
    </aside>

    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 bg-black/60 z-35 lg:hidden animate-fade-in backdrop-blur-xs"
      @click="sidebarOpen = false"
    ></div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen ml-0 lg:ml-[260px] transition-[margin] duration-300">
      <!-- Topbar Header -->
      <header class="sticky top-0 z-30 flex items-center justify-between h-14 px-6 bg-surface-0 dark:bg-surface-900 border-b border-surface-200 dark:border-surface-800">
        <div class="flex items-center gap-3">
          <Button
            icon="pi pi-bars"
            severity="secondary"
            text
            rounded
            class="lg:hidden"
            @click="sidebarOpen = true"
          />
          <span class="text-sm font-bold text-surface-800 dark:text-surface-100">{{ currentPageTitle }}</span>
        </div>

        <!-- User Profile Dropdown Button -->
        <div class="flex items-center gap-2">
          <Button
            type="button"
            severity="secondary"
            text
            class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-surface-100 dark:hover:bg-surface-800"
            @click="toggleUserMenu"
            aria-haspopup="true"
            aria-controls="overlay_user_menu"
          >
            <Avatar
              :label="authStore.userInitials"
              shape="circle"
              size="normal"
              class="bg-primary-500 text-surface-900 font-bold text-xs"
            />
            <span class="hidden sm:block text-xs font-semibold text-surface-800 dark:text-surface-100">
              {{ authStore.userName }}
            </span>
            <i class="pi pi-chevron-down text-xs text-surface-400"></i>
          </Button>

          <Menu ref="userMenuRef" id="overlay_user_menu" :model="menuItems" :popup="true">
            <template #start>
              <div class="p-3 border-b border-surface-200 dark:border-surface-700 bg-surface-50 dark:bg-surface-800/60 rounded-t-lg">
                <div class="text-xs font-bold text-surface-900 dark:text-surface-0">{{ authStore.userName }}</div>
                <div class="text-[11px] text-surface-500 truncate mt-0.5">{{ authStore.user?.email }}</div>
                <Tag :value="authStore.userRole" severity="info" class="mt-1 text-[9px] uppercase font-bold" />
              </div>
            </template>
          </Menu>
        </div>
      </header>

      <!-- Main View Content -->
      <main class="flex-1 p-4 sm:p-6 lg:p-8">
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
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import Avatar from 'primevue/avatar'
import Badge from 'primevue/badge'
import Button from 'primevue/button'
import Menu from 'primevue/menu'
import Tag from 'primevue/tag'
import { useAuthStore } from '@/stores/auth'
import { useDocumentStore } from '@/stores/document'

const authStore = useAuthStore()
const docStore = useDocumentStore()
const route = useRoute()

const sidebarOpen = ref(false)
const userMenuRef = ref()

const roleLabel = computed(() => (authStore.isPenilai ? 'Panel Penilai' : 'Panel Pemohon'))

const currentPageTitle = computed(() => {
  const map: Record<string, string> = {
    DashboardPemohon: 'Dashboard Pemohon',
    SubmitDocument: 'Pengajuan Dokumen',
    DashboardPenilai: 'Dashboard Penilai',
    ReviewQueue: 'Antrean Penilaian Dokumen',
    ReviewHistory: 'Riwayat Hasil Penilaian',
  }
  return map[route.name?.toString() ?? ''] ?? 'Sistem Persetujuan Dokumen'
})

const navItems = computed(() => {
  if (authStore.isPenilai) {
    return [
      { path: '/penilai/dashboard', label: 'Dashboard Utama', icon: 'pi pi-th-large', badge: null },
      { path: '/penilai/review', label: 'Antrean Penilaian', icon: 'pi pi-inbox', badge: docStore.pendingApplications.length || null },
      { path: '/penilai/history', label: 'Riwayat Penilaian', icon: 'pi pi-history', badge: null },
    ]
  }
  return [
    { path: '/pemohon/dashboard', label: 'Dashboard & Permohonan', icon: 'pi pi-th-large', badge: null },
    { path: '/pemohon/submit', label: 'Buat Permohonan Baru', icon: 'pi pi-plus-circle', badge: null },
  ]
})

const menuItems = computed(() => [
  {
    separator: true,
  },
  {
    label: 'Keluar dari Akun',
    icon: 'pi pi-power-off',
    class: 'text-red-500 font-semibold text-xs',
    command: () => {
      handleLogout()
    },
  },
])

function toggleUserMenu(event: Event) {
  userMenuRef.value.toggle(event)
}

function handleLogout() {
  authStore.logout()
}

onMounted(() => {
  docStore.fetchDashboard()
  docStore.fetchApplications()
})

watch(() => route.path, () => {
  sidebarOpen.value = false
})
</script>
