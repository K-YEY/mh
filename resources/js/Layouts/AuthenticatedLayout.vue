<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import QuickSearchModal from '@/Components/QuickSearchModal.vue'
import { 
  Anchor, LayoutDashboard, Ship, Calculator, Receipt, Coins, ShieldCheck, 
  TrendingUp, BookOpen, Building2, FileCheck2, User, Search, Bell, 
  PanelLeft, ChevronRight, History, LogOut, Globe, Cpu,
  Truck, Scale, FileSpreadsheet, BarChart3, Clock
} from 'lucide-vue-next'

const isSidebarOpen = ref(true)
const isSearchOpen = ref(false)
const page = usePage()
const { isAr, dir, toggleLocale, formatDateTime } = useI18n()

const now = ref(new Date())
let clockTimer = null

const handleGlobalKeydown = (e) => {
  if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
    e.preventDefault()
    isSearchOpen.value = !isSearchOpen.value
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleGlobalKeydown)
  clockTimer = setInterval(() => {
    now.value = new Date()
  }, 1000)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleGlobalKeydown)
  if (clockTimer) clearInterval(clockTimer)
})

const currentDateTime = computed(() => formatDateTime(now.value))

const authUser = computed(() => page.props.auth?.user || {})
const userRole = computed(() => authUser.value.role || 'super-admin')
const baseCurrency = computed(() => page.props.base_currency || { code: 'EGP', symbol: 'E£' })

// Fully reactive route matcher tracking page.url across client-side Inertia transitions
const isItemActive = (pattern, href) => {
  const currentUrl = page.url || window.location.pathname || ''
  const currentPath = currentUrl.split('?')[0].replace(/\/$/, '') || '/'

  // 1. Check Ziggy route pattern
  if (pattern && typeof route !== 'undefined') {
    try {
      if (route().current(pattern)) {
        return true
      }
    } catch (e) {}
  }

  // 2. Check URL pathname matching
  if (href) {
    try {
      const url = new URL(href, window.location.origin)
      const targetPath = url.pathname.replace(/\/$/, '') || '/'

      if (targetPath === '/' && currentPath === '/') {
        return true
      }
      if (targetPath !== '/' && (currentPath === targetPath || currentPath.startsWith(targetPath + '/'))) {
        return true
      }
    } catch (e) {}
  }

  return false
}

