// stores/booking.js
import { defineStore } from 'pinia'
import {route} from "ziggy-js";

export const useBookingStore = defineStore('booking', {
    actions: {
        async createBooking(bookingData) {
            // Логика отправки на сервер
        }
    }
})
