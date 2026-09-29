<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    show: boolean;
    type: string;
    item?: any;
    companies?: any[];
    personnels?: any[];
    units?: any[];
    products?: any[];
    crewLeaders?: any[];
    productionLocations?: any[];
    waterSources?: any[];
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'saved'): void;
}>();

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

const companyForm = useForm({
    id: null as any,
    name: '',
    code: '',
    tax_number: '',
    dia_company_code: '',
    is_active: true,
});

const personnelForm = useForm({
    id: null as any,
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    password: '',
    role_title: '',
    parent_personnel_id: null as any,
    company_ids: [] as any[],
    permissions: [] as any[],
    can_enter_backdated_data: false,
    is_active: true,
});

const tradingPartyForm = useForm({
    id: null as any,
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
    id: null as any,
    name: '',
    code: '',
    transport_by: 'customer',
    is_fee_included: true,
    extra_fee: 0,
});

const productionLocationForm = useForm({
    id: null as any,
    company_id: null as any,
    name: '',
    location_type: 'greenhouse',
    dia_branch_code: '',
    dia_warehouse_code: '',
    total_area_dekar: 0,
    approx_plant_count: 0,
    sections: [
        { id: null as any, name: 'Sera 1 - Tünel 1', section_type: 'greenhouse', area_dekar: 2.5, tunnel_count: 14, table_stand_count: 20 }
    ],
    valves: [
        { id: null as any, valve_number: 'V-01', name: 'Vana 1 Sulama', duty: 'irrigation', description: 'Ana damlama hattı' }
    ],
});

const productForm = useForm({
    id: null as any,
    name: '',
    code: '',
    product_type: 'produced',
    dia_stock_code: '',
    description: '',
    company_ids: [] as any[],
    unit_ids: [] as any[],
    subtypes: [
        { id: null as any, name: 'Çilek 1.Kalite', code: 'CLK-1' }
    ],
});

const jobTypeForm = useForm({
    id: null as any,
    name: '',
    code: '',
    form_type: 'general',
    description: '',
    unit_ids: [] as any[],
});

const unitForm = useForm({
    id: null as any,
    name: '',
    symbol: '',
    unit_category: 'quantity',
});

const packagingForm = useForm({
    id: null as any,
    name: '',
    code: '',
    product_id: null as any,
    dia_stock_code: '',
    capacity_qty: 1,
    unit_id: null as any,
});

const crewLeaderForm = useForm({
    id: null as any,
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
    id: null as any,
    crew_leader_id: null as any,
    first_name: '',
    last_name: '',
    identity_number: '',
    performance_rating: 4,
    notes: '',
    is_active: true,
});

const cateringSupplierForm = useForm({
    id: null as any,
    company_title: '',
    contact_person: '',
    phone: '',
    meal_unit_price: 65,
    is_vat_included: true,
    dia_cari_code: '',
});

const fertRecipeForm = useForm({
    id: null as any,
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
    id: null as any,
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
    id: null as any,
    production_location_id: null as any,
    name: '',
    active_cycle_minutes: 60,
    passive_cycle_minutes: 120,
    requires_photo_verification: true,
    is_active: true,
});

const filterForm = useForm({
    id: null as any,
    production_location_id: null as any,
    name: '',
    cleaning_cycle_days: 2,
    requires_photo_verification: true,
    is_active: true,
    water_source_ids: [] as any[],
});

