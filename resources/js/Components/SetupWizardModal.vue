<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps<{
    modelValue: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
}>();

const isOpen = computed({
    get: () => props.modelValue,
    set: (val: boolean) => emit('update:modelValue', val)
});

const savedStep = typeof window !== 'undefined' ? localStorage.getItem('sasa_setup_active_step') : null;
const currentStep = ref(savedStep ? Math.min(Math.max(Number(savedStep), 1), 8) : 1);
const isLoading = ref(false);
const saveFeedback = ref('');

watch(currentStep, (newStep) => {
    if (typeof window !== 'undefined') {
        localStorage.setItem('sasa_setup_active_step', String(newStep));
    }
});

const setupData = ref({
    companies: [] as any[],
    productionLocations: [] as any[],
    units: [] as any[],
    packagings: [] as any[],
    products: [] as any[],
    personnels: [] as any[],
    crewLeaders: [] as any[],
    workers: [] as any[],
    jobTypes: [] as any[],
    waterSources: [] as any[],
    filters: [] as any[],
    fertilizationRecipes: [] as any[],
    sprayingRecipes: [] as any[],
});

const steps = [
    { id: 1, title: 'Firma Tanımı', short: '1. Firma', desc: 'Şirket & Tüzel Kişilik' },
    { id: 2, title: 'Üretim Yeri & Vana', short: '2. Arsa/Parsel', desc: 'Sera, parsel ve vanalar' },
    { id: 3, title: 'Ölçü Birimleri', short: '3. Birimler', desc: 'kg, kasa, adet vb.' },
    { id: 4, title: 'Ürün & Kalite', short: '4. Ürünler', desc: 'Mahsul ve kalite sınıfları' },
    { id: 5, title: 'İş Gücü Kadrosu', short: '5. Personel', desc: 'Mühendis, çavuş ve işçiler' },
    { id: 6, title: 'İş Tipleri', short: '6. Görevler', desc: 'Hasat, budama iş formları' },
    { id: 7, title: 'Altyapı & Su', short: '7. Altyapı', desc: 'Su, filtre ve reçeteler' },
    { id: 8, title: 'Tamamlandı', short: '8. Başlat', desc: 'Kurulum Özeti' },
];

const isStepCompleted = (id: number) => {
    if (id === 1) return setupData.value.companies.length > 0;
    if (id === 2) return setupData.value.productionLocations.length > 0;
    if (id === 3) return setupData.value.units.length > 0;
    if (id === 4) return setupData.value.products.length > 0;
    if (id === 5) return setupData.value.personnels.length > 0 || setupData.value.crewLeaders.length > 0 || setupData.value.workers.length > 0;
    if (id === 6) return setupData.value.jobTypes.length > 0;
    if (id === 7) return setupData.value.waterSources.length > 0 || setupData.value.filters.length > 0 || setupData.value.fertilizationRecipes.length > 0;
    if (id === 8) return completedCount.value >= 4;
    return false;
};

const autoSelectFirstIncomplete = () => {
    const saved = localStorage.getItem('sasa_setup_active_step');
    if (!saved) {
        for (let i = 1; i <= 7; i++) {
            if (!isStepCompleted(i)) {
                currentStep.value = i;
                return;
            }
        }
        currentStep.value = 8;
    }
};

const fetchSetupData = async () => {
    try {
        isLoading.value = true;
        const res = await axios.get('/setup/data');
        if (res.data) {
            setupData.value = {
                companies: res.data.companies || [],
                productionLocations: res.data.productionLocations || [],
                units: res.data.units || [],
                packagings: res.data.packagings || [],
                products: res.data.products || [],
                personnels: res.data.personnels || [],
                crewLeaders: res.data.crewLeaders || [],
                workers: res.data.workers || [],
                jobTypes: res.data.jobTypes || [],
                waterSources: res.data.waterSources || [],
                filters: res.data.filters || [],
                fertilizationRecipes: res.data.fertilizationRecipes || [],
                sprayingRecipes: res.data.sprayingRecipes || [],
            };
            autoSelectFirstIncomplete();
        }
    } catch (e) {
    } finally {
        isLoading.value = false;
    }
};

watch(() => props.modelValue, (newVal) => {
    if (newVal) {
        fetchSetupData();
    }
});

const completedCount = computed(() => {
    let count = 0;
    for (let i = 1; i <= 7; i++) {
        if (isStepCompleted(i)) count++;
    }
    return count;
});

const progressPercent = computed(() => Math.round((completedCount.value / 7) * 100));

const notifySuccess = (msg: string) => {
    saveFeedback.value = msg;
    fetchSetupData();
    setTimeout(() => {
        saveFeedback.value = '';
    }, 2800);
};

const goToNextStep = () => {
    if (currentStep.value < 8) {
        currentStep.value++;
    }
};

const companyForm = useForm({
    name: '',
    code: '',
    tax_number: '',
    dia_company_code: '',
    is_active: true,
});
const submitCompany = () => {
    if (!companyForm.code.trim()) {
        const slug = companyForm.name.trim().toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 6) || 'SASA';
        companyForm.code = slug + '-' + Math.floor(10 + Math.random() * 90);
    }
    companyForm.post(route('definitions.companies.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            companyForm.reset();
            notifySuccess('Firma başarıyla kaydedildi!');
        }
    });
};
const deleteCompany = (c: any) => {
    if (confirm(`'${c.name}' firmasını silmek istediğinize emin misiniz?`)) {
        router.delete(route('definitions.companies.destroy', c.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => fetchSetupData()
        });
    }
};

const locationForm = useForm({
    name: '',
    company_id: '',
    total_area_dekar: 0,
    location_type: 'greenhouse',
    sections: [] as any[],
    valves: [] as any[],
});
const newSectionName = ref('');
const newValveName = ref('');
const addSection = () => {
    if (!newSectionName.value.trim()) return;
    locationForm.sections.push({ name: newSectionName.value.trim(), section_type: 'greenhouse' });
    newSectionName.value = '';
};
const removeSection = (idx: number) => {
    locationForm.sections.splice(idx, 1);
};
const addValve = () => {
    if (!newValveName.value.trim()) return;
    locationForm.valves.push({ name: newValveName.value.trim(), valve_number: 'V-0' + (locationForm.valves.length + 1), duty: 'irrigation' });
    newValveName.value = '';
};
const removeValve = (idx: number) => {
    locationForm.valves.splice(idx, 1);
};
const submitLocation = () => {
    locationForm.post(route('definitions.production-locations.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            locationForm.reset();
            locationForm.sections = [];
            locationForm.valves = [];
            notifySuccess('Üretim alanı başarıyla kaydedildi!');
        }
    });
};
const deleteLocation = (l: any) => {
    if (confirm(`'${l.name}' üretim yerini silmek istediğinize emin misiniz?`)) {
        router.delete(route('definitions.production-locations.destroy', l.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => fetchSetupData()
        });
    }
};

const unitForm = useForm({
    name: '',
    symbol: '',
    unit_category: 'quantity',
});

interface PresetUnit {
    name: string;
    symbol: string;
    unit_category: 'quantity' | 'area' | 'count';
    category_label: string;
    recommended?: boolean;
}

const presetUnits: PresetUnit[] = [
    { name: 'Kilogram', symbol: 'kg', unit_category: 'quantity', category_label: 'Ağırlık & Kütle', recommended: true },
    { name: 'Gram', symbol: 'gr', unit_category: 'quantity', category_label: 'Ağırlık & Kütle', recommended: true },
    { name: 'Ton', symbol: 'ton', unit_category: 'quantity', category_label: 'Ağırlık & Kütle', recommended: true },
    { name: 'Miligram', symbol: 'mg', unit_category: 'quantity', category_label: 'Ağırlık & Kütle' },
    { name: 'Kental', symbol: 'q', unit_category: 'quantity', category_label: 'Ağırlık & Kütle' },

    { name: 'Adet', symbol: 'adt', unit_category: 'count', category_label: 'Sayı & Paket', recommended: true },
    { name: 'Kasa', symbol: 'ksa', unit_category: 'count', category_label: 'Sayı & Paket', recommended: true },
    { name: 'Koli', symbol: 'koli', unit_category: 'count', category_label: 'Sayı & Paket', recommended: true },
    { name: 'Paket', symbol: 'pkt', unit_category: 'count', category_label: 'Sayı & Paket', recommended: true },
    { name: 'Çuval', symbol: 'çvl', unit_category: 'count', category_label: 'Sayı & Paket', recommended: true },
    { name: 'Palet', symbol: 'plt', unit_category: 'count', category_label: 'Sayı & Paket', recommended: true },
    { name: 'Sandık', symbol: 'sdk', unit_category: 'count', category_label: 'Sayı & Paket' },
    { name: 'File', symbol: 'file', unit_category: 'count', category_label: 'Sayı & Paket' },
    { name: 'Demet', symbol: 'dmt', unit_category: 'count', category_label: 'Sayı & Paket', recommended: true },
    { name: 'Bağ', symbol: 'bağ', unit_category: 'count', category_label: 'Sayı & Paket' },
    { name: 'Deste', symbol: 'dst', unit_category: 'count', category_label: 'Sayı & Paket' },
    { name: 'Düzine', symbol: 'dzn', unit_category: 'count', category_label: 'Sayı & Paket' },
    { name: 'Viyol', symbol: 'vyl', unit_category: 'count', category_label: 'Sayı & Paket' },
    { name: 'Rulo', symbol: 'rulo', unit_category: 'count', category_label: 'Sayı & Paket' },
    { name: 'Sepet', symbol: 'spt', unit_category: 'count', category_label: 'Sayı & Paket' },

    { name: 'Litre', symbol: 'lt', unit_category: 'quantity', category_label: 'Hacim & Sıvı', recommended: true },
    { name: 'Mililitre', symbol: 'ml', unit_category: 'quantity', category_label: 'Hacim & Sıvı', recommended: true },
    { name: 'Metreküp', symbol: 'm³', unit_category: 'quantity', category_label: 'Hacim & Sıvı' },
    { name: 'Bidon', symbol: 'bdn', unit_category: 'quantity', category_label: 'Hacim & Sıvı' },
    { name: 'Varil', symbol: 'vrl', unit_category: 'quantity', category_label: 'Hacim & Sıvı' },
    { name: 'Şişe', symbol: 'şş', unit_category: 'quantity', category_label: 'Hacim & Sıvı' },
    { name: 'Teneke', symbol: 'tnk', unit_category: 'quantity', category_label: 'Hacim & Sıvı' },
    { name: 'Tanker', symbol: 'tnkr', unit_category: 'quantity', category_label: 'Hacim & Sıvı' },

    { name: 'Dekar (Dönüm)', symbol: 'da', unit_category: 'area', category_label: 'Alan & Uzunluk', recommended: true },
    { name: 'Hektar', symbol: 'ha', unit_category: 'area', category_label: 'Alan & Uzunluk' },
    { name: 'Metrekare', symbol: 'm²', unit_category: 'area', category_label: 'Alan & Uzunluk' },
    { name: 'Metre', symbol: 'mt', unit_category: 'area', category_label: 'Alan & Uzunluk', recommended: true },
    { name: 'Kilometre', symbol: 'km', unit_category: 'area', category_label: 'Alan & Uzunluk' },
];

