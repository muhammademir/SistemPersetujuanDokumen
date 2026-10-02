<template>
  <div class="animate-fade-in-up">
    <div class="mb-7">
      <h1 class="font-brand text-3xl font-bold text-ink mb-2">Daftar Akun</h1>
      <p class="text-sm text-mute">Buat akun baru untuk menggunakan sistem</p>
    </div>

    <!-- Error alert -->
    <div v-if="authStore.error" class="flex items-center gap-2.5 px-4 py-3 bg-error/10 border border-error/20 rounded-sm mb-6 text-sm text-error animate-fade-in">
      <Icon icon="mdi:alert-circle-outline" class="text-base flex-shrink-0" />
      <span class="flex-1">{{ authStore.error }}</span>
      <button class="bg-transparent border-none text-error cursor-pointer text-sm p-1 flex-shrink-0" @click="authStore.clearError">
        <Icon icon="mdi:close" />
      </button>
    </div>

    <form @submit.prevent="handleRegister" class="flex flex-col gap-4">
      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-bold text-ink" for="reg-name">Nama Lengkap</label>
        <input id="reg-name" v-model="form.name" type="text"
          class="w-full h-11 px-4 bg-canvas border border-hairline rounded-sm font-brand text-base text-ink outline-none transition-colors focus:border-primary focus:border-2 focus:px-[15px] placeholder:text-ash"
          placeholder="Masukkan nama lengkap" required autocomplete="name" />
      </div>

      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-bold text-ink" for="reg-email">Email</label>
        <input id="reg-email" v-model="form.email" type="email"
          class="w-full h-11 px-4 bg-canvas border border-hairline rounded-sm font-brand text-base text-ink outline-none transition-colors focus:border-primary focus:border-2 focus:px-[15px] placeholder:text-ash"
          placeholder="nama@email.com" required autocomplete="email" />
      </div>

      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-bold text-ink">Peran</label>
        <div class="grid grid-cols-2 gap-3">
          <button type="button" @click="form.role = 'pemohon'"
            class="flex flex-col items-center gap-1 px-3 py-4 bg-transparent border rounded-sm cursor-pointer transition-all"
            :class="form.role === 'pemohon' ? 'border-primary border-2 bg-primary/5 px-[11px] py-[15px]' : 'border-hairline hover:border-primary'">
            <Icon icon="mdi:file-document-edit-outline" class="text-2xl" />
            <span class="text-sm font-bold text-ink">Pemohon</span>
            <span class="text-[11px] text-mute">Ajukan dokumen</span>
          </button>
          <button type="button" @click="form.role = 'penilai'"
            class="flex flex-col items-center gap-1 px-3 py-4 bg-transparent border rounded-sm cursor-pointer transition-all"
            :class="form.role === 'penilai' ? 'border-primary border-2 bg-primary/5 px-[11px] py-[15px]' : 'border-hairline hover:border-primary'">
            <Icon icon="mdi:check-decagram" class="text-2xl" />
            <span class="text-sm font-bold text-ink">Penilai</span>
            <span class="text-[11px] text-mute">Review dokumen</span>
          </button>
        </div>
      </div>

      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-bold text-ink" for="reg-password">Password</label>
        <div class="relative">
          <input id="reg-password" v-model="form.password" :type="showPassword ? 'text' : 'password'"
            class="w-full h-11 px-4 pr-11 bg-canvas border border-hairline rounded-sm font-brand text-base text-ink outline-none transition-colors focus:border-primary focus:border-2 focus:px-[15px] focus:pr-[43px] placeholder:text-ash"
            placeholder="Minimal 8 karakter" required minlength="8" autocomplete="new-password" />
          <button type="button" tabindex="-1" @click="showPassword = !showPassword"
            class="absolute right-2 top-1/2 -translate-y-1/2 bg-transparent border-none cursor-pointer p-1.5 text-mute hover:text-ink transition-colors">
            <Icon :icon="showPassword ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" class="text-lg" />
          </button>
        </div>
      </div>

      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-bold text-ink" for="reg-confirm">Konfirmasi Password</label>
        <input id="reg-confirm" v-model="form.password_confirmation" :type="showPassword ? 'text' : 'password'"
          class="w-full h-11 px-4 bg-canvas border border-hairline rounded-sm font-brand text-base text-ink outline-none transition-colors focus:border-primary focus:border-2 focus:px-[15px] placeholder:text-ash"
          placeholder="Ulangi password" required autocomplete="new-password" />
        <span v-if="passwordMismatch" class="text-xs text-error">Password tidak cocok</span>
      </div>

      <button type="submit"
        class="flex items-center justify-center gap-2 w-full h-11 mt-1 bg-primary text-ink border-none rounded-sm font-brand text-base font-bold cursor-pointer transition-colors hover:bg-primary-dark disabled:bg-surface-soft disabled:text-ash disabled:cursor-not-allowed"
        :disabled="authStore.loading || passwordMismatch">
        <span v-if="authStore.loading" class="w-4 h-4 border-2 border-transparent border-t-current rounded-full animate-spin"></span>
        <span>{{ authStore.loading ? 'Memproses...' : 'Daftar' }}</span>
      </button>
    </form>

    <div class="flex items-center justify-center gap-1.5 mt-7 pt-5 border-t border-hairline">
      <span class="text-sm text-mute">Sudah punya akun?</span>
      <router-link to="/login" class="text-sm font-bold text-primary no-underline hover:text-primary-dark transition-colors">Masuk →</router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { Icon } from '@iconify/vue'
import { useAuthStore } from '@/stores/auth'
import type { RegisterData } from '@/types'

const authStore = useAuthStore()
const showPassword = ref(false)

const form = reactive<RegisterData>({
  name: '', email: '', password: '', password_confirmation: '', role: 'pemohon',
})

const passwordMismatch = computed(() =>
  form.password.length > 0 && form.password_confirmation.length > 0 && form.password !== form.password_confirmation
)

async function handleRegister() {
  if (passwordMismatch.value) return
  try { await authStore.register(form) } catch { /* handled */ }
}
</script>
