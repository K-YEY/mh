<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { Anchor, Plus, Globe } from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  ports: Array,
})

const showCreateModal = ref(false)

const form = useForm({
  code: '',
  name: '',
  country_code: '',
})

const submit = () => {
  form.post(route('ports.store'), {
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
          <Anchor class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'دليل الموانئ البحرية العالمية UN/LOCODE' : 'UN/LOCODE Ocean Seaports' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'شبكة الموانئ البحرية الدولية وأكواد الدول المعتمدة للشحن' : 'Global Seaport Network Directory & Country UN/LOCODE Identifiers' }}
        </p>
      </div>

      <button
        @click="showCreateModal = true"
        class="shadcn-btn-primary gap-1.5 shadow-xs cursor-pointer"
      >
        <Plus class="w-4 h-4 font-bold" />
        <span>{{ isAr ? 'إضافة ميناء جديد' : 'Add Seaport' }}</span>
      </button>
    </div>

    <!-- Ports Table Card -->
    <div class="shadcn-card overflow-hidden">
      <div class="p-4 border-b border-zinc-200 flex items-center justify-between bg-zinc-50">
        <div class="flex items-center gap-2">
          <Globe class="w-4 h-4 text-zinc-950" />
          <span class="text-xs font-bold text-zinc-950 uppercase tracking-wider">
            {{ isAr ? `دليل الموانئ البحرية (${ports.length} ميناء)` : `Seaports Directory (${ports.length} Ports)` }}
          </span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="px-4 py-3">UN/LOCODE</th>
              <th class="px-4 py-3">{{ isAr ? 'اسم الميناء' : 'Port Name' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'رمز الدولة' : 'Country Code' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الحالة' : 'Status' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200 font-sans">
            <tr v-for="p in ports" :key="p.id" class="hover:bg-zinc-50 transition-colors">
              <td class="px-4 py-3 font-mono font-bold text-zinc-950">
                {{ p.code }}
              </td>
              <td class="px-4 py-3 font-bold text-zinc-950">
                {{ p.name }}
              </td>
              <td class="px-4 py-3 font-mono text-zinc-800 font-bold">
                {{ p.country_code }}
              </td>
              <td class="px-4 py-3">
                <span class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-zinc-950 text-white border border-zinc-950">
                  {{ isAr ? 'نشط' : 'ACTIVE' }}
                </span>
              </td>
            </tr>

            <tr v-if="!ports?.length">
              <td colspan="4" class="p-8 text-center text-zinc-500 font-mono">
                {{ isAr ? 'لا توجد موانئ مسجلة حالياً' : 'No seaports found.' }}
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
          <h3 class="text-sm font-bold text-zinc-950">{{ isAr ? 'تسجيل ميناء بحري جديد' : 'Add UN/LOCODE Ocean Seaport' }}</h3>
          <button @click="showCreateModal = false" class="text-zinc-400 hover:text-zinc-950 font-bold">✕</button>
        </div>

        <form @submit.prevent="submit" class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">UN/LOCODE (كود 5 أحرف)</label>
            <input v-model="form.code" type="text" placeholder="e.g. EGALY" required class="shadcn-input font-mono uppercase" dir="ltr" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'اسم الميناء البحرى' : 'Port Name' }}</label>
            <input v-model="form.name" type="text" placeholder="e.g. Alexandria Port (ميناء الإسكندرية)" required class="shadcn-input" />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'كود الدولة (ISO-2)' : 'Country Code (ISO-2)' }}</label>
            <input v-model="form.country_code" type="text" placeholder="e.g. EG" required class="shadcn-input font-mono uppercase" dir="ltr" />
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-zinc-200">
            <button type="button" @click="showCreateModal = false" class="shadcn-btn-secondary cursor-pointer">{{ isAr ? 'إلغاء' : 'Cancel' }}</button>
            <button type="submit" :disabled="form.processing" class="shadcn-btn-primary cursor-pointer">{{ isAr ? 'حفظ الميناء' : 'Save Seaport' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
