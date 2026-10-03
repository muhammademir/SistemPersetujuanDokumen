<template>
  <div class="flex min-h-screen bg-background">
    <!-- Sidebar -->
    <aside
      class="fixed top-0 left-0 bottom-0 z-40 w-[260px] bg-neutral-950 text-white flex flex-col transition-transform duration-300 border-r border-neutral-800"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
      <!-- Brand Header -->
      <div class="flex items-center gap-3 px-5 py-5 border-b border-neutral-800">
        <div class="w-8 h-8 rounded-lg bg-primary text-primary-foreground flex items-center justify-center shrink-0 font-bold">
          <FileCheck class="w-4 h-4" />
        </div>
        <div class="flex flex-col flex-1">
          <span class="text-base font-bold leading-tight tracking-tight">SiPerDok</span>
          <span class="text-[10px] font-bold uppercase text-primary tracking-wider mt-0.5">{{ roleLabel }}</span>
        </div>
        <Button
          variant="ghost"
          size="icon"
          class="lg:hidden text-neutral-400 hover:text-white hover:bg-neutral-800 w-8 h-8"
          @click="sidebarOpen = false"
        >
          <X class="w-4 h-4" />
        </Button>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-3 py-4 flex flex-col gap-1.5 overflow-y-auto">
        <span class="block px-3 pb-2 text-[10px] font-bold uppercase text-neutral-400 tracking-widest">MENU UTAMA</span>
        <router-link
          v-for="item in navItems"
          :key="item.path"
          :to="item.path"
          class="group relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-neutral-300 text-xs font-semibold no-underline transition-all hover:bg-neutral-850 hover:text-white"
          active-class="!bg-neutral-800 !text-white font-bold"
          @click="sidebarOpen = false"
        >
          <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-primary rounded-r-md opacity-0 group-[.router-link-active]:opacity-100 transition-opacity"></div>
          <component :is="item.icon" class="w-4 h-4 shrink-0 text-neutral-400 group-hover:text-white group-[.router-link-active]:text-primary" />
          <span class="flex-1">{{ item.label }}</span>
          <Badge
            v-if="item.badge"
            variant="secondary"
            class="text-[10px] bg-amber-500/20 text-amber-400 border-amber-500/30"
          >
            {{ item.badge }}
          </Badge>
        </router-link>
      </nav>

      <!-- Sidebar footer / user info -->
      <div class="flex items-center justify-between px-5 py-4 border-t border-neutral-800 bg-neutral-950">
        <div class="flex items-center gap-2.5 flex-1 min-w-0">
          <Avatar class="h-8 w-8 bg-primary text-primary-foreground font-bold text-xs">
            <AvatarFallback class="bg-primary text-primary-foreground font-bold text-xs">
              {{ authStore.userInitials }}
            </AvatarFallback>
          </Avatar>
          <div class="flex flex-col min-w-0">
            <span class="text-xs font-bold truncate text-white">{{ authStore.userName }}</span>
            <span class="text-[10px] text-neutral-400 truncate capitalize">{{ authStore.userRole }}</span>
          </div>
        </div>
        <Button
          variant="ghost"
          size="icon"
          class="text-neutral-400 hover:text-red-400 hover:bg-neutral-900 w-8 h-8"
          title="Keluar"
          @click="handleLogout"
        >
          <LogOut class="w-4 h-4" />
        </Button>
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
      <header class="sticky top-0 z-30 flex items-center justify-between h-14 px-6 bg-card border-b">
        <div class="flex items-center gap-3">
          <Button
            variant="ghost"
            size="icon"
            class="lg:hidden w-8 h-8"
            @click="sidebarOpen = true"
          >
            <Menu class="w-4 h-4" />
          </Button>
          <span class="text-sm font-bold text-foreground">{{ currentPageTitle }}</span>
        </div>

        <!-- User Profile Dropdown Button -->
        <DropdownMenu>
          <DropdownMenuTrigger as-child>
            <Button
              variant="ghost"
              size="sm"
              class="flex items-center gap-2 px-2 py-1.5 h-auto rounded-lg hover:bg-accent"
            >
              <Avatar class="h-7 w-7 bg-primary text-primary-foreground">
                <AvatarFallback class="bg-primary text-primary-foreground font-bold text-[11px]">
                  {{ authStore.userInitials }}
                </AvatarFallback>
              </Avatar>
              <span class="hidden sm:block text-xs font-semibold text-foreground">
                {{ authStore.userName }}
              </span>
              <ChevronDown class="w-3.5 h-3.5 text-muted-foreground" />
            </Button>
          </DropdownMenuTrigger>

          <DropdownMenuContent align="end" class="w-56">
            <DropdownMenuLabel class="font-normal p-3 bg-muted/40 rounded-t-md">
              <div class="text-xs font-bold text-foreground">{{ authStore.userName }}</div>
              <div class="text-[11px] text-muted-foreground truncate mt-0.5">{{ authStore.user?.email }}</div>
              <Badge variant="secondary" class="mt-1.5 text-[9px] uppercase font-bold">
                {{ authStore.userRole }}
              </Badge>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem
              class="text-xs font-semibold text-destructive cursor-pointer focus:text-destructive focus:bg-destructive/10"
              @click="handleLogout"
            >
              <LogOut class="w-4 h-4 mr-2" />
              <span>Keluar dari Akun</span>
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
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
import { Avatar, AvatarFallback } from '@/components/ui/avatar'
import { Badge } from '@/components/ui/badge'
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
} from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import { useDocumentStore } from '@/stores/document'

const authStore = useAuthStore()
const docStore = useDocumentStore()
const route = useRoute()

const sidebarOpen = ref(false)

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
      { path: '/penilai/dashboard', label: 'Dashboard Utama', icon: LayoutDashboard, badge: null },
      { path: '/penilai/review', label: 'Antrean Penilaian', icon: Inbox, badge: docStore.pendingApplications.length || null },
      { path: '/penilai/history', label: 'Riwayat Penilaian', icon: History, badge: null },
    ]
  }
  return [
    { path: '/pemohon/dashboard', label: 'Dashboard & Permohonan', icon: LayoutDashboard, badge: null },
    { path: '/pemohon/submit', label: 'Buat Permohonan Baru', icon: PlusCircle, badge: null },
  ]
})

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
