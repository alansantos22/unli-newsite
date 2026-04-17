<template>
  <div class="wb-page">
    <div class="wb-bg-grid"></div>
    <div class="wb-bg-glow1"></div>
    <div class="wb-bg-glow2"></div>

    <nav class="wb-nav" :class="{ 'wb-nav-hidden': showWizard }">
      <img src="@/assets/img/logo_horizontal.png" alt="Unli Studio" class="wb-logo-img" width="200" height="32" />
      <div class="wb-nav-badge">
        <span class="wb-dot-live"></span>
        Vagas limitadas
      </div>
    </nav>

    <!-- ============================================================ -->
    <!-- WIZARD FULLSCREEN (primeira visita — sem cadastro anterior)   -->
    <!-- ============================================================ -->
    <section v-if="showWizard" class="wb-wizard-section">
      <div class="wb-wizard-card">

        <!-- Progress bar -->
        <div class="wb-wizard-progress">
          <div class="wb-wizard-bar" :style="{ width: wizardProgressWidth }"></div>
        </div>

        <!-- Steps indicator -->
        <div class="wb-wizard-steps">
          <div
            v-for="s in 3"
            :key="s"
            class="wb-step-dot"
            :class="{ active: wizardStep === s, done: wizardStep > s }"
          >
            <span v-if="wizardStep > s" class="wb-step-check"><i class="fas fa-check"></i></span>
            <span v-else>{{ s }}</span>
          </div>
        </div>

        <!-- STEP 1 — Dados pessoais -->
        <div v-if="wizardStep === 1" class="wb-wizard-body">
          <h2 class="wb-wizard-title">Para te conhecermos melhor</h2>
          <p class="wb-wizard-sub">Webinar gratuito · <strong>25/04/2026</strong></p>

          <div class="wb-form-group">
            <label for="wb-nome">Nome completo</label>
            <input
              id="wb-nome"
              v-model="form.nome"
              type="text"
              placeholder="Seu nome completo"
              :class="{ 'wb-field-error': errors.nome }"
              @focus="errors.nome = false"
              @input="onFirstInput"
            />
          </div>

          <div class="wb-form-group">
            <label for="wb-email">E-mail</label>
            <input
              id="wb-email"
              v-model="form.email"
              type="email"
              placeholder="seu@email.com"
              :class="{ 'wb-field-error': errors.email }"
              @focus="errors.email = false"
              @input="onFirstInput"
              @blur="errors.email = form.email.length > 0 && !isValidEmail(form.email)"
            />
            <span v-if="errors.email" class="wb-field-msg">Digite um e-mail válido</span>
          </div>

          <div class="wb-form-group">
            <label for="wb-contato">WhatsApp <span class="wb-optional">(com DDD)</span></label>
            <input
              id="wb-contato"
              v-model="form.contato"
              type="tel"
              inputmode="numeric"
              placeholder="(11) 99999-9999"
              maxlength="15"
              :class="{ 'wb-field-error': errors.contato }"
              @focus="errors.contato = false"
              @input="form.contato = maskPhone($event.target.value); onFirstInput()"
            />
            <span v-if="errors.contato" class="wb-field-msg">Digite um WhatsApp válido com DDD</span>
          </div>

          <button class="wb-submit-btn" @click="goToStep(2)">
            Continuar
            <span class="wb-submit-arrow">→</span>
          </button>
        </div>

        <!-- STEP 2 — Sobre o negócio -->
        <div v-if="wizardStep === 2" class="wb-wizard-body">
          <h2 class="wb-wizard-title">Sobre seu negócio</h2>
          <p class="wb-wizard-sub">Nos ajude a personalizar o conteúdo para você</p>

          <div class="wb-form-group">
            <label for="wb-ramo">Ramo da empresa</label>
            <select
              id="wb-ramo"
              v-model="form.ramo"
              :class="{ 'wb-field-error': errors.ramo }"
              @change="errors.ramo = false"
            >
              <option value="" disabled>Selecione o ramo</option>
              <option>Agência de Marketing Digital</option>
              <option>Publicidade e Comunicação</option>
              <option>E-commerce e Varejo</option>
              <option>Tecnologia e SaaS</option>
              <option>Consultoria Empresarial</option>
              <option>Saúde e Bem-estar</option>
              <option>Educação e Treinamentos</option>
              <option>Imobiliário</option>
              <option>Financeiro e Contabilidade</option>
              <option>Jurídico</option>
              <option>Indústria e Manufatura</option>
              <option>Alimentação e Gastronomia</option>
              <option>Logística e Transporte</option>
              <option>Autônomo / Freelancer</option>
              <option>Outro</option>
            </select>
          </div>

          <div class="wb-form-row">
            <div class="wb-form-group">
              <label for="wb-cidade">Cidade</label>
              <input
                id="wb-cidade"
                v-model="form.cidade"
                type="text"
                placeholder="Sua cidade"
              />
            </div>
            <div class="wb-form-group">
              <label for="wb-estado">Estado</label>
              <input
                id="wb-estado"
                v-model="form.estado"
                type="text"
                placeholder="SP"
              />
            </div>
          </div>

          <div class="wb-form-group">
            <label for="wb-pais">País</label>
            <select id="wb-pais" v-model="form.pais">
              <option value="Brasil">🇧🇷 Brasil</option>
              <option value="Portugal">🇵🇹 Portugal</option>
              <option value="Estados Unidos">🇺🇸 Estados Unidos</option>
              <option value="Argentina">🇦🇷 Argentina</option>
              <option value="Colômbia">🇨🇴 Colômbia</option>
              <option value="México">🇲🇽 México</option>
              <option value="Outro">🌎 Outro</option>
            </select>
          </div>

          <div class="wb-wizard-nav">
            <button class="wb-back-btn" @click="wizardStep = 1">
              <span class="wb-back-arrow">←</span> Voltar
            </button>
            <button class="wb-submit-btn wb-btn-flex" @click="goToStep(3)">
              Continuar
              <span class="wb-submit-arrow">→</span>
            </button>
          </div>
        </div>

        <!-- STEP 3 — Objetivo -->
        <div v-if="wizardStep === 3" class="wb-wizard-body">
          <h2 class="wb-wizard-title">Seu objetivo</h2>
          <p class="wb-wizard-sub">Última etapa, quase lá!</p>

          <div class="wb-form-group">
            <label for="wb-objetivo">O que você quer dominar com IA?</label>
            <select
              id="wb-objetivo"
              v-model="form.objetivo"
              :class="{ 'wb-field-error': errors.objetivo }"
              @change="errors.objetivo = false"
            >
              <option value="" disabled>Escolha seu maior objetivo</option>
              <option>Automatizar atendimento e suporte ao cliente</option>
              <option>Criar conteúdo e copy com IA</option>
              <option>Captar e qualificar leads automaticamente</option>
              <option>Analisar dados e gerar relatórios inteligentes</option>
              <option>Integrar IA ao meu processo de vendas</option>
              <option>Reduzir custos operacionais com automação</option>
              <option>Criar agentes de IA para tarefas internas</option>
              <option>Automatizar redes sociais e marketing</option>
              <option>Entender o que é IA e por onde começar</option>
            </select>
          </div>

          <div class="wb-form-group">
            <label for="wb-mensagem">
              Quer aprender algo específico?
              <span class="wb-optional">(opcional)</span>
            </label>
            <textarea
              id="wb-mensagem"
              v-model="form.mensagem"
              placeholder="Descreva um desafio ou dúvida que você quer resolver com IA e automação..."
            ></textarea>
          </div>

          <div class="wb-wizard-nav">
            <button class="wb-back-btn" @click="wizardStep = 2">
              <span class="wb-back-arrow">←</span> Voltar
            </button>
            <button class="wb-submit-btn wb-btn-flex" :disabled="loading" @click="submitForm">
              <template v-if="loading">
                <span class="wb-spinner"></span>
                Enviando...
              </template>
              <template v-else>
                Garantir minha vaga
                <span class="wb-submit-arrow">→</span>
              </template>
            </button>
          </div>
        </div>

        <!-- STEP 4 — Sucesso (Entrar no grupo) -->
        <div v-if="wizardStep === 4" class="wb-wizard-body">
          <div class="wb-success-screen">
            <div class="wb-success-check">
              <i class="fas fa-check"></i>
            </div>
            <h3 class="wb-success-title">Vaga confirmada, {{ form.nome.split(' ')[0] }}!</h3>
            <p class="wb-success-sub">
              O link de acesso será enviado para <strong>{{ form.email }}</strong> na véspera do evento.
            </p>
            <div class="wb-success-event">
              <span><i class="fas fa-calendar"></i> 25 de abril de 2026</span>
              <span><i class="fas fa-clock"></i> 14h às 17h, ao vivo</span>
            </div>
            <a
              href="https://chat.whatsapp.com/Lj7o5OYe0KcClkfCXPKIUp"
              target="_blank"
              rel="noopener noreferrer"
              class="wb-whatsapp-btn"
              @click="onJoinGroup"
            >
              <i class="fab fa-whatsapp"></i>
              Entrar no grupo do Webinar
            </a>
            <p class="wb-success-hint">No grupo você recebe avisos, materiais e pode tirar dúvidas antes do evento.</p>
          </div>
        </div>

        <div v-if="wizardStep < 4" class="wb-form-guarantee">
          <svg width="13" height="13" fill="none" viewBox="0 0 16 16">
            <path d="M8 1.5l1.5 4.5H14l-3.75 2.75 1.5 4.5L8 10.5l-3.75 2.75 1.5-4.5L2 6h4.5L8 1.5z" stroke="#8B8D9A" stroke-width="1.3" stroke-linejoin="round"/>
          </svg>
          100% gratuito · Sem spam
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- PÁGINA NORMAL (usuário já cadastrado — returning visitor)     -->
    <!-- ============================================================ -->
    <template v-else>
      <section class="wb-hero">
        <div class="wb-hero-content">
          <div class="wb-hero-eyebrow">
            <span class="wb-eyebrow-line"></span>
            Webinar Gratuito · 25 de Abril de 2026
          </div>

          <h1 class="wb-hero-title">
            IA e Automação<br>
            que <span class="wb-hl">realmente</span><br>
            geram resultado
          </h1>

          <p class="wb-hero-sub">
            Aprenda a usar as melhores ferramentas de inteligência artificial, modelos de linguagem e automação para transformar a operação do seu negócio, ao vivo e de graça.
          </p>

          <div class="wb-date-strip">
            <div class="wb-chip">
              <i class="fas fa-calendar wb-chip-icon"></i>
              25 de Abril de 2026
            </div>
            <div class="wb-chip">
              <i class="fas fa-clock wb-chip-icon"></i>
              14h às 17h
            </div>
            <div class="wb-chip">
              <i class="fas fa-wifi wb-chip-icon"></i>
              Online · Ao vivo
            </div>
          </div>

          <div class="wb-hero-stats">
            <div class="wb-stat">
              <div class="wb-stat-num"><span>Poucas</span></div>
              <div class="wb-stat-label">vagas</div>
            </div>
            <div class="wb-stat">
              <div class="wb-stat-num"><span>100%</span></div>
              <div class="wb-stat-label">conteúdo prático</div>
            </div>
            <div class="wb-stat">
              <div class="wb-stat-num"><span>Grátis</span></div>
              <div class="wb-stat-label">custo de inscrição</div>
            </div>
          </div>

          <!-- CTA para quem já se cadastrou -->
          <div class="wb-returning-cta">
            <div class="wb-returning-badge">
              <i class="fas fa-check-circle"></i>
              Você já está inscrito!
            </div>
            <a
              href="https://chat.whatsapp.com/Lj7o5OYe0KcClkfCXPKIUp"
              target="_blank"
              rel="noopener noreferrer"
              class="wb-whatsapp-btn"
              @click="onJoinGroup"
            >
              <i class="fab fa-whatsapp"></i>
              Entrar no grupo do Webinar
            </a>
          </div>
        </div>
      </section>

      <!-- TOPICS -->
      <section class="wb-topics">
        <div class="wb-section-label">O que você vai aprender</div>
        <h2 class="wb-section-title">Conteúdo 100% prático</h2>
        <div class="wb-topics-grid">
          <div class="wb-topic-card">
            <div class="wb-topic-icon"><i class="fas fa-cogs"></i></div>
            <div class="wb-topic-name">Modelos de IA na prática</div>
            <div class="wb-topic-desc">ChatGPT, Claude, Gemini, quando usar cada um e como extrair o máximo.</div>
          </div>
          <div class="wb-topic-card">
            <div class="wb-topic-icon"><i class="fas fa-bolt"></i></div>
            <div class="wb-topic-name">Automação sem código</div>
            <div class="wb-topic-desc">Construa fluxos de automação que rodam sozinhos, 24h por dia.</div>
          </div>
          <div class="wb-topic-card">
            <div class="wb-topic-icon"><i class="fas fa-bullseye"></i></div>
            <div class="wb-topic-name">Captação de leads com IA</div>
            <div class="wb-topic-desc">Gere e qualifique leads automaticamente via WhatsApp e redes sociais.</div>
          </div>
          <div class="wb-topic-card">
            <div class="wb-topic-icon"><i class="fas fa-pen"></i></div>
            <div class="wb-topic-name">Criação de conteúdo</div>
            <div class="wb-topic-desc">Produza copies, posts e roteiros em escala com prompts avançados.</div>
          </div>
          <div class="wb-topic-card">
            <div class="wb-topic-icon"><i class="fas fa-chart-bar"></i></div>
            <div class="wb-topic-name">Dados e relatórios</div>
            <div class="wb-topic-desc">Transforme números brutos em insights acionáveis com IA analítica.</div>
          </div>
          <div class="wb-topic-card">
            <div class="wb-topic-icon"><i class="fas fa-rocket"></i></div>
            <div class="wb-topic-name">Vendas automatizadas</div>
            <div class="wb-topic-desc">Closers e SDRs inteligentes que nutrem e fecham vendas no automático.</div>
          </div>
        </div>
      </section>
    </template>
  </div>
