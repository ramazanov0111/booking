<template>
    <div class="lightbox" @click.self="close">
        <button class="close-btn" @click="close">&times;</button>

        <div class="lightbox-content">
            <img
                :src="currentImage.imageUrl"
                :alt="'Фото номера ' + (currentIndex + 1)"
                class="lightbox-image"
            >

            <div class="lightbox-controls">
                <button class="nav-btn prev-btn" @click.stop="prevImage" :disabled="isFirstImage">
                    &larr;
                </button>
                <div class="image-counter">
                    {{ currentIndex + 1 }} / {{ images.length }}
                </div>
                <button class="nav-btn next-btn" @click.stop="nextImage" :disabled="isLastImage">
                    &rarr;
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    images: {
        type: Array,
        required: true
    },
    initialIndex: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['close']);

const currentIndex = ref(props.initialIndex);

// Вычисляемые свойства
const currentImage = computed(() => props.images[currentIndex.value]);
const isFirstImage = computed(() => currentIndex.value === 0);
const isLastImage = computed(() => currentIndex.value === props.images.length - 1);

// Навигация по изображениям
function nextImage() {
    if (!isLastImage.value) {
        currentIndex.value++;
    }
}

function prevImage() {
    if (!isFirstImage.value) {
        currentIndex.value--;
    }
}

// Закрытие лайтбокса
function close() {
    emit('close');
}

// Обработка клавиатуры
function handleKeydown(e) {
    switch (e.key) {
        case 'ArrowLeft':
            prevImage();
            break;
        case 'ArrowRight':
            nextImage();
            break;
        case 'Escape':
            close();
            break;
    }
}

// Вешаем обработчики клавиш
onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

// Убираем обработчики при размонтировании
onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<style scoped>
.lightbox {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.close-btn {
    position: absolute;
    top: 20px;
    right: 30px;
    background: transparent;
    color: white;
    border: none;
    font-size: 3rem;
    cursor: pointer;
    transition: color 0.3s;
}

.close-btn:hover {
    color: #42b983;
}

.lightbox-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.lightbox-image {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
    border-radius: 4px;
}

.lightbox-controls {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 1rem;
    color: white;
    gap: 2rem;
}

.nav-btn {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
    cursor: pointer;
    transition: background 0.3s;
}

.nav-btn:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.4);
}

.nav-btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.image-counter {
    font-size: 1.2rem;
    min-width: 80px;
    text-align: center;
}
</style>
