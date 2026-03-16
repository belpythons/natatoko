<script setup>
import { onMounted, onUnmounted, ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import EmployeeLayout from '@/Layouts/EmployeeLayout.vue';
import StatsCard from '@/Components/StatsCard.vue';
import BaseButton from '@/Components/BaseButton.vue';
import { usePosStore } from '@/Stores/usePosStore';
import { Store, ShoppingCart, Search, Trash2, X, Plus, Minus, CreditCard } from 'lucide-vue-next';
import PaymentModal from './Partials/PaymentModal.vue';

const props = defineProps({
    stats: Object,
    sessionInfo: Object,
});

const posStore = usePosStore();
const searchInput = ref(null);
const isPaymentModalOpen = ref(false);

// Initialize Session in Store
if (props.sessionInfo) {
    posStore.setSession(props.sessionInfo);
}

// Dummy Products for Demo
const products = ref([
    { id: 1, name: 'Kopi Susu Gula Aren', price: 18000, category: 'Coffee', color: 'bg-amber-100 text-amber-800' },
    { id: 2, name: 'Americano Items', price: 15000, category: 'Coffee', color: 'bg-amber-100 text-amber-800' },
    { id: 3, name: 'Croissant Butter', price: 22000, category: 'Pastry', color: 'bg-orange-100 text-orange-800' },
    { id: 4, name: 'Choco Muffin', price: 18000, category: 'Pastry', color: 'bg-orange-100 text-orange-800' },
    { id: 5, name: 'Ice Tea', price: 10000, category: 'Beverage', color: 'bg-emerald-100 text-emerald-800' },
    { id: 6, name: 'Lemonade', price: 15000, category: 'Beverage', color: 'bg-emerald-100 text-emerald-800' },
]);

const filteredProducts = computed(() => {
    if (!posStore.searchQuery) return products.value;
    return products.value.filter(p => 
        p.name.toLowerCase().includes(posStore.searchQuery.toLowerCase())
    );
});

// Format Currency
const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(price);
};

// Hotkeys Handler
const handleKeydown = (e) => {
    // F2: Focus Search
    if (e.key === 'F2') {
        e.preventDefault();
        searchInput.value?.focus();
    }
    // F4: Pay (Trigger Checkout)
    if (e.key === 'F4') {
        e.preventDefault();
        if (posStore.cart.length > 0) {
            isPaymentModalOpen.value = true;
        }
    }
    // Esc: Clear Search or Cancel
    if (e.key === 'Escape') {
        if (isPaymentModalOpen.value) {
            // Let the modal handle it, mostly default behavior is close
        } else if (document.activeElement === searchInput.value) {
            posStore.searchQuery = '';
            searchInput.value.blur();
        }
    }
};

