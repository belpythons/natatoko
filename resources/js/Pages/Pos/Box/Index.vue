<!--
  Created/Modified by: Nata Toko Team
  Feature: Order Box - Halaman utama daftar order box
-->
<script setup>
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { formatMoney } from '@/utils/formatMoney';

// Lucide icons
import {
    Plus,
    Clock,
    Package,
    CalendarDays,
    CheckCircle,
    XCircle,
    DollarSign,
    AlertCircle,
    FileText,
    Image,
    Upload,
    PenSquare,
    ChevronDown,
    Bell,
    Sparkles,
    CheckCircle2
} from 'lucide-vue-next';

const props = defineProps({
    upcomingOrders: {
        type: Array,
        default: () => [],
    },
    todayOrders: {
        type: Array,
        default: () => [],
    },
    hasActiveSession: {
        type: Boolean,
        default: false,
    },
    activeSession: {
        type: Object,
        default: null,
    },
});

// Session info for sidebar status
const sessionInfo = computed(() => ({
  shiftName: props.activeSession?.shift_name ?? null,
  openingBalance: props.activeSession?.opening_cash ?? 0,
  isActive: props.hasActiveSession,
  openedAt: props.activeSession?.opened_at ?? null,
}));

// Countdown timer state
const countdowns = ref({});
let intervalId = null;

// Track which orders have been notified (to avoid repeated alerts)
const notifiedOrders = ref(new Set());

// Modal state
const showStatusModal = ref(false);
const selectedOrder = ref(null);
const statusForm = useForm({
    status: '',
    payment_proof: null,
    cancellation_reason: '',
});

// File input ref
const fileInputRef = ref(null);

// Calculate countdown for each order
const updateCountdowns = () => {
    const now = new Date();
    props.upcomingOrders.forEach(order => {
        const pickup = new Date(order.pickup_datetime);
        const diff = pickup - now;

        if (diff <= 0) {
            countdowns.value[order.id] = { expired: true, text: 'Sudah lewat' };
            
            // Auto-notification when countdown reaches zero
            if (!notifiedOrders.value.has(order.id) && order.status === 'pending') {
                notifiedOrders.value.add(order.id);
                showPickupNotification(order);
            }
        } else {
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            countdowns.value[order.id] = {
                expired: false,
                days,
                hours,
                minutes,
                seconds,
                text: days > 0 
                    ? `${days}h ${hours}j ${minutes}m` 
                    : `${hours}j ${minutes}m ${seconds}d`
            };
        }
    });
};

// Show pickup notification modal
const showPickupNotification = (order) => {
    selectedOrder.value = order;
    showNotificationModal.value = true;
};

// Notification modal state
const showNotificationModal = ref(false);

const closeNotificationModal = () => {
    showNotificationModal.value = false;
    selectedOrder.value = null;
};

const confirmFromNotification = () => {
    closeNotificationModal();
    if (selectedOrder.value) {
        openStatusModal(selectedOrder.value);
    }
};

onMounted(() => {
    updateCountdowns();
    intervalId = setInterval(updateCountdowns, 1000);
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});

const getStatusBadge = (status) => {
    const badges = {
        pending: 'bg-amber-50 text-amber-700 border-amber-200',
        paid: 'bg-emerald-50 text-emerald-700 border-emerald-200',
        completed: 'bg-sky-50 text-sky-700 border-sky-200',
        cancelled: 'bg-red-50 text-red-700 border-red-200',
    };
    return badges[status] || 'bg-gray-50 text-gray-700 border-gray-200';
};

const getStatusText = (status) => {
    const texts = {
        pending: 'Menunggu',
        paid: 'Lunas',
        completed: 'Selesai',
        cancelled: 'Batal',
    };
    return texts[status] || status;
};

// Open status change modal
const openStatusModal = (order) => {
    selectedOrder.value = order;
    statusForm.status = order.status;
    statusForm.payment_proof = null;
    statusForm.cancellation_reason = '';
    showStatusModal.value = true;
};