</template>

<script>
import { fbTrackEvent, trackEvent } from '@/plugins/analytics';

const STORAGE_KEY = 'webinar_lead_registered';

export default {
  name: 'WebinarPage',

  data() {
    return {
      showWizard: true,
      wizardStep: 1,
      submitted: false,
      loading: false,
      refCode: null,
      formStartedTracked: false,
      form: {
        nome: '',
        email: '',
        contato: '',
        ramo: '',
        cidade: '',
        estado: '',
        pais: 'Brasil',
        objetivo: '',
        mensagem: '',
      },
      errors: {
        nome: false,
        email: false,
        contato: false,
        ramo: false,
        objetivo: false,
      },
    };
  },

  computed: {
    wizardProgressWidth() {
      if (this.wizardStep >= 4) return '100%';
      return `${((this.wizardStep - 1) / 3) * 100}%`;
    },
  },

  created() {
    const params = new URLSearchParams(window.location.search);
    const ref = params.get('ref');
    if (ref && /^[a-zA-Z0-9_-]{4,64}$/.test(ref)) {
      this.refCode = ref;
    }

    // Se já se cadastrou antes, mostra a página normal sem wizard
    try {
      if (localStorage.getItem(STORAGE_KEY)) {
        this.showWizard = false;
      }
    } catch {
      // localStorage indisponível — mostra wizard
    }
  },

  methods: {
    isValidEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email.trim());
    },

    maskPhone(value) {
      const v = value.replace(/\D/g, '').slice(0, 11);
      if (!v) return '';
      if (v.length <= 2) return `(${v}`;
      if (v.length <= 6) return `(${v.slice(0, 2)}) ${v.slice(2)}`;
      if (v.length <= 10) return `(${v.slice(0, 2)}) ${v.slice(2, 6)}-${v.slice(6)}`;
      return `(${v.slice(0, 2)}) ${v.slice(2, 7)}-${v.slice(7)}`;
    },

    isValidPhone(phone) {
      const digits = phone.replace(/\D/g, '');
      return digits.length >= 10 && digits.length <= 11;
    },

    // ── Analytics: dispara uma vez quando o usuário começa a digitar ──
    onFirstInput() {
      if (this.formStartedTracked) return;
      const hasAnyValue = this.form.nome || this.form.email || this.form.contato;
      if (hasAnyValue) {
        this.formStartedTracked = true;
        trackEvent('webinar_form_started', {
          event_category: 'webinar',
          event_label: 'Começou a preencher',
        });
      }
    },

    // ── Analytics: clicou para entrar no grupo ──
    onJoinGroup() {
      trackEvent('webinar_group_joined', {
        event_category: 'webinar',
        event_label: 'Entrou no grupo WhatsApp',
      });
      fbTrackEvent('Contact', { method: 'whatsapp_group' });
    },

    // ── Salva lead parcial (fire-and-forget) ──
    savePartial(extraData = {}) {
      const payload = {
        nome:    this.form.nome,
        email:   this.form.email,
        contato: this.form.contato,
        ...extraData,
      };
      if (this.refCode) payload.ref_code = this.refCode;
      fetch('/api/admin/webinar-leads.php?action=save_partial', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      }).catch(() => { /* silencioso — não bloqueia o usuário */ });
    },

    // ── Navegação do wizard ──
    goToStep(step) {
      // Validação por etapa
      if (step === 2) {
        this.errors = { ...this.errors, nome: false, email: false, contato: false };
        let valid = true;
        if (!this.form.nome.trim()) { this.errors.nome = true; valid = false; }
        if (!this.isValidEmail(this.form.email)) { this.errors.email = true; valid = false; }
        if (!this.form.contato.trim() || !this.isValidPhone(this.form.contato)) { this.errors.contato = true; valid = false; }
        if (!valid) return;

        // Salva lead parcial assim que o usuário avança da etapa 1
        this.savePartial();
      }

      if (step === 3) {
        this.errors = { ...this.errors, ramo: false };
        if (!this.form.ramo) { this.errors.ramo = true; return; }

        // Analytics: preencheu a segunda etapa
        trackEvent('webinar_step2_completed', {
          event_category: 'webinar',
          event_label: 'Preencheu segunda etapa',
        });

        // Atualiza lead parcial com dados do negócio
        this.savePartial({
          ramo:   this.form.ramo,
          cidade: this.form.cidade,
          estado: this.form.estado,
          pais:   this.form.pais,
        });
      }

      this.wizardStep = step;
    },

    submitForm() {
      this.errors = { nome: false, email: false, contato: false, ramo: false, objetivo: false };

      if (!this.form.objetivo) { this.errors.objetivo = true; return; }

      this.loading = true;

      const payload = { ...this.form };
      if (this.refCode) payload.ref_code = this.refCode;

      fetch('/api/admin/webinar-leads.php?action=register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      })
        .then(r => r.json())
        .then(data => {
          if (data.ok) {
            this.submitted = true;
            this.wizardStep = 4;
            fbTrackEvent('Lead');
            trackEvent('generate_lead', { event_category: 'webinar', event_label: 'Webinar IA e Automação' });

            // Marca como cadastrado para próximas visitas
            try { localStorage.setItem(STORAGE_KEY, '1'); } catch { /* ok */ }
          } else {
            alert(data.error || 'Erro ao realizar inscrição. Tente novamente.');
          }
        })
        .catch(() => {
          alert('Erro de conexão. Verifique sua internet e tente novamente.');
        })
        .finally(() => {
          this.loading = false;
        });
    },
  },
};
</script>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.wb-page {
  --brand: #0F1117;
  --accent: #00E5A0;
  --accent2: #7B5CFF;
  --surface: #16181F;
  --surface2: #1E2028;
  --text: #F0F0F4;
  --text-muted: #8B8D9A;
  --border: rgba(255,255,255,0.08);
  --font-head: 'Syne', sans-serif;
  --font-body: 'DM Sans', sans-serif;
  --r: 12px;

  font-family: var(--font-body);
  background: var(--brand);
  color: var(--text);
  min-height: 100vh;
  overflow-x: hidden;
  position: relative;
}

