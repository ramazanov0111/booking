// stores/booking.js
import { defineStore } from 'pinia'

export const useBookingStore = defineStore('booking', {
    actions: {
        async createBooking(bookingData) {
            // Логика отправки на сервер
        },
        getDisabledDatesForRoom(roomId) {
            // Получение занятых дат
        }
    }
})
