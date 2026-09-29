<script setup lang="ts">
import { ref, computed } from 'vue';

const props = defineProps<{
    customerOrders: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'edit', order: any): void;
    (e: 'print', order: any): void;
    (e: 'ship', order: any): void;
    (e: 'updateStatus', orderId: number, status: string): void;
    (e: 'delete', id: number): void;
}>();

const orderFilterTab = ref<'active' | 'completed' | 'cancelled' | 'all'>('active');
const orderSearchQuery = ref('');

const formatDisplayDate = (val: string) => {
    if (!val) return '-';
    const clean = val.split('T')[0];
    const parts = clean.split('-');
    if (parts.length === 3) {
        return `${parts[2]}.${parts[1]}.${parts[0]}`;
    }
    return clean;
};

const activeOrdersCount = computed(() => (props.customerOrders || []).filter((o: any) => o.status === 'confirmed' || o.status === 'pending' || !o.status).length);
const completedOrdersCount = computed(() => (props.customerOrders || []).filter((o: any) => o.status === 'shipped').length);
const cancelledOrdersCount = computed(() => (props.customerOrders || []).filter((o: any) => o.status === 'cancelled').length);

const filteredCustomerOrders = computed(() => {
    return (props.customerOrders || []).filter((o: any) => {
        if (orderFilterTab.value === 'active' && (o.status === 'shipped' || o.status === 'cancelled')) return false;
        if (orderFilterTab.value === 'completed' && o.status !== 'shipped') return false;
        if (orderFilterTab.value === 'cancelled' && o.status !== 'cancelled') return false;

        if (orderSearchQuery.value.trim()) {
            const q = orderSearchQuery.value.toLowerCase();
            const partyName = o.trading_party?.name?.toLowerCase() || '';
            const cariCode = o.trading_party?.dia_cari_code?.toLowerCase() || '';
            const contact = o.contact_person?.toLowerCase() || '';
            const hasProduct = o.items?.some((i: any) => i.product?.name?.toLowerCase().includes(q));
            return partyName.includes(q) || cariCode.includes(q) || contact.includes(q) || hasProduct;
        }

        return true;
    });
});

const getOrderTotalQty = (order: any) => {
    return (order.items || []).reduce((sum: number, it: any) => sum + parseFloat(it.quantity || 0), 0);
};

const getOrderShippedQty = (order: any) => {
    return (order.shipments || []).reduce((sum: number, sh: any) => sum + parseFloat(sh.quantity || 0), 0);
};

const getOrderRemainingQty = (order: any) => {
    const total = getOrderTotalQty(order);
    const shipped = getOrderShippedQty(order);
    return Math.max(0, total - shipped);
};

const getOrderProgressPercent = (order: any) => {
    const total = getOrderTotalQty(order);
    if (total <= 0) return order.status === 'shipped' ? 100 : 0;
    const shipped = getOrderShippedQty(order);
    return Math.min(100, Math.round((shipped / total) * 100));
};

