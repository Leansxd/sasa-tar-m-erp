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
                    <h2 class="text-xs font-semibold text-slate-600 uppercase tracking-wide">LOJİSTİK VE SEVKİYAT DİREKTÖRLÜĞÜ</h2>
                    <div class="text-xs font-bold text-slate-900 uppercase tracking-widest mt-0.5">RESMİ SEVK İRSALİYESİ VE TAŞIMA BELGESİ</div>
                </div>

                <div class="border border-slate-900 p-2 text-right text-[10px] font-mono leading-tight bg-slate-50 min-w-[130px]">
                    <div><strong>İRSALİYE:</strong> {{ data?.dia_waybill_code || ('IRS-2026-' + data?.id) }}</div>
                    <div><strong>TARİH:</strong> {{ formatDisplayDate(data?.shipment_date) }}</div>
                    <div class="text-[9px] text-slate-500 font-sans mt-0.5">SASA ERP Belgesi</div>
                </div>
            </div>

            <div class="mb-3">
                <h3 class="text-xs font-bold text-teal-800 uppercase tracking-wider mb-1">
                    GÖNDEREN VE ALICI CARİ BİLGİLERİ
                </h3>
                <table class="w-full border-collapse border border-slate-900 text-xs">
                    <tbody>
                        <tr>
                            <td class="border border-slate-900 font-bold p-1.5 w-1/4">Gönderen Firma:</td>
                            <td class="border border-slate-900 p-1.5 w-1/4 font-bold">SASA TARIM İŞLETMELERİ A.Ş.</td>
                            <td class="border border-slate-900 font-bold p-1.5 w-1/4">Sevkiyat Tarihi:</td>
                            <td class="border border-slate-900 p-1.5 w-1/4 font-mono">{{ formatDisplayDate(data?.shipment_date) }}</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-900 font-bold p-1.5">Alıcı Müşteri (Cari):</td>
                            <td class="border border-slate-900 p-1.5 font-bold">{{ data?.trading_party?.name }}</td>
                            <td class="border border-slate-900 font-bold p-1.5">Cari Kodu:</td>
                            <td class="border border-slate-900 p-1.5 font-mono">{{ data?.trading_party?.dia_cari_code || '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-900 font-bold p-1.5">Bağlı Sipariş No:</td>
                            <td class="border border-slate-900 p-1.5 font-mono">
                                {{ (data?.customer_order_id || data?.order_id) ? ('#ORD-' + String(data?.customer_order_id || data?.order_id).padStart(4, '0')) : 'Doğrudan Sevkiyat' }}
                            </td>
                            <td class="border border-slate-900 font-bold p-1.5">Teslimat Şekli:</td>
                            <td class="border border-slate-900 p-1.5">{{ data?.delivery_type?.name || 'Doğrudan Araç Teslim' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-3">
                <h3 class="text-xs font-bold text-teal-800 uppercase tracking-wider mb-1">
                    TAŞIYICI ARAÇ VE NAKLİYE BİLGİLERİ
                </h3>
                <table class="w-full border-collapse border border-slate-900 text-xs">
                    <tbody>
                        <tr>
                            <td class="border border-slate-900 font-bold p-1.5 w-1/4">Nakliye Araç Plakası:</td>
                            <td class="border border-slate-900 p-1.5 w-1/4 font-mono font-bold text-sm">{{ data?.vehicle_plate || '07 SASA 88' }}</td>
                            <td class="border border-slate-900 font-bold p-1.5 w-1/4">Şoför Adı Soyadı:</td>
                            <td class="border border-slate-900 p-1.5 w-1/4 font-bold">{{ data?.driver_name || '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-900 font-bold p-1.5">Şoför Telefon / İletişim:</td>
                            <td class="border border-slate-900 p-1.5 font-mono">{{ data?.driver_phone || '-' }}</td>
                            <td class="border border-slate-900 font-bold p-1.5">Sevkiyat Durumu:</td>
                            <td class="border border-slate-900 p-1.5 font-bold">
                                {{ data?.status === 'delivered' ? 'TESLİM EDİLDİ' : 'YOLDA' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-3">
                <h3 class="text-xs font-bold text-teal-800 uppercase tracking-wider mb-1">
                    SEVK EDİLEN MALIN CİNSİ, MİKTARI VE BEDELİ
                </h3>
                <table class="w-full border-collapse border border-slate-900 text-xs text-left">
                    <thead class="text-slate-900 font-bold">
                        <tr>
                            <th class="border border-slate-900 p-1.5 text-center w-10">Sıra</th>
                            <th class="border border-slate-900 p-1.5">Malın Cinsi ve Nevi</th>
                            <th class="border border-slate-900 p-1.5">Ambalaj & Kasa Şekli</th>
                            <th class="border border-slate-900 p-1.5 text-right">Net Sevk Miktarı</th>
                            <th class="border border-slate-900 p-1.5 text-right">Birim Fiyat</th>
                            <th class="border border-slate-900 p-1.5 text-right">İrsaliye Bedeli (₺)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-slate-900 p-1.5 text-center font-mono">1</td>
                            <td class="border border-slate-900 p-1.5 font-bold">{{ data?.product?.name || 'Tarımsal Ürün' }}</td>
                            <td class="border border-slate-900 p-1.5">{{ data?.packaging?.name || 'Standart Kasa' }}</td>
                            <td class="border border-slate-900 p-1.5 text-right font-mono font-bold">{{ parseFloat(data?.quantity || 0).toLocaleString('tr-TR') }} kg</td>
                            <td class="border border-slate-900 p-1.5 text-right font-mono">₺{{ parseFloat(data?.unit_price || 0).toFixed(2) }}</td>
                            <td class="border border-slate-900 p-1.5 text-right font-mono font-bold">
                                ₺{{ (parseFloat(data?.quantity || 0) * parseFloat(data?.unit_price || 0)).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                            </td>
                        </tr>
                        <tr class="font-black">
                            <td colspan="4" class="border border-slate-900 p-2 text-right uppercase">TOPLAM SEVKİYAT & İRSALİYE TUTARI:</td>
                            <td colspan="2" class="border border-slate-900 p-2 text-right font-mono text-sm text-slate-950 font-black">
                                ₺{{ (parseFloat(data?.quantity || 0) * parseFloat(data?.unit_price || 0)).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-3">
                <h3 class="text-xs font-bold text-teal-800 uppercase tracking-wider mb-1">
                    AÇIKLAMA VE NAKLİYE TALİMATLARI
                </h3>
                <div class="border border-slate-900 p-2 text-xs min-h-[38px]">
                    {{ data?.notes || 'Soğuk hava muhafazalı sevk edilmiştir. Mal tesliminde irsaliye karşılıklı imza altına alınacaktır.' }}
                </div>
            </div>

            <div class="mb-4">
                <h3 class="text-xs font-bold text-teal-800 uppercase tracking-wider mb-1.5">
                    YETKİLİLER (TESLİM VE TESELLÜM İMZA BLOKLARI)
                </h3>
                <div class="grid grid-cols-3 gap-3">
                    <div class="border border-dashed border-slate-700 p-2 text-center flex flex-col justify-between h-20">
                        <span class="text-[10px] font-bold text-slate-900 uppercase">TESLİM EDEN (DEPO)</span>
                        <span class="text-[9px] text-slate-600">SASA Tarım Depo Çıkış</span>
                        <div class="text-[8px] text-slate-400 uppercase">İmza / Kaşe</div>
                    </div>
                    <div class="border border-dashed border-slate-700 p-2 text-center flex flex-col justify-between h-20">
                        <span class="text-[10px] font-bold text-slate-900 uppercase">TAŞIYICI (ŞOFÖR)</span>
                        <span class="text-[9px] text-slate-600">{{ data?.driver_name || 'Şoför' }}</span>
                        <div class="text-[8px] text-slate-400 uppercase">İmza / Tarih</div>
                    </div>
                    <div class="border border-dashed border-slate-700 p-2 text-center flex flex-col justify-between h-20">
                        <span class="text-[10px] font-bold text-slate-900 uppercase">TESLİM ALAN (ALICI)</span>
                        <span class="text-[9px] text-slate-600">{{ data?.trading_party?.name }}</span>
                        <div class="text-[8px] text-slate-400 uppercase">İmza / Kaşe</div>
                    </div>
                </div>
            </div>

            <div class="pt-2 border-t-2 border-slate-900 flex flex-col items-center justify-center text-center">
                <div class="flex items-center justify-center gap-0.5 h-6 mb-0.5">
                    <div v-for="n in 56" :key="n" :class="n % 3 === 0 ? 'w-1 bg-black' : (n % 2 === 0 ? 'w-0.5 bg-black' : 'w-1.5 bg-transparent h-full inline-block')"></div>
                </div>
                <div class="font-mono text-[9px] font-bold tracking-widest text-slate-900">
                    20268250475276849b2
                </div>
                <p class="text-[8.5px] text-slate-600 mt-1 max-w-xl leading-tight">
                    İşbu sevk irsaliyesi ve taşıma belgesi SASA Tarım ERP sistemi tarafından elektronik ortamda tanzim edilmiştir.<br />
                    5070 sayılı Kanun uyarınca güvenli elektronik imza ile geçerlilik kazanmıştır.
                </p>
                <div class="text-[8.5px] font-mono text-slate-400 mt-0.5">
                    1 / 1
                </div>
            </div>

            <div class="no-print flex justify-between items-center pt-4 mt-4 border-t border-slate-200">
                <button type="button" @click="emit('close')" class="px-4 py-2 text-xs border rounded-xl font-bold hover:bg-slate-100 transition">Kapat</button>
                <button type="button" @click="emit('print')" class="px-5 py-2 text-xs bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition flex items-center gap-2 shadow-sm">
                    Resmi İrsaliye Çıktısı Al (A4 Yazdır)
                </button>
            </div>
        </div>
    </div>
</template>
