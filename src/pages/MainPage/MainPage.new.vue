<template>
  <div class="main-page-modern">
    <!-- Scroll Progress Bar -->
    <div class="scroll-progress-bar" :style="{ width: scrollProgress + '%' }"></div>

    <!-- Hero Section -->
    <HeroModern
      badge="UNLI Studio"
      :titleLines="heroData.titleLines"
      :description="heroData.description"
      :images="heroData.images"
      :stats="heroData.stats"
      :primaryButtonText="heroData.primaryButtonText"
      :secondaryButtonText="heroData.secondaryButtonText"
      @primary-action="handleStartProject"
      @secondary-action="handleViewPortfolio"
    />

    <!-- Clientes Satisfeitos -->
    <ClientsSection
      id="clients"
      variant="light"
      badge="Confiança"
      title="Algumas das Marcas que Entregamos Excelência"
      description="Empresas e projetos que confiaram no nosso trabalho"
      :clients="clientsData"
      data-scroll
    />

    <!-- Sobre Nós / Benefícios - GAME STYLE -->
    <BenefitsGameSection id="aboutUs" :benefits="benefitsData" data-scroll />

    <!-- Nosso Trabalho / Portfólio -->
    <section id="ourWorks" class="portfolio-section" data-scroll>
      <div class="portfolio-container">
        <div class="portfolio-header">
          <span class="section-badge">Portfólio</span>
          <h2 class="section-title">Conheça Nosso Trabalho</h2>
          <p class="section-description">
            Projetos que desenvolvemos com paixão e dedicação
          </p>
        </div>

        <!-- Featured Project -->
        <div class="featured-project" :key="currentProject.id">
          <div 
            class="project-image"
            :style="{ backgroundImage: `url(${getProjectImage(currentProject.image)})` }"
          >
            <span class="project-category" :class="currentProject.category.toLowerCase()">
              {{ currentProject.category.replace('_', ' ') }}
            </span>
          </div>
          <div class="project-info">
            <h3>{{ currentProject.name }}</h3>
            <p>{{ currentProject.subtitle }}</p>
            <router-link :to="`/projeto/${currentProject.id}`" class="btn-details">
              Ver Detalhes
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                <path d="M1 8h14M8 1l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </router-link>
          </div>
        </div>

        <!-- Projects List -->
        <div class="projects-list">
          <div 
            v-for="(project, index) in portfolioData" 
            :key="project.id"
            class="project-item"
            :class="{ active: currentProject.id === project.id }"
            @click="handleManualProjectSelect(index)"
          >
            <div 
              class="project-thumbnail"
              :style="{ backgroundImage: `url(${getProjectImage(project.image)})` }"
            ></div>
            <span class="project-name">{{ project.name }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- O Que Fazemos / Serviços -->
    <section id="whatWeDo" class="services-section" data-scroll>
      <div class="services-container">
        <div class="services-header">
          <span class="section-badge">Serviços</span>
          <h2 class="section-title">O Que Podemos Oferecer Para Sua Empresa</h2>
          <p class="section-description">
            Soluções completas em gamificação e desenvolvimento
          </p>
        </div>

        <div class="services-grid">
          <div v-for="service in servicesData" :key="service.title" class="service-card" data-stagger>
            <div class="service-icon">
              <i :class="service.icon"></i>
            </div>
            <h3 class="service-title">{{ service.title }}</h3>
            <ul class="service-list">
              <li v-for="item in service.items" :key="item">{{ item }}</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section: Pronto para Gamificar? -->
    <section class="cta-section" data-scroll>
      <div class="cta-container">
        <div class="cta-content">
          <span class="cta-badge">
            <i class="fas fa-rocket"></i>
            Vamos Começar
          </span>
          <h2 class="cta-title">Pronto para Transformar Seu Negócio com Gamificação?</h2>
          <p class="cta-description">
            Junte-se às empresas que já revolucionaram a experiência dos seus clientes. 
            Agende uma consultoria gratuita e descubra como a gamificação pode impulsionar seus resultados.
          </p>
          <div class="cta-actions">
            <a href="#contact" class="btn-cta-primary">
              <i class="fas fa-calendar-alt"></i>
              Agendar Consultoria Grátis
            </a>
            <a href="https://wa.me/5511968354238?text=Olá! Gostaria de saber mais sobre gamificação" target="_blank" class="btn-cta-secondary">
              <i class="fab fa-whatsapp"></i>
              Falar no WhatsApp
            </a>
          </div>
          <div class="cta-stats">
            <div class="cta-stat" data-stagger>
              <i class="fas fa-code"></i>
              <span>Tecnologia + Game Design</span>
            </div>
            <div class="cta-stat" data-stagger>
              <i class="fas fa-rocket"></i>
              <span>Do MVP ao Scale</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Timeline do Processo -->
    <TimelineSection
      id="process"
      variant="dark"
      badge="Nosso Processo"
      title="Do Problema ao <span style='color: #e67e22'>MVP Validado</span>"
      description="Cada projeto é uma jornada. Veja como transformamos sua ideia em uma solução real, testada e pronta para crescer."
      :steps="processSteps"
      :keyBenefits="processBenefits"
    />

    <!-- Experience Preview Gamificada -->
    <ExperiencePreview />

    <!-- Depoimentos -->
    <TestimonialsSection
      id="testimonials"
      variant="light"
      badge="Depoimentos"
      title="Comentários de Clientes Satisfeitos"
      description="Veja o que nossos parceiros dizem sobre nosso trabalho"
      :testimonials="testimonialsData"
      :autoPlay="true"
      :autoPlayInterval="5000"
      data-scroll
    />

    <!-- Quer Conhecer Mais / CEO Interview -->
    <section id="ceoInterview" class="ceo-section" data-scroll>
      <div class="ceo-container">
        <div class="ceo-header">
          <h2 class="section-title">Quer Conhecer Mais?</h2>
          <p class="section-description">
            Veja a entrevista completa do nosso CEO Alan Reis no Flow Games, durante a BGS
          </p>
        </div>
        <div class="video-wrapper">
          <iframe 
            src="https://www.youtube.com/embed/JkTkHzMdsww?si=xUrnrIyXar4Fv3rI" 
            title="Entrevista CEO - Alan Reis"
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
            allowfullscreen
          ></iframe>
        </div>
      </div>
    </section>

    <!-- Contato -->
    <ContactSection
      id="contact"
      title="Entre em Contato com a Gente!"
      description="Envie um e-mail agora e vamos transformar sua ideia em realidade"
      :contactItems="contactData"
      submitText="Enviar Mensagem"
      @submit="handleContactSubmit"
    />
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue';
import {
  HeroModern,
  ContactSection,
  BenefitsGameSection,
  ExperiencePreview
} from '@/shared/Components';
import ClientsSection from '@/shared/Components/ClientsSection.vue';
import TestimonialsSection from '@/shared/Components/TestimonialsSection.vue';
import TimelineSection from '@/shared/Components/TimelineSection.vue';
import { useScrollAnimation } from '@/core/composables/useScrollAnimation';

export default {
  name: 'MainPage',
  components: {
    HeroModern,
    ClientsSection,
    TestimonialsSection,
    TimelineSection,
    ContactSection,
    BenefitsGameSection,
    ExperiencePreview
  },
  setup() {
    // Scroll Animations
    useScrollAnimation();

    // Scroll Progress
    const scrollProgress = ref(0);

    const updateScrollProgress = () => {
      const windowHeight = window.innerHeight;
      const documentHeight = document.documentElement.scrollHeight;
      const scrollTop = window.scrollY;
      const scrollPercent = (scrollTop / (documentHeight - windowHeight)) * 100;
      scrollProgress.value = Math.min(scrollPercent, 100);
    };

    // Hero Data
    const heroData = {
      titleLines: [
        'Transforme Sua Empresa',
        'Com Gamificação'
      ],
      description: 'Desenvolvimento de software e gamificação com foco em resultado de negócio. Unimos tecnologia, game design e visão estratégica para criar soluções digitais que engajam usuários e geram retorno real.',
      images: [
        { 
          src: require('@/assets/img/workCards/parallelium.png'),
          alt: 'Parallelium - Web3 Gaming'
        },
        { 
          src: require('@/assets/img/workCards/goisland2.png'),
          alt: 'Go Island - Jogo Multiplayer'
        },
        { 
          src: require('@/assets/img/workCards/dreamside.png'),
          alt: 'Dreamside - RPG World'
        },
        { 
          src: require('@/assets/img/workCards/suzano-game.jpeg'),
          alt: 'Suzano Rock in Rio - Gamificação'
        },
        { 
          src: require('@/assets/img/workCards/bobs-gameficacao.png'),
          alt: 'Sistema de Eventos - Gamificação'
        },
        { 
          src: require('@/assets/img/workCards/fishbay-print.png'),
          alt: 'Fishbay - Browser Game'
        }
      ],
      stats: [
        { value: '100000', label: 'usuários impactados' },
        { value: '10', label: 'anos de experiência' }
      ],
      primaryButtonText: 'Iniciar Projeto',
      secondaryButtonText: 'Ver Portfólio'
    };

    // Clients Data
    const clientsData = [
      { name: "YDQUS", image: "ydqus.png" },
      { name: "Ponte Preta", image: "pontePreta.png" },
      { name: "Go Island", image: "goIsland.png" },
      { name: "Suzano", image: "suzano.png" },
      { name: "Softvision", image: "softvision.jpg" },
      { name: "Parallelium", image: "parallelium.png" }
    ];

    // Benefits Data
    const benefitsData = [
      {
        icon: 'icons-cart',
        title: 'Maiores taxas de conversões de vendas'
      },
      {
        icon: 'icons-growth',
        title: 'Aumento na geração de leads'
      },
      {
        icon: 'icons-viralMarketing',
        title: 'Receba mais atenção dos seus usuários'
      },
      {
        icon: 'icons-facilitySell',
        title: 'Aumento de adesão aos seus produtos'
      },
      {
        icon: 'icons-newDigital',
        title: 'Programas de indicação gamificados'
      },
      {
        icon: 'icons-work',
        title: 'Experiências interativas em campanhas e eventos'
      }
    ];

    // Portfolio Data
    const portfolioData = [
      {
        id: 'parallelium',
        image: "parallelium.png",
        name: "Parallelium",
        category: "Web3",
        subtitle: "Parallelium foi um jogo desenvolvido para o mercado Web3, onde criamos o marketplace na rede BSC, com contratos para a mintagem de NFTS e compras para o jogo, se tornando uma referência nesse mercado"
      },
      {
        id: 'go-island',
        image: "goisland2.png",
        name: "Go Island",
        category: "Jogos",
        subtitle: "Go Island é um jogo social multiplayer, focado no público infantil, com diversos minigames, como battle royale, sobrevivência, pega base e policia e ladrão, onde apresentamos na Flow Games After Party, para diversos influenciadores e pessoas importantes do mercado"
      },
      {
        id: 'dreamside',
        image: "dreamside.png",
        name: "Dreamside",
        category: "Jogos",
        subtitle: "Dreamside é o novo jogo da empresa, focado nas histórias desenvolvidas por Alan Reis, é um mundo dos sonhos, onde os jogadores podem jogar da forma que quiserem, como um taverneiro, um caçador de recompensas, ou um mago lendário, tudo é possivel em Dreamside"
      },
      {
        id: 'suzano-rock-in-rio',
        image: "suzano-game.jpeg",
        name: "Suzano Rock in Rio",
        category: "Gameficação",
        subtitle: "Trabalho de gameficação, apresentado no Rock in Rio pela Suzano, para ativação de pessoas que estavam no evento, com um jogo voltado a reciclagem e consciência ambiental"
      },
      {
        id: 'sistema-eventos',
        image: "bobs-gameficacao.png",
        name: "Sistema de Criação de Eventos",
        category: "Gameficação",
        subtitle: "Outro trabalho bem sucedido, onde criamos um sistema para criação de eventos gameficados, com quiz interativos, desafios e recompensas, onde foi um sucesso de engajamento e interação com o público"
      },
      {
        id: 'fishbay',
        image: "fishbay-print.png",
        name: "Fishbay",
        category: "Sites",
        subtitle: "Nosso lançamento mais recente, Fishbay é um game web browser, focado em criar um ambiente tranquilo e divertido"
      },
      {
        id: 'raio-x',
        image: "raiox.jpeg",
        name: "Raio X",
        category: "Realidade_Aumentada",
        subtitle: "Raio X é um conceito de tecnologia que desenvolvemos para a Descomplique Eventos, onde as pessoas andam com a tela e ela interage com o banner de fundo, apresentando informações referentes a posição que ela está"
      },
      {
        id: 'mascote-ponte-preta',
        image: "gorila.jpeg",
        name: "Mascote Ponte Preta",
        category: "Realidade_Aumentada",
        subtitle: "AR desenvolvido para apresentar novos projetos com o mascote para o Museu do Ponte Preta"
      }
    ];

    const currentProject = ref(portfolioData[0]);

    // Testimonials Data
    const testimonialsData = [
      {
        name: 'Marcos Maziero',
        position: 'CEO',
        company: 'Gameshark',
        comment: 'Trabalhamos com a Unli no desenvolvimento do nosso antigo sistema de venda de imóveis, Welchome, e também, participamos em sociedade no desenvolvimento do Parallelium, qualidade sem igual, sempre nos ajudaram com qualquer dúvida que tivessemos, recomendo'
      },
      {
        name: 'Higor Gimenez',
        position: 'CEO',
        company: 'Softvision',
        comment: 'O Alan participou do desenvolvimento do Go Island, um líder muito focado, que nos ajudou bastante com o desenvolvimento do jogo'
      },
      {
        name: 'João Antônio',
        position: 'Marketing',
        company: 'Texan Brand',
        comment: 'Contratei a empresa depois de uma recomendação de um amigo, e não me arrependo, entregaram o trabalho rápido, por um preço justo, e sempre tirando as minha dúvidas'
      },
      {
        name: 'Vinicius Viper',
        position: 'CEO',
        company: 'Descomplica Eventos',
        comment: 'Contratamos a Unli para desenvolver um novo produto para nossos clientes, deu super certo, o suporte foi excelente e conseguimos entregar um evento de sucesso'
      }
    ];

    // Services Data
    const servicesData = [
      {
        icon: 'fas fa-laptop-code',
        title: "Desenvolvimento de Software",
        items: [
          "Criação de sites",
          "Sistemas web",
          "Smart Contracts Web3",
          "Criação de criptomoedas",
          "Criação de NFTs",
          "Marketplace Web3"
        ]
      },
      {
        icon: 'fas fa-gamepad',
        title: "Gameficação",
        items: [
          "Realidade Aumentada (AR)",
          "Experiências imersivas",
          "Consultoria de gameficação para empresas"
        ]
      },
      {
        icon: 'fas fa-dice',
        title: "Jogos",
        items: [
          "Jogos para ativação em eventos",
          "Jogos educacionais",
          "Jogos mobile",
          "Jogos multiplayer"
        ]
      }
    ];

    // Process Timeline Data
    const processSteps = [
      {
        title: 'Conversa inicial e <span style="color: #e67e22">diagnóstico</span>',
        description: 'Começamos com uma conversa aprofundada para entender seu contexto, desafios e objetivos de negócio. Mais do que "o que você quer construir", buscamos identificar qual problema precisa realmente ser resolvido.'
      },
      {
        title: 'Imersão na <span style="color: #e67e22">empresa</span> e processos',
        description: 'Estudamos sua empresa, produto, público, equipe e processos internos. Analisamos como as coisas funcionam hoje para propor uma solução que se encaixe na rotina e gere resultado prático.'
      },
      {
        title: 'Desenho do <span style="color: #e67e22">MVP ideal</span>',
        description: 'A partir do diagnóstico, estruturamos um MVP (Produto Mínimo Viável) que ataca diretamente a necessidade identificada. Apresentamos escopo, funcionalidades essenciais e estimativa de esforço.'
      },
      {
        title: 'Desenvolvimento com <span style="color: #e67e22">acompanhamento</span>',
        description: 'Mantemos um ciclo de acompanhamento contínuo. Alinhamos prioridades, mostramos entregas parciais e ajustamos o rumo rapidamente sempre que necessário.'
      },
      {
        title: 'Lançamento e <span style="color: #e67e22">testes em campo</span>',
        description: 'O MVP é testado junto com você e sua equipe. Observamos o uso real, colhemos feedbacks e identificamos o que funcionou melhor e quais oportunidades surgiram.'
      },
      {
        title: 'Evolução <span style="color: #e67e22">guiada por dados</span>',
        description: 'Com base nos testes e validações, priorizamos as próximas melhorias. Implementamos apenas o que comprovadamente gera valor, evitando desperdício. O projeto cresce de forma sustentável.'
      }
    ];

    const processBenefits = [
      {
        icon: 'fas fa-rocket',
        text: 'MVP rápido que valida sua ideia sem queimar orçamento'
      },
      {
        icon: 'fas fa-chart-line',
        text: 'Crescimento baseado em dados reais, não em achismos'
      },
      {
        icon: 'fas fa-users',
        text: 'Parceria contínua: crescemos junto com seu projeto'
      }
    ];

    // Contact Data
    const contactData = [
      { icon: 'fas fa-envelope', label: 'Email', value: 'alanreis@unli.com.br' },
      { icon: 'fab fa-whatsapp', label: 'WhatsApp', value: '+55 (11) 96835-4238' }
    ];

    // Methods
    const handleStartProject = () => {
      const contactSection = document.querySelector('#contact');
      if (contactSection) {
        contactSection.scrollIntoView({ behavior: 'smooth' });
      }
    };

    const handleViewPortfolio = () => {
      const portfolioSection = document.querySelector('.portfolio-section');
      if (portfolioSection) {
        portfolioSection.scrollIntoView({ behavior: 'smooth' });
      }
    };

    const selectProject = (index) => {
      currentProject.value = portfolioData[index];
    };

    // Portfolio Autoplay
    let portfolioAutoplayTimer = null;
    let restartAutoplayTimer = null;

    const startPortfolioAutoplay = () => {
      // Limpa qualquer timer existente antes de criar um novo
      if (portfolioAutoplayTimer) {
        clearInterval(portfolioAutoplayTimer);
      }
      
      portfolioAutoplayTimer = setInterval(() => {
        const currentIndex = portfolioData.findIndex(p => p.id === currentProject.value.id);
        const nextIndex = (currentIndex + 1) % portfolioData.length;
        selectProject(nextIndex);
      }, 4000); // Troca a cada 4 segundos
    };

    const stopPortfolioAutoplay = () => {
      if (portfolioAutoplayTimer) {
        clearInterval(portfolioAutoplayTimer);
        portfolioAutoplayTimer = null;
      }
      // Também limpa o timer de restart
      if (restartAutoplayTimer) {
        clearTimeout(restartAutoplayTimer);
        restartAutoplayTimer = null;
      }
    };

    // Ao clicar manualmente, para o autoplay por 10 segundos
    const handleManualProjectSelect = (index) => {
      stopPortfolioAutoplay();
      selectProject(index);
      
      // Limpa qualquer restart pendente
      if (restartAutoplayTimer) {
        clearTimeout(restartAutoplayTimer);
      }
      
      // Agenda um novo restart
      restartAutoplayTimer = setTimeout(() => {
        startPortfolioAutoplay();
        restartAutoplayTimer = null;
      }, 10000);
    };

    onMounted(() => {
      startPortfolioAutoplay();
      window.addEventListener('scroll', updateScrollProgress, { passive: true });
      updateScrollProgress();
    });

    onUnmounted(() => {
      stopPortfolioAutoplay();
      window.removeEventListener('scroll', updateScrollProgress);
    });

    const getProjectImage = (imageName) => {
      try {
        return require(`@/assets/img/workCards/${imageName}`);
      } catch (e) {
        console.warn(`Image not found: ${imageName}`);
        return '';
      }
    };

    const handleContactSubmit = async (formData) => {
      console.log('Formulário enviado:', formData);
      
      try {
        const response = await fetch('https://unli.com.br/PHPMail/phpmail.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams({
            email: formData.email,
            telefone: formData.phone,
            assunto: 'Contato pelo site',
            mensagem: formData.message
          })
        });

        if (response.ok) {
          alert('Mensagem enviada com sucesso! Entraremos em contato em breve.');
          return { success: true };
        } else {
          throw new Error('Erro ao enviar mensagem');
        }
      } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao enviar mensagem. Tente novamente mais tarde.');
        return { success: false, error: error.message };
      }
    };

    return {
      heroData,
      clientsData,
      benefitsData,
      portfolioData,
      currentProject,
      testimonialsData,
      servicesData,
      processSteps,
      processBenefits,
      contactData,
      scrollProgress,
      handleStartProject,
      handleViewPortfolio,
      selectProject,
      handleManualProjectSelect,
      getProjectImage,
      handleContactSubmit
    };
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/_colors.scss';
@import '@/assets/sass/settings/_fonts.scss';
@import '@/assets/sass/settings/_keyframes.scss';

