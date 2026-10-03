<template>
  <div class="w-full">
    <!-- Header Title -->
    <div>
      <h1 class="text-3xl font-extrabold text-neutral-900 tracking-tight">Login</h1>
      <p class="text-sm text-neutral-500 mt-1.5">Hi, Welcome back 👋</p>
    </div>

    <!-- Error Message Alert -->
    <Alert v-if="authStore.error" variant="destructive" class="mt-5 py-2.5">
      <AlertDescription class="text-xs">
        {{ authStore.error }}
      </AlertDescription>
    </Alert>

    <!-- Form -->
    <form @submit.prevent="handleLogin" class="mt-6 space-y-4">
      <!-- Email Field -->
      <div class="space-y-1.5">
        <label class="text-xs font-semibold text-neutral-700" for="email">Email</label>
        <Input
          id="email"
          v-model="form.email"
          type="email"
          placeholder="E.g. johndoe@email.com"
          class="h-11 rounded-lg border-neutral-200 text-sm focus-visible:ring-1 focus-visible:ring-[#3b49f5] focus-visible:border-[#3b49f5]"
          required
          autocomplete="email"
        />
      </div>

      <!-- Password Field -->
      <div class="space-y-1.5">
        <label class="text-xs font-semibold text-neutral-700" for="password">Password</label>
        <div class="relative">
          <Input
            id="password"
            v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            placeholder="Enter your password"
            class="h-11 pr-10 rounded-lg border-neutral-200 text-sm focus-visible:ring-1 focus-visible:ring-[#3b49f5] focus-visible:border-[#3b49f5]"
            required
            autocomplete="current-password"
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

      <!-- Remember Me & Forgot Password -->
      <div class="flex items-center justify-between pt-1">
        <div class="flex items-center gap-2">
          <Checkbox id="remember" v-model="rememberMe" />
          <label for="remember" class="text-xs font-medium text-neutral-600 cursor-pointer select-none">
            Remember Me
          </label>
        </div>
        <a
          href="#"
          @click.prevent="handleForgotPassword"
          class="text-xs font-semibold text-[#3b49f5] hover:underline"
        >
          Forgot Password?
        </a>
      </div>

      <!-- Submit Button -->
      <Button
        type="submit"
        class="w-full h-11 rounded-lg bg-[#3b49f5] hover:bg-[#2f3ce0] text-white font-semibold text-sm shadow-xs mt-3 gap-2 transition-colors cursor-pointer"
        :disabled="authStore.loading"
      >
        <Loader2 v-if="authStore.loading" class="w-4 h-4 animate-spin" />
        <span v-else>Login</span>
      </Button>

      <!-- Footer Link -->
      <div class="text-center text-xs text-neutral-500 pt-3">
        Not registered yet?
        <router-link to="/register" class="font-semibold text-[#3b49f5] hover:underline inline-flex items-center gap-0.5 ml-1">
          <span>Create an account</span>
          <span class="text-xs">↗</span>
        </router-link>
      </div>

      <!-- Demo Accounts Helper (Subtle) -->
      <div class="mt-8 pt-5 border-t border-dashed border-neutral-200 text-center">
        <span class="text-[11px] font-semibold text-neutral-400 uppercase tracking-wider block mb-2.5">
          Akun Demo Cepat
        </span>
        <div class="flex items-center justify-center gap-2">
          <Button
            type="button"
            variant="outline"
            size="sm"
            class="h-8 text-xs font-medium text-neutral-600 rounded-md border-neutral-200 hover:bg-neutral-50"
            @click="fillDemo('pemohon')"
          >
            Demo Pemohon
          </Button>
          <Button
            type="button"
            variant="outline"
            size="sm"
            class="h-8 text-xs font-medium text-neutral-600 rounded-md border-neutral-200 hover:bg-neutral-50"
            @click="fillDemo('penilai')"
          >
            Demo Penilai
          </Button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Eye, EyeOff, Loader2 } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import type { LoginCredentials } from '@/types'

const authStore = useAuthStore()

const showPassword = ref(false)
const rememberMe = ref(false)

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

function handleForgotPassword() {
  alert('Silakan hubungi administrator sistem untuk reset kata sandi akun Anda.')
}

async function handleLogin() {
  try {
    await authStore.login(form)
  } catch {
    // Error is handled in authStore
  }
}
</script>
