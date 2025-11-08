<template>
  <div class="main-page-modern">
    <!-- Hero Section -->
    <HeroModern
      badge="Gamificação Premium"
      :titleLines="['Create', 'High-Quality', 'Visual']"
      description="Busque uma melhor conexão com o seu cliente através de experiências gamificadas únicas e envolventes que transformam interações em aventuras memoráveis."
      primaryButtonText="Começar Agora"
      secondaryButtonText="Ver Portfólio"
      :images="heroImages"
      :stats="heroStats"
      @primary-action="handleStartProject"
      @secondary-action="handleViewPortfolio"
    />

    <!-- Stats Section -->
    <StatsSection
      variant="light"
      title="Números que Impressionam"
      description="Resultados concretos que demonstram nossa expertise em gamificação e desenvolvimento"
      :stats="mainStats"
    />

    <!-- Services Section -->
    <ServicesSection
      variant="dark"
      cardVariant="dark"
      badge="Nossos Serviços"
      title="The Services We Offer <br/> For Your Business"
      description="Transformamos ideias em experiências digitais incríveis através da gamificação"
      :services="services"
      @service-click="handleServiceClick"
    />

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
      <div class="container">
        <div class="content-wrapper">
          <div class="text-content">
            <h2 class="section-title">
              We understand your needs better than anybody and we know boring
              <span class="highlight">when we see it</span>,
              so we will keep your audience engaged.
            </h2>
            <p class="author">Jean Brooklyn</p>
            <p class="role">CEO & Founder</p>
          </div>
          <div class="image-content">
            <LazyImage 
              src="/path/to/team-image.jpg"
              alt="Team collaboration"
              class="team-image"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Portfolio/Works Section -->
    <ServicesSection
      variant="light"
      cardVariant="light"
      badge="Portfólio"
      title="Projetos que <span class='highlight-text'>Transformam</span>"
      description="Conheça alguns dos projetos gamificados que desenvolvemos"
      :services="portfolioItems"
      @service-click="handlePortfolioClick"
    />

    <!-- Contact Section -->
    <ContactSection
      title="Share Your Idea + Let's Collaborate"
      description="Looking for exciting ideas or need help transforming content that drives action."
      :contactItems="contactInfo"
      :includePhone="true"
      submitText="Enviar Mensagem"
      @submit="handleContactSubmit"
    />
  </div>
</template>

<script>
import HeroModern from '@/shared/Components/HeroModern.vue';
import StatsSection from '@/shared/Components/StatsSection.vue';
import ServicesSection from '@/shared/Components/ServicesSection.vue';
import ContactSection from '@/shared/Components/ContactSection.vue';
import LazyImage from '@/shared/Components/LazyImage.vue';