.main-page-modern {
  width: 100%;
  overflow-x: hidden;
}

// Scroll Progress Bar
.scroll-progress-bar {
  position: fixed;
  top: 0;
  left: 0;
  height: 4px;
  background: $gradient-primary;
  z-index: 9999;
  transition: width 0.2s ease-out;
  box-shadow: 0 0 10px rgba($p-color, 0.5);
}

// Benefits Section
.benefits-section {
  padding: 6rem 2rem;
  background: $gray-lightness;

  @media (max-width: 768px) {
    padding: 4rem 1rem;
  }
}

.benefits-container {
  max-width: 1400px;
  margin: 0 auto;
}

.benefits-header {
  text-align: center;
  margin-bottom: 4rem;

  .section-badge {
    display: inline-block;
    padding: 0.5rem 1.25rem;
    background: rgba($p-color, 0.1);
    color: $p-color;
    font-family: $font-primary;
    font-size: $text-sm;
    font-weight: $weight-semibold;
    border-radius: 50px;
    margin-bottom: 1.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .section-title {
    font-family: $font-secondary;
    font-size: $text-display-sm;
    font-weight: $weight-bold;
    color: $gray-darkness;
    margin: 0 0 1rem;
    line-height: 1.2;

    @media (max-width: 768px) {
      font-size: $text-h1;
    }
  }

  .section-description {
    font-family: $font-primary;
    font-size: $text-lg;
    color: $gray-medium;
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.6;

    strong {
      color: $p-color;
      font-weight: $weight-semibold;
    }

    @media (max-width: 768px) {
      font-size: $text-base;
    }
  }
}

.benefits-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
}

