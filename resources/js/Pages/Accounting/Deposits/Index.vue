<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { ShieldCheck, Plus } from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  deposits: Object,
  containers: Array,
  parties: Array,
})

const form = useForm({
  master_container_id: '',
  party_id: '',
  deposit_type: 'PAID_TO_LINE',
  amount: 500.00,
  currency_code: 'USD',
  notes: 'Standard Equipment Line Guarantee (تأمين الحاوية المباشر)',
})

const submit = () => {
  form.post(route('deposits.store'), {
    onSuccess: () => form.reset(),
  })
}

const refund = (depositId) => {
  useForm({}).post(route('deposits.refund', depositId))
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <ShieldCheck class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'تأطير وضمانات الحاويات وأمانات الخطوط' : 'Container Equipment Line Guarantees & Deposits' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'متابعة تأمينات حاويات الخطوط الملاحية والضمانات المستردة للعملاء' : 'Track Shipping Line Container Deposits & Customer Guarantee Escrow' }}
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Data Table Card -->
      <div class="lg:col-span-2 shadcn-card overflow-hidden">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="p-4">{{ isAr ? 'رقم الحاوية والملف' : 'Container #' }}</th>
              <th class="p-4">{{ isAr ? 'النوع' : 'Type' }}</th>
              <th class="p-4">{{ isAr ? 'الطرف' : 'Party' }}</th>
              <th class="p-4">{{ isAr ? 'مبلغ التأمين' : 'Deposit Amount' }}</th>
              <th class="p-4">{{ isAr ? 'تاريخ السداد' : 'Paid Date' }}</th>
              <th class="p-4">{{ isAr ? 'الحالة' : 'Status' }}</th>
              <th class="p-4" :class="isAr ? 'text-left' : 'text-right'">{{ isAr ? 'التحكم' : 'Action' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200 font-sans">
            <tr v-for="d in deposits.data" :key="d.id" class="hover:bg-zinc-50 transition-colors">
              <td class="p-4 font-mono font-bold text-zinc-950">
                {{ d.container?.container_number }}
                <div class="text-[10px] text-zinc-500 font-sans font-normal">{{ d.container?.master_shipment?.file_number }}</div>
              </td>
              <td class="p-4">
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold border bg-zinc-100 text-zinc-950 border-zinc-300 uppercase">
                  {{ d.deposit_type === 'PAID_TO_LINE' ? (isAr ? 'تأمين خط ملاحي' : 'Paid to Line') : (isAr ? 'تأمين عميل' : 'Customer Deposit') }}
                </span>
              </td>
              <td class="p-4 font-bold text-zinc-950">
                {{ d.party?.legal_name }}
              </td>
              <td class="p-4 font-mono font-bold text-zinc-950">
                ${{ d.amount }} {{ d.currency_code }}
              </td>
              <td class="p-4 text-zinc-700 font-mono text-[11px]">
                {{ d.paid_date }}
              </td>
              <td class="p-4">
                <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold border uppercase bg-zinc-950 text-white border-zinc-950">
                  {{ d.status }}
                </span>
              </td>
              <td class="p-4" :class="isAr ? 'text-left' : 'text-right'">
                <button
                  v-if="d.status !== 'REFUNDED_FULL'"
                  @click="refund(d.id)"
                  class="shadcn-btn-secondary text-[11px] h-7 px-2.5 cursor-pointer"
                >
                  {{ isAr ? 'تأكيد الاسترداد' : 'Mark Refunded' }}
                </button>
              </td>
            </tr>

            <tr v-if="!deposits.data?.length">
              <td colspan="7" class="p-8 text-center text-zinc-500 font-mono">
                {{ isAr ? 'لا توجد تأمينات أو ضمانات مسجلة حالياً' : 'No container deposits recorded yet.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Add Deposit Form Card -->
      <div class="shadcn-card p-5 space-y-4 h-fit">
        <h3 class="text-sm font-bold text-zinc-950 flex items-center gap-2">
          <Plus class="w-4 h-4 text-zinc-950" />
          <span>{{ isAr ? 'تسجيل تأمين حاوية جديد' : 'Register Container Guarantee' }}</span>
        </h3>

        <form @submit.prevent="submit" class="space-y-3">
          <div>
            <label class="block text-[11px] font-bold text-zinc-800 mb-1">{{ isAr ? 'الحاوية المستهدفة' : 'Target Container' }}</label>
            <select v-model="form.master_container_id" class="shadcn-input" required>
              <option value="" disabled>{{ isAr ? 'اختر وحدة الحاوية...' : 'Select Container Unit...' }}</option>
              <option v-for="c in containers" :key="c.id" :value="c.id">{{ c.container_number }} ({{ c.master_shipment?.file_number }})</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-zinc-800 mb-1">{{ isAr ? 'نوع التأمين' : 'Deposit Type' }}</label>
            <select v-model="form.deposit_type" class="shadcn-input">
              <option value="PAID_TO_LINE">{{ isAr ? 'مدفوع لخط الشحن البحري' : 'Paid to Ocean Carrier Line' }}</option>
              <option value="COLLECTED_FROM_CUSTOMER">{{ isAr ? 'مستلم من المصدر / العميل' : 'Collected from Shipper/Customer' }}</option>
            </select>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-zinc-800 mb-1">{{ isAr ? 'الطرف' : 'Party' }}</label>
            <select v-model="form.party_id" class="shadcn-input" required>
              <option value="" disabled>{{ isAr ? 'اختر الطرف...' : 'Select Party...' }}</option>
              <option v-for="p in parties" :key="p.id" :value="p.id">{{ p.legal_name }} ({{ p.code }})</option>
            </select>
          </div>

          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-[11px] font-bold text-zinc-800 mb-1">{{ isAr ? 'المبلغ' : 'Amount' }}</label>
              <input v-model="form.amount" type="number" step="0.01" class="shadcn-input font-mono font-bold" required dir="ltr" />
            </div>

            <div>
              <label class="block text-[11px] font-bold text-zinc-800 mb-1">{{ isAr ? 'العملة' : 'Currency' }}</label>
              <select v-model="form.currency_code" class="shadcn-input">
                <option value="USD">USD ($)</option>
                <option value="EUR">EUR (€)</option>
                <option value="EGP">EGP (E£)</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-[11px] font-bold text-zinc-800 mb-1">{{ isAr ? 'ملاحظات / حالة المعدات' : 'Notes / Equipment Condition' }}</label>
            <input v-model="form.notes" type="text" class="shadcn-input" />
          </div>

          <button type="submit" class="shadcn-btn-primary w-full mt-2 cursor-pointer">
            {{ isAr ? 'تسجيل تأمين الحاوية' : 'Record Guarantee Deposit' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
