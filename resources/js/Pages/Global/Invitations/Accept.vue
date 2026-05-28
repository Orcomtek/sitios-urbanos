<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Invitación a {{ invitation.community_name }}
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Has sido invitado como {{ translatedRole }}
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <form @submit.prevent="submit" class="space-y-6">
          
          <div>
            <label class="block text-sm font-medium text-gray-700">
              Correo Electrónico
            </label>
            <div class="mt-1">
              <input type="email" disabled :value="invitation.email" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-500 sm:text-sm" />
            </div>
          </div>

          <!-- Si el usuario es nuevo, pedimos nombre -->
          <div v-if="!userExists">
            <label for="name" class="block text-sm font-medium text-gray-700">
              Nombre Completo
            </label>
            <div class="mt-1">
              <input id="name" v-model="form.name" type="text" required class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
            </div>
            <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
          </div>

          <!-- Si el usuario ya está logueado, no le pedimos contraseña, a menos que queramos forzar verificación. Pero en este flujo, si está logueado y es el mismo usuario, podríamos simplemente tener un botón de aceptar. 
               Sin embargo, si userExists y no está logueado, pedimos contraseña. 
               Si es nuevo, pedimos que cree una contraseña. -->
          <div v-if="!isLoggedIn || !userExists">
            <label for="password" class="block text-sm font-medium text-gray-700">
              {{ userExists ? 'Contraseña para verificar identidad' : 'Crea una contraseña' }}
            </label>
            <div class="mt-1">
              <input id="password" v-model="form.password" type="password" :required="!isLoggedIn || !userExists" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
            </div>
            <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">{{ form.errors.password }}</p>
          </div>

          <div v-if="isLoggedIn && userExists">
            <p class="text-sm text-gray-600 mb-4">
              Ya has iniciado sesión con este correo. Simplemente confirma la invitación.
            </p>
          </div>

          <div v-if="form.errors.token">
            <p class="text-sm text-red-600">{{ form.errors.token }}</p>
          </div>

          <div>
            <button type="submit" :disabled="form.processing" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
              {{ form.processing ? 'Procesando...' : (userExists ? 'Aceptar Invitación' : 'Registrarse y Aceptar') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
  invitation: {
    type: Object,
    required: true,
  },
  userExists: {
    type: Boolean,
    required: true,
  },
  isLoggedIn: {
    type: Boolean,
    required: true,
  },
});

const translatedRole = computed(() => {
  const roles = {
    'admin': 'Administrador',
    'resident': 'Residente',
    'guard': 'Guardia'
  };
  return roles[props.invitation.role] || props.invitation.role;
});

const form = useForm({
  token: props.invitation.token,
  name: '',
  password: '',
});

const submit = () => {
  form.post(route('invitations.accept.store'), {
    onSuccess: () => {
      // Redirect handled by backend
    },
  });
};
</script>
