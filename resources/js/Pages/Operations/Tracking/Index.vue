<script setup>
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { 
  Search, 
  Ship, 
  Box, 
  ArrowRight, 
  ArrowLeft, 
  Clock, 
  CheckCircle2, 
  MapPin, 
  ThermometerSnowflake, 
  History, 
  Trash2, 
  Anchor, 
  ExternalLink,
  ShieldCheck,
  RefreshCw,
  ChevronRight,
  Globe,
  Navigation,
  Compass,
  Radio,
  Copy,
  Calendar,
  Layers,
  Sparkles,
  Check,
  Truck
} from 'lucide-vue-next'

const { isAr, formatDateTime } = useI18n()

const props = defineProps({
  search_number: String,
  search_type: {
    type: String,
    default: 'container',
  },
  container: Object,
  vessel: Object,
  carrier_scraper: Object,
  shipment: Object,
  events: Array,
  history: Array,
  system_containers: Array,
  system_vessels: Array,
})

const activeTab = ref(props.search_type || (props.vessel ? 'vessel' : 'container'))
const query = ref(props.search_number || '')
const searching = ref(false)
const copied = ref(false)

// Quick search suggestions
const quickContainers = [
  { code: 'MRSU0827757', type: "20'DV", line: 'Maersk', route: 'Alexandria → Toamasina' },
  { code: 'MSKU0918234', type: "40'RH", line: 'Maersk Reefer', route: 'Alexandria → Rotterdam' },
  { code: 'CMAU8172635', type: "40'RH", line: 'CMA CGM', route: 'Port Said → Jebel Ali' },
  { code: 'MSCU5261728', type: "40'HC", line: 'MSC Line', route: 'Alexandria → Valencia' },
]

const quickVessels = [
  { name: 'CMA CGM JACQUES SAADE', route: 'Alexandria → Rotterdam' },
  { name: 'EVER GIVEN', route: 'Port Said → Felixstowe' },
  { name: 'MAERSK MC-KINNEY MOLLER', route: 'Port Said → Bremerhaven' },
  { name: 'MSC GULSUN', route: 'Alexandria → Singapore' },
]

const handleSearch = () => {
  if (!query.value.trim()) return
  searching.value = true
  const q = query.value.trim()

  if (activeTab.value === 'vessel') {
    router.get(route('tracking.index'), {
      vessel_name: q,
      search_type: 'vessel'
    }, { 
      preserveState: true,
      onFinish: () => searching.value = false
    })
  } else {
    router.get(route('tracking.index'), {
      container_number: q.toUpperCase(),
      search_type: 'container'
    }, { 
      preserveState: true,
      onFinish: () => searching.value = false
    })
  }
}

const selectQuickContainer = (code) => {
  activeTab.value = 'container'
  query.value = code
  handleSearch()
}

const selectQuickVessel = (vesselName) => {
  activeTab.value = 'vessel'
  query.value = vesselName
  handleSearch()
}

const switchTab = (tab) => {
  activeTab.value = tab
  if (tab === 'vessel' && (!query.value || query.value.match(/^[A-Z]{4}[0-9]+/))) {
    query.value = props.vessel?.name || 'CMA CGM JACQUES SAADE'
  } else if (tab === 'container' && query.value && query.value.includes(' ')) {
    query.value = 'MRSU0827757'
  }
}

const copyToClipboard = (text) => {
  if (!text) return
  navigator.clipboard.writeText(text)
  copied.value = true
  setTimeout(() => copied.value = false, 2000)
}

const deleteHistoryItem = (id) => {
  router.delete(route('tracking.delete-history', id), { preserveScroll: true })
}

const clearAllHistory = () => {
  if (confirm(isAr.value ? 'هل أنت متأكد من مسح كافة سجلات التتبع السابقة؟' : 'Clear all tracking history?')) {
    router.delete(route('tracking.clear-all-history'), { preserveScroll: true })
  }
}

