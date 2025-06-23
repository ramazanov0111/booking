<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionMessage from '@/Components/ActionMessage.vue';
import FormSection from '@/Components/FormSection.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

// Ошибки валидации
const errors = ref({
    current_password: '',
    password: [],
    password_confirmation: '',
})

// Валидация формы
const validateForm = () => {
    let isValid = true
    const latinRegex = /^[a-zA-Z0-9]+$/;
    const passRegex = /(?=.*[0-9])(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z])[0-9a-zA-Z!@#$%^&*]{8,}/g;

    errors.value = {
        current_password: '',
        password: [],
        password_confirmation: '',
    }

    if (!form.current_password.trim()) {
        errors.value.current_password = 'Текущий пароль не указан!';
        isValid = false
    }

    if (form.password && form.password.length < 8) {
        errors.value.password.push('Длина пароля должна быть минимум 8 символов!')
        isValid = false
    }

    if (form.password && !passRegex.test(form.password)) {
        errors.value.password.push('Пароль должен содержать латиницу, строчные и прописные буквы, спецсимволы и цифры!')
        isValid = false
    }

    if (form.password !== form.password_confirmation) {
        errors.value.password_confirmation = 'Пароли должны совпадать!'
        isValid = false
    }

    return isValid
}

const updatePassword = () => {
    if (!validateForm()) return

    form.put(route('user-password.update'), {
        errorBag: 'updatePassword',
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value.focus();
            }

            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <FormSection @submitted="updatePassword">
        <template #title>
            Обновить пароль
        </template>

        <template #description>
            Убедитесь, что в вашей учетной записи используется длинный надежный пароль, чтобы обеспечить безопасность.
        </template>

        <template #form>
            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="current_password" value="Текущий пароль" />
                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="current-password"
                />
                <InputError :message="form.errors.current_password" class="mt-2" />
                <InputError :message="errors.current_password" class="mt-2" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="password" value="Новый пароль" />
                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />
                <InputError v-for="error in errors.password" class="mt-2" :message="error"/>
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="password_confirmation" value="Подтверждение пароля" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="new-password"
                />
                <InputError :message="errors.password_confirmation" class="mt-2" />
            </div>
        </template>

        <template #actions>
            <ActionMessage :on="form.recentlySuccessful" class="me-3">
                Сохранено.
            </ActionMessage>

            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Сохранить
            </PrimaryButton>
        </template>
    </FormSection>
</template>
