<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps<{
    stats: {
        location_count: number;
        total_dekar: number;
        active_fert_recipe: string;
        pending_plans: number;
        in_progress_plans: number;
        completed_plans: number;
        pressure_warnings: number;
    };
    analytics: {
        today: any;
        week: any;
        month: any;
        all: any;
    };
    dailyTrends: any[];
    recentSheets: any[];
    recentOrders: any[];
    recentMarketPrices: any[];
    activeFertRun?: any;
    weatherData?: {
        temp_c: number;
        humidity: number;
        wind_kmh: number;
        condition: string;
        frost_risk: boolean;
        rain_mm: number;
        et0_evapotranspiration: number;
    };
    phiWarnings?: any[];
}>();

const page = usePage();
const permissions = computed(() => (page.props.auth as any)?.permissions || []);
const isAdmin = computed(() => !!(page.props.auth as any)?.is_admin);

const hasPermission = (mod: string) => isAdmin.value || permissions.value.includes(mod);

const selectedPeriod = ref<'today' | 'week' | 'month' | 'all'>('month');

const currentMetrics = computed(() => {
    return props.analytics?.[selectedPeriod.value] || {
        revenue: 0,
        harvest_kg: 0,
        labor_wage: 0,
        travel_fee: 0,
        meal_fee: 0,
        total_cost: 0,
        net_profit: 0,
        profit_margin: 0,
        avg_revenue_per_kg: 0,
        avg_cost_per_kg: 0,
        avg_profit_per_kg: 0,
        products: [],
        locations: [],
        crew_leaders: [],
    };
});

