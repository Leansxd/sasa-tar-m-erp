<script setup lang="ts">
const props = defineProps<{
    show: boolean;
    type: string;
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
                    <h2 class="text-xs font-semibold text-slate-600 uppercase tracking-wide">
                        <span v-if="type === 'gubreleme'">GÜBRELEME VE BESLEME BİRİMİ</span>
                        <span v-else-if="type === 'sulama'">SULAMA OTOMASYON BİRİMİ</span>
                        <span v-else-if="type === 'ilaclama'">BİTKİ KORUMA VE İLAÇLAMA BİRİMİ</span>
                        <span v-else-if="type === 'su_analizi'">SU ANALİZ LABORATUVARI</span>
                        <span v-else-if="type === 'kaynak_suyu_kontrol'">KAYNAK SUYU KONTROL BİRİMİ</span>
                        <span v-else-if="type === 'aritma_suyu_kontrol'">ARITMA TESİSİ BİRİMİ</span>
                        <span v-else-if="type === 'is_planlama'">İŞ PLANLAMA VE OPERASYON YÖNETİMİ</span>
                    </h2>
                    <div class="text-xs font-bold text-slate-900 uppercase tracking-widest mt-0.5">RESMİ BİRİM VE SÜREÇ RAPORU</div>
                </div>

                <div class="border border-slate-900 p-2 text-right text-[10px] font-mono leading-tight bg-slate-50 min-w-[130px]">
                    <div><strong>BELGE NO:</strong> #DOC-{{ String(data?.id || '001').padStart(4, '0') }}</div>
                    <div><strong>TARİH:</strong> {{ formatDisplayDate(new Date().toISOString()) }}</div>
                    <div class="text-[9px] text-slate-500 font-sans mt-0.5">SASA ERP Raporu</div>
                </div>
            </div>

            <div class="mb-4 min-h-[260px]">
                <h3 class="text-xs font-black text-teal-800 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                    <span>■</span> SİSTEM KAYIT VE OPERASYON DETAYLARI
                </h3>
                <table class="w-full border-collapse border border-slate-900 text-xs text-left">
                    <tbody>
                        <template v-if="type === 'gubreleme'">
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2 w-1/3">Uygulanan Tesis:</td><td class="border border-slate-900 p-2 font-bold">{{ data?.production_location?.name || 'Tesis Belirtilmedi' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Reçete Adı:</td><td class="border border-slate-900 p-2 font-black text-indigo-900">{{ data?.recipe?.name || '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Başlangıç Tarihi:</td><td class="border border-slate-900 p-2 font-mono">{{ formatDisplayDate(data?.start_date) }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Hedef Parametreler:</td><td class="border border-slate-900 p-2 font-mono font-bold">pH: {{ data?.recipe?.water_ph || '6.0' }} | EC: {{ data?.recipe?.water_ec || '1.8' }} mS/cm</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Bitiş Şartı:</td><td class="border border-slate-900 p-2">{{ data?.end_condition || '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Uygulama Durumu:</td><td class="border border-slate-900 p-2 font-bold text-emerald-700">{{ data?.is_active ? '● Sahada Aktif Uygulanıyor' : '○ Pasif / Tamamlandı' }}</td></tr>
                        </template>
                        
                        <template v-else-if="type === 'sulama'">
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2 w-1/3">Tesis / Lokasyon:</td><td class="border border-slate-900 p-2 font-bold">{{ data?.production_location?.name || data?.location?.name || '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Program Tarihi / Saati:</td><td class="border border-slate-900 p-2 font-mono font-bold">{{ formatDisplayDate(data?.schedule_date) }} - {{ data?.start_time }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Sulama Modu / Tipi:</td><td class="border border-slate-900 p-2 font-bold">{{ data?.is_fertilized ? 'Besinli / Gübreli Sulama' : 'Düz / Boş Sulama' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Aktif Vanalar & Süreler:</td><td class="border border-slate-900 p-2 font-mono">{{ (data?.valves || []).map((v:any) => `${v.name || 'Vana'}: ${v.duration_minutes}dk`).join(', ') || 'Belirtilmedi' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Tank / Saha Notu:</td><td class="border border-slate-900 p-2">{{ data?.tank_stage_note || data?.notes || '-' }}</td></tr>
                        </template>
                        
                        <template v-else-if="type === 'ilaclama'">
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2 w-1/3">Uygulama Tarihi / Parti Kodu:</td><td class="border border-slate-900 p-2 font-mono font-bold">{{ formatDisplayDate(data?.application_date) }} | {{ data?.batch_code || '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">İlaç Reçetesi Adı:</td><td class="border border-slate-900 p-2 font-bold text-indigo-900">{{ data?.recipe?.name || '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Kullanım Amacı / Hedef Zararlı:</td><td class="border border-slate-900 p-2 font-semibold">{{ data?.purpose || '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Uygulama Yapılan Alan:</td><td class="border border-slate-900 p-2">{{ data?.covered_area_description || '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Uygulayan Personel:</td><td class="border border-slate-900 p-2 font-bold">{{ data?.applied_by ? (data.applied_by.first_name + ' ' + data.applied_by.last_name) : '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Tank / Solüsyon Durumu:</td><td class="border border-slate-900 p-2 font-bold">{{ data?.is_tank_finished ? 'Tank Tamamlandı' : 'Ertesi Gün Devam Edilecek' }}</td></tr>
                        </template>
                        
                        <template v-else-if="type === 'su_analizi'">
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2 w-1/3">Analiz Edilen Su Kaynağı:</td><td class="border border-slate-900 p-2 font-black text-indigo-900">{{ data?.water_source?.name || '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Numune / Analiz Tarihi:</td><td class="border border-slate-900 p-2 font-mono font-bold">{{ formatDisplayDate(data?.analysis_date) }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Laboratuvar / Rapor No:</td><td class="border border-slate-900 p-2 font-bold">{{ data?.chemical_details?.lab_name || 'SASA Akredite Analiz Laboratuvarı' }} | <span class="font-mono">{{ data?.chemical_details?.report_no || ('#LAB-' + data?.id) }}</span></td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Temel Fizikokimyasal:</td><td class="border border-slate-900 p-2 font-mono font-black text-sm">pH: {{ data?.ph_level }} | EC: {{ data?.ec_level }} mS/cm | TDS: {{ data?.chemical_details?.tds || '-' }} ppm | Sertlik: {{ data?.chemical_details?.hardness || '-' }} °F | SAR: {{ data?.chemical_details?.sar || '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Katyonlar (Ca, Mg, Na, K):</td><td class="border border-slate-900 p-2 font-mono">Ca: {{ data?.chemical_details?.calcium || '-' }} mg/L | Mg: {{ data?.chemical_details?.magnesium || '-' }} mg/L | Na: {{ data?.chemical_details?.sodium || '-' }} mg/L | K: {{ data?.chemical_details?.potassium || '-' }} mg/L</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Anyonlar (HCO3, SO4, Cl, NO3):</td><td class="border border-slate-900 p-2 font-mono">HCO₃: {{ data?.chemical_details?.bicarbonate || '-' }} mg/L | SO₄: {{ data?.chemical_details?.sulfate || '-' }} mg/L | Cl: {{ data?.chemical_details?.chloride || '-' }} mg/L | NO₃: {{ data?.chemical_details?.nitrate || '-' }} mg/L</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Zirai Sulama Uygunluğu:</td><td class="border border-slate-900 p-2 font-bold text-emerald-700">● {{ data?.chemical_details?.suitability || 'Sulamaya Uygun (A Sınıfı)' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Laboratuvar / Mühendis Yorumu:</td><td class="border border-slate-900 p-2">{{ data?.notes || 'Su analiz değerleri standart tarımsal besleme normlarına uygundur.' }}</td></tr>
                        </template>
                        
                        <template v-else-if="type === 'kaynak_suyu_kontrol'">
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2 w-1/3">Kaynak / Pompa İstasyonu:</td><td class="border border-slate-900 p-2 font-black text-indigo-900">{{ data?.water_source?.name || '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Kontrol ve Ölçüm Tarihi:</td><td class="border border-slate-900 p-2 font-mono font-bold">{{ formatDisplayDate(data?.control_date) }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Depo & Klor Seviyeleri:</td><td class="border border-slate-900 p-2 font-mono">Su Tankı: %{{ data?.water_tank_level }} | Klor Tankı: %{{ data?.chlorine_tank_level }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Dozaj Pompası Çalışma Modu:</td><td class="border border-slate-900 p-2 font-bold">{{ data?.dosing_pump_mode === 'auto' ? 'Otomatik Otomasyon Modu' : 'Manuel Müdahale Modu' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Anlık pH ve EC Ölçümü:</td><td class="border border-slate-900 p-2 font-mono font-bold">pH: {{ data?.ph_val }} | EC: {{ data?.ec_val }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Filtre & Pompa Durumu:</td><td class="border border-slate-900 p-2 font-bold">{{ data?.is_filter_cleaned ? 'Filtre Temiz' : 'Filtre Bakımı Gerekli' }} | Pompa: {{ data?.pump_status === 'open' ? 'Açık / Çalışıyor' : 'Kapalı' }}</td></tr>
                        </template>
                        
                        <template v-else-if="type === 'aritma_suyu_kontrol'">
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2 w-1/3">Arıtma Ünitesi / Kuyu:</td><td class="border border-slate-900 p-2 font-black text-indigo-900">{{ data?.water_source?.name || '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Kontrol Tarihi:</td><td class="border border-slate-900 p-2 font-mono font-bold">{{ formatDisplayDate(data?.control_date) }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Giriş / Çıkış Basınçları:</td><td class="border border-slate-900 p-2 font-mono font-bold">Giriş: {{ data?.inlet_pressure_bar }} bar | Çıkış: {{ data?.outlet_pressure_bar }} bar</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Fark Basıncı (&Delta;P):</td><td class="border border-slate-900 p-2 font-mono font-black text-sm">{{ data?.delta_pressure_bar }} bar (İzin Verilen Eşik: {{ data?.max_threshold_bar }} bar)</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Filtre Durum Değerlendirmesi:</td><td class="border border-slate-900 p-2 font-bold" :class="data?.has_warning ? 'text-rose-700' : 'text-emerald-700'">{{ data?.has_warning ? '⚠️ UYARI: Filtre Tıkalı - Geri Yıkama Gerekli' : '✓ Filtre Geçirgenliği Normal' }}</td></tr>
                        </template>
                        
                        <template v-else-if="type === 'is_planlama'">
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2 w-1/3">Görev Başlığı / İş Türü:</td><td class="border border-slate-900 p-2 font-black text-indigo-900">{{ data?.title }} | {{ data?.job_type?.name || '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">İş Durumu:</td><td class="border border-slate-900 p-2 font-bold">{{ data?.status === 'completed' ? '✓ Tamamlandı' : (data?.status === 'in_progress' ? '⏳ Devam Ediyor' : '⏱ Beklemede') }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Uygulama Tesisi:</td><td class="border border-slate-900 p-2 font-bold">{{ data?.production_location?.name || '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Sorumlu Personel:</td><td class="border border-slate-900 p-2 font-bold">{{ data?.assigned_personnel ? (data.assigned_personnel.first_name + ' ' + data.assigned_personnel.last_name) : '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Plan / Hedef Tarihleri:</td><td class="border border-slate-900 p-2 font-mono">{{ data?.plan_date }} - {{ data?.due_date || '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Yönetim / Görev Talimatı:</td><td class="border border-slate-900 p-2">{{ data?.description || '-' }}</td></tr>
                            <tr><td class="border border-slate-900 bg-slate-100 font-bold p-2">Tamamlama / Süreç Notu:</td><td class="border border-slate-900 p-2">{{ data?.completion_notes || '-' }}</td></tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="mb-4">
                <h3 class="text-xs font-black text-teal-800 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                    <span>■</span> YETKİLİ ONAY VE İMZA BLOKLARI
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="border border-slate-900 p-2.5 text-center flex flex-col justify-between h-24 bg-slate-50/50">
                        <span class="text-[11px] font-bold text-slate-800 uppercase">RAPORU OLUŞTURAN</span>
                        <span class="text-[10px] text-slate-600">Sistem Yetkilisi</span>
                        <div class="border-t border-slate-400 pt-1 text-[9px] text-slate-500 uppercase">İmza / Tarih</div>
                    </div>
                    <div class="border border-slate-900 p-2.5 text-center flex flex-col justify-between h-24 bg-slate-50/50">
                        <span class="text-[11px] font-bold text-slate-800 uppercase">BİRİM SORUMLUSU ONAY</span>
                        <span class="text-[10px] text-slate-600">Ziraat Mühendisi / Yönetim</span>
                        <div class="border-t border-slate-400 pt-1 text-[9px] text-slate-500 uppercase">İmza & Mühür</div>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t-2 border-slate-900 flex flex-col items-center justify-center text-center">
                <div class="flex items-center justify-center gap-0.5 h-6 mb-1">
                    <div v-for="n in 52" :key="n" :class="n % 3 === 0 ? 'w-1 bg-black' : (n % 2 === 0 ? 'w-0.5 bg-black' : 'w-1 bg-transparent h-full inline-block')"></div>
                </div>
                <div class="font-mono text-[9px] font-bold tracking-widest text-slate-900">
                    SASA-DOC-2026-{{ String(data?.id || '001').padStart(6, '0') }}-GEN
                </div>
                <p class="text-[8.5px] text-slate-600 mt-1 max-w-xl">
                    İşbu resmi birim raporu SASA Tarım ERP sistemi tarafından elektronik ortamda üretilmiştir.
                </p>
                <div class="text-[8.5px] font-mono text-slate-400 mt-0.5">
                    Sayfa 1 / 1
                </div>
            </div>

            <div class="no-print flex justify-between items-center pt-4 mt-4 border-t border-slate-200">
                <button type="button" @click="emit('close')" class="px-4 py-2 text-xs border rounded-xl font-bold hover:bg-slate-100 transition">Kapat</button>
                <button type="button" @click="emit('print')" class="px-5 py-2 text-xs bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition flex items-center gap-2 shadow-sm">
                    Belge Çıktısını Al (A4 Yazdır)
                </button>
            </div>
        </div>
    </div>
</template>
