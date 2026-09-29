<script setup lang="ts">
const props = defineProps<{
    show: boolean;
    sheet: any;
    canApproveFn: (sheet: any) => boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'approve', sheetId: number, action: 'approve' | 'reject'): void;
    (e: 'printCrew', sheet: any, crew: any): void;
}>();
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-3xl p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Günlük Saha & Hasat Formu İncelemesi</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Form ID: #{{ sheet?.id }} | Tarih: {{ sheet?.work_date }}</p>
                </div>
                <button type="button" @click="emit('close')" class="text-slate-400 font-bold hover:text-slate-600 text-xl">✕</button>
            </div>

            <div class="space-y-4 text-xs">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Tesis / Lokasyon</span>
                        <strong class="text-slate-800 dark:text-slate-100">{{ sheet?.production_location?.name }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Şirket</span>
                        <strong class="text-slate-800 dark:text-slate-100">{{ sheet?.company?.name }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Gönderen</span>
                        <strong class="text-slate-800 dark:text-slate-100">{{ sheet?.submitted_by ? (sheet.submitted_by.first_name + ' ' + sheet.submitted_by.last_name) : 'Yönetici' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Onay Durumu</span>
                        <span v-if="sheet?.status === 'approved'" class="text-emerald-600 font-bold">✓ Onaylandı</span>
                        <span v-else-if="sheet?.status === 'rejected'" class="text-rose-600 font-bold">✕ Reddedildi</span>
                        <span v-else class="text-amber-600 font-bold">Onay Bekliyor</span>
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-white dark:bg-slate-900">
                    <h4 class="font-bold text-slate-800 dark:text-slate-200 uppercase text-[11px] mb-3">Çavuş ve İşçi Dağılımları</h4>
                    <div class="space-y-2">
                        <div v-for="cl in sheet?.crew_leaders" :key="cl.id" class="p-3 bg-slate-50 dark:bg-slate-950 rounded-lg border text-xs flex justify-between items-center">
                            <div>
                                <div class="font-bold text-slate-800 dark:text-slate-100">{{ cl.crew_leader?.first_name }} {{ cl.crew_leader?.last_name }} ({{ cl.crew_leader?.origin_city }})</div>
                                <div class="text-slate-500 text-[11px]">İşçi: {{ cl.worker_count }} Kişi | Araç/Servis: {{ cl.car_count }} Adet | Mesai: {{ cl.overtime_hours || 0 }} Saat</div>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-emerald-600">₺{{ cl.calculated_wage_total }}</div>
                                <button type="button" @click="emit('printCrew', sheet, cl)" class="text-rose-600 hover:underline text-[11px] font-bold">Fiş Al</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-white dark:bg-slate-900">
                    <h4 class="font-bold text-slate-800 dark:text-slate-200 uppercase text-[11px] mb-3">Toplanan Hasat Ürünleri</h4>
                    <div class="space-y-2">
                        <div v-for="item in sheet?.harvest_items" :key="item.id" class="p-3 bg-slate-50 dark:bg-slate-950 rounded-lg border text-xs flex justify-between items-center">
                            <div>
                                <div class="font-bold text-slate-800 dark:text-slate-100">{{ item.product?.name }} <span v-if="item.product_subtype">({{ item.product_subtype?.name }})</span></div>
                                <div class="text-slate-500 text-[11px]">{{ item.package_count }} Kasa | {{ item.quantity }} {{ item.unit_symbol || 'kg' }} x ₺{{ item.unit_price }}</div>
                            </div>
                            <div class="font-black text-indigo-600">
                                Ciro: ₺{{ item.total_revenue }}
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="sheet?.notes" class="p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800">
                    <strong class="block mb-1 text-slate-700 dark:text-slate-300">Saha Açıklaması / Not:</strong>
                    <p class="text-slate-600 dark:text-slate-400">{{ sheet.notes }}</p>
                </div>

                <div v-if="canApproveFn(sheet)" class="p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900 rounded-xl flex items-center justify-between gap-4">
                    <span class="font-bold text-amber-800 dark:text-amber-300">
                        Bu formu incelediniz. Onaylamak veya reddetmek ister misiniz?
                    </span>
                    <div class="flex space-x-2">
                        <button type="button" @click="emit('approve', sheet.id, 'approve')" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold transition">
                            Formu Onayla
                        </button>
                        <button type="button" @click="emit('approve', sheet.id, 'reject')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl font-bold transition">
                            Reddet
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t mt-4">
                <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl font-bold">Kapat</button>
            </div>
        </div>
    </div>
</template>
