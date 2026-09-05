<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { BookOpen, Scale, Plus, Trash2, ArrowLeft, ArrowRight, CheckCircle2, AlertTriangle, Save } from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  accounts: Array,
  parties: Array,
  currencies: Array,
})

const defaultCurrency = 'USD'

const form = useForm({
  entry_date: new Date().toISOString().split('T')[0],
  narration: '',
  reference_number: '',
  lines: [
    {
      account_id: '',
      party_id: null,
      description: '',
      currency_code: defaultCurrency,
      exchange_rate: 1.0,
      debit: 0,
      credit: 0,
    },
    {
      account_id: '',
      party_id: null,
      description: '',
      currency_code: defaultCurrency,
      exchange_rate: 1.0,
      debit: 0,
      credit: 0,
    },
  ],
})

const addLine = () => {
  form.lines.push({
    account_id: '',
    party_id: null,
    description: form.narration || '',
    currency_code: defaultCurrency,
    exchange_rate: 1.0,
    debit: 0,
    credit: 0,
  })
}

const removeLine = (index) => {
  if (form.lines.length > 2) {
    form.lines.splice(index, 1)
  }
}

// Balance calculations in base currency (USD)
const totalDebitBase = computed(() => {
  return form.lines.reduce((sum, line) => {
    const debit = parseFloat(line.debit) || 0
    const rate = parseFloat(line.exchange_rate) || 1.0
    return sum + (debit * rate)
  }, 0)
})

const totalCreditBase = computed(() => {
  return form.lines.reduce((sum, line) => {
    const credit = parseFloat(line.credit) || 0
    const rate = parseFloat(line.exchange_rate) || 1.0
    return sum + (credit * rate)
  }, 0)
})

const difference = computed(() => {
  return Math.abs(totalDebitBase.value - totalCreditBase.value)
})

const isBalanced = computed(() => {
  return difference.value < 0.005 && totalDebitBase.value > 0
})

const submit = () => {
  if (!isBalanced.value) {
    return
  }
  form.post(route('ledger.journals.store'))
}
</script>

