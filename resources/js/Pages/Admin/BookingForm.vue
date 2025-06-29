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
                            <select v-model="form.room_id" @change="getDisabledDatesForRoom">
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
                                :min-date="minDate"
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
                                :min-date="minDateForCheckOut"
                                :enable-time-picker="false"
                                format="dd-MM-yyy"
                                auto-apply
                                @on-change="loadPrice"
                            />
                            <span v-if="errors.checkOutDate" class="error">{{ errors.checkOutDate }}</span>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-6">
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
                            </div>
                            <div>
                                <label class="flex items-center space-x-2">
                                    <input
                                        v-model="extraBed"
                                        type="checkbox"
                                        class="form-checkbox h-5 w-5 text-blue-600"
                                        @change.prevent="calculateLocally"
                                    >
                                    <span class="text-gray-700">Дополнительное место</span>
                                </label>
                            </div>
                        </div>
                        <!-- Детализация стоимости -->
                        <div v-if="calculation" class="bg-white rounded-xl shadow-lg p-6 sticky top-6">
                            <div>
                                <div v-for="day in calculation.daily_breakdown" class="mt-2 pt-2 border-b">
                                    <div class="flex justify-between mb-2">
                                        <span class="text-gray-600">{{ day.date }}:</span>
                                        <span class="text-gray-600">{{ formatPrice(day.price) }}</span>
                                    </div>
                                </div>
                                <div class="mt-2 pt-2 border-b">
                                    <div class="flex justify-between mb-2">
                                        <span class="text-gray-600">Дополнительное место:</span>
                                        <span class="text-gray-600">
                                            {{ formatPrice(500) }}/ночь
                                        </span>
                                    </div>
                                </div>
                                <div class="pt-2">
                                    <div class="flex justify-between font-bold text-lg mt-2 pt-2">
                                        <span>Итого</span>
                                        <span class="text-gray-600 font-normal">
                                            {{ calculation.nights }} {{ pluralizeNights(calculation.nights) }}
                                        </span>
                                        <span>{{ formatPrice(calculation.total) }}</span>
                                    </div>
                                </div>
                            </div>
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
import {ref, onMounted, computed, watch, watchEffect} from 'vue'
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
    {key: 'online', value: 'Онлайн'},
    {key: 'on_site', value: 'На месте'}
];

const statusList = [
    {key: 'confirmed', value: 'Подтверждено'},
    {key: 'paid', value: 'Оплачено'},
    {key: 'canceled', value: 'Отменено'}
];

// Данные формы
const form = ref({
    user_id: null,
    room_id: null,
    payment_method: 'on_site',
    status: 'confirmed',
})

// Минимальная дата для заезда - текущий день
const minDate = ref(new Date());
minDate.value.setHours(0, 0, 0, 0); // Обнуляем время

// Минимальная дата для выезда:
// - Если выбрана дата заезда, то дата заезда
// - Иначе текущий день
const minDateForCheckOut = computed(() => {
    return checkInDate.value || minDate.value;
});

const checkInDate = ref(null)
const checkOutDate = ref(null)
const currentPrice = ref(null)
const rooms = ref([])
const users = ref([])
const isSubmitting = ref(false)
const disabledDates = ref(null)
const booking = ref([])

const extraBed = ref(false)
const pricePeriods = ref(null)
const calculation = ref(null);

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
            extraBed.value = data.data.extra_bed
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

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long'
    })
}

// Форматирование цены
const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        maximumFractionDigits: 0
    }).format(price)
}

const pluralizeNights = (nights) => {
    if (nights % 10 === 1 && nights % 100 !== 11) return 'ночь'
    if (nights % 10 >= 2 && nights % 10 <= 4 && (nights % 100 < 10 || nights % 100 >= 20)) return 'ночи'
    return 'ночей'
}

const loadPrice = async () => {
    pricePeriods.value = null
    try {
        const params = {
            roomId: form.value.room_id,
            checkInDate: checkInDate.value,
            checkOutDate: checkOutDate.value
        }

        if (checkInDate.value && checkOutDate.value) {
            const response = await axios.get(route('price.by_dates'), {params})

            if (response.data.length) {
                pricePeriods.value = response.data
            } else {
                currentPrice.value = rooms.value.find(room => room.id === form.value.room_id)?.base_price
            }
            calculateLocally()
        } else {
            currentPrice.value = rooms.value.find(room => room.id === form.value.room_id)?.base_price
        }
    } catch (error) {
        console.error('Ошибка загрузки цены:', error)
    }
}

const calculateLocally = () => {
    if (!checkInDate.value || !checkOutDate.value) return

    calculation.value = null
    const start = new Date(checkInDate.value);
    const end = new Date(checkOutDate.value);
    const dailyBreakdown = [];
    let total = 0;

    for (let date = new Date(start); date < end; date.setDate(date.getDate() + 1)) {
        const price = getPriceForDate(date);
        dailyBreakdown.push({
            date: formatDate(date),
            price: price
        });
        total += price;
    }

    const nights = Math.ceil((end - start) / (1000 * 60 * 60 * 24))

    if (extraBed.value) {
        total += 500 * nights;
    }

    calculation.value = {
        total: total,
        nights: nights,
        daily_breakdown: dailyBreakdown
    };
};

const getPriceForDate = (date) => {
    const dateStr = date;
    if (pricePeriods.value) {
        const period = pricePeriods.value.find(p =>
            dateStr >= new Date(p.start_date) && dateStr <= new Date(p.end_date)
        );
        return period ? period.price : currentPrice.value;
    } else {
        return currentPrice.value;
    }
};

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
            total_price: calculation.value.total,
            check_in: checkInDate.value,
            check_out: checkOutDate.value,
            extra_bed: extraBed.value,
            stripe_payment_id: 'qwerty'
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

const getDisabledDatesForRoom = async () => {
    if (form.value.room_id) {
        try {
            const response1 = await axios.get(route('blocked_dates.by_room', form.value.room_id))
            const response2 = await axios.get(route('booking_dates.by_room', form.value.room_id))

            disabledDates.value = [...response1.data, ...response2.data]

        } catch (error) {
            console.error('Ошибка загрузки дат:', error)
        }
    }
    await loadPrice()
}


// Валидация при изменении дат
watch([checkInDate, checkOutDate, form.value.room_id], () => {
    if (checkOutDate.value && checkInDate.value && checkOutDate.value <= checkInDate.value) {
        checkOutDate.value = null
    }
    loadPrice()
    calculateLocally()
})

// Инициализация
onMounted(async () => {
    await loadInitialData()
    // await loadPrice()
    await loadUsers()
    await loadRooms()
    await getDisabledDatesForRoom()
})
</script>

<style scoped>

.form-checkbox {
    width: 10%;
    border-radius: 0.25rem;
    border: 1px solid #d1d5db;
}

.form-checkbox:checked {
    background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
}

label {
    //display: block;
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
