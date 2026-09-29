<script setup lang="ts">
import { ref, computed } from 'vue';

const props = defineProps<{
    shipments: any[];
}>();

const emit = defineEmits<{
    (e: 'create'): void;
    (e: 'printWaybill', shipment: any): void;
    (e: 'updateStatus', shipmentId: number, status: string): void;
    (e: 'delete', id: number): void;
}>();

const shipmentFilterTab = ref<'all' | 'on_the_way' | 'delivered'>('all');
const shipmentSearchQuery = ref('');

const formatDisplayDate = (val: string) => {
    if (!val) return '-';
    const clean = val.split('T')[0];
    const parts = clean.split('-');
    if (parts.length === 3) {
        return `${parts[2]}.${parts[1]}.${parts[0]}`;
    }
    return clean;
};

const totalShipmentQuantity = computed(() => (props.shipments || []).reduce((sum: number, s: any) => sum + parseFloat(s.quantity || 0), 0));
const totalShipmentRevenue = computed(() => (props.shipments || []).reduce((sum: number, s: any) => sum + (parseFloat(s.quantity || 0) * parseFloat(s.unit_price || 0)), 0));
const onTheWayShipmentsCount = computed(() => (props.shipments || []).filter((s: any) => s.status === 'on_the_way').length);
const deliveredShipmentsCount = computed(() => (props.shipments || []).filter((s: any) => s.status === 'delivered').length);

const filteredShipments = computed(() => {
    return (props.shipments || []).filter((s: any) => {
        if (shipmentFilterTab.value === 'on_the_way' && s.status !== 'on_the_way') return false;
        if (shipmentFilterTab.value === 'delivered' && s.status !== 'delivered') return false;

        if (shipmentSearchQuery.value.trim()) {
            const q = shipmentSearchQuery.value.toLowerCase();
            const partyName = s.trading_party?.name?.toLowerCase() || '';
            const plate = s.vehicle_plate?.toLowerCase() || '';
            const driver = s.driver_name?.toLowerCase() || '';
            const productName = s.product?.name?.toLowerCase() || '';
            const waybill = s.dia_waybill_code?.toLowerCase() || '';
            return partyName.includes(q) || plate.includes(q) || driver.includes(q) || productName.includes(q) || waybill.includes(q);
        }

        return true;
    });
});