/* ═══════════════════════════════════════════════════════════════════ */
/* WIZARD FULLSCREEN                                                  */
/* ═══════════════════════════════════════════════════════════════════ */
.wb-wizard-section {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: calc(100vh - 80px);
  padding: 40px 24px;
  position: relative;
  z-index: 1;
}

.wb-wizard-card {
  width: 100%;
  max-width: 480px;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 0 32px 32px;
  position: relative;
  overflow: hidden;
}
.wb-wizard-card::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: linear-gradient(90deg, var(--accent), var(--accent2));
}

/* Progress bar */
.wb-wizard-progress {
  height: 3px;
  background: rgba(255,255,255,0.04);
  position: absolute;
  top: 3px; left: 0; right: 0;
}
.wb-wizard-bar {
  height: 100%;
  background: var(--accent);
  transition: width 0.4s ease;
  border-radius: 0 2px 2px 0;
}

/* Step dots */
.wb-wizard-steps {
  display: flex;
  justify-content: center;
  gap: 16px;
  padding-top: 28px;
  margin-bottom: 8px;
}
.wb-step-dot {
  width: 32px; height: 32px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 600;
  background: var(--surface2);
  border: 1.5px solid var(--border);
  color: var(--text-muted);
  transition: all 0.25s ease;
}
.wb-step-dot.active {
  background: rgba(0,229,160,0.15);
  border-color: var(--accent);
  color: var(--accent);
}
.wb-step-dot.done {
  background: var(--accent);
  border-color: var(--accent);
  color: #0A0D12;
}
.wb-step-check { font-size: 12px; line-height: 1; }