<template>
  <div class="space-y-6 max-w-7xl mx-auto pb-12">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 text-xs text-zinc-500 mb-1 font-mono">
          <Link :href="route('ledger.journals')" class="hover:text-blue-600 transition-colors">
            {{ isAr ? 'دفتر اليومية العامة' : 'General Ledger' }}
          </Link>
          <span>/</span>
          <span class="text-zinc-900 font-bold">{{ isAr ? 'إنشاء قيد تسوية جديد' : 'New Journal Entry' }}</span>
        </div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <BookOpen class="w-6 h-6 text-blue-700" />
          <span>{{ isAr ? 'إنشاء قيد يومية يدوي / تسوية مالية (JV)' : 'Manual Journal Voucher Entry (JV)' }}</span>
        </h1>
        <p class="text-xs text-zinc-500 mt-1">
          {{ isAr ? 'تسجيل القيود اليدوية والتسويات الجردية والتحويلات مع التحقق الفوري من توازن القيد' : 'Record manual adjustments, inter-account transfers, and depreciation with real-time balance enforcement' }}
        </p>
      </div>

      <!-- Real-time Balancing Indicator -->
      <UiCard 
        
        :class="isBalanced ? 'bg-emerald-50 border-emerald-300 text-emerald-950' : 'bg-amber-50 border-amber-300 text-amber-950'"
      >
        <component :is="isBalanced ? CheckCircle2 : AlertTriangle" class="w-5 h-5 flex-shrink-0" :class="isBalanced ? 'text-emerald-600' : 'text-amber-600'" />
        <div class="text-xs">
          <div class="font-bold flex items-center gap-2">
            <span>{{ isBalanced ? (isAr ? 'القيد متوازن محاسبياً' : 'Entry Balanced') : (isAr ? 'القيد غير متوازن' : 'Out of Balance') }}</span>
            <span class="font-mono text-[11px] px-1.5 py-0.2 rounded" :class="isBalanced ? 'bg-emerald-200' : 'bg-amber-200'">
              {{ isAr ? 'الفارق: ' : 'Diff: ' }}${{ difference.toFixed(2) }}
            </span>
          </div>
          <div class="font-mono text-[10px] opacity-80 mt-0.5">
            {{ isAr ? 'مدين: ' : 'Dr: ' }}${{ totalDebitBase.toFixed(2) }} | {{ isAr ? 'دائن: ' : 'Cr: ' }}${{ totalCreditBase.toFixed(2) }}
          </div>
        </div>
      </div>
    </div>

    <!-- Main Entry Form -->
    <form @submit.prevent="submit" class="space-y-6">
      <!-- General Entry Details Card -->
      <UiCard >
        <h2 class="text-sm font-bold text-zinc-900 border-b border-zinc-200 pb-2 flex items-center gap-2">
          <span>{{ isAr ? 'بيانات القيد الأساسية' : 'Header Information' }}</span>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-bold text-zinc-700 mb-1">
              {{ isAr ? 'تاريخ القيد المحاسبي *' : 'Entry Date *' }}
            </label>
            <input
              type="date"
              v-model="form.entry_date"
              required
              class="w-full text-xs font-mono rounded border border-zinc-300 px-3 py-2 focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
            />
            <div v-if="form.errors.entry_date" class="text-[11px] text-red-600 mt-1">{{ form.errors.entry_date }}</div>
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-700 mb-1">
              {{ isAr ? 'المرجع / رقم المستند الورقي' : 'Reference / Paper Doc #' }}
            </label>
            <input
              type="text"
              v-model="form.reference_number"
              :placeholder="isAr ? 'مثال: تسوية بنك مصر / إشعار خصم' : 'e.g., BM-ADJ-2026-01'"
              class="w-full text-xs rounded border border-zinc-300 px-3 py-2 focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
            />
            <div v-if="form.errors.reference_number" class="text-[11px] text-red-600 mt-1">{{ form.errors.reference_number }}</div>
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-700 mb-1">
              {{ isAr ? 'البيان العام للقيد *' : 'General Narration *' }}
            </label>
            <input
              type="text"
              v-model="form.narration"
              required
              :placeholder="isAr ? 'مثال: تسوية إيرادات الشحن البحري وإثبات مصاريف التخليص' : 'e.g., Operational revenue adjustment and freight line accrual'"
              class="w-full text-xs rounded border border-zinc-300 px-3 py-2 focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
            />
            <div v-if="form.errors.narration" class="text-[11px] text-red-600 mt-1">{{ form.errors.narration }}</div>
          </div>
        </div>
      </div>

      <!-- Journal Lines Table Card -->
      <UiCard >
        <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
          <div>
            <h2 class="text-sm font-bold text-zinc-900 flex items-center gap-2">
              <Scale class="w-4 h-4 text-blue-700" />
              <span>{{ isAr ? 'بنود وأطراف القيد المزدوج' : 'Double-Entry Transaction Lines' }}</span>
            </h2>
            <p class="text-xs text-zinc-500">
              {{ isAr ? 'يجب أن يتساوى إجمالي المدين مع إجمالي الدائن بقيمة العملة الأساسية ($)' : 'Total Debit must exactly equal Total Credit in base currency ($)' }}
            </p>
          </div>

          <button
            type="button"
            @click="addLine"
            class="px-3 py-1.5 text-xs font-bold bg-blue-50 text-blue-700 hover:bg-blue-100 rounded border border-blue-200 flex items-center gap-1 transition-colors cursor-pointer"
          >
            <Plus class="w-3.5 h-3.5" />
            <span>{{ isAr ? 'إضافة طرف / سطر قيد' : 'Add Line' }}</span>
          </button>
        </div>

        <div v-if="form.errors.lines" class="text-xs text-red-600 font-bold p-2 bg-red-50 rounded border border-red-200">
          {{ form.errors.lines }}
        </div>

        <!-- Lines Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
            <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
              <tr>
                <th class="p-2 w-10 text-center">#</th>
                <th class="p-2 min-w-[220px]">{{ isAr ? 'الحساب المالي (GL Account) *' : 'GL Account *' }}</th>
                <th class="p-2 min-w-[160px]">{{ isAr ? 'الطرف / المستفيد (اختياري)' : 'Subledger Party' }}</th>
                <th class="p-2 min-w-[180px]">{{ isAr ? 'شرح السطر' : 'Line Description' }}</th>
                <th class="p-2 w-24">{{ isAr ? 'العملة' : 'Currency' }}</th>
                <th class="p-2 w-24">{{ isAr ? 'سعر الصرف' : 'Rate' }}</th>
                <th class="p-2 w-32 text-right">{{ isAr ? 'مدين (Debit)' : 'Debit' }}</th>
                <th class="p-2 w-32 text-right">{{ isAr ? 'دائن (Credit)' : 'Credit' }}</th>
                <th class="p-2 w-10 text-center"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200">
              <tr v-for="(line, idx) in form.lines" :key="idx" class="hover:bg-zinc-50/50">
                <td class="p-2 text-center font-mono text-zinc-400 text-[11px]">{{ idx + 1 }}</td>
                
                <!-- Account Picker -->
                <td class="p-2">
                  <select
                    v-model="line.account_id"
                    required
                    class="w-full text-xs rounded border border-zinc-300 px-2 py-1.5 focus:ring-1 focus:ring-blue-600 focus:border-blue-600 font-medium"
                  >
                    <option value="" disabled>{{ isAr ? '-- اختر الحساب --' : '-- Select Account --' }}</option>
                    <option v-for="acc in accounts" :key="acc.id" :value="acc.id">
                      {{ acc.code }} - {{ isAr ? acc.name_ar : acc.name_en }} ({{ acc.type }})
                    </option>
                  </UiSelect>
                </td>

                <!-- Party Picker -->
                <td class="p-2">
                  <select
                    v-model="line.party_id"
                    class="w-full text-xs rounded border border-zinc-300 px-2 py-1.5 focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
                  >
                    <option :value="null">{{ isAr ? '-- بدون طرف مساعد --' : '-- No Party --' }}</option>
                    <option v-for="p in parties" :key="p.id" :value="p.id">
                      {{ p.legal_name }} ({{ p.party_type }})
                    </option>
                  </UiSelect>
                </td>

                <!-- Line Description -->
                <td class="p-2">
                  <input
                    type="text"
                    v-model="line.description"
                    :placeholder="isAr ? 'شرح السطر...' : 'Narration...'"
                    class="w-full text-xs rounded border border-zinc-300 px-2 py-1.5 focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
                  />
                </td>

                <!-- Currency -->
                <td class="p-2">
                  <select
                    v-model="line.currency_code"
                    class="w-full text-xs font-mono rounded border border-zinc-300 px-2 py-1.5 focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
                  >
                    <option value="USD">USD</option>
                    <option value="EGP">EGP</option>
                    <option value="EUR">EUR</option>
                  </UiSelect>
                </td>

                <!-- Exchange Rate -->
                <td class="p-2">
                  <input
                    type="number"
                    step="0.0001"
                    min="0.0001"
                    v-model.number="line.exchange_rate"
                    class="w-full text-xs font-mono text-center rounded border border-zinc-300 px-2 py-1.5 focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
                  />
                </td>

                <!-- Debit -->
                <td class="p-2">
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    v-model.number="line.debit"
                    @input="line.debit > 0 ? line.credit = 0 : null"
                    class="w-full text-xs font-mono text-right font-bold text-zinc-950 rounded border border-zinc-300 px-2 py-1.5 focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
                    placeholder="0.00"
                  />
                </td>

                <!-- Credit -->
                <td class="p-2">
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    v-model.number="line.credit"
                    @input="line.credit > 0 ? line.debit = 0 : null"
                    class="w-full text-xs font-mono text-right font-bold text-zinc-950 rounded border border-zinc-300 px-2 py-1.5 focus:ring-1 focus:ring-blue-600 focus:border-blue-600"
                    placeholder="0.00"
                  />
                </td>

                <!-- Remove Line -->
                <td class="p-2 text-center">
                  <button
                    type="button"
                    @click="removeLine(idx)"
                    :disabled="form.lines.length <= 2"
                    class="p-1 rounded text-zinc-400 hover:text-red-600 disabled:opacity-20 cursor-pointer disabled:cursor-not-allowed transition-colors"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-zinc-100 font-mono font-bold text-xs border-t-2 border-zinc-300">
              <tr>
                <td colspan="6" class="p-3 text-right">
                  <span class="text-zinc-700">{{ isAr ? 'الإجمالي المعادل بالدولار (Base Currency):' : 'Total Base Currency ($):' }}</span>
                </td>
                <td class="p-3 text-right text-blue-900 font-extrabold text-sm">
                  ${{ totalDebitBase.toFixed(2) }}
                </td>
                <td class="p-3 text-right text-blue-900 font-extrabold text-sm">
                  ${{ totalCreditBase.toFixed(2) }}
                </td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Actions Bar -->
      <div class="flex items-center justify-between pt-2">
        <Link
          :href="route('ledger.journals')"
          class="px-4 py-2 text-xs font-bold text-zinc-700 bg-zinc-100 hover:bg-zinc-200 rounded border border-zinc-300 transition-colors"
        >
          {{ isAr ? 'إلغاء والعودة' : 'Cancel & Return' }}
        </Link>

        <div class="flex items-center gap-3">
          <div v-if="!isBalanced" class="text-xs font-bold text-amber-700 flex items-center gap-1">
            <AlertTriangle class="w-4 h-4" />
            <span>{{ isAr ? 'لا يمكن الحفظ: يجب أن يتساوى المدين والدائن تماماً' : 'Cannot post: Entry must balance' }}</span>
          </div>

          <button
            type="submit"
            :disabled="form.processing || !isBalanced"
            class="px-6 py-2.5 text-xs font-bold text-white bg-blue-700 hover:bg-blue-800 disabled:bg-zinc-400 disabled:cursor-not-allowed rounded shadow-xs flex items-center gap-2 transition-all cursor-pointer"
          >
            <Save class="w-4 h-4" />
            <span>{{ form.processing ? (isAr ? 'جاري الترحيل...' : 'Posting...') : (isAr ? 'حفظ وترحيل القيد المحاسبي' : 'Post Journal Voucher') }}</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</template>
