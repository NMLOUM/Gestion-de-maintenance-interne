<template>
  <AuthenticatedLayout>
    <Head :title="`Modifier: ${user.name}`" />

    <div class="py-6">
      <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="flex justify-between items-center mb-6">
              <h2 class="text-2xl font-semibold text-gray-900">
                Modifier l'utilisateur
              </h2>
              <Link
                :href="route('users.show', user.id)"
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium"
              >
                Annuler
              </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
              <!-- Nom -->
              <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                  Nom complet *
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  :class="{ 'border-red-500': form.errors.name }"
                  placeholder="Prénom Nom"
                />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
              </div>

              <!-- Email -->
              <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                  Email *
                </label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  :class="{ 'border-red-500': form.errors.email }"
                  placeholder="utilisateur@exemple.com"
                />
                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
              </div>

              <!-- Téléphone -->
              <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                  Téléphone
                </label>
                <input
                  id="phone"
                  v-model="form.phone"
                  type="tel"
                  class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  placeholder="+33 1 23 45 67 89"
                />
              </div>

              <!-- Service et Rôle -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label for="service_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Service *
                  </label>
                  <select
                    id="service_id"
                    v-model="form.service_id"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    :class="{ 'border-red-500': form.errors.service_id }"
                  >
                    <option value="">Sélectionner un service</option>
                    <option v-for="service in services" :key="service.id" :value="service.id">
                      {{ service.name }}
                    </option>
                  </select>
                  <p v-if="form.errors.service_id" class="mt-1 text-sm text-red-600">{{ form.errors.service_id }}</p>
                </div>

                <div>
                  <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                    Rôle *
                  </label>
                  <select
                    id="role"
                    v-model="form.role"
                    class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    :class="{ 'border-red-500': form.errors.role }"
                  >
                    <option v-for="role in roleOptions" :key="role.value" :value="role.value">
                      {{ role.label }}
                    </option>
                  </select>
                  <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
                </div>
              </div>

              <!-- Statut -->
              <div>
                <label class="flex items-center">
                  <input
                    v-model="form.is_active"
                    type="checkbox"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                  />
                  <span class="ml-2 text-sm text-gray-700">Utilisateur actif</span>
                </label>
              </div>

              <!-- Nouveau mot de passe (optionnel) -->
              <div class="border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Changer le mot de passe (optionnel)</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                      Nouveau mot de passe
                    </label>
                    <input
                      id="password"
                      v-model="form.password"
                      type="password"
                      class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      :class="{ 'border-red-500': form.errors.password }"
                      placeholder="Laisser vide pour ne pas changer"
                    />
                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                  </div>

                  <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                      Confirmer le mot de passe
                    </label>
                    <input
                      id="password_confirmation"
                      v-model="form.password_confirmation"
                      type="password"
                      class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                      placeholder="Confirmer le nouveau mot de passe"
                    />
                  </div>
                </div>
              </div>

              <!-- Boutons d'action -->
              <div class="flex items-center justify-between pt-4 border-t">
                <Link
                  :href="route('users.show', user.id)"
                  class="text-gray-700 bg-gray-200 hover:bg-gray-300 px-6 py-2 rounded-md text-sm font-medium"
                >
                  Annuler
                </Link>
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium disabled:opacity-50"
                >
                  {{ form.processing ? 'Enregistrement...' : 'Enregistrer' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  user: Object,
  services: Array,
  roleOptions: Array
})

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  phone: props.user.phone,
  service_id: props.user.service_id,
  role: props.user.role,
  is_active: props.user.is_active,
  password: '',
  password_confirmation: ''
})

const submit = () => {
  form.put(route('users.update', props.user.id), {
    preserveScroll: true,
    onSuccess: () => form.reset('password', 'password_confirmation')
  })
}
</script>
