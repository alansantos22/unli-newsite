<template>
  <div class="privacy-page">
    <!-- Header Banner -->
    <section class="privacy-hero">
      <div class="privacy-hero__container">
        <div class="privacy-hero__badge">
          <i class="fas fa-shield-alt"></i>
          Transparência & Segurança
        </div>
        <h1 class="privacy-hero__title">Política de Privacidade</h1>
        <p class="privacy-hero__subtitle">
          Saiba como a Unli Studios coleta, utiliza e protege as suas informações.
        </p>
        <p class="privacy-hero__date">
          <i class="fas fa-calendar-alt"></i>
          Última atualização: março de 2026
        </p>
      </div>
    </section>

    <!-- Content -->
    <section class="privacy-content">
      <div class="privacy-content__container">

        <!-- Intro -->
        <div class="privacy-intro">
          <p>
            Esta Política de Privacidade descreve como a <strong>Unli Studios</strong> coletará,
            usará e protegerá as informações fornecidas por você através dos nossos formulários
            de contato e anúncios no Meta (Facebook e Instagram).
          </p>
        </div>

        <!-- Section 1 -->
        <div class="privacy-section">
          <div class="privacy-section__header">
            <span class="privacy-section__number">01</span>
            <h2 class="privacy-section__title">Coleta de Dados</h2>
          </div>
          <p class="privacy-section__text">
            Ao preencher nosso formulário de interesse, coletamos as seguintes informações:
          </p>
          <ul class="privacy-list">
            <li>
              <i class="fas fa-user"></i>
              <div>
                <strong>Nome</strong>
                <span>Para identificação e personalização do atendimento.</span>
              </div>
            </li>
            <li>
              <i class="fas fa-envelope"></i>
              <div>
                <strong>E-mail e WhatsApp</strong>
                <span>Para entrarmos em contato e enviarmos a proposta solicitada.</span>
              </div>
            </li>
            <li>
              <i class="fas fa-briefcase"></i>
              <div>
                <strong>Informações sobre o seu negócio</strong>
                <span>Para analisarmos a viabilidade técnica e planejarmos a estrutura do seu site.</span>
              </div>
            </li>
          </ul>
        </div>

        <!-- Section 2 -->
        <div class="privacy-section">
          <div class="privacy-section__header">
            <span class="privacy-section__number">02</span>
            <h2 class="privacy-section__title">Finalidade do Uso</h2>
          </div>
          <p class="privacy-section__text">Seus dados são utilizados exclusivamente para:</p>
          <ul class="privacy-list">
            <li>
              <i class="fas fa-search"></i>
              <div>
                <span>Realizar uma análise prévia do seu modelo de negócio.</span>
              </div>
            </li>
            <li>
              <i class="fas fa-comments"></i>
              <div>
                <span>Entrar em contato para apresentar soluções de criação de sites e hospedagem.</span>
              </div>
            </li>
            <li>
              <i class="fas fa-bell"></i>
              <div>
                <span>Enviar atualizações sobre o andamento da sua solicitação.</span>
              </div>
            </li>
          </ul>
          <div class="privacy-highlight">
            <i class="fas fa-lock"></i>
            <p>
              <strong>Importante:</strong> Nós não vendemos, alugamos ou compartilhamos seus dados
              com terceiros para fins publicitários.
            </p>
          </div>
        </div>

        <!-- Section 3 -->
        <div class="privacy-section">
          <div class="privacy-section__header">
            <span class="privacy-section__number">03</span>
            <h2 class="privacy-section__title">Armazenamento e Segurança</h2>
          </div>
          <p class="privacy-section__text">
            Seus dados são armazenados em ambiente seguro e acessados apenas por nossa equipe
            comercial e técnica para os fins descritos acima. Mantemos os dados apenas pelo tempo
            necessário para cumprir a finalidade da coleta ou conforme exigido por lei.
          </p>
        </div>

        <!-- Section 4 -->
        <div class="privacy-section">
          <div class="privacy-section__header">
            <span class="privacy-section__number">04</span>
            <h2 class="privacy-section__title">Seus Direitos (LGPD)</h2>
          </div>
          <p class="privacy-section__text">
            De acordo com a Lei Geral de Proteção de Dados (LGPD), você tem o direito de:
          </p>
          <ul class="privacy-list">
            <li>
              <i class="fas fa-check-circle"></i>
              <div>
                <span>Confirmar a existência do tratamento de seus dados.</span>
              </div>
            </li>
            <li>
              <i class="fas fa-edit"></i>
              <div>
                <span>Acessar, corrigir ou atualizar suas informações.</span>
              </div>
            </li>
            <li>
              <i class="fas fa-trash-alt"></i>
              <div>
                <span>Solicitar a exclusão definitiva dos seus dados de nossa base a qualquer momento.</span>
              </div>
            </li>
          </ul>
        </div>

        <!-- Section 5 -->
        <div class="privacy-section">
          <div class="privacy-section__header">
            <span class="privacy-section__number">05</span>
            <h2 class="privacy-section__title">Alterações nesta Política</h2>
          </div>
          <p class="privacy-section__text">
            Podemos atualizar esta política ocasionalmente para refletir mudanças em nossos
            processos ou por razões legais. Recomendamos a leitura periódica.
          </p>
        </div>

        <!-- Section 6 -->
        <div class="privacy-section">
          <div class="privacy-section__header">
            <span class="privacy-section__number">06</span>
            <h2 class="privacy-section__title">Contato</h2>
          </div>
          <p class="privacy-section__text">
            Para qualquer dúvida sobre seus dados ou para exercer seus direitos, entre em contato
            conosco através do nosso canal de atendimento oficial.
          </p>
        </div>

        <!-- Footer Signature -->
        <div class="privacy-signature">
          <div class="privacy-signature__icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <div class="privacy-signature__text">
            <strong>Unli Studios</strong>
            <span>Comprometidos com a transparência e a segurança dos seus dados.</span>
          </div>
        </div>

      </div>
    </section>
  </div>
