<template>
  <div class="w-full">
    <!-- Header Title -->
    <div>
      <h1 class="text-3xl font-extrabold text-neutral-900 tracking-tight">Daftar Akun</h1>
      <p class="text-sm text-neutral-500 mt-1.5">Buat akun permohonan atau penilaian dokumen</p>
    </div>

    <!-- Error Message Alert -->
    <Alert v-if="authStore.error" variant="destructive" class="mt-5 py-2.5">
      <AlertDescription class="text-xs">
        {{ authStore.error }}
      </AlertDescription>
    </Alert>

    <form @submit.prevent="handleRegister" class="mt-6 space-y-4">
      <!-- Full Name -->
      <div class="space-y-1.5">
        <label class="text-xs font-semibold text-neutral-700" for="reg-name">Nama Lengkap</label>
        <Input
          id="reg-name"
          v-model="form.name"
          placeholder="Masukkan nama lengkap"
          class="h-11 rounded-lg border-neutral-200 text-sm focus-visible:ring-1 focus-visible:ring-[#3b49f5] focus-visible:border-[#3b49f5]"
          required
          autocomplete="name"
        />
      </div>

      <!-- Email -->
      <div class="space-y-1.5">
        <label class="text-xs font-semibold text-neutral-700" for="reg-email">Email</label>
        <Input
          id="reg-email"
          v-model="form.email"
          type="email"
          placeholder="nama@email.com"
          class="h-11 rounded-lg border-neutral-200 text-sm focus-visible:ring-1 focus-visible:ring-[#3b49f5] focus-visible:border-[#3b49f5]"
          required
          autocomplete="email"
        />
      </div>

      <!-- Role Selection -->
      <div class="space-y-1.5">
        <label class="text-xs font-semibold text-neutral-700">Pilih Peran Akun *</label>
        <div class="grid grid-cols-2 gap-3">
          <div
            @click="form.role = 'pemohon'"
            class="p-3 rounded-lg border-2 cursor-pointer transition-all flex flex-col items-center gap-1 text-center select-none"
            :class="form.role === 'pemohon' ? 'border-[#3b49f5] bg-[#3b49f5]/5 text-[#3b49f5]' : 'border-neutral-200 hover:border-neutral-400 text-neutral-600'"
          >
            <FileEdit class="w-5 h-5" />
            <span class="text-xs font-bold">Pemohon</span>
            <span class="text-[10px] text-neutral-400">Ajukan dokumen</span>
          </div>

          <div
            @click="form.role = 'penilai'"
            class="p-3 rounded-lg border-2 cursor-pointer transition-all flex flex-col items-center gap-1 text-center select-none"
            :class="form.role === 'penilai' ? 'border-[#3b49f5] bg-[#3b49f5]/5 text-[#3b49f5]' : 'border-neutral-200 hover:border-neutral-400 text-neutral-600'"
          >
            <Shield class="w-5 h-5" />
            <span class="text-xs font-bold">Penilai</span>
            <span class="text-[10px] text-neutral-400">Review dokumen</span>
          </div>
        </div>
      </div>

      <!-- Password -->
      <div class="space-y-1.5">
        <label class="text-xs font-semibold text-neutral-700" for="reg-password">Kata Sandi</label>
        <div class="relative">
          <Input
            id="reg-password"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            placeholder="Minimal 8 karakter"
            class="h-11 pr-10 rounded-lg border-neutral-200 text-sm focus-visible:ring-1 focus-visible:ring-[#3b49f5] focus-visible:border-[#3b49f5]"
            required
            autocomplete="new-password"
          />
          <button
            type="button"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-700 focus:outline-none"
            @click="showPassword = !showPassword"
          >
            <EyeOff v-if="showPassword" class="w-4 h-4" />
            <Eye v-else class="w-4 h-4" />
          </button>
        </div>
      </div>

      <!-- Password Confirmation -->
      <div class="space-y-1.5">
        <label class="text-xs font-semibold text-neutral-700" for="reg-confirm">Konfirmasi Kata Sandi</label>
        <div class="relative">
          <Input
            id="reg-confirm"
            v-model="form.password_confirmation"
            :type="showConfirm ? 'text' : 'password'"
            placeholder="Ulangi kata sandi"
            class="h-11 pr-10 rounded-lg border-neutral-200 text-sm focus-visible:ring-1 focus-visible:ring-[#3b49f5] focus-visible:border-[#3b49f5]"
            required
            autocomplete="new-password"
          />
          <button
            type="button"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-700 focus:outline-none"
            @click="showConfirm = !showConfirm"
          >
            <EyeOff v-if="showConfirm" class="w-4 h-4" />
            <Eye v-else class="w-4 h-4" />
          </button>
        </div>
        <span v-if="passwordMismatch" class="text-xs text-destructive mt-0.5 block">
          Konfirmasi kata sandi tidak cocok
        </span>
      </div>

      <Button
        type="submit"
        class="w-full h-11 rounded-lg bg-[#3b49f5] hover:bg-[#2f3ce0] text-white font-semibold text-sm shadow-xs mt-3 gap-2 transition-colors cursor-pointer"
        :disabled="passwordMismatch || authStore.loading"
      >
        <Loader2 v-if="authStore.loading" class="w-4 h-4 animate-spin" />
        <span v-else>Daftar Sekarang</span>
      </Button>

      <div class="text-center text-xs text-neutral-500 pt-3">
        Sudah memiliki akun?
        <router-link to="/login" class="font-semibold text-[#3b49f5] hover:underline inline-flex items-center gap-0.5 ml-1">
          <span>Masuk ke sistem</span>
          <span class="text-xs">→</span>
        </router-link>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Eye, EyeOff, FileEdit, Shield, Loader2 } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import type { RegisterData } from '@/types'

const authStore = useAuthStore()
const showPassword = ref(false)
const showConfirm = ref(false)

const form = reactive<RegisterData>({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'pemohon',
})

const passwordMismatch = computed(() =>
  form.password.length > 0 &&
  form.password_confirmation.length > 0 &&
  form.password !== form.password_confirmation
)

async function handleRegister() {
  if (passwordMismatch.value) return
  try {
    await authStore.register(form)
  } catch {
    // Handled in store
  }
}
</script>
