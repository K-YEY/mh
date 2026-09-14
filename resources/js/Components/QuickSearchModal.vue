<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { 
  Search, 
  X, 
  Ship, 
  Boxes, 
  Receipt, 
  Coins, 
  BookOpen, 
  Scale, 
  BarChart3, 
  FileCheck2, 
  Building2, 
  Anchor, 
  User, 
  History, 
  LayoutDashboard, 
  Truck, 
  TrendingUp, 
  Plus, 
  Globe, 
  ArrowRight, 
  ArrowLeft,
  Command,
  CornerDownLeft,
  Clock,
  Sparkles
} from 'lucide-vue-next'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  }
})

const emit = defineEmits(['update:isOpen', 'close'])

const { isAr, dir, toggleLocale } = useI18n()

const query = ref('')
const searchInput = ref(null)
const selectedIndex = ref(0)
const isLoading = ref(false)
const liveResults = ref({
  shipments: [],
  containers: [],
  invoices: [],
  parties: [],
  accounts: [],
})

let debounceTimer = null

// Static System Navigation Pages Index
const systemPages = computed(() => [
  { 
    title: isAr.value ? 'لوحة التحكم الرئيسية' : 'Dashboard Workbench', 
    subtitle: isAr.value ? 'نظرة عامة والتشغيل المباشر' : 'Operations Overview & Telemetry',
    category: 'pages',
    href: route('dashboard'), 
    icon: LayoutDashboard,
    keywords: 'dashboard رئيسية لوحة تحكم تشغيل' 
  },
  { 
    title: isAr.value ? 'الشحنات الماستر والحاويات' : 'Master Shipments & Reefers', 
    subtitle: isAr.value ? 'إدارة بوالص الشحن MBL والحاويات المبردة' : 'Ocean MBL Bookings & Cold-Chain',
    category: 'pages',
    href: route('master-shipments.index'), 
    icon: Ship,
    keywords: 'shipments master reefer شحنات حاويات بحري مبرد بوالص' 
  },
  { 
    title: isAr.value ? 'تتبع الشحنات والحاويات' : 'Container & Cargo Tracking', 
    subtitle: isAr.value ? 'تتبع مسار وسلاسل درجات الحرارة' : 'Telemetry & Live Position',
    category: 'pages',
    href: route('tracking.index'), 
    icon: Search,
    keywords: 'tracking container تتبع حاويات حرارة موقع' 
  },
  { 
    title: isAr.value ? 'الفواتير وفواتير الموردين' : 'Invoices & Vendor Bills', 
    subtitle: isAr.value ? 'الفوترة والمطالبات وحسابات الموردين' : 'AR Invoices & AP Bills Ledger',
    category: 'pages',
    href: route('invoices.index'), 
    icon: Receipt,
    keywords: 'invoices bills فواتير مطالبات عملاء موردين' 
  },
  { 
    title: isAr.value ? 'سندات القبض والصرف' : 'Payment Vouchers', 
    subtitle: isAr.value ? 'حركات الخزينة والتحصيلات والمدفوعات' : 'Treasury Receipts & Disbursements',
    category: 'pages',
    href: route('vouchers.index'), 
    icon: Coins,
    keywords: 'vouchers payment سندات قبض صرف خزينة تحصيل' 
  },
  { 
    title: isAr.value ? 'دليل الحسابات (COA)' : 'Chart of Accounts (COA)', 
    subtitle: isAr.value ? 'الشجرة المحاسبية العامة' : 'Multi-tier Financial Tree',
    category: 'pages',
    href: route('accounts.index'), 
    icon: BookOpen,
    keywords: 'coa accounts دليل حسابات محاسبة شجرة' 
  },
  { 
    title: isAr.value ? 'دفتر اليومية العامة (JV)' : 'Journal Entries (JV)', 
    subtitle: isAr.value ? 'القيود اليومية الموزونة والتسويات' : 'Balanced Double-Entry Journals',
    category: 'pages',
    href: route('ledger.journals'), 
    icon: BookOpen,
    keywords: 'journals jv قيود يومية تسويات محاسبية' 
  },
  { 
    title: isAr.value ? 'ميزان المراجعة (Trial Balance)' : 'Trial Balance', 
    subtitle: isAr.value ? 'أرصدة وحركات الحسابات الدائنة والمدينة' : 'Debit & Credit Trial Balance Sheet',
    category: 'pages',
    href: route('accounting.reports.trial-balance'), 
    icon: Scale,
    keywords: 'trial balance ميزان مراجعة أرصدة دائن مدين' 
  },
  { 
    title: isAr.value ? 'قائمة الدخل والأرباح (P&L)' : 'Income Statement (P&L)', 
    subtitle: isAr.value ? 'الإيرادات والمصروفات وصافي الأرباح' : 'Operational Revenue, Expenses & Net Profit',
    category: 'pages',
    href: route('accounting.reports.income-statement'), 
    icon: BarChart3,
    keywords: 'pnl income profit قائمة دخل أرباح إيرادات مصروفات' 
  },
  { 
    title: isAr.value ? 'خزنة الوثائق والشهادات' : 'Phytosanitary Document Vault', 
    subtitle: isAr.value ? 'شهادات الحجر الزراعي والرقابة على الصادرات' : 'Agricultural Compliance & Certificates',
    category: 'pages',
    href: route('documents.vault'), 
    icon: FileCheck2,
    keywords: 'vault documents phyto حجر زراعي شهادات وثائق فحص' 
  },
  { 
    title: isAr.value ? 'سجل الأطراف والعملاء' : 'Stakeholder Parties', 
    subtitle: isAr.value ? 'العملاء، الموردين والخطوط الملاحية' : 'Clients, Vendors & Ocean Carriers',
    category: 'pages',
    href: route('parties.index'), 
    icon: Building2,
    keywords: 'parties customers vendors أطراف عملاء موردين خطوط' 
  },
  { 
    title: isAr.value ? 'شركات النقل البري' : 'Trucking Carriers', 
    subtitle: isAr.value ? 'أسطول النقل الداخلي والحاويات' : 'Inland Reefer Fleet Haulage',
    category: 'pages',
    href: route('truckers.index'), 
    icon: Truck,
    keywords: 'truckers transport نقل بري مقطورات شاحنات' 
  },
  { 
    title: isAr.value ? 'المخلصين الجمركيين' : 'Customs Brokers', 
    subtitle: isAr.value ? 'سجل وكلاء التخليص الجمركي بالموانئ' : 'Customs Clearance Agents',
    category: 'pages',
    href: route('customs-brokers.index'), 
    icon: Anchor,
    keywords: 'customs brokers تخليص جمركي موانئ' 
  },
  { 
    title: isAr.value ? 'موانئ الشحن البحرية' : 'Ocean Ports Directory', 
    subtitle: isAr.value ? 'موانئ الشحن والتفريغ الدولية' : 'POL & POD Global Marine Terminals',
    category: 'pages',
    href: route('ports.index'), 
    icon: Anchor,
    keywords: 'ports موانئ شحن تفريغ بحري' 
  },
  { 
    title: isAr.value ? 'أسعار الصرف والعملات' : 'Exchange Rates (FX)', 
    subtitle: isAr.value ? 'متابعة العملات والربط الحي مع البنك المركزي' : 'Live FX Sync & Daily Rates',
    category: 'pages',
    href: route('exchange-rates.index'), 
    icon: TrendingUp,
    keywords: 'fx exchange rates عملات أسعار صرف دولار يورو' 
  },
  { 
    title: isAr.value ? 'المستخدمين والصلاحيات' : 'Users & Permissions', 
    subtitle: isAr.value ? 'إدارة فرق العمل وصلاحيات RBAC' : 'Access Control & Personnel',
    category: 'pages',
    href: route('users.index'), 
    icon: User,
    keywords: 'users roles مستخدمين صلاحيات أمان' 
  },
  { 
    title: isAr.value ? 'سجل حركة النظام والتدقيق' : 'Audit Trail Logs', 
    subtitle: isAr.value ? 'سجل العمليات الرقابية وسجلات الأمان' : 'Security Audit & Change History',
    category: 'pages',
    href: route('admin.audit-logs.index'), 
    icon: History,
    keywords: 'audit logs تدقيق رقابة أمان سجل' 
  }
])