watch(() => props.show, (newVal) => {
    if (!newVal) return;
    const item = props.item;
    const type = props.type;

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
            productionLocationForm.sections = item.sections && item.sections.length
                ? item.sections.map((s: any) => ({
                    id: s.id,
                    name: s.name,
                    section_type: s.section_type || 'greenhouse',
                    area_dekar: s.area_dekar,
                    tunnel_count: s.tunnel_count,
                    table_stand_count: s.table_stand_count,
                    approx_plant_count: s.approx_plant_count,
                }))
                : [{ id: null, name: '', section_type: 'greenhouse', area_dekar: 0, tunnel_count: 0, table_stand_count: 0 }];
            productionLocationForm.valves = item.valves && item.valves.length
                ? item.valves.map((v: any) => ({
                    id: v.id,
                    valve_number: v.valve_number,
                    name: v.name,
                    duty: v.duty,
                    description: v.description || '',
                }))
                : [{ id: null, valve_number: 'V-01', name: 'Vana 1', duty: 'irrigation', description: '' }];
        } else {
            productionLocationForm.company_id = props.companies?.[0]?.id || null;
            productionLocationForm.sections = [{ id: null, name: 'Bölüm 1', section_type: 'greenhouse', area_dekar: 1, tunnel_count: 1, table_stand_count: 10 }];
            productionLocationForm.valves = [{ id: null, valve_number: 'V-01', name: 'Vana 1', duty: 'irrigation', description: '' }];
        }
    } else if (type === 'product') {
        productForm.reset();
        if (item) {
            productForm.id = item.id;
            productForm.name = item.name;
            productForm.code = item.code;
            productForm.product_type = item.product_type;
            productForm.dia_stock_code = item.dia_stock_code;
            productForm.description = item.description || '';
            productForm.company_ids = item.companies ? item.companies.map((c: any) => c.id) : [];
            productForm.unit_ids = item.units ? item.units.map((u: any) => u.id) : [];
            productForm.subtypes = item.subtypes && item.subtypes.length 
                ? item.subtypes.map((st: any) => ({ id: st.id, name: st.name, code: st.code })) 
                : [{ id: null, name: '', code: '' }];
        } else {
            productForm.description = '';
            productForm.company_ids = [];
            productForm.unit_ids = [];
            productForm.subtypes = [{ id: null, name: '1.Kalite', code: 'KL-1' }];
        }
    } else if (type === 'job_type') {
        jobTypeForm.reset();
        if (item) {
            jobTypeForm.id = item.id;
            jobTypeForm.name = item.name;
            jobTypeForm.code = item.code;
            jobTypeForm.form_type = item.form_type;
            jobTypeForm.description = item.description;
            jobTypeForm.unit_ids = item.units ? item.units.map((u: any) => u.id) : [];
        } else {
            jobTypeForm.unit_ids = [];
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
        } else {
            packagingForm.product_id = props.products?.[0]?.id || null;
            packagingForm.unit_id = props.units?.[0]?.id || null;
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
        } else {
            workerForm.crew_leader_id = props.crewLeaders?.[0]?.id || null;
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
        } else {
            waterSourceForm.production_location_id = props.productionLocations?.[0]?.id || null;
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
        } else {
            filterForm.production_location_id = props.productionLocations?.[0]?.id || null;
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
});

const submitCompany = () => companyForm.post(route('definitions.companies.store'), { onSuccess: () => { emit('saved'); emit('close'); } });
const submitPersonnel = () => personnelForm.post(route('definitions.personnels.store'), { onSuccess: () => { emit('saved'); emit('close'); } });
const submitTradingParty = () => tradingPartyForm.post(route('definitions.trading-parties.store'), { onSuccess: () => { emit('saved'); emit('close'); } });
const submitDeliveryType = () => deliveryTypeForm.post(route('definitions.delivery-types.store'), { onSuccess: () => { emit('saved'); emit('close'); } });
const submitProductionLocation = () => productionLocationForm.post(route('definitions.production-locations.store'), { onSuccess: () => { emit('saved'); emit('close'); } });
const submitProduct = () => productForm.post(route('definitions.products.store'), { onSuccess: () => { emit('saved'); emit('close'); } });
const submitJobType = () => jobTypeForm.post(route('definitions.job-types.store'), { onSuccess: () => { emit('saved'); emit('close'); } });
const submitUnit = () => unitForm.post(route('definitions.units.store'), { onSuccess: () => { emit('saved'); emit('close'); } });
const submitPackaging = () => packagingForm.post(route('definitions.packagings.store'), { onSuccess: () => { emit('saved'); emit('close'); } });
const submitCrewLeader = () => crewLeaderForm.post(route('definitions.crew-leaders.store'), { onSuccess: () => { emit('saved'); emit('close'); } });
const submitWorker = () => workerForm.post(route('definitions.workers.store'), { onSuccess: () => { emit('saved'); emit('close'); } });
const submitCateringSupplier = () => cateringSupplierForm.post(route('definitions.catering-suppliers.store'), { onSuccess: () => { emit('saved'); emit('close'); } });
const submitFertRecipe = () => fertRecipeForm.post(route('definitions.fertilization-recipes.store'), { onSuccess: () => { emit('saved'); emit('close'); } });
const submitSprayRecipe = () => sprayRecipeForm.post(route('definitions.spraying-recipes.store'), { onSuccess: () => { emit('saved'); emit('close'); } });
const submitWaterSource = () => waterSourceForm.post(route('definitions.water-sources.store'), { onSuccess: () => { emit('saved'); emit('close'); } });
const submitFilter = () => filterForm.post(route('definitions.filters.store'), { onSuccess: () => { emit('saved'); emit('close'); } });

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
</script>

