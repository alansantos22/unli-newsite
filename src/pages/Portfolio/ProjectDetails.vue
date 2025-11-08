<template>
  <div class="project-details-page">
    <div v-if="project" class="project-container">
      <!-- Hero Section -->
      <section class="project-hero">
        <div class="hero-content">
          <router-link to="/" class="back-link">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
              <path d="M15 10H5M5 10L10 15M5 10L10 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Voltar ao Portfólio
          </router-link>
          
          <span class="project-category" :class="project.category.toLowerCase()">
            {{ project.category.replace('_', ' ') }}
          </span>
          
          <h1 class="project-title">{{ project.name }}</h1>
          <p class="project-subtitle">{{ project.subtitle }}</p>
        </div>

        <div class="hero-image">
          <img 
            :src="getProjectImage(project.image)" 
            :alt="project.name"
            class="main-image"
          />
        </div>
      </section>

      <!-- Project Info -->
      <section class="project-info">
        <div class="info-container">
          <div class="info-main">
            <h2>Sobre o Projeto</h2>
            <p>{{ project.description || project.subtitle }}</p>

            <div v-if="project.details" class="project-details">
              <h3>Detalhes Técnicos</h3>
              <ul>
                <li v-for="(detail, index) in project.details" :key="index">
                  {{ detail }}
                </li>
              </ul>
            </div>

            <div v-if="project.technologies" class="project-tech">
              <h3>Tecnologias Utilizadas</h3>
              <div class="tech-tags">
                <span v-for="tech in project.technologies" :key="tech" class="tech-tag">
                  {{ tech }}
                </span>
              </div>
            </div>

            <div v-if="project.link" class="project-link">
              <a :href="project.link" target="_blank" rel="noopener noreferrer" class="btn-visit">
                <span>Visitar Projeto</span>
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path d="M6 3H3v10h10v-3M9 2h5v5M7 9l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </a>
            </div>
          </div>

          <div class="info-sidebar">
            <div class="info-card">
              <h4>Cliente</h4>
              <p>{{ project.client || 'UNLI Studio' }}</p>
            </div>

            <div class="info-card">
              <h4>Categoria</h4>
              <p>{{ project.category.replace('_', ' ') }}</p>
            </div>

            <div v-if="project.year" class="info-card">
              <h4>Ano</h4>
              <p>{{ project.year }}</p>
            </div>

            <div v-if="project.platform" class="info-card">
              <h4>Plataforma</h4>
              <p>{{ project.platform }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Gallery -->
      <section v-if="project.gallery && project.gallery.length" class="project-gallery">
        <div class="gallery-container">
          <h2>Galeria</h2>
          <div class="gallery-grid">
            <div v-for="(image, index) in project.gallery" :key="index" class="gallery-item">
              <img :src="getProjectImage(image)" :alt="`${project.name} - ${index + 1}`" />
            </div>
          </div>
        </div>
      </section>

      <!-- CTA -->
      <section class="project-cta">
        <div class="cta-content">
          <h2>Gostou do projeto?</h2>
          <p>Entre em contato e vamos criar algo incrível juntos!</p>
          <router-link to="/#contact" class="btn-cta">
            Iniciar Meu Projeto
          </router-link>
        </div>
      </section>
    </div>

    <div v-else class="project-not-found">
      <h2>Projeto não encontrado</h2>
      <router-link to="/" class="btn-back">Voltar para Home</router-link>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

export default {
  name: 'ProjectDetails',
  setup() {
    const route = useRoute();
    const project = ref(null);

    // Lista completa de projetos (mesma do MainPage)
    const allProjects = [
      {
        id: 'parallelium',
        image: "parallelium.png",
        name: "Parallelium",
        category: "Web3",
        subtitle: "Parallelium foi um jogo desenvolvido para o mercado Web3, onde criamos o marketplace na rede BSC, com contratos para a mintagem de NFTS e compras para o jogo, se tornando uma referência nesse mercado",
        description: "Parallelium é um projeto completo de jogo Web3 que revolucionou o mercado de NFTs gaming. Desenvolvemos toda a infraestrutura blockchain, smart contracts e marketplace.",
        technologies: ["Phaser", "Solidity", "VueJS", "Node.js", "BSC"],
        year: "2022",
        platform: "PC, Web",
        client: "UNLI Studio"
      },
      {
        id: 'go-island',
        image: "goisland2.png",
        name: "Go Island",
        category: "Jogos",
        subtitle: "Go Island é um jogo social multiplayer, focado no público infantil, com diversos minigames, como battle royale, sobrevivência, pega base e policia e ladrão, onde apresentamos na Flow Games After Party, para diversos influenciadores e pessoas importantes do mercado",
        description: "Um MMO social voltado para crianças com múltiplos minigames e experiências sociais. Apresentado na Flow Games After Party com grande sucesso.",
        technologies: ["Unity", "Photon", "C#", "Blender"],
        year: "2023",
        platform: "PC, Mobile",
        client: "UNLI Studio"
      },
      {
        id: 'dreamside',
        image: "dreamside.png",
        name: "Dreamside",
        category: "Jogos",
        subtitle: "Dreamside é o novo jogo da empresa, focado nas histórias desenvolvidas por Alan Reis, é um mundo dos sonhos, onde os jogadores podem jogar da forma que quiserem, como um taverneiro, um caçador de recompensas, ou um mago lendário, tudo é possivel em Dreamside",
        description: "Um RPG de mundo aberto ambientado no mundo dos sonhos, onde os jogadores têm liberdade total para escolher sua jornada e profissão.",
        technologies: ["Unity", "C#"],
        year: "2024",
        platform: "PC",
        client: "UNLI Studio"
      },
      {
        id: 'suzano-rock-in-rio',
        image: "suzano-game.jpeg",
        name: "Suzano Rock in Rio",
        category: "Gameficação",
        subtitle: "Trabalho de gameficação, apresentado no Rock in Rio pela Suzano, para ativação de pessoas que estavam no evento, com um jogo voltado a reciclagem e consciência ambiental",
        description: "Projeto de gamificação desenvolvido para a Suzano no Rock in Rio, focado em conscientização ambiental através de jogos interativos.",
        technologies: ["VueJS", "PHP"],
        year: "2023",
        platform: "Totem Interativo",
        client: "Suzano"
      },
      {
        id: 'sistema-eventos',
        image: "bobs-gameficacao.png",
        name: "Sistema de Criação de Eventos",
        category: "Gameficação",
        subtitle: "Outro trabalho bem sucedido, onde criamos um sistema para criação de eventos gameficados, com quiz interativos, desafios e recompensas, onde foi um sucesso de engajamento e interação com o público",
        description: "Plataforma completa para criação e gerenciamento de eventos gamificados com quizzes, desafios e sistema de recompensas.",
        technologies: ["Vue.js", "Node.js", "MongoDB", "Socket.io"],
        year: "2023",
        platform: "Web, Mobile",
        client: "Diversos"
      },
      {
        id: 'fishbay',
        image: "fishbay-print.png",
        name: "Fishbay",
        category: "Sites",
        subtitle: "Nosso lançamento mais recente, Fishbay é um game web browser, focado em criar um ambiente tranquilo e divertido",
        description: "Jogo casual browser focado em proporcionar uma experiência relaxante de pesca virtual.",
        technologies: ["Phaser", "Vue.js", "Node.js"],
        year: "2024",
        platform: "Web Browser",
        client: "UNLI Studio"
      },
      {
        id: 'raio-x',
        image: "raiox.jpeg",
        name: "Raio X",
        category: "Realidade_Aumentada",
        subtitle: "Raio X é um conceito de tecnologia que desenvolvemos para a Descomplique Eventos, onde as pessoas andam com a tela e ela interage com o banner de fundo, apresentando informações referentes a posição que ela está",
        description: "Solução inovadora de AR que permite interação com banners físicos através de smartphones, revelando conteúdo digital contextual.",
        technologies: ["Unity", "AR Foundation", "C#", "Vuforia"],
        year: "2023",
        platform: "Mobile (iOS/Android)",
        client: "Descomplique Eventos"
      },
      {
        id: 'mascote-ponte-preta',
        image: "gorila.jpeg",
        name: "Mascote Ponte Preta",
        category: "Realidade_Aumentada",
        subtitle: "AR desenvolvido para apresentar novos projetos com o mascote para o Museu do Ponte Preta",
        description: "Experiência de realidade aumentada para o museu do Ponte Preta, trazendo o mascote do clube à vida através de AR.",
        technologies: ["Unity", "AR Foundation", "C#"],
        year: "2023",
        platform: "Mobile (iOS/Android)",
        client: "Ponte Preta FC"
      }
    ];

    onMounted(() => {
      const projectId = route.params.id;
      project.value = allProjects.find(p => p.id === projectId);

      if (!project.value) {
        console.warn('Projeto não encontrado:', projectId);
      }
    });

    const getProjectImage = (imageName) => {
      try {
        return require(`@/assets/img/workCards/${imageName}`);
      } catch (e) {
        console.warn(`Image not found: ${imageName}`);
        return '';
      }
    };

    return {
      project,
      getProjectImage
    };
  }
};
</script>

<style lang="scss" scoped>
@import '@/assets/sass/settings/_colors.scss';
@import '@/assets/sass/settings/_fonts.scss';

.project-details-page {
  min-height: 100vh;
  padding-top: 80px;
}

// Hero Section
.project-hero {
  padding: 4rem 2rem;
  background: $gray-lightness;

  @media (max-width: 768px) {
    padding: 3rem 1rem;
  }
}

.hero-content {
  max-width: 1200px;
  margin: 0 auto 3rem;
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: $gray-medium;
  text-decoration: none;
  font-family: $font-primary;
  font-size: $text-sm;
  font-weight: $weight-medium;
  margin-bottom: 2rem;
  transition: all 0.3s ease;

  &:hover {
    color: $p-color;
    transform: translateX(-4px);
  }
}

.project-category {
  display: inline-block;
  padding: 0.5rem 1rem;
  background: rgba($p-color, 0.1);
  color: $p-color;
  font-family: $font-primary;
  font-size: $text-xs;
  font-weight: $weight-semibold;
  border-radius: 50px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 1.5rem;
}

.project-title {
  font-family: $font-secondary;
  font-size: $text-display-md;
  font-weight: $weight-bold;
  color: $gray-darkness;
  margin: 0 0 1rem;

  @media (max-width: 768px) {
    font-size: $text-display-sm;
  }
}

.project-subtitle {
  font-family: $font-primary;
  font-size: $text-lg;
  color: $gray-medium;
  line-height: 1.6;
  max-width: 800px;
}

.hero-image {
  max-width: 1200px;
  margin: 0 auto;
  border-radius: 1.5rem;
  overflow: hidden;
  box-shadow: $shadow-2xl;

  .main-image {
    width: 100%;
    height: auto;
    display: block;
  }
}

// Project Info
.project-info {
  padding: 6rem 2rem;

  @media (max-width: 768px) {
    padding: 4rem 1rem;
  }
}

.info-container {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 4rem;

  @media (max-width: 992px) {
    grid-template-columns: 1fr;
    gap: 3rem;
  }
}

.info-main {
  h2 {
    font-family: $font-secondary;
    font-size: $text-h2;
    font-weight: $weight-bold;
    color: $gray-darkness;
    margin-bottom: 1.5rem;
  }

  h3 {
    font-family: $font-secondary;
    font-size: $text-h4;
    font-weight: $weight-semibold;
    color: $gray-darkness;
    margin: 2rem 0 1rem;
  }

  p {
    font-family: $font-primary;
    font-size: $text-base;
    color: $gray-medium;
    line-height: 1.8;
  }

  ul {
    list-style: none;
    padding: 0;

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
}

.tech-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-top: 1rem;
}

.tech-tag {
  padding: 0.5rem 1rem;
  background: rgba($p-color, 0.1);
  color: $p-color;
  font-family: $font-primary;
  font-size: $text-sm;
  font-weight: $weight-medium;
  border-radius: 50px;
}

.btn-visit {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem 2rem;
  background: $gradient-primary;
  color: $white;
  font-family: $font-primary;
  font-size: $text-base;
  font-weight: $weight-semibold;
  text-decoration: none;
  border-radius: 50px;
  margin-top: 2rem;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba($p-color, 0.2);

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba($p-color, 0.3);
  }
}

