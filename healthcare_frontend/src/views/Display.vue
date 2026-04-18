<template>
  <div class="min-h-screen bg-slate-900 text-white font-sans overflow-hidden flex flex-col">
    <!-- Header -->
    <header class="p-8 border-b border-slate-800 flex items-center justify-between">
       <div class="flex items-center gap-6">
          <div class="h-16 w-16 bg-white rounded-2xl flex items-center justify-center p-2 shadow-xl">
             <img v-if="clinic.logo" :src="'http://localhost:8000/storage/' + clinic.logo" class="h-full w-full object-contain" />
             <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
             </svg>
          </div>
          <div>
             <h1 class="text-4xl font-black tracking-tight">{{ clinic.name }}</h1>
             <p class="text-slate-400 font-bold uppercase tracking-widest text-sm mt-1">Live Queue Display</p>
          </div>
       </div>
       <div class="text-right">
          <p class="text-5xl font-black tabular-nums">{{ currentTime }}</p>
          <p class="text-teal-500 font-black uppercase tracking-widest text-sm">{{ currentDate }}</p>
       </div>
    </header>

    <!-- Main Display Body -->
    <main class="flex-1 flex relative">
       <!-- CLOSED / PAUSED OVERLAY -->
       <div v-if="isClosed || isPaused" class="absolute inset-0 z-50 bg-slate-950/90 backdrop-blur-3xl flex flex-col items-center justify-center text-center p-20 space-y-12">
          <div :class="isPaused ? 'bg-orange-500/10 text-orange-500' : 'bg-red-500/10 text-red-500'" class="h-32 w-32 rounded-full flex items-center justify-center animate-pulse">
             <svg v-if="isPaused" xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
             </svg>
             <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
             </svg>
          </div>
          <div class="space-y-4">
            <h2 class="text-7xl font-black uppercase tracking-tighter">{{ isPaused ? 'Clinic is on Break' : 'Clinic is Currently Closed' }}</h2>
            <p class="text-2xl text-slate-400 font-medium max-w-2xl mx-auto">
              {{ isPaused ? 'Our team is taking a short break to better serve you. We will resume shortly.' : 'Registration and queue tracking are unavailable at this time.' }}
            </p>
          </div>
       </div>

       <!-- Multi-Counter Serving Section -->
       <div class="flex-[3] p-12 overflow-y-auto scrollbar-hide">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-8 h-full items-start">
             <div v-for="serving in activeServings" :key="serving.counter_name" 
                  class="bg-slate-800/40 rounded-5xl border border-slate-700/50 p-10 flex flex-col items-center justify-center relative overflow-hidden transition-all duration-700"
                  :class="{'ring-8 ring-teal-500/30 scale-105 bg-slate-800/80 shadow-[0_0_100px_rgba(20,184,166,0.2)]': blink && serving.queue_number === servingNow}">
                
                <div class="absolute top-0 left-0 w-full h-2 bg-teal-500/20"></div>
                <div v-if="blink && serving.queue_number === servingNow" class="absolute inset-0 bg-teal-500/5 animate-pulse"></div>

                <h3 class="text-3xl font-black text-teal-500 uppercase tracking-[0.2em] mb-4">{{ serving.counter_name }}</h3>
                <div class="text-[12rem] font-black leading-none text-white drop-shadow-2xl">
                   #{{ serving.queue_number }}
                </div>
                <p class="text-2xl font-bold text-slate-400 mt-4 tracking-widest uppercase">{{ serving.patient_name ? maskName(serving.patient_name) : 'Serving...' }}</p>
             </div>
             
             <!-- Empty slots if serving few patients -->
             <div v-if="activeServings.length === 0" class="col-span-2 h-full flex flex-col items-center justify-center opacity-20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-32 w-32 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h2 class="text-4xl font-black">Waiting for Next Patient</h2>
             </div>
          </div>
       </div>

       <!-- Waiting List Section -->
       <div class="flex-1 bg-slate-800/20 p-12 flex flex-col border-l border-slate-800">
          <div class="flex items-center justify-between mb-12">
             <h3 class="text-3xl font-black uppercase tracking-widest text-slate-400">Up Next</h3>
             <span class="px-4 py-1.5 bg-slate-700 rounded-full text-xs font-black uppercase tracking-widest">{{ waitingList.length }} Waiting</span>
          </div>

          <div class="space-y-6 flex-1">
             <transition-group name="list">
                <div v-for="(patient, index) in waitingList.slice(0, 6)" :key="patient.id" class="p-8 bg-slate-800/50 rounded-4xl border border-slate-700/50 flex items-center justify-between backdrop-blur-xl">
                    <div class="flex items-center gap-6">
                       <span class="h-16 w-16 bg-slate-700 text-slate-300 rounded-2xl flex items-center justify-center text-3xl font-black">{{ patient.queue_number }}</span>
                        <div>
                           <div class="flex items-center gap-3">
                              <p class="text-2xl font-black text-white">{{ maskName(patient.patient_name) }}</p>
                              <span v-if="patient.priority_type && patient.priority_type !== 'regular'" class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest border" :class="patient.priority_type === 'urgent' ? 'bg-red-500/10 text-red-500 border-red-500/20' : 'bg-orange-500/10 text-orange-500 border-orange-500/20'">
                                 {{ patient.priority_type }}
                              </span>
                           </div>
                           <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mt-1">Ticket Holder</p>
                        </div>
                     </div>
                    <div v-if="index === 0" class="px-4 py-2 bg-teal-500/10 text-teal-500 rounded-xl text-xs font-black uppercase tracking-widest border border-teal-500/20 animate-pulse">
                       Next
                    </div>
                </div>
             </transition-group>
          </div>

          <!-- Footer/Status -->
          <div class="mt-auto pt-8 border-t border-slate-700/50 flex items-center gap-4">
             <div class="h-3 w-3 bg-green-500 rounded-full animate-ping"></div>
             <p class="text-sm font-bold text-slate-500 uppercase tracking-widest">Real-time update active</p>
          </div>
       </div>
    </main>

    <!-- Scrolling Info Ticker -->
    <div class="h-16 bg-teal-600 flex items-center overflow-hidden shrink-0">
       <div class="whitespace-nowrap animate-ticker flex items-center gap-20">
          <span v-for="i in 5" :key="i" class="text-lg font-black uppercase tracking-widest text-teal-50">
             {{ parsedTickerMessage }} • Please watch this screen for your number and assigned room •
          </span>
       </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '../api';
