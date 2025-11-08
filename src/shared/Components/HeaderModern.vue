<template>
  <header 
    ref="headerRef"
    class="modern-header"
    :class="{ 'scrolled': isScrolled, 'menu-open': isMobileMenuOpen }"
  >
    <div class="header-container">
      <!-- Logo -->
      <router-link to="/" class="logo-link">
        <img 
          src="@/assets/img/logo_horizontal.png" 
          alt="UNLI Studio" 
          class="logo"
        />
      </router-link>

      <!-- Desktop Navigation -->
      <nav class="nav-desktop">
        <ul class="nav-list">
          <li v-for="item in navItems" :key="item.href" class="nav-item">
            <a 
              :href="item.href" 
              class="nav-link"
              @click="handleNavClick"
            >
              {{ item.label }}
            </a>
          </li>
        </ul>
      </nav>

      <!-- CTA Button Desktop -->
      <div class="header-actions">
        <a href="#contact" class="btn-cta" @click="handleNavClick">
          <span>Iniciar Projeto</span>
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M1 8h14M8 1l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </a>
      </div>

      <!-- Mobile Menu Button -->
      <button 
        class="mobile-menu-btn"
        @click="toggleMobileMenu"
        :aria-label="isMobileMenuOpen ? 'Fechar menu' : 'Abrir menu'"
        :aria-expanded="isMobileMenuOpen"
      >
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
        <span class="hamburger-line"></span>
      </button>
    </div>

    <!-- Mobile Navigation -->
    <transition name="mobile-menu">
      <nav v-if="isMobileMenuOpen" class="nav-mobile">
        <ul class="mobile-nav-list">
          <li v-for="item in navItems" :key="item.href" class="mobile-nav-item">
            <a 
              :href="item.href" 
              class="mobile-nav-link"
              @click="closeMobileMenu"
            >
              {{ item.label }}
            </a>
          </li>
          <li class="mobile-nav-item mobile-cta">
            <a href="#contact" class="btn-cta-mobile" @click="closeMobileMenu">
              Iniciar Projeto
            </a>
          </li>
        </ul>
      </nav>
    </transition>
  </header>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from 'vue';

export default {
  name: 'HeaderModern',
  setup() {
    const headerRef = ref(null);
    const isScrolled = ref(false);
    const isMobileMenuOpen = ref(false);

    const navItems = [
      { href: '#aboutUs', label: 'Quem Somos' },
      { href: '#ourWorks', label: 'Portfólio' },
      { href: '#whatWeDo', label: 'Serviços' },
      { href: '#contact', label: 'Contato' }
    ];

    const handleScroll = () => {
      isScrolled.value = window.scrollY > 50;
    };

    const toggleMobileMenu = () => {
      isMobileMenuOpen.value = !isMobileMenuOpen.value;
      // Prevenir scroll do body quando menu está aberto
      if (isMobileMenuOpen.value) {
        document.body.style.overflow = 'hidden';
      } else {
        document.body.style.overflow = '';
      }
    };

    const closeMobileMenu = () => {
      isMobileMenuOpen.value = false;
      document.body.style.overflow = '';
    };

    const handleNavClick = () => {
      // Scroll suave já é nativo do browser com CSS
      closeMobileMenu();
    };

    onMounted(() => {
      window.addEventListener('scroll', handleScroll, { passive: true });
      handleScroll(); // Check initial state
    });

    onBeforeUnmount(() => {
      window.removeEventListener('scroll', handleScroll);
      document.body.style.overflow = ''; // Cleanup
    });

    return {
      headerRef,
      isScrolled,
      isMobileMenuOpen,
      navItems,
      toggleMobileMenu,
      closeMobileMenu,
      handleNavClick
    };
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/_colors.scss';
@import '@/assets/sass/settings/_fonts.scss';

.modern-header {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: $z-sticky;
  background: rgba($white, 0.8);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba($gray-light, 0.3);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

  &.scrolled {
    background: rgba($white, 0.95);
    box-shadow: $shadow-md;
    border-bottom-color: rgba($gray-light, 0.5);

    .logo {
      transform: scale(0.9);
    }

    .header-container {
      padding: 0.75rem 2rem;

      @media (max-width: 768px) {
        padding: 0.75rem 1rem;
      }
    }
  }

  &.menu-open {
    .hamburger-line:nth-child(1) {
      transform: translateY(8px) rotate(45deg);
    }

    .hamburger-line:nth-child(2) {
      opacity: 0;
      transform: translateX(-20px);
    }

    .hamburger-line:nth-child(3) {
      transform: translateY(-8px) rotate(-45deg);
    }
  }
}

.header-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 1rem 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 2rem;
  transition: padding 0.3s ease;

  @media (max-width: 768px) {
    padding: 1rem;
  }
}

// Logo
.logo-link {
  display: flex;
  align-items: center;
  text-decoration: none;
  z-index: 10;
}

.logo {
  height: 40px;
  width: auto;
  transition: transform 0.3s ease;

  @media (max-width: 768px) {
    height: 32px;
  }
}

// Desktop Navigation
.nav-desktop {
  display: none;

  @media (min-width: 769px) {
    display: flex;
    flex: 1;
    justify-content: center;
  }
}

.nav-list {
  display: flex;
  align-items: center;
  gap: 2.5rem;
  list-style: none;
  margin: 0;
  padding: 0;
}

.nav-item {
  position: relative;
}

.nav-link {
  font-family: $font-primary;
  font-size: $text-base;
  font-weight: $weight-medium;
  color: $gray-darkness;
  text-decoration: none;
  padding: 0.5rem 0;
  position: relative;
  transition: color 0.3s ease;

  &::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: $gradient-primary;
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  }

  &:hover {
    color: $p-color;

    &::after {
      width: 100%;
    }
  }
}