const navGroups = computed(() => [
  {
    title: isAr.value ? 'نظرة عامة' : 'OVERVIEW',
    items: [
      { 
        name: isAr.value ? 'لوحة التحكم الرئيسية' : 'Dashboard Workbench', 
        href: route('dashboard'), 
        icon: LayoutDashboard, 
        pattern: 'dashboard' 
      }
    ]
  },
  {
    title: isAr.value ? 'عمليات الشحن واللوجستيات' : 'OCEAN OPERATIONS',
    items: [
      { 
        name: isAr.value ? 'الشحنات الماستر والحاويات' : 'Master Shipments & Reefers', 
        href: route('master-shipments.index'), 
        icon: Ship, 
        pattern: 'master-shipments.*', 
        badge: isAr.value ? 'مباشر' : 'Live' 
      },
      { 
        name: isAr.value ? 'المخلصين الجمركيين (البروكرز)' : 'Customs Brokers Directory', 
        href: route('customs-brokers.index'), 
        icon: ShieldCheck, 
        pattern: 'customs-brokers.*', 
        badge: isAr.value ? 'تخليص' : 'Brokers' 
      },
      { 
        name: isAr.value ? 'شركات النقل البري والمبرد' : 'Inland Trucking Carriers', 
        href: route('truckers.index'), 
        icon: Truck, 
        pattern: 'truckers.*', 
        badge: isAr.value ? 'نقل' : 'Haulage' 
      },
      { 
        name: isAr.value ? 'تتبع الشحنات والحاويات' : 'Container & Cargo Tracking', 
        href: route('tracking.index'), 
        icon: Search, 
        pattern: 'tracking.*', 
        badge: isAr.value ? 'تتبع' : 'Track' 
      },
    ]
  },
  {
    title: isAr.value ? 'المحاسبة والمالية' : 'FINANCIAL ACCOUNTING',
    items: [
      { 
        name: isAr.value ? 'دليل الحسابات (COA)' : 'Chart of Accounts (COA)', 
        href: route('accounts.index'), 
        icon: BookOpen, 
        pattern: 'accounts.*', 
        badge: isAr.value ? 'شجري' : 'Tree' 
      },
      { 
        name: isAr.value ? 'الفواتير وفواتير الموردين' : 'Invoices & Bills', 
        href: route('invoices.index'), 
        icon: Receipt, 
        pattern: 'invoices.*' 
      },
      { 
        name: isAr.value ? 'سندات القبض والصرف' : 'Payment Vouchers', 
        href: route('vouchers.index'), 
        icon: Coins, 
        pattern: 'vouchers.*' 
      },
      { 
        name: isAr.value ? 'أسعار الصرف والعملات' : 'Exchange Rates (FX)', 
        href: route('exchange-rates.index'), 
        icon: TrendingUp, 
        pattern: 'exchange-rates.*' 
      },
      { 
        name: isAr.value ? 'دفتر اليومية العامة (JV)' : 'Journal Entries (JV)', 
        href: route('ledger.journals'), 
        icon: BookOpen, 
        pattern: 'ledger.journals*' 
      },
      { 
        name: isAr.value ? 'دفتر الأستاذ العام (Ledger)' : 'General Ledger Account', 
        href: route('accounting.reports.general-ledger'), 
        icon: FileSpreadsheet, 
        pattern: 'accounting.reports.general-ledger*' 
      },
      { 
        name: isAr.value ? 'كشف حساب الأطراف' : 'Party Sub-Ledger', 
        href: route('ledger.statement'), 
        icon: Building2, 
        pattern: 'ledger.statement*' 
      },
      { 
        name: isAr.value ? 'ميزان المراجعة (Trial Balance)' : 'Trial Balance', 
        href: route('accounting.reports.trial-balance'), 
        icon: Scale, 
        pattern: 'accounting.reports.trial-balance*', 
        badge: isAr.value ? 'ميزان' : 'TB' 
      },
      { 
        name: isAr.value ? 'قائمة الدخل والأرباح (P&L)' : 'Income Statement (P&L)', 
        href: route('accounting.reports.income-statement'), 
        icon: BarChart3, 
        pattern: 'accounting.reports.income-statement*', 
        badge: isAr.value ? 'أرباح' : 'P&L' 
      },
    ]
  },
  {
    title: isAr.value ? 'الامتثال والنظام' : 'COMPLIANCE & SYSTEM',
    items: [
      { 
        name: isAr.value ? 'خزنة الوثائق والشهادات' : 'Document Vault', 
        href: route('documents.vault'), 
        icon: FileCheck2, 
        pattern: 'documents.vault', 
        badge: isAr.value ? 'حجر' : 'Phyto' 
      },
      { 
        name: isAr.value ? 'سجل الأطراف والعملاء' : 'Stakeholder Parties', 
        href: route('parties.index'), 
        icon: Building2, 
        pattern: 'parties.*', 
        badge: isAr.value ? 'أطراف' : 'Parties' 
      },
      { 
        name: isAr.value ? 'موانئ الشحن البحرية' : 'Ocean Ports Directory', 
        href: route('ports.index'), 
        icon: Anchor, 
        pattern: 'ports.*', 
        badge: isAr.value ? 'موانئ' : 'Ports' 
      },
      { 
        name: isAr.value ? 'عملات النظام' : 'System Currencies', 
        href: route('currencies.index'), 
        icon: Coins, 
        pattern: 'currencies.*', 
        badge: isAr.value ? 'عملات' : 'FX' 
      },
      { 
        name: isAr.value ? 'المستخدمين والصلاحيات' : 'Users & Permissions', 
        href: route('users.index'), 
        icon: User, 
        pattern: 'users.*', 
        badge: 'RBAC' 
      },
      { 
        name: isAr.value ? 'سجل حركة النظام والتدقيق' : 'Audit Trail Logs', 
        href: route('admin.audit-logs.index'), 
        icon: History, 
        pattern: 'admin.audit-logs.*', 
        badge: isAr.value ? 'تدقيق' : 'Audit' 
      },
    ]
  }
])
</script>

