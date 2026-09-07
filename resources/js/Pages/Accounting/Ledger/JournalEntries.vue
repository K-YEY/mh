<script setup>
import { Link } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { BookOpen, Scale, Plus } from 'lucide-vue-next'

const { isAr, formatDateTime } = useI18n()

const props = defineProps({
  entries: Object,
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <BookOpen class="w-6 h-6 text-blue-700" />
          <span>{{ isAr ? 'دفتر اليومية العامة الموزونة (JV)' : 'General Ledger Double-Entry Journals (JV)' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'سجل قيود اليومية المراجعة مع التحقق التلقائي من توازن المدين والدائن' : 'Audited Financial Journal Voucher Audit Log with Debit/Credit Equilibrium Verification' }}
        </p>
      </div>

      <div class="flex items-center gap-3">
        <Link
          :href="route('ledger.journals.create')"
          class="px-4 py-2 text-xs font-bold text-white bg-blue-700 hover:bg-blue-800 rounded shadow-xs flex items-center gap-1.5 transition-colors cursor-pointer"
        >
          <Plus class="w-4 h-4 font-bold" />
          <span>{{ isAr ? 'إنشاء قيد يدوي / تسوية' : 'New Journal Entry' }}</span>
        </Link>

        <div class="shadcn-card px-3 py-2 flex items-center gap-2 bg-zinc-900 text-white border-zinc-900">
          <Scale class="w-4 h-4 text-emerald-400" />
          <span class="text-xs font-bold text-white">{{ isAr ? 'توازن صارم مفعل' : 'Balanced Double-Entry' }}</span>
        </div>
      </div>
    </div>

    <!-- Journal Entries List -->
    <div class="space-y-4">
      <div v-for="jv in entries.data" :key="jv.id" class="shadcn-card p-5 space-y-3">
        <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
          <div>
            <div class="font-mono font-bold text-zinc-950 text-sm flex items-center gap-2">
              {{ jv.entry_number }}
              <span class="text-xs font-normal text-zinc-500">({{ formatDateTime(jv.created_at || jv.entry_date) }})</span>
            </div>
            <p class="text-xs text-zinc-600 mt-1 font-medium">{{ jv.narration }}</p>
          </div>

          <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-zinc-100 text-zinc-950 border border-zinc-300">
              {{ jv.source_type }}
            </span>
            <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-zinc-950 text-white border border-zinc-950">
              {{ isAr ? 'مُرحل' : 'POSTED' }}
            </span>
          </div>
        </div>

        <!-- Lines Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
            <thead class="text-zinc-500 uppercase text-[9px] font-mono border-b border-zinc-200">
              <tr>
                <th class="py-2">{{ isAr ? 'حساب دفتر اليومية' : 'GL Account' }}</th>
                <th class="py-2">{{ isAr ? 'الطرف (دفتر مساعد)' : 'Sub-ledger Party' }}</th>
                <th class="py-2">{{ isAr ? 'الوصف' : 'Description' }}</th>
                <th class="py-2 text-right">{{ isAr ? 'مدين ($)' : 'Debit ($)' }}</th>
                <th class="py-2 text-right">{{ isAr ? 'دائن ($)' : 'Credit ($)' }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 font-mono">
              <tr v-for="line in jv.lines" :key="line.id" class="hover:bg-zinc-50 transition-colors">
                <td class="py-2 text-zinc-950 font-bold">
                  {{ isAr ? line.account?.name_ar : line.account?.name_en }} ({{ line.account?.code }})
                </td>
                <td class="py-2 text-zinc-700 font-sans">
                  {{ line.party?.legal_name || '-' }}
                </td>
                <td class="py-2 text-zinc-500 font-sans">
                  {{ line.description }}
                </td>
                <td class="py-2 text-right text-zinc-950 font-bold">
                  {{ line.debit > 0 ? '$' + Number(line.debit).toFixed(2) : '-' }}
                </td>
                <td class="py-2 text-right text-zinc-950 font-bold">
                  {{ line.credit > 0 ? '$' + Number(line.credit).toFixed(2) : '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div v-if="!entries.data?.length" class="shadcn-card p-8 text-center text-zinc-500 font-mono">
        {{ isAr ? 'لا توجد قيود يومية مسجلة حالياً' : 'No journal entries recorded yet.' }}
      </div>
    </div>
  </div>
</template>
