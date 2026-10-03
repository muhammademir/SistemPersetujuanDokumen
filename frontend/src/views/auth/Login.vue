<template>
  <Card class="shadow-xl border">
    <CardHeader class="pb-4">
      <CardTitle class="text-2xl font-bold">Masuk ke Akun</CardTitle>
      <CardDescription class="text-xs">Silakan masuk dengan kredensial terdaftar</CardDescription>
    </CardHeader>

    <CardContent class="space-y-4">
      <!-- Error Message -->
      <Alert v-if="authStore.error" variant="destructive" class="py-2.5">
        <AlertDescription class="text-xs">
          {{ authStore.error }}
        </AlertDescription>
      </Alert>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-foreground" for="email">Email</label>
          <div class="relative">
            <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
            <Input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="nama@email.com"
              class="pl-9 text-sm"
              required
              autocomplete="email"
            />
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-foreground" for="password">Kata Sandi</label>
          <div class="relative">
            <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
            <Input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Masukkan kata sandi"
              class="pl-9 pr-9 text-sm"
              required
              autocomplete="current-password"
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

        <Button
          type="submit"
          class="w-full font-bold mt-2 gap-2"
          :disabled="authStore.loading"
        >
          <Loader2 v-if="authStore.loading" class="w-4 h-4 animate-spin" />
          <LogIn v-else class="w-4 h-4" />
          <span>Masuk ke Sistem</span>
        </Button>

        <!-- Demo Accounts Section -->
        <div class="mt-4 p-3 bg-muted/40 rounded-lg border">
          <div class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground text-center mb-2">
            Akun Percobaan (Demo Cepat)
          </div>
          <div class="grid grid-cols-2 gap-2">
            <Button
              type="button"
              variant="outline"
              size="sm"
              class="text-xs font-semibold gap-1.5"
              @click="fillDemo('pemohon')"
            >
              <User class="w-3.5 h-3.5 text-primary" />
              <span>Demo Pemohon</span>
            </Button>
            <Button
              type="button"
              variant="outline"
              size="sm"
              class="text-xs font-semibold gap-1.5"
              @click="fillDemo('penilai')"
            >
              <Shield class="w-3.5 h-3.5 text-primary" />
              <span>Demo Penilai</span>
            </Button>
          </div>
        </div>
      </form>
    </CardContent>

    <CardFooter class="flex items-center justify-center gap-1.5 pt-3 border-t text-xs text-muted-foreground">
      <span>Belum memiliki akun?</span>
      <router-link to="/register" class="font-bold text-primary hover:underline">
        Daftar akun baru →
      </router-link>
    </CardFooter>
  </Card>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Mail, Lock, Eye, EyeOff, LogIn, User, Shield, Loader2 } from 'lucide-vue-next'
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
  } catch {
    // Handled in authStore
  }
}
</script>
