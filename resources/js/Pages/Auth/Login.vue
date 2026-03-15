<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, Sonner } from '@/Components/ui'
import ActionButton from '@/Components/ActionButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed, nextTick } from 'vue'
import { Eye, EyeOff, LogIn, Mail, Lock, Hash, Shield, ArrowLeft, Delete } from 'lucide-vue-next'

defineProps({
  canResetPassword: Boolean,
  status: String,
})

// Mode: 'pin' (default) or 'admin'
const authMode = ref('pin')

// PIN form
const pinForm = useForm({
  pin: '',
})

// Admin form
const adminForm = useForm({
  email: '',
  password: '',
  remember: false,
})

const showPassword = ref(false)

// PIN digits display
const pinDigits = computed(() => {
  const digits = pinForm.pin.split('')
  return Array.from({ length: 6 }, (_, i) => digits[i] || '')
})

// Numpad layout
const numpadKeys = [
  ['1', '2', '3'],
  ['4', '5', '6'],
  ['7', '8', '9'],
  ['clear', '0', 'delete'],
]

const handleNumpadPress = (key) => {
  if (key === 'clear') {
    pinForm.pin = ''
    return
  }
  if (key === 'delete') {
    pinForm.pin = pinForm.pin.slice(0, -1)
    return
  }
  if (pinForm.pin.length < 6) {
    pinForm.pin += key
  }
  // Auto-submit when 6 digits entered
  if (pinForm.pin.length === 6) {
    nextTick(() => submitPin())
  }
}

const submitPin = () => {
  pinForm.post(route('login'), {
    onFinish: () => pinForm.reset('pin'),
  })
}

const submitAdmin = () => {
  adminForm.post(route('login'), {
    onFinish: () => adminForm.reset('password'),
  })
}

const switchMode = (mode) => {
  authMode.value = mode
  pinForm.clearErrors()
  adminForm.clearErrors()
  pinForm.reset()
  adminForm.reset()
}
</script>

