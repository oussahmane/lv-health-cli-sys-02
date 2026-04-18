<template>
  <AdminLayout title="Subscription Plans">
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-2xl font-black text-slate-800 tracking-tight">Manage Plans</h2>
          <p class="text-slate-500 text-sm">Define quotas and pricing for clinic subscriptions</p>
        </div>
        <button @click="openAddModal" class="px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-2xl font-bold transition-all shadow-lg shadow-teal-200 flex items-center gap-2 group">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:rotate-90" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          Add New Plan
        </button>
      </div>

      <!-- Plans Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="plan in plans" :key="plan.id" class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden flex flex-col hover:shadow-md transition-shadow">
          <div class="p-8 space-y-4 flex-1">
            <div class="flex justify-between items-start">
              <div class="p-3 bg-teal-50 rounded-2xl text-teal-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div class="text-right">
                <span class="text-3xl font-black text-slate-800">{{ plan.price }}</span>
                <span class="text-slate-400 text-sm font-medium ml-1">DZD/mo</span>
              </div>
            </div>
            
            <div>
              <h3 class="text-xl font-bold text-slate-800">{{ plan.name }}</h3>
              <p class="text-slate-500 text-sm">Targeted at small to medium clinics</p>
            </div>

            <div class="pt-4 space-y-3">
              <div class="flex items-center gap-3 text-sm text-slate-600">
                <div class="h-8 w-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3.005 3.005 0 013.75-2.906z" />
                  </svg>
                </div>
                <span>Up to <strong>{{ plan.max_doctors }}</strong> Doctors</span>
              </div>
              <div class="flex items-center gap-3 text-sm text-slate-600">
                <div class="h-8 w-8 rounded-lg bg-orange-50 flex items-center justify-center text-orange-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                  </svg>
                </div>
                <span>Up to <strong>{{ plan.max_staff }}</strong> Support Staff</span>
              </div>
              <div class="flex items-center gap-3 text-sm text-slate-600">
                <div class="h-8 w-8 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm2 10V5h10v10H5z" clip-rule="evenodd" />
                  </svg>
                </div>
                <span>Up to <strong>{{ plan.max_screens }}</strong> TV Screens</span>
              </div>
            </div>
          </div>
          
          <div class="px-8 py-6 bg-slate-50 border-t border-slate-100 flex items-center gap-3">
            <button @click="openEditModal(plan)" class="flex-1 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold text-sm hover:bg-slate-50 transition-colors">
              Edit Plan
            </button>
            <button @click="confirmDelete(plan)" class="p-2.5 text-slate-400 hover:text-red-600 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-20">
        <div class="h-12 w-12 border-4 border-teal-500 border-t-transparent rounded-full animate-spin"></div>
      </div>

      <!-- Add/Edit Modal -->
      <div v-if="showModal" @click.self="closeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-300">
          <div class="px-8 pt-8 pb-6 border-b border-slate-50">
            <h3 class="text-2xl font-black text-slate-800">{{ isEditing ? 'Edit Plan' : 'Create New Plan' }}</h3>
            <p class="text-slate-500 text-sm mt-1">Set the features and pricing for this tier</p>
          </div>
          
          <form @submit.prevent="savePlan" class="p-8 space-y-5">
            <div class="space-y-1.5">
              <label class="text-xs font-black text-slate-400 uppercase tracking-widest px-1">Plan Name</label>
              <input v-model="form.name" type="text" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all font-medium" placeholder="Ex: Basic, Premium..." />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest px-1">Price (DZD)</label>
                <input v-model="form.price" type="number" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all font-medium" />
              </div>
              <div class="space-y-1.5">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest px-1">Max Screens</label>
                <input v-model="form.max_screens" type="number" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all font-medium" />
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1.5">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest px-1">Max Doctors</label>
                <input v-model="form.max_doctors" type="number" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all font-medium" />
              </div>
              <div class="space-y-1.5">
                <label class="text-xs font-black text-slate-400 uppercase tracking-widest px-1">Max Staff</label>
                <input v-model="form.max_staff" type="number" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-teal-500/10 focus:border-teal-500 outline-none transition-all font-medium" />
              </div>
            </div>

            <div class="pt-6 flex gap-3">
              <button type="button" @click="closeModal" class="flex-1 px-6 py-4 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-2xl font-bold transition-all">
                Cancel
              </button>
              <button type="submit" :disabled="saving" class="flex-[2] px-6 py-4 bg-teal-600 hover:bg-teal-700 text-white rounded-2xl font-bold transition-all shadow-lg shadow-teal-200 flex items-center justify-center gap-2">
                <div v-if="saving" class="h-5 w-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                {{ isEditing ? 'Update Plan' : 'Create Plan' }}
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
import axios from '../api';
import AdminLayout from '../components/AdminLayout.vue';

const plans = ref([]);
const loading = ref(true);
const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const saving = ref(false);

const form = ref({
  name: '',
  price: 0,
  max_doctors: 1,
  max_staff: 2,
  max_screens: 1
});

const fetchPlans = async () => {
  try {
    const response = await axios.get('/super/plans');
    plans.value = response.data;
  } catch (error) {
    console.error('Failed to fetch plans:', error);
  } finally {
    loading.value = false;
  }
};

const openAddModal = () => {
  isEditing.value = false;
  form.value = { name: '', price: 0, max_doctors: 1, max_staff: 2, max_screens: 1 };
  showModal.value = true;
};

const openEditModal = (plan) => {
  isEditing.value = true;
  editingId.value = plan.id;
  form.value = { ...plan };
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const savePlan = async () => {
  saving.value = true;
  try {
    if (isEditing.value) {
      await axios.put(`/super/plans/${editingId.value}`, form.value);
    } else {
      await axios.post('/super/plans', form.value);
    }
    await fetchPlans();
    closeModal();
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to save plan');
  } finally {
    saving.value = false;
  }
};

const confirmDelete = async (plan) => {
  if (confirm(`Are you sure you want to delete the plan "${plan.name}"?`)) {
    try {
      await axios.delete(`/super/plans/${plan.id}`);
      await fetchPlans();
    } catch (error) {
      alert(error.response?.data?.message || 'Failed to delete plan');
    }
  }
};

onMounted(fetchPlans);
</script>