// Quick Action Shortcuts
const quickActions = computed(() => [
  {
    title: isAr.value ? 'حجز شحنة بحرية جديدة' : 'New Ocean Booking',
    subtitle: isAr.value ? 'إنشاء ملف شحنة وبوالص ماستر جديدة' : 'Create new MBL shipment voyage',
    category: 'actions',
    href: route('master-shipments.create'),
    icon: Plus,
    keywords: 'new booking create إضافة حجز شحنة جديدة'
  },
  {
    title: isAr.value ? 'إصدار فاتورة جديدة' : 'Create New Invoice',
    subtitle: isAr.value ? 'إنشاء فاتورة عميل أو مطالبة مورد' : 'Issue customer invoice or vendor bill',
    category: 'actions',
    href: route('invoices.create'),
    icon: Receipt,
    keywords: 'new invoice فاتورة جديدة إنشاء مطالبة'
  },
  {
    title: isAr.value ? 'إنشاء قيد يومية عامة (JV)' : 'New Journal Entry',
    subtitle: isAr.value ? 'تسجيل قيد محاسبي موزون يدوي' : 'Post balanced double-entry GL journal',
    category: 'actions',
    href: route('ledger.journals.create'),
    icon: BookOpen,
    keywords: 'new journal قيد يومية إضافة محاسبة'
  },
  {
    title: isAr.value ? 'تبديل لغة الواجهة' : 'Switch Language',
    subtitle: isAr.value ? 'التحويل إلى English' : 'Switch to عربي',
    category: 'actions',
    action: () => {
      toggleLocale()
      closeModal()
    },
    icon: Globe,
    keywords: 'language english عربي لغة تبديل'
  }
])