<template>
  <GuestLayout>
    <Head title="Log in" />
    <Sonner />

    <!-- Animated Background -->
    <div class="fixed inset-0 -z-10 overflow-hidden bg-gradient-to-br from-emerald-100 via-white to-orange-100">
      <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl animate-float" />
      <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-orange-500/10 rounded-full blur-3xl animate-float" style="animation-delay: 1.5s" />
      <div class="absolute top-1/2 left-1/2 w-48 h-48 bg-emerald-400/10 rounded-full blur-2xl animate-float" style="animation-delay: 0.75s" />
      <div class="absolute inset-0 bg-pattern-dots opacity-50" />
    </div>

    <!-- Login Card -->
    <div class="min-h-screen flex items-center justify-center p-4">
      <div class="w-full max-w-md">

        <!-- ============================================= -->
        <!-- PIN MODE (Default - Employee) -->
        <!-- ============================================= -->
        <Card v-if="authMode === 'pin'" class="glass border-white/10 animate-fade-in">
          <CardHeader class="text-center">
            <div class="mx-auto w-16 h-16 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-emerald-500/20">
              <Hash class="w-7 h-7 text-white" />
            </div>
            <CardTitle class="text-2xl text-slate-800">Masuk dengan PIN</CardTitle>
            <CardDescription class="text-slate-500">
              Masukkan 6-digit PIN karyawan Anda
            </CardDescription>
          </CardHeader>

          <CardContent class="space-y-6">
            <!-- Status Message -->
            <div v-if="status" class="text-sm font-medium text-emerald-600 bg-emerald-50 rounded-lg p-3 text-center">
              {{ status }}
            </div>

            <!-- PIN Dots Display -->
            <div class="flex justify-center gap-4">
              <div
                v-for="(digit, index) in pinDigits"
                :key="index"
                :class="[
                  'w-14 h-14 rounded-2xl border-2 flex items-center justify-center text-2xl font-bold transition-all duration-200',
                  digit
                    ? 'border-emerald-500 bg-emerald-50 text-emerald-700 scale-105 shadow-md shadow-emerald-500/10'
                    : 'border-slate-200 bg-white text-transparent'
                ]"
              >
                <div v-if="digit" class="w-3 h-3 bg-emerald-500 rounded-full" />
                <div v-else class="w-3 h-3 bg-slate-200 rounded-full" />
              </div>
            </div>

            <!-- Error Message -->
            <p v-if="pinForm.errors.pin" class="text-sm text-red-500 text-center font-medium">
              {{ pinForm.errors.pin }}
            </p>

            <!-- Numpad -->
            <div class="space-y-3 max-w-xs mx-auto">
              <div v-for="(row, rowIndex) in numpadKeys" :key="rowIndex" class="grid grid-cols-3 gap-3">
                <button
                  v-for="key in row"
                  :key="key"
                  type="button"
                  @click="handleNumpadPress(key)"
                  :disabled="pinForm.processing"
                  :class="[
                    'h-14 rounded-xl font-semibold text-lg transition-all duration-150 active:scale-95',
                    'focus:outline-none focus:ring-2 focus:ring-emerald-500/50',
                    'disabled:opacity-50 disabled:cursor-not-allowed',
                    key === 'clear'
                      ? 'bg-red-50 text-red-600 hover:bg-red-100 text-sm'
                      : key === 'delete'
                        ? 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                        : 'bg-white border border-slate-200 text-slate-800 hover:bg-slate-50 hover:border-slate-300 shadow-sm'
                  ]"
                >
                  <span v-if="key === 'delete'" class="flex items-center justify-center">
                    <Delete class="w-5 h-5" />
                  </span>
                  <span v-else-if="key === 'clear'" class="text-xs uppercase tracking-wider">Clear</span>
                  <span v-else>{{ key }}</span>
                </button>
              </div>
            </div>

            <!-- Processing indicator -->
            <div v-if="pinForm.processing" class="text-center text-sm text-emerald-600 font-medium animate-pulse">
              Memverifikasi PIN...
            </div>

            <!-- Switch to Admin Mode -->
            <div class="pt-2 border-t border-slate-100">
              <button
                type="button"
                @click="switchMode('admin')"
                class="w-full flex items-center justify-center gap-2 py-3 text-sm text-slate-500 hover:text-slate-700 transition-colors rounded-xl hover:bg-slate-50"
              >
                <Shield class="w-4 h-4" />
                Login sebagai Admin
              </button>
            </div>
          </CardContent>
        </Card>

        <!-- ============================================= -->
        <!-- ADMIN MODE (Email + Password) -->
        <!-- ============================================= -->
        <Card v-else class="glass border-white/10 animate-fade-in">
          <CardHeader class="text-center">
            <div class="mx-auto w-16 h-16 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-emerald-500/20">
              <Shield class="w-7 h-7 text-white" />
            </div>
            <CardTitle class="text-2xl text-slate-800">Login Admin</CardTitle>
            <CardDescription class="text-slate-500">
              Masuk dengan email dan password
            </CardDescription>
          </CardHeader>

          <CardContent>
            <div v-if="status" class="mb-4 text-sm font-medium text-emerald-400 bg-emerald-500/10 rounded-lg p-3">
              {{ status }}
            </div>

            <form @submit.prevent="submitAdmin" class="space-y-5">
              <!-- Email -->
              <div class="space-y-2">
                <InputLabel for="email" class="text-slate-700">Email</InputLabel>
                <div class="relative">
                  <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500 z-10" />
                  <TextInput
                    id="email"
                    type="email"
                    v-model="adminForm.email"
                    variant="auth"
                    :error="!!adminForm.errors.email"
                    class="pl-10 py-3 bg-white border-slate-200 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                    placeholder="nama@email.com"
                    required
                    autofocus
                    autocomplete="username"
                  />
                </div>
                <p v-if="adminForm.errors.email" class="text-sm text-red-400">
                  {{ adminForm.errors.email }}
                </p>
              </div>

              <!-- Password -->
              <div class="space-y-2">
                <InputLabel for="password" class="text-slate-700">Password</InputLabel>
                <div class="relative">
                  <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500 z-10" />
                  <TextInput
                    id="password"
                    :type="showPassword ? 'text' : 'password'"
                    v-model="adminForm.password"
                    variant="auth"
                    :error="!!adminForm.errors.password"
                    class="pl-10 pr-10 py-3 bg-white border-slate-200 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                    placeholder="••••••••"
                    required
                    autocomplete="current-password"
                  />
                  <button
                    type="button"
                    @click="showPassword = !showPassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors z-10"
                  >
                    <EyeOff v-if="showPassword" class="w-5 h-5" />
                    <Eye v-else class="w-5 h-5" />
                  </button>
                </div>
                <p v-if="adminForm.errors.password" class="text-sm text-red-400">
                  {{ adminForm.errors.password }}
                </p>
              </div>

              <!-- Remember Me -->
              <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer">
                  <input
                    type="checkbox"
                    v-model="adminForm.remember"
                    class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                  />
                  <span class="ml-2 text-sm text-slate-600">Ingat saya</span>
                </label>

                <Link
                  v-if="canResetPassword"
                  :href="route('password.request')"
                  class="text-sm text-emerald-400 hover:text-emerald-300 transition-colors"
                >
                  Lupa password?
                </Link>
              </div>

              <!-- Submit Button -->
              <ActionButton
                type="submit"
                :icon="LogIn"
                :loading="adminForm.processing"
                :disabled="adminForm.processing"
                full-width
                size="lg"
              >
                {{ adminForm.processing ? 'Masuk...' : 'Masuk' }}
              </ActionButton>
            </form>

            <!-- Switch to PIN Mode -->
            <div class="mt-5 pt-4 border-t border-slate-100">
              <button
                type="button"
                @click="switchMode('pin')"
                class="w-full flex items-center justify-center gap-2 py-3 text-sm text-slate-500 hover:text-slate-700 transition-colors rounded-xl hover:bg-slate-50"
              >
                <ArrowLeft class="w-4 h-4" />
                Kembali ke PIN Karyawan
              </button>
            </div>
          </CardContent>
        </Card>

      </div>
    </div>
  </GuestLayout>
</template>