// Close modal
const closeStatusModal = () => {
    showStatusModal.value = false;
    selectedOrder.value = null;
    statusForm.reset();
    if (fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

// Handle file selection
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        statusForm.payment_proof = file;
    }
};

// Check if payment proof is required
const requiresPaymentProof = computed(() => {
    if (!selectedOrder.value) return false;
    return ['paid', 'completed'].includes(statusForm.status) && 
           !selectedOrder.value.payment_proof_path;
});

// Check if cancellation reason is required
const requiresCancellationReason = computed(() => {
    return statusForm.status === 'cancelled';
});

// Check if form is valid
const isFormValid = computed(() => {
    if (statusForm.status === selectedOrder.value?.status) return false;
    if (requiresPaymentProof.value && !statusForm.payment_proof) return false;
    if (requiresCancellationReason.value && !statusForm.cancellation_reason.trim()) return false;
    return true;
});

// Submit status change
const submitStatusChange = () => {
    const formData = new FormData();
    formData.append('status', statusForm.status);
    formData.append('_method', 'PATCH');
    
    if (statusForm.payment_proof) {
        formData.append('payment_proof', statusForm.payment_proof);
    }
    
    if (statusForm.cancellation_reason) {
        formData.append('cancellation_reason', statusForm.cancellation_reason);
    }

    router.post(`/pos/box/${selectedOrder.value.id}/status`, formData, {
        forceFormData: true,
        onSuccess: () => {
            closeStatusModal();
        },
        onError: (errors) => {
            console.error('Status update failed:', errors);
        },
    });
};

// Available status options
const statusOptions = [
    { value: 'pending', label: 'Menunggu', icon: 'clock', description: 'Order belum dibayar' },
    { value: 'paid', label: 'Lunas', icon: 'currency', description: 'Pembayaran sudah diterima' },
    { value: 'cancelled', label: 'Batal', icon: 'x', description: 'Order dibatalkan' },
];
</script>