const selectedPresetNames = ref<string[]>([]);
const unitCategoryFilter = ref<string>('all');
const showCustomUnitForm = ref(false);

const filteredPresetUnits = computed(() => {
    if (unitCategoryFilter.value === 'all') return presetUnits;
    return presetUnits.filter(u => u.category_label === unitCategoryFilter.value);
});

const isUnitSavedInDb = (name: string) => {
    return setupData.value.units.some((u: any) => u.name.toLowerCase().trim() === name.toLowerCase().trim());
};

const togglePresetUnit = (unit: PresetUnit) => {
    if (isUnitSavedInDb(unit.name)) return;
    const idx = selectedPresetNames.value.indexOf(unit.name);
    if (idx >= 0) {
        selectedPresetNames.value.splice(idx, 1);
    } else {
        selectedPresetNames.value.push(unit.name);
    }
};

const selectRecommendedUnits = () => {
    presetUnits.filter(u => u.recommended && !isUnitSavedInDb(u.name)).forEach(u => {
        if (!selectedPresetNames.value.includes(u.name)) {
            selectedPresetNames.value.push(u.name);
        }
    });
};

const selectAllUnits = () => {
    presetUnits.filter(u => !isUnitSavedInDb(u.name)).forEach(u => {
        if (!selectedPresetNames.value.includes(u.name)) {
            selectedPresetNames.value.push(u.name);
        }
    });
};

const clearSelectedUnits = () => {
    selectedPresetNames.value = [];
};

const bulkUnitForm = useForm({
    units: [] as any[],
});

const submitSelectedUnits = () => {
    const unitsToSave = presetUnits.filter(u => selectedPresetNames.value.includes(u.name) && !isUnitSavedInDb(u.name));
    if (unitsToSave.length === 0) {
        goToNextStep();
        return;
    }

    bulkUnitForm.units = unitsToSave.map(u => ({
        name: u.name,
        symbol: u.symbol,
        unit_category: u.unit_category,
    }));

    bulkUnitForm.post(route('definitions.units.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            selectedPresetNames.value = [];
            notifySuccess(`${unitsToSave.length} adet ölçü birimi kaydedildi!`);
        }
    });
};

const submitCustomUnit = () => {
    unitForm.post(route('definitions.units.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            unitForm.reset();
            showCustomUnitForm.value = false;
            notifySuccess('Özel birim kaydedildi!');
        }
    });
};

const deleteExistingUnit = (unit: any) => {
    if (confirm(`'${unit.name}' birimini silmek istediğinize emin misiniz?`)) {
        router.delete(route('definitions.units.destroy', unit.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                fetchSetupData();
            }
        });
    }
};

const quickProducts = [
    { name: 'Çilek', code: 'CLK-01', subtypes: ['1. Kalite', '2. Kalite', 'Sanayi'] },
    { name: 'Muz', code: 'MUZ-01', subtypes: ['Ekstra', '1. Kalite', 'İkinci'] },
    { name: 'Domates (Salkım)', code: 'DOM-01', subtypes: ['1. Sınıf', 'Sanayi'] },
    { name: 'Biber (Kapya)', code: 'KAP-01', subtypes: ['1. Kalite', 'Salçalık'] },
    { name: 'Salatalık', code: 'SLT-01', subtypes: ['Standart', 'Turşuluk'] },
    { name: 'Avokado', code: 'AVK-01', subtypes: ['1. Boy', '2. Boy'] },
    { name: 'Limon (Mayer)', code: 'LMN-01', subtypes: ['1. Kalite', 'Sıkmalık'] },
    { name: 'Yaban Mersini', code: 'YBN-01', subtypes: ['İhracat', 'İç Piyasa'] },
];

const fillQuickProduct = (qp: typeof quickProducts[0]) => {
    productForm.name = qp.name;
    productForm.code = qp.code;
    productForm.subtypes = qp.subtypes.map(s => ({ name: s }));
};

const productForm = useForm({
    name: '',
    code: '',
    product_type: 'produced',
    company_ids: [] as number[],
    unit_ids: [] as number[],
    subtypes: [] as any[],
});
const newSubtypeName = ref('');
const addSubtype = () => {
    if (!newSubtypeName.value.trim()) return;
    productForm.subtypes.push({ name: newSubtypeName.value.trim() });
    newSubtypeName.value = '';
};
const removeSubtype = (idx: number) => {
    productForm.subtypes.splice(idx, 1);
};
const submitProduct = () => {
    productForm.post(route('definitions.products.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            productForm.reset();
            productForm.subtypes = [];
            notifySuccess('Ürün başarıyla kaydedildi!');
        }
    });
};
const deleteProduct = (p: any) => {
    if (confirm(`'${p.name}' ürününü silmek istediğinize emin misiniz?`)) {
        router.delete(route('definitions.products.destroy', p.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => fetchSetupData()
        });
    }
};

const activePersonTab = ref<'personnel' | 'crew' | 'worker'>('personnel');
const availableModules = [
    { key: 'tesis', label: 'Tesis Yönetimi', desc: 'Sera, depo ve alanlar', icon: '🏭' },
    { key: 'uretim', label: 'Üretim & Sulama', desc: 'Reçete, sulama, gübreleme', icon: '🌾' },
    { key: 'teknik', label: 'Teknik & Su', desc: 'Kuyu, filtre, su analizleri', icon: '💧' },
    { key: 'operasyon', label: 'Operasyon & Saha', desc: 'Hasat, işçilik, sevkiyat', icon: '📋' },
    { key: 'raporlar', label: 'Raporlar & Analiz', desc: 'Maliyet, verim, puantaj', icon: '📊' },
    { key: 'tanimlamalar', label: 'Sistem Tanımları', desc: 'Ana ayarlar ve tanımlamalar', icon: '⚙️' },
];

const personnelForm = useForm({
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    password: '',
    role_title: '',
    company_ids: [] as number[],
    permissions: ['tesis', 'uretim', 'teknik', 'operasyon', 'raporlar'] as string[],
    can_enter_backdated_data: false,
    is_active: true,
});

const togglePersonnelPerm = (key: string) => {
    const idx = personnelForm.permissions.indexOf(key);
    if (idx >= 0) {
        personnelForm.permissions.splice(idx, 1);
    } else {
        personnelForm.permissions.push(key);
    }
};

const togglePersonnelCompany = (companyId: number) => {
    const idx = personnelForm.company_ids.indexOf(companyId);
    if (idx >= 0) {
        personnelForm.company_ids.splice(idx, 1);
    } else {
        personnelForm.company_ids.push(companyId);
    }
};

const selectAllPerms = () => {
    personnelForm.permissions = ['tesis', 'uretim', 'teknik', 'operasyon', 'raporlar', 'tanimlamalar'];
};

const selectStandardPerms = () => {
    personnelForm.permissions = ['tesis', 'uretim', 'teknik', 'operasyon', 'raporlar'];
};

const submitPersonnel = () => {
    if (personnelForm.company_ids.length === 0 && setupData.value.companies.length > 0) {
        personnelForm.company_ids = setupData.value.companies.map(c => c.id);
    }
    personnelForm.post(route('definitions.personnels.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            personnelForm.reset();
            personnelForm.permissions = ['tesis', 'uretim', 'teknik', 'operasyon', 'raporlar'];
            personnelForm.company_ids = [];
            notifySuccess('Personel başarıyla kaydedildi!');
        }
    });
};
const deletePersonnel = (p: any) => {
    if (confirm(`'${p.first_name} ${p.last_name}' personelini silmek istediğinize emin misiniz?`)) {
        router.delete(route('definitions.personnels.destroy', p.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => fetchSetupData()
        });
    }
};

const crewLeaderForm = useForm({
    first_name: '',
    last_name: '',
    phone: '',
    daily_wage: 1000,
    multiplier: 1.0,
    is_leader_fee_included: true,
    min_car_requirement: 0,
    travel_fee_per_car: 0,
    is_food_included: true,
});
const submitCrewLeader = () => {
    crewLeaderForm.post(route('definitions.crew-leaders.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            crewLeaderForm.reset();
            notifySuccess('Çavuş başarıyla kaydedildi!');
        }
    });
};
const deleteCrewLeader = (c: any) => {
    if (confirm(`'${c.first_name} ${c.last_name}' çavuşunu silmek istediğinize emin misiniz?`)) {
        router.delete(route('definitions.crew-leaders.destroy', c.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => fetchSetupData()
        });
    }
};

