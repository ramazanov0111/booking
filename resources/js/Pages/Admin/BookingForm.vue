<template>
    <AppLayout title="Booking Form">

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Бронирования
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Заголовок -->
                <div class="mb-8">
                    <h1 class="text-2xl font-bold">
                        {{ isEditMode ? 'Редактирование бронирования' : 'Добавление нового бронирования' }}
                    </h1>
                    <Link :href="route('booking.list')"
                          class="text-blue-600 hover:text-blue-800 text-sm">
                        ← Назад к списку
                    </Link>
                </div>

                <form @submit.prevent="handleSubmit" class="bg-white p-6 rounded-lg shadow-md">
                    <!-- Основные параметры -->
                    <div class="grid grid-cols-2 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label>Пользователь <span class="required">*</span></label>
                            <select v-model="form.user_id">
                                <option
                                    v-for="user in users"
                                    :key="user.id"
                                    :value="user.id"
                                >
                                    {{ user.name }} {{ user.lastname }}
                                </option>
                            </select>
                            <span v-if="errors.user_id" class="error">{{ errors.user_id }}</span>
                        </div>
                        <div>
                            <label>Номер <span class="required">*</span></label>
                            <select v-model="form.room_id">
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
                        <div class="date-range-picker">
                            <label>Дата заезда <span class="required">*</span></label>
                            <VueDatePicker
                                v-model="checkInDate"
                                :disabled-dates="disabledDates"
                                :enable-time-picker="false"
                                format="dd-MM-yyy"
                                auto-apply
                                @on-change="loadPrice"
                            />
                            <span v-if="errors.checkInDate" class="error">{{ errors.checkInDate }}</span>
                        </div>
                        <div class="date-range-picker">
                            <label>Дата выезда <span class="required">*</span></label>
                            <VueDatePicker
                                v-model="checkOutDate"
                                :disabled-dates="disabledDates"
                                :enable-time-picker="false"
                                format="dd-MM-yyy"
                                auto-apply
                                @on-change="loadPrice"
                            />
                            <span v-if="errors.checkOutDate" class="error">{{ errors.checkOutDate }}</span>
                        </div>
                        <div>
                            <label>Статус <span class="required">*</span></label>
                            <select v-model="form.status" required>
                                <option
                                    v-for="status in statusList"
                                    :key="status.key"
                                    :value="status.key"
                                >
                                    {{ status.value }}
                                </option>
                            </select>
                            <span v-if="errors.room_id" class="error">{{ errors.room_id }}</span>
                        </div>
                        <div>
                            <label>Способ оплаты <span class="required">*</span></label>
                            <select v-model="form.payment_method" required>
                                <option
                                    v-for="paymentMethod in paymentMethods"
                                    :key="paymentMethod.key"
                                    :value="paymentMethod.key"
                                >
                                    {{ paymentMethod.value }}
                                </option>
                            </select>
                            <span v-if="errors.room_id" class="error">{{ errors.room_id }}</span>
                        </div>
                        <div>
                            <label>Цена</label>
                            <h3>{{ currentPrice }}</h3>
                        </div>
                        <div>
                            <label>Сумма</label>
                            <h3>{{ totalPrice() }}</h3>
                        </div>
                    </div>

                    <!-- Кнопки -->
                    <div class="flex justify-end space-x-4 mt-8">
                        <Link :href="route('booking.list')"
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
import {ref, onMounted, computed, watch} from 'vue'
import {useRouter} from 'vue-router'
import {Russian} from 'flatpickr/dist/l10n/ru'
import AppLayout from "../../Layouts/AppLayout.vue";
import {route} from "ziggy-js";
import {Link} from "@inertiajs/vue3";
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

const router = useRouter()

const isEditMode = computed(() => !!route().params.booking)

const paymentMethods = [
    { key: 'online', value: 'Онлайн' },
    { key: 'on_site', value: 'На месте' }
];

const statusList = [
    { key: 'confirmed', value: 'Подтверждено' },
    { key: 'paid', value: 'Оплачено' },
    { key: 'canceled', value: 'Отменено' }
];