/* Wizard body */
.wb-wizard-body {
  padding-top: 16px;
}

.wb-wizard-title {
  font-family: var(--font-head);
  font-size: 22px; font-weight: 700;
  letter-spacing: -0.5px;
  margin-bottom: 4px;
  text-align: center;
}
.wb-wizard-sub {
  font-size: 13px; color: var(--text-muted);
  text-align: center;
  margin-bottom: 24px; line-height: 1.5;
}
.wb-wizard-sub strong { color: var(--accent); font-weight: 500; }

/* Wizard nav (back + continue) */
.wb-wizard-nav {
  display: flex;
  gap: 12px;
  margin-top: 22px;
}
.wb-back-btn {
  display: flex; align-items: center; gap: 6px;
  background: transparent;
  border: 1px solid var(--border);
  border-radius: var(--r);
  color: var(--text-muted);
  font-family: var(--font-body);
  font-size: 14px; font-weight: 500;
  padding: 12px 18px;
  cursor: pointer;
  transition: border-color 0.2s, color 0.2s;
  white-space: nowrap;
}
.wb-back-btn:hover {
  border-color: rgba(255,255,255,0.2);
  color: var(--text);
}
.wb-back-arrow { font-size: 16px; }
.wb-btn-flex { flex: 1; }

/* Returning user CTA */
.wb-returning-cta {
  margin-top: 36px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  align-items: flex-start;
}
.wb-returning-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 600;
  color: var(--accent);
  background: rgba(0,229,160,0.1);
  border: 1px solid rgba(0,229,160,0.25);
  border-radius: 10px;
  padding: 10px 18px;
}
.wb-returning-badge i { font-size: 16px; }

