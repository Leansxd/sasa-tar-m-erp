<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    dailyWorkSheets: any[];
    shipments: any[];
}>();

const stockSummary = computed(() => {
    const byProduct: Record<number, any> = {};

    (props.dailyWorkSheets || []).forEach((sheet: any) => {
        (sheet.harvest_items || []).forEach((hi: any) => {
            if (!hi.product_id) return;
            if (!byProduct[hi.product_id]) {
                byProduct[hi.product_id] = {
                    productId: hi.product_id,
                    productName: hi.product?.name || `Ürün #${hi.product_id}`,
                    harvestKg: 0,
                    harvestPackages: 0,
                    shippedKg: 0,
                    subtypeMap: {} as Record<number, any>,
                };
            }
            const qty = parseFloat(hi.quantity || 0);
            const pkgs = parseInt(hi.package_count || 0);
            byProduct[hi.product_id].harvestKg += qty;
            byProduct[hi.product_id].harvestPackages += pkgs;
            if (hi.product_subtype_id && hi.product?.subtypes) {
                const st = hi.product.subtypes.find((s: any) => s.id === hi.product_subtype_id);
                if (st) {
                    if (!byProduct[hi.product_id].subtypeMap[hi.product_subtype_id]) {
                        byProduct[hi.product_id].subtypeMap[hi.product_subtype_id] = { name: st.name, kg: 0 };
                    }
                    byProduct[hi.product_id].subtypeMap[hi.product_subtype_id].kg += qty;
                }
            }
        });
    });

    (props.shipments || []).forEach((s: any) => {
        if (!s.product_id) return;
        if (!byProduct[s.product_id]) {
            byProduct[s.product_id] = {
                productId: s.product_id,
                productName: s.product?.name || `Ürün #${s.product_id}`,
                harvestKg: 0,
                harvestPackages: 0,
                shippedKg: 0,
                subtypeMap: {},
            };
        }
        byProduct[s.product_id].shippedKg += parseFloat(s.quantity || 0);
    });

    const byProductArr = Object.values(byProduct).map((p: any) => ({
        ...p,
        remainingKg: p.harvestKg - p.shippedKg,
        subtypes: Object.values(p.subtypeMap),
    })).sort((a: any, b: any) => b.harvestKg - a.harvestKg);

    const totalHarvestKg = byProductArr.reduce((sum: number, p: any) => sum + p.harvestKg, 0);
    const totalShippedKg = byProductArr.reduce((sum: number, p: any) => sum + p.shippedKg, 0);
    const totalHarvestPackages = byProductArr.reduce((sum: number, p: any) => sum + p.harvestPackages, 0);

    return {
        byProduct: byProductArr,
        totalHarvestKg,
        totalShippedKg,
        totalRemainingKg: totalHarvestKg - totalShippedKg,
        totalHarvestPackages,
    };
});
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">📦 Anlık Ürün Stok Durumu</h2>
                <p class="text-[11px] text-slate-500 dark:text-slate-400">Hasat edilen miktarlardan sevk edilenlerin düşümüyle hesaplanan güncel tahmini stok</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 p-2.5 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs mb-4">
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Hasat (KG)</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ stockSummary.totalHarvestKg.toLocaleString('tr-TR', {maximumFractionDigits:0}) }} kg</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Sevkiyat (KG)</span>
                <span class="font-bold font-mono text-rose-600 dark:text-rose-400 text-sm">{{ stockSummary.totalShippedKg.toLocaleString('tr-TR', {maximumFractionDigits:0}) }} kg</span>
            </div>
            <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Tahmini Kalan Stok (KG)</span>
                <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400 text-sm">{{ stockSummary.totalRemainingKg.toLocaleString('tr-TR', {maximumFractionDigits:0}) }} kg</span>
            </div>
            <div class="px-2">
                <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Hasat Kasa/Koli</span>
                <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ stockSummary.totalHarvestPackages.toLocaleString('tr-TR') }} Kasa</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                        <th class="text-left p-2.5 font-bold rounded-tl-lg">Ürün</th>
                        <th class="text-right p-2.5 font-bold">Toplam Hasat (KG)</th>
                        <th class="text-right p-2.5 font-bold">Toplam Hasat (Kasa)</th>
                        <th class="text-right p-2.5 font-bold text-rose-600 dark:text-rose-400">Sevk Edilen (KG)</th>
                        <th class="text-right p-2.5 font-bold text-emerald-600 dark:text-emerald-400 rounded-tr-lg">Kalan Stok (KG)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in stockSummary.byProduct" :key="item.productId"
                        class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                        <td class="p-2.5 font-bold text-slate-800 dark:text-slate-200">
                            {{ item.productName }}
                            <div v-if="item.subtypes.length > 0" class="text-[10px] text-slate-500 font-normal mt-0.5">
                                <span v-for="(st, i) in item.subtypes" :key="i" class="mr-2 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">
                                    {{ st.name }}: {{ st.kg.toLocaleString('tr-TR', {maximumFractionDigits:0}) }} kg
                                </span>
                            </div>
                        </td>
                        <td class="p-2.5 text-right font-mono text-slate-700 dark:text-slate-300">{{ Number(item.harvestKg).toLocaleString('tr-TR', {maximumFractionDigits:0}) }} kg</td>
                        <td class="p-2.5 text-right font-mono text-slate-500">{{ item.harvestPackages.toLocaleString('tr-TR') }} kasa</td>
                        <td class="p-2.5 text-right font-mono text-rose-600 dark:text-rose-400">{{ Number(item.shippedKg).toLocaleString('tr-TR', {maximumFractionDigits:0}) }} kg</td>
                        <td class="p-2.5 text-right font-bold font-mono"
                            :class="item.remainingKg > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                            {{ Number(item.remainingKg).toLocaleString('tr-TR', {maximumFractionDigits:0}) }} kg
                            <div class="text-[10px] font-normal mt-0.5 text-slate-400">
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1 mt-1">
                                    <div class="h-1 rounded-full transition-all"
                                        :style="{width: item.harvestKg > 0 ? Math.min(100, (item.shippedKg / item.harvestKg) * 100) + '%' : '0%'}"
                                        :class="item.shippedKg / item.harvestKg < 0.7 ? 'bg-emerald-500' : item.shippedKg / item.harvestKg < 0.9 ? 'bg-amber-500' : 'bg-rose-500'"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="stockSummary.byProduct.length === 0">
                        <td colspan="5" class="p-6 text-center text-slate-400 text-xs">Henüz hasat kaydı bulunmuyor.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50 rounded-xl text-xs text-amber-800 dark:text-amber-300">
            ⚠️ Bu stok tahmini, Günlük İşçi Formlarına girilen hasat miktarlarından, Sevkiyat kayıtlarındaki (KG cinsinden) toplam sevkiyat miktarının düşülmesiyle hesaplanmaktadır. Tartı hatası veya fire oranı bu hesaba dahil değildir.
        </div>
    </div>
</template>
