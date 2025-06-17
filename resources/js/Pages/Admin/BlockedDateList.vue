<template>
    <AppLayout title="Заблокированные даты">

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Заблокированные даты
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Заголовок -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Периоды недоступности номеров для бронирования</h1>
                </div>
                <!-- Фильтры и кнопка создания -->
                <div class="controls">
                    <div class="filters">
                        <select v-model="filters.room_id" class="filter-select" @change="loadData">
                            <option value="">Все номера</option>
                            <option
                                v-for="room in rooms"
                                :key="room.id"
                                :value="room.id"
                            >
                                {{ room.name }}
                            </option>
                        </select>

                        <flat-pickr
                            v-model="filters.date_range"
                            :config="dateConfig"
                            placeholder="Выберите период"
                            class="date-picker"
                            @on-change="loadData"
                        />
                    </div>

                    <Link :href="route('blocked_dates.create')"
                          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                        + Добавить период
                    </Link>
                </div>

                <!-- Таблица периодов -->
                <div class="bg-white rounded-lg shadow overflow-x-auto">
                    <table>
                        <thead>
                        <tr>
                            <th>Номер</th>
                            <th>Период</th>
                            <th>Причина</th>
                            <th>Последнее обновление</th>
                            <th>Действия</th>
                        </tr>
                        </thead>

                        <tbody v-if="loading">
                        <tr>
                            <td colspan="6" class="loading">
                                <div class="loader"></div>
                            </td>
                        </tr>
                        </tbody>

                        <tbody v-else>
                        <tr v-for="blocked_date in blocked_dates" :key="blocked_date.id">
                            <td>{{ blocked_date.room.name }}</td>

                            <td>{{ formatDate(blocked_date.date_start) }} - {{ formatDate(blocked_date.date_end) }}</td>

                            <td>{{ blocked_date.reason }}</td>

                            <td>{{ formatDate(blocked_date.updated_at) }}</td>

                            <td class="px-6 py-4 whitespace-nowrap space-x-2">
                                <Link :href="route('blocked_dates.edit', blocked_date.id)" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-edit"></i>
                                </Link>
                                <button
                                    @click="deletePeriod(blocked_date.id)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                    <!-- Пагинация -->
                    <div class="pagination">
                        <button
                            v-for="page in totalPages"
                            :key="page"
                            :class="{ active: currentPage === page }"
                            @click="changePage(page)"
                        >
                            {{ page }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import {ref, onMounted} from 'vue'
import flatPickr from 'vue-flatpickr-component'
import {Russian} from 'flatpickr/dist/l10n/ru'
import AppLayout from "@/Layouts/AppLayout.vue";
import {route} from "ziggy-js";
import flatpickr from "flatpickr";
import {Link} from "@inertiajs/vue3";

flatpickr.localize(Russian)

// Данные
const blocked_dates = ref([])
const rooms = ref([])
const filters = ref({
    room_id: '',
    date_range: ''
})
const currentPage = ref(1)
const totalPages = ref(1)
const loading = ref(false)

// Конфигурация календаря
const dateConfig = {
    mode: 'range',
    dateFormat: 'd-m-Y',
    locale: Russian
}

// Загрузка данных
const loadData = async () => {
    try {
        loading.value = true
        const params = {
            page: currentPage.value,
            room_id: filters.value.room_id,
            // Разбиваем диапазон дат на start_date и end_date
            ...(filters.value.date_range && {
                start_date: filters.value.date_range.split(" — ")[0],
                end_date: filters.value.date_range.split(" — ")[1]
            })
        }

        const response = await axios.get(route('blocked_dates.index'), {params})
        blocked_dates.value = response.data.data
        totalPages.value = response.data.last_page
    } finally {
        loading.value = false
    }
}

// Загрузка номеров
const loadRooms = async () => {
    try {
        const response = await axios.get(route('rooms.index'))
        rooms.value = response.data.data
    } catch (error) {
        console.error('Ошибка загрузки номеров:', error)
    }
}

// Удаление цены
const deletePeriod = async (blocked_date) => {
    if (!confirm('Удалить этот период?')) return
    try {
        await axios.delete(route('blocked_dates.destroy', blocked_date.id))
        await loadData()
    } catch (error) {
        console.error('Ошибка удаления:', error)
    }
}

// Форматирование даты
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU')
}

// Смена страницы
const changePage = (page) => {
    currentPage.value = page;
}

// Инициализация
onMounted(async () => {
    await loadRooms()
    await loadData()
})
</script>

<style scoped>

label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

input {
    width: 25%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

select {
    width: 25%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.controls {
    display: flex;
    justify-content: space-between;
    margin-bottom: 2rem;
    gap: 1rem;
    flex-wrap: wrap;
}

.filters {
    display: flex;
    gap: 1rem;
    flex-grow: 1;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #eee;
    min-width: 150px;
}

th {
    background: #f8f9fa;
    cursor: pointer;
    user-select: none;
}

.pagination {
    display: flex;
    gap: 0.5rem;
    padding: 1rem;
    justify-content: center;
}

.pagination button {
    padding: 0.5rem 1rem;
    border-radius: 4px;
}

.pagination button.active {
    background: #2196f3;
    color: white;
}

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