/* BACKGROUND */
.wb-bg-grid {
  position: fixed; inset: 0; z-index: 0; pointer-events: none;
  background-image:
    linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
  background-size: 48px 48px;
}
.wb-bg-glow1 {
  position: fixed; top: -200px; left: -200px; width: 600px; height: 600px;
  background: radial-gradient(circle, rgba(0,229,160,0.12) 0%, transparent 70%);
  z-index: 0; pointer-events: none;
}
.wb-bg-glow2 {
  position: fixed; bottom: -150px; right: -150px; width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(123,92,255,0.12) 0%, transparent 70%);
  z-index: 0; pointer-events: none;
}

/* NAV */
.wb-nav {
  display: flex; align-items: center; justify-content: space-between;
  padding: 24px 40px;
  border-bottom: 1px solid var(--border);
  backdrop-filter: blur(12px);
  position: sticky; top: 0; z-index: 100;
  background: rgba(15,17,23,0.85);
}
.wb-logo-img {
  height: 32px;
  width: auto;
  object-fit: contain;
  filter: brightness(0) invert(1);
}

.wb-nav-badge {
  display: flex; align-items: center; gap: 8px;
  font-size: 13px; font-weight: 500;
  color: var(--accent);
  background: rgba(0,229,160,0.1);
  border: 1px solid rgba(0,229,160,0.25);
  padding: 6px 14px;
  border-radius: 100px;
}
.wb-dot-live {
  width: 7px; height: 7px; border-radius: 50%;
  background: var(--accent);
  animation: wb-pulse 2s infinite;
}
@keyframes wb-pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.5; transform: scale(0.85); }
}