.benefit-card {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding: 2rem;
  background: $white;
  border-radius: 1rem;
  border: 1px solid $gray-light;
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-4px);
    box-shadow: $shadow-lg;
    border-color: rgba($p-color, 0.3);

    .benefit-icon {
      transform: scale(1.1);
    }
  }
}

.benefit-icon {
  width: 60px;
  height: 60px;
  background: $gradient-primary;
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  transition: transform 0.3s ease;

  i {
    font-size: 28px;
    color: $white;
  }
}

.benefit-title {
  font-family: $font-primary;
  font-size: $text-base;
  font-weight: $weight-medium;
  color: $gray-darkness;
  margin: 0;
  line-height: 1.4;
}

// Portfolio Section
.portfolio-section {
  padding: 6rem 2rem;
  background: $white;

  @media (max-width: 768px) {
    padding: 4rem 1rem;
  }
}

.portfolio-container {
  max-width: 1400px;
  margin: 0 auto;
}

.portfolio-header {
  text-align: center;
  margin-bottom: 4rem;

  .section-badge {
    display: inline-block;
    padding: 0.5rem 1.25rem;
    background: rgba($p-color, 0.1);
    color: $p-color;
    font-family: $font-primary;
    font-size: $text-sm;
    font-weight: $weight-semibold;
    border-radius: 50px;
    margin-bottom: 1.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .section-title {
    font-family: $font-secondary;
    font-size: $text-display-sm;
    font-weight: $weight-bold;
    color: $gray-darkness;
    margin: 0 0 1rem;

    @media (max-width: 768px) {
      font-size: $text-h1;
    }
  }

  .section-description {
    font-family: $font-primary;
    font-size: $text-lg;
    color: $gray-medium;
    max-width: 600px;
    margin: 0 auto;
  }
}

.featured-project {
  display: grid;
  grid-template-columns: 1.5fr 1fr;
  gap: 3rem;
  margin-bottom: 3rem;
  background: $gray-lightness;
  border-radius: 1.5rem;
  overflow: hidden;
  animation: fadeSlideIn 0.6s ease-out;

  @media (max-width: 992px) {
    grid-template-columns: 1fr;
  }
}

.project-image {
  height: 500px;
  background-size: cover;
  background-position: center;
  position: relative;
  animation: scaleIn 0.6s ease-out;

  @media (max-width: 992px) {
    height: 350px;
  }

  @media (max-width: 768px) {
    height: 250px;
  }
}

.project-info {
  padding: 3rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
  animation: slideInRight 0.6s ease-out;

  @media (max-width: 992px) {
    padding: 2rem;
  }

  @media (max-width: 768px) {
    padding: 1.5rem;
  }

  h3 {
    font-family: $font-secondary;
    font-size: $text-h2;
    font-weight: $weight-bold;
    color: $gray-darkness;
    margin: 0 0 1rem;

    @media (max-width: 768px) {
      font-size: $text-h3;
    }
  }

  p {
    font-family: $font-primary;
    font-size: $text-base;
    color: $gray-medium;
    line-height: 1.7;
    margin: 0 0 2rem;
  }
}

.project-category {
  position: absolute;
  top: 1.5rem;
  left: 1.5rem;
  padding: 0.5rem 1rem;
  background: rgba($white, 0.95);
  color: $p-color;
  font-family: $font-primary;
  font-size: $text-xs;
  font-weight: $weight-semibold;
  border-radius: 50px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.project-info {
  padding: 3rem;
  display: flex;
  flex-direction: column;
  justify-content: center;

  @media (max-width: 768px) {
    padding: 2rem;
  }

  h3 {
    font-family: $font-secondary;
    font-size: $text-h2;
    font-weight: $weight-bold;
    color: $gray-darkness;
    margin: 0 0 1rem;

    @media (max-width: 768px) {
      font-size: $text-h3;
    }
  }

  p {
    font-family: $font-primary;
    font-size: $text-base;
    color: $gray-medium;
    line-height: 1.6;
    margin-bottom: 2rem;
  }
}

.btn-details {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  background: $gradient-primary;
  color: $white;
  font-family: $font-primary;
  font-size: $text-sm;
  font-weight: $weight-semibold;
  text-decoration: none;
  border-radius: 50px;
  align-self: flex-start;
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
}

.projects-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1.5rem;

  @media (max-width: 768px) {
    grid-template-columns: repeat(2, 1fr);
  }
}

.project-item {
  cursor: pointer;
  border-radius: 1rem;
  overflow: hidden;
  background: $white;
  border: 2px solid $gray-light;
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-4px);
    box-shadow: $shadow-md;
    border-color: rgba($p-color, 0.3);
  }

  &.active {
    border-color: $p-color;
    box-shadow: 0 0 0 3px rgba($p-color, 0.1);
  }
}

