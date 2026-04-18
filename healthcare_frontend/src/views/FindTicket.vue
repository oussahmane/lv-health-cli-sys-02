<template>
  <div class="min-h-screen bg-slate-50 font-sans flex flex-col items-center justify-center p-6">
    <div class="w-full max-w-md bg-white p-8 rounded-4xl shadow-xl shadow-slate-200/50 border border-slate-100 space-y-8">
      <!-- Header -->
      <div class="text-center space-y-2">
        <div class="h-16 w-16 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-teal-100 shadow-xl shadow-teal-50/50">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <h2 class="text-2xl font-black text-slate-900 tracking-tight">Find My Ticket</h2>
        <p class="text-slate-500 text-sm">Enter the phone number you used to register.</p>
      </div>

      <!-- Search Form -->
      <form @submit.prevent="handleSearch" class="space-y-6">
        <div>
          <label class="block text-sm font-bold text-slate-700 mb-2">Phone Number</label>
          <input v-model="phone" type="tel" required placeholder="Ex: 0555 00 00 00" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 focus:bg-white outline-none transition-all placeholder-slate-400 font-medium" />
        </div>

        <button type="submit" :disabled="loading" class="w-full py-4 bg-teal-600 text-white rounded-2xl font-black shadow-xl shadow-teal-100 hover:bg-teal-700 hover:-translate-y-1 active:translate-y-0 transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:translate-y-0">
          <span v-if="loading" class="animate-spin h-5 w-5 border-2 border-white rounded-full border-t-transparent"></span>
          {{ loading ? 'Searching...' : 'Search Tickets' }}
        </button>
      </form>

      <!-- Results Section -->
      <div v-if="tickets.length > 0" class="mt-8 space-y-4 animate-in slide-in-from-bottom-4 duration-300">
        <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 px-2">Active Tickets Found</h3>
        <div class="space-y-3">
          <button v-for="ticket in tickets" :key="ticket.id" @click="goToTicket(ticket.url)" class="w-full p-6 transition-all text-left bg-slate-50 border-2 border-slate-100 rounded-3xl hover:bg-teal-50 hover:border-teal-100 group">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs font-black uppercase tracking-widest text-teal-600 mb-1">Ticket Holder</p>
                <p class="text-xl font-black text-slate-900">{{ ticket.patient_name }}</p>
              </div>
              <div class="h-12 w-12 bg-white rounded-xl shadow-sm border border-slate-100 flex items-center justify-center text-xl font-black text-teal-600 group-hover:scale-110 transition-transform">
                #{{ ticket.queue_number }}
              </div>
            </div>
            <div class="mt-3 flex items-center gap-2">
              <span class="px-2 py-0.5 rounded bg-white text-[10px] font-bold uppercase tracking-tight border text-slate-500">{{ ticket.status }}</span>
              <span class="text-[10px] font-bold text-teal-600 ml-auto flex items-center gap-1 group-hover:translate-x-1 transition-transform">
                Open Ticket 
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </span>
            </div>
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="searched && tickets.length === 0" class="text-center p-8 bg-slate-50 rounded-3xl border border-slate-100 animate-in fade-in duration-300">
        <p class="text-slate-500 font-medium">No active tickets found for this number today.</p>
        <button @click="router.push(`/q/${route.params.slug}`)" class="mt-4 text-teal-600 font-bold hover:underline text-sm underline-offset-4">Register new patient</button>
      </div>

      <!-- Back Link -->
      <button v-if="!searched" @click="router.push(`/q/${route.params.slug}`)" class="w-full text-center text-slate-400 text-sm font-medium hover:text-slate-600 transition-all">
        Back to registration
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../api';

const route = useRoute();
const router = useRouter();

const phone = ref('');
const loading = ref(false);
const searched = ref(false);
const tickets = ref([]);

async function handleSearch() {
  loading.value = true;
  searched.value = false;
  try {
    const response = await api.get(`/q/${route.params.slug}/find-ticket?phone=${phone.value}`);
    tickets.value = response.data.tickets;
    searched.value = true;
    
    // Auto-redirect if exactly one active ticket is found
    if (tickets.value.length === 1) {
      goToTicket(tickets.value[0].url);
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Something went wrong.');
  } finally {
    loading.value = false;
  }
}

function goToTicket(url) {
  router.push(url);
}
</script>

<style scoped>
.rounded-4xl { border-radius: 2.5rem; }
</style>
