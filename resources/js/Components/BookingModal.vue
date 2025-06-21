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
                        :enable-time-picker="false"
                        format="dd-MM-yyy"
                        auto-apply
                        @on-change="loadPrice"
                    />
                </div>

                <div class="date-range-picker">
                    <label>Дата выезда</label>
                    <VueDatePicker
                        v-model="checkOutDate"
                        :disabled-dates="disabledDates"
                        :enable-time-picker="false"
                        format="dd-MM-yyy"
                        auto-apply
                        @on-change="loadPrice"
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
            </div>
            <!-- Детализация стоимости -->
            <table>
                <thead>
                <tr>
                    <th>Количество ночей</th>
                    <th>Цена за ночь в указанный период</th>
                    <th>Сумма</th>
                </tr>
                </thead>
                <tbody>
                <tr>

                    <td>{{ totalNights() }}</td>

                    <td>{{ currentPrice }}</td>

                    <td>{{ totalPrice }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-row justify-end px-6 py-4 bg-gray-100 text-end">
            <!-- Кнопка бронирования -->
            <button
                class="book-btn"
                @click="handleBooking"
                :disabled="!isFormValid || isProcessing"
            >
                <span v-if="isProcessing">Обработка...</span>
                <span v-else>Забронировать за {{ totalPrice }} ₽</span>
            </button>
        </div>
    </Modal>
</template>

<script setup>
import {ref, computed, watch, onMounted} from 'vue'
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

const paymentMethods = [
    { key: 'online', value: 'Онлайн' },
    { key: 'on_site', value: 'На месте' }
];

const statusList = [
    { key: 'pending', value: 'В обработке' },
    { key: 'confirmed', value: 'Подтверждено' },
    { key: 'paid', value: 'Оплачено' },
    { key: 'canceled', value: 'Отменено' }
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

const loadPrice = async () => {
    try {
        const params = {
            roomId: props.room?.id,
            checkInDate: checkInDate.value,
            checkOutDate: checkOutDate.value
        }

        if (checkInDate.value && checkOutDate.value) {
            const response = await axios.get(route('price.by_dates'), {params})

            if (response.data.price) {
                currentPrice.value = response.data.price
            }
        } else {
            currentPrice.value = props.room.base_price
        }

    } catch (error) {
        console.error('Ошибка загрузки цены:', error)
    }
}

const getDisabledDatesForRoom = async () =>  {
    if (props.show) {
        try {
            const response1 = await axios.get(route('blocked_dates.by_room', props.room?.id))
            const response2 = await axios.get(route('booking_dates.by_room', props.room?.id))

            disabledDates.value = [...response1.data, ...response2.data]

        } catch (error) {
            console.error('Ошибка загрузки дат:', error)
        }
    }
}

// Методы
const closeModal = () => {
    resetForm()
    emit('close')
}

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
            total_price: totalPrice.value,
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
    loadPrice()
})

// Инициализация
onMounted(async () => {
    // await loadPrice()
    await getDisabledDatesForRoom()
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
    @apply px-6 py-4 whitespace-nowrap;
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
