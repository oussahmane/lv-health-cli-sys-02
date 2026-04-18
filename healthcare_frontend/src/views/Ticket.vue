<template>
  <div class="min-h-screen bg-slate-50 font-sans flex flex-col items-center py-12 px-6">
    <div class="w-full max-w-md space-y-8">
      <!-- Header -->
       <div class="text-center space-y-2">
          <p class="text-[10px] font-black uppercase tracking-[0.2em] text-teal-600">Digital Queue Ticket</p>
          <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ clinicName }}</h1>
       </div>

       <!-- Main Ticket Card -->
       <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200/60 border border-slate-100 overflow-hidden relative">
          <!-- Status Ribbon -->
          <div class="h-2 w-full" :class="statusRibbonClass"></div>

          <div class="p-10 space-y-8">
             <div class="text-center">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Your Number</p>
                <div class="relative inline-block">
                   <span class="text-8xl font-black text-slate-900 tracking-tighter">#{{ ticket.queue_number }}</span>
                   <div v-if="ticket.status === 'called'" class="absolute -top-2 -right-4 h-6 w-6 bg-teal-500 rounded-full animate-ping"></div>
                </div>
             </div>

             <!-- Status Specific UI -->
             <div v-if="ticket.status === 'called'" class="bg-teal-50 p-6 rounded-3xl border border-teal-100 text-center space-y-3 animate-bounce">
                <div class="h-10 w-10 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center mx-auto">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                   </svg>
                </div>
                <h3 class="text-xl font-black text-teal-900">You're Called!</h3>
                <p class="text-teal-700 text-sm font-black uppercase tracking-widest bg-white/50 py-2 rounded-xl border border-teal-200" v-if="ticket.counter_name">
                   Proceed to: {{ ticket.counter_name }}
                </p>
                <p class="text-teal-700 text-sm font-medium" v-else>Please proceed to the examination room immediately.</p>
             </div>

             <div v-else-if="ticket.status === 'in_examination'" class="bg-blue-50 p-6 rounded-3xl border border-blue-100 text-center space-y-2">
                <h3 class="text-lg font-black text-blue-900">In Examination</h3>
                <p class="text-blue-700 text-sm" v-if="ticket.counter_name">Currently being served in <b class="uppercase">{{ ticket.counter_name }}</b></p>
                <p class="text-blue-700 text-sm" v-else>Our staff is currently attending to you.</p>
             </div>

             <div v-else-if="ticket.status === 'waiting'" class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                   <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 text-center">
                      <p class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-1">Ahead of You</p>
                      <p class="text-2xl font-black text-slate-900">{{ peopleAhead }}</p>
                   </div>
                   <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 text-center">
                      <p class="text-[10px] uppercase font-black tracking-widest text-slate-400 mb-1">Est. Wait</p>
                      <p class="text-2xl font-black text-slate-900">{{ estWait }} <span class="text-xs">min</span></p>
                   </div>
                </div>

                <div v-if="isPaused" class="bg-orange-50 p-4 rounded-2xl border border-orange-100 flex items-center gap-3">
                   <div class="h-8 w-8 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center shrink-0">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                   </div>
                   <p class="text-xs font-bold text-orange-800 leading-tight">Queue is temporarily paused. Please stay close.</p>
                </div>
             </div>

             <div v-else class="bg-slate-100 p-6 rounded-3xl text-center space-y-2">
                <h3 class="text-lg font-black text-slate-900 uppercase tracking-widest">{{ ticket.status }}</h3>
                <p class="text-slate-500 text-sm">This ticket is no longer active.</p>
             </div>

             <!-- Notification Toggle -->
             <div v-if="ticket.status === 'waiting' && !notificationsEnabled" class="pt-2">
                <button @click="enableNotifications" class="w-full py-4 bg-slate-900 text-white rounded-2xl font-bold text-sm tracking-tight hover:bg-slate-800 transition-all flex items-center justify-center gap-3">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                  Enable Turn Notifications
                </button>
                <p class="text-[10px] text-center text-slate-400 mt-2">Get alerted when there are 3 people ahead or when it's your turn.</p>
             </div>
             <div v-else-if="notificationsEnabled" class="pt-2 text-center text-[10px] font-black uppercase tracking-widest text-teal-600 flex items-center justify-center gap-2">
                <div class="h-1 \w-1 bg-teal-500 rounded-full animate-ping"></div>
                Notifications Active
             </div>
          </div>

          <!-- Ticket Footer (Dashed Divider style) -->
          <div class="p-8 bg-slate-50 border-t border-dashed border-slate-200 relative">
             <!-- Circular cutouts -->
             <div class="absolute -left-3 -top-3 h-6 w-6 rounded-full bg-slate-50"></div>
             <div class="absolute -right-3 -top-3 h-6 w-6 rounded-full bg-slate-50"></div>

             <div class="flex items-center justify-between">
                <div>
                   <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Clinic Serving Now</p>
                   <p class="text-xl font-black text-slate-900">#{{ servingNow }}</p>
                </div>
                <div class="h-12 w-12 bg-white rounded-xl flex items-center justify-center p-2 border border-slate-100 shadow-sm">
                   <img v-if="logo" :src="'http://localhost:8000/storage/' + logo" class="h-full w-full object-contain" />
                </div>
             </div>
          </div>
       </div>

       <!-- WhatsApp Action -->
       <div class="space-y-4">
          <button @click="shareWhatsApp" class="w-full py-5 bg-[#25D366] text-white rounded-[2rem] font-black shadow-xl shadow-green-100 hover:scale-[1.02] active:scale-100 transition-all flex items-center justify-center gap-4">
             <svg class="h-6 w-6 fill-white" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
             Save Ticket to WhatsApp
          </button>
          <p class="text-center text-[11px] text-slate-400 font-medium px-8 leading-relaxed">
            Saves your ticket details & link to WhatsApp so you can easily find it later.
          </p>
       </div>

       <!-- Secondary Actions -->
       <div v-if="ticket.status === 'waiting'" class="flex flex-col items-center gap-6 pt-4">
          <button @click="handleCancel" class="text-xs font-black text-slate-400 hover:text-red-500 transition-colors uppercase tracking-[0.2em]">
            Cancel my spot
          </button>
       </div>

       <p class="text-center text-[10px] text-slate-300 font-medium px-8 leading-relaxed">
         Keep this page open to receive real-time updates. Your ticket number will be called by staff when it's your turn.
       </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../api';