export default {
  name: 'MainPageModern',
  components: {
    HeroModern,
    StatsSection,
    ServicesSection,
    ContactSection,
    LazyImage,
  },
  data() {
    return {
      // Hero Images (mosaic grid)
      heroImages: [
        { src: '/path/to/image1.jpg', alt: 'Professional work 1' },
        { src: '/path/to/image2.jpg', alt: 'Professional work 2' },
        { src: '/path/to/image3.jpg', alt: 'Professional work 3' },
        { src: '/path/to/image4.jpg', alt: 'Professional work 4' },
        { src: '/path/to/image5.jpg', alt: 'Professional work 5' },
        { src: '/path/to/image6.jpg', alt: 'Professional work 6' },
      ],

      // Hero Stats
      heroStats: [
        { value: '158', label: 'Projetos Concluídos' },
        { value: '625', label: 'Clientes Felizes' },
        { value: '730', label: 'Horas de Gamificação' },
      ],

      // Main Stats
      mainStats: [
        {
          value: 250,
          label: 'Projetos Entregues',
          icon: '🎮',
          suffix: '+',
          iconBg: 'linear-gradient(135deg, #e67e22 0%, #d35400 100%)',
        },
        {
          value: 1500,
          label: 'Clientes Satisfeitos',
          icon: '😊',
          suffix: '+',
          iconBg: 'linear-gradient(135deg, #4f7aff 0%, #3d5fd9 100%)',
        },
        {
          value: 98,
          label: 'Taxa de Satisfação',
          icon: '⭐',
          suffix: '%',
          iconBg: 'linear-gradient(135deg, #00d084 0%, #00a86b 100%)',
        },
        {
          value: 50,
          label: 'Prêmios Ganhos',
          icon: '🏆',
          suffix: '+',
          iconBg: 'linear-gradient(135deg, #9d6cff 0%, #7c4dff 100%)',
        },
      ],

      // Services
      services: [
        {
          image: '/path/to/service1.jpg',
          category: 'Gamificação',
          title: 'Turn your ideas into show-stopping presentations',
          description: 'Transforme suas ideias em apresentações envolventes com elementos de gamificação que prendem a atenção do público.',
          actionText: 'Learn More',
        },
        {
          image: '/path/to/service2.jpg',
          category: 'E-commerce',
          title: 'Sell More Products Worldwide',
          description: 'Aumente suas vendas globalmente com estratégias de gamificação aplicadas ao e-commerce.',
          actionText: 'Learn More',
        },
        {
          image: '/path/to/service3.jpg',
          category: 'Marketing',
          title: 'No Jargon Need. Stop Worrying it',
          description: 'Marketing descomplicado com gamificação que gera resultados reais e mensuráveis.',
          actionText: 'Learn More',
        },
      ],

      // Portfolio Items
      portfolioItems: [
        {
          image: '/path/to/portfolio1.jpg',
          category: 'Game Development',
          title: 'Fantasy RPG Adventure',
          description: 'Um jogo de RPG completo com mecânicas inovadoras e storytelling envolvente.',
          actionText: 'Ver Projeto',
        },
        {
          image: '/path/to/portfolio2.jpg',
          category: 'Web Gamification',
          title: 'Educational Platform',
          description: 'Plataforma educacional gamificada que aumentou o engajamento em 300%.',
          actionText: 'Ver Projeto',
        },
        {
          image: '/path/to/portfolio3.jpg',
          category: 'Mobile Game',
          title: 'Puzzle Challenge',
          description: 'Jogo de puzzle mobile com mais de 100k downloads na primeira semana.',
          actionText: 'Ver Projeto',
        },
      ],

      // Contact Info
      contactInfo: [
        {
          icon: '📧',
          label: 'Email',
          value: 'contact@unli.studio',
          iconBg: 'linear-gradient(135deg, #4f7aff 0%, #3d5fd9 100%)',
        },
        {
          icon: '📱',
          label: 'Telefone',
          value: '+55 (11) 99999-9999',
          iconBg: 'linear-gradient(135deg, #00d084 0%, #00a86b 100%)',
        },
        {
          icon: '📍',
          label: 'Endereço',
          value: 'São Paulo, Brasil',
          iconBg: 'linear-gradient(135deg, #9d6cff 0%, #7c4dff 100%)',
        },
      ],
    };
  },
  methods: {
    handleStartProject() {
      console.log('Start project clicked');
      // Navigate or show modal
    },
    handleViewPortfolio() {
      console.log('View portfolio clicked');
      // Navigate to portfolio
    },
    handleServiceClick({ service, index }) {
      console.log('Service clicked:', service, index);
      // Navigate or show details
    },
    handlePortfolioClick({ service, index }) {
      console.log('Portfolio item clicked:', service, index);
      // Navigate to project details
    },
    handleContactSubmit(formData) {
      console.log('Contact form submitted:', formData);
      // Send to API
    },
  },
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/__colors.scss';
@import '@/assets/sass/settings/__fonts.scss';
@import '@/assets/sass/settings/__keyframes.scss';

.main-page-modern {
  width: 100%;
  overflow-x: hidden;
}

// === WHY CHOOSE US SECTION ===
.why-choose-us {
  padding: 120px 0;
  background: $white;

  @media (max-width: 768px) {
    padding: 80px 0;
  }

  .container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 40px;

    @media (max-width: 768px) {
      padding: 0 24px;
    }
  }

  .content-wrapper {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 80px;
    align-items: center;

    @media (max-width: 1024px) {
      grid-template-columns: 1fr;
      gap: 60px;
    }
  }

  .section-title {
    font-family: $font-secondary;
    font-size: $text-h2;
    font-weight: $weight-bold;
    color: $gray-darkness;
    line-height: $leading-tight;
    margin-bottom: 32px;

    @media (max-width: 768px) {
      font-size: $text-h3;
    }

    .highlight {
      color: $p-color;
      position: relative;
      display: inline-block;

      &::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 8px;
        background: rgba($p-color, 0.2);
        z-index: -1;
      }
    }
  }

  .author {
    font-family: $font-primary;
    font-size: $text-lg;
    font-weight: $weight-semibold;
    color: $gray-darkness;
    margin-bottom: 4px;
  }

  .role {
    font-family: $font-primary;
    font-size: $text-base;
    color: $gray-medium;
  }

  .image-content {
    border-radius: $card-radius-lg;
    overflow: hidden;
    box-shadow: $shadow-2xl;

    .team-image {
      width: 100%;
      height: auto;
      transition: transform $duration-slow $ease-out-smooth;

      &:hover {
        transform: scale(1.05);
      }
    }
  }
}

// Utility class for highlighted text
:deep(.highlight-text) {
  color: $p-color;
  position: relative;
  
  &::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 120%;
    height: 120%;
    background: rgba($p-color, 0.1);
    border-radius: 50%;
    z-index: -1;
  }
}
</style>
