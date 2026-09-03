<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();

const props = defineProps<{
    activeTab?: string;
    companies: any[];
    personnels: any[];
    tradingParties: any[];
    deliveryTypes: any[];
    productionLocations: any[];
    products: any[];
    jobTypes: any[];
    units: any[];
    packagings: any[];
    crewLeaders: any[];
    workers: any[];
    cateringSuppliers: any[];
    fertilizationRecipes: any[];
    sprayingRecipes: any[];
    waterSources: any[];
    filters: any[];
}>();

const getInitialTab = () => {
    if (typeof window !== 'undefined') {
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab');
        if (tab) return tab;
    }
    return props.activeTab || 'companies';
};

const currentTab = ref(getInitialTab());

watch(() => page.url, () => {
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab');
    if (tab) currentTab.value = tab;
});

const showModal = ref(false);
const modalType = ref('');
const editItemData = ref<any>(null);
const modalTitles: Record<string, string> = {
    company: 'Firma',
    personnel: 'Personel',
    trading_party: 'Cari Taraf (Alıcı/Satıcı)',
    delivery_type: 'Teslimat Şekli',
    production_location: 'Üretim Yeri',
    product: 'Ürün',
    job_type: 'İş Tanımı',
    unit: 'Birim',
    packaging: 'Paketleme',
    crew_leader: 'Çavuş',
    worker: 'İşçi',
    catering_supplier: 'Yemek Tedarikçisi',
    fert_recipe: 'Gübreleme Reçetesi',
    spray_recipe: 'İlaçlama Reçetesi',
    water_source: 'Su Kaynağı',
    filter: 'Filtre',
};

const showCrewLeaderWorkersModal = ref(false);
const selectedCrewLeader = ref<any>(null);

const openCrewLeaderWorkersModal = (cl: any) => {
    selectedCrewLeader.value = cl;
    showCrewLeaderWorkersModal.value = true;
};

const closeCrewLeaderWorkersModal = () => {
    showCrewLeaderWorkersModal.value = false;
    selectedCrewLeader.value = null;
};

const getCrewLeaderWorkers = (crewLeaderId: number) => {
    if (!props.workers) return [];
    return props.workers.filter((w: any) => Number(w.crew_leader_id) === Number(crewLeaderId));
};

const openAddWorkerForCrewLeader = (cl: any) => {
    closeCrewLeaderWorkersModal();
    workerForm.reset();
    workerForm.crew_leader_id = cl.id;
    openModal('worker');
};

const editWorkerFromModal = (w: any) => {
    closeCrewLeaderWorkersModal();
    openModal('worker', w);
};

const workerSearchQuery = ref('');
const workerCrewLeaderFilter = ref<string>('all');
const workerPerformanceFilter = ref<string>('all');
const workerStatusFilter = ref<string>('all');

const resetWorkerFilters = () => {
    workerSearchQuery.value = '';
    workerCrewLeaderFilter.value = 'all';
    workerPerformanceFilter.value = 'all';
    workerStatusFilter.value = 'all';
};

const filteredWorkers = computed(() => {
    return (props.workers || []).filter((w: any) => {
        if (workerCrewLeaderFilter.value !== 'all') {
            if (workerCrewLeaderFilter.value === 'independent') {
                if (w.crew_leader_id) return false;
            } else if (String(w.crew_leader_id) !== String(workerCrewLeaderFilter.value)) {
                return false;
            }
        }
        if (workerPerformanceFilter.value !== 'all') {
            if (workerPerformanceFilter.value === '5' && Number(w.performance_rating) !== 5) return false;
            if (workerPerformanceFilter.value === '4plus' && Number(w.performance_rating || 0) < 4) return false;
            if (workerPerformanceFilter.value === '3minus' && Number(w.performance_rating || 0) > 3) return false;
        }
        if (workerStatusFilter.value !== 'all') {
            const isActive = workerStatusFilter.value === 'active';
            if (!!w.is_active !== isActive) return false;
        }
        if (workerSearchQuery.value.trim()) {
            const q = workerSearchQuery.value.trim().toLowerCase();
            const fullName = `${w.first_name || ''} ${w.last_name || ''}`.toLowerCase();
            const tc = (w.identity_number || '').toLowerCase();
            const notes = (w.notes || '').toLowerCase();
            const clName = w.crew_leader ? `${w.crew_leader.first_name || ''} ${w.crew_leader.last_name || ''}`.toLowerCase() : '';
            if (!fullName.includes(q) && !tc.includes(q) && !notes.includes(q) && !clName.includes(q)) return false;
        }
        return true;
    });
});

const getLocationTypeLabel = (type: string) => {
    switch (type) {
        case 'greenhouse': return 'Sera';
        case 'open_field': return 'Açık Bahçe';
        case 'mixed': return 'Karışık';
        default: return type || 'Tesis';
    }
};

const getPersonnelCompanies = (companyIds: any[]) => {
    if (!companyIds || companyIds.length === 0) return [];
    return props.companies.filter(c => companyIds.includes(c.id));
};

const getCompanyTotalDekar = (c: any) => {
    if (!c.production_locations || c.production_locations.length === 0) return 0;
    return c.production_locations.reduce((acc: number, cur: any) => acc + Number(cur.total_area_dekar || 0), 0);
};

const companyForm = useForm({
    id: null,
    name: '',
    code: '',
    tax_number: '',
    dia_company_code: '',
    is_active: true,
});

const personnelForm = useForm({
    id: null,
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    password: '',
    role_title: '',
    parent_personnel_id: null,
    company_ids: [] as any[],
    permissions: [] as any[],
    can_enter_backdated_data: false,
    is_active: true,
});

const tradingPartyForm = useForm({
    id: null,
    name: '',
    type: 'buyer',
    dia_cari_code: '',
    tax_number: '',
    phone: '',
    email: '',
    address: '',
    default_transport_fee: 0,
});

const deliveryTypeForm = useForm({
    id: null,
    name: '',
    code: '',
    transport_by: 'customer',
    is_fee_included: true,
    extra_fee: 0,
});

const productionLocationForm = useForm({
    id: null,
    company_id: props.companies[0]?.id || null,
    name: '',
    location_type: 'greenhouse',
    dia_branch_code: '',
    dia_warehouse_code: '',
    total_area_dekar: 0,
    approx_plant_count: 0,
    sections: [
        { name: 'Sera 1 - Tünel 1', section_type: 'greenhouse', area_dekar: 2.5, tunnel_count: 14, table_stand_count: 20 }
    ],
    valves: [
        { valve_number: 'V-01', name: 'Vana 1 Sulama', duty: 'irrigation', description: 'Ana damlama hattı' },
        { valve_number: 'V-22', name: 'Vana 22 Sisleme', duty: 'misting', description: 'Nem ve serinletme sislemesi' }
    ],
});

const productForm = useForm({
    id: null,
    name: '',
    code: '',
    product_type: 'produced',
    dia_stock_code: '',
    description: '',
    company_ids: [] as any[],
    subtypes: [
        { name: 'Çilek 1.Kalite', code: 'CLK-1' },
        { name: 'Çilek 2.Kalite', code: 'CLK-2' }
    ],
});

const jobTypeForm = useForm({
    id: null,
    name: '',
    code: '',
    form_type: 'general',
    description: '',
    unit_ids: [] as any[],
});

const unitForm = useForm({
    id: null,
    name: '',
    symbol: '',
    unit_category: 'quantity',
});

const packagingForm = useForm({
    id: null,
    name: '',
    code: '',
    product_id: props.products[0]?.id || null,
    dia_stock_code: '',
    capacity_qty: 1,
    unit_id: props.units[0]?.id || null,
});

const crewLeaderForm = useForm({
    id: null,
    first_name: '',
    last_name: '',
    identity_number: '',
    phone: '',
    origin_city: 'Antalya',
    daily_wage: 500,
    multiplier: 1.0,
    is_leader_fee_included: true,
    min_car_requirement: 2,
    travel_fee_per_car: 100,
    is_food_included: false,
    dia_cari_code: '',
});

const workerForm = useForm({
    id: null,
    crew_leader_id: props.crewLeaders[0]?.id || null,
    first_name: '',
    last_name: '',
    identity_number: '',
    performance_rating: 4,
    notes: '',
    is_active: true,
});

const cateringSupplierForm = useForm({
    id: null,
    company_title: '',
    contact_person: '',
    phone: '',
    meal_unit_price: 65,
    is_vat_included: true,
    dia_cari_code: '',
});

const fertRecipeForm = useForm({
    id: null,
    name: '',
    creation_date: new Date().toISOString().split('T')[0],
    duration_condition: 'Çiçeklenme başlayana kadar',
    creator_name: 'Ziraat Müh. Ahmet',
    water_ph: 6.5,
    water_ec: 1.8,
    water_notes: 'Damlama suyuna göre solüsyon',
    is_active: true,
    tanks: [
        {
            tank_name: 'A Tankı (Solüsyon)',
            capacity_liters: 1000,
            items: [
                { product_name: 'Kalsiyum Nitrat', brand: 'GÜBRETAŞ', quantity: 25, unit: 'kg', usage_purpose: 'Kök Gelişimi', description: 'İyice karıştırılmalı' }
            ]
        }
    ]
});

const sprayRecipeForm = useForm({
    id: null,
    name: '',
    creation_date: new Date().toISOString().split('T')[0],
    usage_time: 'Akşamüstü rüzgarsız saatler',
    usage_purpose: 'Kırmızı Örümcek Mücadelesi',
    water_volume_liters: 400,
    application_method: 'sprayer_machine',
    is_active: true,
    items: [
        { product_name: 'Abamectin 18g/l', brand: 'Bayer', quantity: 250, unit: 'ml', notes: 'Maske ile uygulanmalı' }
    ]
});

const waterSourceForm = useForm({
    id: null,
    production_location_id: props.productionLocations[0]?.id || null,
    name: '',
    active_cycle_minutes: 60,
    passive_cycle_minutes: 120,
    requires_photo_verification: true,
    is_active: true,
});

const filterForm = useForm({
    id: null,
    production_location_id: props.productionLocations[0]?.id || null,
    name: '',
    cleaning_cycle_days: 2,
    requires_photo_verification: true,
    is_active: true,
    water_source_ids: [] as any[],
});

