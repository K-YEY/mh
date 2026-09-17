<script setup>
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { useCurrency } from '@/Composables/useCurrency'
import { 
  Building2, 
  Printer, 
  FileSpreadsheet, 
  Download, 
  Search, 
  X,
  ArrowUpRight, 
  ArrowDownLeft, 
  ChevronDown,
  UserCheck
} from 'lucide-vue-next'

const { isAr, formatDateTime } = useI18n()
const { formatMoney, currentCurrency, baseCurrency, convertAmount } = useCurrency()

const props = defineProps({
  parties: Array,
  selected_party: Object,
  lines: Array,
  totals: Object,
})

const selectedPartyId = ref(props.selected_party?.id || '')
const roleFilter = ref('ALL')
const lineSearch = ref('')

const roleTabs = [
  { value: 'ALL', labelAr: 'الكل', labelEn: 'All' },
  { value: 'CUSTOMER', labelAr: 'العملاء', labelEn: 'Customers' },
  { value: 'SHIPPING_LINE', labelAr: 'خطوط الشحن', labelEn: 'Shipping Lines' },
  { value: 'CUSTOMS_BROKER', labelAr: 'التخليص', labelEn: 'Customs' },
  { value: 'TRUCKER', labelAr: 'النقل البري', labelEn: 'Truckers' },
]

const filteredParties = computed(() => {
  if (roleFilter.value === 'ALL') return props.parties || []
  return (props.parties || []).filter(p => 
    p.roles?.some(r => r.role === roleFilter.value)
  )
})

const loadStatement = () => {
  if (selectedPartyId.value) {
    router.get(route('ledger.statement'), { party_id: selectedPartyId.value }, { preserveState: true })
  }
}

const printStatement = () => {
  window.print()
}

// Running balance calculation with currency awareness
const ledgerLines = computed(() => {
  let balance = 0
  const q = lineSearch.value.toLowerCase()

  return (props.lines || []).map((l, index) => {
    const rawDebit = Number(l.debit_base || l.debit || 0)
    const rawCredit = Number(l.credit_base || l.credit || 0)
    balance += (rawDebit - rawCredit)
    return {
      ...l,
      index: index + 1,
      rawDebit,
      rawCredit,
      rawBalance: balance,
    }
  }).filter(l => {
    if (!q) return true
    return (
      l.journal_entry?.entry_number?.toLowerCase().includes(q) ||
      l.account?.code?.includes(q) ||
      l.account?.name_ar?.toLowerCase().includes(q) ||
      l.description?.toLowerCase().includes(q) ||
      l.journal_entry?.narration?.toLowerCase().includes(q)
    )
  })
})

const exportCsv = () => {
  if (!props.lines?.length || !props.selected_party) return

  const code = currentCurrency.value.code
  const headers = ['#', 'Entry Number', 'Date', 'Account Code', 'Account Name', 'Description', `Debit (${code})`, `Credit (${code})`]
  const rows = ledgerLines.value.map((l, i) => [
    i + 1,
    `"${l.journal_entry?.entry_number || ''}"`,
    `"${l.journal_entry?.entry_date || ''}"`,
    `"${l.account?.code || ''}"`,
    `"${l.account?.name_ar || ''}"`,
    `"${(l.description || l.journal_entry?.narration || '').replace(/"/g, '""')}"`,
    convertAmount(l.rawDebit).toFixed(2),
    convertAmount(l.rawCredit).toFixed(2)
  ])

  const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const link = document.createElement('a')
  link.href = encodeURI(csvContent)
  link.download = `Statement_${props.selected_party.code}_${code}.csv`
  link.click()
}

const roleName = (role) => {
  const map = {
    CUSTOMER: isAr.value ? 'عميل' : 'Customer',
    SHIPPING_LINE: isAr.value ? 'خط ملاحي' : 'Carrier',
    OVERSEAS_AGENT: isAr.value ? 'وكيل' : 'Agent',
    CUSTOMS_BROKER: isAr.value ? 'مخلص' : 'Customs',
    TRUCKER: isAr.value ? 'نقل بري' : 'Trucker',
    PACKHOUSE: isAr.value ? 'محطة تعبئة' : 'Packhouse',
  }
  return map[role] || role
}
</script>