.project-thumbnail {
  height: 150px;
  background-size: cover;
  background-position: center;
}

.project-name {
  display: block;
  padding: 1rem;
  font-family: $font-primary;
  font-size: $text-sm;
  font-weight: $weight-medium;
  color: $gray-darkness;
  text-align: center;
}

// Services Section
.services-section {
  padding: 6rem 2rem;
  background: $gray-lightness;

  @media (max-width: 768px) {
    padding: 4rem 1rem;
  }
}

.services-container {
  max-width: 1400px;
  margin: 0 auto;
}

.services-header {
  text-align: center;
  margin-bottom: 4rem;

  .section-badge {
    display: inline-block;
    padding: 0.5rem 1.25rem;
    background: rgba($p-color, 0.1);
    color: $p-color;
    font-family: $font-primary;
    font-size: $text-sm;
    font-weight: $weight-semibold;
    border-radius: 50px;
    margin-bottom: 1.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .section-title {
    font-family: $font-secondary;
    font-size: $text-display-sm;
    font-weight: $weight-bold;
    color: $gray-darkness;
    margin: 0 0 1rem;

    @media (max-width: 768px) {
      font-size: $text-h1;
    }
  }

  .section-description {
    font-family: $font-primary;
    font-size: $text-lg;
    color: $gray-medium;
    max-width: 600px;
    margin: 0 auto;
  }
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2rem;

  @media (max-width: 768px) {
    grid-template-columns: 1fr;
  }
}

.service-card {
  padding: 3rem;
  background: $white;
  border-radius: 1.5rem;
  border: 1px solid $gray-light;
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-8px);
    box-shadow: $shadow-xl;
    border-color: rgba($p-color, 0.3);

    .service-icon {
      background: $gradient-primary;
      transform: scale(1.1);

      i {
        color: $white;
      }
    }
  }
}

