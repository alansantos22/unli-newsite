<template>
  <div ref="targetRef" class="lazy-image-wrapper">
    <!-- Skeleton Loader -->
    <div v-if="!isLoaded" class="skeleton-loader"></div>
    
    <!-- Image -->
    <img
      v-if="currentSrc"
      :src="currentSrc"
      :alt="alt"
      :class="['lazy-image', { loaded: isLoaded }]"
      @load="onImageLoad"
      @error="onImageError"
    />

    <!-- Error State -->
    <div v-if="hasError" class="error-placeholder">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </div>
  </div>
</template>

<script>
import { useLazyImage } from '@/core/composables/useLazyImage';

export default {
  name: 'LazyImage',
  props: {
    src: {
      type: String,
      required: true,
    },
    alt: {
      type: String,
      default: '',
    },
  },
  setup(props) {
    const { targetRef, currentSrc, isLoaded, isLoading, hasError } = useLazyImage(props.src);

    const onImageLoad = () => {
      // Additional logic when image loads
    };

    const onImageError = () => {
      // Additional logic on error
    };

    return {
      targetRef,
      currentSrc,
      isLoaded,
      isLoading,
      hasError,
      onImageLoad,
      onImageError,
    };
  },
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';
@import '@/assets/sass/settings/__keyframes.scss';

.lazy-image-wrapper {
  position: relative;
  width: 100%;
  height: 100%;
  overflow: hidden;
}

.lazy-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0;
  transition: opacity 0.4s ease-in-out;

  &.loaded {
    opacity: 1;
  }
}

.skeleton-loader {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    90deg,
    $gray-lightness 0%,
    $gray-light 20%,
    $gray-lightness 40%,
    $gray-lightness 100%
  );
  background-size: 200% 100%;
  animation: $skeleton 1.5s ease-in-out infinite;
}

.error-placeholder {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: $gray-lightness;

  svg {
    width: 40%;
    height: 40%;
    color: $gray-medium;
    opacity: 0.5;
  }
}
</style>
