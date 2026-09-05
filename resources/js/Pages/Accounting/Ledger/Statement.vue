<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { User } from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  parties: Array,
  selected_party: Object,
  lines: Array,
  totals: Object,
})

const selectedPartyId = ref(props.selected_party?.id || '')

const loadStatement = () => {
  router.get(route('ledger.statement'), { party_id: selectedPartyId.value }, { preserveState: true })
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <User class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'كشف حساب الأطراف والدفتر المساعد' : 'Party Sub-Ledger Statement of Account' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'كشف حساب مالي تفصيلي للعملاء، الخطوط الملاحية، محطات التعبئة، وشركات النقل' : 'Detailed Financial Sub-Ledger Statement for Customers, Carriers, Packhouses, & Truckers' }}
        </p>
      </div>
    </div>

    <!-- Filter Bar Card -->
    <UiCard >
      <div class="flex-1">
        <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'اختر الطرف / العميل / الخط الملاحي' : 'Select Party / Customer / Line' }}</label>
        <UiSelect v-model="selectedPartyId" @change="loadStatement" >
          <option value="" disabled>{{ isAr ? 'اختر الطرف لاستخراج كشف الحساب...' : 'Choose Party for Ledger Extract...' }}</option>
          <option v-for="p in parties" :key="p.id" :value="p.id">{{ p.legal_name }} ({{ p.code }})</option>
        </UiSelect>
      </div>
    </div>

    <!-- Statement Results -->
    <div v-if="selected_party" class="space-y-6">
      <!-- Summary Box Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <UiCard >
          <div class="text-xs text-zinc-500 uppercase font-bold">{{ isAr ? 'إجمالي المدين' : 'Total Debits' }}</div>
          <div class="text-xl font-bold text-zinc-950 mt-1 font-mono">${{ totals.debit.toLocaleString() }}</div>
        </div>
        <UiCard >
          <div class="text-xs text-zinc-500 uppercase font-bold">{{ isAr ? 'إجمالي الدائن' : 'Total Credits' }}</div>
          <div class="text-xl font-bold text-zinc-950 mt-1 font-mono">${{ totals.credit.toLocaleString() }}</div>
        </div>
        <UiCard >
          <div class="text-xs text-zinc-300 uppercase font-bold">{{ isAr ? 'الرصيد المتبقي المستحق' : 'Outstanding Balance' }}</div>
          <div class="text-xl font-bold text-white mt-1 font-mono">${{ totals.balance.toLocaleString() }}</div>
        </div>
      </div>

      <!-- Ledger Lines Grid Card -->
      <UiCard >
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="p-4">{{ isAr ? 'رقم القيد والتاريخ' : 'JV # & Date' }}</th>
              <th class="p-4">{{ isAr ? 'رمز واسم الحساب' : 'Account Code & Name' }}</th>
              <th class="p-4">{{ isAr ? 'الوصف' : 'Description' }}</th>
              <th class="p-4 text-right">{{ isAr ? 'مدين ($)' : 'Debit ($)' }}</th>
              <th class="p-4 text-right">{{ isAr ? 'دائن ($)' : 'Credit ($)' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200 font-mono">
            <tr v-for="l in lines" :key="l.id" class="hover:bg-zinc-50 transition-colors">
              <td class="p-4 text-zinc-950 font-bold">
                {{ l.journal_entry?.entry_number }}
                <div class="text-[10px] text-zinc-500 font-sans font-normal">{{ l.journal_entry?.entry_date }}</div>
              </td>
              <td class="p-4 text-zinc-900 font-sans font-bold">
                {{ isAr ? l.account?.name_ar : l.account?.name_en }} ({{ l.account?.code }})
              </td>
              <td class="p-4 text-zinc-700 font-sans">
                {{ l.description }}
              </td>
              <td class="p-4 text-right text-zinc-950 font-bold">
                {{ l.debit > 0 ? '$' + Number(l.debit).toFixed(2) : '-' }}
              </td>
              <td class="p-4 text-right text-zinc-950 font-bold">
                {{ l.credit > 0 ? '$' + Number(l.credit).toFixed(2) : '-' }}
              </td>
            </tr>

            <tr v-if="!lines?.length">
              <td colspan="5" class="p-8 text-center text-zinc-500 font-mono">
                {{ isAr ? 'لا توجد قيود مسجلة على هذا الطرف' : 'No statement lines found for this party.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