const workerForm = useForm({
    first_name: '',
    last_name: '',
    phone: '',
    crew_leader_id: '',
    performance_rating: 5,
    is_active: true,
});
const submitWorker = () => {
    workerForm.post(route('definitions.workers.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            workerForm.reset();
            notifySuccess('İşçi başarıyla kaydedildi!');
        }
    });
};
const deleteWorker = (w: any) => {
    if (confirm(`'${w.first_name} ${w.last_name}' işçisini silmek istediğinize emin misiniz?`)) {
        router.delete(route('definitions.workers.destroy', w.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => fetchSetupData()
        });
    }
};

const jobTypeForm = useForm({
    name: '',
    code: '',
    form_type: 'daily_labor',
    unit_ids: [] as number[],
    selected_unit_id: '',
});
const submitJobType = () => {
    if (!jobTypeForm.code.trim()) {
        const slug = jobTypeForm.name.trim().toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 6) || 'JOB';
        jobTypeForm.code = slug + '-' + Math.floor(10 + Math.random() * 90);
    }
    if (jobTypeForm.selected_unit_id) {
        jobTypeForm.unit_ids = [Number(jobTypeForm.selected_unit_id)];
    }
    jobTypeForm.post(route('definitions.job-types.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            jobTypeForm.reset();
            notifySuccess('İş tipi başarıyla tanımlandı!');
        }
    });
};
const deleteJobType = (j: any) => {
    if (confirm(`'${j.name}' iş tipini silmek istediğinize emin misiniz?`)) {
        router.delete(route('definitions.job-types.destroy', j.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => fetchSetupData()
        });
    }
};

const activeInfraTab = ref<'water' | 'filter' | 'recipe'>('water');
const waterForm = useForm({
    name: '',
    production_location_id: '',
    active_cycle_minutes: 60,
    passive_cycle_minutes: 30,
    requires_photo_verification: false,
    is_active: true,
});
const submitWater = () => {
    waterForm.post(route('definitions.water-sources.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            waterForm.reset();
            notifySuccess('Su kaynağı kaydedildi!');
        }
    });
};
const deleteWaterSource = (w: any) => {
    if (confirm(`'${w.name}' su kaynağını silmek istediğinize emin misiniz?`)) {
        router.delete(route('definitions.water-sources.destroy', w.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => fetchSetupData()
        });
    }
};

const filterForm = useForm({
    name: '',
    production_location_id: '',
    cleaning_cycle_days: 7,
    requires_photo_verification: false,
    is_active: true,
});
const submitFilter = () => {
    filterForm.post(route('definitions.filters.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            filterForm.reset();
            notifySuccess('Filtre kaydedildi!');
        }
    });
};
const deleteFilter = (f: any) => {
    if (confirm(`'${f.name}' filtresini silmek istediğinize emin misiniz?`)) {
        router.delete(route('definitions.filters.destroy', f.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => fetchSetupData()
        });
    }
};

const recipeForm = useForm({
    name: '',
    creation_date: new Date().toISOString().split('T')[0],
    is_active: true,
    tanks: [
        { tank_name: 'A Tankı', capacity_liters: 1000 },
        { tank_name: 'B Tankı', capacity_liters: 1000 }
    ],
});
const submitRecipe = () => {
    recipeForm.post(route('definitions.fertilization-recipes.store'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            recipeForm.reset();
            notifySuccess('Gübreleme reçetesi kaydedildi!');
        }
    });
};
const deleteRecipe = (r: any) => {
    if (confirm(`'${r.name}' reçetesini silmek istediğinize emin misiniz?`)) {
        router.delete(route('definitions.fertilization-recipes.destroy', r.id), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => fetchSetupData()
        });
    }
};

const closeWizard = () => {
    isOpen.value = false;
    if (typeof window !== 'undefined') {
        localStorage.setItem('sasa_setup_wizard_open', 'false');
    }
};
</script>

