<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

interface PollOption {
  id: number;
  poll_id: number;
  text: string;
}

interface Poll {
  id: number;
  title: string;
  description: string;
  options: PollOption[];
}

interface Document {
  id: number;
  title: string;
  description: string;
}

const props = defineProps<{
  activePolls: Poll[];
  pendingDocuments: Document[];
}>();

const votingPollId = ref<number | null>(null);

const form = useForm({
  poll_option_id: null as number | null,
  unit_id: 1, // To be securely inferred from Resident's context drop-down in production implementation
});

const submitVote = (poll: Poll) => {
  if (!form.poll_option_id) return;
  
  votingPollId.value = poll.id;
  
  form.post(route('tenant.resident.governance.polls.vote', poll.id), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      votingPollId.value = null;
    },
    onError: () => {
      votingPollId.value = null;
    }
  });
};
</script>

<template>
  <Head title="Centro de Participación" />

  <AppLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Centro de Participación</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <div class="mb-8">
          <p class="mt-2 text-sm text-gray-600">
            Revisa y responde a las votaciones y documentos pendientes de tu comunidad.
          </p>
        </div>

        <div v-if="activePolls.length > 0" class="mb-10">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">Votaciones Pendientes</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div 
              v-for="poll in activePolls" 
              :key="poll.id" 
              class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 flex flex-col hover:shadow-md transition-shadow"
            >
              <div class="p-6 flex-1">
                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ poll.title }}</h3>
                <p class="text-sm text-gray-500 mb-4">{{ poll.description }}</p>
                
                <form @submit.prevent="submitVote(poll)" class="mt-4 border-t border-gray-100 pt-4">
                  <div class="space-y-3">
                    <div v-for="option in poll.options" :key="option.id" class="flex items-center">
                      <input 
                        :id="`option-${option.id}`" 
                        type="radio" 
                        :value="option.id"
                        v-model="form.poll_option_id"
                        class="h-4 w-4 text-emerald-600 border-gray-300 focus:ring-emerald-500"
                      >
                      <label :for="`option-${option.id}`" class="ml-3 block text-sm font-medium text-gray-700">
                        {{ option.text }}
                      </label>
                    </div>
                  </div>

                  <div class="mt-6">
                    <button 
                      type="submit" 
                      :disabled="form.processing || votingPollId === poll.id || !form.poll_option_id"
                      class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50"
                    >
                      <span v-if="votingPollId === poll.id">Enviando Voto Seguro...</span>
                      <span v-else>Votar</span>
                    </button>
                    <p class="mt-2 text-xs text-center text-gray-500 font-medium">
                      Tu voto es irreversible y será registrado para auditoría.
                    </p>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <div v-if="pendingDocuments.length > 0">
          <h2 class="text-lg font-semibold text-gray-800 mb-4">Documentos por Firmar</h2>
          <div class="space-y-4">
            <div 
              v-for="doc in pendingDocuments" 
              :key="doc.id" 
              class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 flex justify-between items-center hover:shadow-md transition-shadow p-6"
            >
              <div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">{{ doc.title }}</h3>
                <p class="text-sm text-gray-500">{{ doc.description }}</p>
              </div>
              <button 
                type="button" 
                class="ml-4 inline-flex items-center px-4 py-2 border border-emerald-600 shadow-sm text-sm font-medium rounded-md text-emerald-700 bg-white hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
              >
                Leer y Aceptar
              </button>
            </div>
          </div>
        </div>

        <div v-if="activePolls.length === 0 && pendingDocuments.length === 0" class="text-center py-20 bg-white rounded-xl shadow-sm border border-gray-100">
          <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="mt-4 text-base font-semibold text-gray-900">Estás al día</h3>
          <p class="mt-1 text-sm text-gray-500">No tienes votaciones ni firma de documentos pendientes en este momento.</p>
        </div>

      </div>
    </div>
  </AppLayout>
</template>