<template>
  <div class="max-w-7xl mx-auto space-y-6 pb-20">
    <!-- Apple Header (Screen Only) -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2 print:hidden">
      <div>
        <h1 class="text-2xl font-bold text-zinc-950 tracking-tight flex items-center gap-2.5">
          <FileSpreadsheet class="w-6 h-6 text-zinc-900" />
          <span>{{ isAr ? 'كشف حساب الأطراف المالي' : 'Party Statement of Account' }}</span>
        </h1>
        <p class="text-xs text-zinc-500 mt-1">
          {{ isAr ? 'استعراض الحركات والقيود المالية المباشرة مع الرصيد التراكمي المستحق' : 'Isolated financial transaction history and running balances per partner' }}
        </p>
      </div>

      <div class="flex items-center gap-2">
        <Link 
          :href="route('parties.index')" 
          class="px-3.5 py-2 rounded-xl text-xs font-semibold text-zinc-700 bg-zinc-100 hover:bg-zinc-200 transition"
        >
          {{ isAr ? 'سجل الشركات' : 'Companies Directory' }}
        </Link>
        <button
          v-if="selected_party"
          @click="exportCsv"
          class="px-3.5 py-2 rounded-xl text-xs font-semibold text-zinc-700 bg-zinc-100 hover:bg-zinc-200 transition cursor-pointer flex items-center gap-1.5"
        >
          <Download class="w-3.5 h-3.5" />
          <span>{{ isAr ? 'تصدير' : 'Export' }}</span>
        </button>
        <button 
          v-if="selected_party"
          @click="printStatement" 
          class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-zinc-950 hover:bg-zinc-800 shadow-sm transition cursor-pointer"
        >
          <Printer class="w-4 h-4" />
          <span>{{ isAr ? 'طباعة الكشف' : 'Print' }}</span>
        </button>
      </div>
    </div>

    <!-- Apple Segmented Selector Bar (Screen Only) -->
    <div class="bg-white/80 backdrop-blur-md p-3 rounded-2xl border border-zinc-200/80 shadow-xs space-y-3 print:hidden">
      <div class="flex items-center gap-1 overflow-x-auto pb-1 scrollbar-none">
        <button
          v-for="tab in roleTabs"
          :key="tab.value"
          @click="roleFilter = tab.value"
          :class="[
            'px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition cursor-pointer',
            roleFilter === tab.value
              ? 'bg-zinc-950 text-white shadow-xs'
              : 'text-zinc-600 hover:text-zinc-950 hover:bg-zinc-100'
          ]"
        >
          {{ isAr ? tab.labelAr : tab.labelEn }}
        </button>
      </div>

      <div>
        <select 
          v-model="selectedPartyId" 
          @change="loadStatement" 
          class="w-full bg-zinc-100/80 rounded-xl px-4 py-2.5 text-xs sm:text-sm font-semibold text-zinc-900 border-none focus:bg-white focus:ring-2 focus:ring-zinc-950/10 cursor-pointer transition"
        >
          <option value="" disabled>{{ isAr ? '-- اختر الشركة لاستخراج كشف الحساب --' : '-- Choose Company --' }}</option>
          <option 
            v-for="p in filteredParties" 
            :key="p.id" 
            :value="p.id"
          >
            {{ p.legal_name }} ({{ p.code }}) {{ p.account ? `— [حساب: ${p.account.code}]` : '' }}
          </option>
        </select>
      </div>
    </div>

    <!-- Statement Details -->
    <div v-if="selected_party" class="space-y-6">
      <!-- Apple Serene Company Header Card -->
      <div class="bg-white rounded-3xl p-6 sm:p-7 border border-zinc-200/80 shadow-xs space-y-4 print:shadow-none print:border-b-2 print:border-zinc-950 print:p-0">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-zinc-100 pb-4">
          <div>
            <div class="flex items-center gap-2">
              <span class="font-mono text-xs font-bold bg-zinc-100 text-zinc-800 px-2 py-0.5 rounded-md">
                {{ selected_party.code }}
              </span>
              <span 
                v-for="role in selected_party.roles" 
                :key="role.id"
                class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-blue-700"
              >
                {{ roleName(role.role) }}
              </span>
            </div>
            <h2 class="text-2xl font-bold text-zinc-950 mt-1.5 tracking-tight">
              {{ selected_party.legal_name }}
            </h2>
          </div>

          <div class="sm:text-end text-xs text-zinc-500 font-mono">
            <div>{{ new Date().toLocaleDateString(isAr ? 'ar-EG' : 'en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</div>
            <div class="text-[11px] mt-0.5 text-zinc-400 font-mono">{{ currentCurrency.code }} ({{ currentCurrency.symbol }}) | {{ currentCurrency.name }}</div>
          </div>
        </div>

        <!-- Single Clean Financial Account Line -->
        <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
          <div class="flex items-center gap-2">
            <span class="text-zinc-400">{{ isAr ? 'الحساب المالي المخصص:' : 'GL Account:' }}</span>
            <div v-if="selected_party.account" class="flex items-center gap-1.5 font-semibold text-zinc-950">
              <span class="font-mono font-bold bg-zinc-100 px-1.5 py-0.5 rounded border border-zinc-200 text-xs">
                {{ selected_party.account.code }}
              </span>
              <span>{{ isAr ? selected_party.account.name_ar : selected_party.account.name_en }}</span>
            </div>
            <div v-else class="text-zinc-600 font-medium">
              {{ isAr ? 'حساب مجمع عام' : 'General control account' }}
            </div>
          </div>

          <div v-if="selected_party.tax_number" class="text-zinc-500 font-mono text-[11px]">
            {{ isAr ? 'الرقم الضريبي:' : 'Tax ID:' }} <span class="text-zinc-900 font-bold">{{ selected_party.tax_number }}</span>
          </div>
        </div>
      </div>

      <!-- 3 Apple Metric Balance Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Debits -->
        <div class="bg-white rounded-2xl p-5 border border-zinc-200/80 text-center shadow-xs">
          <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">
            {{ isAr ? 'إجمالي المدين' : 'Total Debits' }} ({{ currentCurrency.symbol }})
          </div>
          <div class="text-2xl sm:text-3xl font-bold text-zinc-950 font-mono mt-1">
            {{ formatMoney(totals.debit || 0) }}
          </div>
        </div>

        <!-- Credits -->
        <div class="bg-white rounded-2xl p-5 border border-zinc-200/80 text-center shadow-xs">
          <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-500">
            {{ isAr ? 'إجمالي الدائن' : 'Total Credits' }} ({{ currentCurrency.symbol }})
          </div>
          <div class="text-2xl sm:text-3xl font-bold text-zinc-950 font-mono mt-1">
            {{ formatMoney(totals.credit || 0) }}
          </div>
        </div>

        <!-- Net Outstanding Balance -->
        <div class="bg-zinc-950 text-white rounded-2xl p-5 text-center shadow-sm">
          <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">
            {{ isAr ? 'صافي الرصيد المستحق' : 'Outstanding Balance' }} ({{ currentCurrency.symbol }})
          </div>
          <div class="text-2xl sm:text-3xl font-bold text-white font-mono mt-1">
            {{ formatMoney(Math.abs(Number(totals.balance || 0))) }}
          </div>
          <div class="text-[11px] text-zinc-400 font-medium mt-0.5">
            <span v-if="totals.balance > 0">{{ isAr ? 'رصيد مدين (مستحق لنا)' : 'Debit Balance (Receivable)' }}</span>
            <span v-else-if="totals.balance < 0">{{ isAr ? 'رصيد دائن (مستحق للشركة)' : 'Credit Balance (Payable)' }}</span>
            <span v-else>{{ isAr ? 'الحساب خالص ومتزن' : 'Balanced (Zero)' }}</span>
          </div>
        </div>
      </div>

      <!-- Ledger Table (Spacious, Calm, Apple Style) -->
      <div class="bg-white rounded-3xl border border-zinc-200/80 overflow-hidden shadow-xs">
        <!-- Search within lines (Screen only) -->
        <div class="p-3.5 px-5 bg-zinc-50 border-b border-zinc-100 flex items-center justify-between gap-3 print:hidden">
          <span class="text-xs font-bold text-zinc-800">
            {{ isAr ? 'القيود والحركات المالية' : 'Transactions Ledger' }}
          </span>

          <div class="relative w-48">
            <Search class="w-3.5 h-3.5 text-zinc-400 absolute start-3 top-1/2 -translate-y-1/2" />
            <input 
              v-model="lineSearch" 
              type="text" 
              :placeholder="isAr ? 'تصفية الحركات...' : 'Filter entries...'" 
              class="w-full bg-white rounded-xl text-xs ps-8 pe-3 py-1.5 border border-zinc-200 text-zinc-900"
            />
          </div>
        </div>

        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100/60 text-zinc-500 uppercase text-[10px] tracking-wider border-b border-zinc-100 font-mono">
            <tr>
              <th class="p-4 w-12 text-center">#</th>
              <th class="p-4">{{ isAr ? 'رقم القيد والتاريخ' : 'JV Entry' }}</th>
              <th class="p-4">{{ isAr ? 'الحساب المالي' : 'GL Account' }}</th>
              <th class="p-4">{{ isAr ? 'البيان والوصف' : 'Description' }}</th>
              <th class="p-4 text-right">{{ isAr ? 'مدين (' + currentCurrency.symbol + ')' : 'Debit (' + currentCurrency.code + ')' }}</th>
              <th class="p-4 text-right">{{ isAr ? 'دائن (' + currentCurrency.symbol + ')' : 'Credit (' + currentCurrency.code + ')' }}</th>
              <th class="p-4 text-right font-bold text-zinc-950">{{ isAr ? 'الرصيد التراكمي (' + currentCurrency.symbol + ')' : 'Balance (' + currentCurrency.code + ')' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-100 font-mono">
            <tr 
              v-for="l in ledgerLines" 
              :key="l.id" 
              class="hover:bg-zinc-50/70 transition-colors"
            >
              <td class="p-4 text-center text-zinc-400 text-[11px]">{{ l.index }}</td>

              <td class="p-4 font-bold text-zinc-950 whitespace-nowrap">
                <span>{{ l.journal_entry?.entry_number }}</span>
                <div class="text-[10px] text-zinc-400 font-normal mt-0.5">
                  {{ formatDateTime(l.journal_entry?.entry_date || l.journal_entry?.created_at) }}
                </div>
              </td>

              <td class="p-4 font-sans font-medium text-zinc-900">
                <div class="text-xs">{{ isAr ? l.account?.name_ar : l.account?.name_en }}</div>
                <div class="text-[10px] text-zinc-400 font-mono mt-0.5">[{{ l.account?.code }}]</div>
              </td>

              <td class="p-4 text-zinc-600 font-sans text-xs">
                {{ l.description || l.journal_entry?.narration }}
              </td>

              <td class="p-4 text-right font-bold text-zinc-950">
                {{ l.rawDebit > 0 ? formatMoney(l.rawDebit) : '-' }}
              </td>

              <td class="p-4 text-right font-bold text-zinc-950">
                {{ l.rawCredit > 0 ? formatMoney(l.rawCredit) : '-' }}
              </td>

              <td class="p-4 text-right font-bold text-zinc-950">
                {{ formatMoney(l.rawBalance) }}
              </td>
            </tr>

            <tr v-if="!ledgerLines.length">
              <td colspan="7" class="p-14 text-center text-zinc-400 font-sans">
                <UserCheck class="w-8 h-8 mx-auto mb-2 text-zinc-300" />
                <div class="font-bold text-zinc-700">{{ isAr ? 'لا توجد قيود مسجلة' : 'No entries recorded' }}</div>
              </td>
            </tr>
          </tbody>

          <!-- Calm Total Row -->
          <tfoot v-if="ledgerLines.length" class="bg-zinc-50 text-xs font-mono font-bold border-t border-zinc-200">
            <tr>
              <td colspan="4" class="p-4 text-end text-zinc-600 font-sans">
                {{ isAr ? 'الإجمالي النهائي:' : 'Grand Totals:' }}
              </td>
              <td class="p-4 text-right text-zinc-950 font-bold">
                {{ formatMoney(totals.debit || 0) }}
              </td>
              <td class="p-4 text-right text-zinc-950 font-bold">
                {{ formatMoney(totals.credit || 0) }}
              </td>
              <td class="p-4 text-right text-zinc-950 font-extrabold text-sm">
                {{ formatMoney(totals.balance || 0) }}
              </td>
            </tr>
          </tfoot>
        </table>
        </table>
      </div>
    </div>
  </div>
</template>
