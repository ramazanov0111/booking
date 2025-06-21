<script setup>
defineProps({
    value: String,
    required: false,
});
</script>

<template>
    <label class="block font-medium text-sm text-gray-700">
        <span v-if="value">{{ value }} <span v-if="required" class="required">*</span></span>
        <span v-else><slot /></span>
    </label>
</template>

<style>


.required {
    color: #e53935;
}

</style>
