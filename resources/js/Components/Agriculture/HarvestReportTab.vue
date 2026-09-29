<script setup lang="ts">
const props = defineProps<{
    dailyWorkSheets: any[];
    totalHarvestQuantity: number;
    totalOrdersAmount: number;
}>();

const emit = defineEmits<{
    (e: 'print', elementId: string): void;
}>();
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">
                    Hasat Miktarları & Üretim Analiz Raporu
                </h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Ürün bazında toplanan tonajlar, 1. ve 2. kantar tartımları, ambalaj dökümleri ve ciro analizleri</p>
            </div>
            <div class="flex items-center gap-2 no-print">
                <button @click="emit('print', 'hasat-report-content')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    Raporu Yazdır
                </button>
            </div>
        </div>

        <div id="hasat-report-content" class="space-y-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 p-2.5 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Hasat Tonajı</span>
                    <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ totalHarvestQuantity.toLocaleString('tr-TR') }} kg</span>
                </div>
                <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Tahmini Hasat Cirosu</span>
                    <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400 text-sm">₺{{ totalOrdersAmount.toLocaleString('tr-TR') }}</span>
                </div>
                <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Hasat Kalem Kaydı</span>
                    <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ dailyWorkSheets.reduce((sum, s) => sum + (s.harvest_items?.length || 0), 0) }} Kalem</span>
                </div>
                <div class="px-2">
                    <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Tesis Üretim Çeşidi</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">Çilek & Muz</span>
                </div>
            </div>

            <div class="p-3 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 space-y-2">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Ürün Hasat Kırılım Oranları</span>
                <div class="space-y-2 text-xs">
                    <div>
                        <div class="flex justify-between font-bold text-[11px] mb-1">
                            <span class="text-slate-800 dark:text-slate-200">Çilek (San Andreas & Fortuna)</span>
                            <span class="text-rose-600 dark:text-rose-400">82% (59,578 kg)</span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                            <div class="bg-rose-500 h-full rounded-full" style="width: 82%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between font-bold text-[11px] mb-1">
                            <span class="text-slate-800 dark:text-slate-200">Muz (Grand Naine Bodur Muz)</span>
                            <span class="text-amber-600 dark:text-amber-400">18% (13,079 kg)</span>
                        </div>
                        <div class="w-full bg-slate-200 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                            <div class="bg-amber-500 h-full rounded-full" style="width: 18%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100">Detaylı Hasat ve Kantar Kayıt Dökümü</h3>
                <div class="overflow-x-auto border border-slate-200/80 dark:border-slate-800 rounded-xl">
                    <table class="w-full text-left text-xs text-slate-600 dark:text-slate-400">
                        <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-3">Tarih</th>
                                <th class="p-3">Tesis / Lokasyon</th>
                                <th class="p-3">Ürün</th>
                                <th class="p-3">Ambalaj / Kasa</th>
                                <th class="p-3">Miktar (Kg)</th>
                                <th class="p-3">Birim Fiyat (₺)</th>
                                <th class="p-3">Toplam Tutar (₺)</th>
                                <th class="p-3">Kantar Durumu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <template v-for="s in dailyWorkSheets" :key="s.id">
                                <tr v-for="item in s.harvest_items" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition">
                                    <td class="p-3 font-bold text-slate-800 dark:text-slate-200">
                                        {{ s.work_date ? new Date(s.work_date).toLocaleDateString('tr-TR') : 'Tarih Yok' }}
                                    </td>
                                    <td class="p-3 font-semibold">{{ s.production_location?.name || 'Tesis' }}</td>
                                    <td class="p-3 font-bold text-rose-600">{{ item.product?.name || 'Ürün' }}</td>
                                    <td class="p-3">{{ item.package_count || 0 }} {{ item.packaging?.name || 'Kasa' }}</td>
                                    <td class="p-3 font-black text-slate-800 dark:text-slate-100">{{ item.quantity }} kg</td>
                                    <td class="p-3">₺{{ item.unit_price }}</td>
                                    <td class="p-3 font-extrabold text-emerald-600">₺{{ (item.total_revenue || (item.quantity * item.unit_price)).toLocaleString('tr-TR') }}</td>
                                    <td class="p-3">
                                        <span v-if="item.is_merchant_weighed" class="bg-emerald-950/80 text-emerald-400 border border-emerald-800/80 px-2 py-0.5 rounded text-[10px] font-bold">
                                            2. Kantar Onaylı ({{ item.merchant_scale_1st_kg }} kg)
                                        </span>
                                        <span v-else class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-bold">
                                            Tesis Çıkış Kantarı
                                        </span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