const openModal = (type: string, item: any = null) => {
    modalType.value = type;
    editItemData.value = item;

    if (type === 'company') {
        companyForm.reset();
        if (item) {
            companyForm.id = item.id;
            companyForm.name = item.name;
            companyForm.code = item.code;
            companyForm.tax_number = item.tax_number;
            companyForm.dia_company_code = item.dia_company_code;
            companyForm.is_active = !!item.is_active;
        }
    } else if (type === 'personnel') {
        personnelForm.reset();
        if (item) {
            personnelForm.id = item.id;
            personnelForm.first_name = item.first_name;
            personnelForm.last_name = item.last_name;
            personnelForm.phone = item.phone || '';
            personnelForm.email = item.user?.email || '';
            personnelForm.password = '';
            personnelForm.role_title = item.role_title;
            personnelForm.parent_personnel_id = item.parent_personnel_id;
            personnelForm.company_ids = item.company_ids || [];
            personnelForm.permissions = item.permissions || [];
            personnelForm.can_enter_backdated_data = !!item.can_enter_backdated_data;
            personnelForm.is_active = !!item.is_active;
        }
    } else if (type === 'trading_party') {
        tradingPartyForm.reset();
        if (item) {
            tradingPartyForm.id = item.id;
            tradingPartyForm.name = item.name;
            tradingPartyForm.type = item.type;
            tradingPartyForm.dia_cari_code = item.dia_cari_code;
            tradingPartyForm.tax_number = item.tax_number;
            tradingPartyForm.phone = item.phone;
            tradingPartyForm.email = item.email;
            tradingPartyForm.address = item.address;
            tradingPartyForm.default_transport_fee = item.default_transport_fee;
        }
    } else if (type === 'delivery_type') {
        deliveryTypeForm.reset();
        if (item) {
            deliveryTypeForm.id = item.id;
            deliveryTypeForm.name = item.name;
            deliveryTypeForm.code = item.code;
            deliveryTypeForm.transport_by = item.transport_by;
            deliveryTypeForm.is_fee_included = !!item.is_fee_included;
            deliveryTypeForm.extra_fee = item.extra_fee;
        }
    } else if (type === 'production_location') {
        productionLocationForm.reset();
        if (item) {
            productionLocationForm.id = item.id;
            productionLocationForm.company_id = item.company_id;
            productionLocationForm.name = item.name;
            productionLocationForm.location_type = item.location_type;
            productionLocationForm.dia_branch_code = item.dia_branch_code;
            productionLocationForm.dia_warehouse_code = item.dia_warehouse_code;
            productionLocationForm.total_area_dekar = item.total_area_dekar;
            productionLocationForm.approx_plant_count = item.approx_plant_count;
        }
    } else if (type === 'product') {
        productForm.reset();
        if (item) {
            productForm.id = item.id;
            productForm.name = item.name;
            productForm.code = item.code;
            productForm.product_type = item.product_type;
            productForm.dia_stock_code = item.dia_stock_code;
            productForm.description = item.description;
            productForm.company_ids = item.companies ? item.companies.map((c: any) => c.id) : [];
            productForm.subtypes = item.subtypes && item.subtypes.length 
                ? item.subtypes.map((st: any) => ({ name: st.name, code: st.code })) 
                : [{ name: '', code: '' }];
        }
    } else if (type === 'job_type') {
        jobTypeForm.reset();
        if (item) {
            jobTypeForm.id = item.id;
            jobTypeForm.name = item.name;
            jobTypeForm.code = item.code;
            jobTypeForm.form_type = item.form_type;
            jobTypeForm.description = item.description;
        }
    } else if (type === 'unit') {
        unitForm.reset();
        if (item) {
            unitForm.id = item.id;
            unitForm.name = item.name;
            unitForm.symbol = item.symbol;
            unitForm.unit_category = item.unit_category;
        }
    } else if (type === 'packaging') {
        packagingForm.reset();
        if (item) {
            packagingForm.id = item.id;
            packagingForm.name = item.name;
            packagingForm.code = item.code;
            packagingForm.product_id = item.product_id;
            packagingForm.dia_stock_code = item.dia_stock_code;
            packagingForm.capacity_qty = item.capacity_qty;
            packagingForm.unit_id = item.unit_id;
        }
    } else if (type === 'crew_leader') {
        crewLeaderForm.reset();
        if (item) {
            crewLeaderForm.id = item.id;
            crewLeaderForm.first_name = item.first_name;
            crewLeaderForm.last_name = item.last_name;
            crewLeaderForm.identity_number = item.identity_number;
            crewLeaderForm.phone = item.phone;
            crewLeaderForm.origin_city = item.origin_city;
            crewLeaderForm.daily_wage = item.daily_wage;
            crewLeaderForm.multiplier = item.multiplier;
            crewLeaderForm.is_leader_fee_included = !!item.is_leader_fee_included;
            crewLeaderForm.min_car_requirement = item.min_car_requirement;
            crewLeaderForm.travel_fee_per_car = item.travel_fee_per_car;
            crewLeaderForm.is_food_included = !!item.is_food_included;
            crewLeaderForm.dia_cari_code = item.dia_cari_code;
        }
    } else if (type === 'worker') {
        workerForm.reset();
        if (item) {
            workerForm.id = item.id;
            workerForm.crew_leader_id = item.crew_leader_id;
            workerForm.first_name = item.first_name;
            workerForm.last_name = item.last_name;
            workerForm.identity_number = item.identity_number;
            workerForm.performance_rating = item.performance_rating;
            workerForm.notes = item.notes;
            workerForm.is_active = !!item.is_active;
        }
    } else if (type === 'catering_supplier') {
        cateringSupplierForm.reset();
        if (item) {
            cateringSupplierForm.id = item.id;
            cateringSupplierForm.company_title = item.company_title;
            cateringSupplierForm.contact_person = item.contact_person;
            cateringSupplierForm.phone = item.phone;
            cateringSupplierForm.meal_unit_price = item.meal_unit_price;
            cateringSupplierForm.is_vat_included = !!item.is_vat_included;
            cateringSupplierForm.dia_cari_code = item.dia_cari_code;
        }
    } else if (type === 'water_source') {
        waterSourceForm.reset();
        if (item) {
            waterSourceForm.id = item.id;
            waterSourceForm.production_location_id = item.production_location_id;
            waterSourceForm.name = item.name;
            waterSourceForm.active_cycle_minutes = item.active_cycle_minutes;
            waterSourceForm.passive_cycle_minutes = item.passive_cycle_minutes;
            waterSourceForm.requires_photo_verification = !!item.requires_photo_verification;
            waterSourceForm.is_active = !!item.is_active;
        }
    } else if (type === 'filter') {
        filterForm.reset();
        if (item) {
            filterForm.id = item.id;
            filterForm.production_location_id = item.production_location_id;
            filterForm.name = item.name;
            filterForm.cleaning_cycle_days = item.cleaning_cycle_days;
            filterForm.requires_photo_verification = !!item.requires_photo_verification;
            filterForm.is_active = !!item.is_active;
            filterForm.water_source_ids = item.water_sources?.map((ws: any) => ws.id) || [];
        }
    } else if (type === 'fert_recipe') {
        fertRecipeForm.reset();
        if (item) {
            fertRecipeForm.id = item.id;
            fertRecipeForm.name = item.name;
            fertRecipeForm.creation_date = item.creation_date;
            fertRecipeForm.duration_condition = item.duration_condition || '';
            fertRecipeForm.creator_name = item.creator_name || '';
            fertRecipeForm.water_ph = item.water_ph;
            fertRecipeForm.water_ec = item.water_ec;
            fertRecipeForm.water_notes = item.water_notes || '';
            fertRecipeForm.is_active = !!item.is_active;
            fertRecipeForm.tanks = item.tanks?.map((t: any) => ({
                tank_name: t.tank_name,
                capacity_liters: t.capacity_liters,
                items: t.items?.map((i: any) => ({
                    product_name: i.product_name,
                    brand: i.brand || '',
                    quantity: i.quantity,
                    unit: i.unit,
                    usage_purpose: i.usage_purpose || '',
                    description: i.description || '',
                })) || []
            })) || [];
        }
    } else if (type === 'spray_recipe') {
        sprayRecipeForm.reset();
        if (item) {
            sprayRecipeForm.id = item.id;
            sprayRecipeForm.name = item.name;
            sprayRecipeForm.creation_date = item.creation_date;
            sprayRecipeForm.usage_time = item.usage_time || '';
            sprayRecipeForm.usage_purpose = item.usage_purpose || '';
            sprayRecipeForm.water_volume_liters = item.water_volume_liters;
            sprayRecipeForm.application_method = item.application_method;
            sprayRecipeForm.is_active = !!item.is_active;
            sprayRecipeForm.items = item.items?.map((i: any) => ({
                product_name: i.product_name,
                brand: i.brand || '',
                quantity: i.quantity,
                unit: i.unit,
                notes: i.notes || '',
            })) || [];
        }
    }

    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitCompany = () => companyForm.post(route('definitions.companies.store'), { onSuccess: () => closeModal() });
const submitPersonnel = () => personnelForm.post(route('definitions.personnels.store'), { onSuccess: () => closeModal() });
const submitTradingParty = () => tradingPartyForm.post(route('definitions.trading-parties.store'), { onSuccess: () => closeModal() });
const submitDeliveryType = () => deliveryTypeForm.post(route('definitions.delivery-types.store'), { onSuccess: () => closeModal() });
const submitProductionLocation = () => productionLocationForm.post(route('definitions.production-locations.store'), { onSuccess: () => closeModal() });
const submitProduct = () => productForm.post(route('definitions.products.store'), { onSuccess: () => closeModal() });
const submitJobType = () => jobTypeForm.post(route('definitions.job-types.store'), { onSuccess: () => closeModal() });
const submitUnit = () => unitForm.post(route('definitions.units.store'), { onSuccess: () => closeModal() });
const submitPackaging = () => packagingForm.post(route('definitions.packagings.store'), { onSuccess: () => closeModal() });
const submitCrewLeader = () => crewLeaderForm.post(route('definitions.crew-leaders.store'), { onSuccess: () => closeModal() });
const submitWorker = () => workerForm.post(route('definitions.workers.store'), { onSuccess: () => closeModal() });
const submitCateringSupplier = () => cateringSupplierForm.post(route('definitions.catering-suppliers.store'), { onSuccess: () => closeModal() });
const submitFertRecipe = () => fertRecipeForm.post(route('definitions.fertilization-recipes.store'), { onSuccess: () => closeModal() });
const submitSprayRecipe = () => sprayRecipeForm.post(route('definitions.spraying-recipes.store'), { onSuccess: () => closeModal() });
const submitWaterSource = () => waterSourceForm.post(route('definitions.water-sources.store'), { onSuccess: () => closeModal() });
const submitFilter = () => filterForm.post(route('definitions.filters.store'), { onSuccess: () => closeModal() });
const syncDiaCompanies = () => useForm({}).post(route('definitions.companies.sync-dia'));
const syncDiaTradingParties = () => useForm({}).post(route('definitions.trading-parties.sync-dia'));
const toggleActiveFertRecipe = (id: number) => useForm({}).post(route('definitions.fertilization-recipes.toggle-active', id));

const addFertTank = () => {
    const letters = ['A', 'B', 'C', 'D', 'E', 'F'];
    const tankLetter = letters[fertRecipeForm.tanks.length % letters.length] || `Tank-${fertRecipeForm.tanks.length + 1}`;
    fertRecipeForm.tanks.push({
        tank_name: `${tankLetter} Tankı (Solüsyon)`,
        capacity_liters: 1000,
        items: [
            { product_name: '', brand: '', quantity: 0, unit: 'kg', usage_purpose: '', description: '' }
        ]
    });
};

const removeFertTank = (tIdx: number) => {
    if (fertRecipeForm.tanks.length > 1) {
        fertRecipeForm.tanks.splice(tIdx, 1);
    }
};

const addFertTankItem = (tIdx: number) => {
    fertRecipeForm.tanks[tIdx].items.push({
        product_name: '',
        brand: '',
        quantity: 0,
        unit: 'kg',
        usage_purpose: '',
        description: ''
    });
};

const removeFertTankItem = (tIdx: number, iIdx: number) => {
    if (fertRecipeForm.tanks[tIdx].items.length > 1) {
        fertRecipeForm.tanks[tIdx].items.splice(iIdx, 1);
    }
};

const deleteItem = (routeName: string, id: number) => {
    if (confirm('Bu kaydı silmek istediğinize emin misiniz?')) {
        useForm({}).delete(route(routeName, id));
    }
};

const menuGroups = [
    {
        title: 'Firma & Tesis Yönetimi',
        items: [
            { key: 'companies', label: 'Firma Listesi' },
            { key: 'production_locations', label: 'Üretim Yeri & Vana' },
            { key: 'trading_parties', label: 'Alıcı & Satıcı Carileri' },
            { key: 'delivery_types', label: 'Ürün Teslimat Şekli' },
        ]
    },
    {
        title: 'Personel & Ekip Yönetimi',
        items: [
            { key: 'personnels', label: 'Personel & Görev Hiyerarşisi' },
            { key: 'crew_leaders', label: 'Çavuşlar (Ekip Başı)' },
            { key: 'workers', label: 'İşçiler' },
            { key: 'catering_suppliers', label: 'Yemek Tedarikçileri' },
        ]
    },
    {
        title: 'Ürün & Üretim Tanımları',
        items: [
            { key: 'products', label: 'Ürünler & Kalite Sınıfları' },
            { key: 'job_types', label: 'İş Tanımları & Formlar' },
            { key: 'units', label: 'Birim Tanımları' },
            { key: 'packagings', label: 'Paketleme Şekilleri' },
        ]
    },
    {
        title: 'Reçete & Altyapı Yönetimi',
        items: [
            { key: 'recipes', label: 'Gübre & İlaç Reçeteleri' },
            { key: 'water_and_filters', label: 'Su Kaynakları & Filtreler' },
        ]
    }
];

const currentTabLabel = computed(() => {
    for (const g of menuGroups) {
        const found = g.items.find((i: any) => i.key === currentTab.value);
        if (found) return found.label;
    }
    return 'Tanım';
});
</script>

<template>
    <Head title="Sistem Tanımlamaları" />

    <AuthenticatedLayout>
        <template #header>
            <div class="w-full flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 border border-rose-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">
                                Sistem Tanımlamaları
                            </h1>
                            <span class="text-slate-300 dark:text-slate-700">/</span>
                            <span class="text-xs font-bold text-rose-600 dark:text-rose-400">
                                {{ currentTabLabel }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-tight">Firma, personel, reçete, su kaynağı ve operasyonel parametreler</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-8">
            <div v-if="currentTab === 'companies'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Firma Yönetimi</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Grup ve işletme şirketleri yönetimi</p>
                    </div>
                    <button @click="openModal('company')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <span class="text-sm leading-none">+</span> Yeni Firma Ekle
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Kayıtlı Grup Firmaları</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ companies.length }} Firma</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Aktif İşletme Firması</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ companies.filter(c => c.is_active).length }} Aktif</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Bağlı Üretim Tesisleri</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ companies.reduce((acc, c) => acc + (c.production_locations?.length || 0), 0) }} Tesis</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam İşlenen Arazi</span>
                        <span class="font-bold font-mono text-slate-700 dark:text-slate-300 text-sm">{{ companies.reduce((acc, c) => acc + getCompanyTotalDekar(c), 0) }} da</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold text-slate-500 uppercase border-b">
                                <th class="p-3">Firma Kodu</th>
                                <th class="p-3">Firma Unvanı</th>
                                <th class="p-3">Vergi No</th>
                                <th class="p-3">Bağlı Üretim Tesisleri</th>
                                <th class="p-3">Durum</th>
                                <th class="p-3 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="c in companies" :key="c.id">
                                <td class="p-3">
                                    <span class="font-bold text-rose-600 bg-rose-50 dark:bg-rose-950/50 px-2 py-1 rounded border border-rose-200 dark:border-rose-900">{{ c.code }}</span>
                                </td>
                                <td class="p-3 font-semibold text-slate-800 dark:text-slate-100">{{ c.name }}</td>
                                <td class="p-3 text-slate-500 font-mono font-semibold">
                                    {{ c.tax_number || '-' }}
                                </td>
                                <td class="p-3">
                                    <div v-if="c.production_locations?.length" class="flex flex-wrap gap-1 items-center">
                                        <span v-for="loc in c.production_locations" :key="loc.id" class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold rounded border border-emerald-200 dark:border-emerald-900 text-[11px]">
                                            {{ loc.name }} ({{ loc.total_area_dekar }} da)
                                        </span>
                                        <span class="text-[11px] font-extrabold text-slate-400 ml-1">Toplam: {{ getCompanyTotalDekar(c) }} da</span>
                                    </div>
                                    <span v-else class="text-slate-400 italic">Kayıtlı tesis yok</span>
                                </td>
                                <td class="p-3">
                                    <span :class="c.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'" class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase">
                                        {{ c.is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td class="p-3 text-right space-x-2">
                                    <button @click="openModal('company', c)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                                    <button @click="deleteItem('definitions.companies.destroy', c.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentTab === 'personnels'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Personel & Görev Hiyerarşisi</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Yetkili personeller, ast-üst onay hiyerarşisi, firma ve modül erişim kısıtları</p>
                    </div>
                    <button @click="openModal('personnel')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <span class="text-sm leading-none">+</span> Yeni Personel Ekle
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Kayıtlı Personel Sayısı</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ personnels.length }} Personel</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Aktif Çalışanlar</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ personnels.filter(p => p.is_active).length }} Aktif</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Yönetici / Amir Kadrosu</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ personnels.filter(p => !p.parent_id).length }} Amir</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Geçmiş Veri İzinli</span>
                        <span class="font-bold font-mono text-slate-700 dark:text-slate-300 text-sm">{{ personnels.filter(p => p.can_enter_backdated_data).length }} Personel</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold text-slate-500 uppercase border-b">
                                <th class="p-3">Personel Ad Soyad</th>
                                <th class="p-3">Görev Unvanı & İletişim</th>
                                <th class="p-3">Üst Amiri</th>
                                <th class="p-3">Yetkili Firmalar</th>
                                <th class="p-3">Modül Yetkileri</th>
                                <th class="p-3">Geçmiş Veri</th>
                                <th class="p-3">Durum</th>
                                <th class="p-3 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="p in personnels" :key="p.id">
                                <td class="p-3">
                                    <div class="font-extrabold text-slate-800 dark:text-slate-100">{{ p.first_name }} {{ p.last_name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ p.user?.email || '-' }}</div>
                                </td>
                                <td class="p-3">
                                    <div class="font-semibold text-slate-700 dark:text-slate-200">{{ p.role_title || 'Unvan Belirtilmedi' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ p.phone || '-' }}</div>
                                </td>
                                <td class="p-3">
                                    <span v-if="p.parent" class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950 text-amber-800 dark:text-amber-300 font-bold rounded border border-amber-200 dark:border-amber-900 text-[11px]">
                                        {{ p.parent.first_name }} {{ p.parent.last_name }}
                                    </span>
                                    <span v-else class="text-slate-400 italic">Üst Amir Yok (Ana Yetkili)</span>
                                </td>
                                <td class="p-3">
                                    <div v-if="getPersonnelCompanies(p.company_ids)?.length" class="flex flex-wrap gap-1">
                                        <span v-for="comp in getPersonnelCompanies(p.company_ids)" :key="comp.id" class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold rounded border border-indigo-200 dark:border-indigo-900 text-[10px]">
                                            {{ comp.name }} ({{ comp.code }})
                                        </span>
                                    </div>
                                    <span v-else class="text-slate-400 italic">Tüm Firmalar / Tanımsız</span>
                                </td>
                                <td class="p-3">
                                    <div v-if="p.permissions?.length" class="flex flex-wrap gap-1 max-w-xs">
                                        <span v-for="perm in p.permissions" :key="perm" class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold rounded text-[10px] uppercase">
                                            {{ perm }}
                                        </span>
                                    </div>
                                    <span v-else class="text-slate-400 italic">Yetki Atanmamış</span>
                                </td>
                                <td class="p-3">
                                    <span :class="p.can_enter_backdated_data ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'" class="px-2 py-0.5 rounded text-[10px] font-bold">
                                        {{ p.can_enter_backdated_data ? 'İzinli' : 'Kısıtlı' }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <span :class="p.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'" class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase">
                                        {{ p.is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td class="p-3 text-right space-x-2 shrink-0">
                                    <button @click="openModal('personnel', p)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                                    <button @click="deleteItem('definitions.personnels.destroy', p.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentTab === 'production_locations'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Üretim Yeri & Vana Tanımları</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Bağlı firma, sera/açık bahçe alanları, dekar ve vana matrisi</p>
                    </div>
                    <button @click="openModal('production_location')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <span class="text-sm leading-none">+</span> Yeni Üretim Yeri Ekle
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Tesis Sayısı</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ productionLocations.length }} Tesis</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Dikili Alan</span>
                        <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400 text-sm">{{ productionLocations.reduce((acc, l) => acc + parseFloat(l.total_area_dekar || 0), 0) }} da</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Çilek & Muz Seraları</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ productionLocations.filter(l => l.location_type === 'greenhouse').length }} Sera</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Tanımlı Otomasyon Vanaları</span>
                        <span class="font-bold font-mono text-slate-700 dark:text-slate-300 text-sm">{{ productionLocations.reduce((acc, l) => acc + (l.valves?.length || 0), 0) }} Vana</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold text-slate-500 uppercase border-b">
                                <th class="p-3">Tesis & Sera Adı</th>
                                <th class="p-3">Bağlı Olduğu Firma</th>
                                <th class="p-3">Tesis Tipi</th>
                                <th class="p-3">Alan & Kapasite</th>
                                <th class="p-3">Şube & Depo Kodu</th>
                                <th class="p-3">Bölüm & Vana</th>
                                <th class="p-3 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="pl in productionLocations" :key="pl.id">
                                <td class="p-3 font-extrabold text-slate-800 dark:text-slate-100">{{ pl.name }}</td>
                                <td class="p-3">
                                    <div v-if="pl.company" class="flex items-center gap-1">
                                        <span class="font-bold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-950 px-2 py-0.5 rounded border border-indigo-200 dark:border-indigo-900 text-[11px]">
                                            {{ pl.company.name }}
                                        </span>
                                        <span class="text-[10px] text-slate-400">({{ pl.company.code }})</span>
                                    </div>
                                    <span v-else class="text-slate-400 italic">Firma Atanmamış</span>
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        {{ getLocationTypeLabel(pl.location_type) }}
                                    </span>
                                </td>
                                <td class="p-3 font-semibold">
                                    <div class="text-rose-600 dark:text-rose-400 font-bold">{{ pl.total_area_dekar }} da</div>
                                    <div class="text-[11px] text-slate-400">{{ pl.approx_plant_count ? pl.approx_plant_count.toLocaleString('tr-TR') + ' Fide' : 'Fide belirtilmedi' }}</div>
                                </td>
                                <td class="p-3 text-[11px] text-slate-500 font-mono">
                                    <div>Şube: {{ pl.dia_branch_code || '-' }}</div>
                                    <div>Depo: {{ pl.dia_warehouse_code || '-' }}</div>
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold rounded text-[11px]">
                                        {{ pl.sections?.length || 0 }} Bölüm / {{ pl.valves?.length || 0 }} Vana
                                    </span>
                                </td>
                                <td class="p-3 text-right space-x-2">
                                    <button @click="openModal('production_location', pl)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                                    <button @click="deleteItem('definitions.production-locations.destroy', pl.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentTab === 'trading_parties'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Alıcı & Satıcı Carileri</h2>
                        <p class="text-xs text-slate-400">Alıcı ve satıcı ticari partnerleri listesi</p>
                    </div>
                    <div class="space-x-2">
                        <button @click="openModal('trading_party')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                            + Yeni Cari Firma Ekle
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                                <th class="p-3">Cari Unvanı</th>
                                <th class="p-3">Cari Türü</th>
                                <th class="p-3">Vergi / TCKN No</th>
                                <th class="p-3">İletişim & Adres</th>
                                <th class="p-3">Nakliye Koşulu</th>
                                <th class="p-3 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="tp in tradingParties" :key="tp.id">
                                <td class="p-3 font-bold text-slate-800 dark:text-slate-100">{{ tp.name }}</td>
                                <td class="p-3">
                                    <span :class="tp.type === 'buyer' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800'" class="px-2 py-0.5 font-bold rounded border text-[11px]">
                                        {{ tp.type === 'buyer' ? 'Alıcı (Müşteri)' : (tp.type === 'supplier' ? 'Satıcı (Tedarikçi)' : 'Alıcı & Satıcı') }}
                                    </span>
                                </td>
                                <td class="p-3 font-mono font-semibold text-slate-700 dark:text-slate-300">
                                    {{ tp.tax_number || '-' }}
                                </td>
                                <td class="p-3">
                                    <div class="font-medium text-slate-700 dark:text-slate-300">{{ tp.phone || '-' }}</div>
                                    <div class="text-[11px] text-slate-400 truncate max-w-[200px]">{{ tp.address || tp.email || '-' }}</div>
                                </td>
                                <td class="p-3 font-semibold">
                                    <span v-if="tp.default_transport_fee" class="text-rose-600 dark:text-rose-400">₺{{ tp.default_transport_fee }} / Sabit</span>
                                    <span v-else class="text-slate-400">Standart / Ücretsiz</span>
                                </td>
                                <td class="p-3 text-right space-x-2">
                                    <button @click="openModal('trading_party', tp)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                                    <button @click="deleteItem('definitions.trading-parties.destroy', tp.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentTab === 'delivery_types'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Ürün Teslimat Şekli</h2>
                        <p class="text-xs text-slate-400">Depo teslimi, müşteri nakliyesi ve şirket aracıyla teslimat modelleri</p>
                    </div>
                    <button @click="openModal('delivery_type')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                        + Yeni Teslim Şekli Ekle
                    </button>
                </div>
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                                <th class="p-3">Teslimat Tanımı</th>
                                <th class="p-3">Teslimat Kodu</th>
                                <th class="p-3">Taşıyan Taraf</th>
                                <th class="p-3">Nakliye Ücret Şartı</th>
                                <th class="p-3 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="dt in deliveryTypes" :key="dt.id">
                                <td class="p-3 font-bold text-slate-800 dark:text-slate-100">{{ dt.name }}</td>
                                <td class="p-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ dt.code }}</td>
                                <td class="p-3">
                                    <span :class="dt.transport_by === 'customer' ? 'bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-blue-50 text-blue-800 dark:bg-blue-950 dark:text-blue-300'" class="px-2 py-0.5 font-bold rounded text-[11px]">
                                        {{ dt.transport_by === 'customer' ? 'Müşteri Kendi Aracıyla' : 'Bizim Şirket Aracımızla' }}
                                    </span>
                                </td>
                                <td class="p-3 font-semibold">
                                    <span v-if="dt.is_fee_included" class="text-emerald-600 dark:text-emerald-400 font-bold">Fiyata Dahil</span>
                                    <span v-else class="text-rose-600 dark:text-rose-400 font-bold">Ek Nakliye Ücretli (₺{{ dt.extra_fee }})</span>
                                </td>
                                <td class="p-3 text-right space-x-2">
                                    <button @click="openModal('delivery_type', dt)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentTab === 'crew_leaders'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Çavuşlar (Ekip Başları)</h2>
                        <p class="text-xs text-slate-400">Yevmiye, çavuş çarpanı, araç başı yol tazminatı ve çalışma şartları</p>
                    </div>
                    <button @click="openModal('crew_leader')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                        + Yeni Çavuş Ekle
                    </button>
                </div>
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b border-slate-100 dark:border-slate-800 text-slate-500 dark:text-slate-400">
                                <th class="p-3">Çavuş Ad Soyad</th>
                                <th class="p-3">Geldiği İl / İletişim</th>
                                <th class="p-3">Yevmiye & Çarpan</th>
                                <th class="p-3">Araç & Nakliye Şartı</th>
                                <th class="p-3">Yemek Durumu</th>
                                <th class="p-3">Bağlı İşçiler</th>
                                <th class="p-3 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            <tr v-for="cl in crewLeaders" :key="cl.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition">
                                <td class="p-3">
                                    <div class="font-extrabold text-slate-800 dark:text-slate-100">{{ cl.first_name }} {{ cl.last_name }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">TCKN: {{ cl.identity_number || '-' }}</div>
                                </td>
                                <td class="p-3">
                                    <div class="font-semibold text-slate-700 dark:text-slate-300">{{ cl.origin_city || 'Şehir Belirtilmedi' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ cl.phone || '-' }}</div>
                                </td>
                                <td class="p-3">
                                    <div class="font-extrabold text-emerald-600 dark:text-emerald-400">₺{{ cl.daily_wage }} / gün</div>
                                    <div class="text-[11px] text-slate-400 font-bold">Çarpan: {{ cl.multiplier }}x {{ cl.is_leader_fee_included ? '(Yevmiyeye Dahil)' : '(Ayrı)' }}</div>
                                </td>
                                <td class="p-3">
                                    <div class="font-bold text-slate-700 dark:text-slate-300">Min {{ cl.min_car_requirement }} Araç</div>
                                    <div class="text-[11px] text-rose-600 dark:text-rose-400 font-bold">₺{{ cl.travel_fee_per_car }} / araç</div>
                                </td>
                                <td class="p-3 font-semibold text-slate-700 dark:text-slate-300">
                                    <span :class="cl.is_food_included ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 border-emerald-200 dark:border-emerald-900' : 'text-slate-500 bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-700'" class="px-2 py-0.5 rounded-lg text-[10px] font-bold border">
                                        {{ cl.is_food_included ? 'Yemek Dahil' : 'Yemek Hariç' }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <button @click="openCrewLeaderWorkersModal(cl)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 dark:bg-slate-800/90 dark:hover:bg-indigo-950/60 dark:text-slate-300 dark:hover:text-indigo-300 border border-slate-200/80 dark:border-slate-700/80 hover:border-indigo-300 dark:hover:border-indigo-800 transition-all cursor-pointer group shadow-2xs">
                                        <span class="w-2 h-2 rounded-full shrink-0" :class="getCrewLeaderWorkers(cl.id).length > 0 ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                        <span class="font-black text-slate-900 dark:text-white">{{ getCrewLeaderWorkers(cl.id).length }}</span>
                                        <span class="text-slate-500 dark:text-slate-400 text-[11px]">İşçi</span>
                                        <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-indigo-500 dark:group-hover:text-indigo-400 transition-transform group-hover:translate-x-0.5 ml-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </td>
                                <td class="p-3 text-right space-x-2 shrink-0">
                                    <button @click="openCrewLeaderWorkersModal(cl)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">İşçiler</button>
                                    <button @click="openModal('crew_leader', cl)" class="text-slate-600 dark:text-slate-400 font-bold hover:underline">Düzenle</button>
                                    <button @click="deleteItem('definitions.crew-leaders.destroy', cl.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentTab === 'workers'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">İşçiler & Sicil Havuzu</h2>
                        <p class="text-xs text-slate-400">Çavuş ekiplerine bağlı ve bağımsız tarım işçileri, performans puanları ve sicil kayıtları</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="openModal('worker')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                            + Yeni İşçi Ekle
                        </button>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 dark:bg-slate-950/60 rounded-2xl border border-slate-200/70 dark:border-slate-800/80 mb-5 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="text-xs font-extrabold uppercase text-slate-600 dark:text-slate-300 tracking-wide flex items-center gap-2">
                            <span>Filtreleme & Kategorizasyon</span>
                            <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300 rounded-md text-[10px] font-bold">
                                {{ filteredWorkers.length }} / {{ workers?.length || 0 }} İşçi
                            </span>
                        </div>
                        <button v-if="workerSearchQuery || workerCrewLeaderFilter !== 'all' || workerPerformanceFilter !== 'all' || workerStatusFilter !== 'all'" @click="resetWorkerFilters" class="text-[11px] font-bold text-rose-600 hover:underline">
                            ✕ Filtreleri Temizle
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 text-xs">
                        <div>
                            <label class="block font-bold text-[11px] text-slate-500 mb-1">Arama (İsim / TC / Not)</label>
                            <input v-model="workerSearchQuery" type="text" placeholder="İşçi adı, soyadı, TCKN..." class="w-full border rounded-xl p-2 bg-white dark:bg-slate-900 text-xs focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="block font-bold text-[11px] text-slate-500 mb-1">Bağlı Olduğu Çavuş</label>
                            <select v-model="workerCrewLeaderFilter" class="w-full border rounded-xl p-2 bg-white dark:bg-slate-900 text-xs font-medium">
                                <option value="all">Tüm Çavuşlar (Hepsi)</option>
                                <option v-for="cl in crewLeaders" :key="cl.id" :value="String(cl.id)">
                                    {{ cl.first_name }} {{ cl.last_name }} ({{ getCrewLeaderWorkers(cl.id).length }} İşçi)
                                </option>
                                <option value="independent">Bağımsız İşçiler</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-[11px] text-slate-500 mb-1">Performans Puanı</label>
                            <select v-model="workerPerformanceFilter" class="w-full border rounded-xl p-2 bg-white dark:bg-slate-900 text-xs font-medium">
                                <option value="all">Tüm Puanlar</option>
                                <option value="5">5 Yıldız (Mükemmel)</option>
                                <option value="4plus">4 ve Üzeri Yıldız</option>
                                <option value="3minus">3 ve Altı (Geliştirilmeli)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-[11px] text-slate-500 mb-1">Çalışma Durumu</label>
                            <select v-model="workerStatusFilter" class="w-full border rounded-xl p-2 bg-white dark:bg-slate-900 text-xs font-medium">
                                <option value="all">Tüm Durumlar</option>
                                <option value="active">Yalnızca Aktifler</option>
                                <option value="passive">Yalnızca Pasifler</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                                <th class="p-3">İşçi Ad Soyad</th>
                                <th class="p-3">Bağlı Olduğu Çavuş</th>
                                <th class="p-3">TC Kimlik No</th>
                                <th class="p-3">Performans</th>
                                <th class="p-3">Notlar / Sicil</th>
                                <th class="p-3">Durum</th>
                                <th class="p-3 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="w in filteredWorkers" :key="w.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition">
                                <td class="p-3 font-extrabold text-slate-800 dark:text-slate-100">{{ w.first_name }} {{ w.last_name }}</td>
                                <td class="p-3 font-semibold">
                                    <button v-if="w.crew_leader" @click="openCrewLeaderWorkersModal(w.crew_leader)" class="px-2 py-0.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:hover:bg-indigo-900 dark:text-indigo-300 font-bold rounded-lg border border-indigo-200 dark:border-indigo-800 text-[11px] transition text-left cursor-pointer">
                                        {{ w.crew_leader.first_name }} {{ w.crew_leader.last_name }}
                                    </button>
                                    <span v-else class="text-slate-400 italic">Bağımsız İşçi</span>
                                </td>
                                <td class="p-3 font-mono text-slate-600 dark:text-slate-400">{{ w.identity_number || '-' }}</td>
                                <td class="p-3 font-bold text-amber-500">
                                    <span>Puan: {{ w.performance_rating }}/5</span>
                                </td>
                                <td class="p-3 text-slate-500 dark:text-slate-400 max-w-[220px] truncate">{{ w.notes || '-' }}</td>
                                <td class="p-3">
                                    <span :class="w.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'" class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase">
                                        {{ w.is_active ? 'Aktif' : 'Pasif' }}
                                    </span>
                                </td>
                                <td class="p-3 text-right space-x-2 shrink-0">
                                    <button @click="openModal('worker', w)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                                    <button @click="deleteItem('definitions.workers.destroy', w.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                                </td>
                            </tr>
                            <tr v-if="filteredWorkers.length === 0">
                                <td colspan="7" class="p-8 text-center text-slate-400 italic">
                                    Aranan kriterlere uygun işçi bulunamadı.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentTab === 'catering_suppliers'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Yemek Tedarikçileri</h2>
                        <p class="text-xs text-slate-400">Harici işçiler ve tesis personeli için yemek hizmeti tedarikçileri</p>
                    </div>
                    <button @click="openModal('catering_supplier')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                        + Yeni Yemek Tedarikçisi Ekle
                    </button>
                </div>
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                                <th class="p-3">Firma Unvanı</th>
                                <th class="p-3">Yetkili & İletişim</th>
                                <th class="p-3">Kişi Başı Öğün Ücreti</th>
                                <th class="p-3">Telefon</th>
                                <th class="p-3 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="cs in cateringSuppliers" :key="cs.id">
                                <td class="p-3 font-bold text-slate-800 dark:text-slate-100">{{ cs.company_title }}</td>
                                <td class="p-3">
                                    <div class="font-semibold text-slate-700 dark:text-slate-300">{{ cs.contact_person || '-' }}</div>
                                    <div class="text-[11px] text-slate-400">{{ cs.phone || '-' }}</div>
                                </td>
                                <td class="p-3">
                                    <div class="font-black text-emerald-600 dark:text-emerald-400">₺{{ cs.meal_unit_price }} / öğün</div>
                                    <div class="text-[11px] text-slate-400 font-bold">{{ cs.is_vat_included ? 'KDV Dahil' : 'KDV Hariç' }}</div>
                                </td>
                                <td class="p-3 font-mono font-semibold text-slate-700 dark:text-slate-300">{{ cs.phone || '-' }}</td>
                                <td class="p-3 text-right space-x-2">
                                    <button @click="openModal('catering_supplier', cs)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                                    <button @click="deleteItem('definitions.catering-suppliers.destroy', cs.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentTab === 'products'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Ürünler & Kalite Sınıfları</h2>
                        <p class="text-xs text-slate-400">Üretilen mahsuller, tüketilen girdiler ve kalite alt türleri</p>
                    </div>
                    <button @click="openModal('product')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                        + Yeni Ürün Ekle
                    </button>
                </div>
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                                <th class="p-3">Ürün Kodu</th>
                                <th class="p-3">Ürün Adı</th>
                                <th class="p-3">Ürün Tipi</th>
                                <th class="p-3">Alt Tipler & Kalite Sınıfları</th>
                                <th class="p-3">Bağlı Paketler</th>
                                <th class="p-3 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="p in products" :key="p.id">
                                <td class="p-3 font-mono font-bold text-rose-600 dark:text-rose-400">{{ p.code }}</td>
                                <td class="p-3 font-extrabold text-slate-800 dark:text-slate-100">{{ p.name }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold rounded text-[11px]">
                                        {{ p.product_type === 'harvest' ? 'Mahsul (Hasat)' : (p.product_type === 'seedling' ? 'Fide/Fidan' : 'Girdi / Kimyasal') }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div v-if="p.subtypes?.length" class="flex flex-wrap gap-1">
                                        <span v-for="st in p.subtypes" :key="st.id" class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold rounded border border-emerald-200 dark:border-emerald-900 text-[10px]">
                                            {{ st.name }}
                                        </span>
                                    </div>
                                    <span v-else class="text-slate-400">Tek Kalite</span>
                                </td>
                                <td class="p-3">
                                    <div v-if="p.packagings?.length" class="flex flex-wrap gap-1">
                                        <span v-for="pkg in p.packagings" :key="pkg.id" class="px-2 py-0.5 bg-purple-50 dark:bg-purple-950 text-purple-700 dark:text-purple-300 font-bold rounded text-[10px]">
                                            {{ pkg.name }}
                                        </span>
                                    </div>
                                    <span v-else class="text-slate-400 italic">Paket Tanımsız</span>
                                </td>
                                <td class="p-3 text-right space-x-2">
                                    <button @click="openModal('product', p)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                                    <button @click="deleteItem('definitions.products.destroy', p.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentTab === 'job_types'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">İş Tanımları & Form İlişkisi</h2>
                        <p class="text-xs text-slate-400">Hasat, fidan dikimi, budama, ilaçlama iş tanımları ve form tipleri</p>
                    </div>
                    <button @click="openModal('job_type')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                        + Yeni İş Tanımı Ekle
                    </button>
                </div>
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                                <th class="p-3">İş Kodu</th>
                                <th class="p-3">İş Tanımı Adı</th>
                                <th class="p-3">Form Tipi</th>
                                <th class="p-3">Kullanılan Birimler</th>
                                <th class="p-3 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="jt in jobTypes" :key="jt.id">
                                <td class="p-3 font-mono font-bold text-rose-600 dark:text-rose-400">{{ jt.code }}</td>
                                <td class="p-3 font-extrabold text-slate-800 dark:text-slate-100">{{ jt.name }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold rounded text-[11px]">
                                        {{ jt.form_type }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div v-if="jt.units?.length" class="flex flex-wrap gap-1">
                                        <span v-for="u in jt.units" :key="u.id" class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-semibold text-[10px]">
                                            {{ u.name }} ({{ u.symbol }})
                                        </span>
                                    </div>
                                    <span v-else class="text-slate-400">Yevmiye / Standart</span>
                                </td>
                                <td class="p-3 text-right space-x-2">
                                    <button @click="openModal('job_type', jt)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                                    <button @click="deleteItem('definitions.job-types.destroy', jt.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentTab === 'units'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Birim Tanımları</h2>
                        <p class="text-xs text-slate-400">Kg, Dal, Adet, Dekar, Tünel, Kasa ve Sehpa ölçü birimleri</p>
                    </div>
                    <button @click="openModal('unit')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                        + Yeni Birim Ekle
                    </button>
                </div>
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                                <th class="p-3">Birim Adı</th>
                                <th class="p-3">Sembol</th>
                                <th class="p-3">Kategori</th>
                                <th class="p-3 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="u in units" :key="u.id">
                                <td class="p-3 font-bold text-slate-800 dark:text-slate-100">{{ u.name }}</td>
                                <td class="p-3 font-mono font-bold text-rose-600 dark:text-rose-400">{{ u.symbol }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 font-semibold rounded text-[11px]">
                                        {{ u.unit_category }}
                                    </span>
                                </td>
                                <td class="p-3 text-right space-x-2">
                                    <button @click="openModal('unit', u)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                                    <button @click="deleteItem('definitions.units.destroy', u.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentTab === 'packagings'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Paketleme Şekilleri</h2>
                        <p class="text-xs text-slate-400">Çilek kasası, muz kolisi, plastik kaplar ve ambalaj standartları</p>
                    </div>
                    <button @click="openModal('packaging')" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                        + Yeni Paket Tipi Ekle
                    </button>
                </div>
                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold uppercase border-b">
                                <th class="p-3">Paket Adı</th>
                                <th class="p-3">Bağlı Olduğu Ürün</th>
                                <th class="p-3">Kapasite / Net Miktar</th>
                                <th class="p-3 text-right">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="pkg in packagings" :key="pkg.id">
                                <td class="p-3 font-bold text-slate-800 dark:text-slate-100">{{ pkg.name }}</td>
                                <td class="p-3">
                                    <span v-if="pkg.product" class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold rounded border border-emerald-200 dark:border-emerald-900 text-[11px]">
                                        {{ pkg.product.name }}
                                    </span>
                                    <span v-else class="text-slate-400">Tüm Ürünler</span>
                                </td>
                                <td class="p-3 font-extrabold text-indigo-600 dark:text-indigo-400">{{ pkg.capacity_qty }} {{ pkg.unit?.symbol || 'kg' }}/kap</td>
                                <td class="p-3 text-right space-x-2">
                                    <button @click="openModal('packaging', pkg)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                                    <button @click="deleteItem('definitions.packagings.destroy', pkg.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentTab === 'recipes'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Gübre & İlaç Reçeteleri</h2>
                        <p class="text-xs text-slate-400">Çoklu tank solüsyon reçeteleri, pH/EC hedefleri ve ilaçlama kombinasyonları</p>
                    </div>
                    <div class="space-x-2">
                        <button @click="openModal('fert_recipe')" class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                            + Gübre Reçetesi Ekle
                        </button>
                        <button @click="openModal('spray_recipe')" class="bg-purple-600 hover:bg-purple-500 text-white px-3 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                            + İlaç Reçetesi Ekle
                        </button>
                    </div>
                </div>
                <div class="space-y-4 text-xs">
                    <div v-for="fr in fertilizationRecipes" :key="fr.id" class="border rounded-xl p-4 bg-slate-50 dark:bg-slate-950 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 font-bold px-2 py-0.5 rounded text-[10px]">Gübre Reçetesi</span>
                                <span v-if="fr.is_active" class="bg-emerald-600 text-white font-extrabold px-2 py-0.5 rounded text-[10px]">AKTİF REÇETE</span>
                                <span v-else class="bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold px-2 py-0.5 rounded text-[10px]">PASİF</span>
                            </div>
                            <h3 class="font-extrabold text-sm text-slate-800 dark:text-slate-100 mt-1">{{ fr.name }}</h3>
                            <p class="text-slate-400 mt-0.5">Bitiş Şartı: {{ fr.duration_condition || 'Belirtilmedi' }} | Hedef pH: <span class="font-bold text-slate-700 dark:text-slate-200">{{ fr.water_ph }}</span> | Hedef EC: <span class="font-bold text-slate-700 dark:text-slate-200">{{ fr.water_ec }}</span></p>
                            <div v-if="fr.tanks?.length" class="mt-2 flex flex-wrap gap-1">
                                <span v-for="t in fr.tanks" :key="t.id" class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 rounded font-mono text-[10px]">
                                    {{ t.tank_name }} ({{ t.capacity_liters }}L): {{ t.items?.map((i: any) => i.product_name + ' ' + i.quantity + ' ' + i.unit).join(', ') }}
                                </span>
                            </div>
                        </div>
                        <div class="space-x-2 shrink-0">
                            <button v-if="!fr.is_active" @click="toggleActiveFertRecipe(fr.id)" class="bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded-lg font-bold">
                                Bu Reçeteyi Aktif Yap
                            </button>
                            <button @click="openModal('fert_recipe', fr)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                            <button @click="deleteItem('definitions.fertilization-recipes.destroy', fr.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                        </div>
                    </div>
                    <div v-for="sr in sprayingRecipes" :key="'sr-' + sr.id" class="border rounded-xl p-4 bg-slate-50 dark:bg-slate-950 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="bg-purple-100 dark:bg-purple-950 text-purple-800 dark:text-purple-300 font-bold px-2 py-0.5 rounded text-[10px]">İlaç Reçetesi</span>
                                <span :class="sr.is_active ? 'bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400'" class="font-bold px-2 py-0.5 rounded text-[10px]">{{ sr.is_active ? 'Aktif' : 'Pasif' }}</span>
                            </div>
                            <h3 class="font-extrabold text-sm text-slate-800 dark:text-slate-100 mt-1">{{ sr.name }}</h3>
                            <p class="text-slate-400 mt-0.5">Uygulama Amacı: {{ sr.usage_purpose }} | Su Hacmi: {{ sr.water_volume_liters }}L | Yöntem: {{ sr.application_method }}</p>
                            <div v-if="sr.items?.length" class="mt-2 flex flex-wrap gap-1">
                                <span v-for="it in sr.items" :key="it.id" class="px-2 py-0.5 bg-purple-50 dark:bg-purple-950 text-purple-800 dark:text-purple-300 rounded font-mono text-[10px]">
                                    {{ it.product_name }} ({{ it.quantity }} {{ it.unit }})
                                </span>
                            </div>
                        </div>
                        <div class="space-x-2 shrink-0">
                            <button @click="openModal('spray_recipe', sr)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                            <button @click="deleteItem('definitions.spraying-recipes.destroy', sr.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="currentTab === 'water_and_filters'" class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 shadow-sm p-6">
                <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-slate-100">Su Kaynakları & Filtreler</h2>
                        <p class="text-xs text-slate-400">Kuyu pompaları, aktif/pasif döngüler, temizlik periyotları ve bağlı tesis/firma ilişkisi</p>
                    </div>
                    <div class="space-x-2">
                        <button @click="openModal('water_source')" class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                            + Su Kaynağı Ekle
                        </button>
                        <button @click="openModal('filter')" class="bg-amber-600 hover:bg-amber-500 text-white px-3 py-2 rounded-xl text-xs font-bold shadow-sm transition">
                            + Filtre Ekle
                        </button>
                    </div>
                </div>
                <div class="space-y-6 text-xs">
                    <div>
                        <h3 class="text-xs font-extrabold uppercase text-slate-500 dark:text-slate-400 mb-2 tracking-wide">Su Kaynakları (Kuyu & Havuz)</h3>
                        <div class="space-y-2">
                            <div v-for="ws in waterSources" :key="ws.id" class="border rounded-xl p-4 bg-slate-50 dark:bg-slate-950 flex justify-between items-start">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-extrabold text-sm text-slate-800 dark:text-slate-100">{{ ws.name }}</h4>
                                        <span v-if="ws.production_location?.company" class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold rounded text-[10px]">
                                            {{ ws.production_location.company.name }}
                                        </span>
                                    </div>
                                    <p class="text-slate-400 mt-1">Bağlı Tesis: <span class="font-bold text-slate-700 dark:text-slate-200">{{ ws.production_location?.name || 'Tesis Belirtilmedi' }}</span> | Aktif: {{ ws.active_cycle_minutes }} dk | Pasif: {{ ws.passive_cycle_minutes }} dk | Fotoğraf Doğrulama: <span :class="ws.requires_photo_verification ? 'text-rose-600 font-bold' : 'text-slate-400'">{{ ws.requires_photo_verification ? 'Zorunlu' : 'İsteğe Bağlı' }}</span></p>
                                </div>
                                <div class="space-x-2 shrink-0">
                                    <button @click="openModal('water_source', ws)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                                    <button @click="deleteItem('definitions.water-sources.destroy', ws.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                                </div>
                            </div>
                            <p v-if="!waterSources.length" class="text-slate-400 italic">Henüz su kaynağı tanımlanmamış.</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xs font-extrabold uppercase text-slate-500 dark:text-slate-400 mb-2 tracking-wide">Filtreler & İstasyonlar</h3>
                        <div class="space-y-2">
                            <div v-for="f in filters" :key="f.id" class="border rounded-xl p-4 bg-slate-50 dark:bg-slate-950 flex justify-between items-start">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-extrabold text-sm text-slate-800 dark:text-slate-100">{{ f.name }}</h4>
                                        <span v-if="f.production_location?.company" class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold rounded text-[10px]">
                                            {{ f.production_location.company.name }}
                                        </span>
                                    </div>
                                    <p class="text-slate-400 mt-1">Bağlı Tesis: <span class="font-bold text-slate-700 dark:text-slate-200">{{ f.production_location?.name || 'Tesis Belirtilmedi' }}</span> | Temizlik Döngüsü: <span class="font-bold text-emerald-600">{{ f.cleaning_cycle_days }} günde bir</span> | Fotoğraf: <span :class="f.requires_photo_verification ? 'text-rose-600 font-bold' : 'text-slate-400'">{{ f.requires_photo_verification ? 'Zorunlu' : 'İsteğe Bağlı' }}</span></p>
                                </div>
                                <div class="space-x-2 shrink-0">
                                    <button @click="openModal('filter', f)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                                    <button @click="deleteItem('definitions.filters.destroy', f.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                                </div>
                            </div>
                            <p v-if="!filters.length" class="text-slate-400 italic">Henüz filtre tanımlanmamış.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Transition name="modal">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        {{ editItemData ? ((modalTitles[modalType] || 'Kayıt') + ' Düzenle') : ('Yeni ' + (modalTitles[modalType] || 'Kayıt') + ' Ekle') }}
                    </h3>
                    <button @click="closeModal" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-bold text-xl">&times;</button>
                </div>

                <form v-if="modalType === 'company'" @submit.prevent="submitCompany" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">Firma Unvanı</label>
                        <input v-model="companyForm.name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Firma Kodu</label>
                        <input v-model="companyForm.code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'personnel'" @submit.prevent="submitPersonnel" class="space-y-4 text-xs">
                    <div v-if="personnelForm.errors && Object.keys(personnelForm.errors).length > 0" class="p-3 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-600 rounded-xl space-y-1 font-bold">
                        <div v-for="(err, field) in personnelForm.errors" :key="field">
                            {{ err }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Ad</label>
                            <input v-model="personnelForm.first_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Soyad</label>
                            <input v-model="personnelForm.last_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Sisteme Giriş E-postası</label>
                            <input v-model="personnelForm.email" type="email" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="ornek@sasa.com" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Sisteme Giriş Şifresi</label>
                            <input v-model="personnelForm.password" type="password" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Değiştirmemek için boş bırakın" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Telefon Numarası (SMS/Bildirim)</label>
                            <input v-model="personnelForm.phone" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="0532..." />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Görev Unvanı</label>
                            <input v-model="personnelForm.role_title" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="İşletme Müdürü / Ziraat Müh." />
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Üst Amiri (Onaylayıcı Ast-Üst Hiyerarşisi)</label>
                        <select v-model="personnelForm.parent_personnel_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                            <option :value="null">Üst Amir Yok (Ana Yetkili)</option>
                            <option v-for="p in personnels" :key="p.id" :value="p.id">{{ p.first_name }} {{ p.last_name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Yetkili Olduğu Firmalar (Firma Yetkisi)</label>
                        <div class="space-y-1 bg-slate-50 dark:bg-slate-950 p-2.5 rounded-xl border max-h-32 overflow-y-auto">
                            <label v-for="c in companies" :key="c.id" class="flex items-center space-x-2">
                                <input type="checkbox" :value="c.id" v-model="personnelForm.company_ids" class="rounded text-rose-600" />
                                <span class="font-bold">{{ c.name }} ({{ c.code }})</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Modül Erişim Yetkileri (Yetki Tanımı)</label>
                        <div class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-950 p-2.5 rounded-xl border">
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" value="tesis" v-model="personnelForm.permissions" class="rounded text-rose-600" />
                                <span class="font-bold">Tesis Yönetimi</span>
                            </label>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" value="uretim" v-model="personnelForm.permissions" class="rounded text-rose-600" />
                                <span class="font-bold">Üretim Modülü</span>
                            </label>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" value="teknik" v-model="personnelForm.permissions" class="rounded text-rose-600" />
                                <span class="font-bold">Teknik Modül</span>
                            </label>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" value="operasyon" v-model="personnelForm.permissions" class="rounded text-rose-600" />
                                <span class="font-bold">Operasyon Modülü</span>
                            </label>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" value="raporlar" v-model="personnelForm.permissions" class="rounded text-rose-600" />
                                <span class="font-bold">Raporlar Paneli</span>
                            </label>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" value="tanimlamalar" v-model="personnelForm.permissions" class="rounded text-rose-600" />
                                <span class="font-bold">Sistem Tanımlamaları</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-2.5 bg-slate-50 dark:bg-slate-950 rounded-xl border">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input v-model="personnelForm.can_enter_backdated_data" type="checkbox" class="rounded text-rose-600" />
                            <span class="font-bold">Geçmişe dönük veri girebilir mi?</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input v-model="personnelForm.is_active" type="checkbox" class="rounded text-emerald-600" />
                            <span class="font-bold text-emerald-600">Durum: {{ personnelForm.is_active ? 'Aktif' : 'Pasif (Erişim Kapalı)' }}</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Personel Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'trading_party'" @submit.prevent="submitTradingParty" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">Cari Firma Unvanı</label>
                        <input v-model="tradingPartyForm.name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Cari Tipi</label>
                            <select v-model="tradingPartyForm.type" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option value="buyer">Alıcı Müşteri</option>
                                <option value="seller">Satıcı Tedarikçi</option>
                                <option value="both">Hem Alıcı Hem Satıcı</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Varsayılan Nakliye Ücreti (₺)</label>
                            <input v-model="tradingPartyForm.default_transport_fee" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="0.00" />
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Cari Kodu / Hesap No</label>
                        <input v-model="tradingPartyForm.dia_cari_code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="CAR-001" />
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Cari Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'delivery_type'" @submit.prevent="submitDeliveryType" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">Teslim Şekli Adı</label>
                        <input v-model="deliveryTypeForm.name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Kod</label>
                        <input v-model="deliveryTypeForm.code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Taşıyan Taraf</label>
                        <select v-model="deliveryTypeForm.transport_by" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                            <option value="customer">Müşteri Kendi Aracıyla Alacak (Tesiste Teslim)</option>
                            <option value="company">Bizim Aracımızla Sevk Edilecek</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Nakliye Ücret Şartı</label>
                            <select v-model="deliveryTypeForm.is_fee_included" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option :value="true">Fiyata Dahil / Ücretsiz</option>
                                <option :value="false">Ek Ücretli Nakliye</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Ek Nakliye Ücreti (₺)</label>
                            <input v-model="deliveryTypeForm.extra_fee" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="0.00" />
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Teslim Şekli Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'production_location'" @submit.prevent="submitProductionLocation" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">Firma Seçimi</label>
                        <select v-model="productionLocationForm.company_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Üretim Yeri Adı</label>
                        <input v-model="productionLocationForm.name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Tesis Tipi</label>
                            <select v-model="productionLocationForm.location_type" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option value="greenhouse">Sera</option>
                                <option value="open_field">Açık Bahçe</option>
                                <option value="mixed">Karma (Sera & Bahçe)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Toplam Dekar (da)</label>
                            <input v-model="productionLocationForm.total_area_dekar" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Yaklaşık Bitki Sayısı</label>
                            <input v-model="productionLocationForm.approx_plant_count" type="number" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Şube Kodu</label>
                            <input v-model="productionLocationForm.dia_branch_code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="SUBE-01" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Depo Kodu</label>
                            <input v-model="productionLocationForm.dia_warehouse_code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="DEPO-01" />
                        </div>
                    </div>
                    <div class="border-t pt-2 mt-2">
                        <label class="block font-bold mb-1">Sera Tünel ve Bölüm Detayları</label>
                        <div v-for="(sec, idx) in productionLocationForm.sections" :key="idx" class="grid grid-cols-4 gap-1 mb-2 bg-slate-50 dark:bg-slate-950 p-2 rounded-lg border">
                            <input v-model="sec.name" type="text" placeholder="Tünel/Bölüm Adı" class="border rounded p-1 dark:bg-slate-900" />
                            <input v-model="sec.area_dekar" type="number" step="0.1" placeholder="Dekar" class="border rounded p-1 dark:bg-slate-900" />
                            <input v-model="sec.tunnel_count" type="number" placeholder="Tünel Sayısı" class="border rounded p-1 dark:bg-slate-900" />
                            <input v-model="sec.table_stand_count" type="number" placeholder="Sehpa Sayısı" class="border rounded p-1 dark:bg-slate-900" />
                        </div>
                    </div>
                    <div class="border-t pt-2 mt-2">
                        <label class="block font-bold mb-1">Vana Görev Tanımları</label>
                        <div v-for="(v, idx) in productionLocationForm.valves" :key="idx" class="grid grid-cols-3 gap-1 mb-2 bg-slate-50 dark:bg-slate-950 p-2 rounded-lg border">
                            <input v-model="v.valve_number" type="text" placeholder="Vana No (V-01)" class="border rounded p-1 dark:bg-slate-900" />
                            <input v-model="v.name" type="text" placeholder="Vana Adı" class="border rounded p-1 dark:bg-slate-900" />
                            <select v-model="v.duty" class="border rounded p-1 dark:bg-slate-900">
                                <option value="irrigation">Sulama</option>
                                <option value="misting">Sisleme</option>
                                <option value="fertilization">Gübreleme</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Üretim Yerini Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'product'" @submit.prevent="submitProduct" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">Ürün Adı</label>
                        <input v-model="productForm.name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Ürün Kodu</label>
                            <input v-model="productForm.code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Stok Kodu</label>
                            <input v-model="productForm.dia_stock_code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="STK-CLK-001" />
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Ürün Tipi</label>
                        <select v-model="productForm.product_type" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                            <option value="produced">Üretilen Mahsul (Çilek, Muz, Avokado vb.)</option>
                            <option value="consumed">Tüketilen Girdi (Fide, Gübre, İlaç vb.)</option>
                            <option value="both">Her İkisi de</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Üreten / Tüketen Firmalar (Çoklu Seçim)</label>
                        <div class="space-y-1 bg-slate-50 dark:bg-slate-950 p-2.5 rounded-xl border max-h-32 overflow-y-auto">
                            <label v-for="c in companies" :key="c.id" class="flex items-center space-x-2">
                                <input type="checkbox" :value="c.id" v-model="productForm.company_ids" class="rounded text-rose-600" />
                                <span class="font-bold">{{ c.name }} ({{ c.code }})</span>
                            </label>
                        </div>
                    </div>
                    <div class="border-t pt-2">
                        <label class="block font-bold mb-1">Ürün Alt Tipleri & Kalite Sınıfları</label>
                        <div v-for="(st, idx) in productForm.subtypes" :key="idx" class="grid grid-cols-2 gap-1 mb-2 bg-slate-50 dark:bg-slate-950 p-2 rounded-lg border">
                            <input v-model="st.name" type="text" placeholder="Alt Tip Adı (Örn: 1.Kalite)" class="border rounded p-1 dark:bg-slate-900" />
                            <input v-model="st.code" type="text" placeholder="Kod (Örn: CLK-1)" class="border rounded p-1 dark:bg-slate-900" />
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Ürün Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'job_type'" @submit.prevent="submitJobType" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">İş Tanımı Adı (Örn: Hasat, Budama, Fidan Dikimi)</label>
                        <input v-model="jobTypeForm.name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">İş Kodu</label>
                            <input v-model="jobTypeForm.code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">İlişkili Form Tipi</label>
                            <select v-model="jobTypeForm.form_type" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option value="harvest">Hasat / Ürün Toplama Formu</option>
                                <option value="planting">Fidan Dikimi Formu</option>
                                <option value="pruning">Budama Formu</option>
                                <option value="spraying">İlaçlama Formu</option>
                                <option value="daily_worker">Günlük İşçi Formu</option>
                                <option value="general">Genel Tarım İşi Formu</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Geçerli Ölçü Birimleri</label>
                        <div class="grid grid-cols-3 gap-2 bg-slate-50 dark:bg-slate-950 p-2.5 rounded-xl border">
                            <label v-for="u in units" :key="u.id" class="flex items-center space-x-2">
                                <input type="checkbox" :value="u.id" v-model="jobTypeForm.unit_ids" class="rounded text-rose-600" />
                                <span class="font-bold">{{ u.name }} ({{ u.symbol }})</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">İş Tanımı Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'unit'" @submit.prevent="submitUnit" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">Birim Adı (Örn: Kilogram, Dekar, Tünel, Dal, Sehpa)</label>
                        <input v-model="unitForm.name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Sembol (Örn: kg, da, tnl, dal, shp)</label>
                            <input v-model="unitForm.symbol" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Birim Kategorisi</label>
                            <select v-model="unitForm.unit_category" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option value="quantity">Miktar Bazlı (Kilo, Gram, Litre)</option>
                                <option value="area">Alan & Kısım Bazlı (Dekar, Tünel, Sehpa)</option>
                                <option value="count">Adet & Sayı Bazlı (Dal, Hevenk, Fide Adedi)</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Birim Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'packaging'" @submit.prevent="submitPackaging" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">İlişkili Ürün (Örn: Çilek, Muz)</label>
                        <select v-model="packagingForm.product_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                            <option :value="null">Tüm Ürünler İçin Genel Ambalaj</option>
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} ({{ p.code }})</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Paketleme Tipi Adı (Örn: 5 Kg Çilek Kasası, 500gr Kap)</label>
                        <input v-model="packagingForm.name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Paket Kodu</label>
                            <input v-model="packagingForm.code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="PKG-CLK-05" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Ambalaj Stok Kodu</label>
                            <input v-model="packagingForm.dia_stock_code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="STK-KASA-001" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Kapasite (Miktar/Kap)</label>
                            <input v-model="packagingForm.capacity_qty" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Birim</label>
                            <select v-model="packagingForm.unit_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }} ({{ u.symbol }})</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Paket Tipi Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'crew_leader'" @submit.prevent="submitCrewLeader" class="space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Çavuş Adı</label>
                            <input v-model="crewLeaderForm.first_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Soyadı</label>
                            <input v-model="crewLeaderForm.last_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">TC Kimlik No</label>
                            <input v-model="crewLeaderForm.identity_number" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Geldiği Yer / Şehir</label>
                            <input v-model="crewLeaderForm.origin_city" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Örn: Antalya, Urfa" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">İşçi Başı Günlük Yevmiye (₺)</label>
                            <input v-model="crewLeaderForm.daily_wage" type="number" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Çavuş Çarpanı (1.0x / 1.5x)</label>
                            <input v-model="crewLeaderForm.multiplier" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Min Araba Şartı (Kaç Araba)</label>
                            <input v-model="crewLeaderForm.min_car_requirement" type="number" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Örn: 2" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Araba Başı Yol Ücreti (₺)</label>
                            <input v-model="crewLeaderForm.travel_fee_per_car" type="number" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Yemek Şartı</label>
                            <select v-model="crewLeaderForm.is_food_included" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option :value="false">Yemek Hariç (Tedarikçiden Alınacak)</option>
                                <option :value="true">Yemek Dahil Ücret</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Cari Kodu / Hesap No</label>
                            <input v-model="crewLeaderForm.dia_cari_code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="CAR-CAVUS-01" />
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Çavuş Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'worker'" @submit.prevent="submitWorker" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">Bağlı Olduğu Çavuş (Ekip Başı)</label>
                        <select v-model="workerForm.crew_leader_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                            <option :value="null">Bağımsız İşçi</option>
                            <option v-for="cl in crewLeaders" :key="cl.id" :value="cl.id">{{ cl.first_name }} {{ cl.last_name }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">İşçi Adı</label>
                            <input v-model="workerForm.first_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Soyadı</label>
                            <input v-model="workerForm.last_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">TC Kimlik Numarası</label>
                            <input v-model="workerForm.identity_number" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Performans Notu (1-5 Puan)</label>
                            <select v-model="workerForm.performance_rating" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option :value="5">5 / 5 (Çok İyi)</option>
                                <option :value="4">4 / 5 (İyi)</option>
                                <option :value="3">3 / 5 (Orta)</option>
                                <option :value="2">2 / 5 (Zayıf)</option>
                                <option :value="1">1 / 5 (Değiştirilsin / Gelmesin)</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">İşçi Notları & Değişim Talebi</label>
                        <textarea v-model="workerForm.notes" rows="2" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Çavuşa iletilecek değişim veya performans notu..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">İşçi Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'catering_supplier'" @submit.prevent="submitCateringSupplier" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">Firma / Tedarikçi Unvanı</label>
                        <input v-model="cateringSupplierForm.company_title" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Yetkili İletişim Kişisi</label>
                            <input v-model="cateringSupplierForm.contact_person" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Örn: Ahmet Bey" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Telefon Numarası</label>
                            <input v-model="cateringSupplierForm.phone" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="0532..." />
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Kişi Başı Yemek Ücreti (₺)</label>
                            <input v-model="cateringSupplierForm.meal_unit_price" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">KDV Durumu</label>
                            <select v-model="cateringSupplierForm.is_vat_included" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option :value="true">KDV Dahil</option>
                                <option :value="false">KDV Hariç</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Cari Kodu / Hesap No</label>
                            <input v-model="cateringSupplierForm.dia_cari_code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="CAR-YEMEK-01" />
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Tedarikçi Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'fert_recipe'" @submit.prevent="submitFertRecipe" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">Gübre Reçetesi Adı *</label>
                        <input v-model="fertRecipeForm.name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold" placeholder="Örn: Topraksız Çilek Büyütme ve Meyve Tutum Reçetesi" required />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold mb-1">Reçeteyi Hazırlayan / Oluşturan Kişi *</label>
                            <input v-model="fertRecipeForm.creator_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Ziraat Müh. Ahmet Yılmaz" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Oluşturma Tarihi *</label>
                            <input v-model="fertRecipeForm.creation_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold mb-1 text-slate-800 dark:text-slate-200">Reçete Kullanım Süresi / Bitiş Şartı *</label>
                        <input v-model="fertRecipeForm.duration_condition" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-semibold text-rose-600 dark:text-rose-400" placeholder="Spesifik bir tarih (Örn: 30.11.2026) veya sözel şart (Örn: Çiçeklenme görülene kadar)" required />
                        <p class="text-[10px] text-slate-400 mt-1">Not: Belirli bir takvim tarihi girilebileceği gibi bitki gelişim evresi (çiçeklenme, ilk meyve tutumu vb.) sözel şartı da yazılabilir.</p>
                    </div>

                    <!-- Mevcut Su Değerleri & Analiz Bilgileri -->
                    <div class="p-3.5 bg-blue-50/70 dark:bg-blue-950/40 rounded-xl border border-blue-200 dark:border-blue-900/60 space-y-2">
                        <div class="font-extrabold text-blue-900 dark:text-blue-300 text-xs">Mevcut Kaynak Su Değerleri</div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-[11px] text-slate-700 dark:text-slate-300 mb-1">Hedef / Mevcut pH Değeri</label>
                                <input v-model="fertRecipeForm.water_ph" type="number" step="0.01" min="0" max="14" class="w-full border rounded-xl p-2 bg-white dark:bg-slate-950 font-bold" placeholder="6.50" />
                            </div>
                            <div>
                                <label class="block font-bold text-[11px] text-slate-700 dark:text-slate-300 mb-1">Hedef / Mevcut EC Değeri (mS/cm)</label>
                                <input v-model="fertRecipeForm.water_ec" type="number" step="0.01" min="0" class="w-full border rounded-xl p-2 bg-white dark:bg-slate-950 font-bold" placeholder="1.80" />
                            </div>
                        </div>
                        <div>
                            <input v-model="fertRecipeForm.water_notes" type="text" class="w-full border rounded-xl p-2 bg-white dark:bg-slate-950 text-xs" placeholder="Su analizi notları ve mineral dengesi açıklaması..." />
                        </div>
                    </div>

                    <!-- Reçeteye Bağlı Tanklar ve Solüsyon Karışım Kalemleri -->
                    <div class="border-t border-slate-200 dark:border-slate-800 pt-3 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block font-black text-xs text-slate-800 dark:text-slate-100 uppercase tracking-wider">Reçete Tankları & Solüsyon Gübre İçerikleri</label>
                                <p class="text-[11px] text-slate-400">Her tank hacmini ve içerisine konulacak gübre karışımlarını giriniz</p>
                            </div>
                            <button type="button" @click="addFertTank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs shadow-xs transition">
                                + Yeni Tank Ekle
                            </button>
                        </div>

                        <div v-for="(tank, tIdx) in fertRecipeForm.tanks" :key="tIdx" class="bg-slate-50 dark:bg-slate-950 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-3">
                            <div class="flex items-center justify-between gap-3 pb-2 border-b border-slate-200 dark:border-slate-800">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 flex-1">
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Tank Adı *</label>
                                        <input v-model="tank.tank_name" type="text" placeholder="Örn: A Tankı (Solüsyon)" class="w-full border rounded-xl p-2 font-extrabold text-xs dark:bg-slate-900" required />
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">Tank Hacmi (Litre) *</label>
                                        <input v-model.number="tank.capacity_liters" type="number" placeholder="Örn: 1000" class="w-full border rounded-xl p-2 font-bold text-xs dark:bg-slate-900" required />
                                    </div>
                                </div>
                                <button v-if="fertRecipeForm.tanks.length > 1" type="button" @click="removeFertTank(tIdx)" class="text-rose-600 hover:text-rose-500 font-bold text-xs shrink-0 self-end mb-1">
                                    Tankı Sil
                                </button>
                            </div>

                            <!-- Solüsyon Ürün Karışımları -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider">Solüsyon Gübre Karışımı (Ürünler & Gramajlar)</span>
                                    <button type="button" @click="addFertTankItem(tIdx)" class="text-indigo-600 dark:text-indigo-400 font-bold text-[11px] hover:underline">
                                        + Ürün / Gübre Ekle
                                    </button>
                                </div>

                                <div v-for="(item, iIdx) in tank.items" :key="iIdx" class="grid grid-cols-1 sm:grid-cols-12 gap-1.5 bg-white dark:bg-slate-900 p-2 rounded-xl border border-slate-200 dark:border-slate-800 items-center">
                                    <div class="sm:col-span-3">
                                        <input v-model="item.product_name" type="text" placeholder="Gübre Ürünü *" class="w-full border rounded-lg p-1.5 text-xs font-semibold dark:bg-slate-950" required />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <input v-model="item.brand" type="text" placeholder="Marka" class="w-full border rounded-lg p-1.5 text-xs dark:bg-slate-950" />
                                    </div>
                                    <div class="sm:col-span-2 flex items-center gap-1">
                                        <input v-model.number="item.quantity" type="number" step="0.01" placeholder="Miktar" class="w-full border rounded-lg p-1.5 text-xs font-bold dark:bg-slate-950" required />
                                        <select v-model="item.unit" class="border rounded-lg p-1.5 text-xs font-bold dark:bg-slate-950">
                                            <option value="kg">kg</option>
                                            <option value="gr">gr</option>
                                            <option value="lt">lt</option>
                                            <option value="cc">cc</option>
                                        </select>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <input v-model="item.usage_purpose" type="text" placeholder="Kullanım Amacı" class="w-full border rounded-lg p-1.5 text-xs dark:bg-slate-950" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <input v-model="item.description" type="text" placeholder="Açıklama / Not" class="w-full border rounded-lg p-1.5 text-xs dark:bg-slate-950" />
                                    </div>
                                    <div class="sm:col-span-1 text-right">
                                        <button v-if="tank.items.length > 1" type="button" @click="removeFertTankItem(tIdx, iIdx)" class="text-rose-500 hover:text-rose-700 font-bold text-xs">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                        <button type="submit" :disabled="fertRecipeForm.processing" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-xs transition">
                            Gübre Reçetesini Kaydet
                        </button>
                    </div>
                </form>

                <form v-if="modalType === 'spray_recipe'" @submit.prevent="submitSprayRecipe" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">İlaç Reçete Adı</label>
                        <input v-model="sprayRecipeForm.name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Oluşturma Tarihi</label>
                            <input v-model="sprayRecipeForm.creation_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Kullanım Zamanı</label>
                            <input v-model="sprayRecipeForm.usage_time" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Örn: Akşamüstü rüzgarsız saatler" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Kullanım Amacı</label>
                            <input v-model="sprayRecipeForm.usage_purpose" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Örn: Kırmızı Örümcek Mücadelesi" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Karışım Su Hacmi (Litre)</label>
                            <input v-model="sprayRecipeForm.water_volume_liters" type="number" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Örn: 400" required />
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Uygulama Şekli</label>
                        <select v-model="sprayRecipeForm.application_method" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                            <option value="sprayer_machine">İlaçlama Makinesi (Traktör Arkası Holder)</option>
                            <option value="fertigation_tank">Gübreleme Tankından Uygulama</option>
                            <option value="backpack_pump">Sırt Pompası İle Uygulama</option>
                            <option value="other">Diğer Uygulama Şekli</option>
                        </select>
                    </div>
                    <div class="border-t pt-2">
                        <label class="block font-bold mb-1">Reçete İlaç Karışım Listesi (Kaç Litreye Ne Kadar İlaç)</label>
                        <div v-for="(item, idx) in sprayRecipeForm.items" :key="idx" class="grid grid-cols-4 gap-1.5 bg-slate-50 dark:bg-slate-950 p-2 rounded-xl border mb-2">
                            <input v-model="item.product_name" type="text" placeholder="İlaç Ürünü / Etken Madde" class="border rounded p-1.5 dark:bg-slate-900" />
                            <input v-model="item.brand" type="text" placeholder="Marka (Örn: Bayer)" class="border rounded p-1.5 dark:bg-slate-900" />
                            <input v-model="item.quantity" type="number" step="0.1" placeholder="Miktar (Örn: 250 ml)" class="border rounded p-1.5 dark:bg-slate-900" />
                            <input v-model="item.notes" type="text" placeholder="Güvenlik Notu / Açıklama" class="border rounded p-1.5 dark:bg-slate-900" />
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white font-bold rounded-xl">İlaç Reçetesi Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'water_source'" @submit.prevent="submitWaterSource" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">İlişkili Üretim Yeri (Tesis / Sera / Bahçe)</label>
                        <select v-model="waterSourceForm.production_location_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                            <option v-for="pl in productionLocations" :key="pl.id" :value="pl.id">{{ pl.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Su Kaynağı / Kuyu Pompası Adı</label>
                        <input v-model="waterSourceForm.name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Örn: 1 Nolu Derin Kuyu Pompası" required />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Aktif İken Kontrol Periyodu (Dakika)</label>
                            <input v-model="waterSourceForm.active_cycle_minutes" type="number" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Örn: 30" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Pasif İken Kontrol Periyodu (Dakika)</label>
                            <input v-model="waterSourceForm.passive_cycle_minutes" type="number" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Örn: 240" required />
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Fotoğraflı Kontrol Zorunlu mu?</label>
                        <select v-model="waterSourceForm.requires_photo_verification" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                            <option :value="true">Evet - Kontrol Esnasında Fotoğraf Alınsın</option>
                            <option :value="false">Hayır - Fotoğrafa Gerek Yok</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-xl">Su Kaynağı Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'filter'" @submit.prevent="submitFilter" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">İlişkili Üretim Yeri</label>
                        <select v-model="filterForm.production_location_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                            <option v-for="pl in productionLocations" :key="pl.id" :value="pl.id">{{ pl.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Filtre Tanım Adı</label>
                        <input v-model="filterForm.name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Örn: Disk Filtre Grubu A-1" required />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Temizlik Döngüsü (Kaç Günde Bir)</label>
                            <input v-model="filterForm.cleaning_cycle_days" type="number" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Örn: 2 (2 günde bir)" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Temizlenmiş Filtre Fotoğrafı Şartı</label>
                            <select v-model="filterForm.requires_photo_verification" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option :value="true">Zorunlu - Temizlenen Filtre Fotoğrafı Yüklenecek</option>
                                <option :value="false">İsteğe Bağlı</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Bağlı Olduğu Su Kaynakları (Çoklu Seçim)</label>
                        <div class="space-y-1 bg-slate-50 dark:bg-slate-950 p-2.5 rounded-xl border max-h-32 overflow-y-auto">
                            <label v-for="ws in waterSources" :key="ws.id" class="flex items-center space-x-2">
                                <input type="checkbox" :value="ws.id" v-model="filterForm.water_source_ids" class="rounded text-amber-600" />
                                <span class="font-bold">{{ ws.name }} ({{ ws.production_location?.name || 'Genel' }})</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="closeModal" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-amber-600 text-white font-bold rounded-xl">Filtre Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
        </Transition>

        <Transition name="modal">
            <div v-if="showCrewLeaderWorkersModal && selectedCrewLeader" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl w-full max-w-3xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <div class="p-6 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500 text-white">Çavuş Ekip Kadrosu</span>
                            <span class="text-xs text-slate-300 font-bold">{{ selectedCrewLeader.origin_city }}</span>
                        </div>
                        <h3 class="text-xl font-black mt-1 text-white">
                            {{ selectedCrewLeader.first_name }} {{ selectedCrewLeader.last_name }}
                        </h3>
                        <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-slate-300 font-medium">
                            <span class="flex items-center gap-1">{{ selectedCrewLeader.phone || '-' }}</span>
                            <span>•</span>
                            <span>TCKN: <span class="font-mono text-slate-200">{{ selectedCrewLeader.identity_number || '-' }}</span></span>
                            <span>•</span>
                            <span class="text-emerald-400 font-extrabold">₺{{ selectedCrewLeader.daily_wage }}/gün ({{ selectedCrewLeader.multiplier }}x)</span>
                            <span>•</span>
                            <span class="text-amber-300 font-bold">Min {{ selectedCrewLeader.min_car_requirement }} Araç (₺{{ selectedCrewLeader.travel_fee_per_car }})</span>
                        </div>
                    </div>
                    <button @click="closeCrewLeaderWorkersModal" class="text-slate-400 hover:text-white text-2xl font-bold p-1 leading-none cursor-pointer">&times;</button>
                </div>

                <div class="p-6 max-h-[60vh] overflow-y-auto space-y-4">
                    <div class="flex justify-between items-center pb-2 border-b border-slate-100 dark:border-slate-800">
                        <div class="text-xs text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wide flex items-center gap-2">
                            <span>Kayıtlı Ekip İşçileri</span>
                            <span class="px-2.5 py-0.5 bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 rounded-full font-extrabold text-[11px] border border-indigo-200 dark:border-indigo-800">
                                {{ getCrewLeaderWorkers(selectedCrewLeader.id).length }} İşçi
                            </span>
                        </div>
                        <button @click="openAddWorkerForCrewLeader(selectedCrewLeader)" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-1 cursor-pointer">
                            + Bu Çavuşa İşçi Ekle
                        </button>
                    </div>

                    <div v-if="getCrewLeaderWorkers(selectedCrewLeader.id).length > 0" class="overflow-x-auto rounded-2xl border border-slate-200/80 dark:border-slate-800">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-950/60 font-bold text-slate-500 uppercase border-b border-slate-200/80 dark:border-slate-800">
                                    <th class="p-3">İşçi Ad Soyad</th>
                                    <th class="p-3">TC Kimlik No</th>
                                    <th class="p-3">Performans</th>
                                    <th class="p-3">Sicil / Notlar</th>
                                    <th class="p-3">Durum</th>
                                    <th class="p-3 text-right">İşlem</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="w in getCrewLeaderWorkers(selectedCrewLeader.id)" :key="w.id" class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition">
                                    <td class="p-3 font-extrabold text-slate-800 dark:text-slate-100">
                                        {{ w.first_name }} {{ w.last_name }}
                                    </td>
                                    <td class="p-3 font-mono text-slate-600 dark:text-slate-400">
                                        {{ w.identity_number || '-' }}
                                    </td>
                                    <td class="p-3">
                                        <span class="font-bold text-amber-500">★ {{ w.performance_rating }}/5</span>
                                    </td>
                                    <td class="p-3 text-slate-500 dark:text-slate-400 max-w-[200px] truncate">
                                        {{ w.notes || '-' }}
                                    </td>
                                    <td class="p-3">
                                        <span :class="w.is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400'" class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase">
                                            {{ w.is_active ? 'Aktif' : 'Pasif' }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right space-x-2 shrink-0">
                                        <button @click="editWorkerFromModal(w)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">Düzenle</button>
                                        <button @click="deleteItem('definitions.workers.destroy', w.id)" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">Sil</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="text-center py-10 bg-slate-50 dark:bg-slate-950/40 rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Bu çavuşa kayıtlı işçi bulunmuyor</p>
                        <p class="text-[11px] text-slate-400 mt-1">Yukarıdaki "+ Bu Çavuşa İşçi Ekle" butonunu kullanarak ekip işçilerini sisteme dahil edebilirsiniz.</p>
                    </div>
                </div>

                <div class="p-4 bg-slate-50 dark:bg-slate-950 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button @click="closeCrewLeaderWorkersModal" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 rounded-xl text-xs font-bold transition cursor-pointer">
                        Kapat
                    </button>
                </div>
            </div>
        </div>
        </Transition>
    </AuthenticatedLayout>
</template>