// Filtered Pages matching query
const filteredPages = computed(() => {
  const q = query.value.trim().toLowerCase()
  if (!q) return systemPages.value.slice(0, 6)
  return systemPages.value.filter(p => 
    p.title.toLowerCase().includes(q) || 
    p.subtitle.toLowerCase().includes(q) || 
    p.keywords.toLowerCase().includes(q)
  )
})

// Filtered Actions matching query
const filteredActions = computed(() => {
  const q = query.value.trim().toLowerCase()
  if (!q) return quickActions.value
  return quickActions.value.filter(a => 
    a.title.toLowerCase().includes(q) || 
    a.subtitle.toLowerCase().includes(q) || 
    a.keywords.toLowerCase().includes(q)
  )
})

// Combined Flat List for Keyboard Selection
const allItems = computed(() => {
  const items = []
  
  if (filteredActions.value.length > 0) {
    items.push(...filteredActions.value)
  }
  if (filteredPages.value.length > 0) {
    items.push(...filteredPages.value)
  }
  if (liveResults.value.shipments.length > 0) {
    items.push(...liveResults.value.shipments)
  }
  if (liveResults.value.containers.length > 0) {
    items.push(...liveResults.value.containers)
  }
  if (liveResults.value.invoices.length > 0) {
    items.push(...liveResults.value.invoices)
  }
  if (liveResults.value.parties.length > 0) {
    items.push(...liveResults.value.parties)
  }
  if (liveResults.value.accounts.length > 0) {
    items.push(...liveResults.value.accounts)
  }

  return items
})

const totalResultsCount = computed(() => allItems.value.length)

// Watch query for live database search
watch(query, (newVal) => {
  selectedIndex.value = 0
  if (debounceTimer) clearTimeout(debounceTimer)

  const trimmed = newVal.trim()
  if (trimmed.length < 2) {
    liveResults.value = {
      shipments: [],
      containers: [],
      invoices: [],
      parties: [],
      accounts: [],
    }
    isLoading.value = false
    return
  }

  isLoading.value = true
  debounceTimer = setTimeout(async () => {
    try {
      const res = await fetch(`/quick-search?q=${encodeURIComponent(trimmed)}`, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        }
      })
      if (res.ok) {
        const data = await res.json()
        liveResults.value = data
      }
    } catch (e) {
      console.error('Quick search error:', e)
    } finally {
      isLoading.value = false
    }
  }, 220)
})

const closeModal = () => {
  emit('update:isOpen', false)
  emit('close')
  query.value = ''
  selectedIndex.value = 0
}

const selectItem = (item) => {
  if (item.action) {
    item.action()
  } else if (item.href) {
    closeModal()
    router.visit(item.href)
  }
}

// Keyboard Navigation
const handleKeydown = (e) => {
  if (!props.isOpen) return

  if (e.key === 'Escape') {
    e.preventDefault()
    closeModal()
  } else if (e.key === 'ArrowDown') {
    e.preventDefault()
    if (allItems.value.length > 0) {
      selectedIndex.value = (selectedIndex.value + 1) % allItems.value.length
    }
  } else if (e.key === 'ArrowUp') {
    e.preventDefault()
    if (allItems.value.length > 0) {
      selectedIndex.value = (selectedIndex.value - 1 + allItems.value.length) % allItems.value.length
    }
  } else if (e.key === 'Enter') {
    e.preventDefault()
    if (allItems.value[selectedIndex.value]) {
      selectItem(allItems.value[selectedIndex.value])
    }
  }
}

watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    selectedIndex.value = 0
    nextTick(() => {
      searchInput.value?.focus()
    })
  }
})

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
  if (debounceTimer) clearTimeout(debounceTimer)
})
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto" :dir="dir">
    <!-- Backdrop Blur with Subtle Sky Ambient Gradient -->
    <div 
      class="fixed inset-0 bg-slate-900/40 backdrop-blur-md transition-opacity animate-fade-in"
      @click="closeModal"
    ></div>

    <!-- Centered Command Palette Container -->
    <div class="min-h-full flex items-start justify-center p-4 sm:p-6 pt-16 sm:pt-24 relative z-10">
      <div 
        class="w-full max-w-2xl rounded-[2.25rem] bg-gradient-to-b from-white/95 via-white/90 to-white/95 backdrop-blur-2xl border border-white/90 shadow-[0_25px_70px_rgba(0,0,0,0.18)] overflow-hidden animate-scale-in flex flex-col max-h-[82vh]"
        @click.stop
      >
        <!-- Search Input Bar -->
        <div class="p-4 sm:p-5 border-b border-slate-100 flex items-center gap-3.5 bg-white/70 backdrop-blur-md">
          <div class="w-9 h-9 rounded-2xl bg-slate-900 text-white flex items-center justify-center shrink-0 shadow-xs">
            <Search class="w-4.5 h-4.5" />
          </div>

          <input
            ref="searchInput"
            v-model="query"
            type="text"
            :placeholder="isAr ? 'ابحث عن صفحة، شحنة، رقم حاوية، فاتورة، عميل، أو حساب...' : 'Search pages, shipments, containers, invoices, parties, accounts...'"
            class="flex-1 bg-transparent border-none outline-none text-sm sm:text-base text-slate-900 placeholder:text-slate-400 font-medium"
            autocomplete="off"
            spellcheck="false"
          />

          <!-- Loading Spinner or Clear Button -->
          <div class="flex items-center gap-2">
            <div v-if="isLoading" class="w-4 h-4 border-2 border-slate-300 border-t-slate-800 rounded-full animate-spin"></div>
            
            <button
              v-else-if="query"
              type="button"
              @click="query = ''"
              class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors"
            >
              <X class="w-4 h-4" />
            </button>

            <kbd class="hidden sm:inline-flex items-center px-2 py-0.5 text-[10px] font-mono font-semibold rounded-lg bg-slate-100 text-slate-500 border border-slate-200 shadow-2xs">
              ESC
            </kbd>
          </div>
        </div>

        <!-- Results Scroll Area -->
        <div class="overflow-y-auto flex-1 p-3 sm:p-4 space-y-4 max-h-[58vh]">
          <!-- Quick Actions Section -->
          <div v-if="filteredActions.length > 0" class="space-y-1">
            <div class="px-3 py-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase flex items-center gap-1.5">
              <Sparkles class="w-3 h-3 text-amber-500" />
              <span>{{ isAr ? 'إجراءات سريعة' : 'QUICK ACTIONS' }}</span>
            </div>

            <div class="space-y-1">
              <button
                v-for="item in filteredActions"
                :key="item.title"
                type="button"
                @click="selectItem(item)"
                class="w-full text-start flex items-center justify-between p-3 rounded-2xl transition-all cursor-pointer group"
                :class="allItems.indexOf(item) === selectedIndex ? 'bg-gradient-to-r from-[#25262c] to-[#121316] text-white shadow-md' : 'hover:bg-slate-100/80 text-slate-800'"
              >
                <div class="flex items-center gap-3 min-w-0">
                  <div 
                    class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                    :class="allItems.indexOf(item) === selectedIndex ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700'"
                  >
                    <component :is="item.icon" class="w-4 h-4" />
                  </div>
                  <div class="truncate">
                    <div class="text-xs sm:text-sm font-bold truncate">{{ item.title }}</div>
                    <div class="text-[11px] truncate" :class="allItems.indexOf(item) === selectedIndex ? 'text-slate-300' : 'text-slate-500'">{{ item.subtitle }}</div>
                  </div>
                </div>

                <CornerDownLeft 
                  class="w-3.5 h-3.5 shrink-0 transition-opacity" 
                  :class="allItems.indexOf(item) === selectedIndex ? 'opacity-100' : 'opacity-0'" 
                />
              </button>
            </div>
          </div>

          <!-- Navigation Pages Section -->
          <div v-if="filteredPages.length > 0" class="space-y-1">
            <div class="px-3 py-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase flex items-center gap-1.5">
              <LayoutDashboard class="w-3 h-3 text-blue-500" />
              <span>{{ isAr ? 'الصفحات والنظام' : 'PAGES & NAVIGATION' }}</span>
            </div>

            <div class="space-y-1">
              <button
                v-for="item in filteredPages"
                :key="item.title"
                type="button"
                @click="selectItem(item)"
                class="w-full text-start flex items-center justify-between p-3 rounded-2xl transition-all cursor-pointer group"
                :class="allItems.indexOf(item) === selectedIndex ? 'bg-gradient-to-r from-[#25262c] to-[#121316] text-white shadow-md' : 'hover:bg-slate-100/80 text-slate-800'"
              >
                <div class="flex items-center gap-3 min-w-0">
                  <div 
                    class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 transition-colors"
                    :class="allItems.indexOf(item) === selectedIndex ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700'"
                  >
                    <component :is="item.icon" class="w-4 h-4" />
                  </div>
                  <div class="truncate">
                    <div class="text-xs sm:text-sm font-bold truncate">{{ item.title }}</div>
                    <div class="text-[11px] truncate" :class="allItems.indexOf(item) === selectedIndex ? 'text-slate-300' : 'text-slate-500'">{{ item.subtitle }}</div>
                  </div>
                </div>

                <CornerDownLeft 
                  class="w-3.5 h-3.5 shrink-0 transition-opacity" 
                  :class="allItems.indexOf(item) === selectedIndex ? 'opacity-100' : 'opacity-0'" 
                />
              </button>
            </div>
          </div>

          <!-- Live Master Shipments Results -->
          <div v-if="liveResults.shipments.length > 0" class="space-y-1">
            <div class="px-3 py-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase flex items-center gap-1.5">
              <Ship class="w-3 h-3 text-blue-600" />
              <span>{{ isAr ? 'الشحنات البحرية' : 'OCEAN SHIPMENTS' }} ({{ liveResults.shipments.length }})</span>
            </div>

            <div class="space-y-1">
              <button
                v-for="item in liveResults.shipments"
                :key="item.id"
                type="button"
                @click="selectItem(item)"
                class="w-full text-start flex items-center justify-between p-3 rounded-2xl transition-all cursor-pointer"
                :class="allItems.indexOf(item) === selectedIndex ? 'bg-gradient-to-r from-[#25262c] to-[#121316] text-white shadow-md' : 'hover:bg-slate-100/80 text-slate-800'"
              >
                <div class="flex items-center gap-3 min-w-0">
                  <div 
                    class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                    :class="allItems.indexOf(item) === selectedIndex ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-600'"
                  >
                    <Ship class="w-4 h-4" />
                  </div>
                  <div class="truncate">
                    <div class="text-xs sm:text-sm font-mono font-bold truncate">{{ item.title }}</div>
                    <div class="text-[11px] truncate" :class="allItems.indexOf(item) === selectedIndex ? 'text-slate-300' : 'text-slate-500'">{{ item.subtitle }}</div>
                  </div>
                </div>

                <span 
                  class="px-2 py-0.5 rounded-full text-[9px] font-mono font-bold uppercase border"
                  :class="allItems.indexOf(item) === selectedIndex ? 'bg-white/20 text-white border-white/30' : 'bg-blue-50 text-blue-700 border-blue-200'"
                >
                  {{ item.badge }}
                </span>
              </button>
            </div>
          </div>

          <!-- Live Reefer Containers Results -->
          <div v-if="liveResults.containers.length > 0" class="space-y-1">
            <div class="px-3 py-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase flex items-center gap-1.5">
              <Boxes class="w-3 h-3 text-sky-600" />
              <span>{{ isAr ? 'الحاويات المبردة' : 'REEFER CONTAINERS' }} ({{ liveResults.containers.length }})</span>
            </div>

            <div class="space-y-1">
              <button
                v-for="item in liveResults.containers"
                :key="item.id"
                type="button"
                @click="selectItem(item)"
                class="w-full text-start flex items-center justify-between p-3 rounded-2xl transition-all cursor-pointer"
                :class="allItems.indexOf(item) === selectedIndex ? 'bg-gradient-to-r from-[#25262c] to-[#121316] text-white shadow-md' : 'hover:bg-slate-100/80 text-slate-800'"
              >
                <div class="flex items-center gap-3 min-w-0">
                  <div 
                    class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                    :class="allItems.indexOf(item) === selectedIndex ? 'bg-white/20 text-white' : 'bg-sky-50 text-sky-600'"
                  >
                    <Boxes class="w-4 h-4" />
                  </div>
                  <div class="truncate">
                    <div class="text-xs sm:text-sm font-mono font-bold truncate">{{ item.title }}</div>
                    <div class="text-[11px] truncate" :class="allItems.indexOf(item) === selectedIndex ? 'text-slate-300' : 'text-slate-500'">{{ item.subtitle }}</div>
                  </div>
                </div>

                <span 
                  class="px-2 py-0.5 rounded-full text-[9px] font-mono font-bold uppercase border"
                  :class="allItems.indexOf(item) === selectedIndex ? 'bg-white/20 text-white border-white/30' : 'bg-sky-50 text-sky-700 border-sky-200'"
                >
                  {{ item.badge }}
                </span>
              </button>
            </div>
          </div>

          <!-- Live Invoices Results -->
          <div v-if="liveResults.invoices.length > 0" class="space-y-1">
            <div class="px-3 py-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase flex items-center gap-1.5">
              <Receipt class="w-3 h-3 text-emerald-600" />
              <span>{{ isAr ? 'الفواتير والمطالبات' : 'INVOICES & BILLS' }} ({{ liveResults.invoices.length }})</span>
            </div>

            <div class="space-y-1">
              <button
                v-for="item in liveResults.invoices"
                :key="item.id"
                type="button"
                @click="selectItem(item)"
                class="w-full text-start flex items-center justify-between p-3 rounded-2xl transition-all cursor-pointer"
                :class="allItems.indexOf(item) === selectedIndex ? 'bg-gradient-to-r from-[#25262c] to-[#121316] text-white shadow-md' : 'hover:bg-slate-100/80 text-slate-800'"
              >
                <div class="flex items-center gap-3 min-w-0">
                  <div 
                    class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                    :class="allItems.indexOf(item) === selectedIndex ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-600'"
                  >
                    <Receipt class="w-4 h-4" />
                  </div>
                  <div class="truncate">
                    <div class="text-xs sm:text-sm font-mono font-bold truncate">{{ item.title }}</div>
                    <div class="text-[11px] truncate" :class="allItems.indexOf(item) === selectedIndex ? 'text-slate-300' : 'text-slate-500'">{{ item.subtitle }}</div>
                  </div>
                </div>

                <span 
                  class="px-2 py-0.5 rounded-full text-[9px] font-mono font-bold uppercase border"
                  :class="allItems.indexOf(item) === selectedIndex ? 'bg-white/20 text-white border-white/30' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                >
                  {{ item.badge }}
                </span>
              </button>
            </div>
          </div>

          <!-- Live Parties Results -->
          <div v-if="liveResults.parties.length > 0" class="space-y-1">
            <div class="px-3 py-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase flex items-center gap-1.5">
              <Building2 class="w-3 h-3 text-purple-600" />
              <span>{{ isAr ? 'سجل الأطراف والعملاء' : 'PARTIES & CLIENTS' }} ({{ liveResults.parties.length }})</span>
            </div>

            <div class="space-y-1">
              <button
                v-for="item in liveResults.parties"
                :key="item.id"
                type="button"
                @click="selectItem(item)"
                class="w-full text-start flex items-center justify-between p-3 rounded-2xl transition-all cursor-pointer"
                :class="allItems.indexOf(item) === selectedIndex ? 'bg-gradient-to-r from-[#25262c] to-[#121316] text-white shadow-md' : 'hover:bg-slate-100/80 text-slate-800'"
              >
                <div class="flex items-center gap-3 min-w-0">
                  <div 
                    class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                    :class="allItems.indexOf(item) === selectedIndex ? 'bg-white/20 text-white' : 'bg-purple-50 text-purple-600'"
                  >
                    <Building2 class="w-4 h-4" />
                  </div>
                  <div class="truncate">
                    <div class="text-xs sm:text-sm font-bold truncate">{{ item.title }}</div>
                    <div class="text-[11px] truncate" :class="allItems.indexOf(item) === selectedIndex ? 'text-slate-300' : 'text-slate-500'">{{ item.subtitle }}</div>
                  </div>
                </div>

                <span 
                  class="px-2 py-0.5 rounded-full text-[9px] font-mono font-bold uppercase border"
                  :class="allItems.indexOf(item) === selectedIndex ? 'bg-white/20 text-white border-white/30' : 'bg-purple-50 text-purple-700 border-purple-200'"
                >
                  {{ item.badge }}
                </span>
              </button>
            </div>
          </div>

          <!-- Live Accounts (COA) Results -->
          <div v-if="liveResults.accounts.length > 0" class="space-y-1">
            <div class="px-3 py-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase flex items-center gap-1.5">
              <BookOpen class="w-3 h-3 text-indigo-600" />
              <span>{{ isAr ? 'دليل الحسابات' : 'ACCOUNTS (COA)' }} ({{ liveResults.accounts.length }})</span>
            </div>

            <div class="space-y-1">
              <button
                v-for="item in liveResults.accounts"
                :key="item.id"
                type="button"
                @click="selectItem(item)"
                class="w-full text-start flex items-center justify-between p-3 rounded-2xl transition-all cursor-pointer"
                :class="allItems.indexOf(item) === selectedIndex ? 'bg-gradient-to-r from-[#25262c] to-[#121316] text-white shadow-md' : 'hover:bg-slate-100/80 text-slate-800'"
              >
                <div class="flex items-center gap-3 min-w-0">
                  <div 
                    class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0"
                    :class="allItems.indexOf(item) === selectedIndex ? 'bg-white/20 text-white' : 'bg-indigo-50 text-indigo-600'"
                  >
                    <BookOpen class="w-4 h-4" />
                  </div>
                  <div class="truncate">
                    <div class="text-xs sm:text-sm font-bold truncate">{{ item.title }}</div>
                    <div class="text-[11px] truncate" :class="allItems.indexOf(item) === selectedIndex ? 'text-slate-300' : 'text-slate-500'">{{ item.subtitle }}</div>
                  </div>
                </div>

                <span 
                  class="px-2 py-0.5 rounded-full text-[9px] font-mono font-bold uppercase border"
                  :class="allItems.indexOf(item) === selectedIndex ? 'bg-white/20 text-white border-white/30' : 'bg-indigo-50 text-indigo-700 border-indigo-200'"
                >
                  {{ item.badge }}
                </span>
              </button>
            </div>
          </div>

          <!-- Empty Results State -->
          <div v-if="totalResultsCount === 0" class="py-12 text-center text-slate-400">
            <Search class="w-8 h-8 mx-auto mb-2 text-slate-300" />
            <p class="text-xs sm:text-sm font-medium">{{ isAr ? 'لم يتم العثور على نتائج تطابق بحثك' : 'No matching results found' }}</p>
            <p class="text-[11px] text-slate-400 mt-1">{{ isAr ? 'جرّب البحث برقم الشحنة، الحاوية، الفاتورة أو اسم الصفحة' : 'Try searching by file number, container ID, or page name' }}</p>
          </div>
        </div>

        <!-- Palette Keyboard Legend Footer -->
        <div class="p-3 sm:p-3.5 border-t border-slate-100 bg-white/60 backdrop-blur-md flex items-center justify-between text-[11px] text-slate-500 select-none">
          <div class="flex items-center gap-3">
            <span class="flex items-center gap-1">
              <kbd class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200 font-mono text-[10px]">↑↓</kbd>
              <span>{{ isAr ? 'للتنقل' : 'Navigate' }}</span>
            </span>
            <span class="flex items-center gap-1">
              <kbd class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200 font-mono text-[10px]">↵</kbd>
              <span>{{ isAr ? 'للاختيار' : 'Select' }}</span>
            </span>
            <span class="flex items-center gap-1">
              <kbd class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200 font-mono text-[10px]">ESC</kbd>
              <span>{{ isAr ? 'للإغلاق' : 'Close' }}</span>
            </span>
          </div>

          <div class="text-[10px] font-mono text-slate-400">
            {{ totalResultsCount }} {{ isAr ? 'نتيجة متاحة' : 'results' }}
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.96) translateY(-8px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-fade-in {
  animation: fadeIn 0.2s ease-out forwards;
}

.animate-scale-in {
  animation: scaleIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
