<template>
    <AppLayout title="Форма цены">

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Цены
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Заголовок -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold">
                        {{ isEditMode ? 'Редактирование цены' : 'Добавление новой цены' }}
                    </h1>
                    <Link :href="route('prices.list')"
                          class="text-blue-600 hover:text-blue-800 text-sm">
                        ← Назад к списку
                    </Link>
                </div>

                <form @submit.prevent="handleSubmit" class="bg-white p-6 rounded-lg shadow-md">
                    <!-- Основные параметры -->
                    <div class="grid grid-cols-2 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label>Номер <span class="required">*</span></label>
                            <select v-model="room_id" @on-change="loadBasePrice(room_id)">
                                <option
                                    v-for="room in rooms"
                                    :key="room.id"
                                    :value="room.id"
                                >
                                    {{ room.name }}
                                </option>
                            </select>
                            <span v-if="errors.room_id" class="error">{{ errors.room_id }}</span>
                        </div>

                        <div>
                            <label>Базовая цена</label>
                            <input
                                type="number"
                                v-model.number="base_price"
                                readonly
                                disabled
                            >
                        </div>

                        <div class="date-range-picker">
                            <label>Даты действия <span class="required">*</span></label>
                            <flat-pickr
                                v-model="form.date_range"
                                :config="dateConfig"
                            />
                            <span v-if="errors.date_range" class="error">{{ errors.date_range }}</span>
                        </div>

                        <div>
                            <label>Новая цена за ночь <span class="required">*</span></label>
                            <input
                                type="number"
                                v-model.number="form.price"
                                min="0"
                                step="100"
                            >
                            <span v-if="errors.price" class="error">{{ errors.price }}</span>
                        </div>
                    </div>

                    <!-- Кнопки -->
                    <div class="flex justify-end space-x-4 mt-8">
                        <Link :href="route('prices.list')"
                              class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                            Отмена
                        </Link>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ isSubmitting ? 'Сохранение...' : 'Сохранить' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import {ref, onMounted, computed, watch, watchEffect} from 'vue'
import {useRouter} from 'vue-router'
import {Russian} from 'flatpickr/dist/l10n/ru'
import AppLayout from "../../Layouts/AppLayout.vue";
import {route} from "ziggy-js";
import {Link} from "@inertiajs/vue3";
import FlatPickr from 'vue-flatpickr-component'

const router = useRouter()

const isEditMode = computed(() => !!route().params.price)

// Данные формы
const form = ref({
    price: 0,
    date_range: []
})

const base_price = ref(null)
const room_id = ref(null)

const rooms = ref([])
const isSubmitting = ref(false)

// Конфигурация календаря
const dateConfig = ref({
    mode: 'range',
    dateFormat: 'd-m-Y',
    locale: Russian,
    allowInput: true
})

const price = route().params.price

// Загрузка данных
const loadInitialData = async () => {
    try {
        const [roomsRes] = await Promise.all([
            axios.get(route('rooms.index')),
            isEditMode.value && axios.get(route('prices.show', price))
        ])

        rooms.value = roomsRes.data.data

        if (isEditMode.value) {
            const {data} = await axios.get(route('prices.show', price))
            room_id.value = data.data.room_id;
            form.value = {
                ...data.data,
                date_range: [data.data.start_date, data.data.end_date],
            }
        }
    } catch (error) {
        console.error('Ошибка загрузки:', error)
    }
}

const loadBasePrice = async (roomId) => {
    try {
        const {data} = await axios.get(route('rooms.show', roomId))
        base_price.value = data.data.base_price
    } catch (error) {
        console.error('Ошибка загрузки:', error)
    }
}

// Ошибки валидации
const errors = ref({
    room_id: '',
    price: '',
    date_range: '',
})

// Валидация формы
const validateForm = () => {
    let isValid = true
    errors.value = {
        room_id: '',
        price: '',
        date_range: '',
    }

    if (!room_id.value) {
        errors.value.room_id = 'Выберите номер из списка!'
        isValid = false
    }

    if (form.value.price <= 0) {
        errors.value.price = 'Цена должна быть больше нуля!'
        isValid = false
    } else if (base_price.value === form.value.price) {
        errors.value.price = 'Новая цена не должна быть равна старой!'
        isValid = false
    }

    if (!form.value.date_range.length) {
        errors.value.date_range = 'Выберите период действия цены!'
        isValid = false
    }

    return isValid
}
// Отправка формы
const handleSubmit = async () => {
    if (!validateForm()) return

    try {
        isSubmitting.value = true
        errors.value = {}

        var dates = form.value.date_range.toString().split(" — ");

        const payload = {
            room_id: room_id.value,
            ...form.value,
            start_date: dates[0],
            end_date: dates[1]
        }

        const url = isEditMode.value
            ? route('prices.update', price)
            : route('prices.store')

        const method = isEditMode.value ? 'put' : 'post'

        await axios[method](url, payload)
        window.location.href = route('prices.list')

    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        } else {
            console.log(error);
            alert('Ошибка сохранения')
        }
    } finally {
        isSubmitting.value = false
    }
}

watch([room_id], async () => {
    await loadBasePrice(room_id.value)
})

// Инициализация
onMounted(loadInitialData)
</script>

<style scoped>

label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

input, select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.error {
    color: #e53935;
    font-size: 0.8rem;
}

.required {
    color: #e53935;
}
</style>
