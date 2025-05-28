<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="closeModal"
    >
        <div class="px-6 py-4">
            <div class="text-lg font-medium text-gray-900">
                <!-- Шапка модалки -->
                <header class="modal-header">
                    <h2>Бронирование {{ room?.name }}</h2>
                    <button @click="closeModal" class="close-btn">&times;</button>
                </header>
            </div>

            <div class="mt-4 text-sm text-gray-600">
                <div class="date-pickers">
                    <div class="date-group">
                        <label>Дата заезда</label>
                        <VueDatePicker
                            v-model="checkInDate"
                            :disabled-dates="disabledDates"
                            :enable-time-picker="false"
                            format="yyy-MM-dd"
                            auto-apply
                            @on-change="loadPrice"
                        />
                    </div>

                    <div class="date-group">
                        <label>Дата выезда</label>
                        <VueDatePicker
                            v-model="checkOutDate"
                            :disabled-dates="disabledDates"
                            :enable-time-picker="false"
                            format="yyy-MM-dd"
                            auto-apply
                            @on-change="loadPrice"
                        />
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
import {useBookingStore} from '@/store/booking'
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
})

const emit = defineEmits(['close', 'booking-success'])

// Состояния формы
const checkInDate = ref(null)
const checkOutDate = ref(null)
const isProcessing = ref(false)
const currentPrice = ref(null)
const paymentMethod = ref(null)

const paymentMethods = ref([
    'online',
    'on_site'
])

// Получение хранилища бронирований
const bookingStore = useBookingStore()

// Вычисляемые свойства
const disabledDates = computed(() =>
    bookingStore.getDisabledDatesForRoom(props.room?.id)
)

const isFormValid = computed(() =>
    checkInDate.value &&
    checkOutDate.value &&
    checkOutDate.value > checkInDate.value
)

function totalNights () {
    if (!checkInDate.value || !checkOutDate.value) return 0
    const diff = checkOutDate.value - checkInDate.value

    console.log(checkOutDate.value, checkInDate.value);
    return Math.ceil(diff / (1000 * 60 * 60 * 24))
}

const totalPrice = computed(() =>
    new Intl.NumberFormat('ru-RU').format(currentPrice.value * totalNights())
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

    isProcessing.value = true

    try {
        await bookingStore.createBooking({
            roomId: props.room.id,
            checkIn: checkInDate.value,
            checkOut: checkOutDate.value,
            paymentMethod: paymentMethod.value,
            totalPrice: totalPrice.value
        })

        emit('booking-success')
        closeModal()
    } catch (error) {
        console.error('Ошибка бронирования:', error)
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
})

</script>

<style scoped>

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.date-pickers {
    display: grid;
    position: relative;
    gap: 1rem;
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
</style>
