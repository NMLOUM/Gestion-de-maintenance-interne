# Guide de Compréhension du Frontend - Vue.js 3 + Inertia.js

## Table des matières
1. [Introduction à Vue.js 3](#introduction-à-vuejs-3)
2. [Qu'est-ce qu'Inertia.js ?](#quest-ce-quinertiajs)
3. [Structure des fichiers Vue](#structure-des-fichiers-vue)
4. [Composition API (script setup)](#composition-api-script-setup)
5. [Les Pages principales](#les-pages-principales)
6. [Les Composants réutilisables](#les-composants-réutilisables)
7. [Communication Frontend ↔ Backend](#communication-frontend--backend)
8. [Réactivité et État](#réactivité-et-état)
9. [Exemples pratiques](#exemples-pratiques)

---

## Introduction à Vue.js 3

### C'est quoi Vue.js ?

Vue.js est un **framework JavaScript** pour créer des interfaces utilisateur interactives. Pensez-y comme un outil qui rend votre page web **réactive** : quand les données changent, la page se met à jour automatiquement.

### Exemple simple

```vue
<script setup>
import { ref } from 'vue'

// Créer une variable réactive
const count = ref(0)

// Fonction pour incrémenter
const increment = () => {
  count.value++
}
</script>

<template>
  <div>
    <p>Vous avez cliqué {{ count }} fois</p>
    <button @click="increment">Cliquer</button>
  </div>
</template>
```

**Résultat :** À chaque clic, le nombre s'incrémente automatiquement dans la page !

---

## Qu'est-ce qu'Inertia.js ?

### Le Problème

Normalement, avec Laravel :
- **Option 1** : Blade templates (pas interactif, recharge toute la page)
- **Option 2** : API REST + Vue séparé (complexe, beaucoup de code)

### La Solution : Inertia.js

Inertia.js est un **pont** entre Laravel et Vue.js :
- Le backend (Laravel) envoie des données
- Le frontend (Vue) les affiche
- **Pas besoin d'API REST !**
- **Pas de rechargement de page !**

### Comment ça marche ?

```
BACKEND (Laravel)                    FRONTEND (Vue.js)
┌─────────────────┐                 ┌──────────────────┐
│ Controller      │   JSON via      │ Page Vue         │
│                 │  ─────────────> │                  │
│ return Inertia  │   Inertia.js    │ Affiche les      │
│ ::render(...)   │                 │ données          │
└─────────────────┘                 └──────────────────┘
```

**Exemple concret :**

```php
// Backend (TicketController.php)
public function index()
{
    return Inertia::render('Tickets/Index', [
        'tickets' => Ticket::all()
    ]);
}
```

```vue
<!-- Frontend (Tickets/Index.vue) -->
<script setup>
// Les données arrivent automatiquement dans props
defineProps({
  tickets: Array
})
</script>

<template>
  <div v-for="ticket in tickets" :key="ticket.id">
    {{ ticket.title }}
  </div>
</template>
```

---

## Structure des fichiers Vue

### Organisation typique

```
resources/js/
├── app.js                 # Point d'entrée de l'application
├── Pages/                 # Pages complètes (routes)
│   ├── Dashboard/
│   │   ├── Employe.vue   # Page dashboard employé
│   │   ├── Technicien.vue
│   │   └── ResponsableIt.vue
│   ├── Tickets/
│   │   ├── Index.vue     # Liste des tickets
│   │   ├── Create.vue    # Créer un ticket
│   │   └── Show.vue      # Détails d'un ticket
│   └── Auth/
│       └── Login.vue
├── Components/            # Composants réutilisables
│   ├── Tickets/
│   │   ├── StatusBadge.vue
│   │   └── PriorityBadge.vue
│   └── UI/
│       ├── Modal.vue
│       └── Button.vue
└── Layouts/              # Structures de page
    └── AuthenticatedLayout.vue
```

---

## Composition API (script setup)

### Les bases de `<script setup>`

C'est la syntaxe moderne de Vue.js 3. Tout ce qui est déclaré dans `<script setup>` est automatiquement disponible dans le template.

### 1. **Variables réactives avec `ref`**

```vue
<script setup>
import { ref } from 'vue'

// Créer une variable réactive
const message = ref('Bonjour')
const count = ref(0)

// Modifier la valeur : on utilise .value
const changeMessage = () => {
  message.value = 'Au revoir'
  count.value++
}
</script>

<template>
  <!-- Dans le template, pas besoin de .value -->
  <p>{{ message }}</p>
  <p>Count: {{ count }}</p>
  <button @click="changeMessage">Changer</button>
</template>
```

**Points clés :**
- `ref()` crée une variable réactive
- Dans `<script>` : utiliser `.value`
- Dans `<template>` : pas besoin de `.value`

### 2. **Objets réactifs avec `reactive`**

```vue
<script setup>
import { reactive } from 'vue'

// Pour des objets complexes
const form = reactive({
  email: '',
  password: '',
  remember: false
})

const submit = () => {
  console.log(form.email, form.password)
}
</script>

<template>
  <input v-model="form.email" type="email" />
  <input v-model="form.password" type="password" />
  <button @click="submit">Connexion</button>
</template>
```

### 3. **Valeurs calculées avec `computed`**

Les `computed` se recalculent automatiquement quand leurs dépendances changent.

```vue
<script setup>
import { ref, computed } from 'vue'

const tickets = ref([
  { id: 1, status: 'pending' },
  { id: 2, status: 'resolved' },
  { id: 3, status: 'pending' }
])

// Se recalcule automatiquement quand tickets change
const pendingTickets = computed(() => {
  return tickets.value.filter(t => t.status === 'pending')
})

const pendingCount = computed(() => {
  return pendingTickets.value.length
})
</script>

<template>
  <p>Tickets en attente : {{ pendingCount }}</p>
  <div v-for="ticket in pendingTickets" :key="ticket.id">
    Ticket #{{ ticket.id }}
  </div>
</template>
```

### 4. **Props (recevoir des données)**

```vue
<script setup>
// Recevoir des données du backend ou d'un composant parent
const props = defineProps({
  ticket: Object,
  users: Array,
  canEdit: Boolean
})

// Utiliser les props
console.log(props.ticket.title)
</script>

<template>
  <h1>{{ ticket.title }}</h1>
  <div v-if="canEdit">
    <button>Modifier</button>
  </div>
</template>
```

### 5. **Lifecycle hooks (cycle de vie)**

```vue
<script setup>
import { onMounted, onUnmounted } from 'vue'

// Quand le composant est affiché
onMounted(() => {
  console.log('Composant affiché !')
  // Charger des données, démarrer un timer, etc.
})

// Quand le composant est supprimé
onUnmounted(() => {
  console.log('Composant supprimé !')
  // Nettoyer les timers, etc.
})
</script>
```

---

## Les Pages principales

### 1. **Tickets/Index.vue** (Liste des tickets)

```vue
<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/Tickets/StatusBadge.vue'

// Props envoyées par le backend
const props = defineProps({
  tickets: Array,
  filters: Object
})

// État local pour les filtres
const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')

// Fonction pour filtrer
const filter = () => {
  router.get(route('tickets.index'), {
    search: search.value,
    status: status.value
  }, {
    preserveState: true,  // Garde l'état de la page
    preserveScroll: true  // Garde la position de scroll
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto">
        <!-- Filtres -->
        <div class="mb-4 flex gap-4">
          <input
            v-model="search"
            @input="filter"
            type="text"
            placeholder="Rechercher..."
          />

          <select v-model="status" @change="filter">
            <option value="">Tous les statuts</option>
            <option value="pending">En attente</option>
            <option value="in_progress">En cours</option>
            <option value="resolved">Résolu</option>
          </select>
        </div>

        <!-- Liste des tickets -->
        <div class="grid gap-4">
          <div
            v-for="ticket in tickets"
            :key="ticket.id"
            class="bg-white p-6 rounded-lg shadow"
          >
            <h3>{{ ticket.title }}</h3>
            <StatusBadge :status="ticket.status" />

            <button @click="router.visit(route('tickets.show', ticket.id))">
              Voir détails
            </button>
          </div>
        </div>

        <!-- Message si aucun ticket -->
        <div v-if="tickets.length === 0" class="text-center py-8">
          Aucun ticket trouvé
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
```

**Explications :**
- `defineProps` : reçoit les données du backend
- `ref()` : crée des variables réactives pour les filtres
- `v-model` : liaison bidirectionnelle (input ↔ variable)
- `@input` / `@change` : événements
- `v-for` : boucle pour afficher les tickets
- `v-if` : condition pour afficher ou cacher
- `router.get()` : navigation avec Inertia (sans recharger la page)

### 2. **Tickets/Create.vue** (Créer un ticket)

```vue
<script setup>
import { ref, reactive } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

// Props du backend
const props = defineProps({
  categories: Array,
  services: Array
})

// Formulaire avec Inertia
const form = useForm({
  title: '',
  description: '',
  category_id: null,
  priority: 'medium',
  attachments: []
})

// Gestion des fichiers
const handleFileUpload = (event) => {
  form.attachments = Array.from(event.target.files)
}

// Soumettre le formulaire
const submit = () => {
  form.post(route('tickets.store'), {
    onSuccess: () => {
      // Succès : Inertia redirige automatiquement
      console.log('Ticket créé !')
    },
    onError: (errors) => {
      // Erreurs de validation
      console.log('Erreurs:', errors)
    }
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="max-w-2xl mx-auto py-12">
      <h1 class="text-2xl font-bold mb-6">Créer un ticket</h1>

      <form @submit.prevent="submit">
        <!-- Titre -->
        <div class="mb-4">
          <label>Titre</label>
          <input
            v-model="form.title"
            type="text"
            class="w-full"
            required
          />
          <!-- Afficher l'erreur si elle existe -->
          <p v-if="form.errors.title" class="text-red-600 text-sm mt-1">
            {{ form.errors.title }}
          </p>
        </div>

        <!-- Description -->
        <div class="mb-4">
          <label>Description</label>
          <textarea
            v-model="form.description"
            rows="5"
            class="w-full"
            required
          ></textarea>
          <p v-if="form.errors.description" class="text-red-600 text-sm mt-1">
            {{ form.errors.description }}
          </p>
        </div>

        <!-- Catégorie -->
        <div class="mb-4">
          <label>Catégorie</label>
          <select v-model="form.category_id" required>
            <option :value="null">Sélectionner...</option>
            <option
              v-for="category in categories"
              :key="category.id"
              :value="category.id"
            >
              {{ category.name }}
            </option>
          </select>
        </div>

        <!-- Priorité -->
        <div class="mb-4">
          <label>Priorité</label>
          <div class="flex gap-4">
            <label class="flex items-center">
              <input
                type="radio"
                v-model="form.priority"
                value="low"
              />
              <span class="ml-2">Basse</span>
            </label>
            <label class="flex items-center">
              <input
                type="radio"
                v-model="form.priority"
                value="medium"
              />
              <span class="ml-2">Moyenne</span>
            </label>
            <label class="flex items-center">
              <input
                type="radio"
                v-model="form.priority"
                value="high"
              />
              <span class="ml-2">Haute</span>
            </label>
            <label class="flex items-center">
              <input
                type="radio"
                v-model="form.priority"
                value="urgent"
              />
              <span class="ml-2">Urgente</span>
            </label>
          </div>
        </div>

        <!-- Pièces jointes -->
        <div class="mb-4">
          <label>Pièces jointes</label>
          <input
            type="file"
            @change="handleFileUpload"
            multiple
          />
        </div>

        <!-- Boutons -->
        <div class="flex gap-4">
          <button
            type="submit"
            :disabled="form.processing"
            class="bg-indigo-600 text-white px-4 py-2 rounded"
          >
            {{ form.processing ? 'Création...' : 'Créer le ticket' }}
          </button>

          <button
            type="button"
            @click="router.visit(route('tickets.index'))"
            class="bg-gray-300 px-4 py-2 rounded"
          >
            Annuler
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
```

**Explications :**
- `useForm()` : helper Inertia pour gérer les formulaires
- `form.post()` : envoie les données au backend
- `form.processing` : true pendant l'envoi
- `form.errors` : erreurs de validation du backend
- `@submit.prevent` : empêche le rechargement de la page
- `:disabled` : désactive le bouton pendant l'envoi

### 3. **Dashboard/ResponsableIt.vue** (Dashboard complexe)

```vue
<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/Tickets/StatusBadge.vue'

// Props du backend
const props = defineProps({
  stats: Object,
  ticketsByStatus: Object,
  ticketsByPriority: Object,
  unassignedTickets: Array,
  recentTickets: Array,
  technicians: Array
})

// État local
const showExportModal = ref(false)
const selectedTicket = ref(null)
const selectedTech = ref(null)

// Computed : statistiques calculées
const totalTickets = computed(() => {
  return props.stats.total || 0
})

const urgentTickets = computed(() => {
  return props.ticketsByPriority?.urgent || []
})

const pendingCount = computed(() => {
  return props.ticketsByStatus?.pending?.length || 0
})

// Fonction : assigner un ticket
const assignTicket = (ticketId, technicianId) => {
  router.post(route('tickets.assign', ticketId), {
    assigned_to: technicianId
  }, {
    preserveScroll: true,
    onSuccess: () => {
      // Message de succès
      alert('Ticket assigné !')
      selectedTicket.value = null
      selectedTech.value = null
    }
  })
}

// Auto-refresh toutes les 30 secondes
let refreshInterval = null

onMounted(() => {
  refreshInterval = setInterval(() => {
    router.reload({ only: ['stats', 'recentTickets'] })
  }, 30000) // 30 secondes
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
})
</script>

<template>
  <AuthenticatedLayout>
    <div class="py-12">
      <!-- Statistiques en haut -->
      <div class="grid grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-lg shadow">
          <p class="text-gray-600">Total</p>
          <p class="text-3xl font-bold">{{ totalTickets }}</p>
        </div>

        <div class="bg-yellow-50 p-6 rounded-lg shadow">
          <p class="text-gray-600">En attente</p>
          <p class="text-3xl font-bold text-yellow-600">{{ pendingCount }}</p>
        </div>

        <div class="bg-red-50 p-6 rounded-lg shadow">
          <p class="text-gray-600">Urgents</p>
          <p class="text-3xl font-bold text-red-600">
            {{ urgentTickets.length }}
          </p>
        </div>

        <div class="bg-green-50 p-6 rounded-lg shadow">
          <p class="text-gray-600">Résolus</p>
          <p class="text-3xl font-bold text-green-600">
            {{ stats.resolved }}
          </p>
        </div>
      </div>

      <!-- Tickets non assignés -->
      <div class="bg-white p-6 rounded-lg shadow mb-8">
        <h2 class="text-xl font-bold mb-4">
          Tickets non assignés ({{ unassignedTickets.length }})
        </h2>

        <div class="space-y-4">
          <div
            v-for="ticket in unassignedTickets"
            :key="ticket.id"
            class="border rounded p-4 flex justify-between items-center"
          >
            <div>
              <h3 class="font-medium">{{ ticket.title }}</h3>
              <p class="text-sm text-gray-600">
                Par {{ ticket.requester.name }}
              </p>
              <StatusBadge :status="ticket.status" />
            </div>

            <div>
              <select
                v-model="selectedTech"
                class="mr-2"
              >
                <option :value="null">Choisir un technicien</option>
                <option
                  v-for="tech in technicians"
                  :key="tech.id"
                  :value="tech.id"
                >
                  {{ tech.name }}
                </option>
              </select>

              <button
                @click="assignTicket(ticket.id, selectedTech)"
                :disabled="!selectedTech"
                class="bg-indigo-600 text-white px-4 py-2 rounded disabled:opacity-50"
              >
                Assigner
              </button>
            </div>
          </div>

          <p v-if="unassignedTickets.length === 0" class="text-center text-gray-500">
            Aucun ticket non assigné
          </p>
        </div>
      </div>

      <!-- Bouton export -->
      <button
        @click="showExportModal = true"
        class="bg-green-600 text-white px-6 py-3 rounded"
      >
        Exporter les rapports
      </button>

      <!-- Modal d'export -->
      <div
        v-if="showExportModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center"
        @click.self="showExportModal = false"
      >
        <div class="bg-white p-8 rounded-lg max-w-md w-full">
          <h3 class="text-xl font-bold mb-4">Exporter un rapport</h3>

          <!-- Contenu du modal -->
          <div class="space-y-4">
            <button class="w-full bg-indigo-600 text-white py-2 rounded">
              Rapport général
            </button>
            <button class="w-full bg-indigo-600 text-white py-2 rounded">
              Rapport SLA
            </button>
          </div>

          <button
            @click="showExportModal = false"
            class="mt-4 w-full bg-gray-300 py-2 rounded"
          >
            Fermer
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
```

---

## Les Composants réutilisables

### 1. **StatusBadge.vue** (Badge de statut)

```vue
<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: String
})

// Couleur selon le statut
const badgeClass = computed(() => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    resolved: 'bg-green-100 text-green-800',
    validated: 'bg-purple-100 text-purple-800',
    closed: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[props.status] || 'bg-gray-100 text-gray-800'
})

// Texte selon le statut
const statusText = computed(() => {
  const texts = {
    pending: 'En attente',
    in_progress: 'En cours',
    resolved: 'Résolu',
    validated: 'Validé',
    closed: 'Fermé',
    cancelled: 'Annulé'
  }
  return texts[props.status] || props.status
})
</script>

<template>
  <span
    :class="badgeClass"
    class="inline-block px-3 py-1 rounded-full text-sm font-medium"
  >
    {{ statusText }}
  </span>
</template>
```

**Utilisation :**
```vue
<StatusBadge status="pending" />
<StatusBadge :status="ticket.status" />
```

### 2. **AssignTicketModal.vue** (Modal d'assignation)

```vue
<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const props = defineProps({
  ticketId: Number,
  technicians: Array,
  show: Boolean
})

const emit = defineEmits(['close'])

const selectedTech = ref(null)

const assignTicket = () => {
  if (!selectedTech.value) {
    Swal.fire({
      icon: 'error',
      title: 'Erreur',
      text: 'Veuillez sélectionner un technicien'
    })
    return
  }

  router.post(route('tickets.assign', props.ticketId), {
    assigned_to: selectedTech.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: 'Ticket assigné !',
        text: 'Le technicien a été notifié',
        timer: 3000
      })
      emit('close')
      selectedTech.value = null
    }
  })
}
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click.self="emit('close')"
  >
    <div class="bg-white p-8 rounded-lg max-w-md w-full">
      <h3 class="text-xl font-bold mb-4">Assigner le ticket</h3>

      <select
        v-model="selectedTech"
        class="w-full mb-4 border rounded px-3 py-2"
      >
        <option :value="null">Choisir un technicien...</option>
        <option
          v-for="tech in technicians"
          :key="tech.id"
          :value="tech.id"
        >
          {{ tech.name }} - {{ tech.service?.name }}
        </option>
      </select>

      <div class="flex gap-4">
        <button
          @click="assignTicket"
          class="flex-1 bg-indigo-600 text-white py-2 rounded"
        >
          Assigner
        </button>
        <button
          @click="emit('close')"
          class="flex-1 bg-gray-300 py-2 rounded"
        >
          Annuler
        </button>
      </div>
    </div>
  </div>
</template>
```

**Utilisation dans une page :**
```vue
<script setup>
import { ref } from 'vue'
import AssignTicketModal from '@/Components/Tickets/AssignTicketModal.vue'

const showModal = ref(false)
const currentTicket = ref(null)

const openAssignModal = (ticket) => {
  currentTicket.value = ticket
  showModal.value = true
}
</script>

<template>
  <button @click="openAssignModal(ticket)">
    Assigner
  </button>

  <AssignTicketModal
    :show="showModal"
    :ticket-id="currentTicket?.id"
    :technicians="technicians"
    @close="showModal = false"
  />
</template>
```

---

## Communication Frontend ↔ Backend

### 1. **Recevoir des données (Props)**

```php
// Backend : TicketController.php
return Inertia::render('Tickets/Show', [
    'ticket' => $ticket,
    'comments' => $ticket->comments,
    'canEdit' => auth()->user()->can('edit', $ticket)
]);
```

```vue
<!-- Frontend : Tickets/Show.vue -->
<script setup>
const props = defineProps({
  ticket: Object,
  comments: Array,
  canEdit: Boolean
})
</script>

<template>
  <h1>{{ ticket.title }}</h1>

  <div v-if="canEdit">
    <button>Modifier</button>
  </div>

  <div v-for="comment in comments" :key="comment.id">
    {{ comment.content }}
  </div>
</template>
```

### 2. **Envoyer des données (router)**

#### GET (navigation simple)

```vue
<script setup>
import { router } from '@inertiajs/vue3'

const goToTicket = (id) => {
  router.get(route('tickets.show', id))
}

const filterTickets = (status) => {
  router.get(route('tickets.index'), { status: status })
}
</script>

<template>
  <button @click="goToTicket(5)">Voir ticket #5</button>
  <button @click="filterTickets('pending')">Tickets en attente</button>
</template>
```

#### POST (créer/modifier)

```vue
<script setup>
import { router, useForm } from '@inertiajs/vue3'

// Méthode 1 : router.post directement
const assignTicket = (ticketId, techId) => {
  router.post(route('tickets.assign', ticketId), {
    assigned_to: techId
  })
}

// Méthode 2 : useForm (recommandé pour les formulaires)
const form = useForm({
  title: '',
  description: ''
})

const createTicket = () => {
  form.post(route('tickets.store'))
}
</script>
```

### 3. **Options de navigation Inertia**

```vue
<script setup>
import { router } from '@inertiajs/vue3'

const updateTicket = (id, data) => {
  router.post(route('tickets.update', id), data, {
    // Garde l'état de la page (pas de rechargement complet)
    preserveState: true,

    // Garde la position de scroll
    preserveScroll: true,

    // Recharge uniquement certaines props
    only: ['ticket', 'comments'],

    // Callback de succès
    onSuccess: () => {
      console.log('Ticket mis à jour !')
    },

    // Callback d'erreur
    onError: (errors) => {
      console.log('Erreurs:', errors)
    },

    // Callback de fin (succès ou erreur)
    onFinish: () => {
      console.log('Requête terminée')
    }
  })
}
</script>
```

---

## Réactivité et État

### 1. **ref vs reactive**

```vue
<script setup>
import { ref, reactive } from 'vue'

// ref : pour les valeurs simples
const count = ref(0)
const message = ref('Hello')
const isVisible = ref(true)

// Modification : utiliser .value
count.value++
message.value = 'Bye'

// reactive : pour les objets
const user = reactive({
  name: 'John',
  email: 'john@example.com',
  settings: {
    notifications: true
  }
})

// Modification : pas besoin de .value
user.name = 'Jane'
user.settings.notifications = false
</script>
```

### 2. **watch (surveiller les changements)**

```vue
<script setup>
import { ref, watch } from 'vue'

const search = ref('')
const results = ref([])

// Exécuter du code quand search change
watch(search, (newValue, oldValue) => {
  console.log(`Recherche changée de "${oldValue}" à "${newValue}"`)

  // Rechercher après 500ms (debounce)
  setTimeout(() => {
    // Faire la recherche
    router.get(route('tickets.index'), { search: newValue })
  }, 500)
})
</script>
```

### 3. **watchEffect (réaction automatique)**

```vue
<script setup>
import { ref, watchEffect } from 'vue'

const firstName = ref('John')
const lastName = ref('Doe')

// Se réexécute automatiquement quand firstName ou lastName change
watchEffect(() => {
  console.log(`Nom complet: ${firstName.value} ${lastName.value}`)
})
</script>
```

---

## Exemples pratiques

### Exemple 1 : Système de filtres

```vue
<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  tickets: Array
})

// Filtres
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedPriority = ref('')

// Tickets filtrés (côté frontend)
const filteredTickets = computed(() => {
  let result = props.tickets

  // Filtre par recherche
  if (searchQuery.value) {
    result = result.filter(ticket =>
      ticket.title.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }

  // Filtre par statut
  if (selectedStatus.value) {
    result = result.filter(ticket => ticket.status === selectedStatus.value)
  }

  // Filtre par priorité
  if (selectedPriority.value) {
    result = result.filter(ticket => ticket.priority === selectedPriority.value)
  }

  return result
})

// Réinitialiser les filtres
const resetFilters = () => {
  searchQuery.value = ''
  selectedStatus.value = ''
  selectedPriority.value = ''
}
</script>

<template>
  <div>
    <!-- Barre de filtres -->
    <div class="flex gap-4 mb-6">
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Rechercher..."
        class="flex-1"
      />

      <select v-model="selectedStatus">
        <option value="">Tous les statuts</option>
        <option value="pending">En attente</option>
        <option value="in_progress">En cours</option>
        <option value="resolved">Résolu</option>
      </select>

      <select v-model="selectedPriority">
        <option value="">Toutes les priorités</option>
        <option value="low">Basse</option>
        <option value="medium">Moyenne</option>
        <option value="high">Haute</option>
        <option value="urgent">Urgente</option>
      </select>

      <button @click="resetFilters" class="bg-gray-300 px-4 py-2 rounded">
        Réinitialiser
      </button>
    </div>

    <!-- Résultats -->
    <p class="mb-4 text-gray-600">
      {{ filteredTickets.length }} ticket(s) trouvé(s)
    </p>

    <div class="grid gap-4">
      <div
        v-for="ticket in filteredTickets"
        :key="ticket.id"
        class="bg-white p-4 rounded shadow"
      >
        {{ ticket.title }}
      </div>
    </div>
  </div>
</template>
```

### Exemple 2 : Pagination

```vue
<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  tickets: Array
})

const currentPage = ref(1)
const perPage = 10

// Total de pages
const totalPages = computed(() => {
  return Math.ceil(props.tickets.length / perPage)
})

// Tickets de la page actuelle
const paginatedTickets = computed(() => {
  const start = (currentPage.value - 1) * perPage
  const end = start + perPage
  return props.tickets.slice(start, end)
})

// Navigation
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

const nextPage = () => goToPage(currentPage.value + 1)
const prevPage = () => goToPage(currentPage.value - 1)
</script>

<template>
  <div>
    <!-- Liste des tickets -->
    <div class="grid gap-4 mb-6">
      <div
        v-for="ticket in paginatedTickets"
        :key="ticket.id"
        class="bg-white p-4 rounded shadow"
      >
        {{ ticket.title }}
      </div>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center items-center gap-2">
      <button
        @click="prevPage"
        :disabled="currentPage === 1"
        class="px-4 py-2 bg-gray-200 rounded disabled:opacity-50"
      >
        ← Précédent
      </button>

      <div class="flex gap-2">
        <button
          v-for="page in totalPages"
          :key="page"
          @click="goToPage(page)"
          :class="[
            'px-4 py-2 rounded',
            currentPage === page
              ? 'bg-indigo-600 text-white'
              : 'bg-gray-200'
          ]"
        >
          {{ page }}
        </button>
      </div>

      <button
        @click="nextPage"
        :disabled="currentPage === totalPages"
        class="px-4 py-2 bg-gray-200 rounded disabled:opacity-50"
      >
        Suivant →
      </button>
    </div>

    <p class="text-center mt-4 text-gray-600">
      Page {{ currentPage }} sur {{ totalPages }}
    </p>
  </div>
</template>
```

### Exemple 3 : Confirmation avant action

```vue
<script setup>
import { router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const deleteTicket = (ticketId) => {
  Swal.fire({
    title: 'Êtes-vous sûr ?',
    text: 'Cette action est irréversible !',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Oui, supprimer',
    cancelButtonText: 'Annuler'
  }).then((result) => {
    if (result.isConfirmed) {
      // Supprimer le ticket
      router.delete(route('tickets.destroy', ticketId), {
        onSuccess: () => {
          Swal.fire({
            icon: 'success',
            title: 'Supprimé !',
            text: 'Le ticket a été supprimé.',
            timer: 2000
          })
        }
      })
    }
  })
}
</script>

<template>
  <button
    @click="deleteTicket(ticket.id)"
    class="bg-red-600 text-white px-4 py-2 rounded"
  >
    Supprimer
  </button>
</template>
```

---

## Concepts clés à retenir

### 1. **Directives Vue.js**

| Directive | Utilisation | Exemple |
|-----------|-------------|---------|
| `v-if` | Afficher conditionnellement | `<div v-if="isVisible">...</div>` |
| `v-else` | Alternative à v-if | `<div v-else>...</div>` |
| `v-show` | Cacher avec CSS (display: none) | `<div v-show="isVisible">...</div>` |
| `v-for` | Boucle | `<div v-for="item in items" :key="item.id">` |
| `v-model` | Liaison bidirectionnelle | `<input v-model="name" />` |
| `@click` | Événement click | `<button @click="submit">` |
| `@submit` | Événement submit | `<form @submit.prevent="submit">` |
| `:class` | Classes dynamiques | `:class="{ active: isActive }"` |
| `:style` | Styles dynamiques | `:style="{ color: textColor }"` |

### 2. **Différence v-if vs v-show**

```vue
<!-- v-if : retire complètement l'élément du DOM -->
<div v-if="isVisible">Je suis supprimé si false</div>

<!-- v-show : ajoute display: none -->
<div v-show="isVisible">Je reste dans le DOM</div>
```

**Quand utiliser quoi ?**
- `v-if` : Quand l'élément change rarement
- `v-show` : Quand l'élément change souvent (meilleure performance)

### 3. **Event modifiers**

```vue
<!-- Empêcher le comportement par défaut -->
<form @submit.prevent="submit">

<!-- Stopper la propagation -->
<div @click.stop="handleClick">

<!-- Événement une seule fois -->
<button @click.once="init">

<!-- Touche Enter uniquement -->
<input @keyup.enter="search">

<!-- Touche Escape -->
<input @keyup.esc="cancel">
```

---

## Ressources pour continuer

1. **Documentation officielle Vue.js** : https://vuejs.org/
2. **Documentation Inertia.js** : https://inertiajs.com/
3. **Tailwind CSS (pour le style)** : https://tailwindcss.com/

---

**Questions fréquentes :**

**Q: Pourquoi `.value` avec `ref()` ?**
R: C'est comme ça que Vue.js track les changements. Dans le template, Vue fait `.value` automatiquement.

**Q: Quand utiliser `ref` vs `reactive` ?**
R: `ref` pour les valeurs simples (string, number, boolean), `reactive` pour les objets complexes.

**Q: C'est quoi `:key` dans `v-for` ?**
R: C'est un identifiant unique pour que Vue puisse tracker chaque élément. Toujours utiliser l'ID de l'objet.

**Q: Pourquoi `router.post` au lieu de `fetch()` ?**
R: Inertia gère automatiquement la navigation, les erreurs, et met à jour la page sans recharger.
