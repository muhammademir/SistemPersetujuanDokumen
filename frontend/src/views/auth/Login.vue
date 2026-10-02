<template>
  <div class="animate-fade-in-up">
    <div class="mb-8">
      <h1 class="font-brand text-3xl font-bold text-ink mb-2">Masuk</h1>
      <p class="text-sm text-mute">Silakan masuk ke akun Anda untuk melanjutkan</p>
    </div>

    <!-- Error alert -->
    <div v-if="authStore.error" class="flex items-center gap-2.5 px-4 py-3 bg-error/10 border border-error/20 rounded-sm mb-6 text-sm text-error animate-fade-in">
      <Icon icon="mdi:alert-circle-outline" class="text-base flex-shrink-0" />
      <span class="flex-1">{{ authStore.error }}</span>
      <button class="bg-transparent border-none text-error cursor-pointer text-sm p-1 flex-shrink-0" @click="authStore.clearError">
        <Icon icon="mdi:close" />
      </button>
    </div>

    <form @submit.prevent="handleLogin" class="flex flex-col gap-5">
      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-bold text-ink" for="login-email">Email</label>
        <input
          id="login-email"
          v-model="form.email"
          type="email"
          class="w-full h-11 px-4 bg-canvas border border-hairline rounded-sm font-brand text-base text-ink outline-none transition-colors focus:border-primary focus:border-2 focus:px-[15px] placeholder:text-ash"
          placeholder="nama@email.com"
          required
          autocomplete="email"
        />
      </div>

      <div class="flex flex-col gap-1.5">
        <label class="text-sm font-bold text-ink" for="login-password">Password</label>
        <div class="relative">
          <input
            id="login-password"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            class="w-full h-11 px-4 pr-11 bg-canvas border border-hairline rounded-sm font-brand text-base text-ink outline-none transition-colors focus:border-primary focus:border-2 focus:px-[15px] focus:pr-[43px] placeholder:text-ash"
            placeholder="Masukkan password"
            required
            autocomplete="current-password"
          />
          <button
            type="button"
            class="absolute right-2 top-1/2 -translate-y-1/2 bg-transparent border-none cursor-pointer p-1.5 text-mute hover:text-ink transition-colors"
            @click="showPassword = !showPassword"
            tabindex="-1"
          >
            <Icon :icon="showPassword ? 'mdi:eye-off-outline' : 'mdi:eye-outline'" class="text-lg" />
          </button>
        </div>
      </div>

      <button
        type="submit"
        class="flex items-center justify-center gap-2 w-full h-11 mt-1 bg-primary text-ink border-none rounded-sm font-brand text-base font-bold cursor-pointer transition-colors hover:bg-primary-dark disabled:bg-surface-soft disabled:text-ash disabled:cursor-not-allowed"
        :disabled="authStore.loading"
      >
        <span v-if="authStore.loading" class="w-4 h-4 border-2 border-transparent border-t-current rounded-full animate-spin"></span>
        <span>{{ authStore.loading ? 'Memproses...' : 'Masuk' }}</span>
      </button>

      <!-- Quick Demo Login Buttons -->
      <div class="mt-2 p-3.5 bg-surface-soft/80 border border-hairline rounded-sm flex flex-col gap-2">
        <span class="text-[11px] font-bold uppercase tracking-wider text-mute text-center">Akun Demo Cepat</span>
        <div class="grid grid-cols-2 gap-2">
          <button
            type="button"
            @click="fillDemo('pemohon')"
            class="px-3 py-2 bg-canvas border border-hairline rounded-sm text-xs font-bold text-ink hover:border-primary hover:text-primary transition-all cursor-pointer flex items-center justify-center gap-1.5"
          >
            <Icon icon="mdi:account" class="text-sm" /> Demo Pemohon
          </button>
          <button
            type="button"
            @click="fillDemo('penilai')"
            class="px-3 py-2 bg-canvas border border-hairline rounded-sm text-xs font-bold text-ink hover:border-primary hover:text-primary transition-all cursor-pointer flex items-center justify-center gap-1.5"
          >
            <Icon icon="mdi:shield-account" class="text-sm" /> Demo Penilai
          </button>
        </div>
      </div>
    </form>

    <div class="flex items-center justify-center gap-1.5 mt-8 pt-6 border-t border-hairline">
      <span class="text-sm text-mute">Belum punya akun?</span>
      <router-link to="/register" class="text-sm font-bold text-primary no-underline hover:text-primary-dark transition-colors">
        Daftar sekarang →
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { Icon } from '@iconify/vue'
import { useAuthStore } from '@/stores/auth'
import type { LoginCredentials } from '@/types'

const authStore = useAuthStore()
const showPassword = ref(false)

const form = reactive<LoginCredentials>({
  email: '',
  password: '',
})

function fillDemo(role: 'pemohon' | 'penilai') {
  if (role === 'pemohon') {
    form.email = 'pemohon@demo.test'
    form.password = 'password'
  } else {
    form.email = 'penilai@demo.test'
    form.password = 'password'
  }
}

async function handleLogin() {
  try {
    await authStore.login(form)
  } catch { /* handled by store */ }
}
</script>
