<template>
  <AdminLayout title="Payment Validations">
    <div class="space-y-6">
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-2xl font-black text-slate-800 tracking-tight">Financial Overview</h2>
          <p class="text-slate-500 text-sm">Review manual payment submissions from clinics</p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-sm space-y-2">
          <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Pending Review</p>
          <p class="text-3xl font-black text-orange-600">{{ pendingPayments.length }}</p>
        </div>
        <div class="bg-teal-600 p-6 rounded-3xl shadow-lg shadow-teal-200 space-y-2">
          <p class="text-xs font-black text-teal-100 uppercase tracking-widest">Total Revenue</p>
          <p class="text-3xl font-black text-white">{{ totalRevenue }} <span class="text-sm font-medium opacity-80">DZD</span></p>
        </div>
      </div>

      <!-- Payments Table -->
      <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Clinic & Plan</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Amount</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Method</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-center">Receipt</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest">Status</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-widest text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="payment in payments" :key="payment.id" class="hover:bg-slate-50/50 transition-colors">
              <td class="px-6 py-5">
                <div class="flex flex-col">
                  <span class="font-bold text-slate-900">{{ payment.clinic?.name }}</span>
                  <span class="text-xs text-slate-400 font-medium">Plan: {{ payment.plan?.name }}</span>
                </div>
              </td>
              <td class="px-6 py-5">
                <span class="font-black text-slate-700">{{ payment.amount }} DZD</span>
              </td>
              <td class="px-6 py-5">
                <span class="px-3 py-1 bg-slate-100 rounded-full text-[10px] font-black uppercase tracking-widest text-slate-600 border border-slate-200">
                  {{ payment.method }}
                </span>
              </td>
              <td class="px-6 py-5 text-center">
                <button @click="viewReceipt(payment)" class="p-2 bg-teal-50 text-teal-600 rounded-xl hover:bg-teal-100 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </button>
              </td>
              <td class="px-6 py-5">
                <span :class="statusClass(payment.status)" class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border">
                  {{ payment.status }}
                </span>
              </td>
              <td class="px-6 py-5 text-right">
                <div v-if="payment.status === 'pending'" class="flex justify-end gap-2">
                  <button @click="openRejectModal(payment)" class="p-2 text-slate-400 hover:text-red-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                  <button @click="approvePayment(payment)" class="p-2 text-slate-400 hover:text-green-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </button>
                </div>
                <div v-else class="text-xs text-slate-400 font-medium italic">
                  Processed on {{ formatDate(payment.updated_at) }}
                </div>
              </td>
            </tr>
            <tr v-if="payments.length === 0">
              <td colspan="6" class="px-6 py-20 text-center">
                <div class="max-w-xs mx-auto space-y-3">
                  <div class="h-16 w-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <h3 class="font-bold text-slate-900">No payments found</h3>
                  <p class="text-xs text-slate-500">All submissions will appear here for verification.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Receipt Viewer -->
      <div v-if="selectedReceipt" @click="selectedReceipt = null" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md">
        <div class="relative max-w-4xl w-full bg-white rounded-[2rem] overflow-hidden shadow-2xl animate-in zoom-in duration-300">
          <button @click="selectedReceipt = null" class="absolute top-6 right-6 p-2 bg-white/80 backdrop-blur-sm rounded-full text-slate-800 hover:bg-white transition-all shadow-lg z-10">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <div class="aspect-video bg-slate-100 flex items-center justify-center overflow-auto p-4">
            <img :src="receiptUrl(selectedReceipt.receipt_path)" class="max-w-full max-h-full object-contain rounded-xl" />
          </div>
          <div class="p-8 bg-white border-t border-slate-100 flex justify-between items-center">
            <div>
              <h4 class="font-black text-xl text-slate-800">{{ selectedReceipt.clinic?.name }}</h4>
              <p class="text-slate-500 text-sm">Receipt for {{ selectedReceipt.amount }} DZD ({{ selectedReceipt.method }})</p>
            </div>
            <div v-if="selectedReceipt.status === 'pending'" class="flex gap-3">
              <button @click="openRejectModal(selectedReceipt)" class="px-6 py-3 bg-red-50 text-red-600 rounded-2xl font-bold hover:bg-red-100 transition-colors">Reject</button>
              <button @click="approvePayment(selectedReceipt)" class="px-6 py-3 bg-teal-600 text-white rounded-2xl font-bold hover:bg-teal-700 transition-all shadow-lg shadow-teal-200">Approve & Activate</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Reject Modal -->
      <div v-if="showRejectModal" @click.self="showRejectModal = false" class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8 animate-in zoom-in duration-300">
          <h3 class="text-xl font-black text-slate-800 mb-2">Reject Payment</h3>
          <p class="text-slate-500 text-sm mb-6">Explain to the clinic why their receipt was rejected.</p>
          
          <textarea v-model="rejectNote" required class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-2xl focus:ring-4 focus:ring-red-500/10 focus:border-red-500 outline-none transition-all font-medium min-h-[120px]" placeholder="Ex: Image is too blurry, amount doesn't match..."></textarea>
          
          <div class="mt-8 flex gap-3">
            <button @click="showRejectModal = false" class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl font-bold">Cancel</button>
            <button @click="confirmReject" class="flex-1 px-6 py-4 bg-red-600 text-white rounded-2xl font-bold shadow-lg shadow-red-200">Confirm Rejection</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from '../api';
import AdminLayout from '../components/AdminLayout.vue';

const payments = ref([]);
const loading = ref(true);
const selectedReceipt = ref(null);
const showRejectModal = ref(false);
const rejectNote = ref('');
const rejectingPayment = ref(null);

const pendingPayments = computed(() => payments.value.filter(p => p.status === 'pending'));
const totalRevenue = computed(() => payments.value.filter(p => p.status === 'approved').reduce((sum, p) => sum + parseFloat(p.amount), 0).toFixed(2));

const fetchPayments = async () => {
  try {
    const response = await axios.get('/super/payments');
    payments.value = response.data;
  } catch (error) {
    console.error('Failed to fetch payments:', error);
  } finally {
    loading.value = false;
  }
};

const viewReceipt = (payment) => {
  selectedReceipt.value = payment;
};

const receiptUrl = (path) => {
  return `http://localhost:8000/storage/${path}`;
};

const approvePayment = async (payment) => {
  if (confirm(`Approve payment of ${payment.amount} DZD and activate clinic ${payment.clinic.name}?`)) {
    try {
      await axios.put(`/super/payments/${payment.id}/approve`);
      await fetchPayments();
      selectedReceipt.value = null;
    } catch (error) {
      alert('Approval failed: ' + error.response?.data?.message);
    }
  }
};

const openRejectModal = (payment) => {
  rejectingPayment.value = payment;
  rejectNote.value = '';
  showRejectModal.value = true;
};

const confirmReject = async () => {
  if (!rejectNote.value) return alert('Please provide a reason for rejection.');
  
  try {
    await axios.put(`/super/payments/${rejectingPayment.value.id}/reject`, { notes: rejectNote.value });
    await fetchPayments();
    showRejectModal.value = false;
    selectedReceipt.value = null;
  } catch (error) {
    alert('Rejection failed');
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const statusClass = (status) => {
  switch (status) {
    case 'approved': return 'bg-green-50 text-green-700 border-green-100';
    case 'pending': return 'bg-orange-50 text-orange-700 border-orange-100';
    case 'rejected': return 'bg-red-50 text-red-700 border-red-100';
    default: return 'bg-slate-50 text-slate-700 border-slate-100';
  }
};

onMounted(fetchPayments);
</script>
