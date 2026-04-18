<template>
  <div class="space-y-8">
    <div class="flex justify-between items-end">
      <div>
        <h2 class="text-3xl font-black text-slate-800">Subscription & Billing</h2>
        <p class="text-slate-500 font-medium tracking-tight">Manage your clinic's plan and payment status</p>
      </div>
    </div>

    <!-- Status Banner -->
    <div v-if="clinic" class="p-8 rounded-[2rem] shadow-xl overflow-hidden relative" :class="statusBannerClass">
      <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="space-y-2">
          <div class="flex items-center gap-2">
            <span class="px-3 py-1 bg-white/20 rounded-full text-[10px] font-black uppercase tracking-widest text-white backdrop-blur-sm border border-white/20">
              {{ clinic.plan?.name || 'No Plan Selected' }}
            </span>
            <span v-if="isTrial" class="px-3 py-1 bg-orange-500 rounded-full text-[10px] font-black uppercase tracking-widest text-white animate-pulse">
              Free Trial
            </span>
          </div>
          <h3 class="text-4xl font-black text-white leading-none">
            {{ statusMessage }}
          </h3>
          <p class="text-white/80 font-medium">
            {{ statusSubMessage }}
          </p>
        </div>
        
        <div v-if="clinic.payment_status === 'pending'" class="p-6 bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 text-white min-w-[240px]">
          <p class="text-[10px] font-black uppercase tracking-widest opacity-60 mb-2">Verification Status</p>
          <div class="flex items-center gap-3">
            <div class="h-10 w-10 flex items-center justify-center bg-orange-500 rounded-full">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <span class="font-bold">Awaiting Admin Review</span>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Plan Selection -->
      <div class="lg:col-span-2 space-y-6">
        <h3 class="text-xl font-bold text-slate-800">Select a Plan</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div v-for="plan in plans" :key="plan.id" :class="[
            'p-6 rounded-3xl border-2 transition-all cursor-pointer relative overflow-hidden group',
            form.plan_id === plan.id ? 'border-teal-500 bg-teal-50/30' : 'border-slate-100 bg-white hover:border-slate-200 shadow-sm'
          ]" @click="form.plan_id = plan.id; form.amount = plan.price">
            <div v-if="form.plan_id === plan.id" class="absolute top-0 right-0 p-2 bg-teal-500 text-white rounded-bl-xl shadow-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </div>
            <h4 class="font-black text-slate-800 uppercase tracking-wide text-xs mb-1">{{ plan.name }}</h4>
            <div class="text-2xl font-black text-slate-900 mb-4">{{ plan.price }} <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">DZD</span></div>
            
            <ul class="space-y-2 text-[11px] text-slate-500 font-medium">
              <li class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-teal-500" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                {{ plan.max_doctors }} Doctors
              </li>
              <li class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-teal-500" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                {{ plan.max_staff }} Support Staff
              </li>
              <li class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-teal-500" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                {{ plan.max_screens }} TV Screen
              </li>
            </ul>
          </div>
        </div>

        <!-- Payment Instructions -->
        <div class="bg-slate-50 rounded-[2.5rem] p-10 space-y-8 border border-slate-100">
          <div class="flex items-start gap-6">
            <div class="h-14 w-14 bg-white rounded-2xl flex items-center justify-center text-teal-600 shadow-sm flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h4 class="text-xl font-bold text-slate-800 mb-2">Manual Payment Instructions</h4>
              <p class="text-slate-500 text-sm leading-relaxed">Please send the plan amount via CCP or Edahabia using the details below. Once sent, upload a screenshot or photo of the receipt.</p>
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-3">
              <div class="flex items-center gap-2">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/4e/Algerie_Poste.svg" class="h-5 grayscale opacity-60" />
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">CCP Details</span>
              </div>
              <p class="text-lg font-black text-slate-800 tracking-tight">0021456382 • Key 14</p>
              <p class="text-xs text-slate-500 font-bold uppercase">Name: HEALTHQ REVENUE ACCT</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-3">
              <div class="flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H4a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <span class="text-xs font-black text-slate-400 uppercase tracking-widest">BaridiMob (RIP)</span>
              </div>
              <p class="text-lg font-black text-slate-800 tracking-tight">00799999002145638214</p>
              <p class="text-xs text-slate-500 font-bold uppercase">Min Transfer: 500 DZD</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Submission -->
      <div class="space-y-6">
        <h3 class="text-xl font-bold text-slate-800">Submit Payment</h3>
        <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-xl space-y-6">
          <div class="space-y-2">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Payment Method</label>
            <div class="grid grid-cols-2 gap-3">
              <button @click="form.method = 'edahabia'" :class="[
                'py-3 rounded-2xl font-bold text-sm border-2 transition-all',
                form.method === 'edahabia' ? 'border-teal-600 bg-teal-50 text-teal-700' : 'border-slate-50 bg-slate-50 text-slate-500'
              ]">Edahabia</button>
              <button @click="form.method = 'ccp'" :class="[
                'py-3 rounded-2xl font-bold text-sm border-2 transition-all',
                form.method === 'ccp' ? 'border-teal-600 bg-teal-50 text-teal-700' : 'border-slate-50 bg-slate-50 text-slate-500'
              ]">CCP Cash</button>
            </div>
          </div>

          <div class="space-y-2">
            <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest px-1">Upload Receipt Image</label>
            <div @click="$refs.fileInput.click()" class="border-3 border-dashed border-slate-100 rounded-[2rem] p-8 text-center hover:border-teal-200 hover:bg-teal-50/20 transition-all cursor-pointer group">
              <input type="file" ref="fileInput" @change="handleFileUpload" class="hidden" accept="image/*" />
              <div v-if="!form.receipt" class="space-y-3">
                <div class="h-14 w-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto text-slate-400 group-hover:scale-110 transition-transform">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
                <p class="text-xs text-slate-400 font-bold uppercase tracking-wider">Tap to upload receipt</p>
              </div>
              <div v-else class="space-y-4">
                <img :src="imagePreview" class="h-32 mx-auto rounded-xl shadow-md" />
                <p class="text-[10px] font-black text-teal-600 tracking-widest uppercase">File Selected • Click to change</p>
              </div>
            </div>
          </div>

          <div class="pt-4 space-y-4">
             <button @click="submitPayment" :disabled="!isFormValid || submitting" class="w-full py-5 bg-slate-900 hover:bg-black text-white rounded-[1.5rem] font-black uppercase tracking-widest transition-all shadow-xl disabled:opacity-20 disabled:cursor-not-allowed flex items-center justify-center gap-3">
               <span v-if="submitting" class="h-4 w-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
               Submit For Validation
             </button>
             
             <button v-if="clinic" @click="notifyWhatsApp" class="w-full py-4 border-2 border-slate-100 hover:border-teal-200 text-slate-400 hover:text-teal-600 rounded-[1.5rem] font-black uppercase tracking-widest transition-all flex items-center justify-center gap-2 text-[10px]">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                 <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.246 2.248 3.484 5.232 3.484 8.412-.003 6.557-5.338 11.892-11.893 11.892-1.91 0-3.791-.453-5.485-1.316l-6.512 1.724zm5.972-3.834l.366.217c1.474.876 3.165 1.339 4.908 1.34h.001c5.895 0 10.693-4.798 10.696-10.696 0-2.857-1.112-5.542-3.133-7.561-2.021-2.022-4.707-3.134-7.564-3.134-5.896 0-10.693 4.798-10.696 10.696-.001 1.942.528 3.832 1.533 5.488l.238.388-.999 3.646 3.754-.984zm12.308-7.942c-.22-.11-.1.303-.523-.22-.328-.276-1.54-.757-1.76-.867-.22-.11-.38-.165-.541.077-.162.242-.622.783-.763.943-.14.16-.28.18-.5.07-.22-.11-.926-.342-1.764-1.09-.652-.581-1.092-1.3-1.219-1.52-.128-.22-.014-.339.096-.448.1-.099.22-.25.33-.375.11-.125.147-.213.22-.352.074-.14.037-.26-.018-.369-.055-.11-.541-1.303-.741-1.785-.195-.471-.39-.407-.541-.414-.14-.007-.3-.008-.46-.008s-.421.06-.641.3c-.22.24-.84.821-.84 2.001 0 1.18.86 2.321.981 2.481.12.16 1.69 2.581 4.095 3.621.571.247 1.017.395 1.365.505.576.183 1.1.157 1.513.096.46-.067 1.416-.578 1.616-1.138.2-.56.2-1.041.141-1.139-.06-.098-.22-.158-.441-.268z"/>
               </svg>
               Fast Track: Notify via WhatsApp
             </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from '../api';