.service-icon {
  width: 70px;
  height: 70px;
  background: rgba($p-color, 0.1);
  border-radius: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.5rem;
  transition: all 0.3s ease;

  i {
    font-size: 32px;
    color: $p-color;
    transition: color 0.3s ease;
  }
}

.service-title {
  font-family: $font-secondary;
  font-size: $text-h4;
  font-weight: $weight-semibold;
  color: $gray-darkness;
  margin: 0 0 1.5rem;
}

.service-list {
  list-style: none;
  padding: 0;
  margin: 0;

  li {
    font-family: $font-primary;
    font-size: $text-base;
    color: $gray-medium;
    padding: 0.75rem 0;
    padding-left: 1.5rem;
    position: relative;

    &::before {
      content: '→';
      position: absolute;
      left: 0;
      color: $p-color;
      font-weight: bold;
    }
  }
}

// CTA Section
.cta-section {
  padding: 6rem 2rem;
  background: $gradient-primary;
  position: relative;
  overflow: hidden;

  &::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba($white, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 6s ease-in-out infinite;
  }

  &::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba($white, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    animation: float 8s ease-in-out infinite reverse;
  }

  @media (max-width: 768px) {
    padding: 4rem 1rem;
  }
}

.cta-container {
  max-width: 900px;
  margin: 0 auto;
  position: relative;
  z-index: 1;
}