/* HERO (returning visitor — single column) */
.wb-hero {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 80px 40px 60px;
  max-width: 800px;
  margin: 0 auto;
  position: relative; z-index: 1;
}

.wb-hero-eyebrow {
  display: inline-flex; align-items: center; gap: 8px;
  font-size: 12px; font-weight: 500; letter-spacing: 0.12em;
  text-transform: uppercase; color: var(--accent);
  margin-bottom: 24px;
}
.wb-eyebrow-line { width: 28px; height: 1px; background: var(--accent); }

.wb-hero-title {
  font-family: var(--font-head);
  font-size: clamp(36px, 5vw, 58px);
  font-weight: 800; line-height: 1.05;
  letter-spacing: -1.5px; margin-bottom: 20px;
}
.wb-hl {
  background: linear-gradient(90deg, var(--accent), var(--accent2));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.wb-hero-sub {
  font-size: 17px; font-weight: 300;
  color: var(--text-muted); line-height: 1.7;
  max-width: 520px; margin-bottom: 36px;
}

.wb-date-strip {
  display: flex; gap: 12px; margin-bottom: 36px; flex-wrap: wrap;
}
.wb-chip {
  display: flex; align-items: center; gap: 7px;
  font-size: 13px; font-weight: 500;
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 8px; padding: 8px 14px;
  color: var(--text);
}
.wb-chip-icon { font-size: 14px; width: 16px; display: inline-flex; align-items: center; justify-content: center; }

.wb-hero-stats { display: flex; gap: 32px; }
.wb-stat { border-left: 2px solid var(--accent); padding-left: 14px; }
.wb-stat-num {
  font-family: var(--font-head);
  font-size: 26px; font-weight: 700;
  color: var(--text); line-height: 1; margin-bottom: 4px;
}
.wb-stat-num span { color: var(--accent); }
.wb-stat-label { font-size: 12px; color: var(--text-muted); font-weight: 400; }

/* FORM CARD */
.wb-form-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 36px 32px;
  position: relative; overflow: hidden;
}
.wb-form-card::before {
  content: '';
  position: absolute; top: 0; left: 0; right: 0; height: 3px;
  background: linear-gradient(90deg, var(--accent), var(--accent2));
}

