<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseButton from '@/Components/BaseButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showConfirmPassword = ref(false);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password" />

        <form @submit.prevent="submit" class="bg-white p-6 rounded-xl shadow-sm border border-slate-100 max-w-md mx-auto mt-8">
            <div class="mb-6 text-center">
                <h2 class="text-xl font-semibold text-slate-800">Reset Password</h2>
                <p class="text-sm text-slate-500 mt-1">Buat password baru untuk akun Anda.</p>
            </div>

            <div>
                <InputLabel for="email" value="Email" class="text-slate-700" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full bg-slate-50 text-slate-500 cursor-not-allowed border-slate-200"
                    v-model="form.email"
                    required
                    readonly
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-5">
                <InputLabel for="password" value="Password Baru" class="text-slate-700" />

                <div class="relative mt-1">
                    <TextInput
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        class="block w-full pr-10"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                    >
                        <EyeOff v-if="showPassword" class="w-5 h-5" />
                        <Eye v-else class="w-5 h-5" />
                    </button>
                </div>

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-5">
                <InputLabel
                    for="password_confirmation"
                    value="Konfirmasi Password"
                    class="text-slate-700"
                />

                <div class="relative mt-1">
                    <TextInput
                        id="password_confirmation"
                        :type="showConfirmPassword ? 'text' : 'password'"
                        class="block w-full pr-10"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                    />
                    <button
                        type="button"
                        @click="showConfirmPassword = !showConfirmPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
                    >
                        <EyeOff v-if="showConfirmPassword" class="w-5 h-5" />
                        <Eye v-else class="w-5 h-5" />
                    </button>
                </div>

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-8">
                <BaseButton
                    type="submit"
                    class="w-full justify-center py-2.5"
                    :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Memproses...' : 'Reset Password' }}
                </BaseButton>
            </div>
        </form>
    </GuestLayout>
</template>