// Header Actions
.header-actions {
  display: none;

  @media (min-width: 769px) {
    display: flex;
    align-items: center;
    gap: 1rem;
  }
}

.btn-cta {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: $gradient-primary;
  color: $white;
  font-family: $font-primary;
  font-size: $text-sm;
  font-weight: $weight-semibold;
  text-decoration: none;
  border-radius: 50px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba($p-color, 0.2);

  svg {
    transition: transform 0.3s ease;
  }

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba($p-color, 0.3);

    svg {
      transform: translateX(4px);
    }
  }

  &:active {
    transform: translateY(0);
  }
}

// Mobile Menu Button
.mobile-menu-btn {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  width: 40px;
  height: 40px;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 8px;
  z-index: 10;

  @media (min-width: 769px) {
    display: none;
  }
}

.hamburger-line {
  width: 24px;
  height: 2px;
  background: $gray-darkness;
  border-radius: 2px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

  &:not(:last-child) {
    margin-bottom: 6px;
  }
}

// Mobile Navigation
.nav-mobile {
  position: fixed;
  top: 72px;
  left: 0;
  width: 100%;
  height: calc(100vh - 72px);
  background: rgba($white, 0.98);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  padding: 2rem 1rem;
  overflow-y: auto;

  @media (min-width: 769px) {
    display: none;
  }
}

.mobile-nav-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.mobile-nav-item {
  border-bottom: 1px solid $gray-light;

  &.mobile-cta {
    border: none;
    margin-top: 1.5rem;
  }
}

.mobile-nav-link {
  display: block;
  font-family: $font-primary;
  font-size: $text-lg;
  font-weight: $weight-medium;
  color: $gray-darkness;
  text-decoration: none;
  padding: 1rem 0;
  transition: color 0.3s ease, transform 0.3s ease;

  &:hover {
    color: $p-color;
    transform: translateX(8px);
  }
}

.btn-cta-mobile {
  display: block;
  text-align: center;
  padding: 1rem 2rem;
  background: $gradient-primary;
  color: $white;
  font-family: $font-primary;
  font-size: $text-base;
  font-weight: $weight-semibold;
  text-decoration: none;
  border-radius: 50px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba($p-color, 0.2);

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba($p-color, 0.3);
  }
}

// Animations
.mobile-menu-enter-active,
.mobile-menu-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.mobile-menu-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.mobile-menu-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

// Smooth scroll
html {
  scroll-behavior: smooth;
}
</style>