import echo from '../echo';

const route = useRoute();
const router = useRouter();

const ticket = ref({});
const clinicName = ref('');
const logo = ref('');
const servingNow = ref(0);
const peopleAhead = ref(0);
const estWait = ref(0);
const isPaused = ref(false);
const notificationsEnabled = ref(false);

const notifiedProximity = ref(false);
const notifiedCalled = ref(false);

const statusRibbonClass = computed(() => {
  switch (ticket.value.status) {
    case 'waiting': return 'bg-slate-200';
    case 'called': return 'bg-teal-500';
    case 'in_examination': return 'bg-blue-500';
    case 'done': return 'bg-green-500';
    default: return 'bg-slate-400';
  }
});

async function fetchTicketData() {
  try {
    const response = await api.get(`/q/${route.params.slug}/ticket/${route.params.id}?token=${route.query.token}`);
    ticket.value = response.data.queue;
    clinicName.value = response.data.clinic_name;
    logo.value = response.data.logo;
    servingNow.value = response.data.serving_now;
    peopleAhead.value = response.data.people_ahead;
    estWait.value = response.data.estimated_wait_minutes;
    isPaused.value = response.data.is_paused;

    checkNotifications();
  } catch (err) {
    console.error('Failed to fetch ticket:', err);
  }
}

function shareWhatsApp() {
  const ticketUrl = window.location.href;
  const roomInfo = ticket.value.counter_name ? `\n*Assigned Room: ${ticket.value.counter_name}*` : '';
  const message = `Hi! Here is my ticket for *${clinicName.value}*:\n\n*Ticket Number: #${ticket.value.queue_number}*${roomInfo}\n\nYou can follow my real-time status here:\n${ticketUrl}`;
  const encodedMsg = encodeURIComponent(message);
  window.open(`https://wa.me/?text=${encodedMsg}`, '_blank');
}

function enableNotifications() {
  if (!("Notification" in window)) {
    alert("This browser does not support desktop notification");
    return;
  }

  Notification.requestPermission().then((permission) => {
    if (permission === "granted") {
      notificationsEnabled.value = true;
      new Notification("Notifications Active!", {
        body: `We will alert you when it's your turn at ${clinicName.value}.`,
        icon: '/favicon.ico'
      });
    }
  });
}

function checkNotifications() {
  if (!notificationsEnabled.value || Notification.permission !== "granted") return;

  // Proximity Notification (3 people ahead)
  if (ticket.value.status === 'waiting' && peopleAhead.value <= 3 && peopleAhead.value > 0 && !notifiedProximity.value) {
    new Notification("Your turn is close!", {
      body: `There are only ${peopleAhead.value} people ahead of you at ${clinicName.value}. Please stay close.`,
      vibrate: [200, 100, 200]
    });
    notifiedProximity.value = true;
  }

  // Your Turn Notification
  if (ticket.value.status === 'called' && !notifiedCalled.value) {
    new Notification("It's your turn!", {
      body: `Ticket #${ticket.value.queue_number} is being called at ${clinicName.value}. Please proceed to examination.`,
      tag: 'your-turn',
      requireInteraction: true
    });
    notifiedCalled.value = true;
  }
}

async function handleCancel() {
  if (!confirm('Are you sure you want to cancel your ticket?')) return;
  try {
    await api.post(`/q/ticket/${route.params.id}/cancel?token=${route.query.token}`);
    fetchTicketData();
  } catch (err) {
    alert('Failed to cancel ticket.');
  }
}

onMounted(() => {
  fetchTicketData();
  if (Notification.permission === "granted") {
    notificationsEnabled.value = true;
  }
});

watch(() => ticket.value.clinic_id, (clinicId) => {
  if (clinicId) {
    echo.channel(`clinic.${clinicId}.queue`)
      .listen('.queue.next.called', (e) => {
         servingNow.value = e.currentServingNumber;
         fetchTicketData();
      })
      .listen('.queue.status.updated', (e) => {
         if (e.queueId == ticket.value.id) {
           ticket.value.status = e.status;
         }
         fetchTicketData();
      })
      .listen('.queue.pause.toggled', (e) => {
         isPaused.value = e.isPaused;
      });
  }
});

onUnmounted(() => {
  if (ticket.value.clinic_id) {
    echo.leave(`clinic.${ticket.value.clinic_id}.queue`);
  }
});
</script>

<style scoped>
.rounded-\[2\.5rem\] { border-radius: 2.5rem; }
.rounded-4xl { border-radius: 2.5rem; }
</style>
