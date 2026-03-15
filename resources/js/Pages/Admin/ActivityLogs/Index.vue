<template>
  <Head title="Activity Logs" />

  <AdminLayout>
    <template #header>
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Activity Logs</h2>
          <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
            Riwayat aktivitas dan perubahan data dalam sistem
          </p>
        </div>
      </div>
    </template>

    <div class="mt-8 flex flex-col">
      <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
          
          <Card class="overflow-hidden border-0 shadow-sm ring-1 ring-black/5 dark:ring-white/10">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-300 dark:divide-slate-800">
                <thead class="bg-gray-50/50 dark:bg-slate-800/50">
                  <tr>
                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-slate-100 sm:pl-6">
                      User
                    </th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-slate-100">
                      Waktu
                    </th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-slate-100">
                      Aksi
                    </th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-slate-100">
                      Deskripsi
                    </th>
                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-slate-100">
                      Detail
                    </th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-slate-800 bg-white dark:bg-slate-900">
                  <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-slate-100 sm:pl-6">
                      {{ log.user }}
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-slate-400">
                      <div>{{ log.human_time }}</div>
                      <div class="text-xs text-gray-400 dark:text-slate-500">{{ log.created_at }}</div>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                      <span :class="[
                        getActionColor(log.action),
                        'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset'
                      ]">
                        {{ formatAction(log.action) }}
                      </span>
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-500 dark:text-slate-400 max-w-md truncate" :title="log.description">
                      {{ log.description }}
                    </td>
                    <td class="px-3 py-4 text-sm text-gray-500 dark:text-slate-400">
                      <Button
                        v-if="log.properties"
                        variant="ghost" 
                        size="sm"
                        @click="viewDetails(log)"
                      >
                        <Eye class="mr-2 h-4 w-4" />
                        Lihat Data
                      </Button>
                      <span v-else class="text-gray-400 dark:text-slate-500 italic">Tidak ada payload</span>
                    </td>
                  </tr>
                  
                  <tr v-if="logs.data.length === 0">
                    <td colspan="5" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-slate-400">
                      Belum ada log aktivitas.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-slate-800 sm:px-6">
              <div class="flex-1 flex justify-between sm:hidden">
                <Button 
                  :disabled="!logs.prev_page_url" 
                  @click="router.visit(logs.prev_page_url)" 
                  variant="outline"
                >Previous</Button>
                <Button 
                  :disabled="!logs.next_page_url" 
                  @click="router.visit(logs.next_page_url)" 
                  variant="outline"
                  class="ml-3"
                >Next</Button>
              </div>
              <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                  <p class="text-sm text-gray-700 dark:text-slate-300">
                    Menampilkan <span class="font-medium">{{ logs.from || 0 }}</span> sampai <span class="font-medium">{{ logs.to || 0 }}</span> dari <span class="font-medium">{{ logs.total }}</span> logs
                  </p>
                </div>
                <div>
                  <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <Button
                      v-for="(link, idx) in logs.links"
                      :key="idx"
                      :disabled="!link.url"
                      @click="link.url ? router.visit(link.url) : null"
                      :variant="link.active ? 'default' : 'outline'"
                      class="relative inline-flex items-center px-4 py-2 text-sm font-medium"
                      :class="[
                        idx === 0 ? 'rounded-l-md' : '',
                        idx === logs.links.length - 1 ? 'rounded-r-md' : '',
                        !link.url ? 'opacity-50 cursor-not-allowed' : ''
                      ]"
                      v-html="link.label"
                    />
                  </nav>
                </div>
              </div>
            </div>
            
          </Card>
        </div>
      </div>
    </div>

    <!-- Details View Modal -->
    <Dialog 
      :open="showModal" 
      @update:open="showModal = $event"
      title="Detail Perubahan Data"
      :description="selectedLog ? `${formatAction(selectedLog.action)} - ${selectedLog.human_time}` : ''"
      class="sm:max-w-2xl"
    >
      <div class="max-h-[60vh] overflow-y-auto pr-2">
        <div v-if="selectedLog && selectedLog.properties" class="mt-4">
            
            <div v-if="selectedLog.action === 'updated' && selectedLog.properties.old && selectedLog.properties.new" class="grid grid-cols-2 gap-4">
                <div class="border dark:border-slate-700 rounded-md shadow-sm">
                    <div class="bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 px-4 py-2 text-sm font-medium border-b dark:border-slate-700">Sebelum</div>
                    <pre class="p-4 text-xs bg-gray-50 dark:bg-slate-900/50 text-slate-800 dark:text-slate-300 overflow-x-auto whitespace-pre-wrap">{{ JSON.stringify(selectedLog.properties.old, null, 2) }}</pre>
                </div>
                <div class="border dark:border-slate-700 rounded-md shadow-sm">
                    <div class="bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 px-4 py-2 text-sm font-medium border-b dark:border-slate-700">Sesudah</div>
                    <pre class="p-4 text-xs bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-300 overflow-x-auto whitespace-pre-wrap">{{ JSON.stringify(selectedLog.properties.new, null, 2) }}</pre>
                </div>
            </div>
            
            <div v-else class="border dark:border-slate-700 rounded-md shadow-sm">
                <div class="bg-gray-50 dark:bg-slate-800/50 text-gray-700 dark:text-slate-300 px-4 py-2 text-sm font-medium border-b dark:border-slate-700">Payload</div>
                <pre class="p-4 text-xs bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-300 overflow-x-auto whitespace-pre-wrap">{{ JSON.stringify(selectedLog.properties, null, 2) }}</pre>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
          <Button @click="showModal = false">
            Tutup
          </Button>
        </div>
      </div>
    </Dialog>
    
  </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Eye } from 'lucide-vue-next'
import {
  Button,
  Card,
  Dialog
} from '@/Components/ui'

const props = defineProps({
  logs: Object,
})

const showModal = ref(false)
const selectedLog = ref(null)

const viewDetails = (log) => {
    selectedLog.value = log
    showModal.value = true
}

const formatAction = (action) => {
  const map = {
    'created': 'Dibuat',
    'updated': 'Diedit',
    'deleted': 'Dihapus',
    'completed': 'Diselesaikan'
  }
  return map[action] || action
}

const getActionColor = (action) => {
  switch (action) {
    case 'created':
      return 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 ring-green-600/20 dark:ring-green-500/20'
    case 'updated':
      return 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 ring-blue-600/20 dark:ring-blue-500/20'
    case 'deleted':
      return 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 ring-red-600/20 dark:ring-red-500/20'
    case 'completed':
      return 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 ring-indigo-600/20 dark:ring-indigo-500/20'
    default:
      return 'bg-gray-50 dark:bg-slate-800/50 text-gray-600 dark:text-slate-400 ring-gray-500/10 dark:ring-slate-500/20'
  }
}
</script>
