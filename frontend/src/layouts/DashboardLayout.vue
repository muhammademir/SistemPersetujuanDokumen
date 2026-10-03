<template>
  <div class="flex min-h-screen bg-[#f8f9fb]">
    <!-- Sidebar -->
    <aside
      class="fixed top-0 left-0 bottom-0 z-40 w-[240px] bg-white flex flex-col transition-transform duration-300 border-r border-gray-200"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <!-- Brand Header -->
      <div class="flex items-center gap-2.5 px-5 h-[60px] border-b border-gray-100">
        <div class="w-8 h-8 rounded-lg bg-[#3b49f5] text-white flex items-center justify-center shrink-0">
          <FileCheck class="w-4 h-4" />
        </div>
        <span class="text-[13px] font-bold text-gray-900 tracking-tight leading-tight">Sistem Persetujuan Dokumen</span>
        <Button
          variant="ghost"
          size="icon"
          class="lg:hidden ml-auto text-gray-400 hover:text-gray-700 hover:bg-gray-100 w-7 h-7"
          @click="sidebarOpen = false"
        >
          <X class="w-4 h-4" />
        </Button>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-3 py-4 flex flex-col gap-0.5 overflow-y-auto">
        <router-link
          v-for="item in navItems"
          :key="item.path"
          :to="item.path"
          class="group flex items-center gap-3 px-3 py-2.5 rounded-lg text-[13px] font-medium text-gray-600 no-underline transition-all duration-150 hover:bg-gray-50 hover:text-gray-900"
          active-class="!bg-[#eef0ff] !text-[#3b49f5] !font-semibold"
          @click="sidebarOpen = false"
        >
          <component
            :is="item.icon"
            class="w-[18px] h-[18px] shrink-0 text-gray-400 group-hover:text-gray-600 group-[.router-link-active]:text-[#3b49f5]"
          />
          <span class="flex-1">{{ item.label }}</span>
          <span
            v-if="item.badge"
            class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-[10px] font-bold bg-red-500 text-white"
          >
            {{ item.badge }}
          </span>
        </router-link>
      </nav>

      <!-- Sidebar Footer -->
      <div class="px-4 py-3 border-t border-gray-100">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-full bg-[#3b49f5] text-white flex items-center justify-center text-[11px] font-bold shrink-0">
            {{ authStore.userInitials }}
          </div>
          <div class="flex flex-col min-w-0 flex-1">
            <span class="text-[13px] font-semibold text-gray-900 truncate">{{ authStore.userName }}</span>
            <span class="text-[11px] text-gray-400 truncate capitalize">{{ authStore.userRole }}</span>
          </div>
          <Button
            variant="ghost"
            size="icon"
            class="text-gray-400 hover:text-red-500 hover:bg-red-50 w-7 h-7 shrink-0"
            title="Keluar"
            @click="handleLogout"
          >
            <LogOut class="w-4 h-4" />
          </Button>
        </div>
      </div>
    </aside>

    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 bg-black/40 z-35 lg:hidden animate-fade-in backdrop-blur-[2px]"
      @click="sidebarOpen = false"
    ></div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen ml-0 lg:ml-[240px] transition-[margin] duration-300">
      <!-- Topbar Header -->
      <header class="sticky top-0 z-30 flex items-center justify-between h-[60px] px-5 sm:px-6 bg-white border-b border-gray-200">
        <div class="flex items-center gap-3">
          <Button
            variant="ghost"
            size="icon"
            class="lg:hidden w-8 h-8 text-gray-500 hover:text-gray-700 hover:bg-gray-100"
            @click="sidebarOpen = true"
          >
            <Menu class="w-5 h-5" />
          </Button>
          <div class="hidden lg:block">
            <h1 class="text-[15px] font-bold text-gray-900 leading-tight">{{ currentPageTitle }}</h1>
            <p class="text-[11px] text-gray-400 font-medium mt-0.5">{{ currentPageDescription }}</p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <!-- Notification Bell -->
          <Button
            variant="ghost"
            size="icon"
            class="relative w-9 h-9 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg"
          >
            <Bell class="w-[18px] h-[18px]" />
            <span
              v-if="notificationCount > 0"
              class="absolute top-1 right-1 w-2 h-2 rounded-full bg-red-500"
            ></span>
          </Button>

          <!-- User Profile Dropdown -->
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button
                variant="ghost"
                size="sm"
                class="flex items-center gap-2 px-2 py-1.5 h-auto rounded-lg hover:bg-gray-50"
              >
                <div class="w-8 h-8 rounded-full bg-[#3b49f5] text-white flex items-center justify-center text-[11px] font-bold">
                  {{ authStore.userInitials }}
                </div>
                <div class="hidden sm:flex flex-col items-start">
                  <span class="text-[13px] font-semibold text-gray-900 leading-tight">
                    {{ authStore.userName }}
                  </span>
                  <span class="text-[11px] text-gray-400 capitalize leading-tight">
                    {{ authStore.userRole }}
                  </span>
                </div>
                <ChevronDown class="w-3.5 h-3.5 text-gray-400 hidden sm:block" />
              </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent align="end" class="w-56">
              <DropdownMenuLabel class="font-normal p-3">
                <div class="text-sm font-semibold text-gray-900">{{ authStore.userName }}</div>
                <div class="text-xs text-gray-500 truncate mt-0.5">{{ authStore.user?.email }}</div>
              </DropdownMenuLabel>
              <DropdownMenuSeparator />
              <DropdownMenuItem
                class="text-xs font-medium text-red-600 cursor-pointer focus:text-red-600 focus:bg-red-50"
                @click="handleLogout"
              >
                <LogOut class="w-4 h-4 mr-2" />
                <span>Keluar dari Akun</span>
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </header>

      <!-- Main View Content -->
      <main class="flex-1 p-4 sm:p-6">
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
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu'
import {
  FileCheck,
  X,
  Menu,
  ChevronDown,
  LogOut,
  LayoutDashboard,
  Inbox,
  History,
  PlusCircle,
  Bell,
} from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import { useDocumentStore } from '@/stores/document'

const authStore = useAuthStore()
const docStore = useDocumentStore()
const route = useRoute()

const sidebarOpen = ref(false)

const currentPageTitle = computed(() => {
  const map: Record<string, string> = {
    DashboardPemohon: 'Dashboard',
    SubmitDocument: 'Pengajuan Dokumen',
    DashboardPenilai: 'Dashboard',
    ReviewQueue: 'Antrean Penilaian',
    ReviewHistory: 'Riwayat Penilaian',
  }
  return map[route.name?.toString() ?? ''] ?? 'Sistem Persetujuan Dokumen'
})

const currentPageDescription = computed(() => {
  const map: Record<string, string> = {
    DashboardPemohon: 'Pantau dan kelola permohonan dokumen Anda',
    SubmitDocument: 'Buat permohonan dokumen baru',
    DashboardPenilai: 'Tinjau dan verifikasi permohonan dari semua pemohon',
    ReviewQueue: 'Daftar permohonan yang menunggu penilaian',
    ReviewHistory: 'Riwayat penilaian yang telah diselesaikan',
  }
  return map[route.name?.toString() ?? ''] ?? ''
})

const notificationCount = computed(() => {
  if (authStore.isPenilai) {
    return docStore.pendingApplications.length
  }
  return 0
})

const navItems = computed(() => {
  if (authStore.isPenilai) {
    return [
      { path: '/penilai/dashboard', label: 'Dashboard', icon: LayoutDashboard, badge: null },
      {
        path: '/penilai/review',
        label: 'Antrean Penilaian',
        icon: Inbox,
        badge: docStore.pendingApplications.length || null,
      },
      { path: '/penilai/history', label: 'Riwayat Penilaian', icon: History, badge: null },
    ]
  }
  return [
    { path: '/pemohon/dashboard', label: 'Dashboard', icon: LayoutDashboard, badge: null },
    { path: '/pemohon/submit', label: 'Buat Permohonan', icon: PlusCircle, badge: null },
  ]
})

function handleLogout() {
  authStore.logout()
}

onMounted(() => {
  docStore.fetchDashboard()
  docStore.fetchApplications()
})

watch(
  () => route.path,
  () => {
    sidebarOpen.value = false
  }
)
</script>
