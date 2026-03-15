<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import UpdatePinForm from './Partials/UpdatePinForm.vue'
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
import { User, Mail, Lock, Trash2, AlertTriangle, Loader2, CheckCircle, Camera, Pencil } from 'lucide-vue-next'

const props = defineProps({
  mustVerifyEmail: Boolean,
  status: String,
})

const page = usePage()
const user = computed(() => page.props.auth?.user)

// Profile Info Form
const profileForm = useForm({
  _method: 'PATCH',
  name: user.value?.name || '',
  email: user.value?.email || '',
  photo: null,
})

const photoPreview = ref(null);
const photoInput = ref(null);

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };

    reader.readAsDataURL(photo);
    profileForm.photo = photo;
};

const cancelPhotoUpdate = () => {
    photoPreview.value = null;
    photoInput.value.value = null;
    profileForm.photo = null;
};

const updateProfile = () => {
  profileForm.post(route('profile.update'), {
    preserveScroll: true,
    onSuccess: () => {
        // Clear preview on success? or keep it. user prop update will update the real image.
        // Actually usually inertia reloads props, so user.profile_photo_path will be new.
        // But preview shows local file.
        // We can check if we want to clear preview.
    }
  })
}

// Password Form
const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const updatePassword = () => {
  passwordForm.put(route('password.update'), {
    preserveScroll: true,
    onSuccess: () => passwordForm.reset(),
  })
}

// Delete Account Form
const deleteForm = useForm({
  password: '',
})

const showDeleteConfirm = computed(() => deleteForm.password.length > 0)

const deleteAccount = () => {
  deleteForm.delete(route('profile.destroy'), {
    preserveScroll: true,
  })
}

</script>

