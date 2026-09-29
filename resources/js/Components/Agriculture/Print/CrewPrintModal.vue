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
                    <h2 class="text-xs font-semibold text-slate-600 uppercase tracking-wide">TARIMSAL SAHA VE İŞGÜCÜ YÖNETİMİ</h2>
                    <div class="text-xs font-bold text-slate-900 uppercase tracking-widest mt-0.5">GÜNLÜK ÇAVUŞ HAKEDİŞ VE PUANTAJ FORMU</div>
                </div>

                <div class="border border-slate-900 p-2 text-right text-[10px] font-mono leading-tight bg-slate-50 min-w-[130px]">
                    <div><strong>BELGE NO:</strong> #DW-{{ String(data?.sheet?.id).padStart(4, '0') }}</div>
                    <div><strong>TARİH:</strong> {{ formatDisplayDate(data?.sheet?.work_date) }}</div>
                    <div class="text-[9px] text-slate-500 font-sans mt-0.5">SASA ERP Belgesi</div>
                </div>
            </div>

            <div class="mb-4">
                <h3 class="text-xs font-black text-teal-800 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                    <span>■</span> ÇAVUŞ VE TAŞERON KİMLİK BİLGİLERİ
                </h3>
                <table class="w-full border-collapse border border-slate-900 text-xs">
                    <tbody>
                        <tr>
                            <td class="border border-slate-900 bg-slate-100 font-bold p-1.5 w-1/4">Çavuş Adı Soyadı:</td>
                            <td class="border border-slate-900 p-1.5 w-1/4 font-bold">{{ data?.crew?.crew_leader?.first_name }} {{ data?.crew?.crew_leader?.last_name }}</td>
                            <td class="border border-slate-900 bg-slate-100 font-bold p-1.5 w-1/4">T.C. Kimlik No:</td>
                            <td class="border border-slate-900 p-1.5 w-1/4 font-mono">{{ data?.crew?.crew_leader?.identity_number || '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-900 bg-slate-100 font-bold p-1.5">Memleket / İl:</td>
                            <td class="border border-slate-900 p-1.5">{{ data?.crew?.crew_leader?.origin_city || '-' }}</td>
                            <td class="border border-slate-900 bg-slate-100 font-bold p-1.5">Telefon Numarası:</td>
                            <td class="border border-slate-900 p-1.5 font-mono">{{ data?.crew?.crew_leader?.phone || '-' }}</td>
                        </tr>
                        <tr>
                            <td class="border border-slate-900 bg-slate-100 font-bold p-1.5">Çalışılan Tesis:</td>
                            <td class="border border-slate-900 p-1.5 font-bold">{{ data?.sheet?.production_location?.name || 'SASA Tarım Tesisi' }}</td>
                            <td class="border border-slate-900 bg-slate-100 font-bold p-1.5">Çalışma Tarihi:</td>
                            <td class="border border-slate-900 p-1.5 font-mono">{{ formatDisplayDate(data?.sheet?.work_date) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-4">
                <h3 class="text-xs font-black text-teal-800 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                    <span>■</span> GÜNLÜK İŞÇİ DAĞILIMI VE HAKEDİŞ HESAPLAMA TABLOSU
                </h3>
                <table class="w-full border-collapse border border-slate-900 text-xs text-left">
                    <thead class="bg-slate-100 text-slate-900 font-bold">
                        <tr>
                            <th class="border border-slate-900 p-2">Çalışma Kalemi</th>
                            <th class="border border-slate-900 p-2 text-center">Birim / Kişi</th>
                            <th class="border border-slate-900 p-2 text-center">Katsayı / Saat</th>
                            <th class="border border-slate-900 p-2 text-right">Birim Yevmiye (₺)</th>
                            <th class="border border-slate-900 p-2 text-right">Hakediş Tutarı (₺)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-slate-900 p-2 font-bold">Yevmiyeli Tarım İşçisi</td>
                            <td class="border border-slate-900 p-2 text-center font-mono font-bold">{{ data?.crew?.worker_count }} Kişi</td>
                            <td class="border border-slate-900 p-2 text-center font-mono">1 Gün</td>
                            <td class="border border-slate-900 p-2 text-right font-mono">₺{{ data?.crew?.crew_leader?.daily_wage || 0 }}</td>
                            <td class="border border-slate-900 p-2 text-right font-mono font-bold">
                                ₺{{ ((data?.crew?.worker_count || 0) * (data?.crew?.crew_leader?.daily_wage || 0)).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                            </td>
                        </tr>
                        <tr>
                            <td class="border border-slate-900 p-2 font-bold">Çavuş Primi & Servis / Araç Payı</td>
                            <td class="border border-slate-900 p-2 text-center font-mono">{{ data?.crew?.car_count || 0 }} Araç</td>
                            <td class="border border-slate-900 p-2 text-center font-mono">{{ data?.crew?.crew_leader?.multiplier || 1 }}x Katsayı</td>
                            <td class="border border-slate-900 p-2 text-right font-mono">-</td>
                            <td class="border border-slate-900 p-2 text-right font-mono font-bold">Dahil</td>
                        </tr>
                        <tr v-if="data?.crew?.overtime_hours > 0">
                            <td class="border border-slate-900 p-2 font-bold">Fazla Mesai</td>
                            <td class="border border-slate-900 p-2 text-center font-mono">{{ data?.crew?.worker_count }} Kişi</td>
                            <td class="border border-slate-900 p-2 text-center font-mono">{{ data?.crew?.overtime_hours }} Saat</td>
                            <td class="border border-slate-900 p-2 text-right font-mono">-</td>
                            <td class="border border-slate-900 p-2 text-right font-mono font-bold">Hesaba Eklendi</td>
                        </tr>
                        <tr class="bg-slate-50 font-black">
                            <td colspan="4" class="border border-slate-900 p-2 text-right uppercase">ÇAVUŞA ÖDENECEK TOPLAM NET HAKEDİŞ:</td>
                            <td class="border border-slate-900 p-2 text-right font-mono text-sm text-emerald-700">
                                ₺{{ parseFloat(data?.crew?.calculated_wage_total || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-4">
                <h3 class="text-xs font-black text-teal-800 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                    <span>■</span> PUANTAJ VE HAKEDİŞ NOTLARI
                </h3>
                <div class="border border-slate-900 p-2.5 text-xs bg-slate-50 min-h-[40px]">
                    Hesaplanan tutara günlük yevmiyeler, çavuş organizasyon primi, işçi nakliye/servis bedeli ve saha mesaisi dahildir.
                </div>
            </div>

            <div class="mb-5">
                <h3 class="text-xs font-black text-teal-800 uppercase tracking-wider mb-2 flex items-center gap-1">
                    <span>■</span> YETKİLİ ONAY VE İMZA BLOKLARI
                </h3>
                <div class="grid grid-cols-3 gap-3">
                    <div class="border border-slate-900 p-2.5 text-center flex flex-col justify-between h-24 bg-slate-50/50">
                        <span class="text-[11px] font-bold text-slate-800">ÇAVUŞ (TESLİM ALAN)</span>
                        <span class="text-[10px] text-slate-600">{{ data?.crew?.crew_leader?.first_name }} {{ data?.crew?.crew_leader?.last_name }}</span>
                        <div class="border-t border-slate-400 pt-1 text-[9px] text-slate-500 uppercase">İmza / Tarih</div>
                    </div>
                    <div class="border border-slate-900 p-2.5 text-center flex flex-col justify-between h-24 bg-slate-50/50">
                        <span class="text-[11px] font-bold text-slate-800">SAHA ŞEFİ / KONTROLÖR</span>
                        <span class="text-[10px] text-slate-600">Saha Denetim</span>
                        <div class="border-t border-slate-400 pt-1 text-[9px] text-slate-500 uppercase">İmza / Tarih</div>
                    </div>
                    <div class="border border-slate-900 p-2.5 text-center flex flex-col justify-between h-24 bg-slate-50/50">
                        <span class="text-[11px] font-bold text-slate-800">SASA TARIM GENEL YÖNETİM</span>
                        <span class="text-[10px] text-slate-600">Müdürlük Onayı</span>
                        <div class="border-t border-slate-400 pt-1 text-[9px] text-slate-500 uppercase">İmza / Mühür / Tarih</div>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t-2 border-slate-900 flex flex-col items-center justify-center text-center">
                <div class="flex items-center justify-center gap-0.5 h-7 mb-1">
                    <div v-for="n in 48" :key="n" :class="n % 3 === 0 ? 'w-1 bg-black' : (n % 2 === 0 ? 'w-0.5 bg-black' : 'w-1 bg-transparent h-full inline-block')"></div>
                </div>
                <div class="font-mono text-[10px] font-bold tracking-widest text-slate-900">
                    SASA-2026-LAB-{{ String(data?.sheet?.id).padStart(6, '0') }}-TR
                </div>
                <p class="text-[9px] text-slate-600 mt-1 max-w-xl">
                    Bu puantaj ve hakediş bordrosu SASA Tarım ERP sistemi tarafından elektronik ortamda onaylanmıştır.
                </p>
                <div class="text-[9px] font-mono text-slate-400 mt-1">
                    Sayfa 1 / 1
                </div>
            </div>

            <div class="no-print flex justify-between items-center pt-4 mt-4 border-t border-slate-200">
                <button type="button" @click="emit('close')" class="px-4 py-2 text-xs border rounded-xl font-bold hover:bg-slate-100 transition">Kapat</button>
                <button type="button" @click="emit('print')" class="px-5 py-2 text-xs bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition flex items-center gap-2 shadow-sm">
                    Resmi Bordro Çıktısı Al (A4 Yazdır)
                </button>
            </div>
        </div>
    </div>
</template>
