<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    activeCategory?: string;
    activeModule?: string;
    companies: any[];
    locations: any[];
    jobTypes: any[];
    personnels: any[];
    workers: any[];
    products: any[];
    crewLeaders: any[];
    tradingParties: any[];
    deliveryTypes: any[];
    fertRecipes: any[];
    sprayRecipes: any[];
    waterSources: any[];
    packagings: any[];
    marketPrices?: any[];
    workPlans: any[];
    fertRuns: any[];
    irrigationSchedules: any[];
    sprayApplications: any[];
    waterAnalysisLogs: any[];
    rawWaterControls: any[];
    purificationControls: any[];
    customerOrders: any[];
    shipments: any[];
    dailyWorkSheets: any[];
}>();

const currentCat = ref(props.activeCategory || 'tesis');
const currentMod = ref(props.activeModule || 'is_planlama');

const showModal = ref(false);
const modalType = ref('');
const printModalData = ref<any>(null);
const showPrintModal = ref(false);

const genericPrintData = ref<any>(null);
const genericPrintType = ref<string>('');
const showGenericPrintModal = ref(false);
const openGenericPrintModal = (type: string, data: any) => {
    genericPrintType.value = type;
    genericPrintData.value = data;
    showGenericPrintModal.value = true;
};

const showCrewPrintModal = ref(false);
const crewPrintData = ref<any>(null);

const showDetailModal = ref(false);
const selectedDetailSheet = ref<any>(null);

const openDetailModal = (sheet: any) => {
    selectedDetailSheet.value = sheet;
    showDetailModal.value = true;
};



const showOrderPrintModal = ref(false);
const orderPrintData = ref<any>(null);

const openOrderPrintModal = (order: any) => {
    orderPrintData.value = order;
    showOrderPrintModal.value = true;
};

const orderFilterTab = ref<'active' | 'completed' | 'cancelled' | 'all'>('active');
const orderSearchQuery = ref('');

const formatDisplayDate = (val: string) => {
    if (!val) return '-';
    const clean = val.split('T')[0];
    const parts = clean.split('-');
    if (parts.length === 3) {
        return `${parts[2]}.${parts[1]}.${parts[0]}`;
    }
    return clean;
};

const updateCustomerOrderStatus = (orderId: number, status: string) => {
    router.patch(route('agriculture.customer-orders.update-status', orderId), { status }, { preserveScroll: true });
};

const deleteItem = (routeName: string, id: number, label: string) => {
    if (confirm(`Bu ${label} kaydını silmek istediğinize emin misiniz?`)) {
        router.delete(route(routeName, id), { preserveScroll: true });
    }
};

const openFormModal = (type: string, item: any = null) => {
    modalType.value = type;
    if (type === 'fert_run') {
        if (item) {
            fertRunForm.id = item.id;
            fertRunForm.fertilization_recipe_id = item.fertilization_recipe_id;
            fertRunForm.start_date = item.start_date ? item.start_date.split('T')[0] : '';
            fertRunForm.end_condition = item.end_condition || '';
            fertRunForm.is_active = item.is_active !== undefined ? Boolean(item.is_active) : true;
        } else {
            fertRunForm.id = null;
            fertRunForm.fertilization_recipe_id = props.fertRecipes[0]?.id || null;
            fertRunForm.start_date = new Date().toISOString().split('T')[0];
            fertRunForm.end_condition = '';
            fertRunForm.is_active = true;
        }
    } else if (type === 'sulama') {
        if (item) {
            irrigationForm.id = item.id;
            irrigationForm.schedule_date = item.schedule_date ? item.schedule_date.split('T')[0] : '';
            irrigationForm.run_number = item.run_number || 1;
            irrigationForm.start_time = item.start_time || '09:00';
            irrigationForm.production_location_id = item.production_location_id;
            irrigationForm.is_fertilized = Boolean(item.is_fertilized);
            irrigationForm.tank_stage_note = item.tank_stage_note || '';
            irrigationForm.notes = item.notes || '';
        } else {
            irrigationForm.id = null;
            irrigationForm.schedule_date = new Date().toISOString().split('T')[0];
            irrigationForm.run_number = 1;
            irrigationForm.start_time = '09:00';
            irrigationForm.production_location_id = props.locations[0]?.id || null;
            irrigationForm.is_fertilized = false;
            irrigationForm.tank_stage_note = '';
            irrigationForm.notes = '';
            loadIrrigationValvesForLocation();
        }
    } else if (type === 'spray_app') {
        if (item) {
            sprayAppForm.id = item.id;
            sprayAppForm.application_date = item.application_date ? item.application_date.split('T')[0] : '';
            sprayAppForm.spraying_recipe_id = item.spraying_recipe_id;
            sprayAppForm.purpose = item.purpose || '';
            sprayAppForm.covered_area_description = item.covered_area_description || '';
            sprayAppForm.is_tank_finished = Boolean(item.is_tank_finished);
        } else {
            sprayAppForm.id = null;
            sprayAppForm.application_date = new Date().toISOString().split('T')[0];
            sprayAppForm.spraying_recipe_id = props.sprayRecipes[0]?.id || null;
            sprayAppForm.purpose = '';
            sprayAppForm.covered_area_description = '';
            sprayAppForm.is_tank_finished = false;
            sprayAppForm.batch_code = '';
            sprayAppForm.notes = '';
        }
    } else if (type === 'water_analysis') {
        if (item) {
            waterAnalysisForm.id = item.id;
            waterAnalysisForm.water_source_id = item.water_source_id;
            waterAnalysisForm.ph_level = item.ph_level;
            waterAnalysisForm.ec_level = item.ec_level;
            waterAnalysisForm.notes = item.notes || '';
        } else {
            waterAnalysisForm.id = null;
            waterAnalysisForm.water_source_id = props.waterSources[0]?.id || null;
            waterAnalysisForm.analysis_date = new Date().toISOString().split('T')[0];
            waterAnalysisForm.ph_level = '' as any;
            waterAnalysisForm.ec_level = '' as any;
            waterAnalysisForm.notes = '';
        }
    } else if (type === 'raw_water') {
        if (item) {
            rawWaterForm.id = item.id;
            rawWaterForm.control_date = item.control_date ? item.control_date.split('T')[0] : '';
            rawWaterForm.water_source_id = item.water_source_id;
            rawWaterForm.ec_val = item.ec_val;
            rawWaterForm.ph_val = item.ph_val;
            rawWaterForm.pump_status = item.pump_status || 'open';
            rawWaterForm.water_tank_level = item.water_tank_level || 'full';
            rawWaterForm.chlorine_tank_level = item.chlorine_tank_level || 'full';
            rawWaterForm.dosing_pump_mode = item.dosing_pump_mode || 'auto';
            rawWaterForm.is_filter_cleaned = Boolean(item.is_filter_cleaned);
        } else {
            rawWaterForm.id = null;
            rawWaterForm.water_source_id = props.waterSources[0]?.id || null;
            rawWaterForm.control_date = new Date().toISOString().split('T')[0];
            rawWaterForm.ec_val = '' as any;
            rawWaterForm.ph_val = '' as any;
            rawWaterForm.pump_status = 'open';
            rawWaterForm.water_tank_level = 'full';
            rawWaterForm.chlorine_tank_level = 'full';
            rawWaterForm.dosing_pump_mode = 'auto';
            rawWaterForm.is_filter_cleaned = false;
        }
    } else if (type === 'purification') {
        if (item) {
            purificationForm.id = item.id;
            purificationForm.water_source_id = item.water_source_id;
            purificationForm.inlet_pressure_bar = item.inlet_pressure_bar;
            purificationForm.outlet_pressure_bar = item.outlet_pressure_bar;
        } else {
            purificationForm.id = null;
            purificationForm.water_source_id = props.waterSources[0]?.id || null;
            purificationForm.control_date = new Date().toISOString().split('T')[0];
            purificationForm.inlet_pressure_bar = '' as any;
            purificationForm.outlet_pressure_bar = '' as any;
        }
    } else if (type === 'work_plan') {
        if (item) {
            workPlanForm.id = item.id;
            workPlanForm.title = item.title || '';
            workPlanForm.production_location_id = item.production_location_id;
            workPlanForm.job_type_id = item.job_type_id;
            workPlanForm.assigned_personnel_id = item.assigned_personnel_id;
            workPlanForm.plan_date = item.plan_date ? item.plan_date.split('T')[0] : '';
            workPlanForm.due_date = item.due_date ? item.due_date.split('T')[0] : '';
            workPlanForm.status = item.status || 'pending';
            workPlanForm.description = item.description || '';
            workPlanForm.completion_notes = item.completion_notes || '';
        } else {
            workPlanForm.id = null;
            workPlanForm.title = '';
            workPlanForm.production_location_id = props.locations[0]?.id || null;
            workPlanForm.job_type_id = props.jobTypes[0]?.id || null;
            workPlanForm.assigned_personnel_id = props.personnels[0]?.id || null;
            workPlanForm.plan_date = new Date().toISOString().split('T')[0];
            workPlanForm.due_date = '';
            workPlanForm.status = 'pending';
            workPlanForm.description = '';
            workPlanForm.completion_notes = '';
        }
    } else if (type === 'market_price') {
        if (item) {
            marketPriceForm.id = item.id;
            marketPriceForm.product_id = item.product_id;
            marketPriceForm.price_date = item.price_date ? item.price_date.split('T')[0] : '';
            marketPriceForm.unit_price = item.unit_price;
            marketPriceForm.source_name = item.source_name;
        } else {
            marketPriceForm.id = null;
            marketPriceForm.product_id = props.products[0]?.id || null;
            marketPriceForm.price_date = new Date().toISOString().split('T')[0];
            marketPriceForm.unit_price = 95;
            marketPriceForm.source_name = 'Antalya Hal Fiyatı';
        }
    } else if (type === 'crew_leader') {
        if (item) {
            crewLeaderForm.id = item.id;
            crewLeaderForm.first_name = item.first_name || '';
            crewLeaderForm.last_name = item.last_name || '';
            crewLeaderForm.identity_number = item.identity_number || '';
            crewLeaderForm.phone = item.phone || '';
            crewLeaderForm.origin_city = item.origin_city || '';
            crewLeaderForm.daily_wage = item.daily_wage || 0;
            crewLeaderForm.multiplier = item.multiplier || 1.0;
            crewLeaderForm.min_car_requirement = item.min_car_requirement || 0;
            crewLeaderForm.travel_fee_per_car = item.travel_fee_per_car || 0;
            crewLeaderForm.is_food_included = Boolean(item.is_food_included);
            crewLeaderForm.is_leader_fee_included = Boolean(item.is_leader_fee_included);
            crewLeaderForm.dia_cari_code = item.dia_cari_code || '';
        } else {
            crewLeaderForm.reset();
            crewLeaderForm.id = null;
        }
    } else if (type === 'worker') {
        if (item) {
            workerForm.id = item.id;
            workerForm.crew_leader_id = item.crew_leader_id;
            workerForm.first_name = item.first_name || '';
            workerForm.last_name = item.last_name || '';
            workerForm.identity_number = item.identity_number || '';
            workerForm.performance_rating = item.performance_rating || 3;
            workerForm.notes = item.notes || '';
            workerForm.is_active = Boolean(item.is_active);
        } else {
            workerForm.reset();
            workerForm.id = null;
            // Eger onceden belirlenmis bir crew_leader_id varsa korunacak. (UI tarafindan atanacak)
        }
    }
    showModal.value = true;
};

const activeOrdersCount = computed(() => (props.customerOrders || []).filter((o: any) => o.status === 'confirmed' || o.status === 'pending' || !o.status).length);
const completedOrdersCount = computed(() => (props.customerOrders || []).filter((o: any) => o.status === 'shipped').length);
const cancelledOrdersCount = computed(() => (props.customerOrders || []).filter((o: any) => o.status === 'cancelled').length);

const filteredCustomerOrders = computed(() => {
    return (props.customerOrders || []).filter((o: any) => {
        if (orderFilterTab.value === 'active' && (o.status === 'shipped' || o.status === 'cancelled')) return false;
        if (orderFilterTab.value === 'completed' && o.status !== 'shipped') return false;
        if (orderFilterTab.value === 'cancelled' && o.status !== 'cancelled') return false;

        if (orderSearchQuery.value.trim()) {
            const q = orderSearchQuery.value.toLowerCase();
            const partyName = o.trading_party?.name?.toLowerCase() || '';
            const cariCode = o.trading_party?.dia_cari_code?.toLowerCase() || '';
            const contact = o.contact_person?.toLowerCase() || '';
            const hasProduct = o.items?.some((i: any) => i.product?.name?.toLowerCase().includes(q));
            return partyName.includes(q) || cariCode.includes(q) || contact.includes(q) || hasProduct;
        }

        return true;
    });
});

const shipmentFilterTab = ref<'all' | 'on_the_way' | 'delivered'>('all');
const shipmentSearchQuery = ref('');
const showWaybillPrintModal = ref(false);
const waybillPrintData = ref<any>(null);

const openWaybillPrintModal = (shipment: any) => {
    waybillPrintData.value = shipment;
    showWaybillPrintModal.value = true;
};

const updateShipmentDeliveryStatus = (shipmentId: number, status: string) => {
    router.patch(route('agriculture.shipment-deliveries.update-status', shipmentId), { status }, { preserveScroll: true });
};

const totalShipmentQuantity = computed(() => (props.shipments || []).reduce((sum: number, s: any) => sum + parseFloat(s.quantity || 0), 0));
const totalShipmentRevenue = computed(() => (props.shipments || []).reduce((sum: number, s: any) => sum + (parseFloat(s.quantity || 0) * parseFloat(s.unit_price || 0)), 0));
const onTheWayShipmentsCount = computed(() => (props.shipments || []).filter((s: any) => s.status === 'on_the_way').length);
const deliveredShipmentsCount = computed(() => (props.shipments || []).filter((s: any) => s.status === 'delivered').length);

const filteredShipments = computed(() => {
    return (props.shipments || []).filter((s: any) => {
        if (shipmentFilterTab.value === 'on_the_way' && s.status !== 'on_the_way') return false;
        if (shipmentFilterTab.value === 'delivered' && s.status !== 'delivered') return false;

        if (shipmentSearchQuery.value.trim()) {
            const q = shipmentSearchQuery.value.toLowerCase();
            const partyName = s.trading_party?.name?.toLowerCase() || '';
            const plate = s.vehicle_plate?.toLowerCase() || '';
            const driver = s.driver_name?.toLowerCase() || '';
            const productName = s.product?.name?.toLowerCase() || '';
            const waybill = s.dia_waybill_code?.toLowerCase() || '';
            return partyName.includes(q) || plate.includes(q) || driver.includes(q) || productName.includes(q) || waybill.includes(q);
        }

        return true;
    });
});

