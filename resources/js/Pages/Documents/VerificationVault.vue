<script setup>
import { useForm } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { FileCheck2, ShieldCheck, CheckCircle, XCircle, FileText } from 'lucide-vue-next'

const { isAr } = useI18n()

const props = defineProps({
  documents: Object,
  document_types: Array,
})

const verifyDoc = (docId) => {
  useForm({}).post(route('documents.verify', docId))
}

const rejectDoc = (docId) => {
  const reason = prompt(isAr.value ? 'يرجى تحديد سبب الرفض المباشر للتدقيق الزراعي:' : 'Please specify rejection reason for agricultural regulatory audit:')
  if (reason) {
    useForm({ rejection_notes: reason }).post(route('documents.reject', docId))
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-extrabold text-zinc-950 tracking-tight flex items-center gap-2">
          <FileCheck2 class="w-6 h-6 text-zinc-950" />
          <span>{{ isAr ? 'خزنة وثائق وشهادات الامتثال الزراعي' : 'Agricultural Document Compliance Vault' }}</span>
        </h1>
        <p class="text-xs text-zinc-500">
          {{ isAr ? 'أرشيف وفحص شهادات الحجر الزراعي، متبقيات المبيدات، وشهادات المنشأ' : 'Archival & Verification for Phytosanitary, Pesticide Residue, & Certificates of Origin' }}
        </p>
      </div>

      <UiCard >
        <ShieldCheck class="w-4 h-4 text-white" />
        <span class="text-xs font-bold text-white">{{ isAr ? 'خزنة الامتثال الزراعي USDA مفعّلة' : 'USDA & GOEIC Compliance Vault Active' }}</span>
      </div>
    </div>

    <!-- Data Table Card -->
    <UiCard >
      <table class="w-full text-xs" :class="isAr ? 'text-right' : 'text-left'">
        <thead class="bg-zinc-100 text-zinc-700 uppercase text-[10px] tracking-wider border-b border-zinc-200 font-mono">
          <tr>
            <th class="p-4">{{ isAr ? 'نوع الوثيقة' : 'Document Type' }}</th>
            <th class="p-4">{{ isAr ? 'اسم الملف والرقم المرجعي' : 'File Name & Ref' }}</th>
            <th class="p-4">{{ isAr ? 'جهة الإصدار' : 'Issuing Authority' }}</th>
            <th class="p-4">{{ isAr ? 'تاريخ الإصدار والانتهاء' : 'Issue & Expiry Date' }}</th>
            <th class="p-4">{{ isAr ? 'حالة التوثيق' : 'Verification Status' }}</th>
            <th class="p-4" :class="isAr ? 'text-left' : 'text-right'">{{ isAr ? 'التحكم بالتوثيق' : 'Verification Action' }}</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-zinc-200 font-sans">
          <tr v-for="doc in documents.data" :key="doc.id" class="hover:bg-zinc-50 transition-colors">
            <td class="p-4 font-bold text-zinc-950 flex items-center gap-2">
              <FileText class="w-4 h-4 text-zinc-950 shrink-0" />
              <div>
                <div class="text-xs font-bold text-zinc-950">{{ isAr ? doc.document_type?.name_ar : doc.document_type?.name_en }}</div>
                <div class="text-[10px] text-zinc-500 font-normal">{{ isAr ? doc.document_type?.name_en : doc.document_type?.name_ar }}</div>
              </div>
            </td>
            <td class="p-4">
              <div class="font-bold text-zinc-950">{{ doc.file_name }}</div>
              <div class="text-[10px] font-mono text-zinc-500">Ref: {{ doc.reference_number || 'N/A' }}</div>
            </td>
            <td class="p-4 text-zinc-700">
              {{ doc.issuing_authority || (isAr ? 'وزارة الزراعة المصرية' : 'Ministry of Agriculture') }}
            </td>
            <td class="p-4 text-zinc-700 font-mono text-[11px]">
              <div>{{ isAr ? 'الإصدار:' : 'Issue:' }} {{ doc.issue_date || 'N/A' }}</div>
              <div class="text-zinc-500">{{ isAr ? 'الانتهاء:' : 'Expires:' }} {{ doc.expiry_date || (isAr ? 'بدون تاريخ انتهاء' : 'No Expiry') }}</div>
            </td>
            <td class="p-4">
              <span class="px-2.5 py-1 rounded text-[10px] font-mono font-bold uppercase border bg-zinc-950 text-white border-zinc-950">
                {{ doc.status }}
              </span>
            </td>
            <td class="p-4" :class="isAr ? 'text-left space-x-reverse space-x-2' : 'text-right space-x-2'">
              <UiButton
                v-if="doc.status !== 'VERIFIED'"
                @click="verifyDoc(doc.id)"
                 variant="primary"
              >
                <CheckCircle class="w-3.5 h-3.5" />
                <span>{{ isAr ? 'اعتماد وتوثيق' : 'Verify & Approve' }}</span>
              </UiButton>

              <UiButton
                v-if="doc.status !== 'REJECTED'"
                @click="rejectDoc(doc.id)"
                 variant="secondary"
              >
                <XCircle class="w-3.5 h-3.5" />
                <span>{{ isAr ? 'رفض المستند' : 'Reject Document' }}</span>
              </UiButton>
            </td>
          </tr>

          <tr v-if="!documents.data?.length">
            <td colspan="6" class="p-8 text-center text-zinc-500 font-mono">
              {{ isAr ? 'لا توجد مستندات زراعية مسجلة بالخزنة حالياً' : 'No agricultural compliance documents found in vault.' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
