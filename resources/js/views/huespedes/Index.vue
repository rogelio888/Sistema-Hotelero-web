<!-- resources/js/views/huespedes/Index.vue -->

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-3xl font-bold text-gray-800">Huéspedes</h1>
        <p class="text-gray-600 mt-1">Administra todos los huéspedes registrados</p>
      </div>
      <Button
        v-if="authStore.hasPermission('crear_huespedes')"
        @click="$router.push('/huespedes/crear')"
      >
        ➕ Nuevo Huésped
      </Button>
    </div>

    <!-- Tabla -->
    <Table
      :columns="columns"
      :data="huespedesStore.huespedes"
      :loading="huespedesStore.loading"
    >
      <template #cell-nombre="{ item }">
        {{ item.nombre }} {{ item.apellido }}
      </template>

      <template #cell-estado="{ item }">
        <span :class="[
          'px-3 py-1 rounded-full text-xs font-semibold',
          item.estado === 'ACTIVO' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
        ]">
          {{ item.estado }}
        </span>
      </template>

      <template #actions="{ item }">
        <div class="flex items-center space-x-2">
          <button
            v-if="authStore.hasPermission('editar_huespedes')"
            @click="editarHuesped(item.id)"
            class="text-yellow-600 hover:text-yellow-800"
            title="Editar"
          >
            ✏️
          </button>
          <button
            v-if="authStore.hasPermission('eliminar_huespedes')"
            @click="confirmarEliminar(item)"
            class="text-red-600 hover:text-red-800"
            title="Eliminar"
          >
            🗑️
          </button>
        </div>
      </template>
    </Table>

    <!-- Modal confirmar eliminar -->
    <Modal :show="modalEliminar" @close="modalEliminar = false" title="Confirmar eliminación">
      <p class="text-gray-700">
        ¿Estás seguro de inactivar al huésped <strong>{{ huespedSeleccionado?.nombre }} {{ huespedSeleccionado?.apellido }}</strong>?
      </p>
      
      <template #footer>
        <Button variant="secondary" @click="modalEliminar = false">Cancelar</Button>
        <Button variant="danger" @click="eliminarHuesped" :loading="eliminando">Confirmar</Button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';
import { useHuespedesStore } from '../../stores/huespedes';
import Table from '../../components/Table.vue';
import Button from '../../components/Button.vue';
import Modal from '../../components/Modal.vue';

const router = useRouter();
const authStore = useAuthStore();
const huespedesStore = useHuespedesStore();

const modalEliminar = ref(false);
const huespedSeleccionado = ref(null);
const eliminando = ref(false);

const columns = [
  { key: 'id', label: 'ID' },
  { key: 'nombre', label: 'Nombre Completo' },
  { key: 'ci', label: 'CI' },
  { key: 'telefono', label: 'Teléfono' },
  { key: 'email', label: 'Email' },
  { key: 'estado', label: 'Estado' },
];

const editarHuesped = (id) => {
  router.push(`/huespedes/${id}/editar`);
};

const confirmarEliminar = (huesped) => {
  huespedSeleccionado.value = huesped;
  modalEliminar.value = true;
};

const eliminarHuesped = async () => {
  eliminando.value = true;
  try {
    await huespedesStore.deleteHuesped(huespedSeleccionado.value.id);
    modalEliminar.value = false;
    huespedSeleccionado.value = null;
  } catch (error) {
    alert('Error al eliminar huésped');
  } finally {
    eliminando.value = false;
  }
};

onMounted(() => {
  huespedesStore.fetchHuespedes();
});
</script>