const clinic = ref(null);
const plans = ref([]);
const submitting = ref(false);
const imagePreview = ref(null);

const form = ref({
  plan_id: null,
  amount: 0,
  method: 'edahabia',
  receipt: null
});

const isTrial = computed(() => clinic.value && new Date(clinic.value.trial_ends_at) > new Date());
const isPaid = computed(() => clinic.value && clinic.value.payment_status === 'paid' && new Date(clinic.value.subscription_ends_at) > new Date());

const statusBannerClass = computed(() => {
  if (isPaid) return 'bg-gradient-to-br from-teal-500 to-teal-700 shadow-teal-200';
  if (isTrial) return 'bg-gradient-to-br from-indigo-500 to-purple-700 shadow-indigo-200';
  if (clinic.value?.payment_status === 'pending') return 'bg-gradient-to-br from-orange-400 to-orange-600 shadow-orange-200';
  return 'bg-gradient-to-br from-slate-700 to-slate-900 shadow-slate-300';
});

const statusMessage = computed(() => {
  if (isPaid) return 'System Fully Activated';
  if (isTrial) return 'Free Trial Active';
  if (clinic.value?.payment_status === 'pending') return 'Account Under Review';
  return 'Account Inactive';
});

const statusSubMessage = computed(() => {
  if (isPaid) return `Your subscription is valid until ${formatDate(clinic.value.subscription_ends_at)}`;
  if (isTrial) return `Enjoy full access until ${formatDate(clinic.value.trial_ends_at)}`;
  if (clinic.value?.payment_status === 'pending') return 'Our team is verifying your payment receipt.';
  return 'Select a plan and submit payment to activate your services.';
});

