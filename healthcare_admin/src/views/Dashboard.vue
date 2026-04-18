<template>
  <AdminLayout title="Today's Live Queue">
    <div class="space-y-8">
      <!-- Now Serving Banner -->
      <div v-if="auth.can('manage_queue') || auth.isDoctor" class="bg-gradient-to-r from-teal-600 to-teal-500 rounded-3xl p-8 text-white shadow-xl shadow-teal-100 flex items-center justify-between overflow-hidden relative">
        <div class="relative z-10">
          <h2 class="text-teal-100 font-bold uppercase tracking-widest text-sm mb-2">Currently Serving</h2>
          <div class="flex items-baseline gap-4">
             <span class="text-7xl font-black tracking-tighter">#{{ currentServing }}</span>
             <div class="flex flex-col">
                <span v-if="nowServingPatient" class="text-xl font-medium opacity-90">{{ nowServingPatient.patient_name }}</span>
                <span v-if="selectedCounterObj" class="text-xs font-black uppercase tracking-widest text-teal-200 mt-1">
                  At {{ selectedCounterObj.name }}
                </span>
             </div>
          </div>
          <div class="mt-6 flex flex-wrap gap-3">
             <!-- Room Selector -->
             <div class="relative group min-w-[200px]">
                <select v-model="selectedCounterId" class="w-full px-4 py-3 bg-teal-700/50 text-white rounded-xl font-bold border border-teal-400/30 focus:border-white outline-none appearance-none cursor-pointer">
                   <option :value="null" class="text-slate-800">Select Room / Counter</option>
                   <option v-for="c in availableCounters" :key="c.id" :value="c.id" class="text-slate-800">{{ c.name }}</option>
                </select>
                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none opacity-50">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </div>
             </div>

             <button v-if="auth.can('manage_queue')" @click="callNext" :disabled="loading || !selectedCounterId" class="px-6 py-3 bg-white text-teal-700 rounded-xl font-bold shadow-lg hover:bg-teal-50 transition-all flex items-center gap-2 disabled:opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                </svg>
                Call Next Patient
             </button>
             <template v-if="auth.can('manage_settings')">
               <button v-if="isPaused" @click="togglePause(false)" class="px-6 py-3 bg-orange-100 text-orange-700 rounded-xl font-bold hover:bg-orange-200 transition-all">Resume Queue</button>
               <button v-else @click="togglePause(true)" class="px-6 py-3 bg-teal-700/30 text-white rounded-xl font-bold hover:bg-teal-700/50 transition-all backdrop-blur-sm">Pause Queue</button>
             </template>
          </div>
          <p v-if="!selectedCounterId && auth.can('manage_queue')" class="mt-2 text-xs font-bold text-teal-200 animate-pulse">
            ⚠️ Please select your room before calling patients
          </p>
        </div>
        <div class="hidden lg:block opacity-10 absolute -right-10 -bottom-10 rotate-12">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-64 w-64" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
      </div>

      <!-- Queue Table -->
      <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
         <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
           <div class="flex items-center gap-4">
              <h3 class="font-bold text-slate-800 flex items-center gap-2">
                <span class="h-2 w-2 bg-teal-500 rounded-full animate-pulse"></span>
                Live Waiting List
              </h3>
              <button v-if="auth.can('manage_queue')" @click="showAddModal = true" class="px-4 py-1.5 bg-teal-600 text-white text-xs font-black uppercase tracking-widest rounded-lg hover:bg-teal-700 transition-all flex items-center gap-2">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                   <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                 </svg>
                 Add Patient
              </button>
           </div>
           <div class="flex gap-4">
              <span class="text-sm font-medium text-slate-500"><b class="text-slate-900">{{ waitingCount }}</b> Waiting</span>
              <span class="text-sm font-medium text-slate-500"><b class="text-slate-900">{{ doneCount }}</b> Done</span>
           </div>
        </div>
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/50 border-b border-slate-100">
              <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">#</th>
              <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Patient Name</th>
              <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Status</th>
              <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Priority</th>
              <th class="px-8 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <template v-for="patient in sortedQueue" :key="patient.id">
              <tr v-if="patient.status !== 'done' && patient.status !== 'cancelled' && patient.status !== 'no_show'" class="hover:bg-slate-50/30 transition-all" :class="patient.status === 'called' ? 'bg-teal-50/30' : ''">
                <td class="px-8 py-5">
                  <span class="font-black text-slate-400">#{{ patient.queue_number }}</span>
                </td>
                <td class="px-8 py-5">
                  <div>
                    <h4 class="font-bold text-slate-800">{{ patient.patient_name }}</h4>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-slate-100 text-slate-500">{{ patient.source }}</span>
                    <span v-if="patient.handled_by" class="ml-2 text-[10px] text-teal-600 font-bold italic">Assigned</span>
                  </div>
                </td>
                <td class="px-8 py-5">
                  <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-tight" :class="statusClass(patient.status)">
                    {{ patient.status }}
                  </span>
                </td>
                <td class="px-8 py-5">
                   <div class="flex items-center gap-2">
                      <div class="relative group">
                         <button @click="togglePriorityMenu(patient)" :disabled="!auth.can('manage_queue')" class="h-8 w-8 rounded-full flex items-center justify-center transition-all" :class="patient.priority ? 'text-orange-500 bg-orange-50 border border-orange-100' : 'text-slate-300 hover:text-orange-400 disabled:opacity-30 disabled:hover:text-slate-300'">
                           <svg xmlns="http://www.w3.org/2000/svg" :class="patient.priority ? 'fill-current' : ''" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                           </svg>
                         </button>
                         
                         <!-- Priority Selection Dropdown -->
                         <div v-if="activePriorityMenu === patient.id && auth.can('manage_queue')" class="absolute left-0 mt-2 w-48 bg-white border border-slate-100 rounded-xl shadow-2xl z-50 overflow-hidden">
                            <button @click="updatePriority(patient, false, 'regular')" class="w-full px-4 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2">
                               <div class="h-2 w-2 rounded-full bg-slate-300"></div> Normal
                            </button>
                            <button @click="updatePriority(patient, true, 'urgent')" class="w-full px-4 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2 text-red-600 font-bold">
                               <div class="h-2 w-2 rounded-full bg-red-500"></div> Urgent
                            </button>
                            <button @click="updatePriority(patient, true, 'elderly')" class="w-full px-4 py-2 text-left text-sm hover:bg-slate-50 flex items-center gap-2 text-orange-600 font-bold">
                               <div class="h-2 w-2 rounded-full bg-orange-500"></div> Elderly/Special
                            </button>
                         </div>
                      </div>
                      <span v-if="patient.priority_type !== 'regular'" class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest border" :class="patient.priority_type === 'urgent' ? 'bg-red-50 text-red-600 border-red-100' : 'bg-orange-50 text-orange-600 border-orange-100'">
                        {{ patient.priority_type }}
                      </span>
                   </div>
                </td>
                <td class="px-8 py-5 text-right space-x-2">
                  <template v-if="auth.can('consult_patients')">
                    <button v-if="patient.status === 'called'" @click="updateStatus(patient, 'in_examination')" class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 transition-all">Start Exam</button>
                    <button v-if="patient.status === 'in_examination'" @click="updateStatus(patient, 'done')" class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-bold hover:bg-green-700 transition-all">Mark Done</button>
                  </template>
                  <button v-if="auth.can('manage_queue') || auth.isDoctor" @click="updateStatus(patient, 'no_show')" class="px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-xs font-bold hover:bg-slate-200 transition-all">No Show</button>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>

      <!-- Add Patient Modal -->
      <div v-if="showAddModal && auth.can('manage_queue')" class="fixed inset-0 z-[100] flex items-center justify-center p-6 backdrop-blur-md bg-slate-900/50">
         <div class="bg-white w-full max-w-lg rounded-4xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
               <h3 class="text-xl font-black text-slate-800">Manual Check-In</h3>
               <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
               </button>
            </div>
            <form @submit.prevent="addPatient" class="p-8 space-y-6">
               <div>
                  <label class="block text-sm font-bold text-slate-700 mb-2">Patient Full Name</label>
                  <input v-model="newPatient.patient_name" type="text" required placeholder="Ex: Jane Doe" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all placeholder-slate-400" />
               </div>
               <div>
                  <label class="block text-sm font-bold text-slate-700 mb-2">Phone Number (Optional)</label>
                  <input v-model="newPatient.patient_phone" type="tel" placeholder="Ex: 0555..." class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all placeholder-slate-400" />
               </div>
               <div class="flex gap-4">
                  <div class="flex-1">
                     <label class="block text-sm font-bold text-slate-700 mb-2">Priority Level</label>
                     <select v-model="newPatient.priority_type" class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all appearance-none cursor-pointer">
                        <option value="regular">Regular / Normal</option>
                        <option value="urgent">Urgent Case</option>
                        <option value="elderly">Elderly / Special Needs</option>
                     </select>
                  </div>
               </div>
               <div v-if="newPatient.priority_type !== 'regular'" class="p-4 bg-orange-50 rounded-2xl border border-orange-100 flex gap-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                  </svg>
                  <p class="text-xs text-orange-800 font-medium">This patient will be moved to the FRONT of the queue.</p>
               </div>
               <div class="pt-4">
                  <button type="submit" :disabled="loading" class="w-full py-4 bg-teal-600 text-white rounded-2xl font-black shadow-xl shadow-teal-100 hover:bg-teal-700 transition-all flex items-center justify-center gap-3">
                     <span v-if="loading" class="animate-spin h-5 w-5 border-2 border-white rounded-full border-t-transparent"></span>
                     Check In Patient
                  </button>
               </div>
            </form>
         </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import api from '../api';