</template>

<script>
export default {
  name: 'PrivacyPolicy',
  metaInfo: {
    title: 'Política de Privacidade | Unli Studios'
  }
}
</script>

<style lang="scss" scoped>
@import "@/assets/sass/settings/__colors";
@import "@/assets/sass/settings/__fonts";
@import "@/assets/sass/settings/__breakpoints";

.privacy-page {
  background-color: $black;
  min-height: 100vh;
  color: $white;
}

// ========================
// Hero
// ========================
.privacy-hero {
  background: linear-gradient(135deg, $gray-darkness 0%, $gray-dark 100%);
  padding: 100px 24px 80px;
  text-align: center;
  border-bottom: 1px solid rgba($p-color, 0.2);

  &__container {
    max-width: 760px;
    margin: 0 auto;
  }

  &__badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba($p-color, 0.15);
    border: 1px solid rgba($p-color, 0.3);
    color: $p-color;
    padding: 8px 18px;
    border-radius: 100px;
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    margin-bottom: 28px;
    text-transform: uppercase;
  }

  &__title {
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 800;
    color: $white;
    margin-bottom: 16px;
    line-height: 1.2;
  }

  &__subtitle {
    font-size: 1.1rem;
    color: $gray;
    line-height: 1.7;
    margin-bottom: 24px;
  }

  &__date {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: $gray-medium;

    i { color: $p-color; }
  }
}

// ========================
// Content
// ========================
.privacy-content {
  padding: 64px 24px;

  &__container {
    max-width: 800px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 48px;
  }
}

// ========================
// Intro
// ========================
.privacy-intro {
  background: rgba($gray-darkness, 0.8);
  border: 1px solid rgba($white, 0.06);
  border-radius: 16px;
  padding: 32px;

  p {
    font-size: 1.05rem;
    color: $gray-light;
    line-height: 1.8;
    margin: 0;

    strong {
      color: $p-color;
      font-weight: 700;
    }
  }
}

// ========================
// Section
// ========================
.privacy-section {
  display: flex;
  flex-direction: column;
  gap: 20px;

  &__header {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  &__number {
    font-size: 0.75rem;
    font-weight: 800;
    color: $p-color;
    background: rgba($p-color, 0.1);
    border: 1px solid rgba($p-color, 0.25);
    border-radius: 8px;
    padding: 4px 10px;
    letter-spacing: 1px;
    flex-shrink: 0;
  }

  &__title {
    font-size: 1.35rem;
    font-weight: 700;
    color: $white;
    margin: 0;
  }

  &__text {
    font-size: 1rem;
    color: $gray-light;
    line-height: 1.8;
    margin: 0;
    padding-left: 4px;
  }
}

// ========================
// List
// ========================
.privacy-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 12px;

  li {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    background: rgba($gray-darkness, 0.6);
    border: 1px solid rgba($white, 0.06);
    border-radius: 12px;
    padding: 16px 20px;

    i {
      color: $p-color;
      font-size: 1rem;
      margin-top: 2px;
      flex-shrink: 0;
      width: 18px;
      text-align: center;
    }

    div {
      display: flex;
      flex-direction: column;
      gap: 4px;

      strong {
        font-size: 0.95rem;
        font-weight: 700;
        color: $white;
      }

      span {
        font-size: 0.92rem;
        color: $gray;
        line-height: 1.6;
      }
    }
  }
}

// ========================
// Highlight box
// ========================
.privacy-highlight {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  background: rgba($p-color, 0.07);
  border: 1px solid rgba($p-color, 0.25);
  border-left: 4px solid $p-color;
  border-radius: 12px;
  padding: 20px 24px;

  > i {
    color: $p-color;
    font-size: 1.1rem;
    margin-top: 2px;
    flex-shrink: 0;
  }

  p {
    font-size: 0.95rem;
    color: $gray-light;
    line-height: 1.7;
    margin: 0;

    strong {
      color: $p-color;
    }
  }
}

// ========================
// Signature
// ========================
.privacy-signature {
  display: flex;
  align-items: center;
  gap: 20px;
  background: linear-gradient(135deg, rgba($gray-dark, 0.8) 0%, rgba($gray-darkness, 0.8) 100%);
  border: 1px solid rgba($p-color, 0.2);
  border-radius: 16px;
  padding: 28px 32px;
  margin-top: 16px;

  &__icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: rgba($p-color, 0.15);
    border: 1px solid rgba($p-color, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;

    i {
      font-size: 1.3rem;
      color: $p-color;
    }
  }

  &__text {
    display: flex;
    flex-direction: column;
    gap: 4px;

    strong {
      font-size: 1.05rem;
      font-weight: 800;
      color: $white;
    }

    span {
      font-size: 0.9rem;
      color: $gray;
      font-style: italic;
    }
  }
}

// ========================
// Responsive
// ========================
@media (max-width: 640px) {
  .privacy-hero {
    padding: 80px 16px 60px;
  }

  .privacy-content {
    padding: 40px 16px;
  }

  .privacy-intro {
    padding: 24px;
  }

  .privacy-signature {
    flex-direction: column;
    text-align: center;
    padding: 24px;
  }

  .privacy-section__header {
    flex-wrap: wrap;
  }
}
</style>
