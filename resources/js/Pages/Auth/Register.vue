<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {onMounted, ref} from "vue";
import Inputmask from 'inputmask/dist/inputmask.es6.js';
import {route} from "ziggy-js";

const form = useForm({
    name: '',
    lastname: '',
    login: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const errors = ref({
    login: '',
    phone: '',
    password: [],
    password_confirmation: '',
});


// Валидация формы
const validateForm = () => {
    let isValid = true
    const latinRegex = /^[a-zA-Z0-9]+$/;
    const passRegex = /(?=.*[0-9])(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z!@#$%^&*]{8,}/g;


    errors.value = {
        login: '',
        password: [],
        password_confirmation: '',
        phone: '',
    }

    if (!form.login.trim()) {
        errors.value.login = 'Введите логин пользователя'
        isValid = false
    } else if (!latinRegex.test(form.login)) {
        errors.value.login = 'Логин должен содержать только латиницу и цифры!'
        isValid = false
    }

    if (form.password.length < 8) {
        errors.value.password.push('Длина пароля должна быть минимум 8 символов!')
        isValid = false
    }

    if (!passRegex.test(form.password)) {
        errors.value.password.push('Пароль должен содержать латиницу, строчные и прописные буквы, спецсимволы и цифры!')
        isValid = false
    }

    if (form.password !== form.password_confirmation) {
        errors.value.password_confirmation = 'Пароли должны совпадать!'
        isValid = false
    }

    if (form.phone.length < 18 || form.phone.toString().includes('_')) {
        errors.value.phone = 'Длина номера телефона не соответствует формату!'
        isValid = false
    }

    return isValid
}

const submit = async () => {
    if (!validateForm()) return

    // await axios.post(route('register'), payload)
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const resetForm = () => {
    form.password = null
    form.password_confirmation = null
}

const change = async (value) => {
    if (!value) return;
    let val = value;
    if (val[0] === '8') {
        val = val.replace('8', '+7');
    }
    if (val.replace(/[^0-9]+/g, '') === '789') {
        val = '79';
    }
    this.$emit('input', val);
}

const bindPhoneMask = async (inputElement, regexMask = null, placeholder = null) => {
    if (!regexMask) regexMask = '^\\+7 \\([3489]\\d\\d\\) \\d\\d\\d \\d\\d \\d\\d$';
    if (!placeholder) placeholder = '+7 (___) ___ __ __';

    Inputmask({
        regex: regexMask,
        placeholder: placeholder,
        postValidation: buffer => {
            let nums = buffer.join('').replace(/[^0-9]+/g, '');
            let hasSevenNumbersInARow = (/(\d)\1{6}/g).test(nums);
            return !hasSevenNumbersInARow;
        }
    }).mask(inputElement);
}

// Инициализация
onMounted(() => {
    let selector = document.getElementById('phone');
    bindPhoneMask(selector);
})
</script>

<template>
    <Head title="Register" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Имя" required="1"/>
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="name"
                />
            </div>
            <div>
                <InputLabel for="lastname" value="Фамилия"  required="1"/>
                <TextInput
                    id="lastname"
                    v-model="form.lastname"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autocomplete="lastname"
                />
            </div>

            <div>
                <InputLabel for="login" value="Логин"  required="1"/>
                <TextInput
                    id="login"
                    v-model="form.login"
                    type="text"
                    class="mt-1 block w-full"
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="errors.login" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email"  required="1"/>
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="email"
                />
            </div>

            <div class="mt-4">
                <InputLabel for="phone" value="Телефон"  required="1"/>
                <TextInput
                    id="phone"
                    v-model="form.phone"
                    type="text"
                    class="mt-1 block w-full"
                    @input="change"
                    required
                />
                <InputError class="mt-2" :message="errors.phone" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Пароль"  required="1"/>
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />
                <InputError v-for="error in errors.password" class="mt-2" :message="error" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Подтверждение пароля"  required="1"/>
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="errors.password_confirmation" />
            </div>

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

                        <div class="ms-2">
                            I agree to the <a target="_blank" :href="route('terms.show')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Terms of Service</a> and <a target="_blank" :href="route('policy.show')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Privacy Policy</a>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="errors.terms" />
                </InputLabel>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link :href="route('login')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Уже зарегистрированы?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Зарегистрироваться
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
</template>
