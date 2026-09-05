<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { BookOpen, Plus, FolderTree } from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  accounts: Array,
})

const showCreateModal = ref(false)

const form = useForm({
  code: '',
  name_ar: '',
  name_en: '',
  type: 'ASSET',
  parent_id: '',
  is_active: true,
})

const submit = () => {
  form.post(route('accounts.store'), {
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
          <BookOpen class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'دليل شجرة الحسابات المالية (COA)' : 'Chart of Accounts (COA Tree)' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'شجرة الحسابات المالية الرئيسية وتصنيفات دفاتر اليومية' : 'Master Financial Account Tree & Ledger Categorization' }}
        </p>
      </div>

      <UiButton
        @click="showCreateModal = true"
         variant="primary"
      >
        <Plus class="w-4 h-4 font-bold" />
        <span>{{ isAr ? 'إضافة حساب جديد' : 'New Account' }}</span>
      </UiButton>
    </div>

    <!-- Accounts Table Card -->
    <UiCard >
      <div class="p-4 border-b border-zinc-200 flex items-center justify-between bg-zinc-50">
        <div class="flex items-center gap-2">
          <FolderTree class="w-4 h-4 text-zinc-950" />
          <span class="text-xs font-bold text-zinc-950 uppercase tracking-wider">
            {{ isAr ? `دليل الحسابات (${accounts.length} حساب)` : `Account Directory (${accounts.length} Accounts)` }}
          </span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
          <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
            <tr>
              <th class="px-4 py-3">{{ isAr ? 'رمز الحساب' : 'Account Code' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'اسم الحساب (عربي / انجليزي)' : 'Account Name (EN / AR)' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'النوع' : 'Type' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الحساب الأب' : 'Parent Account' }}</th>
              <th class="px-4 py-3">{{ isAr ? 'الحالة' : 'Status' }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-zinc-200 font-sans">
            <tr v-for="acc in accounts" :key="acc.id" class="hover:bg-zinc-50 transition-colors">
              <td class="px-4 py-3 font-mono font-bold text-zinc-950">
                {{ acc.code }}
              </td>
              <td class="px-4 py-3">
                <div class="font-bold text-zinc-950">{{ isAr ? acc.name_ar : acc.name_en }}</div>
                <div class="text-[11px] text-zinc-500 font-sans">{{ isAr ? acc.name_en : acc.name_ar }}</div>
              </td>
              <td class="px-4 py-3">
                <span 
                  class="px-2 py-0.5 rounded text-[9px] font-mono font-bold uppercase border bg-zinc-100 text-zinc-950 border-zinc-300"
                >
                  {{ acc.type }}
                </span>
              </td>
              <td class="px-4 py-3 font-mono text-zinc-600">
                {{ acc.parent ? `${acc.parent.code} - ${isAr ? acc.parent.name_ar : acc.parent.name_en}` : (isAr ? 'حساب رئيسي' : 'Top Level Account') }}
              </td>
              <td class="px-4 py-3">
                <span v-if="acc.is_active" class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-zinc-950 text-white border border-zinc-950">
                  {{ isAr ? 'نشط' : 'ACTIVE' }}
                </span>
                <span v-else class="px-2 py-0.5 rounded text-[9px] font-mono font-bold bg-zinc-100 text-zinc-500 border border-zinc-300">
                  {{ isAr ? 'غير نشط' : 'INACTIVE' }}
                </span>
              </td>
            </tr>

            <tr v-if="!accounts?.length">
              <td colspan="5" class="p-8 text-center text-zinc-500 font-mono">
                {{ isAr ? 'لا توجد حسابات مسجلة في الشجرة' : 'No financial accounts found in chart.' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Account Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 bg-black/60 flex items-center justify-center p-4">
      <UiCard >
        <div class="flex items-center justify-between border-b border-zinc-200 pb-3">
          <h3 class="text-sm font-bold text-zinc-950">{{ isAr ? 'إضافة حساب مالى جديد' : 'Create Financial Account' }}</h3>
          <button @click="showCreateModal = false" class="text-zinc-400 hover:text-zinc-950 font-bold">✕</button>
        </div>

        <form @submit.prevent="submit" class="space-y-3">
          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'رمز الحساب' : 'Account Code' }}</label>
            <UiInput v-model="form.code" type="text" placeholder="e.g. 11050" required  dir="ltr" / />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الاسم بالإنجليزية' : 'Name (English)' }}</label>
            <UiInput v-model="form.name_en" type="text" placeholder="e.g. Reefer Freight Revenue" required  dir="ltr" / />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الاسم بالعربية' : 'Name (Arabic)' }}</label>
            <UiInput v-model="form.name_ar" type="text" placeholder="مثال: إيرادات النوالين البحرية" required  / />
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'نوع الحساب' : 'Account Type' }}</label>
            <UiSelect v-model="form.type" >
              <option value="ASSET">{{ isAr ? 'أصول (ASSET)' : 'ASSET' }}</option>
              <option value="LIABILITY">{{ isAr ? 'التزامات (LIABILITY)' : 'LIABILITY' }}</option>
              <option value="EQUITY">{{ isAr ? 'حقوق ملكية (EQUITY)' : 'EQUITY' }}</option>
              <option value="REVENUE">{{ isAr ? 'إيرادات (REVENUE)' : 'REVENUE' }}</option>
              <option value="EXPENSE">{{ isAr ? 'مصروفات (EXPENSE)' : 'EXPENSE' }}</option>
            </UiSelect>
          </div>

          <div>
            <label class="block text-xs font-bold text-zinc-800 mb-1">{{ isAr ? 'الحساب الأب (اختياري)' : 'Parent Account (Optional)' }}</label>
            <UiSelect v-model="form.parent_id" >
              <option value="">{{ isAr ? 'بدون (حساب رئيسي)' : 'None (Top Level)' }}</option>
              <option v-for="a in accounts" :key="a.id" :value="a.id">{{ a.code }} - {{ isAr ? a.name_ar : a.name_en }}</option>
            </UiSelect>
          </div>

          <div class="flex justify-end gap-2 pt-2 border-t border-zinc-200">
            <UiButton type="button" @click="showCreateModal = false"  variant="secondary">{{ isAr ? 'إلغاء' : 'Cancel' }}</UiButton>
            <UiButton type="submit" :disabled="form.processing"  variant="primary">{{ isAr ? 'حفظ الحساب' : 'Save Account' }}</UiButton>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
