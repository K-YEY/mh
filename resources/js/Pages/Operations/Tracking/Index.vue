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
import ContainerTrackingTimeline from '@/Components/ContainerTrackingTimeline.vue'
import VesselCurrentVoyage from '@/Components/VesselCurrentVoyage.vue'

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
  iso_validation: Object,
  detected_carrier: Object,
  carrier_deep_link: String,
  milestones: Array,
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

const quickVessels = computed(() => {
  const result = []
  if (props.system_vessels && props.system_vessels.length > 0) {
    props.system_vessels.forEach(v => {
      result.push({
        name: v.name,
        route: v.route || `${v.pol || 'Alexandria'} → ${v.pod || 'Rotterdam'}`
      })
    })
  }

  const defaults = [
    { name: 'MAERSK MC-KINNEY MOLLER', route: 'Alexandria → Rotterdam' },
    { name: 'CMA CGM JACQUES SAADE', route: 'Alexandria → Rotterdam' },
    { name: 'EVER GIVEN', route: 'Rotterdam (At Berth)' },
    { name: 'SIMONE MARTINI', route: 'Trapani → Favignana (Egadi)' },
    { name: 'PORT SAID VOYAGER', route: 'Port Said Anch → El Dekheila' },
    { name: 'MSC GULSUN', route: 'Xiamen → Long Beach' },
  ]

  defaults.forEach(d => {
    if (!result.some(r => r.name === d.name)) {
      result.push(d)
    }
  })

  return result.slice(0, 6)
})

const handleSearch = () => {
  if (!query.value.trim()) return
  searching.value = true
  const q = query.value.trim()

  if (activeTab.value === 'vessel') {
    router.get(route('tracking.index'), {
      vessel_name: q,
      search_type: 'vessel'
    }, { 
      preserveState: false,
      onFinish: () => searching.value = false
    })
  } else {
    router.get(route('tracking.index'), {
      container_number: q.toUpperCase(),
      search_type: 'container'
    }, { 
      preserveState: false,
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

const handleTrackingSynced = () => {
  router.reload({ only: ['milestones', 'container', 'iso_validation', 'detected_carrier', 'carrier_deep_link'] })
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
    <!-- SECTION 1: VESSEL LIVE AIS TRACKING VIEW (CURRENT VOYAGE STYLE)   -->
    <!-- ================================================================= -->
    <div v-if="activeTab === 'vessel'" class="space-y-6">
      <VesselCurrentVoyage
        :vessel="vesselData"
        :search-query="query || props.search_number || ''"
      />
    </div>

    <!-- ================================================================= -->
    <!-- SECTION 2: HYBRID CONTAINER TRACKING MODULE (ISO + CARRIER + API) -->
    <!-- ================================================================= -->
    <div v-if="activeTab === 'container'" class="space-y-6">
      <ContainerTrackingTimeline
        :container-number="query || props.search_number || (container ? container.container_number : '')"
        :iso-validation="props.iso_validation"
        :detected-carrier="props.detected_carrier"
        :carrier-deep-link="props.carrier_deep_link"
        :milestones="props.milestones || []"
        :local-container="props.container"
        @synced="handleTrackingSynced"
      />
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