const isFormValid = computed(() => form.value.plan_id && form.value.receipt && form.value.method);

const fetchData = async () => {
  try {
    const [clRes, plRes] = await Promise.all([
      axios.get('/clinic/settings'),
      axios.get('/super/plans') // Note: We might need a public/clinic-accessible plans endpoint
    ]);
    clinic.value = clRes.data;
    plans.value = plRes.data;
    
    // Auto select first plan if none
    if (!form.value.plan_id && plans.value.length > 0) {
      form.value.plan_id = plans.value[0].id;
      form.value.amount = plans.value[0].price;
    }
  } catch (err) {
    console.error('Failed to load subscription data', err);
  }
};

const handleFileUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    form.value.receipt = file;
    imagePreview.value = URL.createObjectURL(file);
  }
};

const submitPayment = async () => {
  submitting.value = true;
  const formData = new FormData();
  formData.append('plan_id', form.value.plan_id);
  formData.append('amount', form.value.amount);
  formData.append('method', form.value.method);
  formData.append('receipt', form.value.receipt);

  try {
    await axios.post('/clinic/payments', formData);
    alert('Payment submitted successfully!');
    fetchData();
  } catch (err) {
    alert(err.response?.data?.message || 'Submission failed');
  } finally {
    submitting.value = false;
  }
};

const notifyWhatsApp = () => {
  const phone = '213770000000'; // Platform owner number
  const message = `Hello, I have submitted a payment for clinic: ${clinic.value.name}. Please check the verification ticket.`;
  window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`, '_blank');
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' });
};

onMounted(fetchData);
</script>
