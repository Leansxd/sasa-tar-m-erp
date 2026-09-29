<script setup lang="ts">
const props = defineProps<{
    show: boolean;
    data: any;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'print'): void;
}>();

const formatDisplayDate = (d: any) => {
    if (!d) return '-';
    try {
        const dt = new Date(d);
        if (isNaN(dt.getTime())) return String(d);
        return dt.toLocaleDateString('tr-TR', { day: '2-digit', month: '2-digit', year: 'numeric' });
    } catch {
        return String(d);
    }
};

const getOrderTotalQty = (order: any) => {
    if (!order || !order.items) return 0;
    return order.items.reduce((sum: number, it: any) => sum + parseFloat(it.quantity || 0), 0);
};

const getOrderShippedQty = (order: any) => {
    if (!order || !order.shipments) return 0;
    return order.shipments.reduce((sum: number, sh: any) => sum + parseFloat(sh.quantity || 0), 0);
};

const getOrderProgressPercent = (order: any) => {
    const tot = getOrderTotalQty(order);
    if (!tot || tot <= 0) return 0;
    const shp = getOrderShippedQty(order);
    return Math.min(100, Math.round((shp / tot) * 100));
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 print:p-0 print:static print:bg-white overflow-y-auto">
        <div class="print-document bg-white text-slate-900 rounded-none shadow-none w-full max-w-4xl p-8 print:p-0 font-sans border print:border-none border-slate-300">
            <div class="flex justify-between items-center pb-3 mb-3 border-b-2 border-slate-900">
                <div class="flex items-center gap-3">
                    <img src="/sasaerp.svg" alt="SASA Tarım Logo" class="h-14 w-auto" />
                    <div>
                        <div class="text-[10px] font-bold tracking-widest text-slate-500 uppercase">KURUMSAL ERP SİSTEMİ</div>
                        <div class="text-xs font-black text-slate-900 tracking-wider">SASA TARIM İŞLETMELERİ A.Ş.</div>
                    </div>
                </div>

                <div class="text-center">
                    <h1 class="text-sm font-black text-slate-950 uppercase tracking-wider">SASA TARIM İŞLETMELERİ A.Ş.</h1>
                    <h2 class="text-xs font-semibold text-slate-600 uppercase tracking-wide">TARIMSAL ÜRETİM VE OPERASYON YÖNETİMİ</h2>
                    <div class="text-xs font-bold text-slate-900 uppercase tracking-widest mt-0.5">MÜŞTERİ SİPARİŞ VE REZERVASYON FORMU</div>
                </div>

                <div class="border border-slate-900 p-2 text-right text-[10px] font-mono leading-tight bg-slate-50 min-w-[130px]">
                    <div><strong>SİPARİŞ NO:</strong> #ORD-{{ String(data?.id).padStart(4, '0') }}</div>
                    <div><strong>TARİH:</strong> {{ formatDisplayDate(data?.order_date) }}</div>
                    <div class="text-[9px] text-slate-500 font-sans mt-0.5">SASA ERP Belgesi</div>
                </div>
            </div>

            <div class="mb-3">
                <h3 class="text-xs font-bold text-teal-800 uppercase tracking-wider mb-1">
                    BAŞVURU SAHİBİ / MÜŞTERİ BİLGİLERİ
                </h3>
                <table class="w-full border-collapse border border-slate-900 text-xs">
                    <tbody>
                        <tr>
                            <td class="border border-slate-900 font-bold p-1.5 w-1/4">Sipariş Takip No:</td>
                            <td class="border border-slate-900 p-1.5 w-1/4 font-mono font-bold">#ORD-{{ String(data?.id).padStart(4, '0') }}</td>
                            <td class="border border-slate-900 font-bold p-1.5 w-1/4">Sipariş Tarihi:</td>
                            <td class="border border-slate-900 p-1.5 w-1/4 font-mono">{{ formatDisplayDate(data?.order_date) }}</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-900 font-bold p-1.5">Müşteri / Cari Adı:</td>
                            <td class="border border-slate-900 p-1.5 font-bold">{{ data?.trading_party?.name }}</td>
                            <td class="border border-slate-900 font-bold p-1.5">Cari Kodu:</td>
                            <td class="border border-slate-900 p-1.5 font-mono">{{ data?.trading_party?.dia_cari_code || '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-900 font-bold p-1.5">İstenen Termin / Teslim:</td>
                            <td class="border border-slate-900 p-1.5">{{ formatDisplayDate(data?.requested_delivery_date) }}</td>
                            <td class="border border-slate-900 font-bold p-1.5">Yetkili / Kontak Kişi:</td>
                            <td class="border border-slate-900 p-1.5">{{ data?.contact_person || '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-900 font-bold p-1.5">Sipariş Durumu:</td>
                            <td class="border border-slate-900 p-1.5 font-bold">
                                {{ data?.status === 'shipped' ? 'SEVK EDİLDİ' : (data?.status === 'confirmed' ? 'ONAYLI / HAZIRLANIYOR' : (data?.status === 'cancelled' ? 'İPTAL EDİLDİ' : 'BEKLEMEDE')) }}
                            </td>
                            <td class="border border-slate-900 font-bold p-1.5">Toplam Sevk Durumu:</td>
                            <td class="border border-slate-900 p-1.5 font-mono">
                                {{ getOrderShippedQty(data) }} / {{ getOrderTotalQty(data) }} kg (%{{ getOrderProgressPercent(data) }})
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-3">
                <h3 class="text-xs font-bold text-teal-800 uppercase tracking-wider mb-1">
                    SİPARİŞ EDİLEN ÜRÜN KALEMLERİ VE MALİYET DÖKÜMÜ
                </h3>
                <table class="w-full border-collapse border border-slate-900 text-xs text-left">
                    <thead class="text-slate-900 font-bold">
                        <tr>
                            <th class="border border-slate-900 p-1.5 text-center w-10">Sıra</th>
                            <th class="border border-slate-900 p-1.5">Ürün Cinsi ve Çeşidi</th>
                            <th class="border border-slate-900 p-1.5">Paketleme & Ambalaj Şekli</th>
                            <th class="border border-slate-900 p-1.5 text-right">Miktar (Kg)</th>
                            <th class="border border-slate-900 p-1.5 text-right">Birim Satış Fiyatı</th>
                            <th class="border border-slate-900 p-1.5 text-right">Kalem Tutarı (₺)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, idx) in data?.items" :key="item.id">
                            <td class="border border-slate-900 p-1.5 text-center font-mono">{{ idx + 1 }}</td>
                            <td class="border border-slate-900 p-1.5 font-bold">{{ item.product?.name || 'Tarımsal Ürün' }}</td>
                            <td class="border border-slate-900 p-1.5">{{ item.packaging?.name || 'Standart Kasa' }}</td>
                            <td class="border border-slate-900 p-1.5 text-right font-mono font-bold">{{ parseFloat(item.quantity || 0).toLocaleString('tr-TR') }} kg</td>
                            <td class="border border-slate-900 p-1.5 text-right font-mono">₺{{ parseFloat(item.unit_price || 0).toFixed(2) }}</td>
                            <td class="border border-slate-900 p-1.5 text-right font-mono font-bold">
                                ₺{{ parseFloat(item.total_price || (item.quantity * item.unit_price)).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                            </td>
                        </tr>
                        <tr class="font-black">
                            <td colspan="4" class="border border-slate-900 p-2 text-right uppercase">HESAPLANAN GENEL TOPLAM SİPARİŞ BEDELİ:</td>
                            <td colspan="2" class="border border-slate-900 p-2 text-right font-mono text-sm text-slate-950 font-black">
                                ₺{{ parseFloat(data?.total_amount || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-3">
                <h3 class="text-xs font-bold text-teal-800 uppercase tracking-wider mb-1">
                    AÇIKLAMA VE SEVKİYAT TALİMATLARI
                </h3>
                <div class="border border-slate-900 p-2 text-xs min-h-[38px]">
                    {{ data?.notes || 'Özel sevkiyat veya ambalaj şartı belirtilmemiştir. SASA Tarım standart kalite ve hijyen koşullarında teslim edilecektir.' }}
                </div>
            </div>

            <div class="mb-4">
                <h3 class="text-xs font-bold text-teal-800 uppercase tracking-wider mb-1.5">
                    YETKİLİLER (ONAY VE İMZA)
                </h3>
                <div class="grid grid-cols-3 gap-3">
                    <div class="border border-dashed border-slate-700 p-2 text-center flex flex-col justify-between h-20">
                        <span class="text-[10px] font-bold text-slate-900 uppercase">MÜŞTERİ / TESLİM ALAN</span>
                        <span class="text-[9px] text-slate-600">{{ data?.contact_person || data?.trading_party?.name }}</span>
                        <div class="text-[8px] text-slate-400 uppercase">İmza / Kaşe</div>
                    </div>
                    <div class="border border-dashed border-slate-700 p-2 text-center flex flex-col justify-between h-20">
                        <span class="text-[10px] font-bold text-slate-900 uppercase">SEVKİYAT & DEPO SORUMLUSU</span>
                        <span class="text-[9px] text-slate-600">SASA Tarım Operasyon</span>
                        <div class="text-[8px] text-slate-400 uppercase">İmza / Kaşe</div>
                    </div>
                    <div class="border border-dashed border-slate-700 p-2 text-center flex flex-col justify-between h-20">
                        <span class="text-[10px] font-bold text-slate-900 uppercase">SASA TARIM GENEL YÖNETİM</span>
                        <span class="text-[9px] text-slate-600">Müdürlük Onayı</span>
                        <div class="text-[8px] text-slate-400 uppercase">İmza / Mühür</div>
                    </div>
                </div>
            </div>

            <div class="pt-2 border-t-2 border-slate-900 flex flex-col items-center justify-center text-center">
                <div class="flex items-center justify-center gap-0.5 h-6 mb-0.5">
                    <div v-for="n in 56" :key="n" :class="n % 3 === 0 ? 'w-1 bg-black' : (n % 2 === 0 ? 'w-0.5 bg-black' : 'w-1.5 bg-transparent h-full inline-block')"></div>
                </div>
                <div class="font-mono text-[9px] font-bold tracking-widest text-slate-900">
                    20268250475276849a4
                </div>
                <p class="text-[8.5px] text-slate-600 mt-1 max-w-xl leading-tight">
                    Bu belgenin aslına ilişkin sorgulama https://ebelge.sasaerp.com/dogrulama/ORD-{{ String(data?.id).padStart(4, '0') }} internet adresinden yapılabilir.<br />
                    İşbu rapor 5070 sayılı Elektronik İmza Kanunu uyarınca güvenli elektronik imza ile üretilmiştir.
                </p>
                <div class="text-[8.5px] font-mono text-slate-400 mt-0.5">
                    1 / 1
                </div>
            </div>

            <div class="no-print flex justify-between items-center pt-4 mt-4 border-t border-slate-200">
                <button type="button" @click="emit('close')" class="px-4 py-2 text-xs border rounded-xl font-bold hover:bg-slate-100 transition">Kapat</button>
                <button type="button" @click="emit('print')" class="px-5 py-2 text-xs bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition flex items-center gap-2 shadow-sm">
                    Resmi Rapor Çıktısı Al (A4 Yazdır)
                </button>
            </div>
        </div>
    </div>
</template>
