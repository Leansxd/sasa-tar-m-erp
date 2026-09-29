<script setup lang="ts">
const props = defineProps<{
    show: boolean;
    data: any;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'print', elementId: string): void;
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
        <div id="tank-print-document" class="print-document bg-white text-slate-900 rounded-none shadow-none w-full max-w-4xl p-8 print:p-0 font-sans border print:border-none border-slate-300">
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
                    <h2 class="text-xs font-semibold text-slate-600 uppercase tracking-wide">GÜBRELEME VE BESLEME ÜNİTESİ</h2>
                    <div class="text-xs font-bold text-slate-900 uppercase tracking-widest mt-0.5">TANK SOLÜSYON HAZIRLAMA REÇETESİ (SAHA TALİMAT DÖKÜMÜ)</div>
                </div>

                <div class="border border-slate-900 p-2 text-right text-[10px] font-mono leading-tight bg-slate-50 min-w-[130px]">
                    <div><strong>REÇETE NO:</strong> #RCP-{{ String(data?.recipe?.id || '01').padStart(4, '0') }}</div>
                    <div><strong>DÖKÜM:</strong> {{ formatDisplayDate(new Date().toISOString()) }}</div>
                    <div class="text-[9px] text-slate-500 font-sans mt-0.5">SASA ERP Belgesi</div>
                </div>
            </div>

            <div class="mb-4">
                <h3 class="text-xs font-black text-teal-800 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                    <span>■</span> TANK VE UYGULAMA BİLGİLERİ
                </h3>
                <table class="w-full border-collapse border border-slate-900 text-xs">
                    <tbody>
                        <tr>
                            <td class="border border-slate-900 bg-slate-100 font-bold p-1.5 w-1/4">Gübre Reçetesi Adı:</td>
                            <td class="border border-slate-900 p-1.5 w-1/4 font-bold">{{ data?.recipe?.name || 'Genel Besleme Reçetesi' }}</td>
                            <td class="border border-slate-900 bg-slate-100 font-bold p-1.5 w-1/4">Hedef Su Değerleri:</td>
                            <td class="border border-slate-900 p-1.5 w-1/4 font-mono font-bold">pH: {{ data?.recipe?.water_ph || '6.0' }} | EC: {{ data?.recipe?.water_ec || '1.8' }} mS/cm</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-900 bg-slate-100 font-bold p-1.5">Hazırlanacak Tank:</td>
                            <td class="border border-slate-900 p-1.5 font-black text-indigo-900">{{ data?.tank?.tank_name }}</td>
                            <td class="border border-slate-900 bg-slate-100 font-bold p-1.5">Tank Kapasitesi:</td>
                            <td class="border border-slate-900 p-1.5 font-bold font-mono">{{ data?.tank?.capacity_liters }} Litre Su</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-900 bg-slate-100 font-bold p-1.5">Uygulama / Bitiş Şartı:</td>
                            <td class="border border-slate-900 p-1.5 font-bold">{{ data?.run?.end_condition || data?.recipe?.duration_condition || 'Standart Program' }}</td>
                            <td class="border border-slate-900 bg-slate-100 font-bold p-1.5">Aktif Durum:</td>
                            <td class="border border-slate-900 p-1.5 font-bold text-emerald-700">● Sahada Aktif Uygulama</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-4">
                <h3 class="text-xs font-black text-teal-800 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                    <span>■</span> TANKA KONULACAK GÜBRE VE SOLÜSYON İÇERİKLERİ
                </h3>
                <table class="w-full border-collapse border border-slate-900 text-xs text-left">
                    <thead class="bg-slate-100 text-slate-900 font-bold">
                        <tr>
                            <th class="border border-slate-900 p-2 text-center w-10">#</th>
                            <th class="border border-slate-900 p-2">Gübre / Kimyasal Ürün</th>
                            <th class="border border-slate-900 p-2">Marka / Üretici</th>
                            <th class="border border-slate-900 p-2 text-center">Konulacak Net Miktar</th>
                            <th class="border border-slate-900 p-2">Kullanım Amacı</th>
                            <th class="border border-slate-900 p-2">Saha Notu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(it, idx) in data?.tank?.items" :key="idx">
                            <td class="border border-slate-900 p-2 font-mono font-bold text-center">{{ idx + 1 }}</td>
                            <td class="border border-slate-900 p-2 font-extrabold text-slate-900">{{ it.product_name }}</td>
                            <td class="border border-slate-900 p-2">{{ it.brand || '-' }}</td>
                            <td class="border border-slate-900 p-2 font-black text-center text-indigo-900 bg-slate-50 font-mono text-sm">{{ it.quantity }} {{ it.unit }}</td>
                            <td class="border border-slate-900 p-2 font-semibold">{{ it.usage_purpose || '-' }}</td>
                            <td class="border border-slate-900 p-2 text-[11px] text-slate-600">{{ it.description || '-' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-4">
                <h3 class="text-xs font-black text-teal-800 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                    <span>■</span> SAHA HAZIRLAMA VE İŞ GÜVENLİĞİ TALİMATLARI
                </h3>
                <div class="p-3 bg-amber-50 rounded-lg border border-amber-300 text-xs space-y-1 text-amber-900">
                    <div class="font-bold uppercase tracking-wider">Dikkat Edilecek Hususlar:</div>
                    <ol class="list-decimal list-inside text-[11px] space-y-0.5">
                        <li>Tankı önce en az <strong>%80 oranında temiz su</strong> ile doldurunuz.</li>
                        <li>Listede belirtilen gübreleri yukarıdaki gramajlara göre kalibre edilmiş <strong>hassas terazide</strong> tartarak ekleyiniz.</li>
                        <li>Tüm gübreler tamamen homojen çözünene kadar karıştırıcı mikseri en az <strong>15 dakika</strong> aralıksız çalıştırınız.</li>
                        <li>Kimyasal ve konsantre gübre temasını önlemek için <strong>koruyucu eldiven, önlük ve maske</strong> takılması zorunludur.</li>
                    </ol>
                </div>
            </div>

            <div class="mb-4">
                <h3 class="text-xs font-black text-teal-800 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                    <span>■</span> YETKİLİ ONAY VE İMZA BLOKLARI
                </h3>
                <div class="grid grid-cols-3 gap-3">
                    <div class="border border-slate-900 p-2.5 text-center flex flex-col justify-between h-24 bg-slate-50/50">
                        <span class="text-[11px] font-bold text-slate-800 uppercase">HAZIRLAYAN GÖREVLİ</span>
                        <span class="text-[10px] text-slate-600">Saha Personeli</span>
                        <div class="border-t border-slate-400 pt-1 text-[9px] text-slate-500 uppercase">İmza / Tarih</div>
                    </div>
                    <div class="border border-slate-900 p-2.5 text-center flex flex-col justify-between h-24 bg-slate-50/50">
                        <span class="text-[11px] font-bold text-slate-800 uppercase">KONTROL & MÜHENDİS</span>
                        <span class="text-[10px] text-slate-600">Ziraat Mühendisi</span>
                        <div class="border-t border-slate-400 pt-1 text-[9px] text-slate-500 uppercase">İmza & Kaşe</div>
                    </div>
                    <div class="border border-slate-900 p-2.5 text-center flex flex-col justify-between h-24 bg-slate-50/50">
                        <span class="text-[11px] font-bold text-slate-800 uppercase">TESİS YÖNETİMİ</span>
                        <span class="text-[10px] text-slate-600">Operasyon Onayı</span>
                        <div class="border-t border-slate-400 pt-1 text-[9px] text-slate-500 uppercase">İmza & Mühür</div>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t-2 border-slate-900 flex flex-col items-center justify-center text-center">
                <div class="flex items-center justify-center gap-0.5 h-6 mb-1">
                    <div v-for="n in 52" :key="n" :class="n % 3 === 0 ? 'w-1 bg-black' : (n % 2 === 0 ? 'w-0.5 bg-black' : 'w-1 bg-transparent h-full inline-block')"></div>
                </div>
                <div class="font-mono text-[9px] font-bold tracking-widest text-slate-900">
                    SASA-TANK-2026-{{ String(data?.tank?.id || '001').padStart(4, '0') }}-RCP
                </div>
                <p class="text-[8.5px] text-slate-600 mt-1 max-w-xl">
                    İşbu reçete formu SASA Tarım ERP sistemi tarafından sahada gübreleme ve sulama yönetimi için üretilmiştir.
                </p>
                <div class="text-[8.5px] font-mono text-slate-400 mt-0.5">
                    Sayfa 1 / 1
                </div>
            </div>

            <div class="no-print flex justify-between items-center pt-4 mt-4 border-t border-slate-200">
                <button type="button" @click="emit('close')" class="px-4 py-2 text-xs border rounded-xl font-bold hover:bg-slate-100 transition">Kapat</button>
                <button type="button" @click="emit('print', 'tank-print-document')" class="px-5 py-2 text-xs bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition flex items-center gap-2 shadow-sm">
                    Çalışan Talimat Belgesini Yazdır (A4)
                </button>
            </div>
        </div>
    </div>
</template>
