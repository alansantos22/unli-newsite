<template>
  <div class="admin-gamification">
    <!-- Sub Tabs -->
    <div class="sub-tabs">
      <button :class="{ active: subTab === 'missions' }" @click="subTab = 'missions'">
        <i class="fas fa-bullseye"></i> Missões
      </button>
      <button :class="{ active: subTab === 'badges' }" @click="subTab = 'badges'">
        <i class="fas fa-medal"></i> Medalhas
      </button>
    </div>

    <!-- ========== MISSÕES ========== -->
    <div v-if="subTab === 'missions'">
      <div class="actions-bar">
        <button class="btn-primary" @click="openCreateMission">
          <i class="fas fa-plus"></i> Nova Missão
        </button>
      </div>

      <div class="cards-grid">
        <div v-for="m in missions" :key="m.id" class="mission-card" :class="{ disabled: !m.is_active }">
          <div class="mission-top">
            <span class="mission-emoji">{{ m.icon_emoji || '🎯' }}</span>
            <div>
              <h4>{{ m.title }}</h4>
              <span class="mission-cat">{{ categoryLabel(m.category) }} · {{ frequencyLabel(m.frequency) }}</span>
            </div>
            <span class="status-pill" :class="m.is_active ? 'on' : 'off'">{{ m.is_active ? 'Ativa' : 'Inativa' }}</span>
          </div>
          <p class="mission-desc">{{ m.description || '—' }}</p>
          <div class="mission-stats">
            <span><strong>Meta:</strong> {{ m.target_value }} {{ m.target_unit }}</span>
            <span><strong>Recompensa:</strong> {{ m.reward_value }} {{ m.reward_type }}</span>
            <span><strong>Participantes:</strong> {{ m.total_participants || 0 }}</span>
            <span><strong>Completaram:</strong> {{ m.total_completed || 0 }}</span>
          </div>
          <div class="mission-actions">
            <button class="btn-icon" title="Editar" @click="openEditMission(m)"><i class="fas fa-pen"></i></button>
            <button class="btn-icon" :title="m.is_active ? 'Desativar' : 'Ativar'" @click="toggleMission(m)">
              <i :class="m.is_active ? 'fas fa-pause' : 'fas fa-play'"></i>
            </button>
          </div>
        </div>
        <p v-if="!missions.length" class="empty-state">Nenhuma missão cadastrada</p>
      </div>
    </div>

    <!-- ========== MEDALHAS ========== -->
    <div v-if="subTab === 'badges'">
      <div class="actions-bar">
        <button class="btn-primary" @click="openCreateBadge">
          <i class="fas fa-plus"></i> Nova Medalha
        </button>
      </div>

      <div class="cards-grid badges-grid">
        <div v-for="b in badges" :key="b.id" class="badge-card" :class="{ disabled: !b.is_active }">
          <div class="badge-visual">
            <span class="badge-emoji">{{ b.icon_emoji || '🏅' }}</span>
          </div>
          <h4>{{ b.title }}</h4>
          <p class="badge-desc">{{ b.description || '—' }}</p>
          <div class="badge-meta">
            <span class="badge-type">{{ typeLabel(b.type) }}</span>
            <span>{{ b.total_awarded || 0 }} concedida(s)</span>
          </div>
          <div class="badge-actions">
            <button class="btn-icon" title="Editar" @click="openEditBadge(b)"><i class="fas fa-pen"></i></button>
            <button class="btn-icon" title="Conceder a afiliado" @click="openAwardBadge(b)"><i class="fas fa-gift"></i></button>
          </div>
        </div>
        <p v-if="!badges.length" class="empty-state">Nenhuma medalha cadastrada</p>
      </div>
    </div>

    <!-- Modal: Criar/Editar Missão -->
    <div class="modal-overlay" v-if="showMissionModal" @click.self="showMissionModal = false">
      <div class="modal modal-sm">
        <div class="modal-header">
          <h3>{{ missionForm.id ? 'Editar' : 'Nova' }} Missão</h3>
          <button class="modal-close" @click="showMissionModal = false"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          <label>Título</label>
          <input v-model="missionForm.title" class="full-input" placeholder="Ex: Gerar 5 leads" />
          <label>Descrição</label>
          <textarea v-model="missionForm.description" class="full-input textarea" placeholder="Detalhamento da missão"></textarea>
          <label>Emoji</label>
          <input v-model="missionForm.icon_emoji" class="full-input" placeholder="🎯" maxlength="4" />
          <div class="form-row">
            <div>
              <label>Categoria</label>
              <select v-model="missionForm.category" class="full-input">
                <option value="explorer">Explorador</option>
                <option value="lead_hunter">Caçador de Leads</option>
                <option value="closer">Fechador</option>
                <option value="consistency">Consistência</option>
              </select>
            </div>
            <div>
              <label>Frequência</label>
              <select v-model="missionForm.frequency" class="full-input">
                <option value="weekly">Semanal</option>
                <option value="monthly">Mensal</option>
                <option value="one_time">Única</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div>
              <label>Meta (valor)</label>
              <input v-model.number="missionForm.target_value" type="number" class="full-input" />
            </div>
            <div>
              <label>Unidade</label>
              <select v-model="missionForm.target_unit" class="full-input">
                <option value="count">Contagem</option>
                <option value="currency">R$ (valor)</option>
                <option value="days">Dias</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div>
              <label>Recompensa</label>
              <select v-model="missionForm.reward_type" class="full-input">
                <option value="xp">XP</option>
                <option value="commission_bonus">Bônus de comissão</option>
                <option value="badge">Medalha</option>
              </select>
            </div>
            <div>
              <label>Valor recompensa</label>
              <input v-model.number="missionForm.reward_value" type="number" class="full-input" />
            </div>
          </div>
          <button class="btn-primary" @click="submitMission" :disabled="saving">
            <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
            {{ missionForm.id ? 'Salvar' : 'Criar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal: Criar/Editar Medalha -->
    <div class="modal-overlay" v-if="showBadgeModal" @click.self="showBadgeModal = false">
      <div class="modal modal-sm">
        <div class="modal-header">
          <h3>{{ badgeForm.id ? 'Editar' : 'Nova' }} Medalha</h3>
          <button class="modal-close" @click="showBadgeModal = false"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          <label>Nome</label>
          <input v-model="badgeForm.title" class="full-input" placeholder="Ex: Primeiro Lead" />
          <label>Descrição</label>
          <textarea v-model="badgeForm.description" class="full-input textarea" placeholder="Critério para ganhar"></textarea>
          <label>Emoji</label>
          <input v-model="badgeForm.icon_emoji" class="full-input" placeholder="🏅" maxlength="4" />
          <label>URL da Imagem (opcional)</label>
          <input v-model="badgeForm.image_url" class="full-input" placeholder="https://..." />
          <label>Tipo</label>
          <select v-model="badgeForm.type" class="full-input">
            <option value="achievement">Conquista</option>
            <option value="event">Evento</option>
            <option value="legacy">Legado</option>
          </select>
          <button class="btn-primary" @click="submitBadge" :disabled="saving">
            <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
            {{ badgeForm.id ? 'Salvar' : 'Criar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal: Conceder Medalha -->
    <div class="modal-overlay" v-if="showAwardModal" @click.self="showAwardModal = false">
      <div class="modal modal-sm">
        <div class="modal-header">
          <h3>Conceder: {{ awardBadge?.title }}</h3>
          <button class="modal-close" @click="showAwardModal = false"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
          <label>ID do Afiliado</label>
          <input v-model.number="awardForm.affiliate_id" type="number" class="full-input" placeholder="ID do afiliado" />
          <label>Observação (opcional)</label>
          <input v-model="awardForm.notes" class="full-input" placeholder="Ex: Participou do treinamento X" />
          <button class="btn-primary" @click="submitAward" :disabled="saving">
            <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-gift'"></i> Conceder
          </button>
        </div>
      </div>
    </div>

    <!-- Feedback -->
    <div v-if="feedback" class="feedback-toast" :class="feedback.type">
      <i :class="feedback.type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
      {{ feedback.message }}
    </div>
  </div>
</template>

<script>
import { useAdminAuth } from '@/core/composables/useAdminAuth';

export default {
  name: 'AdminGamification',
  data() {
    return {
      subTab: 'missions',
      missions: [],
      badges: [],
      saving: false,
      feedback: null,
      showMissionModal: false,
      missionForm: this.emptyMissionForm(),
      showBadgeModal: false,
      badgeForm: this.emptyBadgeForm(),
      showAwardModal: false,
      awardBadge: null,
      awardForm: { affiliate_id: '', notes: '' }
    };
  },
  watch: {
    subTab(tab) {
      if (tab === 'missions') this.loadMissions();
      if (tab === 'badges') this.loadBadges();
    }
  },
  async created() {
    await Promise.all([this.loadMissions(), this.loadBadges()]);
  },
  methods: {
    emptyMissionForm() {
      return { id: 0, title: '', description: '', icon_emoji: '🎯', category: 'lead_hunter', target_value: 1, target_unit: 'count', reward_type: 'xp', reward_value: 50, frequency: 'weekly' };
    },
    emptyBadgeForm() {
      return { id: 0, title: '', description: '', icon_emoji: '🏅', image_url: '', type: 'achievement' };
    },
    async loadMissions() {
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/gamification.php?action=list_missions');
      if (res.ok) this.missions = res.data;
    },
    async loadBadges() {
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/gamification.php?action=list_badges');
      if (res.ok) this.badges = res.data;
    },
    openCreateMission() {
      this.missionForm = this.emptyMissionForm();
      this.showMissionModal = true;
    },
    openEditMission(m) {
      this.missionForm = { ...m };
      this.showMissionModal = true;
    },
    async submitMission() {
      this.saving = true;
      const { adminFetch } = useAdminAuth();
      const action = this.missionForm.id ? 'update_mission' : 'create_mission';
      const res = await adminFetch(`/api/admin/gamification.php?action=${action}`, {
        method: 'POST',
        body: JSON.stringify(this.missionForm)
      });
      this.saving = false;
      this.showFeedback(res.ok ? 'success' : 'error', res.message || res.error);
      if (res.ok) { this.showMissionModal = false; this.loadMissions(); }
    },
    async toggleMission(m) {
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/gamification.php?action=toggle_mission', {
        method: 'POST',
        body: JSON.stringify({ id: m.id })
      });
      this.showFeedback(res.ok ? 'success' : 'error', res.message || res.error);
      if (res.ok) this.loadMissions();
    },
    openCreateBadge() {
      this.badgeForm = this.emptyBadgeForm();
      this.showBadgeModal = true;
    },
    openEditBadge(b) {
      this.badgeForm = { ...b };
      this.showBadgeModal = true;
    },
    async submitBadge() {
      this.saving = true;
      const { adminFetch } = useAdminAuth();
      const action = this.badgeForm.id ? 'update_badge' : 'create_badge';
      const res = await adminFetch(`/api/admin/gamification.php?action=${action}`, {
        method: 'POST',
        body: JSON.stringify(this.badgeForm)
      });
      this.saving = false;
      this.showFeedback(res.ok ? 'success' : 'error', res.message || res.error);
      if (res.ok) { this.showBadgeModal = false; this.loadBadges(); }
    },
    openAwardBadge(badge) {
      this.awardBadge = badge;
      this.awardForm = { affiliate_id: '', notes: '' };
      this.showAwardModal = true;
    },
    async submitAward() {
      if (!this.awardForm.affiliate_id) {
        this.showFeedback('error', 'Informe o ID do afiliado');
        return;
      }
      this.saving = true;
      const { adminFetch } = useAdminAuth();
      const res = await adminFetch('/api/admin/gamification.php?action=award_badge', {
        method: 'POST',
        body: JSON.stringify({ badge_id: this.awardBadge.id, ...this.awardForm })
      });
      this.saving = false;
      this.showFeedback(res.ok ? 'success' : 'error', res.message || res.error);
      if (res.ok) { this.showAwardModal = false; this.loadBadges(); }
    },
    showFeedback(type, message) {
      this.feedback = { type, message };
      setTimeout(() => { this.feedback = null; }, 3500);
    },
    categoryLabel(c) {
      const labels = { explorer: 'Explorador', lead_hunter: 'Caçador de Leads', closer: 'Fechador', consistency: 'Consistência' };
      return labels[c] || c;
    },
    frequencyLabel(f) {
      const labels = { weekly: 'Semanal', monthly: 'Mensal', one_time: 'Única' };
      return labels[f] || f;
    },
    typeLabel(t) {
      const labels = { event: 'Evento', achievement: 'Conquista', legacy: 'Legado' };
      return labels[t] || t;
    }
  }
};
</script>

<style lang="scss" scoped>
.sub-tabs {
  display: flex; gap: 8px; margin-bottom: 24px;
  button {
    padding: 10px 18px; border: 1px solid rgba(255,255,255,0.1);
    background: rgba(255,255,255,0.03); border-radius: 10px;
    color: #94a3b8; font-size: 13px; font-weight: 600;
    cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 6px;
    &:hover { color: #f1f5f9; }
    &.active { color: #f59e0b; border-color: #f59e0b; background: rgba(245,158,11,0.08); }
  }
}

.actions-bar { display: flex; justify-content: flex-end; margin-bottom: 20px; }

.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
  gap: 16px;
}

.badges-grid {
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
}

.mission-card {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 14px;
  padding: 20px;
  transition: border-color 0.2s;
  &:hover { border-color: rgba(255,255,255,0.12); }
  &.disabled { opacity: 0.5; }
}

.mission-top {
  display: flex; align-items: center; gap: 12px; margin-bottom: 10px;
  .mission-emoji { font-size: 28px; }
  h4 { margin: 0; font-size: 15px; color: #f1f5f9; }
  .mission-cat { font-size: 11px; color: #64748b; }
  .status-pill {
    margin-left: auto; font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600;
    &.on { background: rgba(34,197,94,0.15); color: #22c55e; }
    &.off { background: rgba(239,68,68,0.15); color: #ef4444; }
  }
}

.mission-desc { font-size: 13px; color: #94a3b8; margin: 0 0 12px; }

.mission-stats {
  display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 12px;
  span { font-size: 12px; color: #cbd5e1; strong { color: #64748b; } }
}

.mission-actions { display: flex; gap: 8px; }

.badge-card {
  background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 14px;
  padding: 20px;
  text-align: center;
  &.disabled { opacity: 0.5; }
}

.badge-visual { margin-bottom: 10px; .badge-emoji { font-size: 40px; } }
.badge-card h4 { margin: 0 0 6px; font-size: 15px; color: #f1f5f9; }
.badge-desc { font-size: 12px; color: #94a3b8; margin: 0 0 10px; }
.badge-meta {
  display: flex; justify-content: center; gap: 12px; margin-bottom: 12px;
  span { font-size: 11px; color: #64748b; }
  .badge-type { background: rgba(245,158,11,0.15); color: #f59e0b; padding: 2px 8px; border-radius: 6px; font-weight: 600; }
}
.badge-actions { display: flex; justify-content: center; gap: 8px; }

.btn-icon {
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px; color: #94a3b8; width: 32px; height: 32px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 13px; transition: all 0.2s;
  &:hover { color: #f59e0b; border-color: #f59e0b; }
}

.btn-primary {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #0f172a; border: none; padding: 10px 20px; border-radius: 10px;
  font-weight: 700; font-size: 13px; cursor: pointer;
  display: inline-flex; align-items: center; gap: 6px; margin-top: 12px;
  &:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(245,158,11,0.3); }
  &:disabled { opacity: 0.6; cursor: not-allowed; }
}

.empty-state { text-align: center; color: #64748b; padding: 40px; grid-column: 1 / -1; }

// Modal
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.7);
  display: flex; align-items: center; justify-content: center; z-index: 1000; padding: 20px;
}
.modal {
  background: #111827; border: 1px solid rgba(255,255,255,0.1);
  border-radius: 16px; width: 100%; max-height: 85vh; overflow-y: auto;
  &.modal-sm { max-width: 500px; }
}
.modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.06);
  h3 { margin: 0; font-size: 18px; color: #f1f5f9; }
  .modal-close { background: none; border: none; color: #64748b; font-size: 18px; cursor: pointer; &:hover { color: #ef4444; } }
}
.modal-body {
  padding: 20px 24px;
  label { display: block; font-size: 13px; color: #94a3b8; margin: 12px 0 6px; font-weight: 600; &:first-child { margin-top: 0; } }
}
.full-input {
  width: 100%; padding: 10px 12px;
  background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 8px; color: #f1f5f9; font-size: 13px; outline: none;
  &:focus { border-color: #f59e0b; }
  &.textarea { min-height: 60px; resize: vertical; font-family: inherit; }
}
.form-row { display: flex; gap: 12px; div { flex: 1; } }

.feedback-toast {
  position: fixed; bottom: 24px; right: 24px; padding: 14px 20px; border-radius: 12px;
  font-size: 14px; font-weight: 600; z-index: 2000; animation: slideIn 0.3s ease;
  &.success { background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3); }
  &.error { background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
  i { margin-right: 8px; }
}
@keyframes slideIn { from { transform: translateX(100px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
</style>
