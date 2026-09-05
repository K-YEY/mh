<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { DollarSign, Plus, Coins } from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  currencies: Array,
})

const showCreateModal = ref(false)

const form = useForm({
  code: '',
  name: '',
  symbol: '',
  decimal_places: 2,
})

const submit = () => {
  form.post(route('currencies.store'), {
    onSuccess: () => {
      showCreateModal.value = false
      form.reset()
    }
  })
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <Coins class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'عملات النظام والعملة الأساسية' : 'System Multi-Currencies' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'إعدادات العملة الأساسية للنظام وتعدد العملات بدفاتر الحسابات' : 'Base Currency Configuration & Multi-Currency Ledger Precision' }}
        </p>
      </div>

      <button
        @click="showCreateModal = true"
        class="shadcn-btn-primary gap-1.5 shadow-xs cursor-pointer"
      >
        <Plus class="w-4 h-4 font-bold" />
        <span>{{ isAr ? 'إضافة عملة جديدة' : 'Add Currency' }}</span>
      </button>
    </div>

    <!-- Currencies Table Card -->
    <div class="shadcn-card overflow-hidden">
      <div class="p-4 border-b border-zinc-200 flex items-center justify-between bg-zinc-50">
        <div class="flex items-center gap-2">
          <DollarSign class="w-4 h-4 text-zinc-950" />
          <span class="text-xs font-bold text-zinc-950 uppercase tracking-wider">
            {{ isAr ? `عملات النظام المفعّلة (${currencies.length} عملة)` : `Active System Currencies (${currencies.length} Currencies)` }}
          </span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="px-4 py-3">{{ isAr ? 'كود العملة' : 'Currency Code' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'اسم العملة' : 'Currency Name' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الرمز' : 'Symbol' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الكسور العشرية' : 'Decimals' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'حالة العملة الأساسية' : 'Base Currency Status' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الحالة' : 'Status' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200 font-sans">
            <tr v-for="c in currencies" :key="c.code" class="hover:bg-zinc-50 transition-colors">
              <td class="px-4 py-3 font-mono font-bold text-zinc-950 text-sm">
                {{ c.code }}
              </td>
              <td class="px-4 py-3 font-bold text-zinc-950">
                {{ c.name }}
              </td>
              <td class="px-4 py-3 font-mono text-zinc-950 font-bold text-sm">
                {{ c.symbol }}
              </td>
              <td class="px-4 py-3 font-mono text-zinc-600">
                {{ c.decimal_places }} {{ isAr ? 'خانة عشرية' : 'Places' }}
              </td>
              <td class="px-4 py-3">
                <span v-if="c.is_base_currency" class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-zinc-950 text-white border border-zinc-950 flex items-center gap-1 w-max">
                  <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                  <span>{{ isAr ? 'العملة الأساسية للنظام' : 'BASE SYSTEM CURRENCY' }}</span>
                </span>
                <span v-else class="text-zinc-500 font-mono text-[11px]">
                  {{ isAr ? 'عملة أجنبية' : 'Foreign Currency' }}
                </span>
              </td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-zinc-950 text-white border border-zinc-950">
                  {{ isAr ? 'نشط' : 'ACTIVE' }}
                </span>
              </td>
            </tr>

            <tr v-if="!currencies?.length">
              <td colspan="6" class="p-8 text-center text-zinc-500 font-mono">
                {{ isAr ? 'لا توجد عملات مسجلة للنظام حالياً' : 'No currencies found.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
      <div class="shadcn-card w-full max-w-md p-6 space-y-4 bg-white border-zinc-200 shadow-2xl">
        <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
          <h3 class="text-sm font-bold text-zinc-950">{{ isAr ? 'إضافة عملة نظام جديدة' : 'Add System Currency' }}</h3>
          <button @click="showCreateModal = false" class="text-zinc-400 hover:text-zinc-950 font-bold">✕</button>
        </div>

        <form @submit.prevent="submit" class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">Currency Code (3 Chars)</label>
            <input v-model="form.code" type="text" placeholder="e.g. EUR" required class="shadcn-input font-mono uppercase" dir="ltr" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'اسم العملة' : 'Currency Name' }}</label>
            <input v-model="form.name" type="text" placeholder="e.g. Euro (يورو)" required class="shadcn-input" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'رمز العملة' : 'Currency Symbol' }}</label>
            <input v-model="form.symbol" type="text" placeholder="e.g. €" required class="shadcn-input font-mono" dir="ltr" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'عدد الخانات العشرية' : 'Decimal Places' }}</label>
            <input v-model="form.decimal_places" type="number" min="0" max="4" required class="shadcn-input font-mono" dir="ltr" />
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-zinc-200">
            <button type="button" @click="showCreateModal = false" class="shadcn-btn-secondary cursor-pointer">{{ isAr ? 'إلغاء' : 'Cancel' }}</button>
            <button type="submit" :disabled="form.processing" class="shadcn-btn-primary cursor-pointer">{{ isAr ? 'حفظ العملة' : 'Save Currency' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