// Данные формы
const form = ref({
    user_id: null,
    room_id: null,
    payment_method: 'on_site',
    status: 'confirmed'
})

const checkInDate = ref(null)
const checkOutDate = ref(null)
const currentPrice = ref(null)
const rooms = ref([])
const users = ref([])
const isSubmitting = ref(false)
const disabledDates = ref([])
const booking = ref([])

function totalNights() {
    if (!checkInDate.value || !checkOutDate.value) return 0
    const diff = checkOutDate.value - checkInDate.value

    return Math.ceil(diff / (1000 * 60 * 60 * 24))
}

const bookingId = route().params.booking

// Загрузка данных
const loadInitialData = async () => {
    try {
        if (isEditMode.value) {
            const {data} = await axios.get(route('booking.show', bookingId))
            booking.value = data.data;
            form.value = {
                ...data.data
            }
            checkInDate.value = data.data.check_in
            checkOutDate.value = data.data.check_out
        }
    } catch (error) {
        console.error('Ошибка загрузки:', error)
    }
}

// Загрузка пользователей
const loadUsers = async () => {
    try {
        const response = await axios.get(route('users.index'))
        users.value = response.data.data
    } catch (error) {
        console.error('Ошибка загрузки пользователей:', error)
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

function totalPrice() {
    if (!totalNights()) return booking.value.total_price

    return currentPrice.value * totalNights()
}

const loadPrice = async () => {
    try {
        const params = {
            roomId: form.value.room_id,
            checkInDate: checkInDate.value,
            checkOutDate: checkOutDate.value
        }

        if (checkInDate.value && checkOutDate.value) {
            const response = await axios.get(route('price.by_dates'), {params})

            if (response.data.price) {
                currentPrice.value = parseFloat(response.data.price);
            } else {
                currentPrice.value = booking.value.room.base_price
            }
        } else {
            currentPrice.value = booking.value.room.base_price
        }
    } catch (error) {
        console.error('Ошибка загрузки цены:', error)
    }
}

// Ошибки валидации
const errors = ref({
    user_id: '',
    room_id: '',
    checkInDate: '',
    checkOutDate: ''
})

// Валидация формы
const validateForm = () => {
    let isValid = true
    errors.value = {
        user_id: '',
        room_id: '',
        checkInDate: '',
        checkOutDate: ''
    }

    if (!form.value.user_id) {
        errors.value.user_id = 'Выберите пользователя из списка!'
        isValid = false
    }

    if (!form.value.room_id) {
        errors.value.room_id = 'Выберите номер из списка!'
        isValid = false
    }

    if (!checkInDate.value) {
        errors.value.checkInDate = 'Выберите дату заезда!'
        isValid = false
    }

    if (!checkOutDate.value) {
        errors.value.checkOutDate = 'Выберите дату выезда!'
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

        const payload = {
            ...form.value,
            total_price: totalPrice(),
            check_in: checkInDate.value,
            check_out: checkOutDate.value,
        }

        const url = isEditMode.value
            ? route('booking.update', bookingId)
            : route('booking.store')

        const method = isEditMode.value ? 'put' : 'post'

        await axios[method](url, payload)
        window.location.href = route('booking.list')

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

const getDisabledDatesForRoom = async () =>  {
    try {
        const response1 = await axios.get(route('blocked_dates.by_room', form.value.room_id))
        const response2 = await axios.get(route('booking_dates.by_room', form.value.room_id))

        disabledDates.value = [...response1.data, ...response2.data]

    } catch (error) {
        console.error('Ошибка загрузки дат:', error)
    }
}


// Валидация при изменении дат
watch([form.value.checkIn, form.value.checkOut], () => {
    if (form.value.checkOut && form.value.checkIn && form.value.checkOut <= form.value.checkIn) {
        form.value.checkOut = null
    }
    loadPrice()
})

// Инициализация
onMounted(async () => {
    await loadInitialData()
    await loadPrice()
    await loadUsers()
    await loadRooms()
    await getDisabledDatesForRoom()
})
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