.info-sidebar {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.info-card {
  padding: 1.5rem;
  background: $gray-lightness;
  border-radius: 1rem;
  border: 1px solid $gray-light;

  h4 {
    font-family: $font-primary;
    font-size: $text-sm;
    font-weight: $weight-semibold;
    color: $gray-medium;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 0 0.5rem;
  }

  p {
    font-family: $font-secondary;
    font-size: $text-lg;
    font-weight: $weight-semibold;
    color: $gray-darkness;
    margin: 0;
  }
}

// Gallery
.project-gallery {
  padding: 6rem 2rem;
  background: $gray-lightness;

  @media (max-width: 768px) {
    padding: 4rem 1rem;
  }
}

.gallery-container {
  max-width: 1200px;
  margin: 0 auto;

  h2 {
    font-family: $font-secondary;
    font-size: $text-h2;
    font-weight: $weight-bold;
    color: $gray-darkness;
    margin-bottom: 3rem;
    text-align: center;
  }
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.gallery-item {
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: $shadow-md;
  transition: transform 0.3s ease, box-shadow 0.3s ease;

  &:hover {
    transform: translateY(-8px);
    box-shadow: $shadow-xl;
  }

  img {
    width: 100%;
    height: auto;
    display: block;
  }
}

// CTA Section
.project-cta {
  padding: 6rem 2rem;
  background: $gray-darkness;
  text-align: center;

  @media (max-width: 768px) {
    padding: 4rem 1rem;
  }
}

.cta-content {
  max-width: 600px;
  margin: 0 auto;

  h2 {
    font-family: $font-secondary;
    font-size: $text-display-sm;
    font-weight: $weight-bold;
    color: $white;
    margin-bottom: 1rem;

    @media (max-width: 768px) {
      font-size: $text-h2;
    }
  }

  p {
    font-family: $font-primary;
    font-size: $text-lg;
    color: $gray-light;
    margin-bottom: 2rem;
  }
}

.btn-cta {
  display: inline-block;
  padding: 1rem 2.5rem;
  background: $gradient-primary;
  color: $white;
  font-family: $font-primary;
  font-size: $text-base;
  font-weight: $weight-semibold;
  text-decoration: none;
  border-radius: 50px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba($p-color, 0.3);

  &:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba($p-color, 0.4);
  }
}

// Not Found
.project-not-found {
  text-align: center;
  padding: 6rem 2rem;

  h2 {
    font-family: $font-secondary;
    font-size: $text-display-sm;
    color: $gray-darkness;
    margin-bottom: 2rem;
  }
}

.btn-back {
  display: inline-block;
  padding: 1rem 2rem;
  background: $p-color;
  color: $white;
  font-family: $font-primary;
  font-weight: $weight-semibold;
  text-decoration: none;
  border-radius: 50px;
  transition: all 0.3s ease;

  &:hover {
    background: $p-dark;
    transform: translateY(-2px);
  }
}
</style>