<template>
    <Transition
        enter-active-class="transition-opacity duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6 bg-slate-950/80 backdrop-blur-md">
            
            <Transition
                enter-active-class="transition-all duration-300 cubic-bezier(0.16, 1, 0.3, 1)"
                enter-from-class="opacity-0 scale-95 translate-y-4"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 scale-100 translate-y-0"
                leave-to-class="opacity-0 scale-95 translate-y-4"
                appear
            >
                <div class="bg-slate-900 border border-slate-800/90 rounded-3xl shadow-[0_25px_60px_-15px_rgba(0,0,0,0.7)] text-slate-100 flex flex-col h-[740px] max-h-[92vh] max-w-5xl w-full overflow-hidden">
                    
                    <div class="px-6 py-4 bg-slate-900/95 border-b border-slate-800 flex items-center justify-between shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 shadow-[0_0_15px_rgba(16,185,129,0.15)] group">
                                <svg class="w-5 h-5 transition-transform duration-500 group-hover:rotate-90" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-base font-extrabold text-white tracking-wide">Sistem Kurulum Sihirbazı</h2>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 transition-all">Adım {{ currentStep }} / 8</span>
                                </div>
                                <p class="text-xs text-slate-400">İstediğiniz sayıda kayıt ekleyebilir, hazır olduğunuzda sonraki adıma geçebilirsiniz.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="hidden sm:flex flex-col items-end">
                                <span class="text-[11px] font-bold text-slate-300">%{{ progressPercent }} Tamamlandı</span>
                                <div class="w-32 h-2 bg-slate-800 rounded-full overflow-hidden mt-1 border border-slate-700/50 p-0.5">
                                    <div class="h-full bg-linear-to-r from-emerald-500 via-teal-400 to-cyan-400 rounded-full transition-all duration-500 shadow-[0_0_12px_rgba(52,211,153,0.6)]" :style="{ width: `${progressPercent}%` }"></div>
                                </div>
                            </div>
                            <button @click="closeWizard" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 hover:scale-105 active:scale-95 transition cursor-pointer" title="Kapat">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="px-6 py-3 bg-slate-950/60 border-b border-slate-800/80 shrink-0">
                        <div class="grid grid-cols-4 sm:grid-cols-8 gap-1.5 w-full">
                            <button
                                v-for="st in steps"
                                :key="st.id"
                                @click="currentStep = st.id"
                                :class="[
                                    'flex items-center justify-center gap-1.5 px-2 py-2 rounded-xl text-[11px] font-bold transition-all duration-200 cursor-pointer border truncate',
                                    currentStep === st.id
                                        ? 'bg-emerald-500/20 border-emerald-500/60 text-emerald-300 shadow-[0_0_15px_rgba(16,185,129,0.25)] scale-[1.02] ring-1 ring-emerald-400/40'
                                        : isStepCompleted(st.id)
                                            ? 'bg-slate-800/70 border-slate-700/60 text-slate-300 hover:bg-slate-800 hover:text-white hover:scale-[1.01]'
                                            : 'bg-slate-900/40 border-transparent text-slate-500 hover:text-slate-300 hover:bg-slate-800/40'
                                ]"
                            >
                                <svg v-if="st.id === 1" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <svg v-else-if="st.id === 2" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <svg v-else-if="st.id === 3" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                                <svg v-else-if="st.id === 4" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                <svg v-else-if="st.id === 5" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <svg v-else-if="st.id === 6" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                <svg v-else-if="st.id === 7" class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                <svg v-else class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                
                                <span class="truncate">{{ st.short }}</span>
                                <span v-if="isStepCompleted(st.id)" class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0 shadow-[0_0_6px_rgba(52,211,153,0.8)]"></span>
                            </button>
                        </div>
                    </div>

                    <Transition
                        enter-active-class="transition-all duration-300 ease-out"
                        enter-from-class="opacity-0 -translate-y-2"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition-all duration-200 ease-in"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-2"
                    >
                        <div v-if="saveFeedback" class="px-6 py-2 bg-emerald-500/20 border-b border-emerald-500/40 text-emerald-300 text-xs font-bold flex items-center gap-2 shrink-0">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ saveFeedback }}</span>
                        </div>
                    </Transition>

                    <div class="p-6 overflow-y-auto flex-1 space-y-6">
                        <Transition name="step-fade" mode="out-in">
                            <div :key="currentStep" class="space-y-6">
                                
                                <div v-if="currentStep === 1" class="space-y-6">
                                    <div class="p-4 rounded-2xl bg-sky-950/40 border border-sky-800/60 text-sky-200 shadow-sm">
                                        <div class="flex items-start gap-3">
                                            <div class="p-2 rounded-xl bg-sky-500/10 border border-sky-500/30 text-sky-400 shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black text-sky-100 uppercase tracking-wide">1. Adım Firma & Şirket Tanımı</h4>
                                                <p class="text-xs text-sky-200/90 mt-1 leading-relaxed">
                                                    İşletmenize ait firmaları ekleyin. İstediğiniz kadar firma ekleyebilir, ardından sonraki adıma geçebilirsiniz.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                        <div class="lg:col-span-7 bg-slate-950/50 p-5 rounded-2xl border border-slate-800">
                                            <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]"></span>
                                                Yeni Firma Ekle
                                            </h3>
                                            <form @submit.prevent="submitCompany" class="space-y-4">
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Firma / Şirket Adı <span class="text-rose-400">*</span></label>
                                                    <input v-model="companyForm.name" type="text" placeholder="Örn: SASA Tarım İşletmeleri A.Ş." required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-all" />
                                                </div>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Firma Kodu</label>
                                                        <input v-model="companyForm.code" type="text" placeholder="Örn: SASA-01" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-all" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Vergi No / VKN</label>
                                                        <input v-model="companyForm.tax_number" type="text" placeholder="10 haneli VKN" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-all" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">DİA Entegrasyon Firma Kodu</label>
                                                    <input v-model="companyForm.dia_company_code" type="text" placeholder="Örn: 001" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-all" />
                                                </div>
                                                <div class="pt-2 flex items-center justify-between">
                                                    <span class="text-[11px] text-slate-400">Formu doldurup ekleyin.</span>
                                                    <button type="submit" :disabled="companyForm.processing" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs transition-all duration-200 shadow-lg shadow-emerald-950/60 hover:scale-[1.02] active:scale-95 flex items-center gap-2 cursor-pointer">
                                                        <span>+ Firmayı Ekle</span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="lg:col-span-5 bg-slate-950/30 p-5 rounded-2xl border border-slate-800 flex flex-col justify-between shadow-lg">
                                            <div>
                                                <h4 class="text-xs font-bold text-slate-300 mb-3 flex items-center justify-between">
                                                    <span>Mevcut Firmalar</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-emerald-400">{{ setupData.companies.length }} Kayıt</span>
                                                </h4>
                                                <div class="space-y-2 overflow-y-auto max-h-60 pr-1">
                                                    <div v-for="c in setupData.companies" :key="c.id" class="p-3 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between hover:border-slate-700 transition-all">
                                                        <div>
                                                            <div class="text-xs font-bold text-white">{{ c.name }}</div>
                                                            <div class="text-[10px] text-slate-400">{{ c.code || 'KODSUZ' }} • {{ c.tax_number || 'VKN YOK' }}</div>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                            <button type="button" @click="deleteCompany(c)" class="p-1 rounded-md text-slate-500 hover:text-rose-400 hover:bg-slate-800 transition cursor-pointer" title="Sil">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div v-if="setupData.companies.length === 0" class="text-center py-8 text-xs text-slate-500">
                                                        Henüz kayıtlı firma yok. Soldaki formdan ekleyin.
                                                    </div>
                                                </div>
                                            </div>

                                            <div v-if="setupData.companies.length > 0" class="pt-4 border-t border-slate-800/80 mt-3">
                                                <button type="button" @click="goToNextStep" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-emerald-600 hover:text-slate-950 text-emerald-300 font-bold text-xs transition-all flex items-center justify-center gap-2 cursor-pointer border border-emerald-500/30">
                                                    <span>2. Adıma Geç (Arsa/Parsel)</span>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="currentStep === 2" class="space-y-6">
                                    <div class="p-4 rounded-2xl bg-amber-950/40 border border-amber-800/60 text-amber-200 shadow-sm">
                                        <div class="flex items-start gap-3">
                                            <div class="p-2 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-400 shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black text-amber-100 uppercase tracking-wide">2. Adım Üretim Yeri & Vana Tanımları</h4>
                                                <p class="text-xs text-amber-200/90 mt-1 leading-relaxed">
                                                    Sera, açık tarla ve bahçe alanlarınızı ekleyin. İstediğiniz kadar saha tanımlayabilirsiniz.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                        <div class="lg:col-span-7 bg-slate-950/50 p-5 rounded-2xl border border-slate-800">
                                            <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-amber-400 shadow-[0_0_8px_rgba(251,191,36,0.8)]"></span>
                                                Yeni Üretim Yeri / Sera Ekle
                                            </h3>
                                            <form @submit.prevent="submitLocation" class="space-y-4">
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Bağlı Firma <span class="text-rose-400">*</span></label>
                                                        <select v-model="locationForm.company_id" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-all">
                                                            <option value="">Firma Seçin</option>
                                                            <option v-for="c in setupData.companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Üretim Yeri Tipi</label>
                                                        <select v-model="locationForm.location_type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-all">
                                                            <option value="greenhouse">Sera (Örtü Altı)</option>
                                                            <option value="open_field">Açık Tarla</option>
                                                            <option value="mixed">Meyve Bahçesi / Karma</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-3 gap-3">
                                                    <div class="col-span-2">
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tesis / Sera Adı <span class="text-rose-400">*</span></label>
                                                        <input v-model="locationForm.name" type="text" placeholder="Örn: Silifke Çilek Serası" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-all" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Alan (Dekar)</label>
                                                        <input v-model="locationForm.total_area_dekar" type="number" step="0.1" placeholder="Örn: 45.5" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-all" />
                                                    </div>
                                                </div>

                                                <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-800 space-y-2">
                                                    <label class="block text-xs font-bold text-slate-300">Sulama Vanaları (İsteğe Bağlı)</label>
                                                    <div class="flex gap-2">
                                                        <input v-model="newValveName" type="text" placeholder="Vana Adı (Örn: Vana 1)" class="flex-1 bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500" />
                                                        <button type="button" @click="addValve" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-bold transition hover:scale-105 active:scale-95 cursor-pointer">+ Ekle</button>
                                                    </div>
                                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                                        <span v-for="(v, i) in locationForm.valves" :key="i" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-500/10 text-amber-300 border border-amber-500/30 rounded-lg text-xs">
                                                            {{ v.name }}
                                                            <button type="button" @click="removeValve(i)" class="text-rose-400 font-bold hover:text-rose-200">&times;</button>
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="pt-2 flex items-center justify-between">
                                                    <span class="text-[11px] text-slate-400">İstediğiniz kadar üretim yeri ekleyebilirsiniz.</span>
                                                    <button type="submit" :disabled="locationForm.processing" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs transition-all duration-200 shadow-lg shadow-emerald-950/60 hover:scale-[1.02] active:scale-95 flex items-center gap-2 cursor-pointer">
                                                        <span>+ Sahayı Ekle</span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="lg:col-span-5 bg-slate-950/30 p-5 rounded-2xl border border-slate-800 flex flex-col justify-between shadow-lg">
                                            <div>
                                                <h4 class="text-xs font-bold text-slate-300 mb-3 flex items-center justify-between">
                                                    <span>Mevcut Üretim Alanları</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-emerald-400">{{ setupData.productionLocations.length }} Kayıt</span>
                                                </h4>
                                                <div class="space-y-2 overflow-y-auto max-h-60 pr-1">
                                                    <div v-for="l in setupData.productionLocations" :key="l.id" class="p-3 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between hover:border-slate-700 transition-all">
                                                        <div>
                                                            <div class="text-xs font-bold text-white">{{ l.name }}</div>
                                                            <div class="text-[10px] text-slate-400">{{ l.company?.name }} • {{ l.total_area_dekar || 0 }} Dekar</div>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                            <button type="button" @click="deleteLocation(l)" class="p-1 rounded-md text-slate-500 hover:text-rose-400 hover:bg-slate-800 transition cursor-pointer" title="Sil">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div v-if="setupData.productionLocations.length === 0" class="text-center py-8 text-xs text-slate-500">
                                                        Henüz kayıtlı üretim yeri yok. Soldan ekleyin.
                                                    </div>
                                                </div>
                                            </div>

                                            <div v-if="setupData.productionLocations.length > 0" class="pt-4 border-t border-slate-800/80 mt-3">
                                                <button type="button" @click="goToNextStep" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-emerald-600 hover:text-slate-950 text-emerald-300 font-bold text-xs transition-all flex items-center justify-center gap-2 cursor-pointer border border-emerald-500/30">
                                                    <span>3. Adıma Geç (Ölçü Birimleri)</span>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="currentStep === 3" class="space-y-5">
                                    <div class="p-4 rounded-2xl bg-indigo-950/30 border border-indigo-800/40 text-indigo-200 shadow-sm">
                                        <div class="flex items-start gap-3">
                                            <div class="p-2 rounded-xl bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black text-indigo-100 tracking-wide">3. Adım Ölçü Birimleri Havuzu</h4>
                                                <p class="text-xs text-indigo-200/80 mt-0.5">
                                                    İşletmenizde kullanacağınız birimleri tıklayarak seçin veya standart önerilen paketi tek tıkla yükleyin.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                                        <div class="lg:col-span-8 bg-[#0b101b] p-5 rounded-2xl border border-slate-800/80 flex flex-col justify-between space-y-4 shadow-lg">
                                            <div class="space-y-3.5">
                                                <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-800/70">
                                                    <div class="flex items-center gap-2.5">
                                                        <h3 class="text-sm font-black text-white tracking-wide">Birim Seçimi</h3>
                                                        <span class="text-[11px] font-mono px-2 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300 font-bold border border-emerald-500/30">
                                                            {{ selectedPresetNames.length }} Seçili
                                                        </span>
                                                    </div>

                                                    <div class="flex items-center gap-1.5 text-xs">
                                                        <button
                                                            type="button"
                                                            @click="selectRecommendedUnits"
                                                            class="px-3 py-1.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-300 border border-emerald-500/30 text-xs font-bold transition cursor-pointer flex items-center gap-1.5 active:scale-95"
                                                        >
                                                            <svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                                            <span>Önerilenleri Seç</span>
                                                        </button>
                                                        <button
                                                            type="button"
                                                            @click="selectAllUnits"
                                                            class="px-3 py-1.5 rounded-xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 text-xs font-bold transition cursor-pointer border border-slate-700/60 active:scale-95"
                                                        >
                                                            Tümünü Seç
                                                        </button>
                                                        <button
                                                            v-if="selectedPresetNames.length > 0"
                                                            type="button"
                                                            @click="clearSelectedUnits"
                                                            class="px-2.5 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 text-xs font-bold transition cursor-pointer border border-rose-500/30"
                                                        >
                                                            Temizle
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-1.5 p-1 bg-slate-900/90 rounded-xl border border-slate-800 text-xs overflow-x-auto">
                                                    <button
                                                        type="button"
                                                        @click="unitCategoryFilter = 'all'"
                                                        :class="[
                                                            'px-3 py-1 rounded-lg font-bold transition cursor-pointer text-xs',
                                                            unitCategoryFilter === 'all' ? 'bg-slate-800 text-white shadow-xs' : 'text-slate-400 hover:text-slate-200'
                                                        ]"
                                                    >
                                                        Tüm Kategoriler
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="unitCategoryFilter = 'Ağırlık & Kütle'"
                                                        :class="[
                                                            'px-3 py-1 rounded-lg font-bold transition cursor-pointer text-xs',
                                                            unitCategoryFilter === 'Ağırlık & Kütle' ? 'bg-slate-800 text-white shadow-xs' : 'text-slate-400 hover:text-slate-200'
                                                        ]"
                                                    >
                                                        Ağırlık & Kütle
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="unitCategoryFilter = 'Sayı & Paket'"
                                                        :class="[
                                                            'px-3 py-1 rounded-lg font-bold transition cursor-pointer text-xs',
                                                            unitCategoryFilter === 'Sayı & Paket' ? 'bg-slate-800 text-white shadow-xs' : 'text-slate-400 hover:text-slate-200'
                                                        ]"
                                                    >
                                                        Sayı & Ambalaj
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="unitCategoryFilter = 'Hacim & Sıvı'"
                                                        :class="[
                                                            'px-3 py-1 rounded-lg font-bold transition cursor-pointer text-xs',
                                                            unitCategoryFilter === 'Hacim & Sıvı' ? 'bg-slate-800 text-white shadow-xs' : 'text-slate-400 hover:text-slate-200'
                                                        ]"
                                                    >
                                                        Hacim & Sıvı
                                                    </button>
                                                    <button
                                                        type="button"
                                                        @click="unitCategoryFilter = 'Alan & Uzunluk'"
                                                        :class="[
                                                            'px-3 py-1 rounded-lg font-bold transition cursor-pointer text-xs',
                                                            unitCategoryFilter === 'Alan & Uzunluk' ? 'bg-slate-800 text-white shadow-xs' : 'text-slate-400 hover:text-slate-200'
                                                        ]"
                                                    >
                                                        Alan & Uzunluk
                                                    </button>
                                                </div>

                                                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2 max-h-64 overflow-y-auto pr-1">
                                                    <div
                                                        v-for="pu in filteredPresetUnits"
                                                        :key="pu.name"
                                                        @click="togglePresetUnit(pu)"
                                                        :class="[
                                                            'p-2.5 rounded-xl border transition-all duration-150 flex items-center justify-between select-none',
                                                            isUnitSavedInDb(pu.name)
                                                                ? 'bg-slate-900/40 border-slate-800/60 opacity-60 cursor-default'
                                                                : selectedPresetNames.includes(pu.name)
                                                                    ? 'bg-emerald-950/40 border-emerald-500/70 shadow-sm ring-1 ring-emerald-500/30 cursor-pointer'
                                                                    : 'bg-[#0f141f] border-slate-800/80 hover:border-slate-700 hover:bg-slate-800/40 cursor-pointer'
                                                        ]"
                                                    >
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <span
                                                                :class="[
                                                                    'w-7 h-7 rounded-lg flex items-center justify-center font-mono font-black text-[10px] shrink-0 border uppercase',
                                                                    isUnitSavedInDb(pu.name)
                                                                        ? 'bg-slate-800/60 border-slate-700 text-slate-400'
                                                                        : selectedPresetNames.includes(pu.name)
                                                                            ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300'
                                                                            : 'bg-slate-800/70 border-slate-700/60 text-slate-300'
                                                                ]"
                                                            >
                                                                {{ pu.symbol }}
                                                            </span>
                                                            <div class="min-w-0">
                                                                <div class="text-xs font-bold text-white flex items-center gap-1 truncate">
                                                                    <span class="truncate">{{ pu.name }}</span>
                                                                    <span v-if="pu.recommended && !isUnitSavedInDb(pu.name)" class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0" title="Öneri"></span>
                                                                </div>
                                                                <div class="text-[10px] text-slate-500 font-medium truncate">{{ pu.category_label }}</div>
                                                            </div>
                                                        </div>

                                                        <div class="shrink-0 ml-1">
                                                            <span v-if="isUnitSavedInDb(pu.name)" class="text-emerald-400">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                            </span>
                                                            <span v-else-if="selectedPresetNames.includes(pu.name)" class="w-4 h-4 rounded-md bg-emerald-500 text-slate-950 flex items-center justify-center font-bold">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                            </span>
                                                            <span v-else class="w-4 h-4 rounded-md border border-slate-700 bg-slate-900 flex items-center justify-center"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="pt-2 border-t border-slate-800/70">
                                                    <div class="flex items-center justify-between">
                                                        <button
                                                            type="button"
                                                            @click="showCustomUnitForm = !showCustomUnitForm"
                                                            class="text-xs text-indigo-400 hover:text-indigo-300 font-bold transition flex items-center gap-1.5 cursor-pointer"
                                                        >
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                                            </svg>
                                                            <span>{{ showCustomUnitForm ? 'Özel Birim Formunu Gizle' : 'Listede Olmayan Özel Birim Ekle' }}</span>
                                                        </button>
                                                    </div>

                                                    <form v-if="showCustomUnitForm" @submit.prevent="submitCustomUnit" class="mt-2.5 p-3 rounded-xl bg-slate-900 border border-slate-700/80 grid grid-cols-1 sm:grid-cols-3 gap-2.5 items-end">
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-300 mb-1 uppercase">Birim Adı *</label>
                                                            <input v-model="unitForm.name" required placeholder="Örn: Bidon, Sandık" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-[10px] font-bold text-slate-300 mb-1 uppercase">Sembol / Kod *</label>
                                                            <input v-model="unitForm.symbol" required placeholder="Örn: bdn, sdk" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500" />
                                                        </div>
                                                        <div>
                                                            <button type="submit" :disabled="unitForm.processing" class="w-full py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition cursor-pointer">
                                                                + Özel Birimi Ekle
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                            <div class="pt-3 border-t border-slate-800 flex items-center justify-between">
                                                <div class="text-xs text-slate-400">
                                                    <span v-if="selectedPresetNames.length > 0" class="text-emerald-400 font-bold">{{ selectedPresetNames.length }} birim seçildi</span>
                                                    <span v-else class="text-slate-500">Seçilen yeni birim yok</span>
                                                </div>

                                                <button
                                                    type="button"
                                                    @click="submitSelectedUnits"
                                                    :disabled="bulkUnitForm.processing || selectedPresetNames.length === 0"
                                                    class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 text-slate-950 font-black text-xs transition-all duration-200 shadow-md shadow-emerald-950/60 hover:scale-[1.02] active:scale-95 flex items-center gap-2 cursor-pointer"
                                                >
                                                    <span>+ Seçilenleri Kaydet ({{ selectedPresetNames.length }})</span>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="lg:col-span-4 bg-[#0b101b] p-5 rounded-2xl border border-slate-800/80 flex flex-col justify-between shadow-lg">
                                            <div>
                                                <h4 class="text-xs font-black text-white uppercase tracking-wide mb-3 flex items-center justify-between pb-2 border-b border-slate-800/70">
                                                    <span>Kayıtlı Birimler</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold bg-slate-800 text-emerald-400">{{ setupData.units.length }} Kayıt</span>
                                                </h4>
                                                <div class="space-y-1.5 overflow-y-auto max-h-64 pr-1">
                                                    <div
                                                        v-for="u in setupData.units"
                                                        :key="u.id"
                                                        class="p-2 rounded-xl bg-[#0f141f] border border-slate-800/90 flex items-center justify-between hover:border-slate-700 transition"
                                                    >
                                                        <div class="flex items-center gap-2 min-w-0">
                                                            <span class="w-6 h-6 rounded-md bg-slate-800 text-emerald-400 font-mono font-black text-[10px] flex items-center justify-center shrink-0 uppercase border border-slate-700/60">
                                                                {{ u.symbol || u.code || '-' }}
                                                            </span>
                                                            <span class="text-xs font-bold text-white truncate">{{ u.name }}</span>
                                                        </div>
                                                        <div class="flex items-center gap-1.5">
                                                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                            <button
                                                                type="button"
                                                                @click="deleteExistingUnit(u)"
                                                                class="p-1 rounded-md text-slate-500 hover:text-rose-400 hover:bg-slate-800 transition cursor-pointer"
                                                                title="Birimi Sil"
                                                            >
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div v-if="setupData.units.length === 0" class="text-center py-8 text-xs text-slate-500">
                                                        Henüz kayıtlı ölçü birimi yok.
                                                    </div>
                                                </div>
                                            </div>

                                            <div v-if="setupData.units.length > 0" class="mt-4 pt-3 border-t border-slate-800/70">
                                                <button type="button" @click="goToNextStep" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-emerald-600 hover:text-slate-950 text-emerald-300 font-bold text-xs transition-all flex items-center justify-center gap-2 cursor-pointer border border-emerald-500/30">
                                                    <span>4. Adıma Geç (Ürünler)</span>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="currentStep === 4" class="space-y-6">
                                    <div class="p-4 rounded-2xl bg-rose-950/40 border border-rose-800/60 text-rose-200 shadow-sm">
                                        <div class="flex items-start gap-3">
                                            <div class="p-2 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black text-rose-100 uppercase tracking-wide">4. Adım Ürünler & Kalite Sınıfları</h4>
                                                <p class="text-xs text-rose-200/90 mt-1 leading-relaxed">
                                                    Hasat ve satış yapacağınız mahsulleri ekleyin. İstediğiniz kadar ürün tanımlayabilirsiniz.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-3 bg-slate-950/40 rounded-2xl border border-slate-800 flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-bold text-slate-300 mr-1">Hızlı Ürün Şablonu:</span>
                                        <button
                                            v-for="qp in quickProducts"
                                            :key="qp.name"
                                            type="button"
                                            @click="fillQuickProduct(qp)"
                                            class="px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-rose-500/20 text-slate-300 hover:text-rose-300 border border-slate-800 hover:border-rose-500/40 text-xs font-semibold transition cursor-pointer"
                                        >
                                            + {{ qp.name }}
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                        <div class="lg:col-span-7 bg-slate-950/50 p-5 rounded-2xl border border-slate-800">
                                            <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-rose-400 shadow-[0_0_8px_rgba(251,113,133,0.8)]"></span>
                                                Yeni Ürün & Kalite Tanımı
                                            </h3>
                                            <form @submit.prevent="submitProduct" class="space-y-4">
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Ürün Adı <span class="text-rose-400">*</span></label>
                                                        <input v-model="productForm.name" type="text" placeholder="Örn: Albion Çilek" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-all" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Ürün Kodu</label>
                                                        <input v-model="productForm.code" type="text" placeholder="Örn: PRD-CLK" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none transition-all" />
                                                    </div>
                                                </div>

                                                <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-800 space-y-2">
                                                    <label class="block text-xs font-bold text-slate-300">Kalite Alt Türleri (İsteğe Bağlı)</label>
                                                    <div class="flex gap-2">
                                                        <input v-model="newSubtypeName" type="text" placeholder="Örn: 1. Kalite, 2. Kalite, Sanayi" class="flex-1 bg-slate-950 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white focus:outline-none focus:border-emerald-500" />
                                                        <button type="button" @click="addSubtype" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-bold transition hover:scale-105 active:scale-95 cursor-pointer">+ Ekle</button>
                                                    </div>
                                                    <div class="flex flex-wrap gap-1.5 pt-1">
                                                        <span v-for="(s, i) in productForm.subtypes" :key="i" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-rose-500/10 text-rose-300 border border-rose-500/30 rounded-lg text-xs">
                                                            {{ s.name }}
                                                            <button type="button" @click="removeSubtype(i)" class="text-rose-400 font-bold hover:text-rose-200">&times;</button>
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="pt-2 flex items-center justify-between">
                                                    <span class="text-[11px] text-slate-400">İstediğiniz kadar ürün ekleyebilirsiniz.</span>
                                                    <button type="submit" :disabled="productForm.processing" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs transition-all duration-200 shadow-lg shadow-emerald-950/60 hover:scale-[1.02] active:scale-95 flex items-center gap-2 cursor-pointer">
                                                        <span>+ Ürünü Ekle</span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="lg:col-span-5 bg-slate-950/30 p-5 rounded-2xl border border-slate-800 flex flex-col justify-between shadow-lg">
                                            <div>
                                                <h4 class="text-xs font-bold text-slate-300 mb-3 flex items-center justify-between">
                                                    <span>Mevcut Ürünler</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-emerald-400">{{ setupData.products.length }} Kayıt</span>
                                                </h4>
                                                <div class="space-y-2 overflow-y-auto max-h-60 pr-1">
                                                    <div v-for="p in setupData.products" :key="p.id" class="p-3 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between hover:border-slate-700 transition-all">
                                                        <div>
                                                            <div class="text-xs font-bold text-white">{{ p.name }}</div>
                                                            <div class="text-[10px] text-slate-400">{{ p.code || 'KODSUZ' }}</div>
                                                        </div>
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                            <button type="button" @click="deleteProduct(p)" class="p-1 rounded-md text-slate-500 hover:text-rose-400 hover:bg-slate-800 transition cursor-pointer" title="Sil">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div v-if="setupData.products.length === 0" class="text-center py-8 text-xs text-slate-500">
                                                        Henüz kayıtlı ürün yok. Soldaki formdan ekleyin.
                                                    </div>
                                                </div>
                                            </div>

                                            <div v-if="setupData.products.length > 0" class="pt-4 border-t border-slate-800/80 mt-3">
                                                <button type="button" @click="goToNextStep" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-emerald-600 hover:text-slate-950 text-emerald-300 font-bold text-xs transition-all flex items-center justify-center gap-2 cursor-pointer border border-emerald-500/30">
                                                    <span>5. Adıma Geç (İş Gücü Kadrosu)</span>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="currentStep === 5" class="space-y-6">
                                    <div class="p-4 rounded-2xl bg-teal-950/40 border border-teal-800/60 text-teal-200 shadow-sm">
                                        <div class="flex items-start gap-3">
                                            <div class="p-2 rounded-xl bg-teal-500/10 border border-teal-500/30 text-teal-400 shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black text-teal-100 uppercase tracking-wide">5. Adım İş Gücü Kadrosu</h4>
                                                <p class="text-xs text-teal-200/90 mt-1 leading-relaxed">
                                                    Mühendis, çavuş ve işçilerinizi kaydedin. İstediğiniz kadar personel ve işçi ekleyebilirsiniz.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                        <div class="lg:col-span-7 bg-slate-950/50 p-5 rounded-2xl border border-slate-800 space-y-4">
                                            <div class="flex gap-2 border-b border-slate-800 pb-3">
                                                <button type="button" @click="activePersonTab = 'personnel'" :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-150 cursor-pointer', activePersonTab === 'personnel' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/40 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/40']">1. Personel / Mühendis</button>
                                                <button type="button" @click="activePersonTab = 'crew'" :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-150 cursor-pointer', activePersonTab === 'crew' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/40 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/40']">2. Çavuş (Ekip Başı)</button>
                                                <button type="button" @click="activePersonTab = 'worker'" :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-150 cursor-pointer', activePersonTab === 'worker' ? 'bg-teal-500/20 text-teal-300 border border-teal-500/40 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/40']">3. Tarla İşçisi</button>
                                            </div>

                                            <form v-if="activePersonTab === 'personnel'" @submit.prevent="submitPersonnel" class="space-y-3.5">
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Ad <span class="text-rose-400">*</span></label>
                                                        <input v-model="personnelForm.first_name" type="text" required placeholder="Ad" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Soyad <span class="text-rose-400">*</span></label>
                                                        <input v-model="personnelForm.last_name" type="text" required placeholder="Soyad" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Ünvan / Görev</label>
                                                        <input v-model="personnelForm.role_title" type="text" placeholder="Örn: Ziraat Mühendisi, Saha Sorumlusu" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Telefon</label>
                                                        <input v-model="personnelForm.phone" type="text" placeholder="05XX XXX XX XX" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">E-posta (Sistem Girişi İçin)</label>
                                                        <input v-model="personnelForm.email" type="email" placeholder="muhendis@sasa.com" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Giriş Şifresi</label>
                                                        <input v-model="personnelForm.password" type="text" placeholder="Varsayılan: password" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                    </div>
                                                </div>

                                                <div class="space-y-2 p-3 rounded-xl bg-slate-900/90 border border-slate-800">
                                                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-800">
                                                        <label class="block text-xs font-bold text-slate-200">
                                                            Modül Erişim Yetkileri <span class="text-emerald-400 font-mono text-[11px]">({{ personnelForm.permissions.length }}/6 Seçili)</span>
                                                        </label>
                                                        <div class="flex items-center gap-1.5">
                                                            <button type="button" @click="selectAllPerms" class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/20 cursor-pointer">Tüm Yetkiler</button>
                                                            <button type="button" @click="selectStandardPerms" class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-800 text-slate-300 hover:text-white cursor-pointer">Standart</button>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-1.5 pt-1">
                                                        <div
                                                            v-for="mod in availableModules"
                                                            :key="mod.key"
                                                            @click="togglePersonnelPerm(mod.key)"
                                                            :class="[
                                                                'p-2 rounded-xl border transition-all duration-150 cursor-pointer select-none flex items-center gap-2',
                                                                personnelForm.permissions.includes(mod.key)
                                                                    ? 'bg-emerald-950/50 border-emerald-500/70 text-emerald-200 shadow-sm ring-1 ring-emerald-500/20'
                                                                    : 'bg-slate-950/60 border-slate-800 text-slate-400 hover:border-slate-700 hover:text-slate-300'
                                                            ]"
                                                        >
                                                            <div :class="['w-7 h-7 rounded-lg flex items-center justify-center shrink-0 transition-colors', personnelForm.permissions.includes(mod.key) ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-900 text-slate-500']">
                                                                <svg v-if="mod.key === 'tesis'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                                <svg v-else-if="mod.key === 'uretim'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                                <svg v-else-if="mod.key === 'teknik'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                                                <svg v-else-if="mod.key === 'operasyon'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                                                <svg v-else-if="mod.key === 'raporlar'" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                                            </div>
                                                            <div class="min-w-0">
                                                                <div class="text-[11px] font-bold truncate">{{ mod.label }}</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div v-if="setupData.companies.length > 1" class="space-y-1.5 p-3 rounded-xl bg-slate-900/90 border border-slate-800">
                                                    <label class="block text-xs font-bold text-slate-200">Yetkili Olduğu Şirketler</label>
                                                    <div class="flex flex-wrap gap-1.5">
                                                        <div
                                                            v-for="c in setupData.companies"
                                                            :key="c.id"
                                                            @click="togglePersonnelCompany(c.id)"
                                                            :class="[
                                                                'px-2.5 py-1 rounded-lg text-xs font-semibold border cursor-pointer select-none transition-all',
                                                                personnelForm.company_ids.includes(c.id) || personnelForm.company_ids.length === 0
                                                                    ? 'bg-teal-500/20 border-teal-500/50 text-teal-300'
                                                                    : 'bg-slate-950 border-slate-800 text-slate-500'
                                                            ]"
                                                        >
                                                            {{ c.name }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="pt-2 flex justify-end">
                                                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs transition-all duration-200 shadow-lg shadow-emerald-950/60 hover:scale-[1.02] active:scale-95 cursor-pointer">+ Personeli Ekle</button>
                                                </div>
                                            </form>

                                            <form v-if="activePersonTab === 'crew'" @submit.prevent="submitCrewLeader" class="space-y-4">
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Çavuş Adı <span class="text-rose-400">*</span></label>
                                                        <input v-model="crewLeaderForm.first_name" type="text" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Soyadı <span class="text-rose-400">*</span></label>
                                                        <input v-model="crewLeaderForm.last_name" type="text" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                    </div>
                                                </div>
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Varsayılan Günlük Yevmiye (TL)</label>
                                                        <input v-model="crewLeaderForm.daily_wage" type="number" placeholder="Örn: 1000" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Telefon</label>
                                                        <input v-model="crewLeaderForm.phone" type="text" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                    </div>
                                                </div>
                                                <div class="pt-2 flex justify-end">
                                                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs transition-all duration-200 shadow-lg shadow-emerald-950/60 hover:scale-[1.02] active:scale-95 cursor-pointer">+ Çavuşu Ekle</button>
                                                </div>
                                            </form>

                                            <form v-if="activePersonTab === 'worker'" @submit.prevent="submitWorker" class="space-y-4">
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">İşçi Adı <span class="text-rose-400">*</span></label>
                                                        <input v-model="workerForm.first_name" type="text" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Soyadı <span class="text-rose-400">*</span></label>
                                                        <input v-model="workerForm.last_name" type="text" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Bağlı Olduğu Çavuş</label>
                                                    <select v-model="workerForm.crew_leader_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all">
                                                        <option value="">Çavuşsuz / Doğrudan</option>
                                                        <option v-for="cl in setupData.crewLeaders" :key="cl.id" :value="cl.id">{{ cl.first_name }} {{ cl.last_name }}</option>
                                                    </select>
                                                </div>
                                                <div class="pt-2 flex justify-end">
                                                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs transition-all duration-200 shadow-lg shadow-emerald-950/60 hover:scale-[1.02] active:scale-95 cursor-pointer">+ İşçiyi Ekle</button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="lg:col-span-5 bg-slate-950/30 p-5 rounded-2xl border border-slate-800 flex flex-col justify-between shadow-lg">
                                            <div>
                                                <h4 class="text-xs font-bold text-slate-300 mb-3 flex items-center justify-between">
                                                    <span>Kayıtlı Kadro</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-emerald-400">{{ setupData.personnels.length + setupData.crewLeaders.length + setupData.workers.length }} Kayıt</span>
                                                </h4>
                                                <div class="space-y-2 overflow-y-auto max-h-64 pr-1">
                                                    <div v-for="p in setupData.personnels" :key="'p-'+p.id" class="p-2.5 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between">
                                                        <div>
                                                            <div class="text-xs font-bold text-white">{{ p.first_name }} {{ p.last_name }}</div>
                                                            <div class="text-[10px] text-teal-400">{{ p.role_title || 'Personel' }}</div>
                                                        </div>
                                                        <button type="button" @click="deletePersonnel(p)" class="p-1 text-slate-500 hover:text-rose-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                                    </div>
                                                    <div v-for="c in setupData.crewLeaders" :key="'c-'+c.id" class="p-2.5 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between">
                                                        <div>
                                                            <div class="text-xs font-bold text-white">{{ c.first_name }} {{ c.last_name }}</div>
                                                            <div class="text-[10px] text-amber-400">Çavuş • Yevmiye: {{ c.daily_wage }} TL</div>
                                                        </div>
                                                        <button type="button" @click="deleteCrewLeader(c)" class="p-1 text-slate-500 hover:text-rose-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                                    </div>
                                                    <div v-for="w in setupData.workers" :key="'w-'+w.id" class="p-2.5 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between">
                                                        <div>
                                                            <div class="text-xs font-bold text-white">{{ w.first_name }} {{ w.last_name }}</div>
                                                            <div class="text-[10px] text-slate-400">İşçi</div>
                                                        </div>
                                                        <button type="button" @click="deleteWorker(w)" class="p-1 text-slate-500 hover:text-rose-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                                    </div>
                                                    <div v-if="setupData.personnels.length === 0 && setupData.crewLeaders.length === 0 && setupData.workers.length === 0" class="text-center py-8 text-xs text-slate-500">
                                                        Henüz kayıtlı iş gücü yok.
                                                    </div>
                                                </div>
                                            </div>

                                            <div v-if="setupData.personnels.length > 0 || setupData.crewLeaders.length > 0 || setupData.workers.length > 0" class="pt-4 border-t border-slate-800/80 mt-3">
                                                <button type="button" @click="goToNextStep" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-emerald-600 hover:text-slate-950 text-emerald-300 font-bold text-xs transition-all flex items-center justify-center gap-2 cursor-pointer border border-emerald-500/30">
                                                    <span>6. Adıma Geç (İş Tipleri)</span>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="currentStep === 6" class="space-y-6">
                                    <div class="p-4 rounded-2xl bg-cyan-950/40 border border-cyan-800/60 text-cyan-200 shadow-sm">
                                        <div class="flex items-start gap-3">
                                            <div class="p-2 rounded-xl bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black text-cyan-100 uppercase tracking-wide">6. Adım İş Tipleri & Operasyonlar</h4>
                                                <p class="text-xs text-cyan-200/90 mt-1 leading-relaxed">
                                                    Hasat, budama, çapalama vb. iş tiplerini ekleyin. İstediğiniz kadar operasyon tanımlayabilirsiniz.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                        <div class="lg:col-span-7 bg-slate-950/50 p-5 rounded-2xl border border-slate-800">
                                            <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                                                <span class="w-2 h-2 rounded-full bg-cyan-400 shadow-[0_0_8px_rgba(34,211,238,0.8)]"></span>
                                                Yeni İş Tipi Tanımla
                                            </h3>
                                            <form @submit.prevent="submitJobType" class="space-y-4">
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">İş Tanımı <span class="text-rose-400">*</span></label>
                                                        <input v-model="jobTypeForm.name" type="text" placeholder="Örn: Hasat Toplama, Dal Budama" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Kategori / Form Tipi</label>
                                                        <select v-model="jobTypeForm.form_type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all">
                                                            <option value="daily_labor">Günlük İşçi Formu</option>
                                                            <option value="harvest">Hasat Formu</option>
                                                            <option value="maintenance">Bakım & Budama</option>
                                                            <option value="other">Diğer İşçilik</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Bağlı Ölçü Birimi</label>
                                                    <select v-model="jobTypeForm.selected_unit_id" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all">
                                                        <option value="">Birim Seçin (İsteğe Bağlı)</option>
                                                        <option v-for="u in setupData.units" :key="u.id" :value="u.id">{{ u.name }} ({{ u.symbol || u.code }})</option>
                                                    </select>
                                                </div>
                                                <div class="pt-2 flex items-center justify-between">
                                                    <span class="text-[11px] text-slate-400">İstediğiniz kadar görev tipi ekleyebilirsiniz.</span>
                                                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs transition-all duration-200 shadow-lg shadow-emerald-950/60 hover:scale-[1.02] active:scale-95 cursor-pointer">+ İş Tipini Ekle</button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="lg:col-span-5 bg-slate-950/30 p-5 rounded-2xl border border-slate-800 flex flex-col justify-between shadow-lg">
                                            <div>
                                                <h4 class="text-xs font-bold text-slate-300 mb-3 flex items-center justify-between">
                                                    <span>Mevcut İş Tipleri</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-emerald-400">{{ setupData.jobTypes.length }} Kayıt</span>
                                                </h4>
                                                <div class="space-y-2 overflow-y-auto max-h-60 pr-1">
                                                    <div v-for="j in setupData.jobTypes" :key="j.id" class="p-3 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between hover:border-slate-700 transition-all">
                                                        <span class="text-xs font-bold text-white">{{ j.name }}</span>
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                            <button type="button" @click="deleteJobType(j)" class="p-1 rounded-md text-slate-500 hover:text-rose-400 hover:bg-slate-800 transition cursor-pointer" title="Sil">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div v-if="setupData.jobTypes.length === 0" class="text-center py-8 text-xs text-slate-500">
                                                        Henüz kayıtlı iş tipi yok. Soldan ekleyin.
                                                    </div>
                                                </div>
                                            </div>

                                            <div v-if="setupData.jobTypes.length > 0" class="pt-4 border-t border-slate-800/80 mt-3">
                                                <button type="button" @click="goToNextStep" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-emerald-600 hover:text-slate-950 text-emerald-300 font-bold text-xs transition-all flex items-center justify-center gap-2 cursor-pointer border border-emerald-500/30">
                                                    <span>7. Adıma Geç (Altyapı & Su)</span>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="currentStep === 7" class="space-y-6">
                                    <div class="p-4 rounded-2xl bg-blue-950/40 border border-blue-800/60 text-blue-200 shadow-sm">
                                        <div class="flex items-start gap-3">
                                            <div class="p-2 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-400 shrink-0">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black text-blue-100 uppercase tracking-wide">7. Adım Altyapı, Su & Reçeteler</h4>
                                                <p class="text-xs text-blue-200/90 mt-1 leading-relaxed">
                                                    Sulama kuyu/gölet kaynakları, filtre istasyonları ve gübre reçetelerinizi tanımlayın.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                        <div class="lg:col-span-7 bg-slate-950/50 p-5 rounded-2xl border border-slate-800 space-y-4">
                                            <div class="flex gap-2 border-b border-slate-800 pb-3">
                                                <button type="button" @click="activeInfraTab = 'water'" :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-150 cursor-pointer', activeInfraTab === 'water' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/40 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/40']">1. Su Kaynağı (Kuyu/Gölet)</button>
                                                <button type="button" @click="activeInfraTab = 'filter'" :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-150 cursor-pointer', activeInfraTab === 'filter' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/40 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/40']">2. Filtre İstasyonu</button>
                                                <button type="button" @click="activeInfraTab = 'recipe'" :class="['px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-150 cursor-pointer', activeInfraTab === 'recipe' ? 'bg-blue-500/20 text-blue-300 border border-blue-500/40 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/40']">3. Gübre Reçetesi</button>
                                            </div>

                                            <form v-if="activeInfraTab === 'water'" @submit.prevent="submitWater" class="space-y-4">
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Kaynak Adı <span class="text-rose-400">*</span></label>
                                                        <input v-model="waterForm.name" type="text" placeholder="Örn: 1 Nolu Derin Kuyu" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Bağlı Üretim Yeri <span class="text-rose-400">*</span></label>
                                                        <select v-model="waterForm.production_location_id" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all">
                                                            <option value="">Tesis Seçin</option>
                                                            <option v-for="l in setupData.productionLocations" :key="l.id" :value="l.id">{{ l.name }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="pt-2 flex justify-end">
                                                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs transition-all duration-200 shadow-lg shadow-emerald-950/60 hover:scale-[1.02] active:scale-95 cursor-pointer">+ Su Kaynağını Ekle</button>
                                                </div>
                                            </form>

                                            <form v-if="activeInfraTab === 'filter'" @submit.prevent="submitFilter" class="space-y-4">
                                                <div class="grid grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Filtre Adı <span class="text-rose-400">*</span></label>
                                                        <input v-model="filterForm.name" type="text" placeholder="Örn: Ana Disk Filtre Grubu" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Bağlı Üretim Yeri <span class="text-rose-400">*</span></label>
                                                        <select v-model="filterForm.production_location_id" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all">
                                                            <option value="">Tesis Seçin</option>
                                                            <option v-for="l in setupData.productionLocations" :key="l.id" :value="l.id">{{ l.name }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="pt-2 flex justify-end">
                                                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs transition-all duration-200 shadow-lg shadow-emerald-950/60 hover:scale-[1.02] active:scale-95 cursor-pointer">+ Filtreyi Ekle</button>
                                                </div>
                                            </form>

                                            <form v-if="activeInfraTab === 'recipe'" @submit.prevent="submitRecipe" class="space-y-4">
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Reçete Adı <span class="text-rose-400">*</span></label>
                                                    <input v-model="recipeForm.name" type="text" placeholder="Örn: Çilek Gelişme Dönemi A+B" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-all" />
                                                </div>
                                                <div class="pt-2 flex justify-end">
                                                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs transition-all duration-200 shadow-lg shadow-emerald-950/60 hover:scale-[1.02] active:scale-95 cursor-pointer">+ Reçeteyi Ekle</button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="lg:col-span-5 bg-slate-950/30 p-5 rounded-2xl border border-slate-800 flex flex-col justify-between shadow-lg">
                                            <div>
                                                <h4 class="text-xs font-bold text-slate-300 mb-3 flex items-center justify-between">
                                                    <span>Mevcut Altyapı</span>
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-emerald-400">{{ setupData.waterSources.length + setupData.filters.length + setupData.fertilizationRecipes.length }} Kayıt</span>
                                                </h4>
                                                <div class="space-y-2 overflow-y-auto max-h-60 pr-1">
                                                    <div v-for="w in setupData.waterSources" :key="'w-'+w.id" class="p-2.5 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between">
                                                        <div>
                                                            <div class="text-xs font-bold text-white">{{ w.name }}</div>
                                                            <div class="text-[10px] text-blue-400">Su Kaynağı</div>
                                                        </div>
                                                        <button type="button" @click="deleteWaterSource(w)" class="p-1 text-slate-500 hover:text-rose-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                                    </div>
                                                    <div v-for="f in setupData.filters" :key="'f-'+f.id" class="p-2.5 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between">
                                                        <div>
                                                            <div class="text-xs font-bold text-white">{{ f.name }}</div>
                                                            <div class="text-[10px] text-indigo-400">Filtre İstasyonu</div>
                                                        </div>
                                                        <button type="button" @click="deleteFilter(f)" class="p-1 text-slate-500 hover:text-rose-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                                    </div>
                                                    <div v-for="r in setupData.fertilizationRecipes" :key="'r-'+r.id" class="p-2.5 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between">
                                                        <div>
                                                            <div class="text-xs font-bold text-white">{{ r.name }}</div>
                                                            <div class="text-[10px] text-emerald-400">Gübre Reçetesi</div>
                                                        </div>
                                                        <button type="button" @click="deleteRecipe(r)" class="p-1 text-slate-500 hover:text-rose-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                                    </div>
                                                    <div v-if="setupData.waterSources.length === 0 && setupData.filters.length === 0 && setupData.fertilizationRecipes.length === 0" class="text-center py-8 text-xs text-slate-500">
                                                        Henüz kayıtlı altyapı yok.
                                                    </div>
                                                </div>
                                            </div>

                                            <div v-if="setupData.waterSources.length > 0 || setupData.filters.length > 0 || setupData.fertilizationRecipes.length > 0" class="pt-4 border-t border-slate-800/80 mt-3">
                                                <button type="button" @click="goToNextStep" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-emerald-600 hover:text-slate-950 text-emerald-300 font-bold text-xs transition-all flex items-center justify-center gap-2 cursor-pointer border border-emerald-500/30">
                                                    <span>8. Adıma Geç (Kurulum Özeti)</span>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="currentStep === 8" class="space-y-6">
                                    <div class="text-center py-6">
                                        <div class="w-16 h-16 rounded-3xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center mx-auto mb-4 shadow-[0_0_30px_rgba(16,185,129,0.3)] animate-pulse">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <h3 class="text-xl font-black text-white">Sistem Kurulumu Tamamlandı!</h3>
                                        <p class="text-xs text-slate-400 max-w-md mx-auto mt-2 leading-relaxed">
                                            Tanımlamalarınız başarıyla kaydedildi. Artık SASA ERP üzerinden tüm tarım ve saha operasyonlarınızı yürütebilirsiniz.
                                        </p>
                                    </div>

                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                        <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800 text-center hover:border-emerald-500/40 hover:scale-105 transition-all duration-200">
                                            <div class="text-lg font-black text-emerald-400">{{ setupData.companies.length }}</div>
                                            <div class="text-[11px] font-semibold text-slate-400 mt-0.5">Firma</div>
                                        </div>
                                        <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800 text-center hover:border-emerald-500/40 hover:scale-105 transition-all duration-200">
                                            <div class="text-lg font-black text-emerald-400">{{ setupData.productionLocations.length }}</div>
                                            <div class="text-[11px] font-semibold text-slate-400 mt-0.5">Üretim Yeri</div>
                                        </div>
                                        <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800 text-center hover:border-emerald-500/40 hover:scale-105 transition-all duration-200">
                                            <div class="text-lg font-black text-emerald-400">{{ setupData.products.length }}</div>
                                            <div class="text-[11px] font-semibold text-slate-400 mt-0.5">Ürün</div>
                                        </div>
                                        <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800 text-center hover:border-emerald-500/40 hover:scale-105 transition-all duration-200">
                                            <div class="text-lg font-black text-emerald-400">{{ setupData.personnels.length + setupData.crewLeaders.length + setupData.workers.length }}</div>
                                            <div class="text-[11px] font-semibold text-slate-400 mt-0.5">İş Gücü</div>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-center gap-4 pt-4">
                                        <button @click="closeWizard" class="px-8 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-black text-xs transition-all duration-200 shadow-xl shadow-emerald-950 hover:shadow-[0_0_30px_rgba(16,185,129,0.5)] hover:scale-105 active:scale-95 cursor-pointer">
                                            Sistemi Kullanmaya Başla
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </Transition>
                    </div>

                    <div class="px-6 py-4 bg-slate-900/95 border-t border-slate-800 flex items-center justify-between shrink-0">
                        <button
                            v-if="currentStep > 1"
                            type="button"
                            @click="currentStep--"
                            class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all duration-150 hover:scale-105 active:scale-95 flex items-center gap-1.5 cursor-pointer"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Önceki Adım</span>
                        </button>
                        <div v-else></div>

                        <div class="flex items-center gap-3">
                            <button
                                v-if="currentStep < 8"
                                type="button"
                                @click="currentStep++"
                                class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all duration-150 hover:scale-105 active:scale-95 flex items-center gap-1.5 cursor-pointer"
                            >
                                <span>Sonraki Adım / Atla</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                            <button
                                v-else
                                type="button"
                                @click="closeWizard"
                                class="px-6 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-slate-950 text-xs font-black transition-all duration-150 hover:scale-105 active:scale-95 cursor-pointer shadow-lg shadow-emerald-950"
                            >
                                Kapat
                            </button>
                        </div>
                    </div>

                </div>
            </Transition>
        </div>
    </Transition>
</template>

<style scoped>
.step-fade-enter-active,
.step-fade-leave-active {
    transition: opacity 0.18s ease, transform 0.18s ease;
}

.step-fade-enter-from {
    opacity: 0;
    transform: translateY(6px);
}

.step-fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>
