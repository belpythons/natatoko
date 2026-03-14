<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, Sonner } from '@/Components/ui'
import ActionButton from '@/Components/ActionButton.vue'
import TextInput from '@/Components/TextInput.vue'
import InputLabel from '@/Components/InputLabel.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Eye, EyeOff, Shield, User, Mail, Lock, Sparkles } from 'lucide-vue-next'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const showPassword = ref(false)
const showConfirm = ref(false)

const submit = () => {
  form.post('/setup', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <GuestLayout>
    <Head title="Setup Admin" />
    <Sonner />

    <!-- Animated Background -->
    <div class="fixed inset-0 -z-10 overflow-hidden bg-gradient-to-br from-emerald-100 via-white to-orange-100">
      <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl animate-float" />
      <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-orange-500/10 rounded-full blur-3xl animate-float" style="animation-delay: 1.5s" />
      <div class="absolute top-1/2 left-1/2 w-48 h-48 bg-emerald-400/10 rounded-full blur-2xl animate-float" style="animation-delay: 0.75s" />
      <div class="absolute inset-0 bg-pattern-dots opacity-50" />
    </div>

    <!-- Setup Card -->
    <div class="min-h-screen flex items-center justify-center p-4">
      <Card class="w-full max-w-md glass border-white/10 animate-fade-in">
        <CardHeader class="text-center">
          <div class="mx-auto w-16 h-16 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg shadow-emerald-500/20">
            <Sparkles class="w-7 h-7 text-white" />
          </div>
          <CardTitle class="text-2xl text-slate-800">Selamat Datang!</CardTitle>
          <CardDescription class="text-slate-500">
            Buat akun admin pertama untuk memulai Posita
          </CardDescription>
        </CardHeader>

        <CardContent>
          <form @submit.prevent="submit" class="space-y-5">
            <!-- Name -->
            <div class="space-y-2">
              <InputLabel for="name" class="text-slate-700">Nama Lengkap</InputLabel>
              <div class="relative">
                <User class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500 z-10" />
                <TextInput
                  id="name"
                  type="text"
                  v-model="form.name"
                  variant="auth"
                  :error="!!form.errors.name"
                  class="pl-10 py-3 bg-white border-slate-200 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                  placeholder="Nama lengkap admin"
                  required
                  autofocus
                />
              </div>
              <p v-if="form.errors.name" class="text-sm text-red-400">
                {{ form.errors.name }}
              </p>
            </div>

            <!-- Email -->
            <div class="space-y-2">
              <InputLabel for="email" class="text-slate-700">Email</InputLabel>
              <div class="relative">
                <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500 z-10" />
                <TextInput
                  id="email"
                  type="email"
                  v-model="form.email"
                  variant="auth"
                  :error="!!form.errors.email"
                  class="pl-10 py-3 bg-white border-slate-200 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                  placeholder="admin@posita.com"
                  required
                  autocomplete="username"
                />
              </div>
              <p v-if="form.errors.email" class="text-sm text-red-400">
                {{ form.errors.email }}
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
                  v-model="form.password"
                  variant="auth"
                  :error="!!form.errors.password"
                  class="pl-10 pr-10 py-3 bg-white border-slate-200 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                  placeholder="Minimal 8 karakter"
                  required
                  autocomplete="new-password"
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
              <p v-if="form.errors.password" class="text-sm text-red-400">
                {{ form.errors.password }}
              </p>
            </div>

            <!-- Confirm Password -->
            <div class="space-y-2">
              <InputLabel for="password_confirmation" class="text-slate-700">Konfirmasi Password</InputLabel>
              <div class="relative">
                <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500 z-10" />
                <TextInput
                  id="password_confirmation"
                  :type="showConfirm ? 'text' : 'password'"
                  v-model="form.password_confirmation"
                  variant="auth"
                  :error="!!form.errors.password_confirmation"
                  class="pl-10 pr-10 py-3 bg-white border-slate-200 text-slate-900 placeholder:text-slate-400 focus:border-emerald-500 focus:ring-emerald-500"
                  placeholder="Ulangi password"
                  required
                  autocomplete="new-password"
                />
                <button
                  type="button"
                  @click="showConfirm = !showConfirm"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors z-10"
                >
                  <EyeOff v-if="showConfirm" class="w-5 h-5" />
                  <Eye v-else class="w-5 h-5" />
                </button>
              </div>
            </div>

            <!-- Info Banner -->
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 text-sm text-emerald-700 flex items-start gap-2">
              <Shield class="w-5 h-5 mt-0.5 flex-shrink-0" />
              <span>Akun ini akan menjadi admin utama dengan akses penuh ke sistem.</span>
            </div>

            <!-- Submit -->
            <ActionButton
              type="submit"
              :icon="Sparkles"
              :loading="form.processing"
              :disabled="form.processing"
              full-width
              size="lg"
            >
              {{ form.processing ? 'Membuat Akun...' : 'Mulai Posita' }}
            </ActionButton>
          </form>
        </CardContent>
      </Card>
    </div>
  </GuestLayout>
</template>