import echo from '../echo';

const route = useRoute();
const clinic = ref({});
const activeServings = ref([]);
const servingNow = ref(null); // Used for highlighting last call
const waitingList = ref([]);
const currentTime = ref('');
const currentDate = ref('');
const avgWait = ref(15);
const blink = ref(false);
const isClosed = ref(false);
const isPaused = ref(false);

function updateTime() {
  const now = new Date();
  currentTime.value = now.toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' });
  currentDate.value = now.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
}

function maskName(name) {
  if (!name) return '';
  const parts = name.split(' ');
  if (parts.length === 1) return name[0] + '***';
  return parts[0] + ' ' + parts[parts.length - 1][0] + '***';
}

const parsedTickerMessage = computed(() => {
  const name = clinic.value?.name || 'Prime Care';
  const waitTime = avgWait.value || 0;
  
  const defaultMsg = `Welcome to ${name} • Please watch this screen for your number • Stay hydrated and healthy • Thank you for your patience`;
  
  if (!clinic.value?.display_ticker) return defaultMsg;
  
  return clinic.value.display_ticker
    .replace(/{clinic_name}/g, name)
    .replace(/{wait_time}/g, waitTime);
});

async function fetchData() {
  try {
    const response = await api.get(`/q/${route.params.slug}`);
    clinic.value = response.data.clinic;
    isClosed.value = false;
    isPaused.value = false;
    
    const liveResponse = await api.get(`/q/${route.params.slug}/live`);
    activeServings.value = liveResponse.data.active_servings || [];
    waitingList.value = liveResponse.data.waiting_list || [];
  } catch (err) {
    if (err.response?.data?.clinic) {
       clinic.value = err.response.data.clinic;
    }
    if (err.response?.status === 403) {
       if (err.response.data.status === 'closed') isClosed.value = true;
       if (err.response.data.status === 'paused') isPaused.value = true;
    }
  }
}

// Notification Sound Logic
function playNotification() {
  const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
  audio.play().catch(e => console.log('Audio blocked', e));
}

onMounted(() => {
  updateTime();
  setInterval(updateTime, 1000);
  fetchData();
});

watch(() => clinic.value.id, (id) => {
  if (id) {
    echo.channel(`clinic.${id}.queue`)
      .listen('.queue.next.called', (e) => {
         servingNow.value = e.currentServingNumber;
         blink.value = true;
         playNotification();
         setTimeout(() => blink.value = false, 8000);
         fetchData(); 
      })
      .listen('.queue.patient.joined', fetchData)
      .listen('.queue.status.updated', fetchData)
      .listen('.queue.priority.set', fetchData)
      .listen('.clinic.settings.updated', (e) => {
          if (e.clinicData) {
            if (e.clinicData.name) clinic.value.name = e.clinicData.name;
            if (e.clinicData.display_ticker !== undefined) clinic.value.display_ticker = e.clinicData.display_ticker;
          }
       });
  }
});

onUnmounted(() => {
  if (clinic.value.id) {
    echo.leave(`clinic.${clinic.value.id}.queue`);
  }
});
</script>

<style scoped>
.rounded-4xl { border-radius: 2rem; }
.rounded-5xl { border-radius: 3rem; }

@keyframes ticker {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}

.animate-ticker {
  animation: ticker 60s linear infinite;
}

.list-enter-active, .list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from, .list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
/style>