.wb-form-title {
  font-family: var(--font-head);
  font-size: 20px; font-weight: 700;
  margin-bottom: 6px; letter-spacing: -0.4px;
}
.wb-form-sub {
  font-size: 13px; color: var(--text-muted);
  margin-bottom: 28px; line-height: 1.5;
}
.wb-form-sub strong { color: var(--accent); font-weight: 500; }

.wb-form-group { margin-bottom: 18px; }

label {
  display: block;
  font-size: 12px; font-weight: 500; letter-spacing: 0.04em;
  color: var(--text-muted); text-transform: uppercase;
  margin-bottom: 7px;
}
.wb-optional {
  color: var(--text-muted); text-transform: none; font-size: 11px;
}

input[type="text"],
input[type="email"],
input[type="tel"],
select,
textarea {
  width: 100%;
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: var(--r);
  color: var(--text);
  font-family: var(--font-body);
  font-size: 14px;
  padding: 11px 14px;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  -webkit-appearance: none;
  appearance: none;
}
input[type="text"]:focus,
input[type="email"]:focus,
input[type="tel"]:focus,
select:focus,
textarea:focus {
  border-color: rgba(0,229,160,0.5);
  box-shadow: 0 0 0 3px rgba(0,229,160,0.07);
}
input::placeholder, textarea::placeholder { color: rgba(139,141,154,0.6); }

.wb-field-error {
  border-color: rgba(229,75,74,0.7) !important;
  box-shadow: 0 0 0 3px rgba(229,75,74,0.07) !important;
}

.wb-field-msg {
  display: block;
  margin-top: 5px;
  font-size: 11px;
  color: rgba(229,75,74,0.9);
}