<template>
    <Head title="Order Box" />

    <EmployeeLayout :session-info="sessionInfo">
        <template #header>
            <div class="flex items-center gap-2">
                <Package class="w-6 h-6 text-gray-600" />
                <h2 class="text-lg font-semibold text-gray-800">Order Box</h2>
            </div>
        </template>

        <!-- Main Container -->
        <div class="p-6 lg:p-8 space-y-8">
            
            <!-- STATS SECTION (Top) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 flex-shrink-0">
                <!-- Create New Order Button -->
                <Link
                    href="/pos/box/create"
                    class="md:col-span-1 flex items-center justify-center gap-3 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white rounded-xl font-bold text-center shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 hover:scale-[1.02] transition-all duration-300 group p-6 h-full min-h-[120px]"
                >
                    <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center group-hover:bg-white/30 transition-colors">
                        <Plus class="w-6 h-6" />
                    </div>
                    <span class="text-xl">Order Baru</span>
                </Link>

                <!-- Countdown Widget -->
                <div v-if="upcomingOrders.length > 0" class="md:col-span-2 bg-gradient-to-br from-orange-500 via-orange-600 to-amber-500 rounded-xl shadow-lg shadow-orange-500/20 overflow-hidden flex flex-col relative h-full min-h-[120px]">
                     <!-- Decorative Background -->
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <Clock class="w-32 h-32 text-white" />
                    </div>

                    <div class="p-6 flex flex-col h-full relative z-10">
                        <div class="flex items-center gap-3 mb-4 text-white">
                             <div class="w-8 h-8 bg-white/20 backdrop-blur-md rounded-lg flex items-center justify-center">
                                <Clock class="w-4 h-4" />
                            </div>
                            <h3 class="font-bold text-lg tracking-wide">Countdown Pengambilan</h3>
                        </div>
                        
                        <div class="flex-1 flex gap-4 overflow-x-auto pb-2 custom-scrollbar items-center">
                            <div
                                v-for="order in upcomingOrders.slice(0, 3)"
                                :key="order.id"
                                class="flex-shrink-0 bg-white/10 backdrop-blur-md rounded-lg p-3 min-w-[200px] border border-white/10 hover:bg-white/20 transition-colors cursor-pointer"
                                @click="openStatusModal(order)"
                            >
                                <div class="flex justify-between items-start mb-1">
                                    <span class="font-bold text-white text-sm truncate w-24">{{ order.customer_name }}</span>
                                    <span 
                                        class="font-mono font-bold text-sm"
                                        :class="countdowns[order.id]?.expired ? 'text-red-200' : 'text-amber-200'"
                                    >
                                        {{ countdowns[order.id]?.text?.split(' ')[0] || '...' }}
                                    </span>
                                </div>
                                <div class="text-xs text-white/80">
                                    {{ new Date(order.pickup_datetime).toLocaleDateString('id-ID', { weekday: 'short', hour: '2-digit', minute:'2-digit' }) }}
                                </div>
                            </div>
                             <div v-if="upcomingOrders.length > 3" class="text-white text-xs font-medium pl-2">
                                +{{ upcomingOrders.length - 3 }} more
                            </div>
                        </div>
                    </div>
                </div>
                 <!-- Placeholder if no upcoming orders to keep layout stable (Optional, or just let Create button stretch) -->
                 <div v-else class="md:col-span-2 bg-muted/50 rounded-xl border-2 border-dashed border-muted-foreground/20 flex items-center justify-center text-muted-foreground h-full min-h-[120px]">
                    <div class="flex items-center gap-2">
                        <CalendarDays class="w-5 h-5" />
                        <span>Belum ada order mendatang</span>
                    </div>
                 </div>
            </div>

            <!-- MAIN CONTENT GRID (Bottom) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Today's Orders (2 Cols) -->
                <div class="md:col-span-2 bg-card rounded-xl shadow-sm border border-border flex flex-col h-[600px]">
                    <!-- Header -->
                    <div class="flex-shrink-0 px-6 py-5 border-b border-border bg-muted/30 flex items-center justify-between">
                         <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/50 rounded-lg flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                                <Package class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-foreground">Pengambilan Hari Ini</h3>
                                <p class="text-sm text-muted-foreground">{{ todayOrders.length }} pesanan perlu diproses</p>
                            </div>
                        </div>
                         <div class="px-3 py-1 bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-300 rounded-full text-xs font-bold uppercase tracking-wider">
                            Today
                        </div>
                    </div>
                    
                    <!-- Content List -->
                    <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
                         <div v-if="todayOrders.length === 0" class="h-full flex flex-col items-center justify-center text-muted-foreground min-h-[300px]">
                            <div class="w-20 h-20 bg-muted rounded-full flex items-center justify-center mb-4">
                                <Package class="w-10 h-10 opacity-50" />
                            </div>
                            <p class="font-medium text-lg">Tidak ada order hari ini</p>
                            <p class="text-sm text-muted-foreground">Semua order sudah selesai!</p>
                        </div>
                        <div v-else class="space-y-4">
                             <div
                                v-for="order in todayOrders"
                                :key="order.id"
                                class="group bg-card hover:bg-muted/50 border border-border rounded-xl p-5 hover:shadow-md hover:border-emerald-500/30 transition-all duration-200"
                            >
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <div class="flex items-center gap-2">
                                             <h4 class="font-bold text-lg text-foreground">{{ order.customer_name }}</h4>
                                             <span class="text-xs px-2 py-0.5 rounded bg-muted text-muted-foreground">#{{ order.id }}</span>
                                        </div>
                                        <p class="text-sm text-muted-foreground flex items-center gap-1.5 mt-1">
                                            <Clock class="w-4 h-4" />
                                            {{ new Date(order.pickup_datetime).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                                        </p>
                                    </div>
                                    <button
                                        @click="openStatusModal(order)"
                                        class="transform transition-transform active:scale-95"
                                    >
                                        <span :class="['inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-full border', getStatusBadge(order.status)]">
                                            <component 
                                                :is="order.status === 'completed' ? CheckCircle : order.status === 'cancelled' ? XCircle : order.status === 'paid' ? DollarSign : Clock" 
                                                class="w-3.5 h-3.5" 
                                            />
                                            {{ getStatusText(order.status) }}
                                        </span>
                                    </button>
                                </div>
                                
                                <!-- Items -->
                                <div class="bg-muted/30 rounded-lg p-3 mb-4 space-y-2 text-sm border border-border/50">
                                    <div v-for="item in order.items" :key="item.id" class="flex justify-between text-muted-foreground">
                                        <span>{{ item.product_name }} <span class="text-xs text-muted-foreground">x{{ item.quantity }}</span></span>
                                        <span class="font-medium text-foreground">{{ formatMoney(item.subtotal) }}</span>
                                    </div>
                                    <div class="border-t border-border/50 pt-2 flex justify-between font-bold text-foreground">
                                        <span>Total</span>
                                        <span class="text-emerald-600">{{ formatMoney(order.total_price) }}</span>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-end gap-3">
                                     <a
                                        :href="`/pos/box/${order.id}/receipt`"
                                        target="_blank"
                                        class="text-sm font-medium text-primary hover:underline flex items-center gap-1"
                                    >
                                        <FileText class="w-4 h-4" />
                                        Cetak Kwitansi
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Orders (1 Col) -->
                <div class="md:col-span-1 bg-card rounded-xl shadow-sm border border-border flex flex-col h-[600px]">
                     <!-- Header -->
                    <div class="flex-shrink-0 px-6 py-5 border-b border-border bg-muted/30 flex items-center justify-between">
                         <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                                <CalendarDays class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-bold text-lg text-foreground">Mendatang</h3>
                                <p class="text-sm text-muted-foreground">{{ upcomingOrders.length }} pesanan</p>
                            </div>
                        </div>
                    </div>

                    <!-- Content List -->
                    <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
                        <div v-if="upcomingOrders.length === 0" class="h-full flex flex-col items-center justify-center text-muted-foreground min-h-[300px]">
                            <div class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mb-4">
                                <CalendarDays class="w-8 h-8 opacity-50" />
                            </div>
                            <p class="font-medium">Tidak ada data</p>
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="order in upcomingOrders"
                                :key="order.id"
                                class="bg-card hover:bg-muted/50 border border-border rounded-xl p-4 hover:shadow-sm transition-all cursor-pointer group"
                                @click="openStatusModal(order)"
                            >
                                <div class="flex justify-between items-start mb-2">
                                     <h4 class="font-bold text-foreground group-hover:text-primary transition-colors">{{ order.customer_name }}</h4>
                                     <span :class="['text-[10px] px-2 py-0.5 rounded-full border', getStatusBadge(order.status)]">
                                        {{ getStatusText(order.status) }}
                                     </span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-muted-foreground mb-3">
                                    <CalendarDays class="w-4 h-4" />
                                    {{ new Date(order.pickup_datetime).toLocaleString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', hour:'2-digit', minute:'2-digit' }) }}
                                </div>
                                <div class="text-xs text-muted-foreground line-clamp-2 mb-2">
                                     {{ order.items.map(i => i.product_name).join(', ') }}
                                </div>
                                <div class="flex justify-between items-center border-t border-border pt-2">
                                     <span 
                                        class="text-xs font-bold"
                                        :class="countdowns[order.id]?.expired ? 'text-destructive' : 'text-primary'"
                                    >
                                        {{ countdowns[order.id]?.text || 'Loading...' }}
                                    </span>
                                    <span class="font-bold text-sm text-foreground">{{ formatMoney(order.total_price) }}</span>
                                </div>
                                
                                <!-- Action Footer -->
                                <div class="mt-3 pt-3 border-t border-border flex justify-end">
                                    <a
                                        :href="`/pos/box/${order.id}/receipt`"
                                        target="_blank"
                                        @click.stop
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:text-primary/80 hover:underline transition-colors px-2 py-1 rounded hover:bg-primary/10"
                                    >
                                        <FileText class="w-3.5 h-3.5" />
                                        Cetak Kwitansi
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Status Change Modal -->
        <Teleport to="body">
            <div 
                v-if="showStatusModal" 
                class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
                @click.self="closeStatusModal"
            >
                <div class="bg-card rounded-3xl w-full max-w-md max-h-[90vh] overflow-y-auto shadow-2xl animate-scale-in border border-border">
                    <div class="p-6 border-b border-border bg-gradient-to-r from-indigo-50 to-muted dark:from-indigo-950/20 dark:to-muted/10 rounded-t-3xl">
                        <h3 class="font-bold text-xl text-foreground">Ubah Status Order</h3>
                        <p class="text-sm text-muted-foreground mt-1">{{ selectedOrder?.customer_name }}</p>
                    </div>
                    <div class="p-6 space-y-5">
                        <!-- Status Selection -->
                        <div class="space-y-3">
                            <label class="block text-sm font-semibold text-foreground">Pilih Status</label>
                            <div class="space-y-2">
                                <button
                                    v-for="option in statusOptions"
                                    :key="option.value"
                                    @click="statusForm.status = option.value"
                                    :class="[
                                        'w-full p-4 rounded-2xl border-2 text-left flex items-center gap-4 transition-all duration-300',
                                        statusForm.status === option.value 
                                            ? 'border-indigo-500 bg-gradient-to-r from-indigo-50 to-muted dark:from-indigo-950/20 dark:to-muted/10 shadow-lg shadow-indigo-500/10 scale-[1.02]' 
                                            : 'border-border hover:border-muted-foreground/20 hover:bg-muted/50'
                                    ]"
                                >
                                    <div :class="[
                                        'w-12 h-12 rounded-xl flex items-center justify-center shadow-lg',
                                        option.value === 'pending' ? 'bg-gradient-to-br from-amber-400 to-orange-500 shadow-amber-500/30' :
                                        option.value === 'paid' ? 'bg-gradient-to-br from-emerald-400 to-teal-500 shadow-emerald-500/30' :
                                        option.value === 'completed' ? 'bg-gradient-to-br from-sky-400 to-blue-500 shadow-sky-500/30' :
                                        'bg-gradient-to-br from-red-400 to-rose-500 shadow-red-500/30'
                                    ]">
                                        <Clock v-if="option.icon === 'clock'" class="w-6 h-6 text-white" />
                                        <DollarSign v-else-if="option.icon === 'currency'" class="w-6 h-6 text-white" />
                                        <CheckCircle v-else-if="option.icon === 'check'" class="w-6 h-6 text-white" />
                                        <XCircle v-else class="w-6 h-6 text-white" />
                                    </div>
                                    <div class="flex-1">
                                        <span class="font-bold block text-foreground">{{ option.label }}</span>
                                        <span class="text-xs text-muted-foreground">{{ option.description }}</span>
                                    </div>
                                    <div v-if="statusForm.status === option.value" class="text-indigo-500">
                                        <CheckCircle2 class="w-7 h-7" />
                                    </div>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Payment Proof Upload (for paid/completed) -->
                        <div v-if="requiresPaymentProof" class="space-y-3">
                            <label class="flex items-center gap-2 text-sm font-semibold text-foreground">
                                <Image class="w-5 h-5" />
                                Upload Bukti Pembayaran <span class="text-red-500">*</span>
                            </label>
                            <div class="border-2 border-dashed border-border rounded-2xl p-8 text-center hover:border-indigo-400 hover:bg-gradient-to-br hover:from-indigo-50 hover:to-muted dark:hover:from-indigo-950/10 dark:hover:to-muted/10 transition-all duration-300 cursor-pointer group">
                                <input
                                    ref="fileInputRef"
                                    type="file"
                                    accept="image/*"
                                    @change="handleFileChange"
                                    class="hidden"
                                    id="payment-proof-input"
                                />
                                <label for="payment-proof-input" class="cursor-pointer block">
                                    <div v-if="!statusForm.payment_proof" class="text-muted-foreground">
                                        <div class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-indigo-100 dark:group-hover:bg-indigo-900/30 transition-colors">
                                            <Upload class="w-8 h-8 text-muted-foreground group-hover:text-indigo-500" />
                                        </div>
                                        <p class="text-sm font-medium">Klik untuk upload gambar</p>
                                        <p class="text-xs text-muted-foreground mt-1">JPG, PNG, max 5MB</p>
                                    </div>
                                    <div v-else class="text-emerald-600">
                                        <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <CheckCircle2 class="w-8 h-8" />
                                        </div>
                                        <p class="text-sm font-semibold">{{ statusForm.payment_proof.name }}</p>
                                        <p class="text-xs text-muted-foreground mt-1">Klik untuk ganti</p>
                                    </div>
                                </label>
                            </div>
                            <p v-if="selectedOrder?.payment_proof_path" class="flex items-center gap-1.5 text-xs text-emerald-600 font-medium">
                                <CheckCircle class="w-4 h-4" />
                                Bukti pembayaran sudah ada sebelumnya
                            </p>
                        </div>
                        
                        <!-- Cancellation Reason (for cancelled) -->
                        <div v-if="requiresCancellationReason" class="space-y-3">
                            <label class="flex items-center gap-2 text-sm font-semibold text-foreground">
                                <PenSquare class="w-5 h-5" />
                                Alasan Pembatalan <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="statusForm.cancellation_reason"
                                class="w-full border-2 border-border bg-background text-foreground rounded-2xl px-4 py-3 focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all duration-200"
                                rows="3"
                                placeholder="Masukkan alasan pembatalan order..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="p-6 border-t border-border flex gap-3">
                        <button
                            @click="closeStatusModal"
                            class="flex-1 py-3.5 border-2 border-border rounded-2xl text-foreground font-semibold hover:bg-muted transition-all duration-200"
                        >
                            Batal
                        </button>
                        <button
                            @click="submitStatusChange"
                            :disabled="statusForm.processing || !isFormValid"
                            class="flex-1 py-3.5 bg-gradient-to-r from-indigo-600 to-primary text-white rounded-2xl font-semibold hover:from-indigo-700 hover:to-primary disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg shadow-indigo-500/30"
                        >
                            {{ statusForm.processing ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Pickup Deadline Notification Modal -->
        <Teleport to="body">
            <div 
                v-if="showNotificationModal" 
                class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
            >
                <div class="bg-card rounded-3xl w-full max-w-sm text-center shadow-2xl animate-scale-in border border-border">
                    <div class="p-8">
                        <div class="w-24 h-24 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-6 shadow-xl shadow-amber-500/30 animate-pulse">
                            <Bell class="w-12 h-12 text-white" />
                        </div>
                        <h3 class="text-2xl font-bold text-foreground mb-2">Waktu Pengambilan Tiba!</h3>
                        <p class="text-muted-foreground mb-6">
                            Order untuk <strong class="text-foreground">{{ selectedOrder?.customer_name }}</strong> sudah waktunya diambil.
                        </p>
                        <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/30 dark:to-orange-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-4">
                            <p class="text-sm text-amber-800 dark:text-amber-200 flex items-center justify-center gap-2 font-medium">
                                <Sparkles class="w-5 h-5" />
                                Segera konfirmasi status order ini.
                            </p>
                        </div>
                    </div>
                    <div class="p-6 border-t border-border flex gap-3">
                        <button
                            @click="closeNotificationModal"
                            class="flex-1 py-3.5 border-2 border-border rounded-2xl text-foreground font-semibold hover:bg-muted transition-colors duration-200"
                        >
                            Nanti
                        </button>
                        <button
                            @click="confirmFromNotification"
                            class="flex-1 py-3.5 bg-gradient-to-r from-indigo-600 to-primary text-white rounded-2xl font-semibold hover:from-indigo-700 hover:to-primary transition-all duration-200 shadow-lg shadow-indigo-500/30"
                        >
                            Konfirmasi Status
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </EmployeeLayout>
</template>

<style scoped>
/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}

/* Animation */
@keyframes scale-in {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-scale-in {
    animation: scale-in 0.2s ease-out;
}
</style>
