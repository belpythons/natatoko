<script setup>
/**
 * PaymentModal - Supports Cash and QRIS (Mayar.id) payment methods.
 * Features: method selection, cash input with suggestions, QRIS QR code view,
 * 15-minute countdown timer, and payment status polling.
 */
import { ref, computed, watch, onUnmounted } from 'vue';
import { Dialog, DialogPanel, DialogTitle, DialogDescription, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { usePosStore } from '@/Stores/usePosStore';
import BaseButton from '@/Components/BaseButton.vue';
import { X, Printer, CheckCircle, ArrowRight, Banknote, QrCode, Loader2, XCircle } from 'lucide-vue-next';
import { ThermalPrinter } from '@/Services/ThermalPrinter';
import axios from 'axios';

const props = defineProps({
    isOpen: Boolean,
    /** Order ID for QRIS flow (required for Box Orders) */
    orderId: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(['close', 'complete', 'payment-success']);

const store = usePosStore();
const printer = new ThermalPrinter();

// --- State ---
const step = ref('method'); // 'method' | 'cash' | 'qris' | 'success'
const amountPaid = ref('');
const isLoadingQris = ref(false);
const qrisLink = ref('');
const countdownSeconds = ref(900); // 15 minutes
let countdownTimer = null;
let pollingTimer = null;

// --- Computed ---
const change = computed(() => {
    const paid = Number(amountPaid.value) || 0;
    return paid - store.total;
});

const isValid = computed(() => {
    return Number(amountPaid.value) >= store.total;
});

const suggestions = computed(() => {
    const total = store.total;
    return [
        total,
        Math.ceil(total / 5000) * 5000,
        Math.ceil(total / 10000) * 10000,
        Math.ceil(total / 50000) * 50000,
        Math.ceil(total / 100000) * 100000,
    ].filter((v, i, a) => a.indexOf(v) === i && v >= total).slice(0, 4);
});

const formattedCountdown = computed(() => {
    const mins = Math.floor(countdownSeconds.value / 60);
    const secs = countdownSeconds.value % 60;
    return `${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
});

const qrImageUrl = computed(() => {
    if (!qrisLink.value) return '';
    return `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(qrisLink.value)}`;
});

const hasOrderId = computed(() => props.orderId !== null && props.orderId !== undefined);

// --- Watchers ---
watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        resetState();
    } else {
        cleanupTimers();
    }
});

// --- Methods ---
const resetState = () => {
    step.value = 'method';
    amountPaid.value = '';
    qrisLink.value = '';
    countdownSeconds.value = 900;
    isLoadingQris.value = false;
    cleanupTimers();
};

const cleanupTimers = () => {
    if (countdownTimer) {
        clearInterval(countdownTimer);
        countdownTimer = null;
    }
    if (pollingTimer) {
        clearInterval(pollingTimer);
        pollingTimer = null;
    }
};

const selectCash = () => {
    step.value = 'cash';
};

const selectQris = async () => {
    if (!hasOrderId.value) return;

    isLoadingQris.value = true;
    step.value = 'qris';

    try {
        const response = await axios.post(`/pos/box/${props.orderId}/mayar-payment`);

        if (response.data.success && response.data.link) {
            qrisLink.value = response.data.link;
            startCountdown();
            startPolling();
        } else {
            alert('Gagal membuat pembayaran QRIS. Silakan coba lagi.');
            step.value = 'method';
        }
    } catch (error) {
        console.error('QRIS payment error:', error);
        alert('Terjadi kesalahan saat membuat pembayaran QRIS.');
        step.value = 'method';
    } finally {
        isLoadingQris.value = false;
    }
};

const startCountdown = () => {
    countdownSeconds.value = 900;
    countdownTimer = setInterval(() => {
        countdownSeconds.value--;
        if (countdownSeconds.value <= 0) {
            handleTimerExpired();
        }
    }, 1000);
};

const startPolling = () => {
    pollingTimer = setInterval(async () => {
        if (!props.orderId) return;

        try {
            const response = await axios.get(`/pos/box/${props.orderId}/payment-status`);
            if (response.data.payment_status === 'paid') {
                cleanupTimers();
                step.value = 'success';
                emit('payment-success');
            }
        } catch (error) {
            console.error('Polling error:', error);
        }
    }, 3000);
};

const handleTimerExpired = () => {
    cleanupTimers();
    qrisLink.value = '';
    step.value = 'method';
    alert('Waktu Pembayaran Habis, Silakan Pilih Metode Ulang.');
};

const cancelQris = () => {
    cleanupTimers();
    qrisLink.value = '';
    step.value = 'method';
};

const handlePayCash = () => {
    if (!isValid.value) return;
    step.value = 'success';
};

const handlePrint = () => {
    const transaction = {
        id: `TRX-${Date.now().toString().slice(-6)}`,
        cart: [...store.cart],
        subtotal: store.subtotal,
        tax: store.tax,
        total: store.total,
        amountPaid: Number(amountPaid.value),
        change: change.value
    };
    printer.printBrowser(transaction);
};

const handleComplete = () => {
    cleanupTimers();
    emit('complete');
    emit('close');
};

const handleClose = () => {
    if (step.value === 'success') {
        handleComplete();
    } else {
        cleanupTimers();
        emit('close');
    }
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
};

// --- Cleanup on unmount ---
onUnmounted(() => {
    cleanupTimers();
});
</script>

<template>
    <TransitionRoot as="template" :show="isOpen">
        <Dialog as="div" class="relative z-50" @close="handleClose">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200" leave-from="opacity-100 translate-y-0 sm:scale-100" leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                            
                            <!-- Header -->
                            <div class="flex justify-between items-center p-4 border-b border-gray-100">
                                <DialogTitle as="h3" class="text-lg font-bold leading-6 text-gray-900">
                                    <template v-if="step === 'method'">Pilih Metode Pembayaran</template>
                                    <template v-else-if="step === 'cash'">Pembayaran Tunai</template>
                                    <template v-else-if="step === 'qris'">Pembayaran QRIS</template>
                                    <template v-else>Transaksi Berhasil</template>
                                </DialogTitle>
                                <DialogDescription class="sr-only">
                                    Proses pembayaran untuk transaksi
                                </DialogDescription>
                                <button v-if="step !== 'success'" @click="handleClose" class="text-gray-400 hover:text-gray-500">
                                    <X class="w-6 h-6" />
                                </button>
                            </div>

                            <!-- ========================== -->
                            <!-- STEP: Method Selection     -->
                            <!-- ========================== -->
                            <div v-if="step === 'method'" class="p-6">
                                <div class="mb-6 text-center">
                                    <p class="text-sm text-gray-500 mb-1">Total Tagihan</p>
                                    <p class="text-4xl font-extrabold text-gray-900">{{ formatPrice(store.total) }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <!-- Cash Button -->
                                    <button
                                        @click="selectCash"
                                        class="group flex flex-col items-center justify-center gap-3 p-6 rounded-xl border-2 border-gray-200 hover:border-emerald-500 hover:bg-emerald-50 transition-all duration-200"
                                    >
                                        <div class="w-16 h-16 rounded-full bg-emerald-100 group-hover:bg-emerald-200 flex items-center justify-center transition-colors">
                                            <Banknote class="w-8 h-8 text-emerald-600" />
                                        </div>
                                        <span class="font-bold text-gray-900 text-lg">Tunai</span>
                                        <span class="text-xs text-gray-500">Bayar dengan uang tunai</span>
                                    </button>

                                    <!-- QRIS Button -->
                                    <button
                                        @click="selectQris"
                                        :disabled="!hasOrderId"
                                        :class="[
                                            'group flex flex-col items-center justify-center gap-3 p-6 rounded-xl border-2 transition-all duration-200',
                                            hasOrderId 
                                                ? 'border-gray-200 hover:border-blue-500 hover:bg-blue-50 cursor-pointer' 
                                                : 'border-gray-100 bg-gray-50 cursor-not-allowed opacity-50'
                                        ]"
                                    >
                                        <div :class="[
                                            'w-16 h-16 rounded-full flex items-center justify-center transition-colors',
                                            hasOrderId ? 'bg-blue-100 group-hover:bg-blue-200' : 'bg-gray-100'
                                        ]">
                                            <QrCode :class="['w-8 h-8', hasOrderId ? 'text-blue-600' : 'text-gray-400']" />
                                        </div>
                                        <span class="font-bold text-gray-900 text-lg">QRIS</span>
                                        <span class="text-xs text-gray-500">
                                            {{ hasOrderId ? 'Scan QR untuk bayar' : 'Hanya untuk Order Box' }}
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <!-- ========================== -->
                            <!-- STEP: Cash Payment         -->
                            <!-- ========================== -->
                            <div v-else-if="step === 'cash'" class="p-6">
                                <div class="mb-6 text-center">
                                    <p class="text-sm text-gray-500 mb-1">Total Tagihan</p>
                                    <p class="text-4xl font-extrabold text-gray-900">{{ formatPrice(store.total) }}</p>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Uang Diterima (Tunai)</label>
                                        <div class="relative rounded-md shadow-sm">
                                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                                <span class="text-gray-500 sm:text-sm">Rp</span>
                                            </div>
                                            <input 
                                                type="number" 
                                                v-model="amountPaid"
                                                class="block w-full rounded-md border-gray-300 pl-12 py-3 focus:border-emerald-500 focus:ring-emerald-500 sm:text-lg" 
                                                placeholder="0"
                                                autofocus
                                                @keyup.enter="handlePayCash"
                                            />
                                        </div>
                                    </div>

                                    <!-- Quick Suggestions -->
                                    <div class="flex gap-2 flex-wrap">
                                        <button 
                                            v-for="sugg in suggestions" 
                                            :key="sugg"
                                            @click="amountPaid = sugg"
                                            class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-full text-sm font-medium transition-colors"
                                        >
                                            {{ formatPrice(sugg) }}
                                        </button>
                                    </div>

                                    <!-- Change Display Preview -->
                                    <div v-if="isValid" class="bg-emerald-50 p-3 rounded-lg flex justify-between items-center animate-in fade-in slide-in-from-top-1">
                                        <span class="text-emerald-700 font-medium">Kembali</span>
                                        <span class="text-emerald-700 font-bold text-lg">{{ formatPrice(change) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- ========================== -->
                            <!-- STEP: QRIS Payment         -->
                            <!-- ========================== -->
                            <div v-else-if="step === 'qris'" class="p-6">
                                <!-- Loading State -->
                                <div v-if="isLoadingQris" class="flex flex-col items-center justify-center py-12">
                                    <Loader2 class="w-12 h-12 text-blue-500 animate-spin mb-4" />
                                    <p class="text-gray-600 font-medium">Membuat pembayaran QRIS...</p>
                                </div>

                                <!-- QR Code Display -->
                                <div v-else class="flex flex-col items-center">
                                    <div class="mb-4 text-center">
                                        <p class="text-sm text-gray-500 mb-1">Total Tagihan</p>
                                        <p class="text-3xl font-extrabold text-gray-900">{{ formatPrice(store.total) }}</p>
                                    </div>

                                    <!-- QR Code Image -->
                                    <div class="bg-white border-2 border-gray-200 rounded-2xl p-4 mb-4 shadow-sm">
                                        <img 
                                            :src="qrImageUrl" 
                                            alt="QRIS QR Code"
                                            class="w-[250px] h-[250px]"
                                        />
                                    </div>

                                    <p class="text-sm text-gray-500 mb-3">Scan QR code di atas menggunakan aplikasi pembayaran</p>

                                    <!-- Countdown Timer -->
                                    <div class="bg-red-50 border border-red-200 rounded-xl px-6 py-3 mb-4 text-center">
                                        <p class="text-xs text-red-500 font-medium mb-1">Sisa Waktu Pembayaran</p>
                                        <p class="text-3xl font-bold text-red-600 font-mono tracking-wider">{{ formattedCountdown }}</p>
                                    </div>

                                    <!-- Waiting indicator -->
                                    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
                                        <Loader2 class="w-4 h-4 animate-spin" />
                                        <span>Menunggu pembayaran...</span>
                                    </div>

                                    <!-- Cancel QRIS Button -->
                                    <BaseButton 
                                        variant="danger" 
                                        size="md"
                                        @click="cancelQris"
                                        class="w-full"
                                    >
                                        <XCircle class="w-5 h-5 mr-2" />
                                        Batalkan Pembayaran QRIS
                                    </BaseButton>
                                </div>
                            </div>

                            <!-- ========================== -->
                            <!-- STEP: Success              -->
                            <!-- ========================== -->
                            <div v-else class="p-6 flex flex-col items-center text-center">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                    <CheckCircle class="w-8 h-8 text-green-600" />
                                </div>
                                <h4 class="text-xl font-bold text-gray-900 mb-2">Pembayaran Sukses!</h4>
                                <p class="text-gray-500 mb-6">Transaksi telah berhasil direkam.</p>

                                <div class="w-full bg-gray-50 rounded-xl p-4 mb-6 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Total Tagihan</span>
                                        <span class="font-bold text-gray-900">{{ formatPrice(store.total) }}</span>
                                    </div>
                                    <div v-if="amountPaid" class="flex justify-between text-sm">
                                        <span class="text-gray-600">Tunai</span>
                                        <span class="font-bold text-gray-900">{{ formatPrice(amountPaid) }}</span>
                                    </div>
                                    <div v-if="amountPaid" class="border-t border-gray-200 pt-2 flex justify-between text-base">
                                        <span class="text-gray-600 font-medium">Kembali</span>
                                        <span class="font-bold text-emerald-600">{{ formatPrice(change) }}</span>
                                    </div>
                                    <div v-if="!amountPaid" class="border-t border-gray-200 pt-2 flex justify-between text-base">
                                        <span class="text-gray-600 font-medium">Metode</span>
                                        <span class="font-bold text-blue-600">QRIS</span>
                                    </div>
                                </div>

                                <div class="w-full space-y-3">
                                    <BaseButton 
                                        variant="outline" 
                                        class="w-full"
                                        size="lg"
                                        @click="handlePrint"
                                    >
                                        <Printer class="w-5 h-5 mr-2" />
                                        Cetak Struk
                                    </BaseButton>

                                    <BaseButton 
                                        variant="primary" 
                                        class="w-full"
                                        size="lg"
                                        @click="handleComplete"
                                    >
                                        Transaksi Baru
                                        <ArrowRight class="w-5 h-5 ml-2" />
                                    </BaseButton>
                                </div>
                            </div>

                            <!-- Footer Actions (Cash Input Step only) -->
                            <div v-if="step === 'cash'" class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <BaseButton 
                                    variant="primary" 
                                    class="w-full sm:ml-3 sm:w-auto"
                                    :disabled="!isValid"
                                    @click="handlePayCash"
                                >
                                    Bayar Sekarang
                                </BaseButton>
                                <BaseButton 
                                    variant="ghost" 
                                    class="mt-3 w-full sm:mt-0 sm:w-auto"
                                    @click="step = 'method'"
                                >
                                    Kembali
                                </BaseButton>
                            </div>

                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