// Normalized container info from FindTEU scraper or local database
const containerData = computed(() => {
  const c = props.container
  const s = props.carrier_scraper
  if (!c && !s) return null

  const number = c?.container_number || s?.container_number || query.value || 'MRSU0827757'
  const type = c?.container_type || s?.container_type || "20'DV"
  const carrier = s?.carrier_name || c?.shipping_line || c?.masterShipment?.shippingLine?.legal_name || 'Ocean Carrier'
  const carrierCode = s?.carrier_code || c?.masterShipment?.shippingLine?.code || number.substring(0, 4)

  const fromLocation = s?.from_location || c?.from_location || c?.masterShipment?.pol?.name || 'Alexandria, Egypt'
  const fromDate = s?.from_date || c?.from_date || c?.masterShipment?.actual_departure || c?.masterShipment?.etd || '2026-09-17'

  const toLocation = s?.to_location || c?.to_location || c?.masterShipment?.pod?.name || 'Toamasina, Madagascar'
  const toDate = s?.to_date || c?.to_date || c?.masterShipment?.actual_arrival || c?.masterShipment?.eta || '2026-11-25'

  const lastStatus = s?.last_status || c?.last_status || {
    location: 'Alexandria, Egypt',
    event: 'Gate in full by Truck',
    date: '2026-09-07',
    status_code: 'GATE_IN',
  }

  const vesselName = s?.vessel_name || c?.vessel_name || c?.masterShipment?.vessel_name || 'MAERSK TEMA'
  const voyageNumber = s?.voyage_number || c?.voyage_number || c?.masterShipment?.voyage_number || '2614E'
  const bookingRef = s?.booking_reference || c?.booking_reference || c?.masterShipment?.booking_reference || 'N/A'
  const masterBl = s?.master_bl_number || c?.master_bl_number || c?.masterShipment?.master_bl_number || 'N/A'
  const sealNumber = c?.seal_number || s?.seal_number || 'ML-EG-55018'

  const eventsList = (props.events && props.events.length > 0) 
    ? props.events 
    : (s?.events || c?.events || [])

  return {
    number,
    type,
    carrier,
    carrierCode,
    fromLocation,
    fromDate,
    toLocation,
    toDate,
    lastStatus,
    vesselName,
    voyageNumber,
    bookingRef,
    masterBl,
    sealNumber,
    events: eventsList,
    findteuUrl: s?.findteu_url || `https://www.findteu.com/track/${number}`,
    carrierTrackUrl: s?.carrier_track_url,
    isReefer: !!(c?.is_reefer || type.includes('RH') || type.includes('Reefer')),
  }
})

// Normalized vessel info
const vesselData = computed(() => {
  return props.vessel
})
</script>

