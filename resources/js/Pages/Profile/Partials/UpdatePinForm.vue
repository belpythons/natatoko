<script setup>
import { useForm } from '@inertiajs/vue3'
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  Input,
  Label,
  Button,
} from '@/Components/ui'
import { KeyRound, Lock, CheckCircle, Loader2 } from 'lucide-vue-next'

const form = useForm({
  pin: '',
  verify_password: '',
})

const updatePin = () => {
  form.patch(route('profile.pin.update'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <Card>
    <CardHeader>
      <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-orange-100 flex-shrink-0 flex items-center justify-center">
          <KeyRound class="w-5 h-5 text-orange-600" />
        </div>
        <div>
          <CardTitle>Update PIN POS</CardTitle>
          <CardDescription>
            Atur ulang PIN POS untuk login kasir dan otorisasi tindakan penting. PIN harus 6 angka.
          </CardDescription>
        </div>
      </div>
    </CardHeader>
    <CardContent>
      <form @submit.prevent="updatePin" class="space-y-5">
        <div class="space-y-2 max-w-md">
          <Label for="pin" :error="!!form.errors.pin">PIN Baru (6 Angka)</Label>
          <div class="relative">
            <Lock class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
            <Input
              id="pin"
              type="text"
              inputmode="numeric"
              maxlength="6"
              v-model="form.pin"
              :error="!!form.errors.pin"
              class="pl-10 font-mono tracking-widest text-center"
              placeholder="123456"
              required
            />
          </div>
          <p v-if="form.errors.pin" class="text-sm text-destructive">{{ form.errors.pin }}</p>
        </div>

        <div class="space-y-2 max-w-md">
            <Label for="verify_password" :error="!!form.errors.verify_password">Password Admin (Konfirmasi)</Label>
            <Input
              id="verify_password"
              type="password"
              v-model="form.verify_password"
              :error="!!form.errors.verify_password"
              placeholder="Masukkan password admin Anda"
              required
            />
            <p v-if="form.errors.verify_password" class="text-sm text-destructive">{{ form.errors.verify_password }}</p>
        </div>

        <div class="flex items-center gap-4 pt-2">
          <Button type="submit" :loading="form.processing" :disabled="form.processing">
            <Loader2 v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" />
            Update PIN
          </Button>
          <Transition
            enter-active-class="transition ease-in-out"
            enter-from-class="opacity-0"
            leave-active-class="transition ease-in-out"
            leave-to-class="opacity-0"
          >
            <span v-if="form.recentlySuccessful" class="text-sm text-accent flex items-center gap-1">
              <CheckCircle class="w-4 h-4" /> PIN Berhasil Diupdate!
            </span>
          </Transition>
        </div>
      </form>
    </CardContent>
  </Card>
</template>