select {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='7' fill='none'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%238B8D9A' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 14px center;
  padding-right: 38px; cursor: pointer;
}
select option { background: #1E2028; color: var(--text); }

textarea { resize: vertical; min-height: 88px; line-height: 1.6; }

.wb-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.wb-submit-btn {
  width: 100%; margin-top: 22px;
  background: var(--accent);
  color: #0A0D12;
  font-family: var(--font-head);
  font-size: 15px; font-weight: 700;
  letter-spacing: 0.02em;
  border: none; border-radius: var(--r);
  padding: 14px 24px; cursor: pointer;
  transition: transform 0.15s, box-shadow 0.15s, filter 0.15s;
  display: flex; align-items: center; justify-content: center; gap: 8px;
}
.wb-submit-btn:hover:not(:disabled) {
  filter: brightness(1.08);
  transform: translateY(-1px);
  box-shadow: 0 8px 24px rgba(0,229,160,0.25);
}
.wb-submit-btn:active:not(:disabled) { transform: translateY(0); }
.wb-submit-btn:disabled { opacity: 0.7; cursor: not-allowed; }
.wb-submit-arrow { font-size: 18px; }

.wb-spinner {
  width: 16px; height: 16px; border-radius: 50%;
  border: 2px solid rgba(10,13,18,0.3);
  border-top-color: #0A0D12;
  animation: wb-spin 0.7s linear infinite;
  flex-shrink: 0;
}
@keyframes wb-spin {
  to { transform: rotate(360deg); }
}

.wb-form-guarantee {
  display: flex; align-items: center; justify-content: center; gap: 7px;
  font-size: 12px; color: var(--text-muted); margin-top: 14px;
}
.wb-form-guarantee svg { flex-shrink: 0; }

/* SUCCESS */
.wb-success-screen {
  display: flex; flex-direction: column;
  align-items: center;
  text-align: center; padding: 40px 24px 48px; gap: 14px;
}
.wb-success-check {
  width: 60px; height: 60px;
  background: rgba(0,229,160,0.12);
  border: 1px solid rgba(0,229,160,0.3);
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 22px; color: var(--accent);
  margin-bottom: 4px;
}
.wb-success-title {
  font-family: var(--font-head); font-size: 20px; font-weight: 700;
  letter-spacing: -0.4px; color: var(--text); line-height: 1.3;
}
.wb-success-sub {
  font-size: 14px; color: var(--text-muted);
  line-height: 1.65; max-width: 310px;
}
.wb-success-sub strong { color: var(--text); }
.wb-success-event {
  display: flex; gap: 16px; flex-wrap: wrap; justify-content: center;
  margin: 4px 0;
}
.wb-success-event span {
  display: flex; align-items: center; gap: 7px;
  font-size: 13px; font-weight: 500;
  background: var(--surface2);
  border: 1px solid var(--border);
  border-radius: 8px; padding: 7px 13px;
  color: var(--text);
}
.wb-success-event span i { color: var(--accent); font-size: 13px; }
.wb-whatsapp-btn {
  display: inline-flex; align-items: center; gap: 9px;
  background: #25D366;
  color: #fff;
  text-decoration: none;
  font-family: var(--font-head);
  font-size: 15px; font-weight: 700;
  padding: 13px 24px;
  border-radius: var(--r);
  margin-top: 6px;
  transition: filter 0.15s, transform 0.15s;
}
.wb-whatsapp-btn i { font-size: 18px; }
.wb-whatsapp-btn:hover {
  filter: brightness(1.08);
  transform: translateY(-1px);
}
.wb-success-hint {
  font-size: 12px; color: var(--text-muted);
  max-width: 280px; line-height: 1.5;
}

/* TOPICS */
.wb-topics {
  max-width: 1200px; margin: 0 auto;
  padding: 60px 40px;
  border-top: 1px solid var(--border);
  position: relative; z-index: 1;
}
.wb-section-label {
  font-size: 11px; font-weight: 500; letter-spacing: 0.14em;
  text-transform: uppercase; color: var(--accent);
  margin-bottom: 16px;
  display: flex; align-items: center; gap: 10px;
}
.wb-section-label::after {
  content: ''; flex: 1; height: 1px; background: var(--border);
}
.wb-section-title {
  font-family: var(--font-head); font-size: 28px; font-weight: 700;
  letter-spacing: -0.6px; margin-bottom: 36px;
}
.wb-topics-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 16px;
}
@media (max-width: 1024px) {
  .wb-topics-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 600px) {
  .wb-topics-grid {
    grid-template-columns: 1fr;
  }
}
.wb-topic-card {
  background: var(--surface); border: 1px solid var(--border);
  border-radius: var(--r); padding: 22px;
  transition: border-color 0.2s;
}
.wb-topic-card:hover { border-color: rgba(0,229,160,0.3); }
.wb-topic-icon {
  width: 38px; height: 38px; border-radius: 10px;
  background: rgba(0,229,160,0.1);
  display: flex; align-items: center; justify-content: center;
  font-size: 18px; margin-bottom: 14px;
  color: var(--accent);
}
.wb-topic-icon i {
  font-size: 18px;
}
.wb-topic-name {
  font-family: var(--font-head); font-size: 14px; font-weight: 600;
  margin-bottom: 6px; line-height: 1.3;
}
.wb-topic-desc { font-size: 12px; color: var(--text-muted); line-height: 1.6; }

/* RESPONSIVE */
@media (max-width: 900px) {
  .wb-hero {
    padding: 48px 24px 40px;
  }
  .wb-nav { padding: 18px 24px; }
  .wb-topics { padding: 48px 24px; }
  .wb-form-row { grid-template-columns: 1fr; }
  .wb-hero-stats { gap: 20px; }
}

@media (max-width: 480px) {
  .wb-wizard-title { font-size: 19px; }
  .wb-wizard-nav { flex-direction: column-reverse; }
  .wb-back-btn { justify-content: center; }
}

@media (max-width: 768px) {
  .wb-nav-hidden {
    display: none;
  }
  .wb-wizard-section {
    position: fixed;
    inset: 0;
    z-index: 200;
    min-height: 100vh;
    padding: 0;
    background: var(--brand);
    overflow-y: auto;
    align-items: flex-start;
  }
  .wb-wizard-card {
    max-width: 100%;
    min-height: 100vh;
    border: none;
    border-radius: 0;
    padding: 0 20px 32px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }
  .wb-wizard-card::before {
    border-radius: 0;
  }
}
</style>
