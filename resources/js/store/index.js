import { createStore } from 'vuex'

export default createStore({
    state: {
        user: null,       // Пример состояния
        isLoading: false  // Дополнительное состояние
    },
    mutations: {
        SET_USER(state, user) {
            state.user = user
        },
        SET_LOADING(state, status) {
            state.isLoading = status
        }
    },
    actions: {
        loginUser({ commit }) {
            commit('SET_LOADING', true)
            // Здесь будет логика авторизации
            return props.auth.user
        }
    },
    getters: {
        isAuthenticated: state => !!state.user
    }
})
