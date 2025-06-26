<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="closeModal"
        @focus="getDisabledDatesForRoom"
    >
        <div class="px-6 py-4">
            <div class="text-lg font-medium text-gray-900">
                <!-- Шапка модалки -->
                <header class="modal-header">
                    <h2>Бронирование {{ room?.name }}</h2>
                    <button @click="closeModal" class="close-btn">&times;</button>
                </header>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-2 gap-6 mb-6 mt-4 text-sm text-gray-600">
                <div class="date-range-picker">
                    <label>Дата заезда</label>
                    <VueDatePicker
                        v-model="checkInDate"
                        :disabled-dates="disabledDates"
                        :min-date="minDate"
                        :enable-time-picker="false"
                        format="dd-MM-yyy"
                        auto-apply
                        @on-change="fetchPricePeriods"
                    />
                </div>

                <div class="date-range-picker">
                    <label>Дата выезда</label>
                    <VueDatePicker
                        v-model="checkOutDate"
                        :disabled-dates="disabledDates"
                        :min-date="minDateForCheckOut"
                        :enable-time-picker="false"
                        format="dd-MM-yyy"
                        auto-apply
                        @on-change="fetchPricePeriods"
                    />
                </div>

                <div class="input-group">
                    <label>Способ оплаты <span class="required">*</span></label>
                    <select v-model="paymentMethod" required>
                        <option
                            v-for="item in paymentMethods"
                            :key="item.item"
                            :value="item.key"
                        >
                            {{ item.value }}
                        </option>
                    </select>
                </div>

                <!-- Детализация стоимости -->
<!--                <div v-if="calculation" class="calculation-result">-->
<!--                    <h3>Детали расчета:</h3>-->
<!--                    <div class="price-breakdown">-->
<!--                        <div v-for="day in calculation.daily_breakdown" class="day-price">-->
<!--                            {{ day.date }}: {{ formatPrice(day.price) }}-->
<!--                        </div>-->
<!--                    </div>-->
<!--                    <div class="total-price">-->
<!--                        Итого за {{ calculation.nights }} ночей:-->
<!--                        <strong>{{ formatPrice(calculation.total) }}</strong>-->
<!--                    </div>-->
<!--                </div>-->
            </div>
            <!-- Детализация стоимости -->
            <table v-if="calculation">
                <thead>
                <tr>
                    <th>День</th>
                    <th>Цена за ночь</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="day in calculation.daily_breakdown">
                    <td class="px-6 py-2 whitespace-nowrap space-x-2">{{ day.date }}</td>
                    <td class="px-6 py-2 whitespace-nowrap space-x-2">{{ formatPrice(day.price) }}</td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <td class="px-6 py-2 whitespace-nowrap space-x-2">Итого за {{ calculation.nights }} {{ pluralizeNights(calculation.nights) }} </td>
                    <td class="px-6 py-2 whitespace-nowrap space-x-2">{{ formatPrice(calculation.total) }}</td>
                </tr>
                </tfoot>
            </table>

        </div>

        <div class="flex flex-row justify-end px-6 py-4 bg-gray-100 text-end rounded-lg">
            <!-- Кнопка бронирования -->
            <button
                class="book-btn"
                @click="handleBooking"
                :disabled="!isFormValid || isProcessing"
            >
                <span v-if="isProcessing">Обработка...</span>
                <span v-else>Забронировать за {{ calculation?.total }} ₽</span>
            </button>
        </div>
    </Modal>
</template>

<script setup>
import {ref, computed, watch, onMounted, watchEffect} from 'vue'
import VueDatePicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import Modal from "@/Components/Modal.vue";
import {route} from "ziggy-js";

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    room: {
        type: Object,
        required: true,
        default: null
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    user: Object,
    checkIn: '',
    checkOut: '',
})

const emit = defineEmits(['close', 'booking-success'])

// Состояния формы
const isProcessing = ref(false)
const checkInDate = ref(null)
const checkOutDate = ref(null)
const paymentMethod = ref(null)
const disabledDates = ref(null)
const currentPrice = ref(null)
const errors = ref({})

const pricePeriods = ref(null)
const calculation = ref(null);

const paymentMethods = [
    {key: 'online', value: 'Онлайн'},
    {key: 'on_site', value: 'На месте'}
];

const statusList = [
    {key: 'confirmed', value: 'Подтверждено'},
    {key: 'paid', value: 'Оплачено'},
    {key: 'canceled', value: 'Отменено'}
];

const isFormValid = computed(() =>
    checkInDate.value &&
    checkOutDate.value &&
    checkOutDate.value > checkInDate.value
)

function totalNights() {
    if (!checkInDate.value || !checkOutDate.value) return 0
    const diff = checkOutDate.value - checkInDate.value

    return Math.ceil(diff / (1000 * 60 * 60 * 24))
}