const formatCurrency = (val: number) => {
    return '₺' + Number(val || 0).toLocaleString('tr-TR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
};

const formatNumber = (val: number) => {
    return Number(val || 0).toLocaleString('tr-TR');
};

const maxTrendRevenue = computed(() => {
    if (!props.dailyTrends || props.dailyTrends.length === 0) return 1;
    return Math.max(...props.dailyTrends.map(d => Number(d.revenue || 0)), 1);
});
</script>

<template>
    <Head title="Panel" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-xl font-black text-slate-800 dark:text-slate-100 tracking-tight">İstatistik Paneli</h1>
                    <p class="text-xs text-slate-400">Üretim tesisleri, hasat çıktıları, operasyon masrafları ve kâr/zarar analitiği</p>
                </div>

                <div v-if="hasPermission('raporlar')" class="bg-slate-100 dark:bg-slate-900/80 p-1.5 rounded-2xl border border-slate-200/80 dark:border-slate-800 flex items-center gap-1 shadow-2xs">
                    <button
                        @click="selectedPeriod = 'today'"
                        :class="selectedPeriod === 'today' ? 'bg-rose-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'"
                        class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer"
                    >
                        Bugün
                    </button>
                    <button
                        @click="selectedPeriod = 'week'"
                        :class="selectedPeriod === 'week' ? 'bg-rose-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'"
                        class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer"
                    >
                        Son 7 Gün
                    </button>
                    <button
                        @click="selectedPeriod = 'month'"
                        :class="selectedPeriod === 'month' ? 'bg-rose-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'"
                        class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer"
                    >
                        Son 30 Gün
                    </button>
                    <button
                        @click="selectedPeriod = 'all'"
                        :class="selectedPeriod === 'all' ? 'bg-rose-600 text-white shadow-xs font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'"
                        class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer"
                    >
                        Tüm Zamanlar
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <div v-if="hasPermission('raporlar')" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Toplam Satış Geliri</span>
                            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">
                                {{ formatCurrency(currentMetrics.revenue) }}
                            </div>
                        </div>
                        <div class="p-2.5 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 rounded-2xl">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Ort. Kg Satış:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">₺{{ currentMetrics.avg_revenue_per_kg }}/kg</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Toplam Operasyon Masrafı</span>
                            <div class="text-2xl font-black text-rose-600 dark:text-rose-400 mt-1">
                                {{ formatCurrency(currentMetrics.total_cost) }}
                            </div>
                        </div>
                        <div class="p-2.5 bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 rounded-2xl">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Ort. Kg Maliyeti:</span>
                        <span class="font-bold text-rose-600 dark:text-rose-400">₺{{ currentMetrics.avg_cost_per_kg }}/kg</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Net Operasyonel Kâr</span>
                            <div class="text-2xl font-black text-slate-900 dark:text-white mt-1 flex items-center gap-2">
                                <span>{{ formatCurrency(currentMetrics.net_profit) }}</span>
                            </div>
                        </div>
                        <span :class="currentMetrics.net_profit >= 0 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300'" class="px-2.5 py-1 rounded-full text-xs font-black">
                            %{{ currentMetrics.profit_margin }} Marj
                        </span>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Kg Başına Net Kâr:</span>
                        <span class="font-extrabold text-emerald-600 dark:text-emerald-400">+₺{{ currentMetrics.avg_profit_per_kg }}/kg</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-5 shadow-xs flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[11px] font-extrabold uppercase tracking-wide text-slate-400">Toplam Hasat Çıktısı</span>
                            <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">
                                {{ formatNumber(currentMetrics.harvest_kg) }} kg
                            </div>
                        </div>
                        <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-2xl">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Aktif Tesis Alanı:</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300">{{ stats.total_dekar }} Dekar ({{ stats.location_count }} Tesis)</span>
                    </div>
                </div>
            </div>

            <div v-if="hasPermission('raporlar')" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
                    <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h2 class="text-base font-black text-slate-800 dark:text-slate-100">30 Günlük Gelir, Masraf & Hasat Trendi</h2>
                            <p class="text-xs text-slate-400">Son 30 günün hasat cirosu ve günlük operasyon maliyet akışı</p>
                        </div>
                        <div class="flex items-center gap-4 text-xs font-bold">
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-md bg-emerald-500"></span>
                                <span class="text-slate-600 dark:text-slate-300">Gelir (Ciro)</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-md bg-rose-500"></span>
                                <span class="text-slate-600 dark:text-slate-300">Masraf</span>
                            </div>
                        </div>
                    </div>

                    <div class="h-48 flex items-end gap-1.5 pt-6 px-1">
                        <div v-for="(t, idx) in dailyTrends" :key="idx" class="flex-1 flex flex-col items-center gap-1 h-full justify-end group relative cursor-pointer">
                            <div class="w-full flex items-end justify-center gap-0.5 h-full">
                                <div
                                    class="w-1/2 bg-emerald-500/80 group-hover:bg-emerald-400 rounded-t-xs transition-all"
                                    :style="{ height: `${(t.revenue / maxTrendRevenue) * 100}%` }"
                                ></div>
                                <div
                                    class="w-1/2 bg-rose-500/80 group-hover:bg-rose-400 rounded-t-xs transition-all"
                                    :style="{ height: `${(t.cost / maxTrendRevenue) * 100}%` }"
                                ></div>
                            </div>
                            <span v-if="idx % 5 === 0 || idx === dailyTrends.length - 1" class="text-[9px] text-slate-400 font-mono mt-1">
                                {{ t.label }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                            <h2 class="text-base font-black text-slate-800 dark:text-slate-100">Giderler Nereye Gitti?</h2>
                            <p class="text-xs text-slate-400">Seçili dönem masraf kalemleri dökümü</p>
                        </div>

                        <div class="space-y-4 text-xs">
                            <div>
                                <div class="flex justify-between font-bold mb-1">
                                    <span class="text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                                        İşçi & Yevmiye Gideri
                                    </span>
                                    <span class="font-mono text-slate-800 dark:text-slate-100">{{ formatCurrency(currentMetrics.labor_wage) }}</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                    <div class="bg-indigo-500 h-full rounded-full" :style="{ width: currentMetrics.total_cost > 0 ? `${(currentMetrics.labor_wage / currentMetrics.total_cost) * 100}%` : '0%' }"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between font-bold mb-1">
                                    <span class="text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                        Araç & Nakliye Bedeli
                                    </span>
                                    <span class="font-mono text-slate-800 dark:text-slate-100">{{ formatCurrency(currentMetrics.travel_fee) }}</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                    <div class="bg-rose-500 h-full rounded-full" :style="{ width: currentMetrics.total_cost > 0 ? `${(currentMetrics.travel_fee / currentMetrics.total_cost) * 100}%` : '0%' }"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between font-bold mb-1">
                                    <span class="text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                        Yemek & Catering Masrafı
                                    </span>
                                    <span class="font-mono text-slate-800 dark:text-slate-100">{{ formatCurrency(currentMetrics.meal_fee) }}</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                    <div class="bg-amber-500 h-full rounded-full" :style="{ width: currentMetrics.total_cost > 0 ? `${(currentMetrics.meal_fee / currentMetrics.total_cost) * 100}%` : '0%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 p-3.5 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs">
                        <span class="font-extrabold text-slate-600 dark:text-slate-300">Toplam Dönem Gideri:</span>
                        <span class="font-black text-rose-600 dark:text-rose-400 text-sm">{{ formatCurrency(currentMetrics.total_cost) }}</span>
                    </div>
                </div>
            </div>

            <div v-if="hasPermission('raporlar')" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h2 class="text-base font-black text-slate-800 dark:text-slate-100">Ürün Bazlı Kâr & Gelir Tablosu</h2>
                            <p class="text-xs text-slate-400">Hangi üründen ne kadar tonaj, ciro ve net kâr elde edildi?</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto text-xs">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold text-slate-500 uppercase border-b border-slate-100 dark:border-slate-800">
                                    <th class="p-3">Ürün</th>
                                    <th class="p-3">Hasat (kg)</th>
                                    <th class="p-3">Satış Cirosu</th>
                                    <th class="p-3">İşçilik Maliyeti</th>
                                    <th class="p-3 text-right">Net Kâr</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                <tr v-for="p in currentMetrics.products" :key="p.name" class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30">
                                    <td class="p-3 font-extrabold text-slate-800 dark:text-slate-100">
                                        {{ p.name }}
                                        <div class="text-[10px] text-slate-400 font-mono">{{ p.code }}</div>
                                    </td>
                                    <td class="p-3 font-bold text-indigo-600 dark:text-indigo-400">{{ formatNumber(p.kg) }} kg</td>
                                    <td class="p-3 font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(p.revenue) }}</td>
                                    <td class="p-3 font-mono text-rose-600 dark:text-rose-400">{{ formatCurrency(p.estimated_cost) }}</td>
                                    <td class="p-3 text-right font-black text-slate-900 dark:text-white">
                                        <span class="text-emerald-600 dark:text-emerald-400">+{{ formatCurrency(p.profit) }}</span>
                                        <span class="text-[10px] text-slate-400 block font-normal">(%{{ p.margin }})</span>
                                    </td>
                                </tr>
                                <tr v-if="!currentMetrics.products?.length">
                                    <td colspan="5" class="p-6 text-center text-slate-400 italic">Bu dönemde hasat kaydı bulunmuyor.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h2 class="text-base font-black text-slate-800 dark:text-slate-100">Tesis & Üretim Yeri Kârlılık Dağılımı</h2>
                            <p class="text-xs text-slate-400">Silifke, Anamur ve Gazipaşa tesislerinin masraf & hasat kârı</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto text-xs">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold text-slate-500 uppercase border-b border-slate-100 dark:border-slate-800">
                                    <th class="p-3">Tesis Adı</th>
                                    <th class="p-3">Hasat</th>
                                    <th class="p-3">Masraf</th>
                                    <th class="p-3">Ciro</th>
                                    <th class="p-3 text-right">Net Kâr</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                <tr v-for="loc in currentMetrics.locations" :key="loc.name" class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30">
                                    <td class="p-3 font-extrabold text-slate-800 dark:text-slate-100">
                                        {{ loc.name }}
                                        <div class="text-[10px] text-slate-400">{{ loc.company }}</div>
                                    </td>
                                    <td class="p-3 font-bold text-indigo-600 dark:text-indigo-400">{{ formatNumber(loc.kg) }} kg</td>
                                    <td class="p-3 font-mono text-rose-600 dark:text-rose-400">{{ formatCurrency(loc.cost) }}</td>
                                    <td class="p-3 font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(loc.revenue) }}</td>
                                    <td class="p-3 text-right font-black text-emerald-600 dark:text-emerald-400">
                                        +{{ formatCurrency(loc.profit) }}
                                    </td>
                                </tr>
                                <tr v-if="!currentMetrics.locations?.length">
                                    <td colspan="5" class="p-6 text-center text-slate-400 italic">Bu dönemde tesis çalışması bulunmuyor.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="hasPermission('raporlar')" class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-black text-slate-800 dark:text-slate-100">Çavuş / Ekip Bazında Masraf Dağılımı</h2>
                        <p class="text-xs text-slate-400">Taşeron çavuşlara ödenen net yevmiye, araç nakliye ve yemek giderleri</p>
                    </div>
                    <Link :href="route('definitions.index', { tab: 'crew_leaders' })" class="text-xs font-bold text-rose-600 hover:underline">Çavuş Yönetimine Git →</Link>
                </div>

                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold text-slate-500 uppercase border-b border-slate-100 dark:border-slate-800">
                                <th class="p-3">Çavuş Ekip Lideri</th>
                                <th class="p-3">Memleket</th>
                                <th class="p-3">Puantaj (İşçi-Gün)</th>
                                <th class="p-3">Yevmiye Hakedişi</th>
                                <th class="p-3">Araç / Yol Bedeli</th>
                                <th class="p-3">Yemek Gideri</th>
                                <th class="p-3 text-right">Toplam Şirket Masrafı</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            <tr v-for="cl in currentMetrics.crew_leaders" :key="cl.name" class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30">
                                <td class="p-3 font-extrabold text-slate-800 dark:text-slate-100">{{ cl.name }}</td>
                                <td class="p-3 text-slate-500">{{ cl.origin_city }}</td>
                                <td class="p-3 font-bold text-indigo-600 dark:text-indigo-400">
                                    <div>{{ cl.worker_count }} Kişi-Gün</div>
                                    <div class="text-[10px] text-slate-400 font-normal">({{ cl.shift_count }} Gün Sefer)</div>
                                </td>
                                <td class="p-3 font-mono text-slate-700 dark:text-slate-300">{{ formatCurrency(cl.wage_total) }}</td>
                                <td class="p-3 font-mono text-slate-700 dark:text-slate-300">{{ formatCurrency(cl.travel_total) }}</td>
                                <td class="p-3 font-mono text-slate-700 dark:text-slate-300">{{ formatCurrency(cl.meal_total) }}</td>
                                <td class="p-3 text-right font-black text-rose-600 dark:text-rose-400">
                                    {{ formatCurrency(cl.total_cost) }}
                                </td>
                            </tr>
                            <tr v-if="!currentMetrics.crew_leaders?.length">
                                <td colspan="7" class="p-6 text-center text-slate-400 italic">Bu dönemde çalışan çavuş ekibi bulunmuyor.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <div>
                            <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Son Hasat & İşçi Çalışma Kayıtları</h3>
                            <p class="text-xs text-slate-400">Çavuş yevmiyeleri, basılan kg miktarları ve onay durumları</p>
                        </div>
                        <Link :href="route('agriculture.index', { cat: 'operasyon', mod: 'gunluk_isci_formu' })" class="text-xs font-bold text-rose-600 hover:underline">Tümünü Gör →</Link>
                    </div>

                    <div v-if="recentSheets.length === 0" class="text-center py-8 text-xs text-slate-400">Henüz kaydedilmiş günlük işçi formu bulunmamaktadır.</div>
                    <div v-else class="space-y-3">
                        <div v-for="s in recentSheets" :key="s.id" class="p-3.5 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-100 dark:border-slate-800 flex justify-between items-center text-xs">
                            <div>
                                <div class="font-bold text-slate-800 dark:text-slate-100">{{ s.production_location?.name || 'Tesis' }} - {{ s.work_date }}</div>
                                <div class="text-slate-400 text-[11px] mt-0.5">
                                    Firma: {{ s.company?.name }} | Çavuşlar: {{ s.crew_leaders?.map((c: any) => c.crew_leader?.first_name).join(', ') || 'Yok' }}
                                </div>
                            </div>
                            <div class="text-right">
                                <span :class="s.status === 'approved' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-800'" class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase">
                                    {{ s.status === 'approved' ? 'Onaylandı' : 'Onay Bekliyor' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 rounded-3xl p-6 shadow-xs">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                        <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Hava & Gıda Güvenliği</h3>
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">Canlı İstasyon</span>
                    </div>

                    <div v-if="weatherData" class="p-4 bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-100 dark:border-slate-800 text-xs mb-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ weatherData.temp_c }}°C</span>
                                <div class="text-slate-400 text-[11px] font-medium mt-0.5">{{ weatherData.condition }}</div>
                            </div>
                            <div class="text-right text-[11px] text-slate-500 space-y-0.5">
                                <div>Nem: %{{ weatherData.humidity }}</div>
                                <div>Rüzgar: {{ weatherData.wind_kmh }} km/h</div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="text-[11px] font-extrabold uppercase text-slate-400 tracking-wide mb-1">Gıda Güvenliği (PHI) Bekleme Süreleri</div>
                        <div v-for="(phi, idx) in phiWarnings" :key="idx" class="p-3 rounded-2xl border text-xs" :class="phi.status === 'restricted' ? 'bg-rose-50/60 dark:bg-rose-950/30 border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-200' : 'bg-amber-50/60 dark:bg-amber-950/30 border-amber-200 dark:border-amber-900 text-amber-800 dark:text-amber-200'">
                            <div class="font-bold flex justify-between">
                                <span>{{ phi.location }}</span>
                                <span class="font-black">{{ phi.remaining_days }} Gün Kaldı</span>
                            </div>
                            <div class="text-[11px] opacity-80 mt-0.5">{{ phi.pesticide }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