const handleTransactionComplete = () => {
    posStore.clearCart();
    // In real app, reload page or fetch updated stats
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Head title="POS Interface" />

    <EmployeeLayout :session-info="sessionInfo">
        <!-- Split Layout Container -->
        <div class="h-full flex flex-col lg:flex-row gap-6">
            
            <!-- LEFT COLUMN: Product Catalog & Dashboard (Scrollable) -->
            <div class="flex-1 flex flex-col gap-6 h-full overflow-hidden">
                
                <!-- Helper / Dashboard Stats Header (Collapsible or Small) -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 shrink-0">
                    <div class="bg-card p-3 rounded-xl border border-border shadow-sm flex items-center space-x-3">
                         <div class="bg-emerald-100 p-2 rounded-lg text-emerald-600">
                            <Store class="w-5 h-5" />
                         </div>
                         <div>
                            <p class="text-xs text-muted-foreground">Penjualan</p>
                            <p class="font-bold text-foreground">{{ formatPrice(stats?.todaySales || 0) }}</p>
                         </div>
                    </div>
                     <div class="bg-card p-3 rounded-xl border border-border shadow-sm flex items-center space-x-3">
                         <div class="bg-blue-100 p-2 rounded-lg text-blue-600">
                            <ShoppingCart class="w-5 h-5" />
                         </div>
                         <div>
                            <p class="text-xs text-muted-foreground">Order</p>
                            <p class="font-bold text-foreground">{{ stats?.boxOrders || 0 }}</p>
                         </div>
                    </div>
                </div>

                <!-- Product Search Bar -->
                <div class="shrink-0 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <Search class="h-5 w-5 text-muted-foreground" />
                    </div>
                    <input
                        ref="searchInput"
                        type="text"
                        v-model="posStore.searchQuery"
                        class="block w-full pl-10 pr-4 py-3 border-transparent bg-card shadow-sm rounded-xl focus:ring-2 focus:ring-primary focus:bg-white transition-colors text-foreground placeholder:text-muted-foreground font-medium"
                        placeholder="Cari produk... (Tekan F2)"
                    />
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <span class="text-xs text-muted-foreground border border-border px-1.5 py-0.5 rounded">F2</span>
                    </div>
                </div>

                <!-- Product Grid (Scrollable) -->
                <div class="flex-1 overflow-y-auto custom-scrollbar pr-2 pb-20 lg:pb-0">
                    <div v-if="filteredProducts.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        <button
                            v-for="product in filteredProducts"
                            :key="product.id"
                            @click="posStore.addItem(product)"
                            class="group bg-card hover:bg-card/80 border border-border hover:border-primary/50 rounded-xl p-4 flex flex-col items-start gap-2 transition-all duration-200 text-left relative overflow-hidden shadow-sm hover:shadow-md"
                        >
                            <div :class="['w-full aspect-square rounded-lg mb-2 flex items-center justify-center font-bold text-2xl', product.color]">
                                {{ product.name.charAt(0) }}
                            </div>
                            <h3 class="font-semibold text-foreground line-clamp-2 leading-tight group-hover:text-primary transition-colors">
                                {{ product.name }}
                            </h3>
                            <p class="text-gradient-primary font-bold mt-auto">
                                {{ formatPrice(product.price) }}
                            </p>
                        </button>
                    </div>
                     <div v-else class="flex flex-col items-center justify-center h-48 text-muted-foreground">
                        <Search class="w-12 h-12 mb-2 opacity-20" />
                        <p>Produk tidak ditemukan</p>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Cart & Checkout (Fixed) -->
            <div class="w-full lg:w-96 flex-none flex flex-col h-full bg-card border-l border-border/50 shadow-xl overflow-hidden z-20">
                 <!-- Cart Header -->
                 <div class="p-4 border-b border-border flex items-center justify-between bg-card">
                    <h3 class="font-bold text-lg flex items-center">
                        <ShoppingCart class="w-5 h-5 mr-2 text-primary" />
                        Keranjang
                    </h3>
                    <div class="flex items-center space-x-2">
                        <button 
                            @click="posStore.clearCart" 
                            class="p-2 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg transition-colors"
                            title="Bersihkan Keranjang"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </div>
                 </div>

                 <!-- Cart Items (Scrollable) -->
                 <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-3 bg-muted/10">
                    <div v-if="posStore.cart.length === 0" class="h-full flex flex-col items-center justify-center text-muted-foreground opacity-60">
                         <ShoppingCart class="w-16 h-16 mb-4 opacity-20" />
                         <p class="text-sm">Belum ada item</p>
                         <p class="text-xs mt-1">Pilih produk di sebelah kiri</p>
                    </div>

                    <div 
                        v-for="item in posStore.cart" 
                        :key="item.id"
                        class="bg-card p-3 rounded-lg border border-border shadow-sm flex items-center gap-3 animate-in slide-in-from-bottom-2 duration-200"
                    >
                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-foreground truncate">{{ item.name }}</h4>
                            <p class="text-xs text-muted-foreground">{{ formatPrice(item.price) }}</p>
                        </div>
                        
                        <!-- Qty Controls -->
                        <div class="flex items-center bg-muted rounded-lg p-1">
                            <button 
                                @click="posStore.updateQuantity(item.id, item.quantity - 1)"
                                class="w-7 h-7 flex items-center justify-center rounded-md bg-white hover:bg-white/80 text-foreground shadow-sm transition-colors"
                            >
                                <Minus class="w-3 h-3" />
                            </button>
                            <span class="w-8 text-center text-sm font-bold">{{ item.quantity }}</span>
                            <button 
                                @click="posStore.addItem(item)"
                                class="w-7 h-7 flex items-center justify-center rounded-md bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm transition-colors"
                            >
                                <Plus class="w-3 h-3" />
                            </button>
                        </div>
                    </div>
                 </div>

                 <!-- Footer: Totals & Pay -->
                 <div class="p-4 bg-card border-t border-border shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] space-y-4">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm text-muted-foreground">
                            <span>Subtotal</span>
                            <span>{{ formatPrice(posStore.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-muted-foreground">
                            <span>Pajak (0%)</span>
                            <span>{{ formatPrice(posStore.tax) }}</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold text-foreground pt-2 border-t border-dashed border-border">
                            <span>Total</span>
                            <span class="text-gradient-primary">{{ formatPrice(posStore.total) }}</span>
                        </div>
                    </div>
                    
                    <BaseButton 
                        variant="primary" 
                        size="xl" 
                        full-width 
                        class="shadow-lg shadow-emerald-500/20 py-4"
                        :disabled="posStore.cart.length === 0"
                        @click="handleKeydown({ key: 'F4', preventDefault: () => {} })"
                    >
                        <div class="flex items-center justify-between w-full">
                            <span class="flex items-center">
                                <CreditCard class="w-5 h-5 mr-2" />
                                Bayar (F4)
                            </span>
                            <span class="bg-black/10 px-2 py-0.5 rounded text-sm group-hover:bg-black/20 transition-colors">
                                {{ formatPrice(posStore.total) }}
                            </span>
                        </div>
                    </BaseButton>
                 </div>
            </div>
        </div>
        
        <PaymentModal 
            :is-open="isPaymentModalOpen" 
            :order-id="null"
            @close="isPaymentModalOpen = false"
            @complete="handleTransactionComplete"
            @payment-success="handleTransactionComplete"
        />
    </EmployeeLayout>
</template>
