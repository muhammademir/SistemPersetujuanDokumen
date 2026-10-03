<template>
  <Card class="shadow-xl border border-surface-200 dark:border-surface-700">
    <template #title>
      <div class="mb-1">
        <h1 class="text-2xl font-bold font-brand text-surface-900 dark:text-surface-0">Masuk ke Akun</h1>
        <p class="text-xs text-surface-500 font-normal">Silakan masuk dengan kredensial terdaftar</p>
      </div>
    </template>

    <template #content>
      <!-- Error Message -->
      <Message v-if="authStore.error" severity="error" :closable="true" @close="authStore.clearError" class="mb-4 text-xs">
        {{ authStore.error }}
      </Message>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-semibold text-surface-700 dark:text-surface-300" for="email">Email</label>
          <IconField>
            <InputIcon class="pi pi-envelope" />
            <InputText
              id="email"
              v-model="form.email"
              type="email"
              placeholder="nama@email.com"
              class="w-full text-sm"
              required
              autocomplete="email"
            />
          </IconField>
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-semibold text-surface-700 dark:text-surface-300" for="password">Kata Sandi</label>
          <Password
            id="password"
            v-model="form.password"
            placeholder="Masukkan kata sandi"
            :feedback="false"
            toggleMask
            class="w-full"
            inputClass="w-full text-sm"
            required
            autocomplete="current-password"
          />
        </div>

        <Button
          type="submit"
          label="Masuk ke Sistem"
          icon="pi pi-sign-in"
          class="w-full font-bold mt-2"
          :loading="authStore.loading"
        />

        <!-- Demo Accounts Section -->
        <div class="mt-4 p-3 bg-surface-50 dark:bg-surface-800/60 rounded-lg border border-surface-200 dark:border-surface-700">
          <div class="text-[10px] font-bold uppercase tracking-wider text-surface-500 text-center mb-2">
            Akun Percobaan (Demo Cepat)
          </div>
          <div class="grid grid-cols-2 gap-2">
            <Button
              type="button"
              label="Demo Pemohon"
              icon="pi pi-user"
              severity="secondary"
              outlined
              size="small"
              class="text-xs font-semibold"
              @click="fillDemo('pemohon')"
            />
            <Button
              type="button"
              label="Demo Penilai"
              icon="pi pi-shield"
              severity="secondary"
              outlined
              size="small"
              class="text-xs font-semibold"
              @click="fillDemo('penilai')"
            />
          </div>
        </div>
      </form>
    </template>

    <template #footer>
      <div class="flex items-center justify-center gap-1.5 pt-3 border-t border-surface-200 dark:border-surface-700 text-xs text-surface-500">
        <span>Belum memiliki akun?</span>
        <router-link to="/register" class="font-bold text-primary-600 hover:text-primary-700">
          Daftar akun baru →
        </router-link>
      </div>
    </template>
  </Card>
</template>

<script setup lang="ts">
import { reactive } from 'vue'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Message from 'primevue/message'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import { useAuthStore } from '@/stores/auth'
import type { LoginCredentials } from '@/types'

const authStore = useAuthStore()

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
  } catch {
    // Handled in authStore
  }
}
</script>