const exportShipmentsToExcel = () => {
    const headers = [
        'İrsaliye No',
        'Sevkiyat Tarihi',
        'Müşteri / Cari Adı',
        'Cari Kodu',
        'Bağlı Sipariş No',
        'Araç Plakası',
        'Şoför Adı',
        'Şoför Telefon',
        'Sevk Edilen Ürün',
        'Paketleme / Ambalaj',
        'Sevk Miktarı (Kg)',
        'Birim Satış Fiyatı (TL)',
        'Toplam İrsaliye Tutarı (TL)',
        'Teslimat Şekli',
        'Sevkiyat Durumu',
        'Sevkiyat Notu'
    ];

    const rows = filteredShipments.value.map((s: any) => {
        const orderCode = (s.customer_order_id || s.order_id) ? `#ORD-${String(s.customer_order_id || s.order_id).padStart(4, '0')}` : 'Bağımsız Sevkiyat';
        const totalLineAmount = (parseFloat(s.quantity || 0) * parseFloat(s.unit_price || 0)).toFixed(2);
        const statusText = s.status === 'delivered' ? 'Teslim Edildi' : 'Yolda';

        return [
            s.dia_waybill_code || `IRS-2026-${s.id}`,
            s.shipment_date || '',
            s.trading_party?.name || '',
            s.trading_party?.dia_cari_code || '',
            orderCode,
            s.vehicle_plate || '',
            s.driver_name || '',
            s.driver_phone || '',
            s.product?.name || '',
            s.packaging?.name || 'Standart Kasa',
            parseFloat(s.quantity || 0).toFixed(2),
            parseFloat(s.unit_price || 0).toFixed(2),
            totalLineAmount,
            s.delivery_type?.name || 'Araç Teslim',
            statusText,
            s.notes || ''
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
    link.setAttribute('download', `SASA_Tarim_Sevkiyat_ve_Irsaliyeler_${new Date().toISOString().split('T')[0]}.csv`);
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
                    Sevkiyat & İrsaliyeli Teslimat Yönetimi
                </h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Araç plaka, şoför, teslimat şekli, irsaliye entegrasyonu ve resmi sevk belgeleri</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="exportShipmentsToExcel" title="Tüm filtrelenmiş sevkiyatları Excel CSV olarak indir" class="bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                    Excel / CSV Aktar
                </button>
                <button @click="$emit('create')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <span class="text-sm leading-none">+</span> Yeni Sevkiyat Oluştur
                </button>
            </div>
        </div>

        <!-- Kurumsal Finans & Hacim Özet Şeridi -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Sevk Edilen Hacim</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ totalShipmentQuantity.toLocaleString('tr-TR') }} kg</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam İrsaliye Tutarı</span>
                <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400 text-sm">₺{{ totalShipmentRevenue.toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Yoldaki Araçlar / Sevk</span>
                <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ onTheWayShipmentsCount }} Araç</span>
            </div>
            <div class="px-2">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Teslim Edilenler</span>
                <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">{{ deliveredShipmentsCount }} Sevkiyat</span>
            </div>
        </div>

        <!-- Segmentasyon Filtreleri & Arama Çubuğu -->
        <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-2 mb-3">
            <div class="inline-flex p-0.5 bg-slate-100 dark:bg-slate-950 rounded-lg border border-slate-200 dark:border-slate-800 text-xs">
                <button 
                    @click="shipmentFilterTab = 'all'" 
                    :class="shipmentFilterTab === 'all' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1 rounded-md transition text-xs flex items-center gap-1.5 cursor-pointer"
                >
                    <span>Tüm Sevkiyatlar</span>
                    <span class="text-[10px] opacity-75 font-mono">({{ shipments.length }})</span>
                </button>
                <button 
                    @click="shipmentFilterTab = 'on_the_way'" 
                    :class="shipmentFilterTab === 'on_the_way' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1 rounded-md transition text-xs flex items-center gap-1.5 cursor-pointer"
                >
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                    <span>Yolda Olanlar</span>
                    <span class="text-[10px] opacity-75 font-mono">({{ onTheWayShipmentsCount }})</span>
                </button>
                <button 
                    @click="shipmentFilterTab = 'delivered'" 
                    :class="shipmentFilterTab === 'delivered' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                    class="px-3 py-1 rounded-md transition text-xs flex items-center gap-1.5 cursor-pointer"
                >
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Teslim Edilenler</span>
                    <span class="text-[10px] opacity-75 font-mono">({{ deliveredShipmentsCount }})</span>
                </button>
            </div>

            <div class="relative w-full sm:w-64">
                <input 
                    v-model="shipmentSearchQuery" 
                    type="text" 
                    placeholder="Plaka, şoför, cari veya irsaliye ara..." 
                    class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2.5 py-1 text-xs focus:ring-1 focus:ring-slate-500 focus:outline-none"
                />
            </div>
        </div>

        <!-- Kurumsal Sevkiyat Tablosu -->
        <div class="border border-slate-200 dark:border-slate-800 rounded-lg overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-100/80 dark:bg-slate-800/70 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                    <tr>
                        <th class="p-2.5">İrsaliye No & Tarih</th>
                        <th class="p-2.5">Alıcı Cari (Müşteri)</th>
                        <th class="p-2.5">Araç & Nakliye Bilgisi</th>
                        <th class="p-2.5">Sevk Edilen Ürün & Ambalaj</th>
                        <th class="p-2.5 text-right">Miktar & Tutar</th>
                        <th class="p-2.5 text-center">Durum</th>
                        <th class="p-2.5 text-right">İşlemler</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                    <tr v-for="s in filteredShipments" :key="s.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                        <td class="p-2.5 align-top whitespace-nowrap">
                            <div class="font-mono font-bold text-slate-900 dark:text-slate-100">{{ s.dia_waybill_code || ('IRS-2026-' + s.id) }}</div>
                            <div class="text-[10px] text-slate-500">{{ formatDisplayDate(s.shipment_date) }}</div>
                        </td>
                        <td class="p-2.5 align-top">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ s.trading_party?.name }}</div>
                            <div v-if="s.trading_party?.dia_cari_code" class="text-[10px] font-mono text-slate-500">
                                Kod: {{ s.trading_party?.dia_cari_code }}
                            </div>
                            <div v-if="s.order_id || s.customer_order_id" class="text-[10px] text-indigo-600">
                                Bağlı Sipariş: #ORD-{{ String(s.customer_order_id || s.order_id).padStart(4, '0') }}
                            </div>
                        </td>
                        <td class="p-2.5 align-top whitespace-nowrap">
                            <div class="font-mono font-bold text-slate-900 dark:text-slate-100">{{ s.vehicle_plate || 'Plaka Belirtilmedi' }}</div>
                            <div class="text-[10px] text-slate-600 dark:text-slate-400">Şoför: {{ s.driver_name || '-' }}</div>
                            <div v-if="s.driver_phone" class="text-[10px] text-slate-500 font-mono">Tel: {{ s.driver_phone }}</div>
                        </td>
                        <td class="p-2.5 align-top">
                            <div class="font-semibold text-slate-800 dark:text-slate-200">{{ s.product?.name }}</div>
                            <div class="text-[10px] text-slate-500">
                                Ambalaj: {{ s.packaging?.name || 'Standart Kasa' }}
                                <span v-if="s.delivery_type" class="ml-1 text-slate-400">({{ s.delivery_type?.name }})</span>
                            </div>
                            <div v-if="s.notes" class="mt-0.5 text-[10px] text-slate-500 italic">Not: {{ s.notes }}</div>
                        </td>
                        <td class="p-2.5 align-top text-right whitespace-nowrap">
                            <div class="font-bold text-slate-900 dark:text-slate-100">{{ parseFloat(s.quantity).toLocaleString('tr-TR') }} kg</div>
                            <div class="font-mono text-[11px] text-slate-500">₺{{ s.unit_price }} / kg</div>
                            <div class="font-mono font-bold text-emerald-600 text-xs mt-0.5">
                                ₺{{ (parseFloat(s.quantity) * parseFloat(s.unit_price || 0)).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                            </div>
                        </td>
                        <td class="p-2.5 align-top text-center whitespace-nowrap">
                            <span v-if="s.status === 'delivered'" class="inline-block bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                Teslim Edildi
                            </span>
                            <span v-else class="inline-block bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                Yolda
                            </span>
                        </td>
                        <td class="p-2.5 align-top text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-1.5">
                                <button 
                                    v-if="s.status === 'on_the_way'" 
                                    @click="$emit('updateStatus', s.id, 'delivered')" 
                                    title="Teslimat tamamlandı olarak işaretle"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded-md text-[11px] font-bold shadow-xs transition cursor-pointer"
                                >
                                    Teslim Edildi
                                </button>
                                <button 
                                    v-else 
                                    @click="$emit('updateStatus', s.id, 'on_the_way')" 
                                    title="Yolda durumuna geri al"
                                    class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 px-2.5 py-1 rounded-md text-[11px] font-semibold transition cursor-pointer"
                                >
                                    Yolda Yap
                                </button>
                                <button 
                                    @click="$emit('printWaybill', s)" 
                                    title="Resmi Sevk İrsaliyesi & Taşıma Belgesi Çıktısı Al"
                                    class="bg-white hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 px-2.5 py-1 rounded-md text-[11px] font-semibold transition cursor-pointer"
                                >
                                    İrsaliye Yazdır
                                </button>
                                <button 
                                    @click="$emit('delete', s.id)" 
                                    title="Sevkiyatı kalıcı olarak sil"
                                    class="bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 px-2.5 py-1 rounded-md text-[11px] font-semibold transition cursor-pointer"
                                >
                                    Sil
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filteredShipments.length === 0">
                        <td colspan="7" class="p-6 text-center text-slate-400 text-xs">
                            Filtrelere uygun sevkiyat kaydı bulunmamaktadır.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