const exportOrdersToExcel = () => {
    const headers = [
        'Sipariş No',
        'Sipariş Tarihi',
        'Müşteri / Cari Adı',
        'Cari Kodu',
        'Termin Tarihi',
        'İletişim Kişisi',
        'Sipariş Kalemleri (Ürün - Kasa - Miktar - Birim Fiyat)',
        'Toplam Sipariş Miktarı (Kg)',
        'Sevk Edilen Miktar (Kg)',
        'Kalan Miktar (Kg)',
        'Toplam Tutar (TL)',
        'Sipariş Durumu',
        'Sipariş Notu'
    ];

    const rows = filteredCustomerOrders.value.map((o: any) => {
        const itemsDetail = (o.items || []).map((it: any) => `${it.product?.name || 'Ürün'} (${it.packaging?.name || 'Kasa'}) ${it.quantity}kg x ₺${it.unit_price}`).join(' | ');
        const totalQty = getOrderTotalQty(o);
        const shippedQty = getOrderShippedQty(o);
        const remainingQty = getOrderRemainingQty(o);
        const statusMap: Record<string, string> = {
            'pending': 'Beklemede',
            'confirmed': 'Onaylı / Hazırlıkta',
            'shipped': 'Sevk Edildi',
            'cancelled': 'İptal Edildi'
        };

        return [
            `#ORD-${String(o.id).padStart(4, '0')}`,
            o.order_date || '',
            o.trading_party?.name || '',
            o.trading_party?.dia_cari_code || '',
            o.requested_delivery_date || '',
            o.contact_person || '',
            itemsDetail || '-',
            totalQty.toFixed(2),
            shippedQty.toFixed(2),
            remainingQty.toFixed(2),
            parseFloat(o.total_amount || 0).toFixed(2),
            statusMap[o.status] || o.status || 'Beklemede',
            o.notes || ''
        ];
    });

    const processRow = (row: (string | number)[]) => {
        return row.map(val => {
            const cleanStr = (val === null || val === undefined) ? '' : String(val).replace(/"/g, '""');
            return `"${cleanStr}"`;
        }).join(';');
    };

    const csvContent = '\uFEFF' + [headers.join(';'), ...rows.map(processRow)].join('\r\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `SASA_Tarim_Musteri_Siparisleri_${new Date().toISOString().split('T')[0]}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4">
        <!-- Başlık ve Kurumsal Eylem Butonu -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">
                    Alınan Sipariş & Hasat Rezervasyon Yönetimi
                </h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Müşteri sipariş kayıtları, açık rezervasyonlar, termin takibi ve irsaliye sevkiyat operasyonları</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="exportOrdersToExcel" title="Tüm filtrelenmiş siparişleri Excel CSV olarak indir" class="bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                    Excel / CSV Aktar
                </button>
                <button @click="$emit('create')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <span class="text-sm leading-none">+</span> Yeni Sipariş Girişi
                </button>
            </div>
        </div>

        <!-- Kurumsal Finans & Hacim Özet Şeridi -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Sipariş Portföyü</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">₺{{ customerOrders.reduce((sum, o) => sum + parseFloat(o.total_amount || 0), 0).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Açık / Aktif Siparişler</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ activeOrdersCount }} Sipariş</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Sevk Edilen & Kapanan</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ completedOrdersCount }} Sevk</span>
            </div>
            <div class="px-2">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Müşteri / Cari Adedi</span>
                <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">{{ new Set(customerOrders.map(o => o.trading_party_id)).size }} Firma</span>
            </div>
        </div>

        <!-- Segmentasyon Filtreleri & Arama Çubuğu -->
        <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-2 mb-3">
            <div class="inline-flex p-0.5 bg-slate-100 dark:bg-slate-950 rounded-lg border border-slate-200 dark:border-slate-800 text-xs">
                <button 
                    @click="orderFilterTab = 'active'" 
                    :class="orderFilterTab === 'active' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1 rounded-md transition text-xs flex items-center gap-1.5 cursor-pointer"
                >
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Aktif / Açık</span>
                    <span class="text-[10px] opacity-75 font-mono">({{ activeOrdersCount }})</span>
                </button>
                <button 
                    @click="orderFilterTab = 'completed'" 
                    :class="orderFilterTab === 'completed' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1 rounded-md transition text-xs flex items-center gap-1.5 cursor-pointer"
                >
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    <span>Sevk Edilen</span>
                    <span class="text-[10px] opacity-75 font-mono">({{ completedOrdersCount }})</span>
                </button>
                <button 
                    @click="orderFilterTab = 'cancelled'" 
                    :class="orderFilterTab === 'cancelled' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1 rounded-md transition text-xs flex items-center gap-1.5 cursor-pointer"
                >
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    <span>İptal</span>
                    <span class="text-[10px] opacity-75 font-mono">({{ cancelledOrdersCount }})</span>
                </button>
                <button 
                    @click="orderFilterTab = 'all'" 
                    :class="orderFilterTab === 'all' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1 rounded-md transition text-xs flex items-center gap-1.5 cursor-pointer"
                >
                    <span>Tümü</span>
                    <span class="text-[10px] opacity-75 font-mono">({{ customerOrders.length }})</span>
                </button>
            </div>

            <div class="relative w-full sm:w-64">
                <input 
                    v-model="orderSearchQuery" 
                    type="text" 
                    placeholder="Cari firma, kod veya ürün filtrele..." 
                    class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2.5 py-1 text-xs focus:ring-1 focus:ring-slate-500 focus:outline-none"
                />
            </div>
        </div>

        <!-- Kurumsal ERP Tablosu -->
        <div class="border border-slate-200 dark:border-slate-800 rounded-lg overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-100/80 dark:bg-slate-800/70 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                    <tr>
                        <th class="p-2.5">Sipariş No & Tarih</th>
                        <th class="p-2.5">Cari / Müşteri Bilgisi</th>
                        <th class="p-2.5">Termin / İletişim</th>
                        <th class="p-2.5">Sipariş Kalemleri (Ürün / Kasa / Miktar)</th>
                        <th class="p-2.5 text-right">Tutar</th>
                        <th class="p-2.5 text-center">Durum</th>
                        <th class="p-2.5 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    <tr v-for="o in filteredCustomerOrders" :key="o.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <td class="p-2.5 align-top whitespace-nowrap">
                            <div class="font-mono font-bold text-slate-900 dark:text-slate-100">#ORD-{{ String(o.id).padStart(4, '0') }}</div>
                            <div class="text-[10px] text-slate-500">{{ formatDisplayDate(o.order_date) }}</div>
                        </td>
                        <td class="p-2.5 align-top">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ o.trading_party?.name }}</div>
                            <div v-if="o.trading_party?.dia_cari_code" class="text-[10px] font-mono text-slate-500">
                                Kod: {{ o.trading_party?.dia_cari_code }}
                            </div>
                        </td>
                        <td class="p-2.5 align-top whitespace-nowrap">
                            <div v-if="o.requested_delivery_date" class="text-slate-700 dark:text-slate-300">
                                Termin: <strong>{{ formatDisplayDate(o.requested_delivery_date) }}</strong>
                            </div>
                            <div v-else class="text-slate-400 text-[10px]">Termin Belirtilmedi</div>
                            <div v-if="o.contact_person" class="text-[10px] text-slate-500">Yetkili: {{ o.contact_person }}</div>
                        </td>
                        <td class="p-2.5 align-top min-w-[280px]">
                            <div v-if="o.items && o.items.length > 0" class="space-y-1">
                                <div v-for="item in o.items" :key="item.id" class="text-[11px] flex items-center justify-between gap-2 bg-slate-50 dark:bg-slate-950 p-1.5 rounded border border-slate-200/60 dark:border-slate-800/60">
                                    <div>
                                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ item.product?.name }}</span>
                                        <span v-if="item.packaging" class="text-[10px] text-slate-500 ml-1">({{ item.packaging?.name }})</span>
                                    </div>
                                    <div class="font-mono text-slate-600 dark:text-slate-400 shrink-0">
                                        {{ item.quantity }} kg x ₺{{ item.unit_price }}
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-[10px] text-slate-400 italic">Kalem detayı girilmemiş</div>
                            
                            <!-- Kısmi Sevkiyat & Kalan Miktar İlerleme Çubuğu -->
                            <div v-if="getOrderTotalQty(o) > 0" class="mt-1.5 p-1.5 bg-slate-100/60 dark:bg-slate-950/80 rounded border border-slate-200/60 dark:border-slate-800/60 text-[10px]">
                                <div class="flex justify-between items-center text-slate-600 dark:text-slate-400 font-mono mb-1">
                                    <span>Sipariş: <strong>{{ getOrderTotalQty(o) }} kg</strong></span>
                                    <span>Sevk: <strong class="text-indigo-600">{{ getOrderShippedQty(o) }} kg</strong></span>
                                    <span>Kalan: <strong class="text-emerald-600">{{ getOrderRemainingQty(o) }} kg</strong></span>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                    <div 
                                        class="h-full transition-all duration-300"
                                        :class="getOrderProgressPercent(o) >= 100 ? 'bg-emerald-500' : 'bg-indigo-500'"
                                        :style="{ width: getOrderProgressPercent(o) + '%' }"
                                    ></div>
                                </div>
                            </div>

                            <div v-if="o.notes" class="mt-1 text-[10px] text-slate-500 italic">Not: {{ o.notes }}</div>
                        </td>
                        <td class="p-2.5 align-top text-right whitespace-nowrap">
                            <span class="font-mono font-bold text-slate-900 dark:text-slate-100 text-xs">
                                ₺{{ parseFloat(o.total_amount || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                            </span>
                        </td>
                        <td class="p-2.5 align-top text-center whitespace-nowrap">
                            <span v-if="o.status === 'shipped' || (getOrderTotalQty(o) > 0 && getOrderRemainingQty(o) === 0)" class="inline-block bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                Sevk Edildi (%100)
                            </span>
                            <span v-else-if="getOrderShippedQty(o) > 0" class="inline-block bg-cyan-50 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                Kısmi Sevk (%{{ getOrderProgressPercent(o) }})
                            </span>
                            <span v-else-if="o.status === 'cancelled'" class="inline-block bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                İptal Edildi
                            </span>
                            <span v-else-if="o.status === 'confirmed'" class="inline-block bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                Onaylı / Hazır
                            </span>
                            <span v-else class="inline-block bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                Beklemede
                            </span>
                        </td>
                        <td class="p-2.5 align-top text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <button 
                                    v-if="o.status === 'pending' || !o.status" 
                                    @click="$emit('updateStatus', o.id, 'confirmed')" 
                                    title="Siparişi onayla ve hazırlık aşamasına al"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded-md text-[11px] font-bold shadow-xs transition cursor-pointer"
                                >
                                    Onayla
                                </button>
                                <button 
                                    v-if="o.status !== 'cancelled' && getOrderRemainingQty(o) > 0" 
                                    @click="$emit('ship', o)" 
                                    title="Siparişi otomatik sevkiyata aktar ve irsaliye hazırla"
                                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-2.5 py-1 rounded-md text-[11px] font-bold shadow-xs transition flex items-center gap-1 cursor-pointer"
                                >
                                    Sevk Et
                                </button>
                                <button 
                                    @click="$emit('edit', o)" 
                                    title="Siparişi ve kalemleri düzenle"
                                    class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 px-2.5 py-1 rounded-md text-[11px] font-semibold transition cursor-pointer"
                                >
                                    Düzenle
                                </button>
                                <button 
                                    v-if="o.status !== 'cancelled'" 
                                    @click="$emit('updateStatus', o.id, 'cancelled')" 
                                    title="Siparişi iptal et"
                                    class="bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 px-2.5 py-1 rounded-md text-[11px] font-semibold transition cursor-pointer"
                                >
                                    İptal
                                </button>
                                <button 
                                    v-if="o.status === 'cancelled'" 
                                    @click="$emit('updateStatus', o.id, 'confirmed')" 
                                    title="Tekrar aktife al"
                                    class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 px-2.5 py-1 rounded-md text-[11px] font-semibold transition cursor-pointer"
                                >
                                    Aktif Et
                                </button>
                                <button 
                                    @click="$emit('print', o)" 
                                    title="Resmi Sipariş & Rezervasyon Fişi Yazdır"
                                    class="bg-white hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 px-2.5 py-1 rounded-md text-[11px] font-semibold transition cursor-pointer"
                                >
                                    Yazdır
                                </button>
                                <button 
                                    @click="$emit('delete', o.id)" 
                                    title="Siparişi kalıcı olarak sil"
                                    class="bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 px-2.5 py-1 rounded-md text-[11px] font-semibold transition cursor-pointer"
                                >
                                    Sil
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredCustomerOrders.length === 0">
                        <td colspan="7" class="p-6 text-center text-slate-400 text-xs">
                            Filtrelere uygun sipariş kaydı bulunmamaktadır.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