import echo from '../echo';
import { useAuthStore } from '../authStore';
import AdminLayout from '../components/AdminLayout.vue';

const auth = useAuthStore();
const queue = ref([]);
const currentServing = ref(0);
const isPaused = ref(false);
const loading = ref(false);
const showAddModal = ref(false);
const activePriorityMenu = ref(null);
const availableCounters = ref([]);
const selectedCounterId = ref(localStorage.getItem('selected_counter_id') ? parseInt(localStorage.getItem('selected_counter_id')) : null);

const selectedCounterObj = computed(() => {
  return availableCounters.value.find(c => c.id === selectedCounterId.value);
});

const newPatient = ref({
  patient_name: '',
  patient_phone: '',
  priority_type: 'regular',
});

const nowServingPatient = computed(() => {
  return queue.value.find(p => p.queue_number === currentServing.value && (p.status === 'called' || p.status === 'in_examination'));
});

const sortedQueue = computed(() => {
  return [...queue.value].sort((a, b) => {
    // Sort by priority type weight
    const getWeight = (type) => {
      if (type === 'urgent') return 1;
      if (type === 'elderly') return 2;
      return 3;
    };
    
    const weightA = getWeight(a.priority_type);
    const weightB = getWeight(b.priority_type);
    
    if (weightA !== weightB) return weightA - weightB;
    if (a.priority !== b.priority) return b.priority ? 1 : -1;
    return a.queue_number - b.queue_number;
  });
});