<template>
    <Transition name="modal">
        <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100">
                        {{ item ? ((modalTitles[type] || 'Kayıt') + ' Düzenle') : ('Yeni ' + (modalTitles[type] || 'Kayıt') + ' Ekle') }}
                    </h3>
                    <button type="button" @click="emit('close')" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-bold text-xl">&times;</button>
                </div>

                <form v-if="type === 'company'" @submit.prevent="submitCompany" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">Firma Unvanı</label>
                        <input v-model="companyForm.name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Firma Kodu</label>
                        <input v-model="companyForm.code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Vergi Numarası</label>
                            <input v-model="companyForm.tax_number" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-mono" placeholder="Örn: 7890123456" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">DİA Firma Kodu</label>
                            <input v-model="companyForm.dia_company_code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-mono" placeholder="DIA-SASA-01" />
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-2.5 bg-slate-50 dark:bg-slate-950 rounded-xl border">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input v-model="companyForm.is_active" type="checkbox" class="rounded text-emerald-600" />
                            <span class="font-bold text-emerald-600">Durum: {{ companyForm.is_active ? 'Aktif' : 'Pasif' }}</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Kaydet</button>
                    </div>
                </form>

                <form v-if="type === 'personnel'" @submit.prevent="submitPersonnel" class="space-y-4 text-xs">
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
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Personel Kaydet</button>
                    </div>
                </form>

                <form v-if="type === 'trading_party'" @submit.prevent="submitTradingParty" class="space-y-4 text-xs">
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
                            <label class="block font-bold mb-1">Cari Kodu / Hesap No</label>
                            <input v-model="tradingPartyForm.dia_cari_code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-mono" placeholder="CAR-001" />
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Vergi No / TCKN</label>
                            <input v-model="tradingPartyForm.tax_number" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-mono" placeholder="Vergi No" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Telefon</label>
                            <input v-model="tradingPartyForm.phone" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="0532..." />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">E-posta</label>
                            <input v-model="tradingPartyForm.email" type="email" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="cari@firma.com" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Varsayılan Nakliye Ücreti (₺)</label>
                            <input v-model="tradingPartyForm.default_transport_fee" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="0.00" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Fatura & Sevk Adresi</label>
                            <input v-model="tradingPartyForm.address" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Açık adres..." />
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Cari Kaydet</button>
                    </div>
                </form>

                <form v-if="type === 'delivery_type'" @submit.prevent="submitDeliveryType" class="space-y-4 text-xs">
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
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Teslim Şekli Kaydet</button>
                    </div>
                </form>

                <form v-if="type === 'production_location'" @submit.prevent="submitProductionLocation" class="space-y-4 text-xs">
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
                    <div class="border-t pt-2 mt-2 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block font-bold">Sera Tünel ve Bölüm Detayları</label>
                            <button type="button" @click="productionLocationForm.sections.push({ id: null, name: '', section_type: 'greenhouse', area_dekar: 0, tunnel_count: 0, table_stand_count: 0 })" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">+ Bölüm Ekle</button>
                        </div>
                        <div v-for="(sec, idx) in productionLocationForm.sections" :key="idx" class="flex items-center gap-1 mb-2 bg-slate-50 dark:bg-slate-950 p-2 rounded-lg border">
                            <input v-model="sec.name" type="text" placeholder="Tünel/Bölüm Adı" class="border rounded p-1 dark:bg-slate-900 flex-1" />
                            <input v-model="sec.area_dekar" type="number" step="0.1" placeholder="Dekar" class="border rounded p-1 dark:bg-slate-900 w-20" />
                            <input v-model="sec.tunnel_count" type="number" placeholder="Tünel" class="border rounded p-1 dark:bg-slate-900 w-16" />
                            <input v-model="sec.table_stand_count" type="number" placeholder="Sehpa" class="border rounded p-1 dark:bg-slate-900 w-16" />
                            <button v-if="productionLocationForm.sections.length > 1" type="button" @click="productionLocationForm.sections.splice(idx, 1)" class="text-rose-500 hover:text-rose-700 font-bold px-1.5">✕</button>
                        </div>
                    </div>
                    <div class="border-t pt-2 mt-2 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block font-bold">Vana Görev Tanımları</label>
                            <button type="button" @click="productionLocationForm.valves.push({ id: null, valve_number: `V-${String(productionLocationForm.valves.length + 1).padStart(2, '0')}`, name: '', duty: 'irrigation', description: '' })" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">+ Vana Ekle</button>
                        </div>
                        <div v-for="(v, idx) in productionLocationForm.valves" :key="idx" class="flex items-center gap-1 mb-2 bg-slate-50 dark:bg-slate-950 p-2 rounded-lg border">
                            <input v-model="v.valve_number" type="text" placeholder="Vana No (V-01)" class="border rounded p-1 dark:bg-slate-900 w-24" />
                            <input v-model="v.name" type="text" placeholder="Vana Adı" class="border rounded p-1 dark:bg-slate-900 flex-1" />
                            <select v-model="v.duty" class="border rounded p-1 dark:bg-slate-900 w-28">
                                <option value="irrigation">Sulama</option>
                                <option value="misting">Sisleme</option>
                                <option value="fertilization">Gübreleme</option>
                            </select>
                            <button v-if="productionLocationForm.valves.length > 1" type="button" @click="productionLocationForm.valves.splice(idx, 1)" class="text-rose-500 hover:text-rose-700 font-bold px-1.5">✕</button>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Üretim Yerini Kaydet</button>
                    </div>
                </form>

                <form v-if="type === 'product'" @submit.prevent="submitProduct" class="space-y-4 text-xs">
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
                    <div>
                        <label class="block font-bold mb-1">Geçerli Ölçü Birimleri (Çoklu Seçim)</label>
                        <div class="grid grid-cols-3 gap-2 bg-slate-50 dark:bg-slate-950 p-2.5 rounded-xl border max-h-32 overflow-y-auto">
                            <label v-for="u in units" :key="u.id" class="flex items-center space-x-2">
                                <input type="checkbox" :value="u.id" v-model="productForm.unit_ids" class="rounded text-rose-600" />
                                <span class="font-bold">{{ u.name }} ({{ u.symbol }})</span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Açıklama</label>
                        <textarea v-model="productForm.description" rows="2" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Ürün açıklaması..."></textarea>
                    </div>
                    <div class="border-t pt-2 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block font-bold">Ürün Alt Tipleri & Kalite Sınıfları</label>
                            <button type="button" @click="productForm.subtypes.push({ id: null, name: '', code: '' })" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">+ Alt Tip Ekle</button>
                        </div>
                        <div v-for="(st, idx) in productForm.subtypes" :key="idx" class="flex items-center gap-1 mb-2 bg-slate-50 dark:bg-slate-950 p-2 rounded-lg border">
                            <input v-model="st.name" type="text" placeholder="Alt Tip Adı (Örn: 1.Kalite)" class="border rounded p-1 dark:bg-slate-900 flex-1" />
                            <input v-model="st.code" type="text" placeholder="Kod (Örn: CLK-1)" class="border rounded p-1 dark:bg-slate-900 w-32" />
                            <button v-if="productForm.subtypes.length > 1" type="button" @click="productForm.subtypes.splice(idx, 1)" class="text-rose-500 hover:text-rose-700 font-bold px-1.5">✕</button>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Ürün Kaydet</button>
                    </div>
                </form>

                <form v-if="type === 'job_type'" @submit.prevent="submitJobType" class="space-y-4 text-xs">
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
                    <div>
                        <label class="block font-bold mb-1">Açıklama</label>
                        <textarea v-model="jobTypeForm.description" rows="2" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="İş tanımı hakkında açıklama..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">İş Tanımı Kaydet</button>
                    </div>
                </form>

                <form v-if="type === 'unit'" @submit.prevent="submitUnit" class="space-y-4 text-xs">
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
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Birim Kaydet</button>
                    </div>
                </form>

                <form v-if="type === 'packaging'" @submit.prevent="submitPackaging" class="space-y-4 text-xs">
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
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Paket Tipi Kaydet</button>
                    </div>
                </form>

                <form v-if="type === 'crew_leader'" @submit.prevent="submitCrewLeader" class="space-y-4 text-xs">
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
                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block font-bold mb-1">TC Kimlik No</label>
                            <input v-model="crewLeaderForm.identity_number" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Telefon</label>
                            <input v-model="crewLeaderForm.phone" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="0532..." />
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
                    <div class="p-2.5 bg-slate-50 dark:bg-slate-950 rounded-xl border">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" v-model="crewLeaderForm.is_leader_fee_included" class="rounded text-rose-600" />
                            <span class="font-bold">Çavuş Hakedişi Dahil</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Çavuş Kaydet</button>
                    </div>
                </form>

                <form v-if="type === 'worker'" @submit.prevent="submitWorker" class="space-y-4 text-xs">
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
                    <div class="p-2.5 bg-slate-50 dark:bg-slate-950 rounded-xl border">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" v-model="workerForm.is_active" class="rounded text-rose-600" />
                            <span class="font-bold">Aktif Çalışan İşçi</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">İşçi Kaydet</button>
                    </div>
                </form>

                <form v-if="type === 'catering_supplier'" @submit.prevent="submitCateringSupplier" class="space-y-4 text-xs">
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
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Tedarikçi Kaydet</button>
                    </div>
                </form>

                <form v-if="type === 'fert_recipe'" @submit.prevent="submitFertRecipe" class="space-y-4 text-xs">
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
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                        <button type="submit" :disabled="fertRecipeForm.processing" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-xs transition">
                            Gübre Reçetesini Kaydet
                        </button>
                    </div>
                </form>

                <form v-if="type === 'spray_recipe'" @submit.prevent="submitSprayRecipe" class="space-y-4 text-xs">
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
                    <div class="border-t pt-2 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block font-bold">Reçete İlaç Karışım Listesi (Kaç Litreye Ne Kadar İlaç)</label>
                            <button type="button" @click="sprayRecipeForm.items.push({ product_name: '', brand: '', quantity: 100, unit: 'ml', notes: '' })" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">+ İlaç / Etken Madde Ekle</button>
                        </div>
                        <div v-for="(item, idx) in sprayRecipeForm.items" :key="idx" class="flex items-center gap-1.5 bg-slate-50 dark:bg-slate-950 p-2 rounded-xl border mb-2">
                            <input v-model="item.product_name" type="text" placeholder="İlaç Ürünü / Etken Madde" class="border rounded p-1.5 dark:bg-slate-900 flex-1" />
                            <input v-model="item.brand" type="text" placeholder="Marka (Bayer)" class="border rounded p-1.5 dark:bg-slate-900 w-28" />
                            <input v-model="item.quantity" type="number" step="0.1" placeholder="Miktar" class="border rounded p-1.5 dark:bg-slate-900 w-20" />
                            <input v-model="item.notes" type="text" placeholder="Güvenlik Notu / Açıklama" class="border rounded p-1.5 dark:bg-slate-900 flex-1" />
                            <button v-if="sprayRecipeForm.items.length > 1" type="button" @click="sprayRecipeForm.items.splice(idx, 1)" class="text-rose-500 hover:text-rose-700 font-bold px-1.5">✕</button>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-purple-600 text-white font-bold rounded-xl">İlaç Reçetesi Kaydet</button>
                    </div>
                </form>

                <form v-if="type === 'water_source'" @submit.prevent="submitWaterSource" class="space-y-4 text-xs">
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
                    <div class="p-2.5 bg-slate-50 dark:bg-slate-950 rounded-xl border">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" v-model="waterSourceForm.is_active" class="rounded text-blue-600" />
                            <span class="font-bold">Su Kaynağı Aktif</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-xl">Su Kaynağı Kaydet</button>
                    </div>
                </form>

                <form v-if="type === 'filter'" @submit.prevent="submitFilter" class="space-y-4 text-xs">
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
                    <div class="p-2.5 bg-slate-50 dark:bg-slate-950 rounded-xl border">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" v-model="filterForm.is_active" class="rounded text-amber-600" />
                            <span class="font-bold">Filtre Aktif</span>
                        </label>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="emit('close')" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-amber-600 text-white font-bold rounded-xl">Filtre Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </Transition>
</template>
