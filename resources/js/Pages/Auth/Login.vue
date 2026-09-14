<script setup>
import { ref } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import { useI18n } from '@/Composables/useI18n'
import { Mail, Lock, Eye, EyeOff, Globe, Sparkles, CheckCircle2 } from 'lucide-vue-next'

defineOptions({ layout: null })

const { locale, isAr, dir, toggleLocale } = useI18n()

const showPassword = ref(false)
const isDemoFilled = ref(false)

const form = useForm({
  email: 'admin@agrierp.com',
  password: 'password',
  remember: true,
})

const fillDemo = () => {
  form.email = 'admin@agrierp.com'
  form.password = 'password'
  isDemoFilled.value = true
  setTimeout(() => {
    isDemoFilled.value = false
  }, 2000)
}

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head :title="isAr ? 'تسجيل الدخول - MH For Export ERP' : 'Sign In - MH For Export ERP Portal'" />

  <div
    :dir="dir"
    class="min-h-screen w-full relative flex flex-col justify-between font-sans antialiased select-none overflow-x-hidden bg-[#d7e8f7]"
    style="background: url('/sky-bg.jpg') no-repeat center center / cover; background-attachment: fixed;"
  >
    <!-- Background Concentric Arcs / Orbit Rings (matching reference image) -->
    <div class="pointer-events-none absolute inset-0 flex items-center justify-center overflow-hidden z-0">
      <svg class="w-[1200px] h-[1200px] opacity-60 shrink-0" viewBox="0 0 1000 1000" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="500" cy="500" r="230" stroke="rgba(255, 255, 255, 0.45)" stroke-width="1.2" />
        <circle cx="500" cy="500" r="330" stroke="rgba(255, 255, 255, 0.35)" stroke-width="1.2" />
        <circle cx="500" cy="500" r="440" stroke="rgba(255, 255, 255, 0.25)" stroke-width="1.2" />
        <circle cx="500" cy="500" r="560" stroke="rgba(255, 255, 255, 0.18)" stroke-width="1.2" />
      </svg>
    </div>

    <!-- Top Navigation Bar: Brand Logo & Language Switcher -->
    <header class="relative z-20 w-full px-6 py-6 sm:px-10 flex items-center justify-between">
      <!-- Brand Logo (Matching top-left in reference) -->
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-slate-900 shadow-md flex items-center justify-center text-white">
          <!-- Clover / geometric icon like reference logo -->
          <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2a4 4 0 0 0-4 4 4 4 0 0 0-4 4 4 4 0 0 0 4 4 4 4 0 0 0 4-4V6a4 4 0 0 0-4-4zm0 20a4 4 0 0 0 4-4 4 4 0 0 0 4-4 4 4 0 0 0-4-4 4 4 0 0 0-4 4v4a4 4 0 0 0 4 4zm8-10a4 4 0 0 0-4-4 4 4 0 0 0-4 4 4 4 0 0 0 4 4h4a4 4 0 0 0 0-4zm-16 0a4 4 0 0 0 4 4 4 4 0 0 0 4-4 4 4 0 0 0-4-4H4a4 4 0 0 0 0 4z"/>
          </svg>
        </div>
        <span class="text-base font-bold tracking-tight text-slate-900">MH For Export</span>
      </div>

      <!-- Controls: Language Toggle & Quick Demo Access -->
      <div class="flex items-center gap-2.5">
        <button
          type="button"
          @click="fillDemo"
          class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-white/70 hover:bg-white text-slate-700 backdrop-blur-md border border-white/80 shadow-xs transition-all cursor-pointer active:scale-95"
          :title="isAr ? 'تعبئة حساب تجريبي' : 'Auto Fill Demo Credentials'"
        >
          <component :is="isDemoFilled ? CheckCircle2 : Sparkles" class="w-3.5 h-3.5 text-blue-600" />
          <span>{{ isDemoFilled ? (isAr ? 'تم التعبئة' : 'Filled') : (isAr ? 'حساب التجربة' : 'Demo Login') }}</span>
        </button>

        <button
          type="button"
          @click="toggleLocale"
          class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white/70 hover:bg-white text-slate-800 backdrop-blur-md border border-white/80 shadow-xs hover:shadow-sm transition-all cursor-pointer active:scale-95"
        >
          <Globe class="w-3.5 h-3.5 text-blue-600" />
          <span>{{ isAr ? 'English' : 'عربي' }}</span>
        </button>
      </div>
    </header>

    <!-- Main Center Content: Floating Glassmorphism Login Card -->
    <main class="relative z-10 flex-1 flex items-center justify-center px-4 py-8">
      <div class="w-full max-w-[410px] animate-scale-in">
        
        <!-- The Card (Exactly matching the screenshot) -->
        <div class="relative rounded-[2.25rem] bg-gradient-to-b from-white/95 via-white/90 to-white/95 backdrop-blur-2xl p-7 sm:p-9 border border-white/90 shadow-[0_20px_50px_rgba(0,0,0,0.06),0_1px_3px_rgba(0,0,0,0.03)]">
          
          <!-- Top Center Badge: [→] Arrow In Login Icon -->
          <div class="w-13 h-13 rounded-2xl bg-white shadow-[0_4px_16px_rgba(0,0,0,0.06)] border border-slate-100/90 flex items-center justify-center mx-auto mb-4.5">
            <svg class="w-5.5 h-5.5 text-slate-800" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
              <polyline points="10 17 15 12 10 7" />
              <line x1="15" y1="12" x2="3" y2="12" />
            </svg>
          </div>

          <!-- Titles -->
          <div class="text-center space-y-1.5 mb-5">
            <h1 class="text-[22px] font-bold text-slate-900 tracking-tight leading-tight">
              {{ isAr ? 'تسجيل الدخول بالبريد' : 'Sign in with email' }}
            </h1>
            <p class="text-xs text-slate-400 font-normal leading-relaxed max-w-[280px] mx-auto">
              {{ isAr ? 'سجّل الدخول لمتابعة الشحنات، الحسابات والبيانات في منصة واحدة.' : 'Make a new doc to bring your words, data, and teams together. For free' }}
            </p>
          </div>

          <!-- Error Alert (if validation error occurs) -->
          <div v-if="form.errors.email || form.errors.password" class="mb-4 p-2.5 rounded-xl bg-rose-50 border border-rose-200/80 text-rose-600 text-xs text-center font-medium">
            {{ form.errors.email || form.errors.password }}
          </div>

          <!-- Login Form -->
          <form @submit.prevent="submit" class="space-y-3.5">
            <!-- Email Input -->
            <div class="relative flex items-center rounded-2xl bg-[#f2f4f7] hover:bg-[#eceef2] focus-within:bg-white focus-within:ring-2 focus-within:ring-slate-900/10 focus-within:border-slate-300/80 border border-transparent transition-all">
              <Mail class="w-4 h-4 text-slate-400 pointer-events-none shrink-0" :class="isAr ? 'mr-3.5' : 'ml-3.5'" />
              <input
                v-model="form.email"
                type="email"
                required
                :placeholder="isAr ? 'البريد الإلكتروني' : 'Email'"
                class="w-full bg-transparent border-none outline-none py-3 px-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 font-medium"
                dir="ltr"
              />
            </div>

            <!-- Password Input -->
            <div class="relative flex items-center rounded-2xl bg-[#f2f4f7] hover:bg-[#eceef2] focus-within:bg-white focus-within:ring-2 focus-within:ring-slate-900/10 focus-within:border-slate-300/80 border border-transparent transition-all">
              <Lock class="w-4 h-4 text-slate-400 pointer-events-none shrink-0" :class="isAr ? 'mr-3.5' : 'ml-3.5'" />
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                :placeholder="isAr ? 'كلمة المرور' : 'Password'"
                class="w-full bg-transparent border-none outline-none py-3 px-2.5 text-xs sm:text-sm text-slate-800 placeholder:text-slate-400 font-medium"
                dir="ltr"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="p-2 text-slate-400 hover:text-slate-600 transition-colors cursor-pointer shrink-0"
                :class="isAr ? 'ml-1' : 'mr-1'"
                tabindex="-1"
              >
                <component :is="showPassword ? EyeOff : Eye" class="w-4 h-4" />
              </button>
            </div>

            <!-- Forgot Password Link -->
            <div class="flex justify-end pt-0.5">
              <a href="#" class="text-xs text-slate-500 hover:text-slate-800 font-medium transition-colors">
                {{ isAr ? 'نسيت كلمة المرور؟' : 'Forgot password?' }}
              </a>
            </div>

            <!-- Submit Button (Black pill matching reference) -->
            <button
              type="submit"
              :disabled="form.processing"
              class="w-full h-11 sm:h-12 rounded-2xl bg-gradient-to-b from-[#25262c] to-[#121316] hover:from-[#32343b] hover:to-[#1a1b1f] text-white text-xs sm:text-sm font-semibold tracking-wide shadow-md hover:shadow-lg active:scale-[0.99] transition-all cursor-pointer flex items-center justify-center disabled:opacity-75 disabled:cursor-not-allowed mt-2"
            >
              <span v-if="!form.processing">{{ isAr ? 'تسجيل الدخول' : 'Get Started' }}</span>
              <div v-else class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
            </button>
          </form>

          <!-- Divider: "Or sign in with" -->
          <div class="relative my-5 flex items-center justify-center">
            <div class="w-full border-t border-dotted border-slate-300"></div>
            <span class="absolute bg-white/95 px-2.5 text-[11px] text-slate-400 font-medium">
              {{ isAr ? 'أو الدخول عبر' : 'Or sign in with' }}
            </span>
          </div>

          <!-- Social Buttons (Google, Facebook, Apple) -->
          <div class="grid grid-cols-3 gap-2.5">
            <!-- Google -->
            <button
              type="button"
              @click="fillDemo"
              class="h-10 rounded-xl bg-white hover:bg-slate-50/90 border border-slate-200/80 shadow-[0_1px_2px_rgba(0,0,0,0.04)] hover:shadow-xs transition-all flex items-center justify-center cursor-pointer active:scale-95"
              title="Google"
            >
              <svg class="w-4 h-4" viewBox="0 0 24 24">
                <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/>
                <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.34 24 12 24z"/>
                <path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.14-1.55.38-2.27V6.58H1.25C.45 8.16 0 9.94 0 12c0 2.06.45 3.84 1.25 5.42l4.03-3.15z"/>
                <path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.34 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/>
              </svg>
            </button>

            <!-- Facebook -->
            <button
              type="button"
              @click="fillDemo"
              class="h-10 rounded-xl bg-white hover:bg-slate-50/90 border border-slate-200/80 shadow-[0_1px_2px_rgba(0,0,0,0.04)] hover:shadow-xs transition-all flex items-center justify-center cursor-pointer active:scale-95"
              title="Facebook"
            >
              <svg class="w-4 h-4 text-[#1877F2]" fill="currentColor" viewBox="0 0 24 24">
                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
              </svg>
            </button>

            <!-- Apple -->
            <button
              type="button"
              @click="fillDemo"
              class="h-10 rounded-xl bg-white hover:bg-slate-50/90 border border-slate-200/80 shadow-[0_1px_2px_rgba(0,0,0,0.04)] hover:shadow-xs transition-all flex items-center justify-center cursor-pointer active:scale-95"
              title="Apple"
            >
              <svg class="w-4 h-4 text-slate-900" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.37c.62-.75 1.04-1.8 1.01-2.85-.9.04-2 .6-2.65 1.36-.58.66-1.08 1.73-1.04 2.76 1.01.08 2.06-.52 2.68-1.27z"/>
              </svg>
            </button>
          </div>

        </div>

        <!-- Footer Notice -->
        <div class="mt-4 text-center">
          <p class="text-[11px] text-slate-500/80 font-medium">
            {{ isAr ? 'نظام MH For Export لإدارة الشحن والعمليات التصديرية' : 'MH For Export ERP Portal • Secure Enterprise Access' }}
          </p>
        </div>

      </div>
    </main>

    <!-- Bottom Subtle Spacing -->
    <div class="h-4 relative z-10"></div>
  </div>
</template>

<style scoped>
@keyframes scaleIn {
  from {
    opacity: 0;
    transform: scale(0.96) translateY(8px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-scale-in {
  animation: scaleIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
