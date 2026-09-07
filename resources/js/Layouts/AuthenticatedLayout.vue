<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { 
  Anchor, LayoutDashboard, Ship, Calculator, Receipt, Coins, ShieldCheck, 
  TrendingUp, BookOpen, Building2, FileCheck2, User, Search, Bell, 
  ChevronsUpDown, PanelLeft, ChevronRight, History, LogOut, Globe, Cpu,
  Truck, Scale, FileSpreadsheet, BarChart3, Clock
} from 'lucide-vue-next'

const isSidebarOpen = ref(true)
const page = usePage()
const { isAr, dir, toggleLocale, formatDateTime } = useI18n()

const now = ref(new Date())
let clockTimer = null

onMounted(() => {
  clockTimer = setInterval(() => {
    now.value = new Date()
  }, 1000)
})

onUnmounted(() => {
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
  <div :dir="dir" class="app-shell min-h-screen bg-zinc-50 text-zinc-950 flex font-sans antialiased print:block print:min-h-0 print:bg-white print:p-0 print:m-0">
    <!-- Shadcn Monochromatic White Sidebar -->
    <aside 
      :class="[
        'no-print print:hidden',
        isSidebarOpen ? 'w-64' : 'w-16',
        'bg-white border-e border-zinc-200 flex flex-col justify-between shrink-0 transition-all duration-200 select-none shadow-2xs'
      ]"
    >
      <div>
        <!-- Team / Workspace Header Switcher with MH For Export Logo -->
        <div class="h-16 px-3 flex items-center border-b border-zinc-200 bg-white">
          <div class="w-full flex items-center justify-between p-1 rounded-lg hover:bg-blue-50/50 cursor-pointer transition-colors">
            <div class="flex items-center gap-2.5 min-w-0">
              <div class="w-10 h-10 rounded-lg bg-white border border-blue-100 flex items-center justify-center shrink-0 shadow-xs p-1">
                <img src="/logo.png" alt="MH For Export" class="w-full h-full object-contain" />
              </div>
              <div v-if="isSidebarOpen" class="truncate">
                <div class="font-black text-xs text-blue-950 truncate flex items-center gap-1">
                  <span>MH For Export</span>
                  <span class="px-1 py-0.2 rounded text-[9px] font-mono bg-blue-100 text-blue-800 font-bold border border-blue-200">
                    ERP
                  </span>
                </div>
                <p class="text-[10px] text-zinc-500 truncate font-medium">
                  {{ isAr ? 'اللوجستيات والتصدير البحري' : 'Agri Export Logistics' }}
                </p>
              </div>
            </div>
            <ChevronsUpDown v-if="isSidebarOpen" class="w-4 h-4 text-zinc-400 shrink-0" />
          </div>
        </div>

        <!-- Sidebar Navigation Groups -->
        <div class="p-2 space-y-4 overflow-y-auto max-h-[calc(100vh-8rem)]">
          <div v-for="group in navGroups" :key="group.title" class="space-y-1">
            <!-- Group Title Header -->
            <div v-if="isSidebarOpen" class="px-2 py-1 text-[10px] font-bold tracking-wider text-zinc-400 uppercase">
              {{ group.title }}
            </div>

            <!-- Group Links -->
            <div class="space-y-0.5">
              <Link
                v-for="item in group.items"
                :key="item.name"
                :href="item.href"
                :title="!isSidebarOpen ? item.name : undefined"
                :class="[
                  isItemActive(item.pattern, item.href) 
                    ? 'bg-blue-900 text-white font-semibold shadow-xs shadow-blue-900/20' 
                    : 'text-zinc-600 hover:text-blue-900 hover:bg-blue-50/60',
                  'group relative flex items-center justify-between px-2.5 py-2 text-xs font-medium rounded-lg transition-all duration-150'
                ]"
              >
                <!-- Active Slider / Pill Accent Bar Indicator -->
                <span 
                  v-if="isItemActive(item.pattern, item.href)" 
                  class="absolute start-0 top-1.5 bottom-1.5 w-1 bg-cyan-400 rounded-full shadow-sm"
                ></span>

                <div class="flex items-center gap-2.5 min-w-0" :class="{ 'mx-auto': !isSidebarOpen }">
                  <component 
                    :is="item.icon" 
                    class="h-4 w-4 shrink-0 transition-colors" 
                    :class="isItemActive(item.pattern, item.href) ? 'text-cyan-300' : 'text-zinc-500 group-hover:text-blue-900'" 
                  />
                  <span v-if="isSidebarOpen" class="truncate">{{ item.name }}</span>
                </div>
                <span 
                  v-if="isSidebarOpen && item.badge" 
                  :class="[
                    isItemActive(item.pattern, item.href) ? 'bg-blue-800 text-cyan-200 border-blue-700' : 'bg-zinc-100 text-zinc-600 border-zinc-200 group-hover:border-blue-200 group-hover:bg-blue-50 group-hover:text-blue-900',
                    'px-1.5 py-0.5 text-[9px] font-mono font-bold rounded border transition-colors'
                  ]"
                >
                  {{ item.badge }}
                </span>
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer User Account Profile Switcher & Logout -->
      <div class="p-2 border-t border-zinc-200 bg-white">
        <div class="flex items-center justify-between p-1.5 rounded-lg bg-zinc-50 border border-zinc-200">
          <div class="flex items-center gap-2.5 min-w-0">
            <div class="w-7 h-7 rounded-full bg-blue-900 text-white font-bold text-xs flex items-center justify-center shrink-0">
              {{ authUser.name ? authUser.name.substring(0, 2).toUpperCase() : 'MH' }}
            </div>
            <div v-if="isSidebarOpen" class="truncate">
              <div class="flex items-center gap-1">
                <p class="text-xs font-bold text-zinc-950 leading-none truncate">{{ authUser.name || 'M&H Admin' }}</p>
              </div>
              <span class="inline-block mt-1 px-1.5 py-0.2 text-[9px] font-mono font-bold rounded bg-blue-900 text-blue-100 uppercase border border-blue-800">
                {{ isAr ? 'مدير النظام' : userRole }}
              </span>
            </div>
          </div>

          <Link 
            v-if="isSidebarOpen"
            :href="route('logout')" 
            method="post" 
            as="button"
            class="p-1.5 rounded text-zinc-500 hover:text-zinc-950 hover:bg-zinc-200 transition-colors"
            :title="isAr ? 'تسجيل الخروج' : 'Sign Out'"
          >
            <LogOut class="w-4 h-4" />
          </Link>
        </div>
      </div>
    </aside>

    <!-- Main Application Shell -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden print:block print:overflow-visible">
      <!-- Shadcn Monochromatic Top Navbar Header -->
      <header class="no-print print:hidden h-14 bg-white border-b border-zinc-200 px-4 flex items-center justify-between shrink-0 shadow-2xs">
        <!-- Left Breadcrumbs & Sidebar Toggle -->
        <div class="flex items-center gap-3">
          <button 
            @click="isSidebarOpen = !isSidebarOpen"
            class="p-1.5 rounded-md text-zinc-600 hover:text-zinc-950 hover:bg-zinc-100 transition-colors border border-zinc-200 cursor-pointer"
          >
            <PanelLeft class="w-4 h-4" />
          </button>

          <div class="flex items-center gap-1.5 text-xs text-zinc-500">
            <span class="hover:text-blue-900 font-bold text-blue-950">MH For Export</span>
            <ChevronRight class="w-3 h-3 text-zinc-400" />
            <span class="text-zinc-800 font-semibold">{{ isAr ? 'منظومة اللوجستيات والتصدير البحري' : 'Ocean Logistics & Export ERP' }}</span>
          </div>
        </div>

        <!-- Right Command Palette & Status Controls -->
        <div class="flex items-center gap-2.5">
          <!-- Language Toggle Button -->
          <button 
            @click="toggleLocale"
            class="flex items-center gap-1.5 bg-zinc-100 border border-zinc-300 text-zinc-900 px-2.5 py-1 rounded-md text-xs font-bold hover:bg-blue-900 hover:text-white transition-all cursor-pointer"
          >
            <Globe class="w-3.5 h-3.5" />
            <span>{{ isAr ? '🇪🇬 عربي (الأساسية)' : '🇬🇧 English' }}</span>
          </button>

          <!-- Command K Trigger -->
          <button class="hidden md:flex items-center gap-4 bg-zinc-50 border border-zinc-200 text-zinc-600 px-3 py-1 rounded-md text-xs hover:border-blue-300 transition-all">
            <div class="flex items-center gap-2">
              <Search class="w-3.5 h-3.5" />
              <span>{{ isAr ? 'بحث...' : 'Search...' }}</span>
            </div>
            <kbd class="font-mono text-[10px] bg-white px-1.5 py-0.5 rounded border border-zinc-200 text-zinc-600 shadow-2xs">⌘K</kbd>
          </button>

          <!-- Currency Indicator -->
          <div class="hidden sm:flex items-center gap-1.5 text-xs bg-zinc-50 border border-zinc-200 px-2.5 py-1 rounded-md">
            <span class="text-zinc-500 font-medium">{{ isAr ? 'الأساسية:' : 'Base:' }}</span>
            <span class="font-mono font-bold text-blue-950">{{ baseCurrency.code }} ({{ baseCurrency.symbol }})</span>
          </div>

          <!-- Live System Clock (Date, Hour, AM/PM) -->
          <div class="hidden lg:flex items-center gap-1.5 text-xs bg-blue-50/70 border border-blue-200/80 px-2.5 py-1 rounded-md text-blue-950 font-mono shadow-2xs">
            <Clock class="w-3.5 h-3.5 text-blue-700" />
            <span class="font-bold">{{ currentDateTime }}</span>
          </div>

          <!-- Notifications -->
          <button class="relative p-1.5 rounded-md text-zinc-600 hover:text-blue-900 hover:bg-blue-50 border border-zinc-200 transition-colors">
            <Bell class="w-4 h-4" />
            <span class="absolute top-1 right-1 w-2 h-2 bg-blue-600 rounded-full animate-pulse"></span>
          </button>
        </div>
      </header>

      <!-- Main Canvas Container -->
      <main class="flex-1 overflow-y-auto p-6 bg-slate-50/60 print:p-0 print:m-0 print:overflow-visible print:bg-transparent">
        <!-- Flash Alert -->
        <div v-if="page.props.flash?.success" class="no-print mb-4 p-3 bg-blue-900 text-white rounded-lg text-xs font-bold flex items-center gap-2 shadow-xs border border-blue-800">
          <ShieldCheck class="w-4 h-4 text-cyan-300" />
          <span>{{ page.props.flash.success }}</span>
        </div>

        <slot />
      </main>
    </div>
  </div>
</template>