<template>
  <Head title="Profile Settings" />

  <AdminLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-foreground">Profile Settings</h2>
    </template>

    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Profile Header with Avatar -->
      <div class="bg-card border border-border rounded-2xl p-6 flex items-center gap-6 shadow-sm">
        <!-- Avatar Section -->
        <div class="relative group">
            <input 
                ref="photoInput"
                type="file" 
                class="hidden"
                @change="updatePhotoPreview"
            >
            
            <!-- Avatar Display -->
            <div 
                class="w-24 h-24 rounded-full flex items-center justify-center text-3xl font-bold shadow-lg overflow-hidden cursor-pointer ring-4 ring-background group-hover:ring-primary/20 transition-all relative bg-muted"
                :class="!photoPreview && !user?.profile_photo_path ? 'bg-gradient-to-br from-primary to-primary/60 text-primary-foreground' : 'bg-muted'"
                @click="selectNewPhoto"
            >
                <!-- Preview from file input -->
                <img 
                    v-if="photoPreview" 
                    :src="photoPreview" 
                    class="w-full h-full object-cover" 
                />
                
                <!-- Saved Profile Photo -->
                <img 
                    v-else-if="user?.profile_photo_path" 
                    :src="`/storage/${user.profile_photo_path}`" 
                    class="w-full h-full object-cover" 
                />
                
                <!-- Initials Fallback -->
                <span v-else>
                    {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                </span>
                
                <!-- Overlay on Hover -->
                 <div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                     <Camera class="w-6 h-6 text-white mb-1" />
                     <span class="text-[10px] text-white font-medium">Ubah</span>
                 </div>
            </div>
            
            <!-- Edit Badge -->
            <div 
                @click="selectNewPhoto"
                class="absolute bottom-0 right-0 p-1.5 bg-primary text-primary-foreground rounded-full shadow-md cursor-pointer hover:bg-primary/90 transition-colors border-2 border-background"
                title="Change Photo"
            >
                <Pencil class="w-3.5 h-3.5" />
            </div>
        </div>

        <div class="flex-1 space-y-3">
          <div>
              <h1 class="text-2xl font-bold text-foreground">{{ user?.name }}</h1>
              <p class="text-base text-muted-foreground">{{ user?.email }}</p>
          </div>
          
          <div class="flex items-center gap-3">
               <div class="px-2.5 py-0.5 rounded-full bg-primary/10 border border-primary/20 text-xs font-medium text-primary capitalize">
                 Admin
               </div>
               
               <!-- Photo Actions -->
               <div v-if="photoPreview" class="flex items-center gap-2 animate-in fade-in slide-in-from-left-2 duration-300">
                   <Button 
                       size="sm" 
                       @click="updateProfile"
                       :loading="profileForm.processing"
                   >
                       Simpan Foto
                   </Button>
                   <Button 
                       type="button"
                       variant="ghost" 
                       size="sm"
                       @click="cancelPhotoUpdate"
                       :disabled="profileForm.processing"
                       class="text-muted-foreground hover:text-foreground"
                   >
                       Batal
                   </Button>
               </div>
               <div v-else>
                   <button 
                       type="button" 
                       @click="selectNewPhoto" 
                       class="text-xs text-muted-foreground hover:text-primary transition-colors flex items-center gap-1.5"
                   >
                       <Camera class="w-3.5 h-3.5" />
                       Ubah Foto Profil
                   </button>
               </div>
          </div>
        </div>
      </div>

      <!-- Profile Information Card -->
      <Card>
        <CardHeader>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
              <User class="w-5 h-5 text-primary" />
            </div>
            <div>
              <CardTitle>Profile Information</CardTitle>
              <CardDescription>Update your account's profile information and email address.</CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="updateProfile" class="space-y-4">
            <!-- (Form Fields remain similar, just keeping context) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="name" :error="!!profileForm.errors.name">Name</Label>
                <div class="relative">
                  <User class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                  <Input
                    id="name"
                    v-model="profileForm.name"
                    :error="!!profileForm.errors.name"
                    class="pl-10"
                    required
                    autocomplete="name"
                  />
                </div>
                <p v-if="profileForm.errors.name" class="text-sm text-destructive">{{ profileForm.errors.name }}</p>
              </div>

              <div class="space-y-2">
                <Label for="email" :error="!!profileForm.errors.email">Email</Label>
                <div class="relative">
                  <Mail class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
                  <Input
                    id="email"
                    type="email"
                    v-model="profileForm.email"
                    :error="!!profileForm.errors.email"
                    class="pl-10"
                    required
                    autocomplete="username"
                  />
                </div>
                <p v-if="profileForm.errors.email" class="text-sm text-destructive">{{ profileForm.errors.email }}</p>
              </div>
            </div>

            <div v-if="mustVerifyEmail && !user?.email_verified_at" class="p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800">
              <p class="text-sm text-yellow-800 dark:text-yellow-200">
                Your email address is unverified. 
                <button type="button" class="underline hover:no-underline" @click="$inertia.post(route('verification.send'))">
                  Click here to resend verification.
                </button>
              </p>
            </div>

            <div class="flex items-center gap-4">
              <Button type="submit" :loading="profileForm.processing" :disabled="profileForm.processing">
                <Loader2 v-if="profileForm.processing" class="w-4 h-4 mr-2 animate-spin" />
                Save Changes
              </Button>
              <Transition
                enter-active-class="transition ease-in-out"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out"
                leave-to-class="opacity-0"
              >
                <span v-if="profileForm.recentlySuccessful" class="text-sm text-accent flex items-center gap-1">
                  <CheckCircle class="w-4 h-4" /> Saved!
                </span>
              </Transition>
            </div>
          </form>
        </CardContent>
      </Card>

      <!-- Update PIN Card -->
      <UpdatePinForm />

      <!-- Update Password Card -->
      <Card>
        <CardHeader>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
              <Lock class="w-5 h-5 text-primary" />
            </div>
            <div>
              <CardTitle>Update Password</CardTitle>
              <CardDescription>Ensure your account is using a long, random password to stay secure.</CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="updatePassword" class="space-y-4">
            <div class="space-y-2">
              <Label for="current_password" :error="!!passwordForm.errors.current_password">Current Password</Label>
              <Input
                id="current_password"
                type="password"
                v-model="passwordForm.current_password"
                :error="!!passwordForm.errors.current_password"
                autocomplete="current-password"
              />
              <p v-if="passwordForm.errors.current_password" class="text-sm text-destructive">{{ passwordForm.errors.current_password }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="password" :error="!!passwordForm.errors.password">New Password</Label>
                <Input
                  id="password"
                  type="password"
                  v-model="passwordForm.password"
                  :error="!!passwordForm.errors.password"
                  autocomplete="new-password"
                />
                <p v-if="passwordForm.errors.password" class="text-sm text-destructive">{{ passwordForm.errors.password }}</p>
              </div>

              <div class="space-y-2">
                <Label for="password_confirmation">Confirm Password</Label>
                <Input
                  id="password_confirmation"
                  type="password"
                  v-model="passwordForm.password_confirmation"
                  autocomplete="new-password"
                />
              </div>
            </div>

            <div class="flex items-center gap-4">
              <Button type="submit" :loading="passwordForm.processing" :disabled="passwordForm.processing">
                <Loader2 v-if="passwordForm.processing" class="w-4 h-4 mr-2 animate-spin" />
                Update Password
              </Button>
              <Transition
                enter-active-class="transition ease-in-out"
                enter-from-class="opacity-0"
                leave-active-class="transition ease-in-out"
                leave-to-class="opacity-0"
              >
                <span v-if="passwordForm.recentlySuccessful" class="text-sm text-accent flex items-center gap-1">
                  <CheckCircle class="w-4 h-4" /> Password Updated!
                </span>
              </Transition>
            </div>
          </form>
        </CardContent>
      </Card>

      <!-- Delete Account Card -->
      <Card class="border-destructive/30">
        <CardHeader>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-destructive/10 flex items-center justify-center">
              <Trash2 class="w-5 h-5 text-destructive" />
            </div>
            <div>
              <CardTitle class="text-destructive">Delete Account</CardTitle>
              <CardDescription>Once your account is deleted, all of its resources and data will be permanently deleted.</CardDescription>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div class="p-4 rounded-lg bg-destructive/5 border border-destructive/20 mb-4">
            <div class="flex items-start gap-3">
              <AlertTriangle class="w-5 h-5 text-destructive mt-0.5 flex-shrink-0" />
              <p class="text-sm text-destructive">
                <strong>Warning:</strong> This action cannot be undone. Please enter your password to confirm you want to permanently delete your account.
              </p>
            </div>
          </div>

          <form @submit.prevent="deleteAccount" class="space-y-4">
            <div class="space-y-2 max-w-sm">
              <Label for="delete_password" :error="!!deleteForm.errors.password">Confirm Password</Label>
              <Input
                id="delete_password"
                type="password"
                v-model="deleteForm.password"
                :error="!!deleteForm.errors.password"
                placeholder="Enter your password to confirm"
              />
              <p v-if="deleteForm.errors.password" class="text-sm text-destructive">{{ deleteForm.errors.password }}</p>
            </div>

            <Button
              type="submit"
              variant="destructive"
              :loading="deleteForm.processing"
              :disabled="deleteForm.processing || !showDeleteConfirm"
            >
              <Loader2 v-if="deleteForm.processing" class="w-4 h-4 mr-2 animate-spin" />
              <Trash2 v-else class="w-4 h-4 mr-2" />
              Delete Account
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
