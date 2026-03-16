<script setup>
import { ref, watch, nextTick } from 'vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/Components/ui'
import TextInput from '@/Components/TextInput.vue'
import ActionButton from '@/Components/ActionButton.vue'
import { ShieldAlert, KeyRound, X } from 'lucide-vue-next'

const props = defineProps({
  show: Boolean,
  actionText: {
    type: String,
    default: 'Lanjutkan Tindakan'
  },
  processing: Boolean,
  error: String,
})

const emit = defineEmits(['close', 'confirm'])

const pin = ref('')
const pinInput = ref(null)

watch(() => props.show, (newVal) => {
  if (newVal) {
    pin.value = ''
    nextTick(() => {
      if (pinInput.value) {
        pinInput.value.focus()
      }
    })
  }
})

const submit = () => {
  if (pin.value.length === 6 && !props.processing) {
    emit('confirm', pin.value)
  }
}

const handleClose = () => {
  if (!props.processing) {
    pin.value = ''
    emit('close')
  }
}
</script>

<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
    <Card class="w-full max-w-sm animate-in fade-in zoom-in-95 duration-200">
      <CardHeader class="relative pb-2">
        <button 
          @click="handleClose" 
          :disabled="processing"
          class="absolute right-4 top-4 text-slate-400 hover:text-slate-600 transition-colors"
        >
          <X class="w-5 h-5" />
        </button>
        
        <div class="mx-auto w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-2">
          <ShieldAlert class="w-6 h-6 text-red-600" />
        </div>
        <CardTitle class="text-center text-lg">Otorisasi Diperlukan</CardTitle>
        <CardDescription class="text-center text-sm">
          Masukkan <strong>PIN Admin</strong> Anda untuk {{ actionText.toLowerCase() }}.
        </CardDescription>
      </CardHeader>

      <CardContent>
        <form @submit.prevent="submit" class="flex flex-col gap-4">
          <div class="relative">
            <KeyRound class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
            <TextInput
              ref="pinInput"
              type="password"
              inputmode="numeric"
              maxlength="6"
              v-model="pin"
              class="pl-10 text-center tracking-[0.5em] font-mono text-lg py-3 bg-slate-50 border-slate-200 focus:border-red-500 focus:ring-red-500"
              placeholder="••••••"
              :disabled="processing"
              required
            />
          </div>
          
          <p v-if="error" class="text-sm text-red-500 text-center font-medium">
            {{ error }}
          </p>

          <div class="flex gap-3 pt-2">
            <button
              type="button"
              @click="handleClose"
              :disabled="processing"
              class="flex-1 py-2 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors"
            >
              Batal
            </button>
            <ActionButton
              type="submit"
              :loading="processing"
              :disabled="pin.length !== 6 || processing"
              class="flex-1 bg-red-600 hover:bg-red-700 focus:ring-red-500"
            >
              {{ actionText }}
            </ActionButton>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