.cta-content {
  text-align: center;
}

.cta-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: rgba($white, 0.2);
  backdrop-filter: blur(10px);
  color: $white;
  font-family: $font-primary;
  font-size: $text-sm;
  font-weight: $weight-semibold;
  border-radius: 50px;
  margin-bottom: 2rem;
  border: 1px solid rgba($white, 0.3);

  i {
    font-size: 18px;
  }
}

.cta-title {
  font-family: $font-secondary;
  font-size: $text-display-sm;
  font-weight: $weight-bold;
  color: $white;
  margin: 0 0 1.5rem;
  line-height: 1.2;

  @media (max-width: 768px) {
    font-size: $text-h1;
  }
}

.cta-description {
  font-family: $font-primary;
  font-size: $text-lg;
  color: rgba($white, 0.95);
  line-height: 1.8;
  margin: 0 0 3rem;
  max-width: 700px;
  margin-left: auto;
  margin-right: auto;

  @media (max-width: 768px) {
    font-size: $text-base;
    margin-bottom: 2rem;
  }
}

.cta-actions {
  display: flex;
  gap: 1.5rem;
  justify-content: center;
  margin-bottom: 4rem;

  @media (max-width: 768px) {
    flex-direction: column;
    gap: 1rem;
  }
}

.btn-cta-primary,
.btn-cta-secondary {
  display: inline-flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1.25rem 2.5rem;
  font-family: $font-primary;
  font-size: $text-base;
  font-weight: $weight-semibold;
  text-decoration: none;
  border-radius: 50px;
  transition: all 0.3s ease;
  border: 2px solid transparent;

  i {
    font-size: 20px;
  }

  @media (max-width: 768px) {
    padding: 1rem 2rem;
    justify-content: center;
  }
}

