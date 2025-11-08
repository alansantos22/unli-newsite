<template>
  <component
    :is="tag"
    :class="['modern-button', variant, size, { 'is-loading': loading, 'is-disabled': disabled }]"
    :disabled="disabled || loading"
    :type="type"
    @click="handleClick"
  >
    <!-- Icon Left -->
    <span v-if="iconLeft && !loading" class="button-icon icon-left">
      <component :is="iconLeft" v-if="typeof iconLeft === 'object'" />
      <span v-else>{{ iconLeft }}</span>
    </span>

    <!-- Loading Spinner -->
    <span v-if="loading" class="button-spinner">
      <svg class="spinner" viewBox="0 0 24 24" fill="none">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25"/>
        <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" opacity="0.75"/>
      </svg>
    </span>

    <!-- Text -->
    <span class="button-text">
      <slot>{{ text }}</slot>
    </span>

    <!-- Icon Right -->
    <span v-if="iconRight && !loading" class="button-icon icon-right">
      <component :is="iconRight" v-if="typeof iconRight === 'object'" />
      <span v-else>{{ iconRight }}</span>
    </span>
  </component>
</template>

<script>
export default {
  name: 'ModernButton',
  props: {
    tag: {
      type: String,
      default: 'button',
      validator: (value) => ['button', 'a', 'router-link'].includes(value),
    },
    variant: {
      type: String,
      default: 'primary',
      validator: (value) => [
        'primary',
        'secondary',
        'outline',
        'ghost',
        'gradient',
        'danger',
      ].includes(value),
    },
    size: {
      type: String,
      default: 'medium',
      validator: (value) => ['small', 'medium', 'large'].includes(value),
    },
    type: {
      type: String,
      default: 'button',
    },
    text: {
      type: String,
      default: '',
    },
    iconLeft: {
      type: [String, Object],
      default: null,
    },
    iconRight: {
      type: [String, Object],
      default: null,
    },
    loading: {
      type: Boolean,
      default: false,
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['click'],
  setup(props, { emit }) {
    const handleClick = (event) => {
      if (!props.disabled && !props.loading) {
        emit('click', event);
      }
    };

    return {
      handleClick,
    };
  },
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';
@import '@/assets/sass/settings/__fonts.scss';
@import '@/assets/sass/settings/__keyframes.scss';

.modern-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-family: $font-primary;
  font-weight: $weight-semibold;
  border: none;
  border-radius: 12px;
  cursor: pointer;
  transition: all $duration-normal $ease-out-smooth;
  text-decoration: none;
  white-space: nowrap;
  position: relative;
  overflow: hidden;

  &:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba($p-color, 0.3);
  }

  &.is-loading,
  &.is-disabled {
    cursor: not-allowed;
    opacity: 0.6;
  }

  // === SIZES ===
  &.small {
    padding: 8px 16px;
    font-size: $text-sm;

    .button-icon {
      width: 16px;
      height: 16px;
      font-size: 16px;
    }
  }

  &.medium {
    padding: 12px 24px;
    font-size: $text-base;

    .button-icon {
      width: 20px;
      height: 20px;
      font-size: 20px;
    }
  }

  &.large {
    padding: 16px 32px;
    font-size: $text-lg;

    .button-icon {
      width: 24px;
      height: 24px;
      font-size: 24px;
    }
  }

  // === VARIANTS ===
  &.primary {
    background: $gradient-primary;
    color: $white;
    box-shadow: $shadow-md;

    &:hover:not(.is-disabled):not(.is-loading) {
      transform: translateY(-2px);
      box-shadow: $shadow-xl;
    }

    &:active:not(.is-disabled):not(.is-loading) {
      transform: translateY(0);
    }
  }

  &.secondary {
    background: $gray-darkness;
    color: $white;
    box-shadow: $shadow-md;

    &:hover:not(.is-disabled):not(.is-loading) {
      background: $black;
      transform: translateY(-2px);
      box-shadow: $shadow-xl;
    }
  }

  &.outline {
    background: transparent;
    color: $p-color;
    border: 2px solid $p-color;

    &:hover:not(.is-disabled):not(.is-loading) {
      background: rgba($p-color, 0.1);
      transform: translateY(-2px);
    }
  }

  &.ghost {
    background: transparent;
    color: $gray-darkness;

    &:hover:not(.is-disabled):not(.is-loading) {
      background: rgba($gray-darkness, 0.05);
    }
  }

  &.gradient {
    background: $gradient-accent;
    color: $white;
    box-shadow: $shadow-glow;

    &:hover:not(.is-disabled):not(.is-loading) {
      transform: translateY(-2px);
      box-shadow: $shadow-glow-strong;
    }
  }

  &.danger {
    background: $error;
    color: $white;
    box-shadow: $shadow-md;

    &:hover:not(.is-disabled):not(.is-loading) {
      background: darken($error, 10%);
      transform: translateY(-2px);
      box-shadow: $shadow-xl;
    }
  }
}

// === ICON ===
.button-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform $duration-normal $ease-out-smooth;

  svg {
    width: 100%;
    height: 100%;
  }

  &.icon-right {
    .modern-button:hover:not(.is-disabled):not(.is-loading) & {
      transform: translateX(4px);
    }
  }

  &.icon-left {
    .modern-button:hover:not(.is-disabled):not(.is-loading) & {
      transform: translateX(-4px);
    }
  }
}

// === SPINNER ===
.button-spinner {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.spinner {
  width: 20px;
  height: 20px;
  animation: $spin 1s linear infinite;
}

// === TEXT ===
.button-text {
  display: inline-block;
}
</style>
