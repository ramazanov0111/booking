<template>
    <AppLayout title="Rooms">

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Номера
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Заголовок и кнопка добавления -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Управление номерами</h1>
                    <Link :href="route('rooms.create')"
                          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        + Добавить номер
                    </Link>
                </div>

                <!-- Таблица номеров -->
                <div class="bg-white rounded-lg shadow overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Название</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Вместимость</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Цена</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Статус</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Действия</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Состояние загрузки -->
                        <tr v-if="loading">
                            <td colspan="5" class="px-6 py-4 text-center">
                                <div class="animate-pulse flex space-x-4">
                                    <div class="flex-1 space-y-4 py-1">
                                        <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <!-- Список номеров -->
                        <tr v-for="room in rooms" :key="room.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ room.name }}</div>
                                <div class="text-gray-500 text-sm mt-1">{{ room.description }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ room.capacity }} {{ pluralize(room.capacity, ['гость', 'гостя', 'гостей']) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ formatPrice(room.base_price) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
              <span
                  :class="room.is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
              >
                {{ room.is_available ? 'Доступен' : 'Недоступен' }}
              </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                <!--                        <router-link-->
                                <!--                            :to="`/admin/rooms/edit/${room.id}`"-->
                                <!--                            class="text-blue-600 hover:text-blue-900"-->
                                <!--                        >-->
                                <!--                            <i class="fas fa-edit"></i>-->
                                <!--                        </router-link>-->
                                <Link :href="route('proger.rooms.edit', room)" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </Link>
                                <button
                                    @click="deleteRoom(room.id)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <!-- Пагинация -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <div class="text-sm text-gray-700">
                                Показано с {{ meta.from }} по {{ meta.to }} из {{ meta.total }} номеров
                            </div>
                            <div class="flex space-x-2">
                                <button
                                    v-for="page in meta.last_page"
                                    :key="page"
                                    @click="changePage(page)"
                                    :class="page === meta.current_page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700'"
                                    class="px-3 py-1 rounded-md border border-gray-300 hover:bg-gray-50"
                                >
                                    {{ page }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import {ref, onMounted} from 'vue';
import {Link} from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Welcome from "@/Components/Welcome.vue";

const rooms = ref([])
const meta = ref({})
const loading = ref(true)

// Получение данных
const fetchRooms = async (page = 1) => {
    console.log(12312431);
    try {
        loading.value = true
        const {data} = await axios.get(`api/proger/rooms?page=${page}`)
        rooms.value = data.data
        meta.value = data.meta
    } catch (error) {
        console.error('Ошибка загрузки номеров:', error)
    } finally {
        loading.value = false
    }
}

// Удаление номера
const deleteRoom = async (id) => {
    if (confirm('Вы уверены, что хотите удалить номер?')) {
        try {
            await axios.delete(`api/proger/rooms/${room}`)
            rooms.value = rooms.value.filter(room => room.id !== id)
        } catch (error) {
            console.error('Ошибка удаления:', error)
        }
    }
}

// Форматирование цены
const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        maximumFractionDigits: 0
    }).format(price)
}

// Смена страницы
const changePage = (page) => {
    fetchRooms(page)
}

// Склонение числительных
const pluralize = (n, forms) => {
    return forms[n % 10 === 1 && n % 100 !== 11 ? 0 : n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20) ? 1 : 2]
}

onMounted(() => {
    fetchRooms()
})

</script>

<style scoped>
table {
    @apply min-w-full divide-y divide-gray-200;
}

th {
    @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider;
}

td {
    @apply px-6 py-4 whitespace-nowrap;
}

tr:hover td {
    @apply bg-gray-50;
}
</style>