.btn-cta-primary {
  background: $white;
  color: $p-color;

  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba($black, 0.2);
  }
}

.btn-cta-secondary {
  background: transparent;
  color: $white;
  border-color: $white;

  &:hover {
    background: $white;
    color: $p-color;
    transform: translateY(-4px);
  }
}

.cta-stats {
  display: flex;
  justify-content: center;
  gap: 3rem;
  padding-top: 3rem;
  border-top: 1px solid rgba($white, 0.2);

  @media (max-width: 768px) {
    flex-direction: column;
    gap: 1.5rem;
    text-align: center;
  }
}

.cta-stat {
  display: flex;
  align-items: center;
  gap: 1rem;
  color: $white;
  font-family: $font-primary;
  font-size: $text-base;
  font-weight: $weight-medium;

  i {
    font-size: 24px;
    color: rgba($white, 0.8);
  }

  @media (max-width: 768px) {
    justify-content: center;
  }
}

// CEO Interview Section
.ceo-section {
  padding: 6rem 2rem;
  background: $gray-lightness;
  position: relative;

  @media (max-width: 768px) {
    padding: 4rem 1rem;
  }
}

.ceo-container {
  max-width: 1200px;
  margin: 0 auto;
}

.ceo-header {
  text-align: center;
  margin-bottom: 4rem;

  .section-title {
    font-family: $font-secondary;
    font-size: $text-display-sm;
    font-weight: $weight-bold;
    color: $gray-darkness;
    margin: 0 0 1rem;
    line-height: 1.2;

    @media (max-width: 768px) {
      font-size: $text-h1;
    }
  }

  .section-description {
    font-family: $font-primary;
    font-size: $text-lg;
    color: $gray-medium;
    max-width: 700px;
    margin: 0 auto;
    line-height: 1.6;

    @media (max-width: 768px) {
      font-size: $text-base;
    }
  }
}

