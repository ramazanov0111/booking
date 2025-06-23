<script setup>

import {onMounted, ref} from "vue";
import {route} from "ziggy-js";
import flatPickr from 'vue-flatpickr-component'
import {Russian} from 'flatpickr/dist/l10n/ru'

const bookings = ref([])
const rooms = ref([])
const filters = ref({
    date_range: '',
})
const loading = ref(false)

// Загрузка данных
const loadBookings = async () => {
    try {
        loading.value = true

        const params = {
            is_not_canceled: true
        }

        const response = await axios.get(route('booking.index'), {params})
        bookings.value = response.data.data
    } finally {
        loading.value = false
    }
}

// Получение данных
const loadEnabledRooms = async () => {
    try {
        loading.value = true

        const params = {
            ...(filters.value.date_range && {
                start_date: filters.value.date_range.split(" — ")[0],
                end_date: filters.value.date_range.split(" — ")[1]
            })
        }

        const {data} = await axios.get(route('rooms.enabled'), {params})

        rooms.value = data
    } catch (error) {
        console.error('Ошибка загрузки номеров:', error)
    } finally {
        loading.value = false
    }
}

// Форматирование даты
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('ru-RU')
}

const statuses = ref({
    'confirmed': 'Подтверждено',
    'paid': 'Оплачено',
    'canceled': 'Отменено',
})

// Конфигурация календаря
const dateConfig = {
    mode: 'range',
    dateFormat: 'd-m-Y',
    locale: Russian
}

// Инициализация
onMounted(async () => {
    await loadBookings()
    await loadEnabledRooms()
})

</script>

<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Заголовок -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Список текущих постояльцев</h1>
            </div>
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table>
                    <thead>
                    <tr>
                        <th>Номер</th>
                        <th>Пользователь</th>
                        <th>Телефон</th>
                        <th>Период проживания</th>
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
                    <tr v-for="booking in bookings" :key="booking.id">
                        <td>{{ booking.room.name }}</td>
                        <td>{{ booking.user.name }} {{ booking.user.lastname }}</td>
                        <td>{{ booking.user.phone }}</td>
                        <td>{{ formatDate(booking.check_in) }} - {{ formatDate(booking.check_out) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Заголовок -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Доступные для заселения номера</h1>
            </div>
            <div class="controls">
                <div class="filters">
                    <flat-pickr
                        v-model="filters.date_range"
                        :config="dateConfig"
                        placeholder="Выберите период"
                        class="date-picker"
                        @on-change="loadEnabledRooms"
                    />
                </div>

            </div>
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table>
                    <thead>
                    <tr>
                        <th>Номер</th>
                        <th>Цена</th>
                        <th>Дата заселения</th>
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
                    <tr v-for="room in rooms" :key="room.id">
                        <td>{{ room.name }}</td>
                        <td>{{ !room.price ? room.base_price : room.price.price }}</td>
                        <td>{{ formatDate(room.firstDate) }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

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

