<template>
  <div class="min-h-screen bg-slate-50 font-sans">
    <!-- Hero / Clinic Info -->
    <div class="bg-white border-b border-slate-100 px-6 py-12 text-center shadow-sm">
       <div class="max-w-md mx-auto space-y-6">
          <div class="flex justify-center">
            <div class="h-20 w-20 bg-teal-50 rounded-2xl flex items-center justify-center p-1 border border-teal-100 shadow-xl shadow-teal-50/50">
               <img v-if="clinic.logo" :src="'http://localhost:8000/storage/' + clinic.logo" class="h-full w-full object-cover rounded-xl" />
               <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
               </svg>
            </div>
          </div>
          <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">{{ clinic.name }}</h1>
            <p class="text-slate-500 mt-2 font-medium">{{ clinic.address }}</p>
          </div>
          
          <div v-if="clinic.phone" class="inline-flex items-center gap-2 px-4 py-1.5 bg-slate-100 rounded-full text-slate-600 text-xs font-bold uppercase tracking-widest border border-slate-200">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
             </svg>
             {{ clinic.phone }}
          </div>
       </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-md mx-auto px-6 py-12">
       <!-- Availability Checks -->
       <div v-if="error" class="bg-red-50 p-6 rounded-3xl border border-red-100 text-center space-y-4 shadow-xl shadow-red-50/50 mb-8">
          <div class="h-12 w-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
             </svg>
          </div>
          <h3 class="text-lg font-bold text-red-900">{{ error.message }}</h3>
          <p v-if="error.opens_at" class="text-red-700 text-sm">We open at <b>{{ error.opens_at }}</b>. See you then!</p>
          <p v-if="error.next_available" class="text-red-700 text-sm">Next available date: <b>{{ error.next_available }}</b></p>
       </div>

       <!-- Registration Form -->
       <div v-else class="bg-white p-8 rounded-4xl shadow-xl shadow-slate-200/50 border border-slate-100 space-y-8 relative overflow-hidden">
          <div class="space-y-2">
             <h2 class="text-2xl font-black text-slate-900 tracking-tight">Join the Queue</h2>
             <p class="text-slate-500 text-sm">Fill in your details to get a ticket.</p>
          </div>

          <form @submit.prevent="handleRegister" class="space-y-6">
             <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Your Full Name</label>
                <input v-model="form.patient_name" type="text" required placeholder="Ex: John Doe" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 focus:bg-white outline-none transition-all placeholder-slate-400 font-medium" />
             </div>
 
             <div>
                <label class="block text-sm font-bold text-slate-700 mb-2 flex items-center justify-between">
                   Phone Number
                   <span class="text-[10px] text-teal-600 font-black uppercase tracking-widest px-2 py-0.5 rounded bg-teal-50 border border-teal-100">Identity / Recovery</span>
                </label>
                <input v-model="form.patient_phone" type="tel" required placeholder="Ex: 0555 00 00 00" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 focus:bg-white outline-none transition-all placeholder-slate-400 font-medium" />
                <p class="mt-2 text-[10px] text-slate-400 italic">Used only to help you find your ticket if you lose this link.</p>
             </div>
 
             <div class="pt-2">
                <button type="submit" :disabled="loading" class="w-full py-4 bg-teal-600 text-white rounded-2xl font-black shadow-xl shadow-teal-100 hover:bg-teal-700 hover:-translate-y-1 active:translate-y-0 transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:translate-y-0">
                   <span v-if="loading" class="animate-spin h-5 w-5 border-2 border-white rounded-full border-t-transparent"></span>
                   {{ loading ? 'Securing your spot...' : 'Get My Ticket' }}
                </button>
                <button type="button" @click="router.push(`/q/${route.params.slug}/find`)" class="w-full mt-4 py-3 bg-white text-slate-600 border border-slate-200 rounded-2xl font-bold hover:bg-slate-50 transition-all text-sm">
                   Lost your ticket? Find it here
                </button>
                <p class="text-center text-[11px] text-slate-400 mt-4 leading-relaxed px-4">
                   By continuing, you agree to receive a digital ticket for today's visit. 
                </p>
             </div>
          </form>

          <!-- Capacity Badge -->
          <div class="flex items-center justify-between pt-6 border-t border-slate-50 mt-8">
             <div class="flex flex-col">
                <span class="text-[10px] uppercase font-black tracking-widest text-slate-400">Current Wait</span>
                <span class="text-lg font-black text-slate-900">{{ currentQueueCount }} Patients</span>
             </div>
             <div class="h-10 w-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
             </div>
          </div>
       </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../api';

const route = useRoute();
const router = useRouter();

const clinic = ref({});
const error = ref(null);
const loading = ref(false);
const currentQueueCount = ref(0);
const activeServings = ref([]);

const form = ref({
  patient_name: '',
  patient_phone: '',
});

async function fetchClinicInfo() {
  try {
    const response = await api.get(`/q/${route.params.slug}`);
    clinic.value = response.data.clinic;
    currentQueueCount.value = response.data.current_queue_count;
    activeServings.value = response.data.active_servings || [];
  } catch (err) {
    if (err.response?.data?.clinic) {
       clinic.value = err.response.data.clinic;
    }
    
    if (err.response?.data?.status) {
       error.value = err.response.data;
    } else {
       error.value = { message: 'Clinic not found.' };
    }
  }
}

async function handleRegister() {
  loading.value = true;
  try {
    const source = route.query.src || 'custom';
    const response = await api.post(`/q/${route.params.slug}/register?src=${source}`, form.value);
    
    // Success - redirect to ticket
    const ticketUrl = response.data.ticket_url; // Format: /q/slug/ticket/id?token=uuid
    router.push(ticketUrl);
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to register. Please try again.');
  } finally {
    loading.value = false;
  }
}

onMounted(fetchClinicInfo);
</script>

<style scoped>
.rounded-4xl { border-radius: 2.5rem; }
</style>
