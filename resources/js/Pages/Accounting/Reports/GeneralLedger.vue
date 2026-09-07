<script setup>
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { BookOpen, Printer, Calendar, Filter, ArrowUpDown, Building2 } from 'lucide-vue-next'

const { isAr, formatDateTime } = useI18n()

const props = defineProps({
  accounts: Array,
  selectedAccount: Object,
  openingBalance: Number,
  lines: Array,
  closingBalance: Number,
  filters: Object,
})

const accountId = ref(props.filters?.account_id || props.selectedAccount?.id || '')
const startDate = ref(props.filters?.start_date || '')
const endDate = ref(props.filters?.end_date || '')

const applyFilter = () => {
  router.get(route('accounting.reports.general-ledger'), {
    account_id: accountId.value,
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
  if (Math.abs(num) < 0.005) return '-'
  return '$' + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const totalPeriodDebit = computed(() => {
  return (props.lines || []).reduce((sum, l) => sum + (parseFloat(l.debit) || 0), 0)
})

const totalPeriodCredit = computed(() => {
  return (props.lines || []).reduce((sum, l) => sum + (parseFloat(l.credit) || 0), 0)
})

const isDebitNature = computed(() => {
  return ['ASSET', 'EXPENSE'].includes(props.selectedAccount?.type)
})
</script>

<template>
  <div class="space-y-6 max-w-7xl mx-auto print:p-0 print:space-y-3">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 print:border-b print:pb-4">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <BookOpen class="w-6 h-6 text-blue-700" />
          <span>{{ isAr ? 'دفتر الأستاذ العام للحسابات (GL Ledger)' : 'General Ledger Account Statement' }}</span>
        </h1>
        <p class="text-xs text-zinc-500 mt-1">
          {{ isAr ? 'كشف تفصيلي لحركات الحسابات المالية، الرصيد الافتتاحي، وخط السير التراكمي للقيود' : 'Detailed ledger movements, opening balance, and chronological running balance per financial account' }}
        </p>
      </div>

      <div class="flex items-center gap-3 print:hidden">
        <button
          type="button"
          @click="printReport"
          class="px-3.5 py-2 text-xs font-bold text-zinc-700 bg-white hover:bg-zinc-50 border border-zinc-300 rounded shadow-xs flex items-center gap-2 transition-colors cursor-pointer"
        >
          <Printer class="w-4 h-4 text-zinc-600" />
          <span>{{ isAr ? 'طباعة كشف الحساب / PDF' : 'Print / Export PDF' }}</span>
        </button>
      </div>
    </div>

    <!-- Filter Form -->
    <div class="shadcn-card p-4 bg-white border border-zinc-200 print:hidden">
      <form @submit.prevent="applyFilter" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div class="md:col-span-2">
          <label class="block text-xs font-bold text-zinc-700 mb-1">
            {{ isAr ? 'اختر الحساب المالي المراد عرضه: *' : 'Select Financial Account: *' }}
          </label>
          <select
            v-model="accountId"
            required
            class="w-full text-xs rounded border border-zinc-300 px-3 py-1.5 focus:ring-1 focus:ring-blue-600 focus:border-blue-600 font-medium"
          >
            <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
              {{ acc.code }} - {{ isAr ? acc.name_ar : acc.name_en }} ({{ acc.type }})
            </option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-zinc-700 mb-1">
            {{ isAr ? 'من تاريخ:' : 'From Date:' }}
          </label>
          <input
            type="date"
            v-model="startDate"
            class="w-full text-xs font-mono rounded border border-zinc-300 px-3 py-1.5 focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
          />
        </div>

        <div class="flex items-center gap-2">
          <div class="flex-1">
            <label class="block text-xs font-bold text-zinc-700 mb-1">
              {{ isAr ? 'إلى تاريخ:' : 'To Date:' }}
            </label>
            <input
              type="date"
              v-model="endDate"
              class="w-full text-xs font-mono rounded border border-zinc-300 px-3 py-1.5 focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
            />
          </div>

          <button
            type="submit"
            class="px-4 py-2 mt-5 text-xs font-bold text-white bg-blue-700 hover:bg-blue-800 rounded shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer flex-shrink-0"
          >
            <Filter class="w-3.5 h-3.5" />
            <span>{{ isAr ? 'عرض' : 'Filter' }}</span>
          </button>
        </div>
      </form>
    </div>

    <!-- Printable Header Banner -->
    <div class="hidden print:block text-center border-b pb-3 mb-4">
      <h2 class="text-xl font-bold text-zinc-950">M&H Agricultural Ocean Freight & Financial ERP</h2>
      <h3 class="text-sm font-semibold text-zinc-700 mt-0.5">{{ isAr ? 'كشف حساب الأستاذ العام' : 'General Ledger Account Statement' }}</h3>
      <p class="text-xs font-bold text-blue-900 mt-1">
        {{ selectedAccount?.code }} - {{ isAr ? selectedAccount?.name_ar : selectedAccount?.name_en }}
      </p>
    </div>

    <!-- Account Details Summary -->
    <div v-if="selectedAccount" class="grid grid-cols-2 md:grid-cols-5 gap-3 print:grid-cols-5 print:gap-2">
      <!-- Account Info -->
      <div class="shadcn-card p-3 bg-white border border-zinc-200 col-span-2 md:col-span-1">
        <div class="text-[10px] font-bold text-zinc-500 uppercase">{{ isAr ? 'طبيعة الحساب' : 'Account Nature' }}</div>
        <div class="text-sm font-extrabold text-blue-950 mt-1">{{ selectedAccount.code }}</div>
        <div class="text-[11px] font-medium text-zinc-700 truncate">
          {{ isAr ? selectedAccount.name_ar : selectedAccount.name_en }}
        </div>
        <div class="text-[10px] font-mono mt-0.5 text-zinc-500">
          {{ isDebitNature ? (isAr ? 'مدين بطبيعته (Dr)' : 'Debit Nature') : (isAr ? 'دائن بطبيعته (Cr)' : 'Credit Nature') }}
        </div>
      </div>

      <!-- Opening Balance -->
      <div class="shadcn-card p-3 bg-white border border-zinc-200">
        <div class="text-[10px] font-bold text-zinc-500 uppercase">{{ isAr ? 'الرصيد الافتتاحي' : 'Opening Balance' }}</div>
        <div class="text-base font-extrabold font-mono text-zinc-900 mt-1">
          {{ formatMoney(openingBalance) }}
        </div>
        <div class="text-[10px] text-zinc-400 mt-0.5">{{ isAr ? 'حتى بداية الفترة' : 'Before period' }}</div>
      </div>

      <!-- Period Debit -->
      <div class="shadcn-card p-3 bg-white border border-zinc-200">
        <div class="text-[10px] font-bold text-blue-600 uppercase">{{ isAr ? 'مجموع المدين (الفترة)' : 'Period Debits' }}</div>
        <div class="text-base font-extrabold font-mono text-blue-900 mt-1">
          {{ formatMoney(totalPeriodDebit) }}
        </div>
        <div class="text-[10px] text-zinc-400 mt-0.5">{{ (lines || []).length }} {{ isAr ? 'حركات' : 'entries' }}</div>
      </div>

      <!-- Period Credit -->
      <div class="shadcn-card p-3 bg-white border border-zinc-200">
        <div class="text-[10px] font-bold text-blue-600 uppercase">{{ isAr ? 'مجموع الدائن (الفترة)' : 'Period Credits' }}</div>
        <div class="text-base font-extrabold font-mono text-blue-900 mt-1">
          {{ formatMoney(totalPeriodCredit) }}
        </div>
        <div class="text-[10px] text-zinc-400 mt-0.5">{{ (lines || []).length }} {{ isAr ? 'حركات' : 'entries' }}</div>
      </div>

      <!-- Closing Balance -->
      <div class="shadcn-card p-3 bg-white border border-zinc-200">
        <div class="text-[10px] font-bold text-emerald-700 uppercase">{{ isAr ? 'الرصيد الختامي' : 'Closing Balance' }}</div>
        <div class="text-base font-extrabold font-mono text-emerald-950 mt-1">
          {{ formatMoney(closingBalance) }}
        </div>
        <div class="text-[10px] font-mono text-zinc-500 mt-0.5">
          {{ closingBalance >= 0 ? (isDebitNature ? 'Dr' : 'Cr') : (isDebitNature ? 'Cr' : 'Dr') }}
        </div>
      </div>
    </div>

    <!-- Ledger Lines Table -->
    <div class="shadcn-card overflow-hidden bg-white border border-zinc-200 shadow-xs">
      <div class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="p-3">{{ isAr ? 'التاريخ' : 'Date' }}</th>
              <th class="p-3">{{ isAr ? 'رقم القيد' : 'JV #' }}</th>
              <th class="p-3 min-w-[220px]">{{ isAr ? 'بيان القيد والشرح' : 'Narration & Description' }}</th>
              <th class="p-3">{{ isAr ? 'الطرف / المستفيد' : 'Sub-ledger Party' }}</th>
              <th class="p-3 text-right text-blue-950">{{ isAr ? 'مدين (Debit)' : 'Debit' }}</th>
              <th class="p-3 text-right text-blue-950">{{ isAr ? 'دائن (Credit)' : 'Credit' }}</th>
              <th class="p-3 text-right text-emerald-950 bg-emerald-50/40">{{ isAr ? 'الرصيد التراكمي' : 'Running Balance' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200 font-mono text-[11px]">
            <!-- Opening Balance Row -->
            <tr class="bg-zinc-50/70 font-semibold">
              <td class="p-2.5 text-zinc-500 font-sans" colspan="4">
                {{ isAr ? 'الرصيد الافتتاحي المنقول (Opening Balance Forward)' : 'Opening Balance Brought Forward' }}
              </td>
              <td class="p-2.5 text-right">-</td>
              <td class="p-2.5 text-right">-</td>
              <td class="p-2.5 text-right font-extrabold text-emerald-950 bg-emerald-50/30">
                {{ formatMoney(openingBalance) }}
              </td>
            </tr>

            <!-- Transaction Rows -->
            <tr v-for="line in lines" :key="line.id" class="hover:bg-blue-50/30 transition-colors">
              <td class="p-2.5 text-zinc-600 whitespace-nowrap text-[11px]">
                {{ formatDateTime(line.created_at || line.entry_date) }}
              </td>
              <td class="p-2.5 font-bold text-blue-800">
                {{ line.entry_number }}
              </td>
              <td class="p-2.5 font-sans">
                <div class="font-bold text-zinc-900">{{ line.narration }}</div>
                <div v-if="line.line_description && line.line_description !== line.narration" class="text-[10px] text-zinc-500 mt-0.5">
                  {{ line.line_description }}
                </div>
              </td>
              <td class="p-2.5 font-sans text-zinc-700">
                {{ line.party_name || '-' }}
              </td>
              <td class="p-2.5 text-right font-bold text-zinc-900">
                {{ formatMoney(line.debit) }}
              </td>
              <td class="p-2.5 text-right font-bold text-zinc-900">
                {{ formatMoney(line.credit) }}
              </td>
              <td class="p-2.5 text-right font-extrabold text-emerald-950 bg-emerald-50/20">
                {{ formatMoney(line.running_balance) }}
              </td>
            </tr>

            <tr v-if="!lines?.length">
              <td colspan="7" class="p-8 text-center text-zinc-500 font-sans">
                {{ isAr ? 'لا توجد حركات مسجلة على هذا الحساب في الفترة المحددة' : 'No movements recorded on this account in the specified period.' }}
              </td>
            </tr>
          </tbody>

          <!-- Totals Footer -->
          <tfoot class="bg-zinc-100 font-mono font-extrabold text-xs border-t-2 border-zinc-300">
            <tr>
              <td colspan="4" class="p-3 text-zinc-900 font-sans uppercase">
                {{ isAr ? 'المجاميع والرصيد الختامي:' : 'Totals & Closing Balance:' }}
              </td>
              <td class="p-3 text-right text-blue-900">
                {{ formatMoney(totalPeriodDebit) }}
              </td>
              <td class="p-3 text-right text-blue-900">
                {{ formatMoney(totalPeriodCredit) }}
              </td>
              <td class="p-3 text-right text-emerald-950 bg-emerald-100/60 font-extrabold text-sm">
                {{ formatMoney(closingBalance) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</template>