.video-wrapper {
  position: relative;
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
  padding-bottom: 56.25%; // 16:9 aspect ratio
  height: 0;
  overflow: hidden;
  border-radius: 1.5rem;
  box-shadow: $shadow-lg;

  iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
    border-radius: 1.5rem;
  }
}

// ==========================================
// SCROLL ANIMATIONS
// ==========================================

// Elementos com data-scroll começam invisíveis
[data-scroll] {
  opacity: 0;
  transform: translateY(40px);
  transition: opacity 0.8s ease-out, transform 0.8s ease-out;

  &.animate-in {
    opacity: 1;
    transform: translateY(0);
  }
}

// Elementos com data-stagger (animação sequencial)
[data-stagger] {
  opacity: 0;
  transform: translateY(30px) scale(0.95);
  transition: opacity 0.6s ease-out, transform 0.6s ease-out;

  &.animate-in {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

// Animações específicas para cards
.benefit-card,
.service-card,
.project-item {
  &:hover {
    animation: float 3s ease-in-out infinite;
  }
}

@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-8px);
  }
}

// Animação para ícones
.benefit-icon,
.service-icon {
  position: relative;
  
  &::before {
    content: '';
    position: absolute;
    top: -5px;
    left: -5px;
    right: -5px;
    bottom: -5px;
    background: $gradient-primary;
    border-radius: 1rem;
    opacity: 0;
    z-index: -1;
    transition: opacity 0.3s ease;
  }

  &:hover::before {
    opacity: 0.2;
    animation: pulse 2s ease-in-out infinite;
  }
}

@keyframes pulse {
  0%, 100% {
    transform: scale(1);
    opacity: 0.2;
  }
  50% {
    transform: scale(1.1);
    opacity: 0.3;
  }
}

// Efeito parallax suave nos badges
.section-badge {
  position: relative;
  
  &::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba($p-color, 0.2), transparent);
    border-radius: 50px;
    opacity: 0;
    transition: opacity 0.3s ease;
  }

  &:hover::after {
    opacity: 1;
  }
}

// Animação de entrada para o portfólio
.featured-project {
  .project-image {
    position: relative;
    overflow: hidden;

    &::after {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba($white, 0.3), transparent);
      transition: left 0.5s ease;
    }

    &:hover::after {
      left: 100%;
    }
  }
}

// Animação para dots do slider de projetos
.project-item.active {
  animation: activeProject 0.5s ease-out;
}

@keyframes activeProject {
  0% {
    transform: scale(0.9);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

// Melhoria: Adicionar sombra dinâmica nos cards
.service-card,
.benefit-card {
  position: relative;
  
  &::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: inherit;
    box-shadow: 0 20px 40px rgba($p-color, 0);
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
    z-index: -1;
  }

  &:hover::after {
    opacity: 0.3;
  }
}

// Animação para video wrapper
.video-wrapper {
  &:hover {
    transform: scale(1.02);
    transition: transform 0.3s ease;
  }
}

// Animações para troca de projeto
@keyframes fadeSlideIn {
  0% {
    opacity: 0;
    transform: translateY(20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes scaleIn {
  0% {
    opacity: 0;
    transform: scale(0.95);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

@keyframes slideInRight {
  0% {
    opacity: 0;
    transform: translateX(30px);
  }
  100% {
    opacity: 1;
    transform: translateX(0);
  }
}
</style>
