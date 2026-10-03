<template>
  <Card class="shadow-xl border">
    <CardHeader class="pb-4">
      <CardTitle class="text-2xl font-bold">Daftar Akun Baru</CardTitle>
      <CardDescription class="text-xs">Buat akun untuk mengajukan atau menilai dokumen kelayakan</CardDescription>
    </CardHeader>

    <CardContent class="space-y-4">
      <!-- Error Message -->
      <Alert v-if="authStore.error" variant="destructive" class="py-2.5">
        <AlertDescription class="text-xs">
          {{ authStore.error }}
        </AlertDescription>
      </Alert>

      <form @submit.prevent="handleRegister" class="space-y-4">
        <!-- Full Name -->
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-foreground" for="reg-name">Nama Lengkap</label>
          <div class="relative">
            <User class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
            <Input
              id="reg-name"
              v-model="form.name"
              placeholder="Masukkan nama lengkap"
              class="pl-9 text-sm"
              required
              autocomplete="name"
            />
          </div>
        </div>

        <!-- Email -->
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-foreground" for="reg-email">Email</label>
          <div class="relative">
            <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
            <Input
              id="reg-email"
              v-model="form.email"
              type="email"
              placeholder="nama@email.com"
              class="pl-9 text-sm"
              required
              autocomplete="email"
            />
          </div>
        </div>

        <!-- Role Selection -->
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-foreground">Pilih Peran Akun *</label>
          <div class="grid grid-cols-2 gap-3">
            <div
              @click="form.role = 'pemohon'"
              class="p-3.5 rounded-lg border-2 cursor-pointer transition-all flex flex-col items-center gap-1.5 text-center select-none"
              :class="form.role === 'pemohon' ? 'border-primary bg-primary/10 text-primary' : 'border-border hover:border-foreground/30'"
            >
              <FileEdit class="w-6 h-6" />
              <span class="text-xs font-bold">Pemohon</span>
              <span class="text-[10px] text-muted-foreground">Ajukan dokumen</span>
            </div>

            <div
              @click="form.role = 'penilai'"
              class="p-3.5 rounded-lg border-2 cursor-pointer transition-all flex flex-col items-center gap-1.5 text-center select-none"
              :class="form.role === 'penilai' ? 'border-primary bg-primary/10 text-primary' : 'border-border hover:border-foreground/30'"
            >
              <Shield class="w-6 h-6" />
              <span class="text-xs font-bold">Penilai</span>
              <span class="text-[10px] text-muted-foreground">Review dokumen</span>
            </div>
          </div>
        </div>

        <!-- Password -->
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-foreground" for="reg-password">Kata Sandi</label>
          <div class="relative">
            <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
            <Input
              id="reg-password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Minimal 8 karakter"
              class="pl-9 pr-9 text-sm"
              required
              autocomplete="new-password"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground focus:outline-none"
              @click="showPassword = !showPassword"
            >
              <EyeOff v-if="showPassword" class="w-4 h-4" />
              <Eye v-else class="w-4 h-4" />
            </button>
          </div>
        </div>

        <!-- Password Confirmation -->
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-foreground" for="reg-confirm">Konfirmasi Kata Sandi</label>
          <div class="relative">
            <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
            <Input
              id="reg-confirm"
              v-model="form.password_confirmation"
              :type="showConfirm ? 'text' : 'password'"
              placeholder="Ulangi kata sandi"
              class="pl-9 pr-9 text-sm"
              required
              autocomplete="new-password"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground focus:outline-none"
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
          class="w-full font-bold mt-2 gap-2"
          :disabled="passwordMismatch || authStore.loading"
        >
          <Loader2 v-if="authStore.loading" class="w-4 h-4 animate-spin" />
          <UserPlus v-else class="w-4 h-4" />
          <span>Daftar Sekarang</span>
        </Button>
      </form>
    </CardContent>

    <CardFooter class="flex items-center justify-center gap-1.5 pt-3 border-t text-xs text-muted-foreground">
      <span>Sudah memiliki akun?</span>
      <router-link to="/login" class="font-bold text-primary hover:underline">
        Masuk ke sistem →
      </router-link>
    </CardFooter>
  </Card>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { User, Mail, Lock, Eye, EyeOff, FileEdit, Shield, UserPlus, Loader2 } from 'lucide-vue-next'
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