const downloadCsvFile = (filename: string, headers: string[], rows: (string | number)[][]) => {
    const processRow = (row: (string | number)[]) => {
        return row.map(val => {
            const cleanStr = (val === null || val === undefined) ? '' : String(val).replace(/"/g, '""');
            return `"${cleanStr}"`;
        }).join(';');
    };

    const csvContent = '\uFEFF' + [headers.join(';'), ...rows.map(processRow)].join('\r\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `${filename}_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
};

const exportOrdersToExcel = () => {
    const headers = [
        'Sipariş No',
        'Sipariş Tarihi',
        'İstenen Teslim Tarihi',
        'Müşteri / Cari Adı',
        'Cari Kodu',
        'Yetkili / Kontak',
        'Sipariş Kalemleri (Ürün - Ambalaj - Miktar - Birim Fiyat)',
        'Toplam Sipariş Kg',
        'Sevk Edilen Kg',
        'Kalan Bakiye Kg',
        'Toplam Tutar (TL)',
        'Sipariş Durumu',
        'Sipariş Notu'
    ];

    const rows = filteredCustomerOrders.value.map((o: any) => {
        const itemsSummary = (o.items || []).map((i: any) => `${i.product?.name || 'Ürün'} (${i.quantity} kg @ ₺${i.unit_price})`).join(' | ');
        const statusText = o.status === 'shipped' ? 'Sevk Edildi' : (o.status === 'confirmed' ? 'Onaylı / Hazırlanıyor' : (o.status === 'cancelled' ? 'İptal Edildi' : 'Beklemede'));

        return [
            `#ORD-${String(o.id).padStart(4, '0')}`,
            o.order_date || '',
            o.requested_delivery_date || '',
            o.trading_party?.name || '',
            o.trading_party?.dia_cari_code || '',
            o.contact_person || '',
            itemsSummary,
            getOrderTotalQty(o),
            getOrderShippedQty(o),
            getOrderRemainingQty(o),
            parseFloat(o.total_amount || 0).toFixed(2),
            statusText,
            o.notes || ''
        ];
    });

    downloadCsvFile('SASA_Tarim_Musteri_Siparisleri', headers, rows);
};

const exportShipmentsToExcel = () => {
    const headers = [
        'İrsaliye No',
        'Sevkiyat Tarihi',
        'Müşteri / Cari Adı',
        'Cari Kodu',
        'Bağlı Sipariş No',
        'Araç Plakası',
        'Şoför Adı',
        'Şoför Telefon',
        'Sevk Edilen Ürün',
        'Paketleme / Ambalaj',
        'Sevk Miktarı (Kg)',
        'Birim Satış Fiyatı (TL)',
        'Toplam İrsaliye Tutarı (TL)',
        'Teslimat Şekli',
        'Sevkiyat Durumu',
        'Sevkiyat Notu'
    ];

    const rows = filteredShipments.value.map((s: any) => {
        const orderCode = (s.customer_order_id || s.order_id) ? `#ORD-${String(s.customer_order_id || s.order_id).padStart(4, '0')}` : 'Bağımsız Sevkiyat';
        const totalLineAmount = (parseFloat(s.quantity || 0) * parseFloat(s.unit_price || 0)).toFixed(2);
        const statusText = s.status === 'delivered' ? 'Teslim Edildi' : 'Yolda';

        return [
            s.dia_waybill_code || `IRS-2026-${s.id}`,
            s.shipment_date || '',
            s.trading_party?.name || '',
            s.trading_party?.dia_cari_code || '',
            orderCode,
            s.vehicle_plate || '',
            s.driver_name || '',
            s.driver_phone || '',
            s.product?.name || '',
            s.packaging?.name || 'Standart Kasa',
            parseFloat(s.quantity || 0).toFixed(2),
            parseFloat(s.unit_price || 0).toFixed(2),
            totalLineAmount,
            s.delivery_type?.name || 'Araç Teslim',
            statusText,
            s.notes || ''
        ];
    });

    downloadCsvFile('SASA_Tarim_Sevkiyat_ve_Irsaliyeler', headers, rows);
};

const exportDailyWorkSheetsToExcel = () => {
    const headers = [
        'Form No',
        'Tarih',
        'Tesis / Üretim Yeri',
        'Çavuş Adı (Taşeron)',
        'Çavuş Kodu',
        'Gelen İşçi Sayısı',
        'Servis Araç Sayısı',
        'Toplam Mesai Saati',
        'Toplam Çavuş Hakedişi (TL)',
        'Toplanan Hasat Detayı (Ürün - Miktar - Kasa)',
        'Toplam Hasat Miktarı (Kg)',
        'Tahmini Hasat Geliri (TL)',
        'Onay Durumu',
        'Onaylayan Yönetici'
    ];

    const rows = (props.dailyWorkSheets || []).map((sheet: any) => {
        const harvestDetails = (sheet.harvest_items || []).map((h: any) => `${h.product?.name || 'Ürün'} (${h.quantity} kg, ${h.box_count || 0} kasa)`).join(' | ');
        const totalKg = (sheet.harvest_items || []).reduce((sum: number, h: any) => sum + parseFloat(h.quantity || 0), 0);
        const totalRevenue = (sheet.harvest_items || []).reduce((sum: number, h: any) => sum + (parseFloat(h.quantity || 0) * parseFloat(h.unit_price || 0)), 0);
        const statusText = sheet.approval_status === 'approved' ? 'Onaylandı' : (sheet.approval_status === 'rejected' ? 'Reddedildi' : 'Onay Bekliyor');

        return [
            `#DW-${String(sheet.id).padStart(4, '0')}`,
            sheet.work_date || '',
            sheet.production_location?.name || 'SASA Tarım Tesisi',
            sheet.labor_head?.name || sheet.labor_head_name || '-',
            sheet.labor_head?.dia_cari_code || '-',
            sheet.worker_count || 0,
            sheet.service_vehicle_count || 0,
            sheet.total_work_hours || 0,
            parseFloat(sheet.total_labor_cost || 0).toFixed(2),
            harvestDetails || '-',
            totalKg.toFixed(2),
            totalRevenue.toFixed(2),
            statusText,
            sheet.approved_by_user?.name || '-'
        ];
    });

    downloadCsvFile('SASA_Tarim_Gunluk_Isci_Hasat_Puantaj', headers, rows);
};

const showBananaModal = ref(false);
const bananaItemData = ref<any>(null);

const totalWorkPlansCount = computed(() => (props.workPlans || []).length);
const previewImageUrl = ref('');
const showImagePreview = ref(false);
const openImagePreview = (url: string) => {
    previewImageUrl.value = url;
    showImagePreview.value = true;
};

const activeFertRun = computed(() => (props.fertRuns || []).find((r: any) => r.is_active));
const pressureWarningsCount = computed(() => (props.purificationControls || []).filter((pc: any) => pc.has_warning).length);
const totalOrdersAmount = computed(() => (props.customerOrders || []).reduce((sum: number, o: any) => sum + parseFloat(o.total_amount || 0), 0));
const totalHarvestQuantity = computed(() => {
    let total = 0;
    (props.dailyWorkSheets || []).forEach((sheet: any) => {
        sheet.harvest_items?.forEach((item: any) => {
            total += parseFloat(item.quantity || 0);
        });
    });
    return total;
});

const getModalTitle = computed(() => {
    switch (modalType.value) {
        case 'work_plan':
            return workPlanForm.id ? 'İş Planı Düzenleme & Durum Güncelleme' : 'Yeni İş Planı Oluşturma Formu';
        case 'fert_run':
            return fertRunForm.id ? 'Gübreleme Programı Düzenleme' : 'Yeni Gübreleme Reçetesi Aktifleştirme Formu';
        case 'sulama':
            return irrigationForm.id ? 'Sulama Programı & Vana Matrisi Düzenleme' : 'Yeni Sulama Programı & Vana Süre Matrisi Formu';
        case 'spray_app':
            return sprayAppForm.id ? 'Zirai İlaçlama Kaydı Düzenleme' : 'Yeni Zirai İlaçlama Uygulama Formu';
        case 'water_analysis':
            return waterAnalysisForm.id ? 'Su Analiz Raporu Düzenleme' : 'Yeni Su Analiz Raporu Kayıt Formu';
        case 'raw_water':
            return rawWaterForm.id ? 'Kaynak Suyu & Filtre Kontrolü Düzenleme' : 'Günlük Kaynak Suyu, Depo & Filtre Kontrol Formu';
        case 'purification':
            return purificationForm.id ? 'Arıtma Suyu & Basınç Kontrolü Düzenleme' : 'Arıtma Suyu & Barometre Fark Basınç Kayıt Formu';
        case 'market_price':
            return marketPriceForm.id ? 'Piyasa Hal Fiyatı Düzenleme' : 'Yeni Piyasa Hal Fiyatı Kaydı';
        case 'shipment':
            return 'Yeni Sevkiyat & İrsaliye Kayıt Formu';
        case 'order':
            return 'Yeni Müşteri Siparişi Kayıt Formu';
        case 'order_edit':
            return 'Müşteri Siparişi Düzenleme Formu';
        case 'daily_sheet':
            return 'Günlük İşçi ve Hasat Formu';
        case 'crew_leader':
            return crewLeaderForm.id ? 'Çavuş Kaydı Düzenleme' : 'Yeni Çavuş Kayıt Formu';
        case 'worker':
            return workerForm.id ? 'İşçi Kaydı Düzenleme' : 'Yeni İşçi Kayıt Formu';
        default:
            return 'Operasyonel Form';
    }
});

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

const totalCrewWages = computed(() => {
    let total = 0;
    (props.dailyWorkSheets || []).forEach((sheet: any) => {
        sheet.crew_leaders?.forEach((cl: any) => {
            total += parseFloat(cl.calculated_wage_total || 0);
        });
    });
    return total;
});

const groupedCrewLeaderCosts = computed(() => {
    const map = new Map<number, {
        id: number;
        name: string;
        code: string;
        total_days: number;
        total_workers: number;
        total_cars: number;
        total_overtime: number;
        total_meal_fee: number;
        total_travel_fee: number;
        total_wage: number;
    }>();

    props.dailyWorkSheets.forEach((sheet: any) => {
        sheet.crew_leaders?.forEach((cl: any) => {
            const leaderId = cl.crew_leader_id;
            if (!leaderId) return;
            const name = cl.crew_leader ? `${cl.crew_leader.first_name} ${cl.crew_leader.last_name}` : 'Çavuş';
            const code = cl.dia_cari_code || cl.crew_leader?.dia_cari_code || 'CH-001';

            if (!map.has(leaderId)) {
                map.set(leaderId, {
                    id: leaderId,
                    name,
                    code,
                    total_days: 0,
                    total_workers: 0,
                    total_cars: 0,
                    total_overtime: 0,
                    total_meal_fee: 0,
                    total_travel_fee: 0,
                    total_wage: 0,
                });
            }

            const item = map.get(leaderId)!;
            item.total_days += 1;
            item.total_workers += parseInt(cl.worker_count || 0);
            item.total_cars += parseInt(cl.car_count || 0);
            item.total_overtime += parseFloat(cl.overtime_hours || 0);
            item.total_meal_fee += parseFloat(cl.meal_fee || 0);
            item.total_travel_fee += parseFloat(cl.travel_fee || 0);
            item.total_wage += parseFloat(cl.calculated_wage_total || 0);
        });
    });

    return Array.from(map.values());
});

const bananaForm = useForm({
    merchant_scale_1st_kg: 0,
    merchant_scale_2nd_kg: 0,
    unit_price: 45,
});

const workPlanForm = useForm({
    id: null,
    title: '',
    production_location_id: props.locations[0]?.id || null,
    job_type_id: props.jobTypes[0]?.id || null,
    assigned_personnel_id: props.personnels[0]?.id || null,
    plan_date: new Date().toISOString().split('T')[0],
    due_date: '',
    status: 'pending',
    description: '',
    completion_notes: '',
});

const showCommentModal = ref(false);
const commentWorkPlan = ref<any>(null);
const commentForm = useForm({
    work_plan_id: null as any,
    comment: '',
    photo: null as any,
});

const openAddCommentModal = (wp: any) => {
    commentWorkPlan.value = wp;
    commentForm.work_plan_id = wp.id;
    commentForm.comment = '';
    commentForm.photo = null;
    showCommentModal.value = true;
};

const submitCommentForm = () => {
    commentForm.post(route('agriculture.work-plans.comment'), {
        onSuccess: () => {
            showCommentModal.value = false;
        }
    });
};

const fertRunForm = useForm({
    id: null as number | null,
    fertilization_recipe_id: props.fertRecipes[0]?.id || null,
    start_date: new Date().toISOString().split('T')[0],
    end_condition: '',
    is_active: true,
});

const showTankLogModal = ref(false);
const tankLogTarget = ref<any>(null);
const tankLogForm = useForm({
    fertilization_run_id: null as any,
    fertilization_tank_id: null as any,
    prepared_at: new Date().toISOString().slice(0, 16),
    prepared_by_id: props.personnels[0]?.id || null,
    tank_name: '',
    notes: '',
});

const openTankLogModal = (run: any, tank: any) => {
    tankLogTarget.value = { run, tank };
    tankLogForm.fertilization_run_id = run.id;
    tankLogForm.fertilization_tank_id = tank.id;
    tankLogForm.tank_name = tank.tank_name;
    tankLogForm.prepared_at = new Date().toISOString().slice(0, 16);
    tankLogForm.prepared_by_id = props.personnels[0]?.id || null;
    tankLogForm.notes = `${tank.tank_name} solüsyonu yenilendi. Reçete gübreleri hazırlandı ve DIA stok düşüm kaydı oluşturuldu.`;
    showTankLogModal.value = true;
};

const submitTankLogForm = () => {
    tankLogForm.post(route('agriculture.fertilization-tank-logs.store'), {
        onSuccess: () => {
            showTankLogModal.value = false;
        }
    });
};

const showTankPrintModal = ref(false);
const tankPrintData = ref<any>(null);

const openTankPrintModal = (run: any, tank: any) => {
    tankPrintData.value = { run, recipe: run.recipe, tank };
    showTankPrintModal.value = true;
};

const irrigationForm = useForm({
    id: null as number | null,
    schedule_date: new Date().toISOString().split('T')[0],
    run_number: 1,
    start_time: '09:00',
    production_location_id: props.locations[0]?.id || null,
    is_fertilized: false,
    fertilization_recipe_id: props.fertRecipes[0]?.id || null,
    tank_stage_note: '',
    notes: '',
    valves: [] as any[],
});

const loadIrrigationValvesForLocation = () => {
    const loc = props.locations.find((l: any) => l.id === irrigationForm.production_location_id);
    if (loc && loc.valves && loc.valves.length > 0) {
        irrigationForm.valves = loc.valves.map((v: any) => ({
            valve_id: v.id,
            valve_number: v.valve_number,
            name: `${loc.name} > ${v.name} (${v.duty === 'misting' ? 'Sisleme' : 'Sulama'})`,
            duration_minutes: '',
        }));
    } else {
        irrigationForm.valves = [
            { valve_id: null, valve_number: 'V-01', name: 'Sera 1 > Vana 1 (Sulama)', duration_minutes: '' },
            { valve_id: null, valve_number: 'V-02', name: 'Sera 1 > Vana 2 (Sulama)', duration_minutes: '' },
            { valve_id: null, valve_number: 'V-03', name: 'Sera 1 > Vana 3 (Sisleme)', duration_minutes: '' },
            { valve_id: null, valve_number: 'V-04', name: 'Sera 1 > Vana 4 (Sisleme)', duration_minutes: '' }
        ];
    }
};

const applyEqualDuration = (minutes: number) => {
    irrigationForm.valves.forEach((v: any) => {
        v.duration_minutes = minutes;
    });
};

const sprayAppForm = useForm({
    id: null as number | null,
    application_date: new Date().toISOString().split('T')[0],
    spraying_recipe_id: props.sprayRecipes[0]?.id || null,
    purpose: '',
    applied_by_id: props.personnels[0]?.id || null,
    covered_area_description: '',
    is_tank_finished: false,
    batch_code: '',
    notes: '',
});

const waterAnalysisForm = useForm({
    id: null as number | null,
    water_source_id: props.waterSources[0]?.id || null,
    analysis_date: new Date().toISOString().split('T')[0],
    ph_level: '' as any,
    ec_level: '' as any,
    notes: '',
});

const rawWaterForm = useForm({
    id: null as number | null,
    water_source_id: props.waterSources[0]?.id || null,
    control_date: new Date().toISOString().split('T')[0],
    ec_val: '' as any,
    ph_val: '' as any,
    pump_status: 'open',
    pump_fault_note: '',
    active_start_date: new Date().toISOString().split('T')[0],
    passive_start_date: '',
    source_switch_reason: '',
    is_filter_cleaned: false,
    water_tank_level: 'full',
    water_tank_note: '',
    chlorine_tank_level: 'full',
    dosing_pump_mode: 'auto',
    dosing_pump_manual_val: '',
    dosing_pump_fault_note: '',
});

const purificationForm = useForm({
    id: null as number | null,
    water_source_id: props.waterSources[0]?.id || null,
    control_date: new Date().toISOString().split('T')[0],
    inlet_pressure_bar: '' as any,
    outlet_pressure_bar: '' as any,
    max_threshold_bar: 1.5,
});

const orderForm = useForm({
    trading_party_id: props.tradingParties[0]?.id || null,
    order_date: new Date().toISOString().split('T')[0],
    requested_delivery_date: '',
    contact_person: '',
    total_amount: 0,
    notes: '',
    items: [
        { product_id: props.products[0]?.id || null, packaging_definition_id: props.packagings[0]?.id || null, quantity: 100, unit_price: 50 }
    ],
});

const shipmentForm = useForm({
    customer_order_id: props.customerOrders[0]?.id || null,
    trading_party_id: props.tradingParties[0]?.id || null,
    product_id: props.products[0]?.id || null,
    packaging_definition_id: props.packagings[0]?.id || null,
    shipment_date: new Date().toISOString().split('T')[0],
    quantity: 100,
    unit_price: 50,
    delivery_type_id: props.deliveryTypes[0]?.id || null,
    vehicle_plate: '',
    driver_name: '',
    driver_phone: '',
    dia_waybill_code: '',
    status: 'on_the_way',
    notes: '',
});

const marketPriceForm = useForm({
    id: null as number | null,
    product_id: props.products[0]?.id || null,
    price_date: new Date().toISOString().split('T')[0],
    unit_price: 95,
    source_name: 'Antalya Hal Fiyatı',
});

const crewLeaderForm = useForm({
    id: null as number | null,
    first_name: '',
    last_name: '',
    identity_number: '',
    phone: '',
    origin_city: '',
    daily_wage: 0,
    multiplier: 1.0,
    min_car_requirement: 0,
    travel_fee_per_car: 0,
    is_food_included: false,
    is_leader_fee_included: true,
    dia_cari_code: '',
});

const workerForm = useForm({
    id: null as number | null,
    crew_leader_id: null as number | null,
    first_name: '',
    last_name: '',
    identity_number: '',
    performance_rating: 3,
    notes: '',
    is_active: true,
});

const dailyWorkSheetForm = useForm({
    work_date: new Date().toISOString().split('T')[0],
    company_id: props.companies[0]?.id || null,
    production_location_id: props.locations[0]?.id || null,
    has_external_workers: true,
    has_internal_workers: true,
    storage_destination: 'direct_sale',
    notes: '',
    crew_leaders: [
        { crew_leader_id: props.crewLeaders[0]?.id || null, worker_count: props.crewLeaders[0]?.workers?.length || 6, extra_worker_count: 0, car_count: props.crewLeaders[0]?.min_car_requirement || 2, overtime_hours: 0, extra_wage_per_worker: 0 }
    ],
    assignments: [
        { crew_leader_id: props.crewLeaders[0]?.id || null, worker_id: null, personnel_id: null, job_type_id: props.jobTypes[0]?.id || null, start_time: '08:00', end_time: '17:00', break_minutes: 60 }
    ],
    harvest_items: [
        {
            product_id: props.products[0]?.id || null,
            product_subtype_id: props.products[0]?.subtypes?.[0]?.id || null,
            packaging_definition_id: props.packagings[0]?.id || null,
            package_count: 50,
            quantity: 500,
            unit_symbol: 'kg',
            unit_price: 90,
            crop_type: 'strawberry',
            banana_bunch_count: 0,
            farm_scale_kg: 0,
            merchant_scale_1st_kg: 0,
            merchant_scale_2nd_kg: 0,
        }
    ]
});

const formatSheetDate = (dateVal: any) => {
    if (!dateVal) return '-';
    const s = String(dateVal).split('T')[0];
    const parts = s.split('-');
    if (parts.length === 3) {
        return `${parts[2]}.${parts[1]}.${parts[0]}`;
    }
    return s;
};

const onDailyWorkSheetCrewLeaderChange = () => {
    const clId = dailyWorkSheetForm.crew_leaders[0]?.crew_leader_id;
    const cl = props.crewLeaders?.find((c: any) => Number(c.id) === Number(clId));
    if (cl) {
        dailyWorkSheetForm.crew_leaders[0].extra_worker_count = 0;
        dailyWorkSheetForm.crew_leaders[0].worker_count = cl.workers?.length || 6;
        dailyWorkSheetForm.crew_leaders[0].car_count = cl.min_car_requirement || 2;
    }
};

const getSelectedCrewLeaderRegisteredCount = (clId: any) => {
    const cl = props.crewLeaders?.find((c: any) => Number(c.id) === Number(clId));
    return cl?.workers?.length || 6;
};

const searchDailyWorkSheet = ref('');
const selectedLocationFilter = ref('all');
const dailyWorkSheetViewMode = ref<'cards' | 'table'>('cards');

const getDayNumber = (dateVal: any) => {
    if (!dateVal) return '';
    const s = String(dateVal).split('T')[0];
    const parts = s.split('-');
    return parts[2] || '';
};

const getMonthYearText = (dateVal: any) => {
    if (!dateVal) return '';
    const s = String(dateVal).split('T')[0];
    const parts = s.split('-');
    const months: Record<string, string> = {
        '01': 'Oca', '02': 'Şub', '03': 'Mar', '04': 'Nis', '05': 'May', '06': 'Haz',
        '07': 'Tem', '08': 'Ağu', '09': 'Eyl', '10': 'Eki', '11': 'Kas', '12': 'Ara'
    };
    return `${months[parts[1]] || parts[1]} ${parts[0]}`;
};

const filteredDailyWorkSheets = computed(() => {
    return (props.dailyWorkSheets || []).filter((dws: any) => {
        if (selectedLocationFilter.value !== 'all' && String(dws.production_location_id) !== String(selectedLocationFilter.value)) {
            return false;
        }
        if (!searchDailyWorkSheet.value.trim()) return true;
        const q = searchDailyWorkSheet.value.toLowerCase();
        const dateStr = formatSheetDate(dws.work_date).toLowerCase();
        const locName = (dws.production_location?.name || '').toLowerCase();
        const crewNames = (dws.crew_leaders || []).map((cl: any) => `${cl.crew_leader?.first_name} ${cl.crew_leader?.last_name}`).join(' ').toLowerCase();
        const productNames = (dws.harvest_items || []).map((h: any) => h.product?.name || '').join(' ').toLowerCase();
        return dateStr.includes(q) || locName.includes(q) || crewNames.includes(q) || productNames.includes(q);
    });
});

const menuHierarchy = [
    {
        category: 'tesis',
        title: 'Tesis Yönetimi',
        items: [
            { key: 'is_planlama', label: 'İş Planlama' },
        ]
    },
    {
        category: 'uretim',
        title: 'Üretim',
        items: [
            { key: 'gubreleme', label: 'Gübreleme' },
            { key: 'sulama', label: 'Sulama' },
            { key: 'ilaclama', label: 'İlaçlama' },
            { key: 'su_analizleri', label: 'Su Analizleri' },
        ]
    },
    {
        category: 'teknik',
        title: 'Teknik',
        items: [
            { key: 'kaynak_suyu_kontrol', label: 'Kaynak Suyu Kontrol' },
            { key: 'aritma_suyu_kontrol', label: 'Arıtma Suyu Kontrol' },
        ]
    },
    {
        category: 'operasyon',
        title: 'Operasyon',
        items: [
            { key: 'alinan_siparis', label: 'Alınan Sipariş' },
            { key: 'sevkiyat_teslimat', label: 'Sevkiyat & Teslimat' },
            { key: 'gunluk_isci_formu', label: 'Günlük İşçi Formu' },
            { key: 'isci_ve_cavuslar', label: 'İşçi & Çavuş Yönetimi' },
        ]
    },
    {
        category: 'raporlar',
        title: 'Raporlar',
        items: [
            { key: 'piyasa_fiyatlari', label: 'Piyasa Fiyatları' },
            { key: 'hasat_miktarlari', label: 'Hasat Miktarları' },
            { key: 'isci_maliyet_analizi', label: 'İşçi Maliyet Analizi' },
            { key: 'stok_inceleme', label: 'Stok İnceleme' },
        ]
    }
];

const page = usePage();
const permissions = computed(() => (page.props.auth as any)?.permissions || []);
const isAdmin = computed(() => !!(page.props.auth as any)?.is_admin);

const filteredMenuHierarchy = computed(() => {
    if (isAdmin.value) return menuHierarchy;
    return menuHierarchy.filter(g => permissions.value.includes(g.category));
});

const openDropdown = ref<string | null>(null);

const selectModule = (catKey: string, modKey: string) => {
    currentCat.value = catKey;
    currentMod.value = modKey;
    openDropdown.value = null;
    router.get(route('agriculture.index'), { cat: catKey, mod: modKey }, { preserveState: true });
};

onMounted(() => {
    const handleDocumentClick = (e: MouseEvent) => {
        if (!(e.target as HTMLElement).closest('.group-dropdown-container')) {
            openDropdown.value = null;
        }
    };
    document.addEventListener('click', handleDocumentClick);
    onUnmounted(() => {
        document.removeEventListener('click', handleDocumentClick);
    });
});



const openEditWorkPlanModal = (wp: any) => {
    openFormModal('work_plan', wp);
};

const submitWorkPlan = () => {
    workPlanForm.post(route('agriculture.work-plans.store'), { onSuccess: () => showModal.value = false });
};

const submitFertRun = () => {
    fertRunForm.post(route('agriculture.fertilization-runs.store'), { onSuccess: () => showModal.value = false });
};

const submitIrrigation = () => {
    irrigationForm.post(route('agriculture.irrigation-schedules.store'), { onSuccess: () => showModal.value = false });
};



const submitSprayApp = () => {
    sprayAppForm.post(route('agriculture.spraying-applications.store'), { onSuccess: () => showModal.value = false });
};

const submitWaterAnalysis = () => {
    waterAnalysisForm.post(route('agriculture.water-analyses.store'), { onSuccess: () => showModal.value = false });
};

const submitRawWater = () => {
    rawWaterForm.post(route('agriculture.raw-water-controls.store'), { onSuccess: () => showModal.value = false });
};

const submitPurification = () => {
    purificationForm.post(route('agriculture.purification-controls.store'), { onSuccess: () => showModal.value = false });
};

const addOrderItem = () => {
    orderForm.items.push({
        product_id: props.products[0]?.id || null,
        packaging_definition_id: props.packagings[0]?.id || null,
        quantity: 100,
        unit_price: 50,
    });
};

const removeOrderItem = (index: number) => {
    if (orderForm.items.length > 1) {
        orderForm.items.splice(index, 1);
    }
};

const calculateOrderTotal = computed(() => {
    return orderForm.items.reduce((sum: number, item: any) => sum + (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0)), 0);
});

const submitOrder = () => {
    orderForm.total_amount = calculateOrderTotal.value;
    orderForm.post(route('agriculture.customer-orders.store'), { onSuccess: () => showModal.value = false });
};

const editingOrderId = ref<number | null>(null);
const orderEditForm = useForm({
    trading_party_id: null as any,
    order_date: '',
    requested_delivery_date: '',
    contact_person: '',
    notes: '',
    status: 'pending',
    items: [] as any[],
});

const openEditOrderModal = (order: any) => {
    editingOrderId.value = order.id;
    orderEditForm.trading_party_id = order.trading_party_id;
    orderEditForm.order_date = order.order_date ? order.order_date.split('T')[0] : '';
    orderEditForm.requested_delivery_date = order.requested_delivery_date ? order.requested_delivery_date.split('T')[0] : '';
    orderEditForm.contact_person = order.contact_person || '';
    orderEditForm.notes = order.notes || '';
    orderEditForm.status = order.status || 'pending';
    orderEditForm.items = (order.items || []).map((i: any) => ({
        product_id: i.product_id,
        packaging_definition_id: i.packaging_definition_id,
        quantity: parseFloat(i.quantity || 0),
        unit_price: parseFloat(i.unit_price || 0),
    }));
    if (orderEditForm.items.length === 0) {
        orderEditForm.items.push({
            product_id: props.products[0]?.id || null,
            packaging_definition_id: props.packagings[0]?.id || null,
            quantity: 100,
            unit_price: 50,
        });
    }
    modalType.value = 'order_edit';
    showModal.value = true;
};

const addOrderEditItem = () => {
    orderEditForm.items.push({
        product_id: props.products[0]?.id || null,
        packaging_definition_id: props.packagings[0]?.id || null,
        quantity: 100,
        unit_price: 50,
    });
};

const removeOrderEditItem = (index: number) => {
    if (orderEditForm.items.length > 1) {
        orderEditForm.items.splice(index, 1);
    }
};

const calculateOrderEditTotal = computed(() => {
    return orderEditForm.items.reduce((sum, it) => sum + ((parseFloat(it.quantity) || 0) * (parseFloat(it.unit_price) || 0)), 0);
});

const submitEditOrder = () => {
    if (!editingOrderId.value) return;
    orderEditForm.put(route('agriculture.customer-orders.update', editingOrderId.value), {
        onSuccess: () => {
            showModal.value = false;
        }
    });
};

const getOrderTotalQty = (order: any) => {
    return (order.items || []).reduce((sum: number, it: any) => sum + parseFloat(it.quantity || 0), 0);
};

const getOrderShippedQty = (order: any) => {
    return (order.shipments || []).reduce((sum: number, sh: any) => sum + parseFloat(sh.quantity || 0), 0);
};

const getOrderRemainingQty = (order: any) => {
    const total = getOrderTotalQty(order);
    const shipped = getOrderShippedQty(order);
    return Math.max(0, total - shipped);
};

const getOrderProgressPercent = (order: any) => {
    const total = getOrderTotalQty(order);
    if (total <= 0) return order.status === 'shipped' ? 100 : 0;
    const shipped = getOrderShippedQty(order);
    return Math.min(100, Math.round((shipped / total) * 100));
};

const openShipmentFromOrder = (order: any) => {
    const remaining = getOrderRemainingQty(order);
    const firstItem = order.items?.[0];
    shipmentForm.customer_order_id = order.id;
    shipmentForm.trading_party_id = order.trading_party_id;
    shipmentForm.product_id = firstItem?.product_id || (props.products[0]?.id || null);
    shipmentForm.packaging_definition_id = firstItem?.packaging_definition_id || (props.packagings[0]?.id || null);
    shipmentForm.shipment_date = new Date().toISOString().split('T')[0];
    shipmentForm.quantity = remaining > 0 ? remaining : (firstItem?.quantity || 100);
    shipmentForm.unit_price = firstItem?.unit_price || 0;
    shipmentForm.delivery_type_id = props.deliveryTypes[0]?.id || null;
    shipmentForm.vehicle_plate = '';
    shipmentForm.driver_name = '';
    shipmentForm.driver_phone = '';
    shipmentForm.dia_waybill_code = `IRS-${new Date().getFullYear()}-${String(order.id).padStart(4, '0')}`;
    shipmentForm.status = 'on_the_way';
    shipmentForm.notes = `#ORD-${String(order.id).padStart(4, '0')} nolu siparişe istinaden sevk edildi`;
    modalType.value = 'shipment';
    showModal.value = true;
};

const submitShipment = () => {
    shipmentForm.post(route('agriculture.shipment-deliveries.store'), { onSuccess: () => showModal.value = false });
};

const submitMarketPrice = () => {
    marketPriceForm.post(route('agriculture.market-prices.store'), { onSuccess: () => showModal.value = false });
};

const submitCrewLeader = () => {
    crewLeaderForm.post(route('crew-leaders.store'), { onSuccess: () => showModal.value = false });
};

const submitWorker = () => {
    workerForm.post(route('workers.store'), { onSuccess: () => showModal.value = false });
};

const submitDailyWorkSheet = () => {
    const regCount = getSelectedCrewLeaderRegisteredCount(dailyWorkSheetForm.crew_leaders[0]?.crew_leader_id);
    const extraCount = Number(dailyWorkSheetForm.crew_leaders[0]?.extra_worker_count || 0);
    dailyWorkSheetForm.crew_leaders[0].worker_count = regCount + extraCount;
    dailyWorkSheetForm.post(route('agriculture.daily-work-sheets.store'), { onSuccess: () => showModal.value = false });
};

const openCrewPrintModal = (sheet: any, crew: any) => {
    crewPrintData.value = { sheet, crew };
    showCrewPrintModal.value = true;
};

const openBananaModal = (item: any) => {
    bananaItemData.value = item;
    bananaForm.merchant_scale_1st_kg = item.merchant_scale_1st_kg || 0;
    bananaForm.merchant_scale_2nd_kg = item.merchant_scale_2nd_kg || 0;
    bananaForm.unit_price = item.unit_price || 45;
    showBananaModal.value = true;
};

const submitBananaWeights = () => {
    if (bananaItemData.value) {
        bananaForm.patch(route('agriculture.daily-work-sheets.update-banana-weights', bananaItemData.value.id), {
            onSuccess: () => showBananaModal.value = false
        });
    }
};

const openPrintRecipeModal = (recipe: any, tank: any) => {
    printModalData.value = { recipe, tank };
    showPrintModal.value = true;
};

const printPage = (elementId?: string) => {
    let targetEl: HTMLElement | null = null;
    if (elementId) {
        targetEl = document.getElementById(elementId);
    } else {
        targetEl = document.querySelector('.print-document') as HTMLElement;
    }

    if (!targetEl) {
        const prevTitle = document.title;
        document.title = "";
        window.print();
        document.title = prevTitle;
        return;
    }

    const iframe = document.createElement('iframe');
    iframe.style.position = 'fixed';
    iframe.style.right = '0';
    iframe.style.bottom = '0';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    document.body.appendChild(iframe);

    const doc = iframe.contentWindow?.document;
    if (!doc) {
        window.print();
        return;
    }

    const tailwindLink = document.querySelector('link[rel="stylesheet"]')?.outerHTML || '';
    const styleTags = Array.from(document.querySelectorAll('style')).map(s => s.outerHTML).join('\n');

    doc.open();
    doc.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title></title>
            <meta charset="utf-8">
            ${tailwindLink}
            ${styleTags}
            <style>
                @page { 
                    size: A4 portrait; 
                    margin: 8mm 12mm; 
                }
                * { box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif !important; 
                    background: white !important; 
                    color: #0f172a !important; 
                    margin: 0 !important; 
                    padding: 0 !important; 
                }
                .no-print { display: none !important; }
                .print-document { 
                    width: 100% !important; 
                    max-width: 100% !important; 
                    border: none !important; 
                    box-shadow: none !important; 
                    padding: 0 !important; 
                }
            </style>
        </head>
        <body>
            <div class="print-document">
                ${targetEl.innerHTML}
            </div>
        </body>
        </html>
    `);
    doc.close();

    if (iframe.contentWindow) {
        iframe.contentWindow.document.title = "";
    }

    iframe.contentWindow?.focus();
    setTimeout(() => {
        iframe.contentWindow?.print();
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 1000);
    }, 300);
};

const canApprove = (dws: any) => {
    if (dws.status !== 'submitted') return false;
    if (isAdmin.value) return true;
    const currentPersonnel = (page.props.auth as any).personnel;
    if (currentPersonnel && dws.submitted_by?.parent_personnel_id === currentPersonnel.id) {
        return true;
    }
    return false;
};

const handleApproval = (id: number, action: string) => {
    let rejectionNote = '';
    if (action === 'reject') {
        const res = window.prompt('Formu reddetmek için bir açıklama giriniz:');
        if (res === null) return;
        if (!res.trim()) {
            alert('Red açıklaması girmek zorunludur.');
            return;
        }
        rejectionNote = res;
    }
    
    router.post(route('agriculture.daily-work-sheets.approve', id), {
        action: action,
        rejection_note: rejectionNote
    });
};
</script>

<template>
    <Head title="Tarım Operasyonları" />

    <AuthenticatedLayout>
        <template #header>
            <div class="w-full flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0 border border-emerald-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M7 20h10"/><path d="M12 20v-8"/><path d="M12 12a5 5 0 0 1 8-3.5 5 5 0 0 1-3.5 8.5H12"/><path d="M12 12a5 5 0 0 0-8-3.5 5 5 0 0 0 3.5 8.5H12"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-sm font-black text-slate-900 dark:text-slate-100 uppercase tracking-tight">
                                Tarım Operasyonları
                            </h1>
                            <span class="text-slate-300 dark:text-slate-700">/</span>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                {{ filteredMenuHierarchy.flatMap(g => g.items).find(i => i.key === currentMod)?.label || 'Genel' }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-tight">Üretim sahaları, gübreleme, sulama ve operasyon yönetimi</p>
                    </div>
                </div>
            </div>
        </template>

        <div class="space-y-4">
            <div v-if="currentMod === 'is_planlama'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Yapılacaklar Listesi (İş Planlama)</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">İşletme müdürü tarafından atanan işler, hedef tarihler ve görevlilerin fotoğraf doğrulamaları</p>
                    </div>
                    <button @click="openFormModal('work_plan')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <span class="text-sm leading-none">+</span> Yeni İş Planı Oluştur
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam İş Planı</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ workPlans.length }} Görev</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Bekleyen Görevler</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400 text-sm">{{ workPlans.filter(w => w.status === 'pending').length }} İş</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Devam Eden Saha İşleri</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ workPlans.filter(w => w.status === 'in_progress').length }} İş</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Tamamlananlar</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ workPlans.filter(w => w.status === 'completed').length }} İş</span>
                    </div>
                </div>

                <div v-if="workPlans.length === 0" class="text-center py-12 border border-slate-100 dark:border-slate-800 rounded-xl bg-slate-50/50 dark:bg-slate-950/40">
                    <h3 class="text-sm font-bold text-slate-600 dark:text-slate-300">Henüz İş Planı Oluşturulmadı</h3>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div v-for="wp in workPlans" :key="wp.id" class="border border-slate-200 dark:border-slate-800/80 rounded-2xl p-4 bg-slate-50/50 dark:bg-slate-900/90 space-y-3">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">{{ wp.title }}</h3>
                                <span class="text-[10px] text-slate-400">İş Türü: {{ wp.job_type?.name || 'Genel Görev' }}</span>
                            </div>
                            <span :class="wp.status === 'completed' ? 'bg-emerald-950/80 text-emerald-400 border-emerald-800/80' : (wp.status === 'in_progress' ? 'bg-indigo-950/80 text-indigo-300 border-indigo-800/80' : (wp.status === 'cancelled' ? 'bg-rose-950/80 text-rose-400 border-rose-800/80' : 'bg-amber-950/80 text-amber-400 border-amber-800/80'))" class="px-2.5 py-1 rounded-lg text-xs font-semibold border">
                                {{ wp.status === 'completed' ? 'Tamamlandı' : (wp.status === 'in_progress' ? 'Devam Ediyor' : (wp.status === 'cancelled' ? 'İptal Edildi' : 'Beklemede')) }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-950/80 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800/80">
                            <strong>Müdür Talimatı:</strong> {{ wp.description || 'Açıklama girilmedi.' }}
                        </p>
                        <div class="text-xs space-y-1.5 text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-950/80 p-3 rounded-xl border border-slate-200 dark:border-slate-800/80">
                            <div>Tesis: <strong>{{ wp.production_location?.name || 'Tesis Belirtilmedi' }}</strong></div>
                            <div>Görevli / Sorumlu: <strong>{{ wp.assigned_personnel ? (wp.assigned_personnel.first_name + ' ' + wp.assigned_personnel.last_name) : 'Genel Atama' }}</strong></div>
                            <div>Plan Tarihi: <strong>{{ wp.plan_date }}</strong> | Hedef Bitiş: <strong class="text-rose-600 dark:text-rose-400">{{ wp.due_date || 'Süre Belirtilmedi' }}</strong></div>
                            <div v-if="wp.completion_notes" class="pt-2 border-t border-slate-800 text-emerald-700 dark:text-emerald-400">
                                <strong>Süreç / Sonuç Notu:</strong> {{ wp.completion_notes }}
                            </div>
                            <div v-if="wp.photo_path" class="pt-1">
                                <strong>Fotoğraf Kanıtı:</strong> <button @click="openImagePreview(wp.photo_path)" class="text-indigo-600 dark:text-indigo-400 underline font-bold text-[11px]">Fotoğrafı Görüntüle</button>
                            </div>
                            <div v-if="wp.comments && wp.comments.length > 0" class="pt-2 border-t border-slate-800 space-y-1.5">
                                <div class="text-[11px] font-bold text-slate-500 uppercase">Personel Yorum & Süreç Akışı ({{ wp.comments.length }} Not):</div>
                                <div v-for="c in wp.comments" :key="c.id" class="p-2 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-[11px]">
                                    <div class="flex justify-between font-bold text-slate-700 dark:text-slate-300">
                                        <span>{{ c.personnel ? (c.personnel.first_name + ' ' + c.personnel.last_name) : 'Personel' }}</span>
                                        <span class="text-[10px] text-slate-400 font-normal">{{ new Date(c.created_at).toLocaleDateString('tr-TR') }}</span>
                                    </div>
                                    <p class="text-slate-600 dark:text-slate-400 mt-0.5">{{ c.comment }}</p>
                                    <div v-if="c.photo_path" class="mt-1">
                                        <button @click="openImagePreview(c.photo_path)" class="text-rose-600 dark:text-rose-400 font-bold underline text-[10px]">Yorum Görselini Büyüt</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center gap-2 pt-1 flex-wrap">
                            <button @click="openAddCommentModal(wp)" class="bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                Not / Yorum Ekle
                            </button>
                            <div class="flex items-center gap-2">
                                <button @click="openGenericPrintModal('is_planlama', wp)" class="bg-teal-600 hover:bg-teal-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                    Çıktı Al
                                </button>
                                <button @click="openEditWorkPlanModal(wp)" class="bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition">
                                    Durum & Not Güncelle
                                </button>
                                <button @click="deleteItem('agriculture.work-plans.destroy', wp.id, 'iş planı')" class="bg-rose-500/10 hover:bg-rose-500/20 text-rose-500 border border-rose-500/30 px-3 py-1.5 rounded-lg text-xs font-bold transition cursor-pointer">
                                    Sil
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="currentMod === 'gubreleme'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Gübreleme Programı & Tank Takibi</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Reçete aktifleştirme, sözel bitiş şartı ve çalışanlar için tank hazırlama döküm çıktısı</p>
                    </div>
                    <button @click="openFormModal('fert_run')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <span class="text-sm leading-none">+</span> Yeni Reçete Aktifleştir
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-2 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Programlanan Reçeteler</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ fertRuns.length }} Reçete</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Aktif Uygulanan Reçete</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ fertRuns.filter(f => f.is_active).length }} Aktif</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Hedef pH Seviyesi</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">6.50 pH</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Hedef EC İletkenlik</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">1.80 mS/cm</span>
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-800 rounded-lg overflow-hidden">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-50 dark:bg-slate-950/80 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-2">Reçete Adı</th>
                                <th class="p-2">Tesis</th>
                                <th class="p-2">Başlangıç</th>
                                <th class="p-2">pH / EC</th>
                                <th class="p-2">Bitiş Şartı</th>
                                <th class="p-2 text-center">Durum</th>
                                <th class="p-2 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="fr in fertRuns" :key="fr.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/40 transition">
                                <td class="p-2 font-bold text-slate-800 dark:text-slate-100">
                                    {{ fr.recipe?.name || 'Topraksız Çilek Büyütme Reçetesi' }}
                                </td>
                                <td class="p-2 text-slate-600 dark:text-slate-300">
                                    {{ fr.production_location?.name || 'SASA Tarım Tesisi' }}
                                </td>
                                <td class="p-2 font-mono text-slate-500 text-[11px]">
                                    {{ formatDisplayDate(fr.start_date) }}
                                </td>
                                <td class="p-2 font-mono text-slate-700 dark:text-slate-300 text-[11px]">
                                    pH: {{ fr.recipe?.water_ph || '6.5' }} | EC: {{ fr.recipe?.water_ec || '1.8' }}
                                </td>
                                <td class="p-2 font-semibold text-rose-500 text-[11px]">
                                    {{ fr.end_condition || fr.recipe?.duration_condition || '-' }}
                                </td>
                                <td class="p-2 text-center">
                                    <button type="button" @click="router.post(route('agriculture.fertilization-runs.toggle-active', fr.id), {}, { preserveScroll: true })"
                                            :class="fr.is_active ? 'bg-emerald-950/80 text-emerald-400 border-emerald-800/80 hover:bg-emerald-900/80' : 'bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700'"
                                            class="px-2 py-0.5 rounded text-[10px] font-bold border inline-flex items-center gap-1 cursor-pointer transition">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="fr.is_active ? 'bg-emerald-400' : 'bg-slate-500'"></span>
                                        {{ fr.is_active ? 'Aktif' : 'Pasif (Aktif Yap)' }}
                                    </button>
                                </td>
                                <td class="p-2 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2 text-[11px]">
                                        <button v-if="!fr.is_active" @click="router.post(route('agriculture.fertilization-runs.toggle-active', fr.id), {}, { preserveScroll: true })" class="text-emerald-500 font-bold hover:underline cursor-pointer">
                                            Aktif Yap
                                        </button>
                                        <button @click="openFormModal('fert_run', fr)" class="text-slate-700 dark:text-slate-200 font-semibold hover:underline cursor-pointer">
                                            Düzenle
                                        </button>
                                        <button v-if="fr.recipe?.tanks && fr.recipe.tanks[0]" @click="openTankPrintModal(fr, fr.recipe.tanks[0])" class="text-slate-700 dark:text-slate-200 font-semibold hover:underline cursor-pointer">
                                            Çıktı
                                        </button>
                                        <button v-else @click="openGenericPrintModal('gubreleme', fr)" class="text-slate-700 dark:text-slate-200 font-semibold hover:underline cursor-pointer">
                                            Çıktı
                                        </button>
                                        <button @click="deleteItem('agriculture.fertilization-runs.destroy', fr.id, 'gübreleme reçetesi')" class="text-rose-500 font-semibold hover:underline cursor-pointer">
                                            Sil
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- AKTİF REÇETENİN TANK HAZIRLAMA & STOK KARTLARI -->
                <div v-if="fertRuns.filter(f => f.is_active).length > 0" class="space-y-2.5 pt-1">
                    <div v-for="fr in fertRuns.filter(f => f.is_active)" :key="'tanks-' + fr.id" class="space-y-2.5">
                        <div class="flex items-center justify-between flex-wrap gap-2 pb-1.5 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <span class="px-1.5 py-0.5 bg-emerald-600 text-white text-[9px] font-bold rounded">AKTİF REÇETE</span>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-slate-100">
                                    {{ fr.recipe?.name || 'Topraksız Çilek Reçetesi' }}
                                </h3>
                                <span class="text-slate-400 text-[11px]">({{ fr.end_condition || fr.recipe?.duration_condition || '-' }})</span>
                            </div>
                            <div class="text-[11px] font-mono text-slate-500 dark:text-slate-400">
                                Hedef: pH {{ fr.recipe?.water_ph || '6.5' }} | EC {{ fr.recipe?.water_ec || '1.8' }} mS/cm
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-stretch">
                            <div v-for="tank in (fr.recipe?.tanks || [{ id: 1, tank_name: 'A Tankı (Solüsyon)', capacity_liters: 1000, items: [] }])" :key="'tank-card-' + tank.id" class="rounded-lg border border-slate-200 dark:border-slate-800 p-3 bg-slate-50/40 dark:bg-slate-950/20 flex flex-col justify-between h-full">
                                <div>
                                    <div class="flex items-center justify-between gap-2 pb-1.5 border-b border-slate-100 dark:border-slate-800">
                                        <div>
                                            <span class="font-bold text-xs text-slate-900 dark:text-slate-100">{{ tank.tank_name }}</span>
                                            <span class="text-[10px] text-slate-400 ml-1.5">({{ tank.capacity_liters }} L Hacim)</span>
                                        </div>
                                        <span class="px-1.5 py-0.5 bg-emerald-950/60 text-emerald-400 text-[9px] font-bold rounded border border-emerald-800/60">
                                            {{ (fr.tankLogs || []).filter((tl: any) => tl.tank_name === tank.tank_name || tl.fertilization_tank_id === tank.id).length }} Kez Hazırlandı
                                        </span>
                                    </div>

                                    <div class="space-y-0.5 pt-1.5">
                                        <div class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Reçete Karışımı:</div>
                                        <div v-if="tank.items?.length" class="divide-y divide-slate-100 dark:divide-slate-800/60 text-xs">
                                            <div v-for="(it, iIdx) in tank.items" :key="iIdx" class="py-1 flex items-center justify-between gap-2">
                                                <span class="text-slate-700 dark:text-slate-300 font-medium text-[11px] truncate">{{ it.product_name }}</span>
                                                <span class="font-bold text-slate-900 dark:text-slate-100 font-mono text-[11px] shrink-0">{{ it.quantity }} {{ it.unit }}</span>
                                            </div>
                                        </div>
                                        <div v-else class="text-xs text-slate-400 italic py-0.5">Solüsyon ürün karışımları girilmemiş.</div>
                                    </div>
                                </div>

                                <div class="pt-2 mt-auto space-y-2">
                                    <div class="pt-1.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-500">
                                        <span class="text-[10px] font-semibold text-slate-400 uppercase">Son Hazırlama:</span>
                                        <template v-if="(fr.tankLogs || []).filter((tl: any) => tl.tank_name === tank.tank_name || tl.fertilization_tank_id === tank.id).length">
                                            <span class="font-medium text-slate-700 dark:text-slate-300">
                                                {{ formatDisplayDate((fr.tankLogs || []).filter((tl: any) => tl.tank_name === tank.tank_name || tl.fertilization_tank_id === tank.id)[0]?.prepared_at) }}
                                                <span class="text-slate-400 font-normal">({{ (fr.tankLogs || []).filter((tl: any) => tl.tank_name === tank.tank_name || tl.fertilization_tank_id === tank.id)[0]?.prepared_by?.first_name || 'Personel' }})</span>
                                            </span>
                                        </template>
                                        <template v-else>
                                            <span class="text-slate-400 italic">Henüz hazırlanmadı</span>
                                        </template>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="openTankLogModal(fr, tank)" class="flex-1 py-1.5 px-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-lg transition text-center cursor-pointer">
                                            + Hazırla & Kaydet
                                        </button>
                                        <button type="button" @click="openTankPrintModal(fr, tank)" class="flex-1 py-1.5 px-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-semibold text-xs rounded-lg transition text-center cursor-pointer">
                                            Çalışan Çıktısı Al
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="p-3 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200 dark:border-slate-800 text-center text-xs text-slate-400">
                    Şu anda aktif uygulanan bir gübreleme reçetesi bulunmuyor. Yukarıdaki tablodan bir reçetenin durumundaki <strong>'Pasif (Aktif Yap)'</strong> butonuna tıklayarak başlatabilirsiniz.
                </div>
            </div>

            <div v-if="currentMod === 'sulama'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Sulama Programı & Vana Süre Matrisi</h2>
                        <p class="text-[11px] text-slate-500 dark:border-slate-400">Gübreli / Gübresiz sulama seçimi ve hızlı eşit vana süresi eşitleme</p>
                    </div>
                    <button @click="openFormModal('sulama')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <span class="text-sm leading-none">+</span> Yeni Sulama Programı Ekle
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Sulama Programı</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ irrigationSchedules.length }} Program</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Gübre Kademeli Sulama</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ irrigationSchedules.filter(i => i.is_fertilized).length }} Kayıt</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Boş Sulama (Sadece Su)</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ irrigationSchedules.filter(i => !i.is_fertilized).length }} Kayıt</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Aktif Sulama Vanaları</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">4 Vana Faal</span>
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-2.5">Tesis / Lokasyon</th>
                                <th class="p-2.5">Program Tarihi</th>
                                <th class="p-2.5">Başlangıç Saati</th>
                                <th class="p-2.5">Sulama Tipi</th>
                                <th class="p-2.5">Aktif Vanalar & Süreler</th>
                                <th class="p-2.5">Tank / Saha Notu</th>
                                <th class="p-2.5 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="is in irrigationSchedules" :key="is.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition">
                                <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100">
                                    {{ is.production_location?.name || is.location?.name || 'Silifke Çilek Üretim Serası' }}
                                </td>
                                <td class="p-2.5 font-mono text-slate-500">
                                    {{ is.schedule_date ? (is.schedule_date.includes('T') ? is.schedule_date.split('T')[0].split('-').reverse().join('.') : is.schedule_date) : '01.09.2026' }}
                                </td>
                                <td class="p-2.5 font-mono font-semibold text-slate-700 dark:text-slate-300">
                                    {{ is.start_time || '09:00' }} (Sıra {{ is.run_number || 1 }})
                                </td>
                                <td class="p-2.5">
                                    <span :class="is.is_fertilized ? 'bg-emerald-950/80 text-emerald-400 border-emerald-800/80' : 'bg-slate-800 text-slate-300 border-slate-700'" class="px-2 py-0.5 rounded text-[10px] font-bold border inline-block">
                                        {{ is.is_fertilized ? 'Gübreli Sulama' : 'Boş Sulama' }}
                                    </span>
                                </td>
                                <td class="p-2.5">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="v in (is.valves || [])" :key="v.id" class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded font-mono text-[10px]">
                                            {{ v.name || ('V-' + v.valve_number) }}: {{ v.duration_minutes }}dk
                                        </span>
                                        <span v-if="!is.valves || is.valves.length === 0" class="text-slate-400 italic text-[11px]">4 Vana x 5dk</span>
                                    </div>
                                </td>
                                <td class="p-2.5 text-slate-600 dark:text-slate-400 max-w-[220px] truncate">
                                    {{ is.tank_stage_note || is.notes || '-' }}
                                </td>
                                <td class="p-2.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openFormModal('sulama', is)" class="text-slate-700 dark:text-slate-200 font-bold hover:underline">
                                            Düzenle
                                        </button>
                                        <button @click="openGenericPrintModal('sulama', is)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                                            Çıktı Al
                                        </button>
                                        <button @click="deleteItem('agriculture.irrigation-schedules.destroy', is.id, 'sulama programı')" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">
                                            Sil
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentMod === 'ilaclama'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">İlaçlama Uygulamaları & İlaç Reçete Takibi</h2>
                        <p class="text-[11px] text-slate-500 dark:border-slate-400">İlaçlama formülasyonu, uygulama amacı, yapılan alan ve tank devamlılığı</p>
                    </div>
                    <button @click="openFormModal('spray_app')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <span class="text-sm leading-none">+</span> Yeni İlaçlama Uygulaması Ekle
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam İlaçlama Kaydı</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ sprayApplications.length }} Kayıt</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Tamamlanan Tanklar</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ sprayApplications.filter(s => s.is_tank_finished).length }} Tank</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Devam Eden Tanklar</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400 text-sm">{{ sprayApplications.filter(s => !s.is_tank_finished).length }} Tank</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">İlaç Formülasyon Çeşidi</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">{{ sprayRecipes.length }} Reçete</span>
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-2.5">Uygulama Tarihi & Kod</th>
                                <th class="p-2.5">İlaç Reçetesi</th>
                                <th class="p-2.5">Kullanım Amacı / Hedef</th>
                                <th class="p-2.5">Kaplanan Alan</th>
                                <th class="p-2.5">Uygulayan Personel</th>
                                <th class="p-2.5 text-center">Tank Durumu</th>
                                <th class="p-2.5 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="sa in sprayApplications" :key="sa.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition">
                                <td class="p-2.5 font-mono">
                                    <div class="font-bold text-slate-800 dark:text-slate-100">
                                        {{ sa.application_date ? (sa.application_date.includes('T') ? sa.application_date.split('T')[0].split('-').reverse().join('.') : sa.application_date) : '01.09.2026' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400">{{ sa.batch_code || 'ILAC-2026-01' }}</div>
                                </td>
                                <td class="p-2.5 font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ sa.recipe?.name || 'Kırmızı Örümcek & Mantar Koruma Reçetesi' }}
                                </td>
                                <td class="p-2.5 text-slate-700 dark:text-slate-300">
                                    {{ sa.purpose || 'Haşere ve Kırmızı Örümcek Mücadelesi' }}
                                </td>
                                <td class="p-2.5 font-semibold text-rose-600 dark:text-rose-400">
                                    {{ sa.covered_area_description || 'Sera 1 - 4. Tünele kadar' }}
                                </td>
                                <td class="p-2.5 text-slate-600 dark:text-slate-400">
                                    {{ sa.applied_by ? (sa.applied_by.first_name + ' ' + sa.applied_by.last_name) : 'Saha Operatörü' }}
                                </td>
                                <td class="p-2.5 text-center">
                                    <span :class="sa.is_tank_finished ? 'bg-emerald-950/80 text-emerald-400 border-emerald-800/80' : 'bg-amber-950/80 text-amber-400 border-amber-800/80'" class="px-2 py-0.5 rounded text-[10px] font-bold border inline-block">
                                        {{ sa.is_tank_finished ? 'Tank Tamamlandı' : 'Ertesi Gün Devam' }}
                                    </span>
                                </td>
                                <td class="p-2.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openFormModal('spray_app', sa)" class="text-slate-700 dark:text-slate-200 font-bold hover:underline">
                                            Düzenle
                                        </button>
                                        <button @click="openGenericPrintModal('ilaclama', sa)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                                            Çıktı Al
                                        </button>
                                        <button @click="deleteItem('agriculture.spraying-applications.destroy', sa.id, 'ilaçlama')" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">
                                            Sil
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentMod === 'su_analizleri'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Su Analiz Raporları</h2>
                        <p class="text-[11px] text-slate-500 dark:border-slate-400">Su kaynaklarının pH, EC ve laboratuvar kimyasal analiz takibi</p>
                    </div>
                    <button @click="openFormModal('water_analysis')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <span class="text-sm leading-none">+</span> Yeni Su Analizi Gir
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Su Analiz Raporları</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ waterAnalysisLogs.length }} Rapor</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Ortalama pH Değeri</span>
                        <span class="font-bold font-mono text-indigo-600 dark:text-indigo-400 text-sm">6.60 pH</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Ortalama EC İletkenlik</span>
                        <span class="font-bold font-mono text-slate-700 dark:text-slate-300 text-sm">1.40 mS/cm</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Laboratuvar Uygunluğu</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">%100 Uygun</span>
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-2.5">Su Kaynağı</th>
                                <th class="p-2.5">Analiz Tarihi</th>
                                <th class="p-2.5">pH Seviyesi</th>
                                <th class="p-2.5">EC İletkenlik</th>
                                <th class="p-2.5">Laboratuvar Notu</th>
                                <th class="p-2.5 text-center">Uygunluk Durumu</th>
                                <th class="p-2.5 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="wa in waterAnalysisLogs" :key="wa.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition">
                                <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100">
                                    {{ wa.water_source?.name || 'Ana Derin Kuyu Suyu' }}
                                </td>
                                <td class="p-2.5 text-slate-500 font-mono">
                                    {{ wa.analysis_date ? (wa.analysis_date.includes('T') ? wa.analysis_date.split('T')[0].split('-').reverse().join('.') : wa.analysis_date) : '01.09.2026' }}
                                </td>
                                <td class="p-2.5 font-bold text-rose-600 dark:text-rose-400 font-mono">pH: {{ wa.ph_level }}</td>
                                <td class="p-2.5 font-bold text-cyan-600 dark:text-cyan-400 font-mono">EC: {{ wa.ec_level }} mS/cm</td>
                                <td class="p-2.5 text-slate-600 dark:text-slate-400 max-w-[280px] truncate">
                                    {{ wa.notes || 'Laboratuvar rutin analiz sonucu tarımsal sulamaya uygundur.' }}
                                </td>
                                <td class="p-2.5 text-center">
                                    <span class="bg-emerald-950/80 text-emerald-400 border border-emerald-800/80 px-2 py-0.5 rounded text-[10px] font-bold inline-block">
                                        Sulamaya Uygun
                                    </span>
                                </td>
                                <td class="p-2.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openFormModal('water_analysis', wa)" class="text-slate-700 dark:text-slate-200 font-bold hover:underline">
                                            Düzenle
                                        </button>
                                        <button @click="openGenericPrintModal('su_analizi', wa)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                                            Çıktı Al
                                        </button>
                                        <button @click="deleteItem('agriculture.water-analyses.destroy', wa.id, 'su analizi')" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">
                                            Sil
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentMod === 'kaynak_suyu_kontrol'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Kaynak Suyu, Depo & Filtre Kontrolleri</h2>
                        <p class="text-[11px] text-slate-500 dark:border-slate-400">Pompa durumu, filtre temizlik periyodu uyarısı, klor ve ham su deposu seviyeleri</p>
                    </div>
                    <button @click="openFormModal('raw_water')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <span class="text-sm leading-none">+</span> Günlük Kaynak Suyu Kontrolü Gir
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Kaynak Suyu Kontrolleri</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ rawWaterControls.length }} Kayıt</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Faal Derin Kuyu Pompaları</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ rawWaterControls.filter(r => r.pump_status === 'open').length }} Pompa Açık</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Temizlenen Filtreler</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ rawWaterControls.filter(r => r.is_filter_cleaned).length }} Filtre Onaylı</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Klor Dozaj Modu</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">Otomatik Dozaj</span>
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-2.5">Kaynak / Pompa Adı</th>
                                <th class="p-2.5">Kontrol Tarihi</th>
                                <th class="p-2.5">Ham Su Deposu</th>
                                <th class="p-2.5">Klor Tankı</th>
                                <th class="p-2.5">Dozaj Pompası</th>
                                <th class="p-2.5">Ölçülen Değerler</th>
                                <th class="p-2.5 text-center">Filtre Durumu</th>
                                <th class="p-2.5 text-center">Pompa Durumu</th>
                                <th class="p-2.5 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="rw in rawWaterControls" :key="rw.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition">
                                <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100">
                                    {{ rw.water_source?.name || 'Ana Kuyu Suyu Kaynağı' }}
                                </td>
                                <td class="p-2.5 text-slate-500 font-mono">
                                    {{ rw.control_date ? (rw.control_date.includes('T') ? rw.control_date.split('T')[0].split('-').reverse().join('.') : rw.control_date) : '01.09.2026' }}
                                </td>
                                <td class="p-2.5 font-semibold text-slate-700 dark:text-slate-300">
                                    {{ rw.water_tank_level || 'Tam Dolu (%100)' }}
                                </td>
                                <td class="p-2.5 text-slate-700 dark:text-slate-300">
                                    {{ rw.chlorine_tank_level || 'Seviye Yeterli' }}
                                </td>
                                <td class="p-2.5 font-semibold text-slate-700 dark:text-slate-300">
                                    {{ rw.dosing_pump_mode === 'auto' ? 'Otomatik Dozaj' : 'Manuel Mod' }}
                                </td>
                                <td class="p-2.5 font-mono text-slate-800 dark:text-slate-200">
                                    EC: {{ rw.ec_val || '1.2' }} | pH: {{ rw.ph_val || '6.8' }}
                                </td>
                                <td class="p-2.5 text-center">
                                    <span :class="rw.is_filter_cleaned ? 'bg-emerald-950/80 text-emerald-400 border-emerald-800/80' : 'bg-amber-950/80 text-amber-400 border-amber-800/80'" class="px-2 py-0.5 rounded text-[10px] font-bold border inline-block">
                                        {{ rw.is_filter_cleaned ? 'Filtre Temizlendi' : 'Filtre Temizliği Bekliyor' }}
                                    </span>
                                </td>
                                <td class="p-2.5 text-center">
                                    <span :class="rw.pump_status === 'open' ? 'bg-emerald-950/80 text-emerald-400 border-emerald-800/80' : 'bg-rose-950/80 text-rose-400 border-rose-800/80'" class="px-2 py-0.5 rounded text-[10px] font-bold border inline-block">
                                        {{ rw.pump_status === 'open' ? 'Açık (Faal)' : 'Kapalı / Arızalı' }}
                                    </span>
                                </td>
                                <td class="p-2.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openFormModal('raw_water', rw)" class="text-slate-700 dark:text-slate-200 font-bold hover:underline">
                                            Düzenle
                                        </button>
                                        <button @click="openGenericPrintModal('kaynak_suyu_kontrol', rw)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                                            Çıktı Al
                                        </button>
                                        <button @click="deleteItem('agriculture.raw-water-controls.destroy', rw.id, 'kaynak suyu kontrol')" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">
                                            Sil
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentMod === 'aritma_suyu_kontrol'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Arıtma Suyu & Barometre Fark Basınç Takibi</h2>
                        <p class="text-[11px] text-slate-500 dark:border-slate-400">Giriş/Çıkış basınç farkı (&Delta;P) ve otomatik filtre tıkanıklık uyarısı</p>
                    </div>
                    <button @click="openFormModal('purification')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <span class="text-sm leading-none">+</span> Barometre Basınç Kontrolü Gir
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Arıtma Ünite Kontrolleri</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ purificationControls.length }} Kontrol</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Basınç Farkı Normal</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ purificationControls.filter(p => !p.has_warning).length }} Ünite Normal</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Filtre Uyarıları</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ purificationControls.filter(p => p.has_warning).length }} Uyarı</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Max Eşik Barı (&Delta;P)</span>
                        <span class="font-bold font-mono text-slate-700 dark:text-slate-300 text-sm">1.50 bar</span>
                    </div>
                </div>

                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-2.5">Arıtma Ünitesi / Kuyu</th>
                                <th class="p-2.5">Kontrol Tarihi</th>
                                <th class="p-2.5 text-right">Giriş Basıncı</th>
                                <th class="p-2.5 text-right">Çıkış Basıncı</th>
                                <th class="p-2.5 text-right">Basınç Farkı (&Delta;P)</th>
                                <th class="p-2.5 text-right">Max Eşik Limit</th>
                                <th class="p-2.5 text-center">Filtre & Basınç Durumu</th>
                                <th class="p-2.5 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="pc in purificationControls" :key="pc.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition">
                                <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100">
                                    {{ pc.water_source?.name || 'Silifke 1 No\'lu Derin Kuyu Sondaj Pompası' }}
                                </td>
                                <td class="p-2.5 text-slate-500 font-mono">
                                    {{ pc.control_date ? (pc.control_date.includes('T') ? pc.control_date.split('T')[0].split('-').reverse().join('.') : pc.control_date) : '20.08.2026' }}
                                </td>
                                <td class="p-2.5 text-right font-mono font-semibold">{{ pc.inlet_pressure_bar }} bar</td>
                                <td class="p-2.5 text-right font-mono font-semibold">{{ pc.outlet_pressure_bar }} bar</td>
                                <td class="p-2.5 text-right font-mono font-bold" :class="pc.has_warning ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'">
                                    {{ pc.delta_pressure_bar }} bar
                                </td>
                                <td class="p-2.5 text-right font-mono text-slate-500">{{ pc.max_threshold_bar || '1.50' }} bar</td>
                                <td class="p-2.5 text-center">
                                    <span :class="pc.has_warning ? 'bg-rose-950/80 text-rose-400 border-rose-800/80' : 'bg-emerald-950/80 text-emerald-400 border-emerald-800/80'" class="px-2 py-0.5 rounded text-[10px] font-bold border inline-block">
                                        {{ pc.has_warning ? 'UYARI: Filtre Doldu!' : 'Basınç Farkı Normal' }}
                                    </span>
                                </td>
                                <td class="p-2.5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="openFormModal('purification', pc)" class="text-slate-700 dark:text-slate-200 font-bold hover:underline">
                                            Düzenle
                                        </button>
                                        <button @click="openGenericPrintModal('aritma_suyu_kontrol', pc)" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                                            Çıktı Al
                                        </button>
                                        <button @click="deleteItem('agriculture.purification-controls.destroy', pc.id, 'arıtma kontrol')" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">
                                            Sil
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentMod === 'alinan_siparis'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4">
                <!-- Başlık ve Kurumsal Eylem Butonu -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">
                            Alınan Sipariş & Hasat Rezervasyon Yönetimi
                        </h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Müşteri sipariş kayıtları, açık rezervasyonlar, termin takibi ve irsaliye sevkiyat operasyonları</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="exportOrdersToExcel" title="Tüm filtrelenmiş siparişleri Excel CSV olarak indir" class="bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
                            Excel / CSV Aktar
                        </button>
                        <button @click="openFormModal('order')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs">
                            <span class="text-sm leading-none">+</span> Yeni Sipariş Girişi
                        </button>
                    </div>
                </div>

                <!-- Kurumsal Finans & Hacim Özet Şeridi -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Sipariş Portföyü</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">₺{{ customerOrders.reduce((sum, o) => sum + parseFloat(o.total_amount || 0), 0).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Açık / Aktif Siparişler</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ activeOrdersCount }} Sipariş</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Sevk Edilen & Kapanan</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ completedOrdersCount }} Sevk</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Müşteri / Cari Adedi</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">{{ new Set(customerOrders.map(o => o.trading_party_id)).size }} Firma</span>
                    </div>
                </div>

                <!-- Segmentasyon Filtreleri & Arama Çubuğu -->
                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-2 mb-3">
                    <div class="inline-flex p-0.5 bg-slate-100 dark:bg-slate-950 rounded-lg border border-slate-200 dark:border-slate-800 text-xs">
                        <button 
                            @click="orderFilterTab = 'active'" 
                            :class="orderFilterTab === 'active' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                            class="px-3 py-1 rounded-md transition text-xs flex items-center gap-1.5"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Aktif / Açık</span>
                            <span class="text-[10px] opacity-75 font-mono">({{ activeOrdersCount }})</span>
                        </button>
                        <button 
                            @click="orderFilterTab = 'completed'" 
                            :class="orderFilterTab === 'completed' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                            class="px-3 py-1 rounded-md transition text-xs flex items-center gap-1.5"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            <span>Sevk Edilen</span>
                            <span class="text-[10px] opacity-75 font-mono">({{ completedOrdersCount }})</span>
                        </button>
                        <button 
                            @click="orderFilterTab = 'cancelled'" 
                            :class="orderFilterTab === 'cancelled' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                            class="px-3 py-1 rounded-md transition text-xs flex items-center gap-1.5"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                            <span>İptal</span>
                            <span class="text-[10px] opacity-75 font-mono">({{ cancelledOrdersCount }})</span>
                        </button>
                        <button 
                            @click="orderFilterTab = 'all'" 
                            :class="orderFilterTab === 'all' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                            class="px-3 py-1 rounded-md transition text-xs flex items-center gap-1.5"
                        >
                            <span>Tümü</span>
                            <span class="text-[10px] opacity-75 font-mono">({{ customerOrders.length }})</span>
                        </button>
                    </div>

                    <div class="relative w-full sm:w-64">
                        <input 
                            v-model="orderSearchQuery" 
                            type="text" 
                            placeholder="Cari firma, kod veya ürün filtrele..." 
                            class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2.5 py-1 text-xs focus:ring-1 focus:ring-slate-500 focus:outline-none"
                        />
                    </div>
                </div>

                <!-- Kurumsal ERP Tablosu -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-lg overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-100/80 dark:bg-slate-800/70 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                            <tr>
                                <th class="p-2.5">Sipariş No & Tarih</th>
                                <th class="p-2.5">Cari / Müşteri Bilgisi</th>
                                <th class="p-2.5">Termin / İletişim</th>
                                <th class="p-2.5">Sipariş Kalemleri (Ürün / Kasa / Miktar)</th>
                                <th class="p-2.5 text-right">Tutar</th>
                                <th class="p-2.5 text-center">Durum</th>
                                <th class="p-2.5 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            <tr v-for="o in filteredCustomerOrders" :key="o.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="p-2.5 align-top whitespace-nowrap">
                                    <div class="font-mono font-bold text-slate-900 dark:text-slate-100">#ORD-{{ String(o.id).padStart(4, '0') }}</div>
                                    <div class="text-[10px] text-slate-500">{{ formatDisplayDate(o.order_date) }}</div>
                                </td>
                                <td class="p-2.5 align-top">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ o.trading_party?.name }}</div>
                                    <div v-if="o.trading_party?.dia_cari_code" class="text-[10px] font-mono text-slate-500">
                                        Kod: {{ o.trading_party?.dia_cari_code }}
                                    </div>
                                </td>
                                <td class="p-2.5 align-top whitespace-nowrap">
                                    <div v-if="o.requested_delivery_date" class="text-slate-700 dark:text-slate-300">
                                        Termin: <strong>{{ formatDisplayDate(o.requested_delivery_date) }}</strong>
                                    </div>
                                    <div v-else class="text-slate-400 text-[10px]">Termin Belirtilmedi</div>
                                    <div v-if="o.contact_person" class="text-[10px] text-slate-500">Yetkili: {{ o.contact_person }}</div>
                                </td>
                                <td class="p-2.5 align-top min-w-[280px]">
                                    <div v-if="o.items && o.items.length > 0" class="space-y-1">
                                        <div v-for="item in o.items" :key="item.id" class="text-[11px] flex items-center justify-between gap-2 bg-slate-50 dark:bg-slate-950 p-1.5 rounded border border-slate-200/60 dark:border-slate-800/60">
                                            <div>
                                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ item.product?.name }}</span>
                                                <span v-if="item.packaging" class="text-[10px] text-slate-500 ml-1">({{ item.packaging?.name }})</span>
                                            </div>
                                            <div class="font-mono text-slate-600 dark:text-slate-400 shrink-0">
                                                {{ item.quantity }} kg x ₺{{ item.unit_price }}
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-[10px] text-slate-400 italic">Kalem detayı girilmemiş</div>
                                    
                                    <!-- Kısmi Sevkiyat & Kalan Miktar İlerleme Çubuğu -->
                                    <div v-if="getOrderTotalQty(o) > 0" class="mt-1.5 p-1.5 bg-slate-100/60 dark:bg-slate-950/80 rounded border border-slate-200/60 dark:border-slate-800/60 text-[10px]">
                                        <div class="flex justify-between items-center text-slate-600 dark:text-slate-400 font-mono mb-1">
                                            <span>Sipariş: <strong>{{ getOrderTotalQty(o) }} kg</strong></span>
                                            <span>Sevk: <strong class="text-indigo-600">{{ getOrderShippedQty(o) }} kg</strong></span>
                                            <span>Kalan: <strong class="text-emerald-600">{{ getOrderRemainingQty(o) }} kg</strong></span>
                                        </div>
                                        <div class="w-full bg-slate-200 dark:bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                            <div 
                                                class="h-full transition-all duration-300"
                                                :class="getOrderProgressPercent(o) >= 100 ? 'bg-emerald-500' : 'bg-indigo-500'"
                                                :style="{ width: getOrderProgressPercent(o) + '%' }"
                                            ></div>
                                        </div>
                                    </div>

                                    <div v-if="o.notes" class="mt-1 text-[10px] text-slate-500 italic">Not: {{ o.notes }}</div>
                                </td>
                                <td class="p-2.5 align-top text-right whitespace-nowrap">
                                    <span class="font-mono font-bold text-slate-900 dark:text-slate-100 text-xs">
                                        ₺{{ parseFloat(o.total_amount || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                                    </span>
                                </td>
                                <td class="p-2.5 align-top text-center whitespace-nowrap">
                                    <span v-if="o.status === 'shipped' || (getOrderTotalQty(o) > 0 && getOrderRemainingQty(o) === 0)" class="inline-block bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                        Sevk Edildi (%100)
                                    </span>
                                    <span v-else-if="getOrderShippedQty(o) > 0" class="inline-block bg-cyan-50 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300 border border-cyan-200 dark:border-cyan-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                        Kısmi Sevk (%{{ getOrderProgressPercent(o) }})
                                    </span>
                                    <span v-else-if="o.status === 'cancelled'" class="inline-block bg-rose-50 dark:bg-rose-950 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                        İptal Edildi
                                    </span>
                                    <span v-else-if="o.status === 'confirmed'" class="inline-block bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                        Onaylı / Hazır
                                    </span>
                                    <span v-else class="inline-block bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                        Beklemede
                                    </span>
                                </td>
                                <td class="p-2.5 align-top text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button 
                                            v-if="o.status === 'pending' || !o.status" 
                                            @click="updateCustomerOrderStatus(o.id, 'confirmed')" 
                                            title="Siparişi onayla ve hazırlık aşamasına al"
                                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded-md text-[11px] font-bold shadow-xs transition"
                                        >
                                            Onayla
                                        </button>
                                        <button 
                                            v-if="o.status !== 'cancelled' && getOrderRemainingQty(o) > 0" 
                                            @click="openShipmentFromOrder(o)" 
                                            title="Siparişi otomatik sevkiyata aktar ve irsaliye hazırla"
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-2.5 py-1 rounded-md text-[11px] font-bold shadow-xs transition flex items-center gap-1"
                                        >
                                            Sevk Et
                                        </button>
                                        <button 
                                            @click="openEditOrderModal(o)" 
                                            title="Siparişi ve kalemleri düzenle"
                                            class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 px-2.5 py-1 rounded-md text-[11px] font-semibold transition"
                                        >
                                            Düzenle
                                        </button>
                                        <button 
                                            v-if="o.status !== 'cancelled'" 
                                            @click="updateCustomerOrderStatus(o.id, 'cancelled')" 
                                            title="Siparişi iptal et"
                                            class="bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 px-2.5 py-1 rounded-md text-[11px] font-semibold transition"
                                        >
                                            İptal
                                        </button>
                                        <button 
                                            v-if="o.status === 'cancelled'" 
                                            @click="updateCustomerOrderStatus(o.id, 'confirmed')" 
                                            title="Tekrar aktife al"
                                            class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 px-2.5 py-1 rounded-md text-[11px] font-semibold transition"
                                        >
                                            Aktif Et
                                        </button>
                                        <button 
                                            @click="openOrderPrintModal(o)" 
                                            title="Resmi Sipariş & Rezervasyon Fişi Yazdır"
                                            class="bg-white hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 px-2.5 py-1 rounded-md text-[11px] font-semibold transition"
                                        >
                                            Yazdır
                                        </button>
                                        <button 
                                            @click="deleteItem('agriculture.customer-orders.destroy', o.id, 'sipariş')" 
                                            title="Siparişi kalıcı olarak sil"
                                            class="bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 px-2.5 py-1 rounded-md text-[11px] font-semibold transition"
                                        >
                                            Sil
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredCustomerOrders.length === 0">
                                <td colspan="7" class="p-6 text-center text-slate-400 text-xs">
                                    Filtrelere uygun sipariş kaydı bulunmamaktadır.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentMod === 'sevkiyat_teslimat'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4">
                <!-- Başlık ve Kurumsal Eylem Butonu -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">
                            Sevkiyat & İrsaliyeli Teslimat Yönetimi
                        </h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Araç plaka, şoför, teslimat şekli, irsaliye entegrasyonu ve resmi sevk belgeleri</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="exportShipmentsToExcel" title="Tüm filtrelenmiş sevkiyatları Excel CSV olarak indir" class="bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs">
                            Excel / CSV Aktar
                        </button>
                        <button @click="openFormModal('shipment')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs">
                            <span class="text-sm leading-none">+</span> Yeni Sevkiyat Oluştur
                        </button>
                    </div>
                </div>

                <!-- Kurumsal Finans & Hacim Özet Şeridi -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Sevk Edilen Hacim</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ totalShipmentQuantity.toLocaleString('tr-TR') }} kg</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam İrsaliye Tutarı</span>
                        <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400 text-sm">₺{{ totalShipmentRevenue.toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Yoldaki Araçlar / Sevk</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ onTheWayShipmentsCount }} Araç</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Teslim Edilenler</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">{{ deliveredShipmentsCount }} Sevkiyat</span>
                    </div>
                </div>

                <!-- Segmentasyon Filtreleri & Arama Çubuğu -->
                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-2 mb-3">
                    <div class="inline-flex p-0.5 bg-slate-100 dark:bg-slate-950 rounded-lg border border-slate-200 dark:border-slate-800 text-xs">
                        <button 
                            @click="shipmentFilterTab = 'all'" 
                            :class="shipmentFilterTab === 'all' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                            class="px-3 py-1 rounded-md transition text-xs flex items-center gap-1.5"
                        >
                            <span>Tüm Sevkiyatlar</span>
                            <span class="text-[10px] opacity-75 font-mono">({{ shipments.length }})</span>
                        </button>
                        <button 
                            @click="shipmentFilterTab = 'on_the_way'" 
                            :class="shipmentFilterTab === 'on_the_way' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                            class="px-3 py-1 rounded-md transition text-xs flex items-center gap-1.5"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            <span>Yolda Olanlar</span>
                            <span class="text-[10px] opacity-75 font-mono">({{ onTheWayShipmentsCount }})</span>
                        </button>
                        <button 
                            @click="shipmentFilterTab = 'delivered'" 
                            :class="shipmentFilterTab === 'delivered' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900'"
                            class="px-3 py-1 rounded-md transition text-xs flex items-center gap-1.5"
                        >
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            <span>Teslim Edilenler</span>
                            <span class="text-[10px] opacity-75 font-mono">({{ deliveredShipmentsCount }})</span>
                        </button>
                    </div>

                    <div class="relative w-full sm:w-64">
                        <input 
                            v-model="shipmentSearchQuery" 
                            type="text" 
                            placeholder="Plaka, şoför, cari veya irsaliye ara..." 
                            class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2.5 py-1 text-xs focus:ring-1 focus:ring-slate-500 focus:outline-none"
                        />
                    </div>
                </div>

                <!-- Kurumsal Sevkiyat Tablosu -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-lg overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-100/80 dark:bg-slate-800/70 border-b border-slate-200 dark:border-slate-800 text-[11px] font-semibold text-slate-600 dark:text-slate-300">
                            <tr>
                                <th class="p-2.5">İrsaliye No & Tarih</th>
                                <th class="p-2.5">Alıcı Cari (Müşteri)</th>
                                <th class="p-2.5">Araç & Nakliye Bilgisi</th>
                                <th class="p-2.5">Sevk Edilen Ürün & Ambalaj</th>
                                <th class="p-2.5 text-right">Miktar & Tutar</th>
                                <th class="p-2.5 text-center">Durum</th>
                                <th class="p-2.5 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            <tr v-for="s in filteredShipments" :key="s.id" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition">
                                <td class="p-2.5 align-top whitespace-nowrap">
                                    <div class="font-mono font-bold text-slate-900 dark:text-slate-100">{{ s.dia_waybill_code || ('IRS-2026-' + s.id) }}</div>
                                    <div class="text-[10px] text-slate-500">{{ formatDisplayDate(s.shipment_date) }}</div>
                                </td>
                                <td class="p-2.5 align-top">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ s.trading_party?.name }}</div>
                                    <div v-if="s.trading_party?.dia_cari_code" class="text-[10px] font-mono text-slate-500">
                                        Kod: {{ s.trading_party?.dia_cari_code }}
                                    </div>
                                    <div v-if="s.order_id || s.customer_order_id" class="text-[10px] text-indigo-600">
                                        Bağlı Sipariş: #ORD-{{ String(s.customer_order_id || s.order_id).padStart(4, '0') }}
                                    </div>
                                </td>
                                <td class="p-2.5 align-top whitespace-nowrap">
                                    <div class="font-mono font-bold text-slate-900 dark:text-slate-100">{{ s.vehicle_plate || 'Plaka Belirtilmedi' }}</div>
                                    <div class="text-[10px] text-slate-600 dark:text-slate-400">Şoför: {{ s.driver_name || '-' }}</div>
                                    <div v-if="s.driver_phone" class="text-[10px] text-slate-500 font-mono">Tel: {{ s.driver_phone }}</div>
                                </td>
                                <td class="p-2.5 align-top">
                                    <div class="font-semibold text-slate-800 dark:text-slate-200">{{ s.product?.name }}</div>
                                    <div class="text-[10px] text-slate-500">
                                        Ambalaj: {{ s.packaging?.name || 'Standart Kasa' }}
                                        <span v-if="s.delivery_type" class="ml-1 text-slate-400">({{ s.delivery_type?.name }})</span>
                                    </div>
                                    <div v-if="s.notes" class="mt-0.5 text-[10px] text-slate-500 italic">Not: {{ s.notes }}</div>
                                </td>
                                <td class="p-2.5 align-top text-right whitespace-nowrap">
                                    <div class="font-bold text-slate-900 dark:text-slate-100">{{ parseFloat(s.quantity).toLocaleString('tr-TR') }} kg</div>
                                    <div class="font-mono text-[11px] text-slate-500">₺{{ s.unit_price }} / kg</div>
                                    <div class="font-mono font-bold text-emerald-600 text-xs mt-0.5">
                                        ₺{{ (parseFloat(s.quantity) * parseFloat(s.unit_price || 0)).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                                    </div>
                                </td>
                                <td class="p-2.5 align-top text-center whitespace-nowrap">
                                    <span v-if="s.status === 'delivered'" class="inline-block bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                        Teslim Edildi
                                    </span>
                                    <span v-else class="inline-block bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 text-[10px] font-semibold px-2 py-0.5 rounded">
                                        Yolda
                                    </span>
                                </td>
                                <td class="p-2.5 align-top text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button 
                                            v-if="s.status === 'on_the_way'" 
                                            @click="updateShipmentDeliveryStatus(s.id, 'delivered')" 
                                            title="Teslimat tamamlandı olarak işaretle"
                                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded-md text-[11px] font-bold shadow-xs transition"
                                        >
                                            Teslim Edildi
                                        </button>
                                        <button 
                                            v-else 
                                            @click="updateShipmentDeliveryStatus(s.id, 'on_the_way')" 
                                            title="Yolda durumuna geri al"
                                            class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-700 px-2.5 py-1 rounded-md text-[11px] font-semibold transition"
                                        >
                                            Yolda Yap
                                        </button>
                                        <button 
                                            @click="openWaybillPrintModal(s)" 
                                            title="Resmi Sevk İrsaliyesi & Taşıma Belgesi Çıktısı Al"
                                            class="bg-white hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 px-2.5 py-1 rounded-md text-[11px] font-semibold transition"
                                        >
                                            İrsaliye Yazdır
                                        </button>
                                        <button 
                                            @click="deleteItem('agriculture.shipment-deliveries.destroy', s.id, 'sevkiyat')" 
                                            title="Sevkiyatı kalıcı olarak sil"
                                            class="bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 px-2.5 py-1 rounded-md text-[11px] font-semibold transition"
                                        >
                                            Sil
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredShipments.length === 0">
                                <td colspan="7" class="p-6 text-center text-slate-400 text-xs">
                                    Filtrelere uygun sevkiyat kaydı bulunmamaktadır.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentMod === 'gunluk_isci_formu'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">
                            Günlük İşçi & Hasat Kayıtları
                        </h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Saha hasatları, çavuş puantajları ve hakediş bordroları</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="exportDailyWorkSheetsToExcel" title="Tüm puantaj kayıtlarını Excel CSV olarak indir" class="bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:hover:bg-emerald-900 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800 px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                            Excel / CSV Aktar
                        </button>
                        <button @click="openFormModal('daily_sheet')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                            <span class="text-sm leading-none">+</span> Yeni Form Ekle
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 p-2.5 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Puantaj Kaydı</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ dailyWorkSheets.length }} Form</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Onaylanan Kayıtlar</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ dailyWorkSheets.filter(d => d.status === 'approved').length }} Onaylı</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Onay Bekleyenler</span>
                        <span class="font-bold text-amber-600 dark:text-amber-400 text-sm">{{ dailyWorkSheets.filter(d => d.status === 'pending' || !d.status).length }} Beklemede</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam İşçi Hakedişi</span>
                        <span class="font-bold font-mono text-indigo-600 dark:text-indigo-400 text-sm">₺{{ dailyWorkSheets.reduce((sum, d) => sum + (d.crew_leaders?.reduce((s: number, c: any) => s + Number(c.calculated_wage_total || 0), 0) || 0), 0).toLocaleString('tr-TR') }}</span>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-stretch sm:items-center gap-2 mb-2">
                    <div class="flex items-center gap-2">
                        <select v-model="selectedLocationFilter" class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2.5 py-1 text-xs text-slate-700 dark:text-slate-300 font-semibold focus:outline-none">
                            <option value="all">Tüm Tesisler</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                        </select>
                    </div>

                    <div class="relative w-full sm:w-64">
                        <input v-model="searchDailyWorkSheet" type="text" placeholder="Çavuş, ürün veya tesis ara..." class="w-full bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-lg px-2.5 py-1 text-xs focus:ring-1 focus:ring-slate-500 focus:outline-none" />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 text-[11px] font-black uppercase tracking-wider text-slate-400 bg-slate-50/50 dark:bg-slate-950/40">
                                <th class="py-3.5 px-5 w-[250px]">Tarih & Tesis</th>
                                <th class="py-3.5 px-4 w-[180px]">Çavuş & İşgücü</th>
                                <th class="py-3.5 px-4 min-w-[220px]">Toplanan Hasat</th>
                                <th class="py-3.5 px-4 w-[160px] text-right">Hakediş / Ciro</th>
                                <th class="py-3.5 px-4 w-[130px] text-center">Durum</th>
                                <th class="py-3.5 px-5 w-[150px] text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            <tr 
                                v-for="dws in filteredDailyWorkSheets" 
                                :key="dws.id" 
                                class="hover:bg-slate-50/70 dark:hover:bg-slate-800/30 transition duration-150 group"
                            >
                                <td class="py-4 px-5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-black text-slate-800 dark:text-slate-100 text-xs tracking-tight">
                                            {{ formatSheetDate(dws.work_date) }}
                                        </span>
                                    </div>
                                    <div class="font-bold text-slate-700 dark:text-slate-300 text-xs mt-1">
                                        {{ dws.production_location?.name }}
                                    </div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 truncate max-w-[220px]">
                                        {{ dws.company?.name || 'SASA Tarım' }} • <span class="text-slate-500">{{ dws.submitted_by ? (dws.submitted_by.first_name + ' ' + dws.submitted_by.last_name) : 'Yönetici' }}</span>
                                    </div>
                                </td>

                                <td class="py-4 px-4 align-middle">
                                    <div v-for="cl in dws.crew_leaders" :key="cl.id">
                                        <div class="font-black text-slate-800 dark:text-slate-200 text-xs">
                                            {{ cl.crew_leader?.first_name }} {{ cl.crew_leader?.last_name }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                                            {{ cl.worker_count }} İşçi • {{ cl.car_count }} Araç <span v-if="cl.overtime_hours > 0" class="text-amber-500">• +{{ cl.overtime_hours }}s</span>
                                        </div>
                                    </div>
                                    <div v-if="!dws.crew_leaders || dws.crew_leaders.length === 0" class="text-slate-400 text-xs italic">
                                        İşçi kaydı yok
                                    </div>
                                </td>

                                <td class="py-4 px-4 align-middle">
                                    <div v-for="item in dws.harvest_items" :key="item.id">
                                        <div class="font-black text-slate-800 dark:text-slate-200 text-xs">
                                            {{ item.product?.name }} <span v-if="item.product_subtype" class="text-slate-400 font-normal">({{ item.product_subtype?.name }})</span>
                                        </div>
                                        <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">
                                            {{ Number(item.quantity).toLocaleString('tr-TR') }} {{ item.unit_symbol || 'kg' }} <span class="text-slate-400 font-normal">({{ item.package_count }} Kasa • ₺{{ item.unit_price }}/kg)</span>
                                        </div>
                                    </div>
                                    <div v-if="!dws.harvest_items || dws.harvest_items.length === 0" class="text-slate-400 text-xs italic">
                                        Hasat kaydı yok
                                    </div>
                                </td>

                                <td class="py-4 px-4 text-right align-middle">
                                    <div class="text-[11px] text-slate-400">
                                        Hakediş: <strong class="text-slate-700 dark:text-slate-300 font-extrabold">₺{{ Number(dws.crew_leaders?.reduce((sum: number, cl: any) => sum + Number(cl.calculated_wage_total || 0), 0) || 0).toLocaleString('tr-TR') }}</strong>
                                    </div>
                                    <div class="text-xs font-black text-slate-900 dark:text-slate-100 mt-0.5">
                                        Ciro: ₺{{ Number(dws.harvest_items?.reduce((sum: number, h: any) => sum + Number(h.total_revenue || 0), 0) || 0).toLocaleString('tr-TR') }}
                                    </div>
                                </td>

                                <td class="py-4 px-4 text-center align-middle">
                                    <span v-if="dws.status === 'approved'" class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 font-extrabold px-2.5 py-1 rounded-xl text-[11px] border border-emerald-200 dark:border-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Onaylandı
                                    </span>
                                    <span v-else-if="dws.status === 'rejected'" class="inline-flex items-center gap-1.5 bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 font-extrabold px-2.5 py-1 rounded-xl text-[11px] border border-rose-200 dark:border-rose-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Reddedildi
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 font-extrabold px-2.5 py-1 rounded-xl text-[11px] border border-amber-200 dark:border-amber-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span> Onay Bekliyor
                                    </span>
                                </td>

                                <td class="py-4 px-5 text-right align-middle">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button 
                                            @click="openDetailModal(dws)" 
                                            class="bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 px-2.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            <span>İncele</span>
                                        </button>

                                        <button 
                                            v-if="dws.crew_leaders?.[0]" 
                                            @click="openCrewPrintModal(dws, dws.crew_leaders[0])" 
                                            title="İmzalı Çavuş Hakediş Fişi"
                                            class="bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/60 dark:hover:bg-rose-900 text-rose-600 dark:text-rose-300 border border-rose-200 dark:border-rose-800 px-2.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span>Fiş</span>
                                        </button>
                                        <button
                                            @click="deleteItem('agriculture.daily-work-sheets.destroy', dws.id, 'günlük işçi formu')" 
                                            title="Formu kalıcı olarak sil"
                                            class="bg-rose-100 hover:bg-rose-200 dark:bg-rose-950 dark:hover:bg-rose-900 text-rose-700 dark:text-rose-300 border border-rose-300 dark:border-rose-800 px-2.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                        >
                                            <span>Sil</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="filteredDailyWorkSheets.length === 0">
                                <td colspan="6" class="p-12 text-center text-slate-400 text-xs">
                                    Aramanıza uygun kayıt bulunamadı.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentMod === 'isci_ve_cavuslar'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">İşçi & Çavuş Yönetimi</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Çavuş (Ekip Başı) ve onlara bağlı işçilerin (Ekip) tanımlamaları, yevmiye ve dia cari eşleşmeleri.</p>
                    </div>
                    <button @click="openFormModal('crew_leader')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <span class="text-sm leading-none">+</span> Yeni Çavuş Ekle
                    </button>
                </div>

                <!-- Çavuşlar Tablosu -->
                <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-2.5">Ad Soyad</th>
                                <th class="p-2.5">Geldiği Yer</th>
                                <th class="p-2.5">Yevmiye</th>
                                <th class="p-2.5">Çavuş Çarpanı</th>
                                <th class="p-2.5">Araç Şartı / Yol Ücreti</th>
                                <th class="p-2.5">Cari Kodu</th>
                                <th class="p-2.5 text-right">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <template v-for="cl in (crewLeaders || [])" :key="'cl-' + cl.id">
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition bg-slate-50/30">
                                    <td class="p-2.5 font-bold text-slate-800 dark:text-slate-100">
                                        {{ cl.first_name }} {{ cl.last_name }}
                                        <div class="text-[10px] text-slate-400 font-normal">Kayıtlı İşçi: {{ (cl.workers || []).length }}</div>
                                    </td>
                                    <td class="p-2.5 text-slate-700 dark:text-slate-300">{{ cl.origin_city || '-' }}</td>
                                    <td class="p-2.5 font-mono text-slate-700 dark:text-slate-300">₺{{ cl.daily_wage }}</td>
                                    <td class="p-2.5 font-mono text-slate-700 dark:text-slate-300">{{ cl.multiplier }}x <span class="text-[10px] text-slate-400">({{ cl.is_leader_fee_included ? 'Dahil' : 'Hariç' }})</span></td>
                                    <td class="p-2.5 text-slate-700 dark:text-slate-300">
                                        Min {{ cl.min_car_requirement }} Araç<br/>
                                        <span class="text-xs">₺{{ cl.travel_fee_per_car }} / Araç</span>
                                    </td>
                                    <td class="p-2.5 font-mono text-indigo-600 dark:text-indigo-400">{{ cl.dia_cari_code || 'Tanımsız' }}</td>
                                    <td class="p-2.5 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openFormModal('worker', {crew_leader_id: cl.id})" class="text-emerald-600 dark:text-emerald-400 font-bold hover:underline">
                                                İşçi Ekle
                                            </button>
                                            <button @click="openFormModal('crew_leader', cl)" class="text-slate-700 dark:text-slate-200 font-bold hover:underline">
                                                Düzenle
                                            </button>
                                            <button @click="deleteItem('crew-leaders.destroy', cl.id, 'çavuş')" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">
                                                Sil
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- İşçiler Alt Tablo -->
                                <tr v-if="(cl.workers || []).length > 0">
                                    <td colspan="7" class="p-0 border-0">
                                        <div class="pl-8 py-2 bg-slate-50/50 dark:bg-slate-900/50">
                                            <table class="w-full text-left text-[11px] border-collapse bg-white dark:bg-slate-950 rounded-lg shadow-sm border border-slate-200 dark:border-slate-800">
                                                <thead class="bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 uppercase font-semibold">
                                                    <tr>
                                                        <th class="p-2 rounded-tl-lg">İşçi Adı Soyadı</th>
                                                        <th class="p-2">TC Kimlik</th>
                                                        <th class="p-2">Performans (1-5)</th>
                                                        <th class="p-2">Durum</th>
                                                        <th class="p-2 text-right rounded-tr-lg">İşlemler</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                                    <tr v-for="w in cl.workers" :key="'w-' + w.id" class="hover:bg-slate-50 dark:hover:bg-slate-900 transition">
                                                        <td class="p-2 font-medium text-slate-700 dark:text-slate-200">{{ w.first_name }} {{ w.last_name }}</td>
                                                        <td class="p-2 font-mono text-slate-500">{{ w.identity_number || '-' }}</td>
                                                        <td class="p-2">
                                                            <div class="flex text-amber-400">
                                                                <span v-for="i in 5" :key="'s'+i">{{ i <= w.performance_rating ? '★' : '☆' }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="p-2">
                                                            <span :class="w.is_active ? 'text-emerald-600' : 'text-slate-400'" class="font-semibold">{{ w.is_active ? 'Aktif' : 'Pasif' }}</span>
                                                        </td>
                                                        <td class="p-2 text-right">
                                                            <div class="flex items-center justify-end gap-2">
                                                                <button @click="openFormModal('worker', w)" class="text-slate-600 hover:text-slate-900 dark:hover:text-slate-100 hover:underline">Düzenle</button>
                                                                <button @click="deleteItem('workers.destroy', w.id, 'işçi')" class="text-rose-500 hover:text-rose-700 hover:underline">Sil</button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentMod === 'piyasa_fiyatlari'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">Piyasa ve Hal Fiyatları Takibi</h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Ürün bazında günlük piyasa ve hal fiyatları</p>
                    </div>
                    <button @click="openFormModal('market_price')" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <span class="text-sm leading-none">+</span> Yeni Fiyat Kaydı Gir
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-3 p-2 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Fiyat Kaydı</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ (marketPrices || []).length }} Kayıt</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Çilek Hal Fiyatı</span>
                        <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400 text-sm">₺75,00 / kg</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Muz Hal Fiyatı</span>
                        <span class="font-bold font-mono text-indigo-600 dark:text-indigo-400 text-sm">₺55,00 / kg</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Güncellenme Tarihi</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">Bugün</span>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                    <table class="w-full text-left text-xs whitespace-nowrap">
                        <thead class="bg-slate-50 dark:bg-slate-950/50 text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-3 py-2 font-semibold">Tarih</th>
                                <th class="px-3 py-2 font-semibold">Ürün Adı</th>
                                <th class="px-3 py-2 font-semibold">Kaynak / Borsa</th>
                                <th class="px-3 py-2 font-semibold text-right">Fiyat</th>
                                <th class="px-3 py-2 font-semibold text-right w-24">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60">
                            <tr v-for="mp in [...(marketPrices || [])].sort((a,b) => { const d = new Date(b.price_date).getTime() - new Date(a.price_date).getTime(); return d === 0 ? b.id - a.id : d; })" :key="mp.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-900/50 transition-colors">
                                <td class="px-3 py-2">{{ formatDisplayDate(mp.price_date) }}</td>
                                <td class="px-3 py-2 font-bold text-slate-800 dark:text-slate-100">{{ mp.product?.name }}</td>
                                <td class="px-3 py-2 text-slate-500 dark:text-slate-400">{{ mp.source_name }}</td>
                                <td class="px-3 py-2 font-bold font-mono text-emerald-600 dark:text-emerald-400 text-right">₺{{ mp.unit_price }} / kg</td>
                                <td class="px-3 py-2 text-right">
                                    <div class="flex justify-end items-center gap-3">
                                        <button @click="openFormModal('market_price', mp)" class="text-teal-600 dark:text-teal-400 font-bold hover:underline">
                                            Düzenle
                                        </button>
                                        <button @click="deleteItem('agriculture.market-prices.destroy', mp.id, 'fiyat kaydı')" class="text-rose-600 dark:text-rose-400 font-bold hover:underline">
                                            Sil
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!marketPrices || marketPrices.length === 0">
                                <td colspan="5" class="px-3 py-4 text-center text-slate-500 dark:text-slate-400">Kayıt bulunamadı.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="currentMod === 'hasat_miktarlari'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">
                            Hasat Miktarları & Üretim Analiz Raporu
                        </h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Ürün bazında toplanan tonajlar, 1. ve 2. kantar tartımları, ambalaj dökümleri ve ciro analizleri</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="printPage()" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                            Raporu Yazdır
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 p-2.5 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Hasat Tonajı</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">{{ totalHarvestQuantity.toLocaleString('tr-TR') }} kg</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Tahmini Hasat Cirosu</span>
                        <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400 text-sm">₺{{ totalOrdersAmount.toLocaleString('tr-TR') }}</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Hasat Kalem Kaydı</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ dailyWorkSheets.reduce((sum, s) => sum + (s.harvest_items?.length || 0), 0) }} Kalem</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Tesis Üretim Çeşidi</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">Çilek & Muz</span>
                    </div>
                </div>

                <div class="p-3 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 space-y-2">
                    <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Ürün Hasat Kırılım Oranları</span>
                    <div class="space-y-2 text-xs">
                        <div>
                            <div class="flex justify-between font-bold text-[11px] mb-1">
                                <span class="text-slate-800 dark:text-slate-200">Çilek (San Andreas & Fortuna)</span>
                                <span class="text-rose-600 dark:text-rose-400">82% (59,578 kg)</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                                <div class="bg-rose-500 h-full rounded-full" style="width: 82%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between font-bold text-[11px] mb-1">
                                <span class="text-slate-800 dark:text-slate-200">Muz (Grand Naine Bodur Muz)</span>
                                <span class="text-amber-600 dark:text-amber-400">18% (13,079 kg)</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                                <div class="bg-amber-500 h-full rounded-full" style="width: 18%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100">Detaylı Hasat ve Kantar Kayıt Dökümü</h3>
                    <div class="overflow-x-auto border border-slate-200/80 dark:border-slate-800 rounded-xl">
                        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-400">
                            <thead class="bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                                <tr>
                                    <th class="p-3">Tarih</th>
                                    <th class="p-3">Tesis / Lokasyon</th>
                                    <th class="p-3">Ürün</th>
                                    <th class="p-3">Ambalaj / Kasa</th>
                                    <th class="p-3">Miktar (Kg)</th>
                                    <th class="p-3">Birim Fiyat (₺)</th>
                                    <th class="p-3">Toplam Tutar (₺)</th>
                                    <th class="p-3">Kantar Durumu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <template v-for="s in dailyWorkSheets" :key="s.id">
                                    <tr v-for="item in s.harvest_items" :key="item.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition">
                                        <td class="p-3 font-bold text-slate-800 dark:text-slate-200">
                                            {{ s.work_date ? new Date(s.work_date).toLocaleDateString('tr-TR') : 'Tarih Yok' }}
                                        </td>
                                        <td class="p-3 font-semibold">{{ s.production_location?.name || 'Tesis' }}</td>
                                        <td class="p-3 font-bold text-rose-600">{{ item.product?.name || 'Ürün' }}</td>
                                        <td class="p-3">{{ item.package_count || 0 }} {{ item.packaging?.name || 'Kasa' }}</td>
                                        <td class="p-3 font-black text-slate-800 dark:text-slate-100">{{ item.quantity }} kg</td>
                                        <td class="p-3">₺{{ item.unit_price }}</td>
                                        <td class="p-3 font-extrabold text-emerald-600">₺{{ (item.total_revenue || (item.quantity * item.unit_price)).toLocaleString('tr-TR') }}</td>
                                        <td class="p-3">
                                            <span v-if="item.is_merchant_weighed" class="bg-emerald-950/80 text-emerald-400 border border-emerald-800/80 px-2 py-0.5 rounded text-[10px] font-bold">
                                                2. Kantar Onaylı ({{ item.merchant_scale_1st_kg }} kg)
                                            </span>
                                            <span v-else class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-bold">
                                                Tesis Çıkış Kantarı
                                            </span>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div v-if="currentMod === 'stok_inceleme'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
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

            <div v-if="currentMod === 'isci_maliyet_analizi'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-xs p-4 space-y-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-slate-100 tracking-tight">
                            İşçi & Çavuş Kümülatif Maliyet Analiz Raporu
                        </h2>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">Çavuş bazında gruplanmış kümülatif hakediş dökümleri, yevmiyeler, araç/ulaşım giderleri ve kg başı maliyet</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="printPage()" class="bg-slate-900 hover:bg-slate-800 dark:bg-rose-600 dark:hover:bg-rose-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                            Raporu Yazdır
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 p-2.5 bg-slate-50 dark:bg-slate-950/60 rounded-lg border border-slate-200/80 dark:border-slate-800/80 text-xs">
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam İşçilik & Çavuş Hakedişi</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-slate-100 text-sm">₺{{ totalCrewWages.toLocaleString('tr-TR') }}</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Kg Başı İşçilik Maliyeti</span>
                        <span class="font-bold font-mono text-emerald-600 dark:text-emerald-400 text-sm">₺{{ (totalHarvestQuantity > 0 ? (totalCrewWages / totalHarvestQuantity) : 0).toFixed(2) }} / kg</span>
                    </div>
                    <div class="px-2 border-r border-slate-200 dark:border-slate-800">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Toplam Sahada Çalışan İşçi</span>
                        <span class="font-bold text-indigo-600 dark:text-indigo-400 text-sm">{{ dailyWorkSheets.reduce((sum, s) => sum + (s.crew_leaders?.reduce((cSum: number, cl: any) => cSum + parseInt(cl.worker_count || 0), 0) || 0), 0) }} Kişi</span>
                    </div>
                    <div class="px-2">
                        <span class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider block">Kayıtlı Çavuş Sayısı</span>
                        <span class="font-bold text-slate-700 dark:text-slate-300 text-sm">{{ (crewLeaders || []).length }} Çavuş</span>
                    </div>
                </div>

                <div class="space-y-3">
                    <h3 class="font-bold text-sm text-slate-800 dark:text-slate-100">Çavuş Bazında Kümülatif Hakediş ve Yevmiye Tablosu</h3>
                    <div class="overflow-x-auto border border-slate-200/80 dark:border-slate-800 rounded-xl">
                        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-400">
                            <thead class="bg-slate-100 dark:bg-slate-950 text-slate-700 dark:text-slate-300 font-bold uppercase text-[10px]">
                                <tr>
                                    <th class="p-3">ÇAVUŞ ADI</th>
                                    <th class="p-3">CARİ KODU</th>
                                    <th class="p-3">TOPLAM ÇALIŞTIĞI GÜN</th>
                                    <th class="p-3">GETİRDİĞİ TOPLAM İŞÇİ</th>
                                    <th class="p-3">SERVİS ARAÇ SAYISI</th>
                                    <th class="p-3">TOPLAM MESAİ (SAAT)</th>
                                    <th class="p-3">TOPLAM YEMEK BEDELİ (₺)</th>
                                    <th class="p-3">TOPLAM ULAŞIM BEDELİ (₺)</th>
                                    <th class="p-3">KÜMÜLATİF ÇAVUŞ HAKEDİŞİ (₺)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="cl in groupedCrewLeaderCosts" :key="cl.id" class="hover:bg-slate-50 dark:hover:bg-slate-950/60 transition">
                                    <td class="p-3 font-bold text-indigo-600 dark:text-indigo-400 text-sm flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                        <span>{{ cl.name }}</span>
                                    </td>
                                    <td class="p-3 font-semibold text-rose-600 dark:text-rose-400">{{ cl.code }}</td>
                                    <td class="p-3 font-bold text-slate-800 dark:text-slate-200">{{ cl.total_days }} Gün</td>
                                    <td class="p-3 font-bold text-slate-800 dark:text-slate-200">{{ cl.total_workers }} İşçi / Gün</td>
                                    <td class="p-3 text-slate-600 dark:text-slate-300">{{ cl.total_cars }} Araç</td>
                                    <td class="p-3 font-bold text-amber-600 dark:text-amber-400">{{ cl.total_overtime }} Saat</td>
                                    <td class="p-3 text-slate-600 dark:text-slate-300">₺{{ cl.total_meal_fee.toLocaleString('tr-TR') }}</td>
                                    <td class="p-3 text-slate-600 dark:text-slate-300">₺{{ cl.total_travel_fee.toLocaleString('tr-TR') }}</td>
                                    <td class="p-3 font-black text-emerald-600 dark:text-emerald-400 text-sm font-mono">₺{{ cl.total_wage.toLocaleString('tr-TR') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
            <div :class="['daily_sheet', 'order', 'order_edit', 'shipment', 'work_plan', 'spraying', 'sulama', 'raw_water'].includes(modalType) ? 'max-w-4xl' : 'max-w-xl'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full p-7 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                            {{ getModalTitle }}
                        </h3>
                        <p class="text-xs text-slate-500">
                            {{ modalType === 'work_plan' && workPlanForm.id ? 'Mevcut iş planının durumunu, atamasını veya sonuç notunu güncelleyebilirsiniz.' : 'Lütfen operasyonel alanları eksiksiz doldurunuz.' }}
                        </p>
                    </div>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 font-bold text-2xl leading-none">&times;</button>
                </div>

                <form v-if="modalType === 'work_plan'" @submit.prevent="submitWorkPlan" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">İş Başlığı / Görev Adı *</label>
                        <input v-model="workPlanForm.title" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs font-semibold" required placeholder="Örn: Silifke Tünel A Damlama Boruları ve Debi Testi" />
                    </div>

                    <!-- Görevin Güncel Durumu (Butonlu Seçim) -->
                    <div class="p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2">
                        <label class="block font-bold text-slate-800 dark:text-slate-200 text-xs">Görevin Güncel Durumu *</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            <button type="button" @click="workPlanForm.status = 'pending'" :class="workPlanForm.status === 'pending' ? 'bg-amber-600 text-white font-black ring-2 ring-amber-400' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-100'" class="py-2 px-3 rounded-xl text-xs font-bold transition text-center cursor-pointer">
                                Beklemede
                            </button>
                            <button type="button" @click="workPlanForm.status = 'in_progress'" :class="workPlanForm.status === 'in_progress' ? 'bg-indigo-600 text-white font-black ring-2 ring-indigo-400' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-100'" class="py-2 px-3 rounded-xl text-xs font-bold transition text-center cursor-pointer">
                                Devam Ediyor
                            </button>
                            <button type="button" @click="workPlanForm.status = 'completed'" :class="workPlanForm.status === 'completed' ? 'bg-emerald-600 text-white font-black ring-2 ring-emerald-400 shadow-sm' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-100'" class="py-2 px-3 rounded-xl text-xs font-bold transition text-center cursor-pointer">
                                ✓ Tamamlandı
                            </button>
                            <button type="button" @click="workPlanForm.status = 'cancelled'" :class="workPlanForm.status === 'cancelled' ? 'bg-rose-600 text-white font-black ring-2 ring-rose-400' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800 hover:bg-slate-100'" class="py-2 px-3 rounded-xl text-xs font-bold transition text-center cursor-pointer">
                                İptal Edildi
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold mb-1">Tesis / Saha Bölgesi</label>
                            <select v-model="workPlanForm.production_location_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs font-medium">
                                <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Yapılan İş Türü</label>
                            <select v-model="workPlanForm.job_type_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs font-medium">
                                <option :value="null">Genel Saha İşi</option>
                                <option v-for="jt in jobTypes" :key="jt.id" :value="jt.id">{{ jt.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Görevli / Sorumlu Personel</label>
                        <select v-model="workPlanForm.assigned_personnel_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs font-medium">
                            <option :value="null">Genel Atama / Tüm Ekip</option>
                            <option v-for="p in personnels" :key="p.id" :value="p.id">{{ p.first_name }} {{ p.last_name }} ({{ p.department || 'Personel' }})</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold mb-1">Planlama Başlangıç Tarihi *</label>
                            <input v-model="workPlanForm.plan_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Hedef Bitiş Tarihi</label>
                            <input v-model="workPlanForm.due_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Müdür Talimatı / Görev Açıklaması</label>
                        <textarea v-model="workPlanForm.description" rows="2" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs" placeholder="İşletme müdürü tarafından verilen çalışma talimatı ve detaylar..."></textarea>
                    </div>

                    <div class="p-3 bg-emerald-50/70 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-900/60 space-y-1.5">
                        <label class="block font-bold text-emerald-900 dark:text-emerald-300 text-xs">Süreç / Tamamlama Sonuç Notu</label>
                        <textarea v-model="workPlanForm.completion_notes" rows="2" class="w-full border rounded-xl p-2.5 bg-white dark:bg-slate-950 text-xs" placeholder="İş tamamlandığında veya süreç esnasında sahada elde edilen sonuçlar ve açıklamalar..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                        <button type="submit" :disabled="workPlanForm.processing" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-xs transition">
                            Güncellemeyi Kaydet
                        </button>
                    </div>
                </form>

                <form v-if="modalType === 'fert_run'" @submit.prevent="submitFertRun" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">Daha Önce Tanımlanmış Gübre Reçetesi</label>
                        <select v-model="fertRunForm.fertilization_recipe_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                            <option v-for="fr in fertRecipes" :key="fr.id" :value="fr.id">{{ fr.name }} (Hedef pH: {{ fr.water_ph }}, EC: {{ fr.water_ec }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Reçete Uygulama Durumu</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button" @click="fertRunForm.is_active = true"
                                :class="fertRunForm.is_active ? 'bg-emerald-600 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                                class="py-2 rounded-xl text-xs transition">
                                Aktif Uygulanan Reçete
                            </button>
                            <button type="button" @click="fertRunForm.is_active = false"
                                :class="!fertRunForm.is_active ? 'bg-slate-700 text-white font-bold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                                class="py-2 rounded-xl text-xs transition">
                                Pasif / Tamamlandı
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Reçete Başlangıç Tarihi</label>
                        <input v-model="fertRunForm.start_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1 text-rose-600 dark:text-rose-400">Reçete Bitiş Şartı (Spesifik bir tarih olamaz)</label>
                        <input v-model="fertRunForm.end_condition" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold" placeholder="Örn: Çiçeklenme başlayana kadar / İlk meyve tutumuna kadar" required />
                        <p class="text-[10px] text-slate-400 mt-1">Not: Gübreleme reçetesinin ne zaman biteceği spesifik bir tarih olarak değil, sözel bitki gelişimi şartı olarak ifade edilir.</p>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-xs transition">
                            {{ fertRunForm.id ? 'Değişiklikleri Kaydet' : 'Reçeteyi Başlat' }}
                        </button>
                    </div>
                </form>

                <form v-if="modalType === 'sulama'" @submit.prevent="submitIrrigation" class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block font-bold mb-1">Sulama Tarihi</label>
                            <input v-model="irrigationForm.schedule_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Sulama Sıra No</label>
                            <input v-model="irrigationForm.run_number" type="number" min="1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Başlangıç Saati</label>
                            <input v-model="irrigationForm.start_time" type="time" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Üretim Yeri / Sera Seçimi</label>
                            <select v-model="irrigationForm.production_location_id" @change="loadIrrigationValvesForLocation" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold" required>
                                <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="p-3.5 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input v-model="irrigationForm.is_fertilized" type="checkbox" class="rounded text-rose-600" />
                                <span class="font-black text-rose-600 dark:text-rose-400">Gübre Solüsyonu Dahil mi? (Boş bırakılırsa sadece su verilir)</span>
                            </label>

                            <div v-if="irrigationForm.is_fertilized" class="flex items-center gap-2">
                                <span class="text-[11px] font-bold text-slate-400">Aktif Reçete:</span>
                                <span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 font-extrabold rounded-lg border border-emerald-200 dark:border-emerald-800">
                                    {{ fertRuns.find((fr: any) => fr.is_active)?.recipe?.name || 'Topraksız Çilek Büyütme Reçetesi' }}
                                </span>
                            </div>
                        </div>

                        <div v-if="irrigationForm.is_fertilized" class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div>
                                <label class="block font-bold text-[11px] text-slate-500 mb-1">Tank Vana Kademe Seviyesi</label>
                                <input v-model="irrigationForm.tank_stage_note" type="text" placeholder="Örn: A Tankı 1. Kademe Solüsyon (%100 Dolu)" class="w-full border rounded-xl p-2 bg-white dark:bg-slate-900" />
                            </div>
                            <div>
                                <label class="block font-bold text-[11px] text-slate-500 mb-1">Saha Notları / Açıklama</label>
                                <input v-model="irrigationForm.notes" type="text" placeholder="Günün sulama notu..." class="w-full border rounded-xl p-2 bg-white dark:bg-slate-900" />
                            </div>
                        </div>
                    </div>

                    <div class="border border-slate-200 dark:border-slate-800 rounded-2xl p-4 bg-slate-50/50 dark:bg-slate-950/40 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-200 dark:border-slate-800">
                            <div>
                                <h4 class="font-black text-xs text-slate-800 dark:text-slate-100 uppercase tracking-wider">Sera & Vana Çalışma Süre Matrisi</h4>
                                <p class="text-[11px] text-slate-400 mt-0.5">Her vana için özel sulama/sisleme süresini dakika cinsinden giriniz</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-bold text-slate-400 hidden sm:inline">Eşit Süre Eşitle:</span>
                                <button type="button" @click="applyEqualDuration(5)" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950 dark:hover:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-bold rounded-lg border border-indigo-200 dark:border-indigo-800 text-[11px] transition cursor-pointer">
                                    5 dk
                                </button>
                                <button type="button" @click="applyEqualDuration(10)" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950 dark:hover:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-bold rounded-lg border border-indigo-200 dark:border-indigo-800 text-[11px] transition cursor-pointer">
                                    10 dk
                                </button>
                                <button type="button" @click="applyEqualDuration(15)" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950 dark:hover:bg-indigo-900 text-indigo-700 dark:text-indigo-300 font-bold rounded-lg border border-indigo-200 dark:border-indigo-800 text-[11px] transition cursor-pointer">
                                    15 dk
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            <div v-for="(v, idx) in irrigationForm.valves" :key="idx" class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 flex items-center justify-between gap-2 shadow-2xs">
                                <div>
                                    <div class="font-extrabold text-xs text-slate-800 dark:text-slate-100">{{ v.name }}</div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">Vana No: {{ v.valve_number }}</div>
                                </div>
                                <div class="flex items-center gap-1 shrink-0">
                                    <input v-model.number="v.duration_minutes" type="number" min="1" max="180" class="w-16 border rounded-lg p-1.5 text-center font-bold text-xs bg-slate-50 dark:bg-slate-950" required />
                                    <span class="text-[11px] font-bold text-slate-400">dk</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl font-bold">İptal</button>
                        <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-extrabold rounded-xl shadow-xs transition">Sulamayı Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'spray_app'" @submit.prevent="submitSprayApp" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">İlaç Reçetesi</label>
                        <select v-model="sprayAppForm.spraying_recipe_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                            <option v-for="sr in sprayRecipes" :key="sr.id" :value="sr.id">{{ sr.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Kullanım Amacı</label>
                        <input v-model="sprayAppForm.purpose" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Kaplanan Alan (örn: Sera 1 - 4. Tünel)</label>
                        <input v-model="sprayAppForm.covered_area_description" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Hazırlanan İlaç Bitti mi?</label>
                        <input v-model="sprayAppForm.is_tank_finished" type="checkbox" class="rounded text-rose-600" />
                        <span class="ml-2 font-bold">Tank Bitti (Bitmediyse ertesi gün devam eder)</span>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">İlaçlamayı Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'water_analysis'" @submit.prevent="submitWaterAnalysis" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">Su Kaynağı</label>
                        <select v-model="waterAnalysisForm.water_source_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                            <option v-for="ws in waterSources" :key="ws.id" :value="ws.id">{{ ws.name }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">pH Seviyesi</label>
                            <input v-model="waterAnalysisForm.ph_level" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">EC Seviyesi (mS/cm)</label>
                            <input v-model="waterAnalysisForm.ec_level" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Analiz Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'raw_water'" @submit.prevent="submitRawWater" class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold mb-1">Kontrol Tarihi</label>
                            <input v-model="rawWaterForm.control_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Su Kaynağı</label>
                            <select v-model="rawWaterForm.water_source_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold" required>
                                <option v-for="ws in waterSources" :key="ws.id" :value="ws.id">{{ ws.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                        <h4 class="font-black text-xs text-rose-600 dark:text-rose-400 uppercase tracking-wider">Ölçülen Su Değerleri</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold mb-1">Ölçülen EC Değeri (mS/cm)</label>
                                <input v-model="rawWaterForm.ec_val" type="number" step="0.01" min="0" max="20" class="w-full border rounded-xl p-2.5 dark:bg-slate-900 font-extrabold text-indigo-600 dark:text-indigo-400" placeholder="1.80" required />
                            </div>
                            <div>
                                <label class="block font-bold mb-1">Ölçülen pH Değeri</label>
                                <input v-model="rawWaterForm.ph_val" type="number" step="0.01" min="0" max="14" class="w-full border rounded-xl p-2.5 dark:bg-slate-900 font-extrabold text-rose-600 dark:text-rose-400" placeholder="6.60" required />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-bold mb-1">Pompa Çalışma Durumu</label>
                            <select v-model="rawWaterForm.pump_status" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold">
                                <option value="open">Açık (Faal)</option>
                                <option value="closed">Kapalı</option>
                                <option value="faulty">Arızalı</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Ham Su Deposu Seviyesi</label>
                            <select v-model="rawWaterForm.water_tank_level" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold">
                                <option value="full">Dolu (%100)</option>
                                <option value="half_plus">Yarıdan Fazla (%75)</option>
                                <option value="half_minus">Yarıdan Az (%35)</option>
                                <option value="empty">Boş (%0)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Klor Tankı Seviyesi</label>
                            <select v-model="rawWaterForm.chlorine_tank_level" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold">
                                <option value="full">Dolu (%100)</option>
                                <option value="half_plus">Yarıdan Fazla (%75)</option>
                                <option value="half_minus">Yarıdan Az (%35)</option>
                                <option value="empty">Boş (%0)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border">
                        <div>
                            <label class="block font-bold mb-1">Dozaj Pompası Modu</label>
                            <select v-model="rawWaterForm.dosing_pump_mode" class="w-full border rounded-xl p-2 bg-white dark:bg-slate-900 font-bold">
                                <option value="auto">Otomatik Dozaj</option>
                                <option value="manual">Manuel Dozaj</option>
                            </select>
                        </div>
                        <div class="flex items-center">
                            <label class="flex items-center space-x-2 cursor-pointer mt-5">
                                <input v-model="rawWaterForm.is_filter_cleaned" type="checkbox" class="rounded text-emerald-600" />
                                <span class="font-bold text-emerald-700 dark:text-emerald-300">Filtre Temizliği Yapıldı (Fotoğraf Kanıtlı)</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                        <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-extrabold rounded-xl shadow-xs transition">Kontrolü Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'purification'" @submit.prevent="submitPurification" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">Su Kaynağı</label>
                        <select v-model="purificationForm.water_source_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                            <option v-for="ws in waterSources" :key="ws.id" :value="ws.id">{{ ws.name }}</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Giriş Basıncı (bar)</label>
                            <input v-model="purificationForm.inlet_pressure_bar" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Çıkış Basıncı (bar)</label>
                            <input v-model="purificationForm.outlet_pressure_bar" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border rounded-xl">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 text-white font-bold rounded-xl">Basınç Kaydet & Analiz Et</button>
                    </div>
                </form>

                <form v-if="modalType === 'order'" @submit.prevent="submitOrder" class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Alıcı Müşteri (Cari Firma)</label>
                            <select v-model="orderForm.trading_party_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                                <option v-for="tp in tradingParties" :key="tp.id" :value="tp.id">{{ tp.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Sipariş Tarihi</label>
                            <input v-model="orderForm.order_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">İstenen Teslim Tarihi</label>
                            <input v-model="orderForm.requested_delivery_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Satınalma / Kontak Yetkili Kişi</label>
                        <input v-model="orderForm.contact_person" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="örn: Ahmet Satınalma Müdürü" />
                    </div>

                    <!-- Sipariş Kalemleri / Ürün Listesi -->
                    <div class="p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-800 dark:text-slate-200 uppercase text-[11px]">Sipariş Kalemleri & Ürünler</span>
                            <button type="button" @click="addOrderItem" class="text-rose-600 hover:text-rose-500 font-bold text-xs flex items-center gap-1">
                                + Yeni Ürün Ekle
                            </button>
                        </div>

                        <div v-for="(item, idx) in orderForm.items" :key="idx" class="p-3 bg-white dark:bg-slate-900 rounded-xl border space-y-2">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-2 items-center">
                                <div>
                                    <label class="block text-[11px] text-slate-500 mb-1">Ürün</label>
                                    <select v-model="item.product_id" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs" required>
                                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] text-slate-500 mb-1">Paketleme / Ambalaj</label>
                                    <select v-model="item.packaging_definition_id" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs">
                                        <option :value="null">Seçiniz (Varsayılan Kasa)</option>
                                        <option v-for="pkg in packagings" :key="pkg.id" :value="pkg.id">{{ pkg.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] text-slate-500 mb-1">Miktar (Kg / Adet)</label>
                                    <input v-model="item.quantity" type="number" min="0.1" step="0.1" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs" required />
                                </div>
                                <div>
                                    <label class="block text-[11px] text-slate-500 mb-1">Birim Fiyat (₺)</label>
                                    <div class="flex items-center gap-1">
                                        <input v-model="item.unit_price" type="number" min="0" step="0.1" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs" required />
                                        <button v-if="orderForm.items.length > 1" type="button" @click="removeOrderItem(idx)" class="text-rose-500 hover:text-rose-700 px-2 font-bold text-sm">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right text-[11px] font-bold text-indigo-600 pt-1 border-t border-dashed">
                                Kalem Tutarı: ₺{{ (parseFloat(String(item.quantity || 0)) * parseFloat(String(item.unit_price || 0))).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-2 border-t font-black text-sm">
                            <span>HESAPLANAN GENEL TOPLAM:</span>
                            <span class="text-emerald-600 text-base">₺{{ calculateOrderTotal.toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Sipariş & Sevkiyat Notu</label>
                        <textarea v-model="orderForm.notes" rows="2" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Özel paketleme, soğuk zincir veya teslimat talimatları..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold rounded-xl transition">
                            Sipariş Oluştur (₺{{ calculateOrderTotal.toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }})
                        </button>
                    </div>
                </form>

                <!-- Sipariş Düzenleme Formu -->
                <form v-if="modalType === 'order_edit'" @submit.prevent="submitEditOrder" class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Alıcı Müşteri (Cari Firma)</label>
                            <select v-model="orderEditForm.trading_party_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                                <option v-for="tp in tradingParties" :key="tp.id" :value="tp.id">{{ tp.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Sipariş Tarihi</label>
                            <input v-model="orderEditForm.order_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">İstenen Teslim Tarihi</label>
                            <input v-model="orderEditForm.requested_delivery_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Satınalma / Kontak Yetkili Kişi</label>
                            <input v-model="orderEditForm.contact_person" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="örn: Ahmet Satınalma Müdürü" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Sipariş Durumu</label>
                            <select v-model="orderEditForm.status" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold">
                                <option value="pending">Beklemede</option>
                                <option value="confirmed">Onaylı / Hazırlanıyor</option>
                                <option value="shipped">Sevk Edildi</option>
                                <option value="cancelled">İptal Edildi</option>
                            </select>
                        </div>
                    </div>

                    <!-- Sipariş Kalemleri / Ürün Listesi Düzenleme -->
                    <div class="p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-slate-800 dark:text-slate-200 uppercase text-[11px]">Sipariş Kalemleri & Ürünler (Düzenle)</span>
                            <button type="button" @click="addOrderEditItem" class="text-rose-600 hover:text-rose-500 font-bold text-xs flex items-center gap-1">
                                + Yeni Kalem Ekle
                            </button>
                        </div>

                        <div v-for="(item, idx) in orderEditForm.items" :key="idx" class="p-3 bg-white dark:bg-slate-900 rounded-xl border space-y-2">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-2 items-center">
                                <div>
                                    <label class="block text-[11px] text-slate-500 mb-1">Ürün</label>
                                    <select v-model="item.product_id" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs" required>
                                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] text-slate-500 mb-1">Paketleme / Ambalaj</label>
                                    <select v-model="item.packaging_definition_id" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs">
                                        <option :value="null">Seçiniz (Varsayılan Kasa)</option>
                                        <option v-for="pkg in packagings" :key="pkg.id" :value="pkg.id">{{ pkg.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] text-slate-500 mb-1">Miktar (Kg / Adet)</label>
                                    <input v-model="item.quantity" type="number" min="0.1" step="0.1" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs" required />
                                </div>
                                <div>
                                    <label class="block text-[11px] text-slate-500 mb-1">Birim Fiyat (₺)</label>
                                    <div class="flex items-center gap-1">
                                        <input v-model="item.unit_price" type="number" min="0" step="0.1" class="w-full border rounded-xl p-2 dark:bg-slate-950 text-xs" required />
                                        <button v-if="orderEditForm.items.length > 1" type="button" @click="removeOrderEditItem(idx)" class="text-rose-500 hover:text-rose-700 px-2 font-bold text-sm">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right text-[11px] font-bold text-indigo-600 pt-1 border-t border-dashed">
                                Kalem Tutarı: ₺{{ (parseFloat(item.quantity || 0) * parseFloat(item.unit_price || 0)).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                            </div>
                        </div>

                        <div class="flex justify-between items-center pt-2 border-t font-black text-sm">
                            <span>YENİ TOPLAM SİPARİŞ TUTARI:</span>
                            <span class="text-emerald-600 text-base">₺{{ calculateOrderEditTotal.toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Sipariş & Sevkiyat Notu</label>
                        <textarea v-model="orderEditForm.notes" rows="2" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Özel paketleme, soğuk zincir veya teslimat talimatları..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 text-white font-bold rounded-xl transition">
                            Değişiklikleri Kaydet (₺{{ calculateOrderEditTotal.toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }})
                        </button>
                    </div>
                </form>

                <form v-if="modalType === 'shipment'" @submit.prevent="submitShipment" class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Alıcı Müşteri (Cari Firma)</label>
                            <select v-model="shipmentForm.trading_party_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                                <option v-for="tp in tradingParties" :key="tp.id" :value="tp.id">{{ tp.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Bağlı Sipariş (Opsiyonel)</label>
                            <select v-model="shipmentForm.customer_order_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option :value="null">Bağımsız / Doğrudan Sevkiyat</option>
                                <option v-for="o in customerOrders" :key="o.id" :value="o.id">
                                    #ORD-{{ String(o.id).padStart(4, '0') }} - {{ o.trading_party?.name }} (₺{{ o.total_amount }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Sevkiyat & Çıkış Tarihi</label>
                            <input v-model="shipmentForm.shipment_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Sevk Edilen Ürün</label>
                            <select v-model="shipmentForm.product_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                                <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Paketleme / Ambalaj</label>
                            <select v-model="shipmentForm.packaging_definition_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option :value="null">Standart / Kasa</option>
                                <option v-for="pkg in packagings" :key="pkg.id" :value="pkg.id">{{ pkg.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Net Sevk Miktarı (Kg)</label>
                            <input v-model="shipmentForm.quantity" type="number" min="0.1" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Birim Fiyat (₺/Kg)</label>
                            <input v-model="shipmentForm.unit_price" type="number" min="0" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <div>
                                 <input v-model="shipmentForm.dia_waybill_code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-mono" placeholder="IRS-2026-001" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Teslimat Şekli</label>
                            <select v-model="shipmentForm.delivery_type_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option :value="null">Seçiniz</option>
                                <option v-for="dt in deliveryTypes" :key="dt.id" :value="dt.id">{{ dt.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Sevkiyat Durumu</label>
                            <select v-model="shipmentForm.status" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold">
                                <option value="on_the_way">Yolda</option>
                                <option value="delivered">Teslim Edildi</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Sevkiyat & Taşıma Notu</label>
                        <textarea v-model="shipmentForm.notes" rows="2" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Soğuk zincir derece talimatı, rampa no veya teslimat notu..."></textarea>
                    </div>

                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-900 flex justify-between items-center text-xs">
                        <span class="font-bold text-emerald-800 dark:text-emerald-300">HESAPLANAN İRSALİYE TUTARI:</span>
                        <span class="text-base font-black text-emerald-700 dark:text-emerald-400 font-mono">
                            ₺{{ (parseFloat(String(shipmentForm.quantity || 0)) * parseFloat(String(shipmentForm.unit_price || 0))).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                        </span>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                        <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-emerald-600 text-white font-bold rounded-xl transition">
                            Sevkiyatı & İrsaliyeyi Kaydet
                        </button>
                    </div>
                </form>

                <form v-if="modalType === 'daily_sheet'" @submit.prevent="submitDailyWorkSheet" class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Tarih</label>
                            <input v-model="dailyWorkSheetForm.work_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Şirket</label>
                            <select v-model="dailyWorkSheetForm.company_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Tesis / Bölge</label>
                            <select v-model="dailyWorkSheetForm.production_location_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="p-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 rounded-xl space-y-3">
                        <h4 class="font-bold text-slate-700 dark:text-slate-300 border-b border-slate-200 dark:border-slate-800 pb-2">Çavuş ve İşçi Ekibi</h4>
                        <div v-for="(cl, index) in dailyWorkSheetForm.crew_leaders" :key="'cl-'+index" class="grid grid-cols-1 md:grid-cols-4 gap-2 items-end">
                            <div class="md:col-span-2">
                                <label class="block font-bold mb-1">Çavuş (Ekip Başı)</label>
                                <select v-model="cl.crew_leader_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                    <option :value="null">Seçiniz</option>
                                    <option v-for="leader in crewLeaders" :key="leader.id" :value="leader.id">
                                        {{ leader.first_name }} {{ leader.last_name }} 
                                        ({{ leader.workers?.length || 0 }} Kayıtlı İşçi)
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold mb-1">Ekstra Gelen İşçi Sayısı</label>
                                <input v-model="cl.extra_worker_count" type="number" min="0" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                            </div>
                            <div>
                                <label class="block font-bold mb-1">Gelen Araç Sayısı</label>
                                <input v-model="cl.car_count" type="number" min="0" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50 rounded-xl space-y-3">
                        <h4 class="font-bold text-emerald-800 dark:text-emerald-400 border-b border-emerald-200 dark:border-emerald-900/50 pb-2">Hasat Bilgileri</h4>
                        <div v-for="(hi, index) in dailyWorkSheetForm.harvest_items" :key="'hi-'+index" class="space-y-3">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                <div>
                                    <label class="block font-bold mb-1">Hasat Edilen Ürün</label>
                                    <select v-model="hi.product_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                        <option :value="null">Seçiniz</option>
                                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold mb-1">Ambalaj Tipi</label>
                                    <select v-model="hi.packaging_definition_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                                        <option :value="null">Dökme / Kasa</option>
                                        <option v-for="pkg in packagings" :key="pkg.id" :value="pkg.id">{{ pkg.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold mb-1">Ambalaj Sayısı (Kasa/Koli Adedi)</label>
                                    <input v-model="hi.package_count" type="number" min="0" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-2">
                                <div>
                                    <label class="block font-bold mb-1">Toplam Tahmini KG</label>
                                    <input v-model="hi.quantity" type="number" min="0" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Hasat Sonrası Depolama / Sevk Durumu</label>
                        <select v-model="dailyWorkSheetForm.storage_destination" class="w-full border rounded-xl p-2.5 dark:bg-slate-950">
                            <option value="direct_sale">Doğrudan Satışa Gitti</option>
                            <option value="warehouse">Soğuk Hava Deposuna İndi</option>
                            <option value="merchant">Tüccara (Komisyoncuya) Gitti</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Günlük Notlar / Olumsuzluklar</label>
                        <textarea v-model="dailyWorkSheetForm.notes" rows="2" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Örn: Yağmur nedeniyle saat 15:00'te paydos edildi..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                        <button type="submit" :disabled="dailyWorkSheetForm.processing" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-bold rounded-xl transition">Formu Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'market_price'" @submit.prevent="submitMarketPrice" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1">Ürün Seçimi</label>
                        <select v-model="marketPriceForm.product_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required>
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                        <div v-if="marketPriceForm.errors.product_id" class="text-rose-600 text-[10px] mt-1">{{ marketPriceForm.errors.product_id }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Tarih</label>
                            <input v-model="marketPriceForm.price_date" type="date" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                            <div v-if="marketPriceForm.errors.price_date" class="text-rose-600 text-[10px] mt-1">{{ marketPriceForm.errors.price_date }}</div>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Birim Fiyat (TL/Kg)</label>
                            <input v-model="marketPriceForm.unit_price" type="number" step="0.1" max="999999" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                            <div v-if="marketPriceForm.errors.unit_price" class="text-rose-600 text-[10px] mt-1">{{ marketPriceForm.errors.unit_price }}</div>
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Kaynak Adı (Örn: Antalya Hal Fiyatı)</label>
                        <input v-model="marketPriceForm.source_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        <div v-if="marketPriceForm.errors.source_name" class="text-rose-600 text-[10px] mt-1">{{ marketPriceForm.errors.source_name }}</div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                        <button type="submit" :disabled="marketPriceForm.processing" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition">Piyasa Fiyatı Kaydet</button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="showModal && (modalType === 'crew_leader' || modalType === 'worker')" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-xs p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-xl p-7 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                            {{ modalType === 'crew_leader' ? 'Yeni Çavuş Kayıt Formu' : 'Yeni İşçi Kayıt Formu' }}
                        </h3>
                        <p class="text-xs text-slate-500">Lütfen operasyonel alanları eksiksiz doldurunuz.</p>
                    </div>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-700 font-bold text-2xl leading-none">&times;</button>
                </div>

                <form v-if="modalType === 'crew_leader'" @submit.prevent="submitCrewLeader" class="space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Adı</label>
                            <input v-model="crewLeaderForm.first_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                            <div v-if="crewLeaderForm.errors.first_name" class="text-rose-600 text-[10px] mt-1">{{ crewLeaderForm.errors.first_name }}</div>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Soyadı</label>
                            <input v-model="crewLeaderForm.last_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                            <div v-if="crewLeaderForm.errors.last_name" class="text-rose-600 text-[10px] mt-1">{{ crewLeaderForm.errors.last_name }}</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">TC Kimlik</label>
                            <input v-model="crewLeaderForm.identity_number" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Telefon</label>
                            <input v-model="crewLeaderForm.phone" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Geldiği Yer (İlçe/Köy)</label>
                        <input v-model="crewLeaderForm.origin_city" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Günlük Yevmiye Ücreti (TL)</label>
                            <input v-model="crewLeaderForm.daily_wage" type="number" step="1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                            <div v-if="crewLeaderForm.errors.daily_wage" class="text-rose-600 text-[10px] mt-1">{{ crewLeaderForm.errors.daily_wage }}</div>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Çavuş Çarpanı (Örn: 1.5)</label>
                            <input v-model="crewLeaderForm.multiplier" type="number" step="0.1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">Min. Araç Şartı</label>
                            <input v-model="crewLeaderForm.min_car_requirement" type="number" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                        <div>
                            <label class="block font-bold mb-1">Araç Başı Yol Ücreti (TL)</label>
                            <input v-model="crewLeaderForm.travel_fee_per_car" type="number" step="1" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                        </div>
                    </div>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer font-bold">
                            <input type="checkbox" v-model="crewLeaderForm.is_food_included" class="rounded text-rose-600" />
                            Yemek Dahil mi?
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer font-bold">
                            <input type="checkbox" v-model="crewLeaderForm.is_leader_fee_included" class="rounded text-rose-600" />
                            Çavuşluk Bedeli Dahil mi?
                        </label>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">DİA Cari Kodu</label>
                        <input v-model="crewLeaderForm.dia_cari_code" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" placeholder="Örn: 120.01.001" />
                    </div>
                    
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                        <button type="submit" :disabled="crewLeaderForm.processing" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition">Kaydet</button>
                    </div>
                </form>

                <form v-if="modalType === 'worker'" @submit.prevent="submitWorker" class="space-y-4 text-xs">
                    <div v-if="workerForm.crew_leader_id" class="bg-indigo-50 dark:bg-indigo-950/50 p-2 rounded-lg border border-indigo-100 dark:border-indigo-900 mb-2">
                        <span class="font-bold text-indigo-700 dark:text-indigo-400">Bağlı Olduğu Çavuş ID: </span>
                        <span class="text-indigo-900 dark:text-indigo-200">{{ workerForm.crew_leader_id }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold mb-1">İşçi Adı</label>
                            <input v-model="workerForm.first_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                            <div v-if="workerForm.errors.first_name" class="text-rose-600 text-[10px] mt-1">{{ workerForm.errors.first_name }}</div>
                        </div>
                        <div>
                            <label class="block font-bold mb-1">İşçi Soyadı</label>
                            <input v-model="workerForm.last_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" required />
                            <div v-if="workerForm.errors.last_name" class="text-rose-600 text-[10px] mt-1">{{ workerForm.errors.last_name }}</div>
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">TC Kimlik</label>
                        <input v-model="workerForm.identity_number" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Performans Notu (1-5)</label>
                        <input v-model="workerForm.performance_rating" type="number" min="1" max="5" class="w-full border rounded-xl p-2.5 dark:bg-slate-950" />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Notlar / Değerlendirme</label>
                        <textarea v-model="workerForm.notes" rows="2" class="w-full border rounded-xl p-2.5 dark:bg-slate-950"></textarea>
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="showModal = false" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                        <button type="submit" :disabled="workerForm.processing" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="showPrintModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
            <div class="bg-white text-slate-900 rounded-2xl shadow-2xl w-full max-w-lg p-6 font-mono">
                <div class="border-b-2 border-slate-900 pb-3 mb-4 text-center">
                    <h2 class="text-base font-bold">SASA TARIM ERP - GÜBRE TANK HAZIRLAMA DÖKÜM ÇIKTISI</h2>
                    <p class="text-xs mt-1">Reçete: {{ printModalData?.recipe?.name }} | {{ printModalData?.tank?.tank_name }}</p>
                </div>
                <div class="text-xs space-y-2 mb-6">
                    <div class="font-bold border-b pb-1">TANK KAPASİTESİ: {{ printModalData?.tank?.capacity_liters }} LİTRE</div>
                    <div class="font-bold text-sm text-rose-600">HAZIRLANACAK SOLÜSYON İÇERİĞİ:</div>
                    <ul class="space-y-1">
                        <li v-for="item in printModalData?.tank?.items" :key="item.id" class="flex justify-between border-b border-dashed pb-1">
                            <span>- {{ item.product_name }} ({{ item.brand || 'Marka Belirtilmedi' }})</span>
                            <span class="font-bold text-rose-600">{{ item.quantity }} {{ item.unit }}</span>
                        </li>
                    </ul>
                </div>
                <div class="flex justify-between items-center pt-4 border-t-2 border-slate-900">
                    <button @click="showPrintModal = false" class="px-4 py-2 text-xs border rounded-xl font-bold">Kapat</button>
                    <button @click="printPage()" class="px-4 py-2 text-xs bg-emerald-600 text-white rounded-xl font-bold">Yazdır</button>
                </div>
            </div>
        </div>

        <div v-if="showCommentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl w-full max-w-md p-6 border border-slate-200 dark:border-slate-800">
                <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                    <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Not / Yorum Ekle</h3>
                    <button @click="showCommentModal = false" class="text-slate-400 font-bold hover:text-slate-600">✕</button>
                </div>
                <form @submit.prevent="submitCommentForm" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold mb-1 text-slate-700 dark:text-slate-300">Görev:</label>
                        <div class="p-2.5 bg-slate-50 dark:bg-slate-950 rounded-xl font-bold text-slate-800 dark:text-slate-200 border">
                            {{ commentWorkPlan?.title }}
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold mb-1 text-slate-700 dark:text-slate-300">Süreç Notu / Yorumunuz:</label>
                        <textarea v-model="commentForm.comment" rows="3" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs" placeholder="İş durumu, yapılan müdahale veya süreç hakkında açıklama yazın..." required></textarea>
                    </div>
                    <div>
                        <label class="block font-bold mb-1 text-slate-700 dark:text-slate-300">Görsel Kanıt Yükle (İsteğe Bağlı Fotoğraf):</label>
                        <input type="file" @change="(e: any) => commentForm.photo = e.target.files[0]" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200" accept="image/*" />
                    </div>
                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="showCommentModal = false" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                        <button type="submit" :disabled="commentForm.processing" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl transition">
                            Notu Kaydet
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div v-if="showImagePreview" @click="showImagePreview = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 transition-opacity">
            <div class="relative max-w-4xl max-h-[90vh] bg-slate-900 p-2 rounded-2xl shadow-2xl border border-slate-700" @click.stop>
                <button @click="showImagePreview = false" class="absolute -top-3 -right-3 bg-rose-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm shadow-lg hover:bg-rose-500 transition">
                    ✕
                </button>
                <img :src="previewImageUrl" alt="SASA ERP Belge Görseli" class="max-h-[80vh] w-auto object-contain rounded-xl" />
                <div class="mt-2 text-center text-xs font-semibold text-slate-300">
                    📄 Belge Kanıt Fotoğrafı - SASA Tarım ERP
                </div>
            </div>
        </div>
        <!-- İmzalı Çavuş Fişi Yazdırma Modalı -->
        <!-- Resmi Günlük Çavuş Hakediş ve Puantaj Fişi Yazdırma Modalı -->
        <div v-if="showCrewPrintModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 print:p-0 print:static print:bg-white overflow-y-auto">
            <div class="print-document bg-white text-black rounded-none shadow-none w-full max-w-4xl p-8 print:p-0 font-sans border print:border-none border-slate-300" style="background-color: #ffffff !important; color: #000000 !important;">
                <!-- ÜST KURUMSAL BAŞLIK VE LOGO -->
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
                        <div><strong>BELGE NO:</strong> #DW-{{ String(crewPrintData?.sheet?.id).padStart(4, '0') }}</div>
                        <div><strong>TARİH:</strong> {{ formatDisplayDate(crewPrintData?.sheet?.work_date) }}</div>
                        <div class="text-[9px] text-slate-500 font-sans mt-0.5">SASA ERP Belgesi</div>
                    </div>
                </div>

                <!-- BÖLÜM 1: ÇAVUŞ / TAŞERON BİLGİLERİ -->
                <div class="mb-4">
                    <h3 class="text-xs font-black text-teal-800 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                        <span>■</span> ÇAVUŞ VE TAŞERON KİMLİK BİLGİLERİ
                    </h3>
                    <table class="w-full border-collapse border border-slate-900 text-xs">
                        <tbody>
                            <tr>
                                <td class="border border-slate-900 bg-slate-100 font-bold p-1.5 w-1/4">Çavuş Adı Soyadı:</td>
                                <td class="border border-slate-900 p-1.5 w-1/4 font-bold">{{ crewPrintData?.crew?.crew_leader?.first_name }} {{ crewPrintData?.crew?.crew_leader?.last_name }}</td>
                                <td class="border border-slate-900 bg-slate-100 font-bold p-1.5 w-1/4">T.C. Kimlik No:</td>
                                <td class="border border-slate-900 p-1.5 w-1/4 font-mono">{{ crewPrintData?.crew?.crew_leader?.identity_number || '-' }}</td>
                            </tr>
                            <tr>
                                <td class="border border-slate-900 bg-slate-100 font-bold p-1.5">Memleket / İl:</td>
                                <td class="border border-slate-900 p-1.5">{{ crewPrintData?.crew?.crew_leader?.origin_city || '-' }}</td>
                                <td class="border border-slate-900 bg-slate-100 font-bold p-1.5">Telefon Numarası:</td>
                                <td class="border border-slate-900 p-1.5 font-mono">{{ crewPrintData?.crew?.crew_leader?.phone || '-' }}</td>
                            </tr>
                            <tr>
                                <td class="border border-slate-900 bg-slate-100 font-bold p-1.5">Çalışılan Tesis:</td>
                                <td class="border border-slate-900 p-1.5 font-bold">{{ crewPrintData?.sheet?.production_location?.name || 'SASA Tarım Tesisi' }}</td>
                                <td class="border border-slate-900 bg-slate-100 font-bold p-1.5">Çalışma Tarihi:</td>
                                <td class="border border-slate-900 p-1.5 font-mono">{{ formatDisplayDate(crewPrintData?.sheet?.work_date) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- BÖLÜM 2: PUANTAJ VE HAKEDİŞ HESABI -->
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
                                <td class="border border-slate-900 p-2 text-center font-mono font-bold">{{ crewPrintData?.crew?.worker_count }} Kişi</td>
                                <td class="border border-slate-900 p-2 text-center font-mono">1 Gün</td>
                                <td class="border border-slate-900 p-2 text-right font-mono">₺{{ crewPrintData?.crew?.crew_leader?.daily_wage || 0 }}</td>
                                <td class="border border-slate-900 p-2 text-right font-mono font-bold">
                                    ₺{{ ((crewPrintData?.crew?.worker_count || 0) * (crewPrintData?.crew?.crew_leader?.daily_wage || 0)).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="border border-slate-900 p-2 font-bold">Çavuş Primi & Servis / Araç Payı</td>
                                <td class="border border-slate-900 p-2 text-center font-mono">{{ crewPrintData?.crew?.car_count || 0 }} Araç</td>
                                <td class="border border-slate-900 p-2 text-center font-mono">{{ crewPrintData?.crew?.crew_leader?.multiplier || 1 }}x Katsayı</td>
                                <td class="border border-slate-900 p-2 text-right font-mono">-</td>
                                <td class="border border-slate-900 p-2 text-right font-mono font-bold">Dahil</td>
                            </tr>
                            <tr v-if="crewPrintData?.crew?.overtime_hours > 0">
                                <td class="border border-slate-900 p-2 font-bold">Fazla Mesai</td>
                                <td class="border border-slate-900 p-2 text-center font-mono">{{ crewPrintData?.crew?.worker_count }} Kişi</td>
                                <td class="border border-slate-900 p-2 text-center font-mono">{{ crewPrintData?.crew?.overtime_hours }} Saat</td>
                                <td class="border border-slate-900 p-2 text-right font-mono">-</td>
                                <td class="border border-slate-900 p-2 text-right font-mono font-bold">Hesaba Eklendi</td>
                            </tr>
                            <tr class="bg-slate-50 font-black">
                                <td colspan="4" class="border border-slate-900 p-2 text-right uppercase">ÇAVUŞA ÖDENECEK TOPLAM NET HAKEDİŞ:</td>
                                <td class="border border-slate-900 p-2 text-right font-mono text-sm text-emerald-700">
                                    ₺{{ parseFloat(crewPrintData?.crew?.calculated_wage_total || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- BÖLÜM 3: AÇIKLAMA -->
                <div class="mb-4">
                    <h3 class="text-xs font-black text-teal-800 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                        <span>■</span> PUANTAJ VE HAKEDİŞ NOTLARI
                    </h3>
                    <div class="border border-slate-900 p-2.5 text-xs bg-slate-50 min-h-[40px]">
                        Hesaplanan tutara günlük yevmiyeler, çavuş organizasyon primi, işçi nakliye/servis bedeli ve saha mesaisi dahildir.
                    </div>
                </div>

                <!-- BÖLÜM 4: İMZA BLOKLARI -->
                <div class="mb-5">
                    <h3 class="text-xs font-black text-teal-800 uppercase tracking-wider mb-2 flex items-center gap-1">
                        <span>■</span> YETKİLİ ONAY VE İMZA BLOKLARI
                    </h3>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="border border-slate-900 p-2.5 text-center flex flex-col justify-between h-24 bg-slate-50/50">
                            <span class="text-[11px] font-bold text-slate-800">ÇAVUŞ (TESLİM ALAN)</span>
                            <span class="text-[10px] text-slate-600">{{ crewPrintData?.crew?.crew_leader?.first_name }} {{ crewPrintData?.crew?.crew_leader?.last_name }}</span>
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

                <!-- BÖLÜM 5: BARKOD VE ELEKTRONİK DOĞRULAMA -->
                <div class="pt-3 border-t-2 border-slate-900 flex flex-col items-center justify-center text-center">
                    <div class="flex items-center justify-center gap-0.5 h-7 mb-1">
                        <div v-for="n in 48" :key="n" :class="n % 3 === 0 ? 'w-1 bg-black' : (n % 2 === 0 ? 'w-0.5 bg-black' : 'w-1 bg-transparent h-full inline-block')"></div>
                    </div>
                    <div class="font-mono text-[10px] font-bold tracking-widest text-slate-900">
                        SASA-2026-LAB-{{ String(crewPrintData?.sheet?.id).padStart(6, '0') }}-TR
                    </div>
                    <p class="text-[9px] text-slate-600 mt-1 max-w-xl">
                        Bu puantaj ve hakediş bordrosu SASA Tarım ERP sistemi tarafından elektronik ortamda onaylanmıştır.
                    </p>
                    <div class="text-[9px] font-mono text-slate-400 mt-1">
                        Sayfa 1 / 1
                    </div>
                </div>

                <!-- DÖKÜMAN EYLEMLERİ -->
                <div class="no-print flex justify-between items-center pt-4 mt-4 border-t border-slate-200">
                    <button @click="showCrewPrintModal = false" class="px-4 py-2 text-xs border rounded-xl font-bold hover:bg-slate-100 transition">Kapat</button>
                    <button @click="printPage()" class="px-5 py-2 text-xs bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition flex items-center gap-2 shadow-sm">
                        Resmi Bordro Çıktısı Al (A4 Yazdır)
                    </button>
                </div>
            </div>
        </div>

        <!-- Günlük İşçi ve Hasat Formu Detaylı İnceleme Modalı -->
        <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-3xl p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800 mb-4">
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Günlük Saha & Hasat Formu İncelemesi</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Form ID: #{{ selectedDetailSheet?.id }} | Tarih: {{ selectedDetailSheet?.work_date }}</p>
                    </div>
                    <button @click="showDetailModal = false" class="text-slate-400 font-bold hover:text-slate-600 text-xl">✕</button>
                </div>

                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800">
                        <div>
                            <span class="text-slate-400 block text-[11px]">Tesis / Lokasyon</span>
                            <strong class="text-slate-800 dark:text-slate-100">{{ selectedDetailSheet?.production_location?.name }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Şirket</span>
                            <strong class="text-slate-800 dark:text-slate-100">{{ selectedDetailSheet?.company?.name }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Gönderen</span>
                            <strong class="text-slate-800 dark:text-slate-100">{{ selectedDetailSheet?.submitted_by ? (selectedDetailSheet.submitted_by.first_name + ' ' + selectedDetailSheet.submitted_by.last_name) : 'Yönetici' }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[11px]">Onay Durumu</span>
                            <span v-if="selectedDetailSheet?.status === 'approved'" class="text-emerald-600 font-bold">✓ Onaylandı</span>
                            <span v-else-if="selectedDetailSheet?.status === 'rejected'" class="text-rose-600 font-bold">✕ Reddedildi</span>
                            <span v-else class="text-amber-600 font-bold">Onay Bekliyor</span>
                        </div>
                    </div>

                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-white dark:bg-slate-900">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 uppercase text-[11px] mb-3">Çavuş ve İşçi Dağılımları</h4>
                        <div class="space-y-2">
                            <div v-for="cl in selectedDetailSheet?.crew_leaders" :key="cl.id" class="p-3 bg-slate-50 dark:bg-slate-950 rounded-lg border text-xs flex justify-between items-center">
                                <div>
                                    <div class="font-bold text-slate-800 dark:text-slate-100">{{ cl.crew_leader?.first_name }} {{ cl.crew_leader?.last_name }} ({{ cl.crew_leader?.origin_city }})</div>
                                    <div class="text-slate-500 text-[11px]">İşçi: {{ cl.worker_count }} Kişi | Araç/Servis: {{ cl.car_count }} Adet | Mesai: {{ cl.overtime_hours || 0 }} Saat</div>
                                </div>
                                <div class="text-right">
                                    <div class="font-black text-emerald-600">₺{{ cl.calculated_wage_total }}</div>
                                    <button @click="openCrewPrintModal(selectedDetailSheet, cl)" class="text-rose-600 hover:underline text-[11px] font-bold">Fiş Al</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border border-slate-200 dark:border-slate-800 rounded-xl p-4 bg-white dark:bg-slate-900">
                        <h4 class="font-bold text-slate-800 dark:text-slate-200 uppercase text-[11px] mb-3">Toplanan Hasat Ürünleri</h4>
                        <div class="space-y-2">
                            <div v-for="item in selectedDetailSheet?.harvest_items" :key="item.id" class="p-3 bg-slate-50 dark:bg-slate-950 rounded-lg border text-xs flex justify-between items-center">
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

                    <div v-if="selectedDetailSheet?.notes" class="p-3 bg-slate-50 dark:bg-slate-950 rounded-xl border border-slate-200 dark:border-slate-800">
                        <strong class="block mb-1 text-slate-700 dark:text-slate-300">Saha Açıklaması / Not:</strong>
                        <p class="text-slate-600 dark:text-slate-400">{{ selectedDetailSheet.notes }}</p>
                    </div>

                    <div v-if="canApprove(selectedDetailSheet)" class="p-4 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900 rounded-xl flex items-center justify-between gap-4">
                        <span class="font-bold text-amber-800 dark:text-amber-300">
                            Bu formu incelediniz. Onaylamak veya reddetmek ister misiniz?
                        </span>
                        <div class="flex space-x-2">
                            <button @click="handleApproval(selectedDetailSheet.id, 'approve'); showDetailModal = false;" class="bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-bold transition">
                                Formu Onayla
                            </button>
                            <button @click="handleApproval(selectedDetailSheet.id, 'reject'); showDetailModal = false;" class="bg-rose-600 hover:bg-rose-500 text-white px-4 py-2 rounded-xl font-bold transition">
                                Reddet
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t mt-4">
                    <button type="button" @click="showDetailModal = false" class="px-4 py-2 border rounded-xl font-bold">Kapat</button>
                </div>
            </div>
        </div>
        <!-- Resmi Müşteri Sipariş Fişi Yazdırma Modalı -->
        <div v-if="showOrderPrintModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 print:p-0 print:static print:bg-white overflow-y-auto">
            <div class="print-document bg-white text-black rounded-none shadow-none w-full max-w-4xl p-8 print:p-0 font-sans border print:border-none border-slate-300" style="background-color: #ffffff !important; color: #000000 !important;">
                <!-- ÜST KURUMSAL BAŞLIK VE LOGO -->
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
                        <div><strong>SİPARİŞ NO:</strong> #ORD-{{ String(orderPrintData?.id).padStart(4, '0') }}</div>
                        <div><strong>TARİH:</strong> {{ formatDisplayDate(orderPrintData?.order_date) }}</div>
                        <div class="text-[9px] text-slate-500 font-sans mt-0.5">SASA ERP Belgesi</div>
                    </div>
                </div>

                <!-- BÖLÜM 1: BAŞVURU SAHİBİ / MÜŞTERİ BİLGİLERİ -->
                <div class="mb-3">
                    <h3 class="text-xs font-bold text-teal-800 uppercase tracking-wider mb-1">
                        BAŞVURU SAHİBİ / MÜŞTERİ BİLGİLERİ
                    </h3>
                    <table class="w-full border-collapse border border-slate-900 text-xs">
                        <tbody>
                            <tr>
                                <td class="border border-slate-900 font-bold p-1.5 w-1/4">Sipariş Takip No:</td>
                                <td class="border border-slate-900 p-1.5 w-1/4 font-mono font-bold">#ORD-{{ String(orderPrintData?.id).padStart(4, '0') }}</td>
                                <td class="border border-slate-900 font-bold p-1.5 w-1/4">Sipariş Tarihi:</td>
                                <td class="border border-slate-900 p-1.5 w-1/4 font-mono">{{ formatDisplayDate(orderPrintData?.order_date) }}</td>
                            </tr>
                            <tr>
                                <td class="border border-slate-900 font-bold p-1.5">Müşteri / Cari Adı:</td>
                                <td class="border border-slate-900 p-1.5 font-bold">{{ orderPrintData?.trading_party?.name }}</td>
                                <td class="border border-slate-900 font-bold p-1.5">Cari Kodu:</td>
                                <td class="border border-slate-900 p-1.5 font-mono">{{ orderPrintData?.trading_party?.dia_cari_code || '-' }}</td>
                            </tr>
                            <tr>
                                <td class="border border-slate-900 font-bold p-1.5">İstenen Termin / Teslim:</td>
                                <td class="border border-slate-900 p-1.5">{{ formatDisplayDate(orderPrintData?.requested_delivery_date) }}</td>
                                <td class="border border-slate-900 font-bold p-1.5">Yetkili / Kontak Kişi:</td>
                                <td class="border border-slate-900 p-1.5">{{ orderPrintData?.contact_person || '-' }}</td>
                            </tr>
                            <tr>
                                <td class="border border-slate-900 font-bold p-1.5">Sipariş Durumu:</td>
                                <td class="border border-slate-900 p-1.5 font-bold">
                                    {{ orderPrintData?.status === 'shipped' ? 'SEVK EDİLDİ' : (orderPrintData?.status === 'confirmed' ? 'ONAYLI / HAZIRLANIYOR' : (orderPrintData?.status === 'cancelled' ? 'İPTAL EDİLDİ' : 'BEKLEMEDE')) }}
                                </td>
                                <td class="border border-slate-900 font-bold p-1.5">Toplam Sevk Durumu:</td>
                                <td class="border border-slate-900 p-1.5 font-mono">
                                    {{ getOrderShippedQty(orderPrintData) }} / {{ getOrderTotalQty(orderPrintData) }} kg (%{{ getOrderProgressPercent(orderPrintData) }})
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- BÖLÜM 2: SİPARİŞ EDİLEN ÜRÜNLER VE FİYATLANDIRMA -->
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
                            <tr v-for="(item, idx) in orderPrintData?.items" :key="item.id">
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
                                    ₺{{ parseFloat(orderPrintData?.total_amount || 0).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- BÖLÜM 3: AÇIKLAMA VE ÖZEL ŞARTLAR -->
                <div class="mb-3">
                    <h3 class="text-xs font-bold text-teal-800 uppercase tracking-wider mb-1">
                        AÇIKLAMA VE SEVKİYAT TALİMATLARI
                    </h3>
                    <div class="border border-slate-900 p-2 text-xs min-h-[38px]">
                        {{ orderPrintData?.notes || 'Özel sevkiyat veya ambalaj şartı belirtilmemiştir. SASA Tarım standart kalite ve hijyen koşullarında teslim edilecektir.' }}
                    </div>
                </div>

                <!-- BÖLÜM 4: YETKİLİLER / İMZA KUTULARI -->
                <div class="mb-4">
                    <h3 class="text-xs font-bold text-teal-800 uppercase tracking-wider mb-1.5">
                        YETKİLİLER (ONAY VE İMZA)
                    </h3>
                    <div class="grid grid-cols-3 gap-3">
                        <div class="border border-dashed border-slate-700 p-2 text-center flex flex-col justify-between h-20">
                            <span class="text-[10px] font-bold text-slate-900 uppercase">MÜŞTERİ / TESLİM ALAN</span>
                            <span class="text-[9px] text-slate-600">{{ orderPrintData?.contact_person || orderPrintData?.trading_party?.name }}</span>
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

                <!-- BÖLÜM 5: BARKOD VE ELEKTRONİK DOĞRULAMA -->
                <div class="pt-2 border-t-2 border-slate-900 flex flex-col items-center justify-center text-center">
                    <div class="flex items-center justify-center gap-0.5 h-6 mb-0.5">
                        <div v-for="n in 56" :key="n" :class="n % 3 === 0 ? 'w-1 bg-black' : (n % 2 === 0 ? 'w-0.5 bg-black' : 'w-1.5 bg-transparent h-full inline-block')"></div>
                    </div>
                    <div class="font-mono text-[9px] font-bold tracking-widest text-slate-900">
                        20268250475276849a4
                    </div>
                    <p class="text-[8.5px] text-slate-600 mt-1 max-w-xl leading-tight">
                        Bu belgenin aslına ilişkin sorgulama https://ebelge.sasaerp.com/dogrulama/ORD-{{ String(orderPrintData?.id).padStart(4, '0') }} internet adresinden yapılabilir.<br />
                        İşbu rapor 5070 sayılı Elektronik İmza Kanunu uyarınca güvenli elektronik imza ile üretilmiştir.
                    </p>
                    <div class="text-[8.5px] font-mono text-slate-400 mt-0.5">
                        1 / 1
                    </div>
                </div>

                <!-- DÖKÜMAN EYLEMLERİ (Yazdırırken gizlenir) -->
                <div class="no-print flex justify-between items-center pt-4 mt-4 border-t border-slate-200">
                    <button @click="showOrderPrintModal = false" class="px-4 py-2 text-xs border rounded-xl font-bold hover:bg-slate-100 transition">Kapat</button>
                    <button @click="printPage()" class="px-5 py-2 text-xs bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition flex items-center gap-2 shadow-sm">
                        Resmi Rapor Çıktısı Al (A4 Yazdır)
                    </button>
                </div>
            </div>
        </div>

        <!-- Resmi SASA Tarım Sevk İrsaliyesi / Taşıma Belgesi Çıktı Modalı -->
        <div v-if="showWaybillPrintModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 print:p-0 print:static print:bg-white overflow-y-auto">
            <div class="print-document bg-white text-black rounded-none shadow-none w-full max-w-4xl p-8 print:p-0 font-sans border print:border-none border-slate-300" style="background-color: #ffffff !important; color: #000000 !important;">
                <!-- ÜST KURUMSAL BAŞLIK VE LOGO -->
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
                        <div><strong>İRSALİYE:</strong> {{ waybillPrintData?.dia_waybill_code || ('IRS-2026-' + waybillPrintData?.id) }}</div>
                        <div><strong>TARİH:</strong> {{ formatDisplayDate(waybillPrintData?.shipment_date) }}</div>
                        <div class="text-[9px] text-slate-500 font-sans mt-0.5">SASA ERP Belgesi</div>
                    </div>
                </div>

                <!-- BÖLÜM 1: GÖNDEREN & ALICI BİLGİLERİ -->
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
                                <td class="border border-slate-900 p-1.5 w-1/4 font-mono">{{ formatDisplayDate(waybillPrintData?.shipment_date) }}</td>
                            </tr>
                            <tr>
                                <td class="border border-slate-900 font-bold p-1.5">Alıcı Müşteri (Cari):</td>
                                <td class="border border-slate-900 p-1.5 font-bold">{{ waybillPrintData?.trading_party?.name }}</td>
                                <td class="border border-slate-900 font-bold p-1.5">Cari Kodu:</td>
                                <td class="border border-slate-900 p-1.5 font-mono">{{ waybillPrintData?.trading_party?.dia_cari_code || '-' }}</td>
                            </tr>
                            <tr>
                                <td class="border border-slate-900 font-bold p-1.5">Bağlı Sipariş No:</td>
                                <td class="border border-slate-900 p-1.5 font-mono">
                                    {{ (waybillPrintData?.customer_order_id || waybillPrintData?.order_id) ? ('#ORD-' + String(waybillPrintData?.customer_order_id || waybillPrintData?.order_id).padStart(4, '0')) : 'Doğrudan Sevkiyat' }}
                                </td>
                                <td class="border border-slate-900 font-bold p-1.5">Teslimat Şekli:</td>
                                <td class="border border-slate-900 p-1.5">{{ waybillPrintData?.delivery_type?.name || 'Doğrudan Araç Teslim' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- BÖLÜM 2: TAŞIYICI VE ARAÇ BİLGİLERİ -->
                <div class="mb-3">
                    <h3 class="text-xs font-bold text-teal-800 uppercase tracking-wider mb-1">
                        TAŞIYICI ARAÇ VE NAKLİYE BİLGİLERİ
                    </h3>
                    <table class="w-full border-collapse border border-slate-900 text-xs">
                        <tbody>
                            <tr>
                                <td class="border border-slate-900 font-bold p-1.5 w-1/4">Nakliye Araç Plakası:</td>
                                <td class="border border-slate-900 p-1.5 w-1/4 font-mono font-bold text-sm">{{ waybillPrintData?.vehicle_plate || '07 SASA 88' }}</td>
                                <td class="border border-slate-900 font-bold p-1.5 w-1/4">Şoför Adı Soyadı:</td>
                                <td class="border border-slate-900 p-1.5 w-1/4 font-bold">{{ waybillPrintData?.driver_name || '-' }}</td>
                            </tr>
                            <tr>
                                <td class="border border-slate-900 font-bold p-1.5">Şoför Telefon / İletişim:</td>
                                <td class="border border-slate-900 p-1.5 font-mono">{{ waybillPrintData?.driver_phone || '-' }}</td>
                                <td class="border border-slate-900 font-bold p-1.5">Sevkiyat Durumu:</td>
                                <td class="border border-slate-900 p-1.5 font-bold">
                                    {{ waybillPrintData?.status === 'delivered' ? 'TESLİM EDİLDİ' : 'YOLDA' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- BÖLÜM 3: SEVK EDİLEN MALIN CİNSİ VE BEDELİ -->
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
                                <td class="border border-slate-900 p-1.5 font-bold">{{ waybillPrintData?.product?.name || 'Tarımsal Ürün' }}</td>
                                <td class="border border-slate-900 p-1.5">{{ waybillPrintData?.packaging?.name || 'Standart Kasa' }}</td>
                                <td class="border border-slate-900 p-1.5 text-right font-mono font-bold">{{ parseFloat(waybillPrintData?.quantity || 0).toLocaleString('tr-TR') }} kg</td>
                                <td class="border border-slate-900 p-1.5 text-right font-mono">₺{{ parseFloat(waybillPrintData?.unit_price || 0).toFixed(2) }}</td>
                                <td class="border border-slate-900 p-1.5 text-right font-mono font-bold">
                                    ₺{{ (parseFloat(waybillPrintData?.quantity || 0) * parseFloat(waybillPrintData?.unit_price || 0)).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                                </td>
                            </tr>
                            <tr class="font-black">
                                <td colspan="4" class="border border-slate-900 p-2 text-right uppercase">TOPLAM SEVKİYAT & İRSALİYE TUTARI:</td>
                                <td colspan="2" class="border border-slate-900 p-2 text-right font-mono text-sm text-slate-950 font-black">
                                    ₺{{ (parseFloat(waybillPrintData?.quantity || 0) * parseFloat(waybillPrintData?.unit_price || 0)).toLocaleString('tr-TR', { minimumFractionDigits: 2 }) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- BÖLÜM 4: SEVKİYAT NOTU -->
                <div class="mb-3">
                    <h3 class="text-xs font-bold text-teal-800 uppercase tracking-wider mb-1">
                        AÇIKLAMA VE NAKLİYE TALİMATLARI
                    </h3>
                    <div class="border border-slate-900 p-2 text-xs min-h-[38px]">
                        {{ waybillPrintData?.notes || 'Soğuk hava muhafazalı sevk edilmiştir. Mal tesliminde irsaliye karşılıklı imza altına alınacaktır.' }}
                    </div>
                </div>

                <!-- BÖLÜM 5: RESMİ 3'LÜ İMZA BLOKLARI -->
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
                            <span class="text-[9px] text-slate-600">{{ waybillPrintData?.driver_name || 'Şoför' }}</span>
                            <div class="text-[8px] text-slate-400 uppercase">İmza / Tarih</div>
                        </div>
                        <div class="border border-dashed border-slate-700 p-2 text-center flex flex-col justify-between h-20">
                            <span class="text-[10px] font-bold text-slate-900 uppercase">TESLİM ALAN (ALICI)</span>
                            <span class="text-[9px] text-slate-600">{{ waybillPrintData?.trading_party?.name }}</span>
                            <div class="text-[8px] text-slate-400 uppercase">İmza / Kaşe</div>
                        </div>
                    </div>
                </div>

                <!-- BÖLÜM 6: BARKOD VE ELEKTRONİK DOĞRULAMA -->
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

                <!-- DÖKÜMAN EYLEMLERİ (Yazdırırken gizlenir) -->
                <div class="no-print flex justify-between items-center pt-4 mt-4 border-t border-slate-200">
                    <button @click="showWaybillPrintModal = false" class="px-4 py-2 text-xs border rounded-xl font-bold hover:bg-slate-100 transition">Kapat</button>
                    <button @click="printPage()" class="px-5 py-2 text-xs bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition flex items-center gap-2 shadow-sm">
                        Resmi İrsaliye Çıktısı Al (A4 Yazdır)
                    </button>
                </div>
            </div>
        </div>
        <!-- Generic Print Modal -->
        <div v-if="showGenericPrintModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 print:p-0 print:static print:bg-white overflow-y-auto">
            <div class="print-document bg-white text-black rounded-none shadow-none w-full max-w-4xl p-8 print:p-0 font-sans border print:border-none border-slate-300" style="background-color: #ffffff !important; color: #000000 !important;">
                <!-- ÜST KURUMSAL BAŞLIK VE LOGO -->
                <div class="flex justify-between items-center pb-3 mb-3 border-b-2 border-black" style="border-color: #000 !important;">
                    <div class="flex items-center gap-3">
                        <img src="/sasaerp.svg" alt="SASA Tarım Logo" class="h-14 w-auto" />
                        <div>
                            <div class="text-[10px] font-bold tracking-widest uppercase" style="color: #475569 !important;">KURUMSAL ERP SİSTEMİ</div>
                            <div class="text-xs font-black tracking-wider" style="color: #000000 !important;">SASA TARIM İŞLETMELERİ A.Ş.</div>
                        </div>
                    </div>

                    <div class="text-center">
                        <h1 class="text-sm font-black uppercase tracking-wider" style="color: #000000 !important;">SASA TARIM İŞLETMELERİ A.Ş.</h1>
                        <h2 class="text-xs font-semibold uppercase tracking-wide" style="color: #334155 !important;">
                            <span v-if="genericPrintType === 'gubreleme'">GÜBRELEME VE BESLEME BİRİMİ</span>
                            <span v-else-if="genericPrintType === 'sulama'">SULAMA OTOMASYON BİRİMİ</span>
                            <span v-else-if="genericPrintType === 'ilaclama'">BİTKİ KORUMA VE İLAÇLAMA BİRİMİ</span>
                            <span v-else-if="genericPrintType === 'su_analizi'">SU ANALİZ LABORATUVARI</span>
                            <span v-else-if="genericPrintType === 'kaynak_suyu_kontrol'">KAYNAK SUYU KONTROL BİRİMİ</span>
                            <span v-else-if="genericPrintType === 'aritma_suyu_kontrol'">ARITMA TESİSİ BİRİMİ</span>
                            <span v-else-if="genericPrintType === 'is_planlama'">İŞ PLANLAMA VE YÖNETİM</span>
                        </h2>
                        <div class="text-xs font-bold uppercase tracking-widest mt-0.5" style="color: #000000 !important;">RESMİ SİSTEM ÇIKTISI</div>
                    </div>

                    <div class="border p-2 text-right text-[10px] font-mono leading-tight min-w-[130px]" style="border-color: #000 !important; background-color: #f8fafc !important; color: #000 !important;">
                        <div><strong>BELGE NO:</strong> DOC-2026-{{ genericPrintData?.id || '000' }}</div>
                        <div><strong>TARİH:</strong> {{ formatDisplayDate(new Date().toISOString()) }}</div>
                        <div class="text-[9px] font-sans mt-0.5" style="color: #64748b !important;">SASA ERP Raporu</div>
                    </div>
                </div>

                <!-- DİNAMİK İÇERİK BÖLÜMÜ -->
                <div class="mb-4 min-h-[300px]">
                    <h3 class="text-xs font-bold uppercase tracking-wider mb-2 border-b pb-1" style="color: #115e59 !important; border-color: #cbd5e1 !important;">
                        SİSTEM KAYIT DETAYLARI
                    </h3>
                    <table class="w-full border-collapse border text-xs text-left" style="border-color: #000 !important; color: #000 !important;">
                        <tbody>
                            <!-- Gubreleme -->
                            <template v-if="genericPrintType === 'gubreleme'">
                                <tr><td class="border p-2 font-bold w-1/3" style="border-color: #000 !important;">Uygulanan Tesis:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.production_location?.name || 'Tesis Belirtilmedi' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Reçete Adı:</td><td class="border p-2 font-bold" style="border-color: #000 !important;">{{ genericPrintData?.recipe?.name || '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Başlangıç Tarihi:</td><td class="border p-2" style="border-color: #000 !important;">{{ formatDisplayDate(genericPrintData?.start_date) }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Hedef pH / EC:</td><td class="border p-2" style="border-color: #000 !important;">pH: {{ genericPrintData?.recipe?.water_ph }} | EC: {{ genericPrintData?.recipe?.water_ec }} mS/cm</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Bitiş Şartı:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.end_condition || '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Durum:</td><td class="border p-2 font-bold" style="border-color: #000 !important;">{{ genericPrintData?.is_active ? 'Aktif Çalışan' : 'Pasif' }}</td></tr>
                            </template>
                            
                            <!-- Sulama -->
                            <template v-else-if="genericPrintType === 'sulama'">
                                <tr><td class="border p-2 font-bold w-1/3" style="border-color: #000 !important;">Tesis / Lokasyon:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.production_location?.name || genericPrintData?.location?.name || '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Program Tarihi / Saati:</td><td class="border p-2" style="border-color: #000 !important;">{{ formatDisplayDate(genericPrintData?.schedule_date) }} - {{ genericPrintData?.start_time }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Sulama Tipi:</td><td class="border p-2 font-bold" style="border-color: #000 !important;">{{ genericPrintData?.is_fertilized ? 'Gübreli Sulama' : 'Boş Sulama' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Aktif Vanalar & Süreler:</td><td class="border p-2" style="border-color: #000 !important;">{{ (genericPrintData?.valves || []).map((v:any) => `${v.name || 'Vana'}: ${v.duration_minutes}dk`).join(', ') || 'Belirtilmedi' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Tank / Saha Notu:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.tank_stage_note || genericPrintData?.notes || '-' }}</td></tr>
                            </template>
                            
                            <!-- Ilaclama -->
                            <template v-else-if="genericPrintType === 'ilaclama'">
                                <tr><td class="border p-2 font-bold w-1/3" style="border-color: #000 !important;">Uygulama Tarihi / Kod:</td><td class="border p-2" style="border-color: #000 !important;">{{ formatDisplayDate(genericPrintData?.application_date) }} | {{ genericPrintData?.batch_code || '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">İlaç Reçetesi:</td><td class="border p-2 font-bold" style="border-color: #000 !important;">{{ genericPrintData?.recipe?.name || '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Kullanım Amacı:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.purpose || '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Kaplanan Alan:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.covered_area_description || '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Uygulayan Personel:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.applied_by ? (genericPrintData.applied_by.first_name + ' ' + genericPrintData.applied_by.last_name) : '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Tank Durumu:</td><td class="border p-2 font-bold" style="border-color: #000 !important;">{{ genericPrintData?.is_tank_finished ? 'Tank Tamamlandı' : 'Ertesi Gün Devam' }}</td></tr>
                            </template>
                            
                            <!-- Su Analizi -->
                            <template v-else-if="genericPrintType === 'su_analizi'">
                                <tr><td class="border p-2 font-bold w-1/3" style="border-color: #000 !important;">Su Kaynağı:</td><td class="border p-2 font-bold" style="border-color: #000 !important;">{{ genericPrintData?.water_source?.name || '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Analiz Tarihi:</td><td class="border p-2" style="border-color: #000 !important;">{{ formatDisplayDate(genericPrintData?.analysis_date) }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Ölçülen Değerler:</td><td class="border p-2 font-bold" style="border-color: #000 !important;">pH: {{ genericPrintData?.ph_level }} | EC: {{ genericPrintData?.ec_level }} mS/cm</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Laboratuvar Notu:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.notes || '-' }}</td></tr>
                            </template>
                            
                            <!-- Kaynak Suyu -->
                            <template v-else-if="genericPrintType === 'kaynak_suyu_kontrol'">
                                <tr><td class="border p-2 font-bold w-1/3" style="border-color: #000 !important;">Kaynak / Pompa Adı:</td><td class="border p-2 font-bold" style="border-color: #000 !important;">{{ genericPrintData?.water_source?.name || '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Kontrol Tarihi:</td><td class="border p-2" style="border-color: #000 !important;">{{ formatDisplayDate(genericPrintData?.control_date) }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Depo / Tank Durumu:</td><td class="border p-2" style="border-color: #000 !important;">Su: {{ genericPrintData?.water_tank_level }} | Klor: {{ genericPrintData?.chlorine_tank_level }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Dozaj Pompası:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.dosing_pump_mode === 'auto' ? 'Otomatik Dozaj' : 'Manuel Mod' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Ölçülen Değerler:</td><td class="border p-2 font-bold" style="border-color: #000 !important;">pH: {{ genericPrintData?.ph_val }} | EC: {{ genericPrintData?.ec_val }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Filtre & Pompa Durumu:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.is_filter_cleaned ? 'Filtre Temiz' : 'Filtre Kirli' }} | Pompa: {{ genericPrintData?.pump_status === 'open' ? 'Açık' : 'Kapalı' }}</td></tr>
                            </template>
                            
                            <!-- Arıtma Suyu -->
                            <template v-else-if="genericPrintType === 'aritma_suyu_kontrol'">
                                <tr><td class="border p-2 font-bold w-1/3" style="border-color: #000 !important;">Arıtma Ünitesi / Kuyu:</td><td class="border p-2 font-bold" style="border-color: #000 !important;">{{ genericPrintData?.water_source?.name || '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Kontrol Tarihi:</td><td class="border p-2" style="border-color: #000 !important;">{{ formatDisplayDate(genericPrintData?.control_date) }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Basınç Değerleri:</td><td class="border p-2" style="border-color: #000 !important;">Giriş: {{ genericPrintData?.inlet_pressure_bar }} bar | Çıkış: {{ genericPrintData?.outlet_pressure_bar }} bar</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Fark Basınç (&Delta;P):</td><td class="border p-2 font-bold" style="border-color: #000 !important;">{{ genericPrintData?.delta_pressure_bar }} bar (Max Eşik: {{ genericPrintData?.max_threshold_bar }} bar)</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Durum:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.has_warning ? 'UYARI: Filtre Doldu!' : 'Basınç Farkı Normal' }}</td></tr>
                            </template>
                            
                            <!-- İş Planlama -->
                            <template v-else-if="genericPrintType === 'is_planlama'">
                                <tr><td class="border p-2 font-bold w-1/3" style="border-color: #000 !important;">Görev Başlığı / Türü:</td><td class="border p-2 font-bold" style="border-color: #000 !important;">{{ genericPrintData?.title }} | {{ genericPrintData?.job_type?.name || '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Durum:</td><td class="border p-2 font-bold" style="border-color: #000 !important;">{{ genericPrintData?.status === 'completed' ? 'Tamamlandı' : (genericPrintData?.status === 'in_progress' ? 'Devam Ediyor' : 'Beklemede') }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Tesis:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.production_location?.name || '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Görevli / Sorumlu:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.assigned_personnel ? (genericPrintData.assigned_personnel.first_name + ' ' + genericPrintData.assigned_personnel.last_name) : '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Plan / Hedef Tarih:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.plan_date }} - {{ genericPrintData?.due_date || '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Müdür Talimatı:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.description || '-' }}</td></tr>
                                <tr><td class="border p-2 font-bold" style="border-color: #000 !important;">Süreç Notu:</td><td class="border p-2" style="border-color: #000 !important;">{{ genericPrintData?.completion_notes || '-' }}</td></tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- BÖLÜM 5: YETKİLİLER / İMZA KUTULARI -->
                <div class="mb-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider mb-1.5" style="color: #115e59 !important;">YETKİLİLER (ONAY VE İMZA)</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="border border-dashed p-2 text-center flex flex-col justify-between h-20" style="border-color: #334155 !important;">
                            <span class="text-[10px] font-bold uppercase" style="color: #000 !important;">RAPORU OLUŞTURAN</span>
                            <span class="text-[9px]" style="color: #475569 !important;">Sistem Kullanıcısı</span>
                            <div class="text-[8px] uppercase" style="color: #94a3b8 !important;">İmza / Kaşe</div>
                        </div>
                        <div class="border border-dashed p-2 text-center flex flex-col justify-between h-20" style="border-color: #334155 !important;">
                            <span class="text-[10px] font-bold uppercase" style="color: #000 !important;">SASA TARIM BİRİM SORUMLUSU</span>
                            <span class="text-[9px]" style="color: #475569 !important;">Onay</span>
                            <div class="text-[8px] uppercase" style="color: #94a3b8 !important;">İmza / Mühür</div>
                        </div>
                    </div>
                </div>

                <!-- BARKOD VE ELEKTRONİK DOĞRULAMA -->
                <div class="pt-2 border-t-2 flex flex-col items-center justify-center text-center" style="border-color: #000 !important;">
                    <p class="text-[8.5px] mt-1 max-w-xl leading-tight" style="color: #475569 !important;">
                        İşbu rapor SASA Tarım ERP sistemi tarafından üretilmiştir.
                    </p>
                    <div class="text-[8.5px] font-mono mt-0.5" style="color: #94a3b8 !important;">1 / 1</div>
                </div>

                <!-- DÖKÜMAN EYLEMLERİ -->
                <div class="no-print flex justify-between items-center pt-4 mt-4 border-t border-slate-200">
                    <button @click="showGenericPrintModal = false" class="px-4 py-2 text-xs border rounded-xl font-bold hover:bg-slate-100 transition">Kapat</button>
                    <button @click="printPage()" class="px-5 py-2 text-xs bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold transition flex items-center gap-2 shadow-sm">
                        Belge Çıktısını Al (A4 Yazdır)
                    </button>
                </div>
            </div>
        </div>
        <!-- TANK HAZIRLAMA KAYDI MODALI -->
        <div v-if="showTankLogModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Tank Yeniden Hazırlama Kaydı</h3>
                        <p class="text-[11px] text-slate-500">{{ tankLogForm.tank_name }} solüsyon yenileme</p>
                    </div>
                    <button @click="showTankLogModal = false" class="text-slate-400 hover:text-slate-700 font-bold text-xl leading-none">&times;</button>
                </div>

                <form @submit.prevent="submitTankLogForm" class="space-y-3.5 text-xs">
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 rounded-xl border border-emerald-200 dark:border-emerald-900 text-emerald-900 dark:text-emerald-300 text-[11px] space-y-1">
                        <div class="font-bold">
                            DIA Stok Entegrasyonu:
                        </div>
                        <p class="text-[10px] text-emerald-700 dark:text-emerald-400">Bu kaydı oluşturduğunuzda, reçetede tanımlı {{ tankLogForm.tank_name }} içerisindeki tüm gübreler DIA stoklarından otomatik olarak düşülür.</p>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Hazırlanan Tank Adı</label>
                        <input v-model="tankLogForm.tank_name" type="text" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-extrabold" required readonly />
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Hazırlama Tarihi ve Saati *</label>
                        <input v-model="tankLogForm.prepared_at" type="datetime-local" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-bold" required />
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Hazırlayan Görevli Personel *</label>
                        <select v-model="tankLogForm.prepared_by_id" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 font-medium" required>
                            <option v-for="p in personnels" :key="p.id" :value="p.id">{{ p.first_name }} {{ p.last_name }} ({{ p.department || 'Personel' }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold mb-1">Saha Notları / Açıklama</label>
                        <textarea v-model="tankLogForm.notes" rows="2" class="w-full border rounded-xl p-2.5 dark:bg-slate-950 text-xs" placeholder="Örn: 1000L tank tam dolduruldu, karıştırıcı motor çalıştırıldı..."></textarea>
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="showTankLogModal = false" class="px-4 py-2 border rounded-xl font-bold">İptal</button>
                        <button type="submit" :disabled="tankLogForm.processing" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-xs transition">
                            Kaydet & Stoktan Düş
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ÇALIŞAN TANK HAZIRLAMA TALİMATI ÇIKTI MODALI -->
        <div v-if="showTankPrintModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 z-50">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl w-full max-w-3xl p-6 overflow-y-auto max-h-[90vh]">
                <div class="no-print flex justify-between items-center pb-3 mb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100">Çalışan Tank Hazırlama Talimat Çıktısı</h3>
                        <p class="text-[11px] text-slate-500">{{ tankPrintData?.tank?.tank_name }} için sahaya verilecek basılı reçete belgesi</p>
                    </div>
                    <button @click="showTankPrintModal = false" class="text-slate-400 hover:text-slate-700 font-bold text-2xl leading-none">&times;</button>
                </div>

                <div id="tank-print-document" class="print-document bg-white p-6 rounded-xl border border-slate-300 text-slate-900 space-y-4">
                    <div class="flex justify-between items-center border-b-2 border-slate-900 pb-3">
                        <div class="flex items-center gap-3">
                            <img src="/sasaerp.svg" alt="SASA Logo" class="h-12 w-auto" />
                            <div>
                                <div class="text-[10px] font-bold tracking-widest text-slate-500">KURUMSAL ERP SİSTEMİ</div>
                                <div class="text-xs font-black text-slate-900">SASA TARIM İŞLETMELERİ A.Ş.</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900">TANK SOLÜSYON HAZIRLAMA REÇETESİ</h2>
                            <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">SAHA İŞÇİ TALİMAT DÖKÜMÜ</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <div>
                            <div><strong>GÜBRE REÇETESİ:</strong> {{ tankPrintData?.recipe?.name || 'Reçete' }}</div>
                            <div><strong>HAZIRLANACAK TANK:</strong> <span class="font-black text-indigo-700">{{ tankPrintData?.tank?.tank_name }}</span></div>
                            <div><strong>TANK KAPASİTESİ:</strong> <span class="font-bold">{{ tankPrintData?.tank?.capacity_liters }} Litre Su</span></div>
                        </div>
                        <div class="text-right">
                            <div><strong>DÖKÜM TARİHİ:</strong> {{ new Date().toLocaleDateString('tr-TR') }}</div>
                            <div><strong>SU pH / EC HEDEFİ:</strong> pH: {{ tankPrintData?.recipe?.water_ph || '6.5' }} | EC: {{ tankPrintData?.recipe?.water_ec || '1.8' }} mS/cm</div>
                            <div><strong>BİTİŞ ŞARTI:</strong> {{ tankPrintData?.run?.end_condition || tankPrintData?.recipe?.duration_condition || '-' }}</div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-black uppercase tracking-wider mb-1.5 text-slate-800">TANKA KONULACAK GÜBRE VE SOLÜSYON İÇERİKLERİ</h3>
                        <table class="w-full border-collapse border border-slate-900 text-xs text-left">
                            <thead class="bg-slate-200 font-bold uppercase text-[10px]">
                                <tr>
                                    <th class="border border-slate-900 p-2">#</th>
                                    <th class="border border-slate-900 p-2">Gübre Ürünü</th>
                                    <th class="border border-slate-900 p-2">Marka</th>
                                    <th class="border border-slate-900 p-2 text-center">Konulacak Miktar</th>
                                    <th class="border border-slate-900 p-2">Kullanım Amacı</th>
                                    <th class="border border-slate-900 p-2">Açıklama / Not</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(it, idx) in tankPrintData?.tank?.items" :key="idx" class="border-b border-slate-900">
                                    <td class="border border-slate-900 p-2 font-bold text-center">{{ idx + 1 }}</td>
                                    <td class="border border-slate-900 p-2 font-extrabold text-slate-900">{{ it.product_name }}</td>
                                    <td class="border border-slate-900 p-2">{{ it.brand || '-' }}</td>
                                    <td class="border border-slate-900 p-2 font-black text-center text-indigo-900 bg-slate-50">{{ it.quantity }} {{ it.unit }}</td>
                                    <td class="border border-slate-900 p-2 font-semibold">{{ it.usage_purpose || '-' }}</td>
                                    <td class="border border-slate-900 p-2 text-[11px]">{{ it.description || '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs space-y-1 text-amber-900">
                        <div class="font-bold">SAHA HAZIRLAMA VE GÜVENLİK TALİMATLARI:</div>
                        <ol class="list-decimal list-inside text-[11px] space-y-0.5">
                            <li>Tankı önce %80 oranında temiz su ile doldurunuz.</li>
                            <li>Listede verilen gübreleri yukarıdaki gramajlara göre hassas terazide tartarak ekleyiniz.</li>
                            <li>Tüm gübreler tamamen eriyene kadar karıştırıcıyı en az 15 dakika çalıştırınız.</li>
                            <li>Eldiven ve koruyucu maske takılması zorunludur.</li>
                        </ol>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-xs pt-4 border-t border-slate-300">
                        <div class="border border-dashed p-3 rounded-lg text-center h-20 flex flex-col justify-between">
                            <span class="font-bold uppercase">HAZIRLAYAN GÖREVLİ İMZA</span>
                            <span class="text-[10px] text-slate-500">Tarih: ..... / ..... / 2026</span>
                        </div>
                        <div class="border border-dashed p-3 rounded-lg text-center h-20 flex flex-col justify-between">
                            <span class="font-bold uppercase">ZİRAAT MÜHENDİSİ ONAY</span>
                            <span class="text-[10px] text-slate-500">İmza & Kaşe</span>
                        </div>
                    </div>
                </div>

                <div class="no-print flex justify-between items-center pt-4 mt-4 border-t border-slate-200">
                    <button @click="showTankPrintModal = false" class="px-4 py-2 text-xs border rounded-xl font-bold">Kapat</button>
                    <button @click="printPage('tank-print-document')" class="px-5 py-2 text-xs bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl font-bold transition flex items-center gap-2 shadow-xs">
                        Çalışan Talimat Belgesini Yazdır (A4)
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* Karanlık Modda Çıktı Önizlemelerini Beyaz/Siyah Zorlaması */
.print-document {
    background-color: #ffffff !important;
    color: #0f172a !important;
}
.print-document *, .print-document table, .print-document td, .print-document th {
    border-color: #0f172a !important;
}
.print-document h1, .print-document h2, .print-document h3, .print-document p, .print-document span, .print-document div, .print-document td, .print-document th, .print-document strong {
    color: #0f172a !important;
}
.print-document .text-slate-500 { color: #64748b !important; }
.print-document .text-slate-600 { color: #475569 !important; }
.print-document .text-slate-400 { color: #94a3b8 !important; }
.print-document .text-teal-800 { color: #115e59 !important; }
.print-document .bg-slate-50 { background-color: #f8fafc !important; }

@media print {
    @page {
        size: A4 portrait;
        margin: 10mm 12mm;
    }
    body {
        background: white !important;
        color: black !important;
    }
    .no-print {
        display: none !important;
    }
    nav, aside, header, footer {
        display: none !important;
    }
    .fixed.inset-0 {
        position: static !important;
        background: transparent !important;
        padding: 0 !important;
        display: block !important;
    }
    .print-document {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        box-shadow: none !important;
        border: none !important;
    }
}
</style>