const waitingCount = computed(() => queue.value.filter(p => p.status === 'waiting').length);
const doneCount = computed(() => queue.value.filter(p => p.status === 'done').length);

async function fetchData() {
  try {
    const response = await api.get('/clinic/dashboard');
    queue.value = response.data.queue;
    currentServing.value = response.data.current_serving;
    isPaused.value = response.data.is_paused;
    availableCounters.value = response.data.counters;
  } catch (err) {
    console.error('Failed to fetch dashboard data:', err);
  } finally {
    loading.value = false;
  }
}

async function callNext() {
  if (!selectedCounterId.value) {
    alert('Please select your room/counter first.');
    return;
  }
  
  loading.value = true;
  try {
    const response = await api.post('/clinic/queue/call-next', {
      counter_id: selectedCounterId.value
    });
    // Remember counter selection
    localStorage.setItem('selected_counter_id', selectedCounterId.value);
    fetchData(); 
  } catch (err) {
    alert(err.response?.data?.message || 'No patients to call');
  } finally {
    loading.value = false;
  }
}

async function updateStatus(patient, status) {
  try {
    await api.post(`/clinic/queue/${patient.id}/status`, { status });
    fetchData();
  } catch (err) {
    console.error('Failed to update status:', err);
  }
}

async function togglePause(paused) {
  try {
    await api.post('/clinic/settings/pause', { is_paused: paused, message: paused ? 'Currently taking a short break.' : '' });
    isPaused.value = paused;
  } catch (err) {
    console.error('Failed to toggle pause:', err);
  }
}

async function addPatient() {
  loading.value = true;
  try {
    const payload = {
      ...newPatient.value,
      priority: newPatient.value.priority_type !== 'regular'
    };
    await api.post('/clinic/queue', payload);
    showAddModal.value = false;
    newPatient.value = { patient_name: '', patient_phone: '', priority_type: 'regular' };
    fetchData();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to add patient');
  } finally {
    loading.value = false;
  }
}

function togglePriorityMenu(patient) {
  if (activePriorityMenu.value === patient.id) {
    activePriorityMenu.value = null;
  } else {
    activePriorityMenu.value = patient.id;
  }
}

async function updatePriority(patient, priority, type) {
  try {
    await api.post(`/clinic/queue/${patient.id}/priority`, { priority, priority_type: type });
    activePriorityMenu.value = null;
    fetchData();
  } catch (err) {
    console.error('Failed to update priority:', err);
  }
}

function statusClass(status) {
  switch (status) {
    case 'waiting': return 'bg-slate-100 text-slate-600 border border-slate-200';
    case 'called': return 'bg-teal-100 text-teal-700 border border-teal-200 animate-pulse';
    case 'in_examination': return 'bg-blue-100 text-blue-700 border border-blue-200';
    case 'done': return 'bg-green-100 text-green-700 border border-green-200';
    default: return 'bg-slate-50 text-slate-400';
  }
}

onMounted(() => {
  fetchData();
  
  if (auth.user?.clinic_id) {
    echo.channel(`clinic.${auth.user.clinic_id}.queue`)
      .listen('.queue.patient.joined', (e) => {
        queue.value.push(e.patientData);
      })
      .listen('.queue.next.called', (e) => {
        currentServing.value = e.currentServingNumber;
        fetchData();
      })
      .listen('.queue.status.updated', (e) => {
        fetchData();
      })
      .listen('.queue.pause.toggled', (e) => {
        isPaused.value = e.isPaused;
      });
  }
});

onUnmounted(() => {
  if (auth.user?.clinic_id) {
    echo.leave(`clinic.${auth.user.clinic_id}.queue`);
  }
});
</script>
