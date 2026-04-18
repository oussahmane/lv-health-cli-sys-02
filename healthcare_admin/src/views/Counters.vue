<template>
  <AdminLayout title="Rooms & Counters">
    <div class="max-w-5xl mx-auto space-y-8">
      <!-- Header Action -->
      <div class="flex items-center justify-between bg-white p-8 rounded-4xl shadow-sm border border-slate-100">
         <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Clinic Room Management</h2>
            <p class="text-slate-500 font-medium">Define your physical rooms, doctor offices, or service counters.</p>
         </div>
         <button @click="openCreateModal" class="px-6 py-4 bg-teal-600 text-white rounded-2xl font-black shadow-xl shadow-teal-100 hover:bg-teal-700 hover:-translate-y-1 transition-all flex items-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add New Room
         </button>
      </div>

      <!-- Counters Grid -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-pulse">
        <div v-for="i in 3" :key="i" class="h-48 bg-slate-100 rounded-4xl"></div>
      </div>
      
      <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="counter in counters" :key="counter.id" class="bg-white p-8 rounded-4xl border border-slate-100 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition-all group relative overflow-hidden">
          <!-- Active Indicator -->
          <div :class="counter.is_active ? 'bg-teal-500' : 'bg-slate-300'" class="absolute top-0 right-0 h-2 w-16 rotate-45 translate-x-6 -translate-y-1 shadow-sm"></div>

          <div class="space-y-4">
            <div class="h-12 w-12 bg-slate-50 rounded-2xl flex items-center justify-center text-teal-600 border border-slate-100 group-hover:scale-110 transition-transform">
               <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
               </svg>
            </div>
            <div>
              <h3 class="text-xl font-black text-slate-900 line-clamp-1">{{ counter.name }}</h3>
              <p class="text-slate-500 text-sm font-medium line-clamp-1">{{ counter.description || 'No description' }}</p>
            </div>
            
            <div class="pt-4 flex items-center justify-between border-t border-slate-50">
              <span :class="counter.is_active ? 'text-teal-600 bg-teal-50 border-teal-100' : 'text-slate-400 bg-slate-50 border-slate-100'" class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest border">
                {{ counter.is_active ? 'Active' : 'Disabled' }}
              </span>
              <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-all">
                <button @click="openEditModal(counter)" class="p-2 text-slate-400 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition-all">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                </button>
                <button @click="deleteCounter(counter.id)" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-all">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && counters.length === 0" class="text-center p-20 bg-white rounded-4xl border border-slate-100 shadow-sm">
         <div class="h-20 w-20 bg-slate-50 text-slate-300 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
         </div>
         <h3 class="text-xl font-bold text-slate-900">No rooms defined yet</h3>
         <p class="text-slate-500 max-w-sm mx-auto mt-2">Before doctors can start calling patients, you need to define at least one room or counter.</p>
      </div>

      <!-- Modal -->
      <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-6 backdrop-blur-md bg-slate-900/50">
        <div class="bg-white w-full max-w-lg rounded-4xl shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
           <div class="px-8 py-6 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
              <h3 class="text-xl font-black text-slate-800">{{ isEditing ? 'Edit Room' : 'Add New Room' }}</h3>
              <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                 </svg>
              </button>
           </div>
           <form @submit.prevent="saveCounter" class="p-8 space-y-6">
              <div>
                 <label class="block text-sm font-bold text-slate-700 mb-2">Room Name / Label</label>
                 <input v-model="form.name" type="text" required placeholder="Ex: Room 1, Clinic Suite, Dr. House..." class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all placeholder-slate-400" />
              </div>
              <div>
                 <label class="block text-sm font-bold text-slate-700 mb-2">Description (Optional)</label>
                 <textarea v-model="form.description" rows="3" placeholder="Ex: Ground floor, pediatric department..." class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all placeholder-slate-400 resize-none"></textarea>
              </div>
              <div v-if="isEditing">
                 <label class="flex items-center gap-3 cursor-pointer group">
                    <div class="relative">
                      <input v-model="form.is_active" type="checkbox" class="sr-only" />
                      <div class="w-12 h-6 bg-slate-200 rounded-full group-hover:bg-slate-300 transition-all" :class="form.is_active ? 'bg-teal-500' : ''"></div>
                      <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all" :class="form.is_active ? 'translate-x-6' : ''"></div>
                    </div>
                    <span class="text-sm font-bold text-slate-700">Room is Active</span>
                 </label>
              </div>
              <div class="pt-4">
                 <button type="submit" :disabled="submitting" class="w-full py-4 bg-teal-600 text-white rounded-2xl font-black shadow-xl shadow-teal-100 hover:bg-teal-700 transition-all flex items-center justify-center gap-3">
                    <span v-if="submitting" class="animate-spin h-5 w-5 border-2 border-white rounded-full border-t-transparent"></span>
                    {{ isEditing ? 'Update Room' : 'Create Room' }}
                 </button>
              </div>
           </form>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';
import AdminLayout from '../components/AdminLayout.vue';

const counters = ref([]);
const loading = ref(true);
const showModal = ref(false);
const submitting = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = ref({
  name: '',
  description: '',
  is_active: true
});

async function fetchCounters() {
  loading.value = true;
  try {
    const response = await api.get('/clinic/counters');
    counters.value = response.data;
  } catch (err) {
    console.error('Failed to fetch counters:', err);
  } finally {
    loading.value = false;
  }
}

function openCreateModal() {
  isEditing.value = false;
  form.value = { name: '', description: '', is_active: true };
  showModal.value = true;
}

function openEditModal(counter) {
  isEditing.value = true;
  editingId.value = counter.id;
  form.value = { ...counter };
  showModal.value = true;
}

async function saveCounter() {
  submitting.value = true;
  try {
    if (isEditing.value) {
      await api.put(`/clinic/counters/${editingId.value}`, form.value);
    } else {
      await api.post('/clinic/counters', form.value);
    }
    showModal.value = false;
    fetchCounters();
  } catch (err) {
    alert('Failed to save room.');
  } finally {
    submitting.value = false;
  }
}

async function deleteCounter(id) {
  if (!confirm('Are you sure? This will remove the room assignments.')) return;
  try {
    await api.delete(`/clinic/counters/${id}`);
    fetchCounters();
  } catch (err) {
    alert('Failed to delete room.');
  }
}

onMounted(fetchCounters);
</script>

<style scoped>
.rounded-4xl { border-radius: 2.5rem; }
</style>
