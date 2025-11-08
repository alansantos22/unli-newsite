<template>
  <footer class="modern-footer">
    <div class="footer-container">
      <!-- Footer Top -->
      <div class="footer-top">
        <div class="footer-brand">
          <img 
            src="@/assets/img/logo_horizontal.png" 
            alt="UNLI Studio" 
            class="footer-logo"
          />
          <p class="footer-tagline">
            Transformando ideias em experiências gamificadas incríveis
          </p>
          <div class="footer-social">
            <a 
              v-for="social in socialLinks" 
              :key="social.name"
              :href="social.url" 
              target="_blank"
              rel="noopener noreferrer"
              class="social-link"
              :aria-label="social.name"
            >
              <i :class="social.icon"></i>
            </a>
          </div>
        </div>

        <div class="footer-links">
          <div class="footer-column">
            <h4 class="footer-title">Navegação</h4>
            <ul class="footer-list">
              <li v-for="link in navLinks" :key="link.href">
                <a :href="link.href" class="footer-link">{{ link.label }}</a>
              </li>
            </ul>
          </div>

          <div class="footer-column">
            <h4 class="footer-title">Serviços</h4>
            <ul class="footer-list">
              <li v-for="service in servicesLinks" :key="service">
                <a href="#whatWeDo" class="footer-link">{{ service }}</a>
              </li>
            </ul>
          </div>

          <div class="footer-column">
            <h4 class="footer-title">Contato</h4>
            <ul class="footer-list contact-list">
              <li>
                <i class="fas fa-envelope"></i>
                <a href="mailto:contato@unli.com.br" class="footer-link">contato@unli.com.br</a>
              </li>
              <li>
                <i class="fab fa-whatsapp"></i>
                <a href="https://wa.me/5511968354238" class="footer-link" target="_blank">+55 (11) 96835-4238</a>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Footer Bottom -->
      <div class="footer-bottom">
        <p class="footer-copyright">
          © {{ currentYear }} UNLI Studio. Todos os direitos reservados.
        </p>
      </div>
    </div>

    <!-- Decorative Elements -->
    <div class="footer-decoration">
      <div class="decoration-circle circle-1"></div>
      <div class="decoration-circle circle-2"></div>
    </div>
  </footer>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'FooterModern',
  setup() {
    const currentYear = ref(new Date().getFullYear());

    const navLinks = [
      { href: '#aboutUs', label: 'Quem Somos' },
      { href: '#ourWorks', label: 'Portfólio' },
      { href: '#whatWeDo', label: 'Serviços' },
      { href: '#contact', label: 'Contato' }
    ];

    const servicesLinks = [
      'Desenvolvimento de Jogos',
      'Gamificação',
      'Realidade Aumentada',
      'Sistemas Web',
      'Web3 & NFTs'
    ];

    const socialLinks = [
      { name: 'Instagram', url: 'https://www.instagram.com/unligames/', icon: 'fab fa-instagram' },
      { name: 'LinkedIn', url: 'https://www.linkedin.com/company/unli-games/?viewAsMember=true', icon: 'fab fa-linkedin-in' },
      { name: 'WhatsApp', url: 'https://wa.me/5511968354238', icon: 'fab fa-whatsapp' }
    ];

    return {
      currentYear,
      navLinks,
      servicesLinks,
      socialLinks
    };
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/_colors.scss';
@import '@/assets/sass/settings/_fonts.scss';

.modern-footer {
  position: relative;
  background: $gray-darkness;
  color: $gray-light;
  padding: 4rem 0 0;
  overflow: hidden;
}

.footer-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;

  @media (max-width: 768px) {
    padding: 0 1rem;
  }
}

// Footer Top
.footer-top {
  display: grid;
  grid-template-columns: 1.5fr 2fr;
  gap: 4rem;
  padding-bottom: 3rem;
  border-bottom: 1px solid rgba($gray-light, 0.1);

  @media (max-width: 992px) {
    grid-template-columns: 1fr;
    gap: 3rem;
  }
}

.footer-brand {
  .footer-logo {
    height: 40px;
    width: auto;
    margin-bottom: 1.5rem;
    filter: brightness(0) invert(1);
  }

  .footer-tagline {
    font-family: $font-primary;
    font-size: $text-base;
    color: $gray;
    line-height: 1.6;
    margin-bottom: 2rem;
    max-width: 300px;
  }
}

.footer-social {
  display: flex;
  gap: 1rem;
}

.social-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 44px;
  height: 44px;
  background: rgba($white, 0.05);
  border-radius: 50%;
  color: $gray-light;
  text-decoration: none;
  transition: all 0.3s ease;
  border: 1px solid rgba($white, 0.1);

  i {
    font-size: 20px;
  }

  &:hover {
    background: $p-color;
    color: $white;
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba($p-color, 0.3);
    border-color: $p-color;
  }
}

// Footer Links
.footer-links {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
}

.footer-column {
  .footer-title {
    font-family: $font-secondary;
    font-size: $text-lg;
    font-weight: $weight-semibold;
    color: $white;
    margin-bottom: 1.5rem;
  }

  .footer-list {
    list-style: none;
    padding: 0;
    margin: 0;

    li {
      margin-bottom: 0.75rem;

      &:last-child {
        margin-bottom: 0;
      }
    }
  }

  .footer-link {
    font-family: $font-primary;
    font-size: $text-sm;
    color: $gray;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;

    &:hover {
      color: $p-color;
      transform: translateX(4px);
    }
  }

  .contact-list {
    li {
      display: flex;
      align-items: center;
      gap: 0.75rem;

      i {
        font-size: 18px;
        color: $p-color;
        flex-shrink: 0;
      }

      .footer-text {
        font-family: $font-primary;
        font-size: $text-sm;
        color: $gray;
      }
    }
  }
}

// Footer Bottom
.footer-bottom {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 2rem 0;

  .footer-copyright {
    font-family: $font-primary;
    font-size: $text-sm;
    color: $gray;
    margin: 0;
  }
}

// Decorative Elements
.footer-decoration {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  overflow: hidden;
  z-index: 0;
}

.decoration-circle {
  position: absolute;
  border-radius: 50%;
  background: radial-gradient(circle, rgba($p-color, 0.08) 0%, transparent 70%);
  
  &.circle-1 {
    width: 400px;
    height: 400px;
    top: -200px;
    right: -100px;
  }

  &.circle-2 {
    width: 300px;
    height: 300px;
    bottom: -150px;
    left: -50px;
  }
}
</style>
