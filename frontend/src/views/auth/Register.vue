<template>
  <Card class="shadow-xl border border-surface-200 dark:border-surface-700">
    <template #title>
      <div class="mb-1">
        <h1 class="text-2xl font-bold font-brand text-surface-900 dark:text-surface-0">Daftar Akun Baru</h1>
        <p class="text-xs text-surface-500 font-normal">Buat akun untuk mengajukan atau menilai dokumen kelayakan</p>
      </div>
    </template>

    <template #content>
      <!-- Error Message -->
      <Message v-if="authStore.error" severity="error" :closable="true" @close="authStore.clearError" class="mb-4 text-xs">
        {{ authStore.error }}
      </Message>

      <form @submit.prevent="handleRegister" class="space-y-4">
        <!-- Full Name -->
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-semibold text-surface-700 dark:text-surface-300" for="reg-name">Nama Lengkap</label>
          <IconField>
            <InputIcon class="pi pi-user" />
            <InputText
              id="reg-name"
              v-model="form.name"
              placeholder="Masukkan nama lengkap"
              class="w-full text-sm"
              required
              autocomplete="name"
            />
          </IconField>
        </div>

        <!-- Email -->
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-semibold text-surface-700 dark:text-surface-300" for="reg-email">Email</label>
          <IconField>
            <InputIcon class="pi pi-envelope" />
            <InputText
              id="reg-email"
              v-model="form.email"
              type="email"
              placeholder="nama@email.com"
              class="w-full text-sm"
              required
              autocomplete="email"
            />
          </IconField>
        </div>

        <!-- Role Selection -->
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-semibold text-surface-700 dark:text-surface-300">Pilih Peran Akun *</label>
          <div class="grid grid-cols-2 gap-3">
            <div
              @click="form.role = 'pemohon'"
              class="p-3.5 rounded-lg border-2 cursor-pointer transition-all flex flex-col items-center gap-1.5 text-center"
              :class="form.role === 'pemohon' ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300' : 'border-surface-200 dark:border-surface-700 hover:border-surface-400'"
            >
              <i class="pi pi-file-edit text-2xl"></i>
              <span class="text-xs font-bold">Pemohon</span>
              <span class="text-[10px] text-surface-500">Ajukan dokumen</span>
            </div>

            <div
              @click="form.role = 'penilai'"
              class="p-3.5 rounded-lg border-2 cursor-pointer transition-all flex flex-col items-center gap-1.5 text-center"
              :class="form.role === 'penilai' ? 'border-primary-500 bg-primary-50 dark:bg-primary-950/40 text-primary-700 dark:text-primary-300' : 'border-surface-200 dark:border-surface-700 hover:border-surface-400'"
            >
              <i class="pi pi-shield text-2xl"></i>
              <span class="text-xs font-bold">Penilai</span>
              <span class="text-[10px] text-surface-500">Review dokumen</span>
            </div>
          </div>
        </div>

        <!-- Password -->
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-semibold text-surface-700 dark:text-surface-300" for="reg-password">Kata Sandi</label>
          <Password
            id="reg-password"
            v-model="form.password"
            placeholder="Minimal 8 karakter"
            toggleMask
            class="w-full"
            inputClass="w-full text-sm"
            required
            autocomplete="new-password"
          />
        </div>

        <!-- Password Confirmation -->
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-semibold text-surface-700 dark:text-surface-300" for="reg-confirm">Konfirmasi Kata Sandi</label>
          <Password
            id="reg-confirm"
            v-model="form.password_confirmation"
            placeholder="Ulangi kata sandi"
            :feedback="false"
            toggleMask
            class="w-full"
            inputClass="w-full text-sm"
            required
            autocomplete="new-password"
          />
          <span v-if="passwordMismatch" class="text-xs text-red-500 mt-0.5">
            Konfirmasi kata sandi tidak cocok
          </span>
        </div>

        <Button
          type="submit"
          label="Daftar Sekarang"
          icon="pi pi-user-plus"
          class="w-full font-bold mt-2"
          :loading="authStore.loading"
          :disabled="passwordMismatch || authStore.loading"
        />
      </form>
    </template>

    <template #footer>
      <div class="flex items-center justify-center gap-1.5 pt-3 border-t border-surface-200 dark:border-surface-700 text-xs text-surface-500">
        <span>Sudah memiliki akun?</span>
        <router-link to="/login" class="font-bold text-primary-600 hover:text-primary-700">
          Masuk ke sistem →
        </router-link>
      </div>
    </template>
  </Card>
</template>

<script setup lang="ts">
import { reactive, computed } from 'vue'
import Card from 'primevue/card'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import Message from 'primevue/message'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import { useAuthStore } from '@/stores/auth'
import type { RegisterData } from '@/types'

const authStore = useAuthStore()

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