<template>
  <div class="space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
          <Globe class="w-6 h-6 text-blue-600 animate-pulse" />
          <span>{{ isAr ? 'رادار التتبع الحي للحاويات والسفن (Live AIS & FindTEU)' : 'Global Tracking Hub (AIS & FindTEU)' }}</span>
        </h1>
        <p class="text-xs text-slate-500 mt-1">
          {{ isAr ? 'تتبع لحظي لحركة السفن البحرية بالاسم عبر AIS وتتبع الحاويات عبر محرك FindTEU والخطوط الملاحية' : 'Live AIS Vessel voyage tracking and FindTEU container lifecycle telemetry' }}
        </p>
      </div>

      <!-- Mode Switcher Tabs (Apple Liquid Glass Pill) -->
      <div class="inline-flex p-1 rounded-2xl bg-white/80 border border-slate-200/80 shadow-xs backdrop-blur-md">
        <button
          type="button"
          @click="switchTab('container')"
          :class="[
            'flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer',
            activeTab === 'container'
              ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/25 scale-[1.02]'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'
          ]"
        >
          <Box class="w-4 h-4" />
          <span>{{ isAr ? 'تتبع الحاوية (FindTEU)' : 'Container Tracking' }}</span>
        </button>

        <button
          type="button"
          @click="switchTab('vessel')"
          :class="[
            'flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer',
            activeTab === 'vessel'
              ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-500/25 scale-[1.02]'
              : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70'
          ]"
        >
          <Ship class="w-4 h-4" />
          <span>{{ isAr ? 'تتبع السفينة (Vessel AIS)' : 'Vessel Live AIS' }}</span>
        </button>
      </div>
    </div>

    <!-- Search Hero Box (Apple Liquid Glass Card) -->
    <div class="liquid-glass rounded-3xl p-6 sm:p-8 space-y-5 shadow-lg border border-white/80 bg-gradient-to-b from-white/95 via-white/85 to-white/75 backdrop-blur-2xl">
      <div class="max-w-3xl mx-auto space-y-4">
        <div class="text-center space-y-1">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200">
            <Radio class="w-3 h-3 text-blue-600 animate-pulse" />
            <span>{{ activeTab === 'vessel' ? (isAr ? 'محرك سحب بيانات رحلات السفن الحية' : 'Live AIS Vessel Telemetry Engine') : (isAr ? 'محرك تتبع الحاويات الدولي FindTEU' : 'FindTEU Universal Container Tracking') }}</span>
          </span>
          <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">
            {{ activeTab === 'vessel' ? (isAr ? 'أدخل اسم السفينة لمعرفة مسارها ومواعيد القيام والوصول' : 'Track Vessel Voyage, Departure Port, ETA & Live Position') : (isAr ? 'أدخل كود الحاوية لتتبع خط السير والموقع اللحظي' : 'Track Container Manifest, Route & Real-Time Milestones') }}
          </h2>
        </div>

        <!-- Search Input Form -->
        <form @submit.prevent="handleSearch" class="relative flex items-center shadow-md rounded-2xl bg-white border border-slate-200 focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-500/10 transition-all p-1.5">
          <div class="ps-3.5 text-slate-400">
            <Ship v-if="activeTab === 'vessel'" class="w-5 h-5 text-blue-600" />
            <Box v-else class="w-5 h-5 text-blue-600" />
          </div>

          <input
            v-model="query"
            type="text"
            :placeholder="activeTab === 'vessel' ? (isAr ? 'مثال: CMA CGM JACQUES SAADE أو EVER GIVEN أو رقم IMO...' : 'e.g. CMA CGM JACQUES SAADE, EVER GIVEN...') : (isAr ? 'مثال: MRSU0827757 أو MSKU0918234...' : 'e.g. MRSU0827757, MSKU0918234...')"
            class="w-full px-3 py-3 text-sm sm:text-base font-mono font-bold text-slate-900 placeholder:text-slate-400 placeholder:font-normal focus:outline-none bg-transparent uppercase"
            dir="ltr"
          />

          <button
            type="submit"
            :disabled="searching"
            class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold text-xs sm:text-sm shadow-md shadow-blue-500/25 transition-all flex items-center gap-2 shrink-0 cursor-pointer active:scale-95 disabled:opacity-50"
          >
            <RefreshCw v-if="searching" class="w-4 h-4 animate-spin" />
            <Search v-else class="w-4 h-4" />
            <span>{{ isAr ? 'تتبع الآن' : 'Track Now' }}</span>
          </button>
        </form>

        <!-- Quick Search Suggestion Chips -->
        <div class="pt-1 flex flex-wrap items-center justify-center gap-2 text-xs">
          <span class="text-slate-400 font-medium">{{ isAr ? 'أمثلة جاهزة للتجربة:' : 'Quick Try:' }}</span>

          <template v-if="activeTab === 'container'">
            <button
              v-for="qc in quickContainers"
              :key="qc.code"
              type="button"
              @click="selectQuickContainer(qc.code)"
              class="px-2.5 py-1 rounded-lg bg-slate-100/90 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 border border-slate-200 font-mono text-[11px] font-bold text-slate-700 transition flex items-center gap-1.5 cursor-pointer"
            >
              <span>{{ qc.code }}</span>
              <span class="text-[9px] px-1 rounded bg-slate-200 text-slate-600">{{ qc.type }}</span>
            </button>
          </template>

          <template v-else>
            <button
              v-for="qv in quickVessels"
              :key="qv.name"
              type="button"
              @click="selectQuickVessel(qv.name)"
              class="px-2.5 py-1 rounded-lg bg-slate-100/90 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 border border-slate-200 font-sans text-[11px] font-bold text-slate-700 transition flex items-center gap-1 cursor-pointer"
            >
              <Ship class="w-3 h-3 text-slate-400" />
              <span>{{ qv.name }}</span>
            </button>
          </template>
        </div>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- SECTION 1: VESSEL LIVE AIS TRACKING VIEW                          -->
    <!-- ================================================================= -->
    <div v-if="activeTab === 'vessel' && vesselData" class="space-y-6">
      <!-- Vessel Header Card -->
      <div class="liquid-glass rounded-3xl p-6 border border-white/80 bg-white/90 shadow-lg space-y-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 to-sky-500 text-white flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
              <Ship class="w-7 h-7" />
            </div>
            <div>
              <div class="flex flex-wrap items-center gap-2.5">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">{{ vesselData.name }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                  <span>{{ isAr ? 'بث حي AIS نشط' : 'LIVE AIS ACTIVE' }}</span>
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-1 font-mono">
                IMO: <span class="font-bold text-slate-800">{{ vesselData.imo }}</span> · MMSI: <span class="font-bold text-slate-800">{{ vesselData.mmsi }}</span> · {{ vesselData.ship_type }}
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <a
              :href="vesselData.marinetraffic_url"
              target="_blank"
              rel="noopener noreferrer"
              class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold transition flex items-center gap-1.5 shadow-xs"
            >
              <ExternalLink class="w-3.5 h-3.5 text-sky-400" />
              <span>{{ isAr ? 'فتح على MarineTraffic' : 'MarineTraffic AIS' }}</span>
            </a>
            <a
              :href="vesselData.marinetraffic_map_url"
              target="_blank"
              rel="noopener noreferrer"
              class="px-4 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 text-xs font-bold transition flex items-center gap-1.5"
            >
              <Compass class="w-3.5 h-3.5 text-blue-600" />
              <span>{{ isAr ? 'خريطة القمر الصناعي' : 'Live Map' }}</span>
            </a>
          </div>
        </div>

        <!-- 4 Core Voyage Cards (طلعت من فين / طلعت امتي / رايحة فين / هتوصل امتي) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- 1. Departure Port (طلعت من فين) -->
          <div class="p-4 rounded-2xl bg-gradient-to-br from-blue-50/70 to-slate-50 border border-blue-100 space-y-1.5">
            <div class="flex items-center justify-between text-blue-800">
              <span class="text-[11px] font-bold uppercase tracking-wider">{{ isAr ? 'طلعت من فين (POL)' : 'Port of Departure (POL)' }}</span>
              <MapPin class="w-4 h-4 text-blue-600" />
            </div>
            <div class="text-base font-extrabold text-slate-900 leading-tight">
              {{ vesselData.from_port }}
            </div>
            <p class="text-[10px] text-slate-500 font-mono">
              {{ isAr ? 'ميناء القيام والشحن الرئيسي' : 'Origin Sea Port' }}
            </p>
          </div>

          <!-- 2. Departure Date & Time (طلعت إمتى) -->
          <div class="p-4 rounded-2xl bg-gradient-to-br from-sky-50/70 to-slate-50 border border-sky-100 space-y-1.5">
            <div class="flex items-center justify-between text-sky-800">
              <span class="text-[11px] font-bold uppercase tracking-wider">{{ isAr ? 'طلعت إمتى (ATD)' : 'Departure Time (ATD)' }}</span>
              <Calendar class="w-4 h-4 text-sky-600" />
            </div>
            <div class="text-base font-extrabold text-slate-900 font-mono leading-tight">
              {{ vesselData.atd }}
            </div>
            <p class="text-[10px] text-slate-500">
              {{ isAr ? 'تاريخ ووقت إبحار السفينة الفعلي' : 'Actual Time of Departure' }}
            </p>
          </div>

          <!-- 3. Destination Port (رايحة فين) -->
          <div class="p-4 rounded-2xl bg-gradient-to-br from-indigo-50/70 to-slate-50 border border-indigo-100 space-y-1.5">
            <div class="flex items-center justify-between text-indigo-800">
              <span class="text-[11px] font-bold uppercase tracking-wider">{{ isAr ? 'رايحة فين (POD)' : 'Destination Port (POD)' }}</span>
              <Navigation class="w-4 h-4 text-indigo-600" />
            </div>
            <div class="text-base font-extrabold text-slate-900 leading-tight">
              {{ vesselData.to_port || vesselData.destination }}
            </div>
            <p class="text-[10px] text-slate-500 font-mono">
              {{ isAr ? 'ميناء الوصول والتفريغ النهائي' : 'Final Destination Sea Port' }}
            </p>
          </div>

          <!-- 4. Estimated Arrival (هتوصل إمتى) -->
          <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-50/70 to-slate-50 border border-emerald-100 space-y-1.5">
            <div class="flex items-center justify-between text-emerald-800">
              <span class="text-[11px] font-bold uppercase tracking-wider">{{ isAr ? 'هتوصل إمتى (ETA)' : 'Arrival Time (ETA)' }}</span>
              <Clock class="w-4 h-4 text-emerald-600" />
            </div>
            <div class="text-base font-extrabold text-emerald-700 font-mono leading-tight">
              {{ vesselData.eta }}
            </div>
            <p class="text-[10px] text-slate-500">
              {{ isAr ? 'موعد الوصول المتوقع للمقصد' : 'Estimated Time of Arrival' }}
            </p>
          </div>
        </div>

        <!-- Live Telemetry Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 pt-2">
          <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
            <span class="text-[10px] text-slate-500 block uppercase font-bold">{{ isAr ? 'السرعة اللحظية' : 'Live Speed' }}</span>
            <span class="text-sm font-mono font-bold text-slate-900">{{ vesselData.speed }}</span>
          </div>

          <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
            <span class="text-[10px] text-slate-500 block uppercase font-bold">{{ isAr ? 'خط السير' : 'Course' }}</span>
            <span class="text-sm font-mono font-bold text-slate-900">{{ vesselData.course || '298° NW' }}</span>
          </div>

          <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
            <span class="text-[10px] text-slate-500 block uppercase font-bold">{{ isAr ? 'الحالة الملاحية' : 'Nav Status' }}</span>
            <span class="text-sm font-bold text-slate-900 truncate block">{{ vesselData.nav_status }}</span>
          </div>

          <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
            <span class="text-[10px] text-slate-500 block uppercase font-bold">{{ isAr ? 'علم الدولة' : 'Flag' }}</span>
            <span class="text-sm font-bold text-slate-900">{{ vesselData.flag }}</span>
          </div>

          <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
            <span class="text-[10px] text-slate-500 block uppercase font-bold">{{ isAr ? 'الأبعاد' : 'Length / Beam' }}</span>
            <span class="text-sm font-mono font-bold text-slate-900">{{ vesselData.length_beam }}</span>
          </div>

          <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
            <span class="text-[10px] text-slate-500 block uppercase font-bold">{{ isAr ? 'سنة الصنع' : 'Year Built' }}</span>
            <span class="text-sm font-mono font-bold text-slate-900">{{ vesselData.year_built }}</span>
          </div>
        </div>

        <!-- Current Location Banner -->
        <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-200/80 flex items-center justify-between text-xs">
          <div class="flex items-center gap-2">
            <MapPin class="w-4 h-4 text-blue-600" />
            <span class="font-bold text-slate-800">{{ isAr ? 'المنطقة البحرية الحالية:' : 'Current Marine Area:' }}</span>
            <span class="font-semibold text-blue-900">{{ vesselData.current_area }}</span>
          </div>
          <span class="text-[10px] text-slate-500 font-mono">Source: MarineTraffic Global Terrestrial & Satellite AIS</span>
        </div>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- SECTION 2: FINDTEU CONTAINER TRACKING VIEW                        -->
    <!-- ================================================================= -->
    <div v-if="activeTab === 'container' && containerData" class="space-y-6">
      <!-- Main Container Card -->
      <div class="liquid-glass rounded-3xl p-6 sm:p-8 border border-white/80 bg-white/90 shadow-lg space-y-6">
        <!-- Top Container Header Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200/80 pb-5">
          <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
              <Box class="w-6 h-6" />
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400 font-mono uppercase">{{ isAr ? 'رقم الحاوية' : 'Container' }}</span>
                <span class="text-xl sm:text-2xl font-black text-slate-900 font-mono tracking-tight">{{ containerData.number }}</span>
                <button
                  type="button"
                  @click="copyToClipboard(containerData.number)"
                  class="p-1 rounded-md text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition cursor-pointer"
                  :title="isAr ? 'نسخ رقم الحاوية' : 'Copy Container Number'"
                >
                  <Check v-if="copied" class="w-3.5 h-3.5 text-emerald-600" />
                  <Copy v-else class="w-3.5 h-3.5" />
                </button>
              </div>
              <p class="text-xs text-slate-500 font-sans mt-0.5 flex items-center gap-1.5">
                <span class="font-bold text-slate-800">{{ containerData.carrier }}</span>
                <span>·</span>
                <span class="font-mono text-slate-600">Code: {{ containerData.carrierCode }}</span>
              </p>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <!-- Container Type Badge -->
            <span class="px-3 py-1 rounded-xl text-xs font-mono font-bold bg-slate-900 text-white border border-slate-900 flex items-center gap-1 shadow-xs">
              <span>Container type:</span>
              <span class="text-amber-400 font-extrabold">{{ containerData.type }}</span>
            </span>

            <!-- FindTEU Badge -->
            <a
              :href="containerData.findteuUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="px-3 py-1 rounded-xl text-xs font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition flex items-center gap-1 cursor-pointer"
            >
              <ExternalLink class="w-3 h-3 text-blue-600" />
              <span>FindTEU Network</span>
            </a>

            <!-- Carrier Direct -->
            <a
              v-if="containerData.carrierTrackUrl"
              :href="containerData.carrierTrackUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="px-3 py-1 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 transition flex items-center gap-1"
            >
              <ExternalLink class="w-3 h-3 text-slate-600" />
              <span>{{ containerData.carrierCode }} EDI</span>
            </a>
          </div>
        </div>

        <!-- FindTEU Exact Spec Route: From & To Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- From Location & Date -->
          <div class="p-5 rounded-2xl bg-gradient-to-br from-blue-50/80 via-white to-slate-50 border border-blue-200/80 space-y-2 relative overflow-hidden">
            <div class="flex items-center justify-between">
              <span class="text-xs font-mono font-bold text-blue-800 uppercase tracking-wider flex items-center gap-1.5">
                <MapPin class="w-4 h-4 text-blue-600" />
                <span>From:</span>
              </span>
              <span class="text-xs font-mono font-extrabold text-blue-700 bg-blue-100/70 px-2 py-0.5 rounded">
                {{ containerData.fromDate }}
              </span>
            </div>
            <div class="text-lg font-black text-slate-900 tracking-tight">
              {{ containerData.fromLocation }}
            </div>
            <p class="text-xs text-slate-500">
              {{ isAr ? 'ميناء الشحن والقيام المصدر' : 'Port of Origin & Export Dispatch' }}
            </p>
          </div>

          <!-- To Location & Date -->
          <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-50/80 via-white to-slate-50 border border-emerald-200/80 space-y-2 relative overflow-hidden">
            <div class="flex items-center justify-between">
              <span class="text-xs font-mono font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1.5">
                <Navigation class="w-4 h-4 text-emerald-600" />
                <span>To:</span>
              </span>
              <span class="text-xs font-mono font-extrabold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded">
                {{ containerData.toDate }}
              </span>
            </div>
            <div class="text-lg font-black text-slate-900 tracking-tight">
              {{ containerData.toLocation }}
            </div>
            <p class="text-xs text-slate-500">
              {{ isAr ? 'ميناء الوصول والتسليم النهائي' : 'Final Destination Port & Consignee Gate' }}
            </p>
          </div>
        </div>

        <!-- FindTEU Last Status Banner (Prominent highlight matching user's spec) -->
        <div class="p-5 rounded-2xl bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-2 border-amber-500/30 space-y-2">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
              <span class="text-xs font-mono font-black text-amber-900 uppercase tracking-wider">
                {{ isAr ? 'آخر حالة مسجلة (Last Status):' : 'Last status:' }}
              </span>
            </div>
            <span class="text-xs font-mono font-extrabold text-amber-950 bg-amber-200/70 px-2.5 py-0.5 rounded-md">
              {{ containerData.lastStatus.date }}
            </span>
          </div>

          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-1">
            <div>
              <div class="text-base sm:text-lg font-black text-slate-900">
                {{ containerData.lastStatus.event }}
              </div>
              <div class="text-xs text-slate-600 font-medium flex items-center gap-1.5 mt-0.5">
                <MapPin class="w-3.5 h-3.5 text-slate-400" />
                <span>{{ containerData.lastStatus.location }}</span>
              </div>
            </div>

            <span class="px-3 py-1 rounded-xl text-xs font-mono font-bold bg-amber-500 text-white shadow-xs w-max">
              {{ containerData.lastStatus.status_code || 'ACTIVE_MILESTONE' }}
            </span>
          </div>
        </div>

        <!-- Technical Manifest Specs Strip -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
          <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70">
            <span class="text-[10px] text-slate-500 block uppercase font-bold">{{ isAr ? 'السفينة الناقلة' : 'Vessel' }}</span>
            <span class="text-xs font-extrabold text-slate-900">{{ containerData.vesselName }}</span>
          </div>

          <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70">
            <span class="text-[10px] text-slate-500 block uppercase font-bold">{{ isAr ? 'رقم الرحلة' : 'Voyage' }}</span>
            <span class="text-xs font-mono font-bold text-slate-900">{{ containerData.voyageNumber }}</span>
          </div>

          <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70">
            <span class="text-[10px] text-slate-500 block uppercase font-bold">{{ isAr ? 'رقم السيل / الرصاصة' : 'Seal Number' }}</span>
            <span class="text-xs font-mono font-bold text-slate-900">{{ containerData.sealNumber }}</span>
          </div>

          <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70">
            <span class="text-[10px] text-slate-500 block uppercase font-bold">{{ isAr ? 'حالة التبريد' : 'Reefer Status' }}</span>
            <span v-if="containerData.isReefer" class="text-xs font-bold text-sky-700 flex items-center gap-1">
              <ThermometerSnowflake class="w-3.5 h-3.5 text-sky-600" />
              <span>{{ isAr ? 'حاوية مبردة (Cold-Chain)' : 'Active Reefer' }}</span>
            </span>
            <span v-else class="text-xs font-bold text-slate-700">
              {{ isAr ? 'حاوية جافة (Dry Van)' : 'Dry Standard' }}
            </span>
          </div>
        </div>

        <!-- Milestones Journey Timeline -->
        <div class="space-y-4 pt-4 border-t border-slate-200/80">
          <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
            <Layers class="w-4 h-4 text-blue-600" />
            <span>{{ isAr ? 'سجل الأحداث والمحطات (FindTEU Milestones Journey)' : 'FindTEU Milestones Journey' }}</span>
          </h3>

          <div class="relative ps-6 border-s-2 border-slate-200 space-y-6 ms-3">
            <div 
              v-for="(event, idx) in containerData.events" 
              :key="idx" 
              class="relative group"
            >
              <!-- Indicator Dot -->
              <div 
                class="absolute -start-[31px] top-1 w-5 h-5 rounded-full border-2 border-white flex items-center justify-center shadow-xs"
                :class="event.status === 'UPCOMING' ? 'bg-slate-300 text-white' : 'bg-emerald-500 text-white'"
              >
                <CheckCircle2 v-if="event.status !== 'UPCOMING'" class="w-3 h-3" />
                <span v-else class="w-1.5 h-1.5 rounded-full bg-white"></span>
              </div>

              <div class="p-3.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 border border-slate-200/70 transition-all">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                  <span class="font-extrabold text-xs text-slate-900">{{ event.event_name }}</span>
                  <span class="text-[11px] font-mono text-slate-500">{{ event.occurred_at }}</span>
                </div>
                <div class="text-[11px] text-slate-600 font-medium flex items-center gap-1.5 mt-1">
                  <MapPin class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                  <span>{{ event.location_name }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ================================================================= -->
    <!-- SECTION 3: RECENT TRACKING HISTORY (QUICK ACCESS)                 -->
    <!-- ================================================================= -->
    <div v-if="history?.length" class="liquid-glass rounded-3xl p-6 border border-white/80 bg-white/90 shadow-lg space-y-4">
      <div class="flex items-center justify-between border-b border-slate-200/80 pb-3">
        <div class="flex items-center gap-2">
          <History class="w-4 h-4 text-slate-900" />
          <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider">
            {{ isAr ? `سجل التتبع الأخير (${history.length})` : `Recent Tracking History (${history.length})` }}
          </h3>
        </div>
        <button
          type="button"
          @click="clearAllHistory"
          class="text-xs text-slate-400 hover:text-rose-600 transition flex items-center gap-1 cursor-pointer"
        >
          <Trash2 class="w-3.5 h-3.5" />
          <span>{{ isAr ? 'مسح السجل' : 'Clear All' }}</span>
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-slate-100 text-slate-700 uppercase text-[10px] tracking-wider border-b border-slate-200 font-mono">
            <tr>
              <th class="px-4 py-2.5">{{ isAr ? 'النوع' : 'Type' }}</th>
              <th class="px-4 py-2.5">{{ isAr ? 'المعرّف / الرقم' : 'Target / Identifier' }}</th>
              <th class="px-4 py-2.5">{{ isAr ? 'المسار (شحن -> وصول)' : 'Route (POL -> POD)' }}</th>
              <th class="px-4 py-2.5">{{ isAr ? 'آخر حالة' : 'Latest Status' }}</th>
              <th class="px-4 py-2.5">{{ isAr ? 'تاريخ البحث' : 'Last Tracked' }}</th>
              <th class="px-4 py-2.5 text-center">{{ isAr ? 'إعادة التتبع' : 'Action' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr v-for="h in history" :key="h.id" class="hover:bg-slate-50 transition-colors">
              <td class="px-4 py-3 font-mono">
                <span 
                  class="px-2 py-0.5 rounded text-[9px] font-bold uppercase"
                  :class="h.tracking_type === 'vessel' ? 'bg-blue-100 text-blue-800' : 'bg-slate-900 text-white'"
                >
                  {{ h.tracking_type }}
                </span>
              </td>
              <td class="px-4 py-3 font-mono font-bold text-slate-900">
                {{ h.container_number || h.vessel_name }}
              </td>
              <td class="px-4 py-3 text-slate-700">
                {{ h.pol_name }} ← {{ h.pod_name }}
              </td>
              <td class="px-4 py-3 font-semibold text-slate-900 truncate max-w-[200px]">
                {{ h.current_status }}
              </td>
              <td class="px-4 py-3 font-mono text-[11px] text-slate-500">
                {{ formatDateTime(h.last_tracked_at) }}
              </td>
              <td class="px-4 py-3 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button
                    type="button"
                    @click="h.tracking_type === 'vessel' ? selectQuickVessel(h.vessel_name) : selectQuickContainer(h.container_number)"
                    class="px-2.5 py-1 rounded-lg text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 transition cursor-pointer"
                  >
                    {{ isAr ? 'تتبع' : 'Track' }}
                  </button>
                  <button
                    type="button"
                    @click="deleteHistoryItem(h.id)"
                    class="p-1 text-slate-400 hover:text-rose-600 transition cursor-pointer"
                  >
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
