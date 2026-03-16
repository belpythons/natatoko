<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, Sonner } from '@/Components/ui'
import { Head, useForm, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import { Lock, Delete } from 'lucide-vue-next'

const form = useForm({
  pin: '',
})

const addNumber = (num) => {
  if (form.pin.length < 6) {
    form.pin += num
  }
}

const removeNumber = () => {
  if (form.pin.length > 0) {
    form.pin = form.pin.slice(0, -1)
  }
}

const submit = () => {
  if (form.pin.length === 6) {
    form.post('/pos/login', {
      onFinish: () => form.reset('pin'),
    })
  }
}

const pinDots = computed(() => {
  const dots = []
  for (let i = 0; i < 6; i++) {
    dots.push(i < form.pin.length)
  }
  return dots
})
</script>

<template>
  <GuestLayout>
    <Head title="Staff Login" />
    <Sonner />

    <div class="fixed inset-0 -z-10 bg-slate-50"></div>

    <div class="min-h-screen flex items-center justify-center p-4">
      <Card class="w-full max-w-sm border-slate-200 shadow-xl">
        <CardHeader class="text-center pb-2">
          <div class="mx-auto w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mb-4">
            <Lock class="w-6 h-6 text-emerald-600" />
          </div>
          <CardTitle class="text-xl">POS Login</CardTitle>
          <CardDescription>Masukkan PIN POS</CardDescription>
        </CardHeader>

        <CardContent>
          <div class="flex flex-col items-center">
            
            <p v-if="form.errors.pin" class="text-sm text-red-500 mb-4 text-center">
              {{ form.errors.pin }}
            </p>

            <!-- PIN Indicator Dots -->
            <div class="flex gap-3 mb-8 h-4">
              <div 
                v-for="(filled, index) in pinDots" 
                :key="index"
                class="w-4 h-4 rounded-full transition-all duration-200 border-2"
                :class="filled ? 'bg-emerald-500 border-emerald-500' : 'bg-transparent border-slate-300'"
              ></div>
            </div>

            <!-- Numpad -->
            <div class="grid grid-cols-3 gap-4 w-full px-4">
              <button 
                v-for="n in 9" 
                :key="n"
                type="button"
                @click="addNumber(n)"
                class="h-16 rounded-2xl bg-white border border-slate-200 text-2xl font-medium text-slate-700 hover:bg-slate-50 hover:border-emerald-300 active:bg-emerald-50 transition-colors shadow-sm"
              >
                {{ n }}
              </button>
              
              <button 
                type="button"
                @click="removeNumber"
                class="h-16 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-red-500 transition-colors shadow-sm"
              >
                <Delete class="w-6 h-6" />
              </button>
              
              <button 
                type="button"
                @click="addNumber(0)"
                class="h-16 rounded-2xl bg-white border border-slate-200 text-2xl font-medium text-slate-700 hover:bg-slate-50 hover:border-emerald-300 active:bg-emerald-50 transition-colors shadow-sm"
              >
                0
              </button>
              
              <button 
                type="button"
                @click="submit"
                :disabled="form.pin.length !== 6 || form.processing"
                class="h-16 rounded-2xl flex items-center justify-center transition-colors shadow-sm text-lg font-medium"
                :class="form.pin.length === 6 ? 'bg-emerald-500 text-white hover:bg-emerald-600' : 'bg-slate-100 text-slate-400 border border-slate-200'"
              >
                OK
              </button>
            </div>

            <!-- Admin Login Link -->
            <div class="mt-6 text-center">
              <Link
                :href="route('login')"
                class="text-sm font-medium text-slate-500 hover:text-emerald-600 transition-colors"
                tabindex="-1"
              >
                Masuk sebagai Admin &rarr;
              </Link>
            </div>

          </div>
        </CardContent>
      </Card>
    </div>
  </GuestLayout>
</template>