const totalPrice = computed(() =>
    currentPrice.value * totalNights()
)

const pluralizeNights = (nights) => {
    if (nights % 10 === 1 && nights % 100 !== 11) return 'ночь'
    if (nights % 10 >= 2 && nights % 10 <= 4 && (nights % 100 < 10 || nights % 100 >= 20)) return 'ночи'
    return 'ночей'
}

// Форматирование цены
const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU', {
        style: 'currency',
        currency: 'RUB',
        maximumFractionDigits: 0
    }).format(price)
}

const fetchPricePeriods = async () => {
    try {
        const params = {
            roomId: props.room?.id,
            checkInDate: checkInDate.value,
            checkOutDate: checkOutDate.value
        }

        if (checkInDate.value && checkOutDate.value) {
            const response = await axios.get(route('price.by_dates'), {params})

            if (response.data.length) {
                pricePeriods.value = response.data
                calculateLocally();
            } else {
                currentPrice.value = props.room.base_price
            }
        } else {
            currentPrice.value = props.room.base_price
        }

    } catch (error) {
        console.error('Ошибка загрузки цены:', error)
    }
}

// Форматирование данных
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const calculateLocally = () => {
    if (!pricePeriods.value.length) return;

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

    calculation.value = {
        total: total,
        nights: Math.ceil((end - start) / (1000 * 60 * 60 * 24)),
        daily_breakdown: dailyBreakdown
    };
};

const getPriceForDate = (date) => {
    const dateStr = date;
    const period = pricePeriods.value.find(p =>
        dateStr >= new Date(p.start_date) && dateStr <= new Date(p.end_date)
    );
    return period ? period.price : 0;
};

const getDisabledDatesForRoom = async () => {
    if (props.show) {
        try {
            const response1 = await axios.get(route('blocked_dates.by_room', props.room?.id))
            const response2 = await axios.get(route('booking_dates.by_room', props.room?.id))

            disabledDates.value = [...response1.data, ...response2.data]

        } catch (error) {
            console.error('Ошибка загрузки дат:', error)
        }
        await loadDates();
    }
}

const loadDates = async () => {
    if (props.checkIn && props.checkOut) {
        checkInDate.value = props.checkIn
        checkOutDate.value = props.checkOut
    }
}


// Методы
const closeModal = () => {
    resetForm()
    emit('close')
}

// Минимальная дата для заезда - текущий день
const minDate = ref(new Date());
minDate.value.setHours(0, 0, 0, 0); // Обнуляем время

// Минимальная дата для выезда:
// - Если выбрана дата заезда, то дата заезда
// - Иначе текущий день
const minDateForCheckOut = computed(() => {
    return checkInDate.value || minDate.value;
});

const resetForm = () => {
    checkInDate.value = null
    checkOutDate.value = null
    currentPrice.value = null
}

const handleBooking = async () => {
    if (!isFormValid.value) return

    try {
        isProcessing.value = true
        errors.value = {}

        const payload = {
            user_id: props.user.id,
            room_id: props.room.id,
            payment_method: paymentMethod.value,
            total_price: calculation.value.total ,
            check_in: checkInDate.value,
            check_out: checkOutDate.value,
            status: 'confirmed',
            stripe_payment_id: 'qwerty'
        }

        const response = await axios.post(route('booking.store'), payload)
        closeModal()
        console.log('Успех:', response.data);

    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        } else {
            console.log(error);
            alert('Ошибка сохранения')
        }
    } finally {
        isProcessing.value = false
    }
}

// Валидация при изменении дат
watch([checkInDate, checkOutDate], () => {
    if (checkOutDate.value && checkInDate.value && checkOutDate.value <= checkInDate.value) {
        checkOutDate.value = null
    }
    fetchPricePeriods()
})

// Инициализация
onMounted(async () => {
    // await loadDates()
    await getDisabledDatesForRoom()
    const now = new Date();
    const msUntilMidnight = new Date(now).setHours(24, 0, 0, 0) - now;

    setTimeout(() => {
        minDate.value = new Date();
        minDate.value.setHours(0, 0, 0, 0);
    }, msUntilMidnight);
})

</script>

<style scoped>

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.book-btn {
    width: 100%;
    padding: 1rem;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: background 0.2s;
}

.book-btn:disabled {
    background: #94a3b8;
    cursor: not-allowed;
}

table {
    @apply min-w-full divide-y divide-gray-200;
}

th {
    @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider;
}

td {
    @apply px-6 py-2 whitespace-nowrap;
}

tr:hover td {
    @apply bg-gray-50;
}

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

.required {
    color: #e53935;
}
</style>