<template>
  <div :dir="dir" class="app-shell min-h-screen bg-[#edf3f9] text-slate-900 flex font-sans antialiased relative overflow-hidden print:block print:min-h-0 print:bg-white print:p-0 print:m-0">
    <!-- Subtle Ambient Luminous Sky Background Lights -->
    <div class="no-print pointer-events-none fixed inset-0 overflow-hidden z-0">
      <div class="absolute -top-32 start-[10%] w-[650px] h-[650px] rounded-full bg-gradient-to-br from-blue-400/12 via-sky-300/10 to-transparent blur-[130px]"></div>
      <div class="absolute top-[35%] end-[-5%] w-[700px] h-[700px] rounded-full bg-gradient-to-tl from-indigo-300/10 via-cyan-300/10 to-transparent blur-[140px]"></div>
      <div class="absolute -bottom-32 start-[25%] w-[600px] h-[600px] rounded-full bg-gradient-to-tr from-sky-200/30 via-sky-100/40 to-transparent blur-[110px]"></div>
    </div>

    <!-- Apple Frosted Liquid Glass Sidebar -->
    <aside 
      :class="[
        'no-print print:hidden z-20',
        isSidebarOpen ? 'w-68' : 'w-20',
        'bg-white/80 backdrop-blur-2xl border-e border-white/90 flex flex-col justify-between shrink-0 transition-all duration-300 select-none shadow-[4px_0_24px_rgba(0,0,0,0.02)]'
      ]"
    >
      <div class="flex flex-col h-full min-h-0 pt-2">
        <!-- Sidebar Navigation Groups -->
        <div class="p-3 space-y-5 overflow-y-auto flex-1">
          <div v-for="group in navGroups" :key="group.title" class="space-y-1">
            <!-- Group Title Header -->
            <div v-if="isSidebarOpen" class="px-3 py-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase">
              {{ group.title }}
            </div>

            <!-- Group Links -->
            <div class="space-y-1">
              <Link
                v-for="item in group.items"
                :key="item.name"
                :href="item.href"
                :title="!isSidebarOpen ? item.name : undefined"
                :class="[
                  isItemActive(item.pattern, item.href) 
                    ? 'bg-gradient-to-r from-[#25262c] to-[#121316] text-white font-semibold shadow-[0_4px_16px_rgba(0,0,0,0.18)] scale-[1.01]' 
                    : 'text-slate-600 hover:text-slate-900 hover:bg-[#f2f4f7] active:scale-[0.98]',
                  'group relative flex items-center justify-between px-3.5 py-2.5 text-xs font-medium rounded-2xl transition-all duration-200 cursor-pointer'
                ]"
              >
                <div class="flex items-center gap-2.5 min-w-0" :class="{ 'mx-auto': !isSidebarOpen }">
                  <component 
                    :is="item.icon" 
                    class="h-4 w-4 shrink-0 transition-all duration-200" 
                    :class="isItemActive(item.pattern, item.href) ? 'text-white' : 'text-slate-500 group-hover:text-slate-900 group-hover:scale-105'" 
                  />
                  <span v-if="isSidebarOpen" class="truncate">{{ item.name }}</span>
                </div>
                <span 
                  v-if="isSidebarOpen && item.badge" 
                  :class="[
                    isItemActive(item.pattern, item.href) 
                      ? 'bg-white/20 text-white border-white/30 backdrop-blur-sm' 
                      : 'bg-black/[0.04] text-slate-600 border-black/[0.06] group-hover:bg-slate-200/60 group-hover:text-slate-900',
                    'px-2 py-0.5 text-[9px] font-mono font-bold rounded-full border transition-colors'
                  ]"
                >
                  {{ item.badge }}
                </span>
              </Link>
            </div>
          </div>
        </div>

        <!-- Footer User Account Profile Switcher & Logout -->
        <div class="p-3 border-t border-slate-100 bg-white/50 backdrop-blur-md">
          <div class="flex items-center justify-between p-2 rounded-2xl bg-white/90 border border-white shadow-[0_2px_10px_rgba(0,0,0,0.02)] hover:bg-white transition-all">
            <div class="flex items-center gap-2.5 min-w-0">
              <div class="w-8 h-8 rounded-xl bg-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-sm">
                {{ authUser.name ? authUser.name.substring(0, 2).toUpperCase() : 'MH' }}
              </div>
              <div v-if="isSidebarOpen" class="truncate">
                <div class="flex items-center gap-1">
                  <p class="text-xs font-bold text-slate-900 leading-none truncate">{{ authUser.name || 'M&H Admin' }}</p>
                </div>
                <span class="inline-block mt-1 px-1.5 py-0.2 text-[9px] font-mono font-bold rounded-full bg-slate-100 text-slate-700 uppercase border border-slate-200">
                  {{ isAr ? 'مدير النظام' : userRole }}
                </span>
              </div>
            </div>

            <Link 
              v-if="isSidebarOpen"
              :href="route('logout')" 
              method="post" 
              as="button"
              class="p-1.5 rounded-xl text-slate-500 hover:text-rose-600 hover:bg-rose-50/80 transition-all cursor-pointer"
              :title="isAr ? 'تسجيل الخروج' : 'Sign Out'"
            >
              <LogOut class="w-4 h-4" />
            </Link>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Application Shell -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden z-10 print:block print:overflow-visible">
      <!-- Apple Frosted Glass Top Navbar Header -->
      <header class="no-print print:hidden h-16 bg-white/80 backdrop-blur-2xl border-b border-white/90 px-6 flex items-center justify-between shrink-0 shadow-[0_2px_12px_rgba(0,0,0,0.015)]">
        <!-- Left Breadcrumbs & Sidebar Toggle -->
        <div class="flex items-center gap-3">
          <button 
            @click="isSidebarOpen = !isSidebarOpen"
            class="p-2 rounded-xl text-slate-600 hover:text-slate-900 bg-white/90 hover:bg-white border border-slate-200/80 shadow-2xs transition-all cursor-pointer active:scale-95"
            :title="isAr ? 'تبديل الشريط الجانبي' : 'Toggle Sidebar'"
          >
            <PanelLeft class="w-4 h-4" />
          </button>

          <div class="flex items-center gap-2.5 text-xs text-slate-500">
            <div class="w-8 h-8 rounded-xl bg-slate-900 shadow-sm flex items-center justify-center text-white shrink-0">
              <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2a4 4 0 0 0-4 4 4 4 0 0 0-4 4 4 4 0 0 0 4 4 4 4 0 0 0 4-4V6a4 4 0 0 0-4-4zm0 20a4 4 0 0 0 4-4 4 4 0 0 0 4-4 4 4 0 0 0-4-4 4 4 0 0 0-4 4v4a4 4 0 0 0 4 4zm8-10a4 4 0 0 0-4-4 4 4 0 0 0-4 4 4 4 0 0 0 4 4h4a4 4 0 0 0 0-4zm-16 0a4 4 0 0 0 4 4 4 4 0 0 0 4-4 4 4 0 0 0-4-4H4a4 4 0 0 0 0 4z"/>
              </svg>
            </div>
            <span class="font-extrabold text-slate-900 tracking-tight">MH For Export</span>
            <ChevronRight class="w-3.5 h-3.5 text-slate-400" />
            <span class="text-slate-700 font-semibold">{{ isAr ? 'منظومة اللوجستيات والتصدير البحري' : 'Ocean Logistics & Export ERP' }}</span>
          </div>
        </div>

        <!-- Right Command Palette & Status Controls -->
        <div class="flex items-center gap-3">
          <!-- Language Toggle Button (Apple Pill) -->
          <button 
            @click="toggleLocale"
            class="flex items-center gap-1.5 bg-white/90 hover:bg-white border border-slate-200/80 text-slate-800 px-3 py-1.5 rounded-full text-xs font-semibold shadow-2xs hover:shadow-xs transition-all cursor-pointer active:scale-95 backdrop-blur-md"
          >
            <Globe class="w-3.5 h-3.5 text-blue-600" />
            <span>{{ isAr ? '🇪🇬 عربي (الأساسية)' : '🇬🇧 English' }}</span>
          </button>

          <!-- Search Trigger (Desktop & Tablet) -->
          <button 
            type="button"
            @click="isSearchOpen = true"
            class="hidden md:flex items-center gap-4 bg-[#f2f4f7] hover:bg-white border border-slate-200/80 text-slate-600 px-3.5 py-1.5 rounded-full text-xs transition-all backdrop-blur-md cursor-pointer shadow-2xs active:scale-95"
            :title="isAr ? 'البحث السريع (Ctrl+K)' : 'Quick Search (⌘K)'"
          >
            <div class="flex items-center gap-2">
              <Search class="w-3.5 h-3.5 text-slate-500" />
              <span>{{ isAr ? 'بحث سريع...' : 'Quick Search...' }}</span>
            </div>
            <kbd class="font-mono text-[10px] bg-white px-1.5 py-0.5 rounded-md border border-slate-200 text-slate-500 shadow-2xs">⌘K</kbd>
          </button>

          <!-- Mobile Search Trigger Button -->
          <button 
            type="button"
            @click="isSearchOpen = true"
            class="md:hidden p-2 rounded-xl text-slate-600 hover:text-slate-900 bg-white/90 hover:bg-white border border-slate-200/80 shadow-2xs transition-all cursor-pointer active:scale-95"
            :title="isAr ? 'بحث سريع' : 'Quick Search'"
          >
            <Search class="w-4 h-4" />
          </button>

          <!-- Base Currency Pill -->
          <div class="hidden sm:flex items-center gap-1.5 text-xs bg-white/90 border border-slate-200/80 px-3 py-1.5 rounded-full backdrop-blur-md shadow-2xs">
            <span class="text-slate-500 font-medium">{{ isAr ? 'الأساسية:' : 'Base:' }}</span>
            <span class="font-mono font-bold text-slate-900">{{ baseCurrency.code }} ({{ baseCurrency.symbol }})</span>
          </div>

          <!-- Live System Clock (Date, Hour, AM/PM) -->
          <div class="hidden lg:flex items-center gap-1.5 text-xs bg-white/90 border border-slate-200/80 px-3.5 py-1.5 rounded-full text-slate-800 font-mono shadow-2xs backdrop-blur-md">
            <Clock class="w-3.5 h-3.5 text-slate-600" />
            <span class="font-bold">{{ currentDateTime }}</span>
          </div>

          <!-- Notifications Button -->
          <button class="relative p-2 rounded-xl text-slate-600 hover:text-slate-900 bg-white/90 hover:bg-white border border-slate-200/80 shadow-2xs transition-all cursor-pointer active:scale-95">
            <Bell class="w-4 h-4" />
            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-blue-500 rounded-full ring-2 ring-white animate-pulse"></span>
          </button>
        </div>
      </header>

      <!-- Main Canvas Container with Apple Liquid Aesthetics -->
      <main class="flex-1 overflow-y-auto p-6 md:p-8 relative z-10 print:p-0 print:m-0 print:overflow-visible print:bg-transparent">
        <!-- Flash Alert in Frosted Apple Style -->
        <div v-if="page.props.flash?.success" class="no-print mb-6 p-4 bg-emerald-500/10 border border-emerald-500/25 text-emerald-950 backdrop-blur-xl rounded-2xl text-xs font-semibold flex items-center gap-3 shadow-xs">
          <ShieldCheck class="w-5 h-5 text-emerald-600 shrink-0" />
          <span>{{ page.props.flash.success }}</span>
        </div>

        <slot />
      </main>
    </div>

    <!-- Spotlight Command Palette / Quick Search Modal -->
    <QuickSearchModal v-model:isOpen="isSearchOpen" />
  </div>
</template>
