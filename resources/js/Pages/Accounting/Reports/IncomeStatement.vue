<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { TrendingUp, Printer, Calendar, Filter, DollarSign, Percent, ArrowUpRight, ArrowDownRight } from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  revenues: Object,
  direct_costs: Object,
  gross_profit: Number,
  expenses: Object,
  net_profit: Number,
  period: Object,
})

const startDate = ref(props.period?.start_date || '')
const endDate = ref(props.period?.end_date || '')

const applyFilter = () => {
  router.get(route('accounting.reports.income-statement'), {
    start_date: startDate.value,
    end_date: endDate.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const printReport = () => {
  window.print()
}

const formatMoney = (val) => {
  const num = parseFloat(val) || 0
  return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const grossMarginPct = computed(() => {
  const rev = props.revenues?.total || 0
  if (rev <= 0) return 0
  return ((props.gross_profit / rev) * 100).toFixed(1)
})

const netMarginPct = computed(() => {
  const rev = props.revenues?.total || 0
  if (rev <= 0) return 0
  return ((props.net_profit / rev) * 100).toFixed(1)
})
</script>

<template>
  <div class="space-y-6 max-w-5xl mx-auto print:p-0 print:space-y-3">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 print:border-b print:pb-4">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <TrendingUp class="w-6 h-6 text-blue-700" />
          <span>{{ isAr ? 'قائمة الدخل والأرباح والخسائر (P&L)' : 'Income Statement (Profit & Loss)' }}</span>
        </h1>
        <p class="text-xs text-zinc-500 mt-1">
          {{ isAr ? 'تحليل نتائج أعمال الشحن البحري وتصدير الحاصلات الزراعية وحساب مجمل وصافي الأرباح' : 'Operational Performance, Freight Margins, and Net Earnings for Agricultural Ocean Export' }}
        </p>
      </div>

      <div class="flex items-center gap-3 print:hidden">
        <button
          type="button"
          @click="printReport"
          class="px-3.5 py-2 text-xs font-bold text-zinc-700 bg-white hover:bg-zinc-50 border border-zinc-300 rounded shadow-xs flex items-center gap-2 transition-colors cursor-pointer"
        >
          <Printer class="w-4 h-4 text-zinc-600" />
          <span>{{ isAr ? 'طباعة القائمة / PDF' : 'Print / Export PDF' }}</span>
        </button>
      </div>
    </div>

    <!-- Filter Bar (Hidden on print) -->
    <UiCard >
      <form @submit.prevent="applyFilter" class="flex flex-wrap items-end gap-4">
        <div>
          <label class="block text-xs font-bold text-zinc-700 mb-1">
            {{ isAr ? 'من تاريخ:' : 'From Date:' }}
          </label>
          <input
            type="date"
            v-model="startDate"
            required
            class="text-xs font-mono rounded border border-zinc-300 px-3 py-1.5 focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-700 mb-1">
            {{ isAr ? 'إلى تاريخ:' : 'To Date:' }}
          </label>
          <input
            type="date"
            v-model="endDate"
            required
            class="text-xs font-mono rounded border border-zinc-300 px-3 py-1.5 focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
          />
        </div>

        <button
          type="submit"
          class="px-4 py-2 text-xs font-bold text-white bg-blue-700 hover:bg-blue-800 rounded shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer"
        >
          <Filter class="w-3.5 h-3.5" />
          <span>{{ isAr ? 'تحديث النتائج' : 'Update Statement' }}</span>
        </button>
      </form>
    </div>

    <!-- Printable Header Banner for PDF / Print -->
    <div class="hidden print:block text-center border-b pb-3 mb-4">
      <h2 class="text-xl font-bold text-zinc-950">M&H Agricultural Ocean Freight & Financial ERP</h2>
      <h3 class="text-sm font-semibold text-zinc-700 mt-0.5">{{ isAr ? 'قائمة الدخل والأرباح والخسائر' : 'Income Statement (Profit & Loss)' }}</h3>
      <p class="text-xs font-mono text-zinc-500 mt-1">
        {{ isAr ? 'عن الفترة من ' : 'For the period from ' }}{{ period.start_date }} {{ isAr ? 'إلى ' : 'to ' }}{{ period.end_date }}
      </p>
    </div>

    <!-- Executive Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 print:grid-cols-4 print:gap-2">
      <!-- Total Revenue -->
      <UiCard >
        <div class="text-[11px] font-bold text-emerald-700 uppercase">{{ isAr ? 'إجمالي الإيرادات التشغيلية' : 'Operating Revenue' }}</div>
        <div class="text-xl font-extrabold font-mono text-emerald-950 mt-1">{{ formatMoney(revenues?.total) }}</div>
        <div class="text-[10px] text-zinc-500 mt-0.5">{{ isAr ? 'شحن وتخليص ونولون' : 'Ocean Freight & Logistics' }}</div>
      </div>

      <!-- Direct Freight Costs -->
      <UiCard >
        <div class="text-[11px] font-bold text-amber-700 uppercase">{{ isAr ? 'تكاليف الشحن المباشرة' : 'Direct Freight Costs' }}</div>
        <div class="text-xl font-extrabold font-mono text-amber-950 mt-1">{{ formatMoney(direct_costs?.total) }}</div>
        <div class="text-[10px] text-zinc-500 mt-0.5">{{ isAr ? 'خطوط ملاحية ومقاولين' : 'Carrier slots & Haulers' }}</div>
      </div>

      <!-- Gross Profit -->
      <UiCard >
        <div class="text-[11px] font-bold text-blue-700 uppercase">{{ isAr ? 'مجمل الربح (الهامش)' : 'Gross Profit' }}</div>
        <div class="text-xl font-extrabold font-mono text-blue-950 mt-1">{{ formatMoney(gross_profit) }}</div>
        <div class="text-[10px] font-bold text-blue-700 mt-0.5">{{ grossMarginPct }}% {{ isAr ? 'هامش مجمل الربح' : 'Margin' }}</div>
      </div>

      <!-- Net Profit -->
      <UiCard 
        
        :class="net_profit >= 0 ? 'bg-emerald-50/60 border-emerald-300' : 'bg-rose-50/60 border-rose-300'"
      >
        <div class="text-[11px] font-bold uppercase" :class="net_profit >= 0 ? 'text-emerald-700' : 'text-rose-700'">
          {{ isAr ? 'صافي أرباح / (خسائر) الفترة' : 'Net Profit / (Loss)' }}
        </div>
        <div class="text-xl font-extrabold font-mono mt-1" :class="net_profit >= 0 ? 'text-emerald-900' : 'text-rose-900'">
          {{ formatMoney(net_profit) }}
        </div>
        <div class="text-[10px] font-bold mt-0.5" :class="net_profit >= 0 ? 'text-emerald-700' : 'text-rose-700'">
          {{ netMarginPct }}% {{ isAr ? 'هامش صافي الربح' : 'Net Margin' }}
        </div>
      </div>
    </div>

    <!-- Formal Corporate Statement Card -->
    <UiCard >
      <div class="p-4 bg-zinc-50 border-b border-zinc-200 flex items-center justify-between">
        <span class="font-bold text-xs text-zinc-800 uppercase tracking-wider">
          {{ isAr ? 'تفاصيل بنود قائمة الدخل (عملة الأساس USD)' : 'Income Statement Breakdown (Base Currency USD)' }}
        </span>
        <span class="font-mono text-xs text-zinc-500">
          {{ period.start_date }} - {{ period.end_date }}
        </span>
      </div>

      <div class="divide-y divide-zinc-200 text-xs font-sans">
        <!-- 1. Operational Revenues -->
        <div class="p-4 space-y-2 bg-emerald-50/15">
          <div class="flex items-center justify-between font-bold text-sm text-emerald-950 pb-1 border-b border-emerald-100">
            <span>{{ isAr ? '1. الإيرادات التشغيلية (Operating Revenues)' : '1. Operating Revenues' }}</span>
            <span class="font-mono text-base font-extrabold">{{ formatMoney(revenues?.total) }}</span>
          </div>
          <div class="space-y-1.5 pt-1 pl-4 pr-4">
            <div v-for="item in revenues?.items" :key="item.code" class="flex items-center justify-between text-zinc-700 hover:text-zinc-950">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[11px] text-zinc-400">{{ item.code }}</span>
                <span>{{ isAr ? item.name_ar : item.name_en }}</span>
              </span>
              <span class="font-mono font-medium">{{ formatMoney(item.amount) }}</span>
            </div>
            <div v-if="!revenues?.items?.length" class="text-zinc-400 italic text-[11px]">
              {{ isAr ? 'لا توجد إيرادات مسجلة خلال هذه الفترة' : 'No revenue movements recorded in this period.' }}
            </div>
          </div>
        </div>

        <!-- 2. Direct Costs (Cost of Goods / Freight Sold) -->
        <div class="p-4 space-y-2 bg-amber-50/15">
          <div class="flex items-center justify-between font-bold text-sm text-amber-950 pb-1 border-b border-amber-100">
            <span>{{ isAr ? '2. تكاليف الشحن والتشغيل المباشرة (Direct Freight Costs)' : '2. Direct Freight Costs' }}</span>
            <span class="font-mono text-base font-extrabold text-amber-900">({{ formatMoney(direct_costs?.total) }})</span>
          </div>
          <div class="space-y-1.5 pt-1 pl-4 pr-4">
            <div v-for="item in direct_costs?.items" :key="item.code" class="flex items-center justify-between text-zinc-700 hover:text-zinc-950">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[11px] text-zinc-400">{{ item.code }}</span>
                <span>{{ isAr ? item.name_ar : item.name_en }}</span>
              </span>
              <span class="font-mono font-medium text-amber-900">({{ formatMoney(item.amount) }})</span>
            </div>
            <div v-if="!direct_costs?.items?.length" class="text-zinc-400 italic text-[11px]">
              {{ isAr ? 'لا توجد تكاليف شحن مباشرة مسجلة خلال هذه الفترة' : 'No direct cost movements recorded.' }}
            </div>
          </div>
        </div>

        <!-- 3. Gross Profit Summary Row -->
        <div class="p-4 bg-blue-50/40 flex items-center justify-between font-extrabold text-sm text-blue-950 border-y-2 border-blue-200">
          <div class="flex items-center gap-2">
            <span>{{ isAr ? '3. إجمالي الربح التشغيلي (Gross Profit)' : '3. Gross Profit' }}</span>
            <span class="px-2 py-0.5 rounded text-[10px] bg-blue-100 text-blue-800 font-mono font-bold">{{ grossMarginPct }}%</span>
          </div>
          <span class="font-mono text-base text-blue-900">{{ formatMoney(gross_profit) }}</span>
        </div>

        <!-- 4. General & Administrative Expenses -->
        <div class="p-4 space-y-2 bg-rose-50/15">
          <div class="flex items-center justify-between font-bold text-sm text-rose-950 pb-1 border-b border-rose-100">
            <span>{{ isAr ? '4. المصروفات العمومية والإدارية (G&A Expenses)' : '4. General & Administrative Expenses' }}</span>
            <span class="font-mono text-base font-extrabold text-rose-900">({{ formatMoney(expenses?.total) }})</span>
          </div>
          <div class="space-y-1.5 pt-1 pl-4 pr-4">
            <div v-for="item in expenses?.items" :key="item.code" class="flex items-center justify-between text-zinc-700 hover:text-zinc-950">
              <span class="flex items-center gap-2">
                <span class="font-mono text-[11px] text-zinc-400">{{ item.code }}</span>
                <span>{{ isAr ? item.name_ar : item.name_en }}</span>
              </span>
              <span class="font-mono font-medium text-rose-900">({{ formatMoney(item.amount) }})</span>
            </div>
            <div v-if="!expenses?.items?.length" class="text-zinc-400 italic text-[11px]">
              {{ isAr ? 'لا توجد مصروفات إدارية مسجلة خلال هذه الفترة' : 'No administrative expenses recorded.' }}
            </div>
          </div>
        </div>

        <!-- 5. Net Profit / (Loss) Bottom Line -->
        <div 
          class="p-5 flex items-center justify-between font-extrabold text-base border-t-2"
          :class="net_profit >= 0 ? 'bg-emerald-100/50 text-emerald-950 border-emerald-400' : 'bg-rose-100/50 text-rose-950 border-rose-400'"
        >
          <div class="flex items-center gap-2">
            <span>{{ isAr ? '5. صافي الربح / (الخسارة) النهائي للفترة (Net Income)' : '5. Net Income / (Loss)' }}</span>
            <span class="px-2 py-0.5 rounded text-[11px] font-mono font-bold" :class="net_profit >= 0 ? 'bg-emerald-200 text-emerald-900' : 'bg-rose-200 text-rose-900'">
              {{ netMarginPct }}% {{ isAr ? 'صافي الهامش' : 'Net Margin' }}
            </span>
          </div>
          <span class="font-mono text-xl tracking-tight" :class="net_profit >= 0 ? 'text-emerald-950' : 'text-rose-950'">
            {{ formatMoney(net_profit) }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
