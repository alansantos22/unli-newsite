<template>
  <div class="aff-dashboard">
    <!-- Sidebar -->
    <aside class="aff-sidebar">
      <div class="sidebar-header">
        <span class="sidebar-icon">🤝</span>
        <h2>Painel Afiliado</h2>
      </div>
      <nav class="sidebar-nav">
        <button :class="['nav-item', { active: activeTab === 'dashboard' }]" @click="activeTab = 'dashboard'">
          <i class="fas fa-home"></i> Dashboard
        </button>
        <button :class="['nav-item', { active: activeTab === 'referrals' }]" @click="activeTab = 'referrals'">
          <i class="fas fa-users"></i> Indicações
        </button>
        <button :class="['nav-item', { active: activeTab === 'links' }]" @click="activeTab = 'links'">
          <i class="fas fa-link"></i> Meus Links
        </button>
        <button :class="['nav-item', { active: activeTab === 'calculator' }]" @click="activeTab = 'calculator'">
          <i class="fas fa-calculator"></i> Simulador
        </button>
        <button :class="['nav-item', { active: activeTab === 'missions' }]" @click="activeTab = 'missions'">
          <i class="fas fa-crosshairs"></i> Missões
        </button>
        <button :class="['nav-item', { active: activeTab === 'badges' }]" @click="activeTab = 'badges'">
          <i class="fas fa-award"></i> Conquistas
        </button>
        <button :class="['nav-item', { active: activeTab === 'ranking' }]" @click="activeTab = 'ranking'">
          <i class="fas fa-trophy"></i> Ranking
        </button>
        <button :class="['nav-item', { active: activeTab === 'tiers' }]" @click="activeTab = 'tiers'">
          <i class="fas fa-medal"></i> Ligas
        </button>
      </nav>
      <div class="sidebar-footer">
        <button class="nav-item logout" @click="handleLogout">
          <i class="fas fa-sign-out-alt"></i> Sair
        </button>
      </div>
    </aside>

    <!-- Mobile Header -->
    <header class="aff-mobile-header">
      <button class="menu-toggle" @click="showMobileMenu = !showMobileMenu">
        <i class="fas fa-bars"></i>
      </button>
      <span>🤝 Painel Afiliado</span>
      <button class="btn-logout-mobile" @click="handleLogout">
        <i class="fas fa-sign-out-alt"></i>
      </button>
    </header>

    <!-- Mobile Menu -->
    <div v-if="showMobileMenu" class="mobile-menu-overlay" @click="showMobileMenu = false">
      <nav class="mobile-menu" @click.stop>
        <button v-for="tab in tabs" :key="tab.id" :class="['nav-item', { active: activeTab === tab.id }]" @click="activeTab = tab.id; showMobileMenu = false">
          <i :class="tab.icon"></i> {{ tab.label }}
        </button>
      </nav>
    </div>

    <!-- Main Content -->
    <main class="aff-main">
      <!-- Loading State -->
      <div v-if="loading" class="loading-state">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Carregando dados...</p>
      </div>

      <!-- Dashboard Tab -->
      <div v-else-if="activeTab === 'dashboard'" class="tab-content">
        <!-- Welcome & Tier -->
        <div class="welcome-section">
          <div class="welcome-text">
            <h1>Olá, {{ data.affiliate?.first_name }}! {{ data.affiliate?.tier_icon }}</h1>
            <p class="tier-badge">{{ data.affiliate?.tier_name }} • {{ data.affiliate?.commission_rate }}% de comissão</p>
          </div>
          <div class="affiliate-code">
            <span class="code-label">Seu código:</span>
            <span class="code-value" @click="copyToClipboard(data.affiliate?.hash)">
              {{ data.affiliate?.hash }}
              <i class="fas fa-copy"></i>
            </span>
          </div>
        </div>

        <!-- Tier Alert -->
        <div v-if="data.tier_status?.at_risk" class="tier-alert">
          <i class="fas fa-exclamation-triangle"></i>
          <p>{{ data.tier_status.message }}</p>
        </div>

        <!-- Next Tier Progress -->
        <div v-if="data.tier_status?.next_tier" class="next-tier-card">
          <div class="next-tier-header">
            <span>Próxima liga: {{ data.tier_status.next_tier.icon }} {{ data.tier_status.next_tier.name }}</span>
            <span class="next-tier-commission">{{ data.tier_status.next_tier.commission }}%</span>
          </div>
          <div class="progress-bar">
            <div class="progress-fill" :style="{ width: nextTierProgress + '%' }"></div>
          </div>
          <p class="progress-text">
            Faltam <strong>R$ {{ formatNumber(data.tier_status.next_tier.amount_needed) }}</strong> para subir de liga
          </p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-info">
              <span class="stat-value">{{ data.stats?.total_referrals || 0 }}</span>
              <span class="stat-label">Indicações</span>
            </div>
          </div>
          <div class="stat-card accent">
            <div class="stat-icon"><i class="fas fa-handshake"></i></div>
            <div class="stat-info">
              <span class="stat-value">{{ data.stats?.total_sales_closed || 0 }}</span>
              <span class="stat-label">Vendas Fechadas</span>
            </div>
          </div>
          <div class="stat-card gold">
            <div class="stat-icon"><i class="fas fa-coins"></i></div>
            <div class="stat-info">
              <span class="stat-value">R$ {{ formatNumber(data.stats?.total_sales_amount || 0) }}</span>
              <span class="stat-label">Total Vendido</span>
            </div>
          </div>
          <div class="stat-card green">
            <div class="stat-icon"><i class="fas fa-wallet"></i></div>
            <div class="stat-info">
              <span class="stat-value">R$ {{ formatNumber(data.stats?.pending_commission || 0) }}</span>
              <span class="stat-label">Comissão Pendente</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
              <span class="stat-value">R$ {{ formatNumber(data.stats?.paid_commission || 0) }}</span>
              <span class="stat-label">Comissão Paga</span>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="stat-info">
              <span class="stat-value">R$ {{ formatNumber(data.stats?.last_3_months_sales || 0) }}</span>
              <span class="stat-label">Vendas (3 meses)</span>
            </div>
          </div>
        </div>

        <!-- Payment Info -->
        <div class="payment-info">
          <i class="fas fa-info-circle"></i>
          <div>
            <strong>Sobre pagamentos:</strong>
            <p>As comissões são pagas a partir do <strong>5º dia útil</strong> de cada mês, em até <strong>30 dias úteis</strong> após a venda ser confirmada. Pagamento feito manualmente via PIX.</p>
          </div>
        </div>
      </div>

      <!-- Referrals Tab -->
      <div v-else-if="activeTab === 'referrals'" class="tab-content">
        <h2><i class="fas fa-users"></i> Minhas Indicações</h2>
        
        <!-- Filter -->
        <div class="filter-bar">
          <select v-model="referralFilter" @change="loadReferrals(1)">
            <option value="">Todos os status</option>
            <option value="lead">Lead</option>
            <option value="contacted">Contatado</option>
            <option value="negotiating">Em negociação</option>
            <option value="closed">Venda fechada</option>
            <option value="onboarding">Onboarding</option>
            <option value="completed">Concluído</option>
            <option value="lost">Perdido</option>
          </select>
        </div>

        <!-- Referrals List -->
        <div v-if="referrals.length > 0" class="referrals-list">
          <div v-for="ref in referrals" :key="ref.id" class="referral-card">
            <div class="referral-header">
              <span class="referral-name">{{ ref.lead_name || 'Lead sem nome' }}</span>
              <span :class="['status-badge', 'status-' + ref.status]">
                {{ statusLabels[ref.status] || ref.status }}
              </span>
            </div>
            <div class="referral-details">
              <span v-if="ref.lead_email"><i class="fas fa-envelope"></i> {{ ref.lead_email }}</span>
              <span v-if="ref.source_page"><i class="fas fa-link"></i> {{ ref.source_page }}</span>
              <span><i class="fas fa-calendar"></i> {{ formatDate(ref.created_at) }}</span>
            </div>
            <div v-if="ref.sale_amount" class="referral-financials">
              <span class="sale-value">Venda: R$ {{ formatNumber(ref.sale_amount) }}</span>
              <span class="commission-value" :class="{ paid: ref.commission_paid }">
                Comissão: R$ {{ formatNumber(ref.commission_amount) }}
                <span v-if="ref.commission_paid" class="paid-tag">✓ Paga</span>
                <span v-else class="pending-tag">Pendente</span>
              </span>
            </div>
          </div>
        </div>
        <div v-else class="empty-state">
          <i class="fas fa-inbox"></i>
          <p>Nenhuma indicação ainda. Compartilhe seus links para começar!</p>
        </div>

        <!-- Pagination -->
        <div v-if="pagination.total_pages > 1" class="pagination">
          <button :disabled="pagination.page <= 1" @click="loadReferrals(pagination.page - 1)">
            <i class="fas fa-chevron-left"></i>
          </button>
          <span>{{ pagination.page }} / {{ pagination.total_pages }}</span>
          <button :disabled="pagination.page >= pagination.total_pages" @click="loadReferrals(pagination.page + 1)">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>

      <!-- Links Tab -->
      <div v-else-if="activeTab === 'links'" class="tab-content">
        <h2><i class="fas fa-link"></i> Meus Links de Indicação</h2>
        <p class="tab-description">Compartilhe estes links para indicar clientes. Quando alguém clicar, seu código será salvo automaticamente por 30 dias.</p>
        
        <div class="links-list">
          <div v-for="link in data.links" :key="link.page" class="link-card">
            <div class="link-header">
              <i class="fas fa-external-link-alt"></i>
              <span class="link-name">{{ link.name }}</span>
            </div>
            <div class="link-url" @click="copyToClipboard(link.url)">
              <span>{{ link.url }}</span>
              <button class="btn-copy"><i class="fas fa-copy"></i> Copiar</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Calculator Tab -->
      <div v-else-if="activeTab === 'calculator'" class="tab-content">
        <h2><i class="fas fa-calculator"></i> Simulador de Comissão</h2>
        <p class="tab-description">Veja quanto pode ganhar de comissão baseado no seu título atual e no produto vendido.</p>

        <!-- Commission Table -->
        <div class="commission-table-wrapper">
          <table class="commission-table">
            <thead>
              <tr>
                <th>Liga</th>
                <th>Comissão</th>
                <th>Site Vitrine (R$ 599)</th>
                <th>Multi-páginas (R$ 926)</th>
                <th>Pacote Autoridade (R$ 1.174)</th>
                <th>Ecossistema Digital (R$ 1.972)</th>
                <th>Consultoria (R$ 20.990)</th>
                <th>Transformação Digital Empresarial (R$ 50.000)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="tier in allTiers" :key="tier.key" :class="{ 'current-tier': tier.key === data.affiliate?.tier }">
                <td class="tier-cell">
                  <span class="tier-icon">{{ tier.icon }}</span>
                  {{ tier.name }}
                  <span v-if="tier.key === data.affiliate?.tier" class="you-badge">Você</span>
                </td>
                <td class="commission-cell">{{ tier.commission }}%</td>
                <td>R$ {{ formatNumber(599 * tier.commission / 100) }}</td>
                <td>R$ {{ formatNumber(926 * tier.commission / 100) }}</td>
                <td>R$ {{ formatNumber(1174 * tier.commission / 100) }}</td>
                <td>R$ {{ formatNumber(1972 * tier.commission / 100) }}</td>
                <td>R$ {{ formatNumber(20990 * tier.commission / 100) }}</td>
                <td>R$ {{ formatNumber(50000 * tier.commission / 100) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Custom Simulator -->
        <div class="custom-simulator">
          <h3>Simulação personalizada</h3>
          <div class="simulator-row">
            <div class="sim-field">
              <label>Valor da venda (R$)</label>
              <input type="number" v-model.number="simValue" min="0" step="100" placeholder="Ex: 1500" />
            </div>
            <div class="sim-result" v-if="simTier">
              <div class="sim-tier-badge">
                <span class="sim-tier-icon">{{ simTier.icon }}</span>
                <span class="sim-tier-name">{{ simTier.name }}</span>
              </div>
              <span class="sim-label">Comissão ({{ simTier.commission }}%):</span>
              <span class="sim-amount">R$ {{ formatNumber(simValue * simTier.commission / 100) }}</span>
            </div>
          </div>
        </div>

        <div class="commission-note">
          <i class="fas fa-info-circle"></i>
          <div>
            <p>Comissões pagas em até <strong>30 dias úteis</strong> após a venda ser confirmada.</p>
            <p>Pagamos a partir do <strong>5º dia útil</strong> de cada mês (por enquanto, feito manualmente via PIX).</p>
          </div>
        </div>
      </div>

      <!-- Missões Tab -->
      <div v-else-if="activeTab === 'missions'" class="tab-content">
        <h2><i class="fas fa-crosshairs"></i> Missões da Semana</h2>
        <p class="tab-description" v-if="missionsWeek.start">
          Semana de {{ formatDate(missionsWeek.start) }} a {{ formatDate(missionsWeek.end) }}
          <span v-if="missionsWeek.days_left > 0" class="days-left-badge">
            {{ missionsWeek.days_left }} dia{{ missionsWeek.days_left > 1 ? 's' : '' }} restante{{ missionsWeek.days_left > 1 ? 's' : '' }}
          </span>
        </p>
        <p class="tab-description" v-else>Missões resetam toda segunda-feira. Complete para ganhar XP e bônus!</p>

        <div v-if="missions.length > 0" class="missions-grid">
          <div v-for="mission in missions" :key="mission.id" :class="['mission-card', { completed: mission.completed == 1 }]">
            <div class="mission-header">
              <span class="mission-icon">{{ mission.icon_emoji || '🎯' }}</span>
              <span :class="['mission-category', 'cat-' + mission.category]">
                {{ categoryLabels[mission.category] || mission.category }}
              </span>
              <span v-if="mission.completed == 1" class="mission-done-badge">
                <i class="fas fa-check-circle"></i> Completa
              </span>
            </div>
            <h3 class="mission-title">{{ mission.title }}</h3>
            <p class="mission-desc">{{ mission.description }}</p>
            <div class="mission-progress">
              <div class="mission-progress-bar">
                <div class="mission-progress-fill" :style="{ width: mission.progress_percent + '%' }"></div>
              </div>
              <span class="mission-progress-text">
                {{ formatMissionValue(mission.current_value, mission.target_unit) }}
                / {{ formatMissionValue(mission.target_value, mission.target_unit) }}
              </span>
            </div>
            <div class="mission-reward">
              <i class="fas fa-gift"></i>
              <span v-if="mission.reward_type === 'xp'">+{{ mission.reward_value }} XP</span>
              <span v-else-if="mission.reward_type === 'commission_bonus'">+{{ mission.reward_value }}% comissão</span>
              <span v-else>Medalha especial</span>
            </div>
          </div>
        </div>
        <div v-else class="empty-state">
          <i class="fas fa-crosshairs"></i>
          <p>Nenhuma missão disponível no momento. Em breve teremos missões para você!</p>
        </div>

        <div v-if="missions.length > 0" class="missions-summary">
          <div class="missions-summary-card">
            <span class="summary-number">{{ missionsCompleted }}</span>
            <span class="summary-label">Completas</span>
          </div>
          <div class="missions-summary-card">
            <span class="summary-number">{{ missions.length - missionsCompleted }}</span>
            <span class="summary-label">Pendentes</span>
          </div>
          <div class="missions-summary-card">
            <span class="summary-number">{{ missions.length }}</span>
            <span class="summary-label">Total</span>
          </div>
        </div>
      </div>

      <!-- Conquistas/Badges Tab -->
      <div v-else-if="activeTab === 'badges'" class="tab-content">
        <h2><i class="fas fa-award"></i> Minhas Conquistas</h2>
        <p class="tab-description">Medalhas permanentes que mostram sua trajetória como afiliado.</p>

        <!-- Minhas medalhas -->
        <div v-if="myBadges.length > 0" class="badges-shelf">
          <h3 class="badges-section-title">
            <i class="fas fa-star"></i> Sua Estante de Medalhas ({{ myBadges.length }})
          </h3>
          <div class="badges-grid">
            <div v-for="badge in myBadges" :key="badge.id" class="badge-card earned">
              <div class="badge-icon-big">{{ badge.icon_emoji || '🏅' }}</div>
              <div class="badge-info">
                <h4>{{ badge.title }}</h4>
                <p>{{ badge.description }}</p>
                <span class="badge-date">
                  <i class="fas fa-calendar-check"></i> {{ formatDate(badge.awarded_at) }}
                </span>
              </div>
              <span :class="['badge-type-tag', 'type-' + badge.type]">
                {{ badgeTypeLabels[badge.type] || badge.type }}
              </span>
            </div>
          </div>
        </div>
        <div v-else class="empty-state">
          <i class="fas fa-award"></i>
          <p>Você ainda não conquistou nenhuma medalha. Continue vendendo e participando para desbloquear!</p>
        </div>

        <!-- Catálogo completo -->
        <div v-if="allBadges.length > 0" class="badges-catalog">
          <h3 class="badges-section-title">
            <i class="fas fa-book-open"></i> Catálogo de Conquistas
          </h3>
          <div v-for="(badges, type) in allBadgesGrouped" :key="type" class="badge-type-group">
            <h4 class="badge-type-title" v-if="badges.length > 0">
              {{ badgeTypeLabels[type] || type }}
            </h4>
            <div class="badges-grid" v-if="badges.length > 0">
              <div v-for="badge in badges" :key="badge.id"
                   :class="['badge-card', { earned: isBadgeEarned(badge.id), locked: !isBadgeEarned(badge.id) }]">
                <div class="badge-icon-big">{{ badge.icon_emoji || '🏅' }}</div>
                <div class="badge-info">
                  <h4>{{ badge.title }}</h4>
                  <p>{{ badge.description }}</p>
                </div>
                <div v-if="isBadgeEarned(badge.id)" class="badge-earned-check">
                  <i class="fas fa-check-circle"></i>
                </div>
                <div v-else class="badge-locked-icon">
                  <i class="fas fa-lock"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Ranking Tab -->
      <div v-else-if="activeTab === 'ranking'" class="tab-content">
        <h2><i class="fas fa-trophy"></i> Ranking de Afiliados</h2>
        <p class="tab-description">Os melhores afiliados ranqueados por valor total vendido.</p>

        <div v-if="ranking.length > 0" class="ranking-list">
          <div v-for="(item, idx) in ranking" :key="idx" :class="['ranking-item', { 'top-3': item.position <= 3 }]"
               @click="openProfile(item.id)" style="cursor: pointer;">
            <div class="rank-position">
              <span v-if="item.position === 1">🥇</span>
              <span v-else-if="item.position === 2">🥈</span>
              <span v-else-if="item.position === 3">🥉</span>
              <span v-else class="rank-number">#{{ item.position }}</span>
            </div>
            <div class="rank-info">
              <span class="rank-name">{{ item.name }}</span>
              <span class="rank-tier">{{ item.tier_icon }} {{ item.tier_name }}</span>
            </div>
            <div v-if="item.badges && item.badges.length > 0" class="rank-badges">
              <span v-for="(badge, bi) in item.badges" :key="bi" class="rank-badge-icon" :title="badge.title">
                {{ badge.icon }}
              </span>
              <span v-if="item.badge_count > 5" class="rank-badge-more">+{{ item.badge_count - 5 }}</span>
            </div>
            <div class="rank-score">
              R$ {{ formatNumber(item.score) }}
            </div>
          </div>
        </div>
        <div v-else class="empty-state">
          <i class="fas fa-trophy"></i>
          <p>O ranking ainda está vazio. Seja o primeiro!</p>
        </div>
      </div>

      <!-- Tiers Tab -->
      <div v-else-if="activeTab === 'tiers'" class="tab-content">
        <h2><i class="fas fa-medal"></i> Sistema de Ligas</h2>
        <p class="tab-description">Evolua seu título conforme realiza vendas e ganhe comissões maiores!</p>

        <div class="tiers-list">
          <div v-for="tier in allTiers" :key="tier.key" 
               :class="['tier-card', { active: tier.key === data.affiliate?.tier, locked: tier.min_sales > (data.stats?.total_sales_amount || 0) }]">
            <div class="tier-icon-big">{{ tier.icon }}</div>
            <div class="tier-info">
              <h3>{{ tier.name }}</h3>
              <p class="tier-commission">{{ tier.commission }}% de comissão</p>
              <p class="tier-requirement" v-if="tier.min_sales > 0">
                A partir de R$ {{ formatNumber(tier.min_sales) }} em vendas
              </p>
              <p class="tier-requirement" v-else>Disponível desde o cadastro</p>
            </div>
            <div v-if="tier.key === data.affiliate?.tier" class="tier-current-badge">
              <i class="fas fa-check-circle"></i> Seu título atual
            </div>
          </div>
        </div>

        <div class="tier-rules">
          <h3><i class="fas fa-gavel"></i> Regras das Ligas</h3>
          <ul>
            <li>Cada liga aumenta a comissão em <strong>1.25%</strong></li>
            <li>Começa em <strong>5%</strong> (Bronze 1) e vai até <strong>15%</strong> (Diamante 2)</li>
            <li>Para subir de liga, basta bater a meta <strong>1 vez</strong></li>
            <li>Para manter o título, é preciso bater a mesma meta em <strong>pelo menos 1 dos 3 meses</strong> — contando o mês em que a meta foi batida</li>
            <li>Se nos <strong>3 meses</strong> (incluindo o mês em que subiu) não bater novamente, você cai para a última maior meta já atingida</li>
            <li>Você será avisado quando seu título estiver em risco</li>
          </ul>
        </div>
      </div>
    </main>

    <!-- Profile Modal -->
    <div v-if="showProfileModal" class="profile-modal-overlay" @click="showProfileModal = false">
      <div class="profile-modal" @click.stop>
        <button class="profile-close" @click="showProfileModal = false">
          <i class="fas fa-times"></i>
        </button>
        <div v-if="profileData" class="profile-content">
          <div class="profile-header-card">
            <span class="profile-tier-icon">{{ profileData.profile.tier_icon }}</span>
            <div class="profile-info">
              <h3>{{ profileData.profile.name }}</h3>
              <span class="profile-tier-name">{{ profileData.profile.tier_name }}</span>
              <span class="profile-member-since">
                <i class="fas fa-calendar-alt"></i>
                Membro há {{ profileData.profile.member_days }} dias
              </span>
            </div>
          </div>
          <div class="profile-stats-row">
            <div class="profile-stat">
              <span class="profile-stat-value">R$ {{ formatNumber(profileData.profile.total_sales) }}</span>
              <span class="profile-stat-label">Vendas</span>
            </div>
            <div class="profile-stat">
              <span class="profile-stat-value">{{ profileData.badge_count }}</span>
              <span class="profile-stat-label">Medalhas</span>
            </div>
          </div>
          <div v-if="profileData.badges.length > 0" class="profile-badges-section">
            <h4><i class="fas fa-award"></i> Conquistas</h4>
            <div class="profile-badges-grid">
              <div v-for="badge in profileData.badges" :key="badge.id" class="profile-badge-item">
                <span class="profile-badge-icon">{{ badge.icon_emoji || '🏅' }}</span>
                <span class="profile-badge-title">{{ badge.title }}</span>
              </div>
            </div>
          </div>
          <div v-else class="profile-no-badges">
            <p>Ainda sem conquistas desbloqueadas.</p>
          </div>
        </div>
        <div v-else class="loading-state" style="height: auto; padding: 40px;">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Carregando perfil...</p>
        </div>
      </div>
    </div>

    <!-- Copy Toast -->
    <div v-if="showCopyToast" class="copy-toast">
      <i class="fas fa-check-circle"></i> Copiado!
    </div>
  </div>
</template>

<script>
import { useAffiliateAuth } from '@/core/composables/useAffiliateAuth';

export default {
  name: 'AffiliateDashboard',
  data() {
    return {
      activeTab: 'dashboard',
      showMobileMenu: false,
      loading: true,
      data: {},
      referrals: [],
      pagination: { page: 1, total_pages: 1 },
      referralFilter: '',
      ranking: [],
      allTiers: [],
      missions: [],
      missionsWeek: {},
      missionsCompleted: 0,
      myBadges: [],
      allBadges: [],
      allBadgesGrouped: {},
      showProfileModal: false,
      profileData: null,
      simValue: 1500,
      showCopyToast: false,
      categoryLabels: {
        explorer: 'Explorador',
        lead_hunter: 'Caçador de Leads',
        closer: 'Fechador',
        consistency: 'Consistência'
      },
      badgeTypeLabels: {
        event: '🎓 Eventos',
        achievement: '📈 Conquistas',
        legacy: '🛡️ Legado'
      },
      tabs: [
        { id: 'dashboard', icon: 'fas fa-home', label: 'Dashboard' },
        { id: 'referrals', icon: 'fas fa-users', label: 'Indicações' },
        { id: 'links', icon: 'fas fa-link', label: 'Meus Links' },
        { id: 'calculator', icon: 'fas fa-calculator', label: 'Simulador' },
        { id: 'missions', icon: 'fas fa-crosshairs', label: 'Missões' },
        { id: 'badges', icon: 'fas fa-award', label: 'Conquistas' },
        { id: 'ranking', icon: 'fas fa-trophy', label: 'Ranking' },
        { id: 'tiers', icon: 'fas fa-medal', label: 'Ligas' }
      ],
      statusLabels: {
        lead: 'Lead',
        contacted: 'Contatado',
        negotiating: 'Em negociação',
        closed: 'Venda fechada',
        onboarding: 'Onboarding',
        completed: 'Concluído',
        lost: 'Perdido'
      }
    };
  },
  computed: {
    nextTierProgress() {
      if (!this.data.tier_status?.next_tier) return 100;
      const needed = this.data.tier_status.next_tier.min_sales;
      const current = this.data.stats?.total_sales_amount || 0;
      return Math.min(100, Math.round((current / needed) * 100));
    },
    simTier() {
      if (!this.allTiers.length) return null;
      const sorted = [...this.allTiers].sort((a, b) => a.min_sales - b.min_sales);
      let result = sorted[0];
      for (const tier of sorted) {
        if (this.simValue >= tier.min_sales) result = tier;
      }
      return result;
    }
  },
  async created() {
    const { validate } = useAffiliateAuth();
    const valid = await validate();
    if (!valid) {
      this.$router.push('/afiliados');
      return;
    }
    await this.loadDashboard();
    this.loadTiers();
  },
  watch: {
    activeTab(tab) {
      if (tab === 'referrals' && this.referrals.length === 0) this.loadReferrals(1);
      if (tab === 'ranking' && this.ranking.length === 0) this.loadRanking();
      if (tab === 'missions' && this.missions.length === 0) this.loadMissions();
      if (tab === 'badges' && this.myBadges.length === 0) this.loadBadges();
    }
  },
  methods: {
    async loadDashboard() {
      this.loading = true;
      const { authHeaders } = useAffiliateAuth();
      try {
        const res = await fetch('/api/affiliate/dashboard.php', { headers: authHeaders() });
        const result = await res.json();
        if (result.ok) {
          this.data = result;
        }
      } catch (e) {
        console.error('Erro ao carregar dashboard:', e);
      } finally {
        this.loading = false;
      }
    },

    async loadReferrals(page) {
      const { authHeaders } = useAffiliateAuth();
      try {
        let url = `/api/affiliate/dashboard.php?action=referrals&page=${page}`;
        if (this.referralFilter) url += `&status=${this.referralFilter}`;
        const res = await fetch(url, { headers: authHeaders() });
        const result = await res.json();
        if (result.ok) {
          this.referrals = result.referrals;
          this.pagination = result.pagination;
        }
      } catch (e) {
        console.error('Erro ao carregar indicações:', e);
      }
    },

    async loadRanking() {
      const { authHeaders } = useAffiliateAuth();
      try {
        const res = await fetch('/api/affiliate/dashboard.php?action=ranking', { headers: authHeaders() });
        const result = await res.json();
        if (result.ok) {
          this.ranking = result.ranking;
        }
      } catch (e) {
        console.error('Erro ao carregar ranking:', e);
      }
    },

    async loadTiers() {
      try {
        const res = await fetch('/api/affiliate/dashboard.php?action=tiers');
        const result = await res.json();
        if (result.ok) {
          this.allTiers = result.tiers;
        }
      } catch (e) {
        console.error('Erro ao carregar tiers:', e);
      }
    },

    async loadMissions() {
      const { authHeaders } = useAffiliateAuth();
      try {
        const res = await fetch('/api/affiliate/badges.php?action=missions', { headers: authHeaders() });
        const result = await res.json();
        if (result.ok) {
          this.missions = result.missions;
          this.missionsWeek = result.week;
          this.missionsCompleted = result.completed;
        }
      } catch (e) {
        console.error('Erro ao carregar missões:', e);
      }
    },

    async loadBadges() {
      const { authHeaders } = useAffiliateAuth();
      try {
        // Carregar minhas badges e catálogo em paralelo
        const [myRes, allRes] = await Promise.all([
          fetch('/api/affiliate/badges.php?action=badges', { headers: authHeaders() }),
          fetch('/api/affiliate/badges.php?action=all_badges', { headers: authHeaders() })
        ]);
        const myResult = await myRes.json();
        const allResult = await allRes.json();
        if (myResult.ok) {
          this.myBadges = myResult.badges;
        }
        if (allResult.ok) {
          this.allBadges = allResult.badges;
          this.allBadgesGrouped = allResult.grouped;
        }
      } catch (e) {
        console.error('Erro ao carregar badges:', e);
      }
    },

    isBadgeEarned(badgeId) {
      return this.myBadges.some(b => b.id === badgeId || b.id === String(badgeId));
    },

    async openProfile(affiliateId) {
      this.showProfileModal = true;
      this.profileData = null;
      const { authHeaders } = useAffiliateAuth();
      try {
        const res = await fetch(`/api/affiliate/badges.php?action=profile&id=${encodeURIComponent(affiliateId)}`, { headers: authHeaders() });
        const result = await res.json();
        if (result.ok) {
          this.profileData = result;
        }
      } catch (e) {
        console.error('Erro ao carregar perfil:', e);
      }
    },

    formatMissionValue(value, unit) {
      const val = Number(value);
      if (unit === 'currency') return 'R$ ' + this.formatNumber(val);
      if (unit === 'days') return val + ' dia' + (val !== 1 ? 's' : '');
      return Math.floor(val);
    },

    handleLogout() {
      const { logout } = useAffiliateAuth();
      logout();
      this.$router.push('/afiliados');
    },

    formatNumber(val) {
      if (!val && val !== 0) return '0,00';
      return Number(val).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },

    formatDate(dateStr) {
      if (!dateStr) return '';
      return new Date(dateStr).toLocaleDateString('pt-BR');
    },

    async copyToClipboard(text) {
      if (!text) return;
      try {
        await navigator.clipboard.writeText(text);
        this.showCopyToast = true;
        setTimeout(() => { this.showCopyToast = false; }, 2000);
      } catch {
        // Fallback
        const el = document.createElement('textarea');
        el.value = text;
        document.body.appendChild(el);
        el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        this.showCopyToast = true;
        setTimeout(() => { this.showCopyToast = false; }, 2000);
      }
    }
  }
};
</script>

<style lang="scss" scoped>
$green: #10B981;
$green-dark: #059669;
$dark: #0D1117;
$card-bg: rgba(255, 255, 255, 0.05);
$border: rgba(255, 255, 255, 0.1);
$gold: #F59E0B;

.aff-dashboard {
  display: flex;
  min-height: calc(100vh - 80px);
  margin-top: 80px;
  background: linear-gradient(135deg, $dark 0%, #161B22 50%, #0D4429 100%);
  color: #fff;
}

// Sidebar
.aff-sidebar {
  position: fixed;
  top: 80px;
  left: 0;
  width: 240px;
  height: calc(100vh - 80px);
  background: rgba(0, 0, 0, 0.3);
  border-right: 1px solid $border;
  display: flex;
  flex-direction: column;
  z-index: 100;

  @media (max-width: 768px) {
    display: none;
  }
}

.sidebar-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px;
  border-bottom: 1px solid $border;

  .sidebar-icon { font-size: 24px; }

  h2 {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
  }
}

.sidebar-nav {
  flex: 1;
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.sidebar-footer {
  padding: 12px;
  border-top: 1px solid $border;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 14px;
  background: none;
  border: none;
  border-radius: 10px;
  color: rgba(255, 255, 255, 0.6);
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  width: 100%;
  text-align: left;

  i { width: 18px; text-align: center; }

  &:hover {
    background: rgba(255, 255, 255, 0.06);
    color: #fff;
  }

  &.active {
    background: rgba($green, 0.15);
    color: $green;
  }

  &.logout {
    color: rgba(255, 255, 255, 0.4);
    &:hover { color: #EF4444; background: rgba(239, 68, 68, 0.1); }
  }
}

// Mobile Header
.aff-mobile-header {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 56px;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid $border;
  align-items: center;
  justify-content: space-between;
  padding: 0 16px;
  z-index: 100;
  font-weight: 700;

  @media (max-width: 768px) {
    display: flex;
  }

  button {
    background: none;
    border: none;
    color: #fff;
    font-size: 18px;
    cursor: pointer;
    padding: 8px;
  }
}

.mobile-menu-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  z-index: 200;

  @media (max-width: 768px) {
    display: block;
  }

  .mobile-menu {
    position: absolute;
    top: 56px;
    left: 0;
    right: 0;
    background: #161B22;
    border-bottom: 1px solid $border;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 4px;
  }
}

// Main Content
.aff-main {
  flex: 1;
  margin-left: 240px;
  padding: 32px;
  min-height: 100vh;

  @media (max-width: 768px) {
    margin-left: 0;
    padding: 72px 16px 16px;
  }
}

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 60vh;
  gap: 16px;
  color: rgba(255, 255, 255, 0.5);

  i { font-size: 32px; }
}

// Welcome Section
.welcome-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;

  h1 {
    font-size: 28px;
    font-weight: 800;
    margin: 0 0 4px;
  }

  .tier-badge {
    color: $green;
    font-weight: 600;
    font-size: 15px;
    margin: 0;
  }
}

.affiliate-code {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background: $card-bg;
  border: 1px solid $border;
  border-radius: 10px;

  .code-label {
    color: rgba(255, 255, 255, 0.5);
    font-size: 13px;
  }

  .code-value {
    font-family: monospace;
    font-size: 16px;
    font-weight: 700;
    color: $green;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;

    i {
      font-size: 13px;
      color: rgba(255, 255, 255, 0.4);
    }

    &:hover i { color: $green; }
  }
}

// Tier Alert
.tier-alert {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: rgba($gold, 0.1);
  border: 1px solid rgba($gold, 0.3);
  border-radius: 12px;
  margin-bottom: 24px;

  i { color: $gold; font-size: 20px; flex-shrink: 0; }
  p { margin: 0; color: rgba(255, 255, 255, 0.8); font-size: 14px; }
}

// Next Tier Progress
.next-tier-card {
  padding: 20px;
  background: $card-bg;
  border: 1px solid $border;
  border-radius: 14px;
  margin-bottom: 24px;

  .next-tier-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    font-size: 14px;

    .next-tier-commission {
      color: $green;
      font-weight: 700;
      font-size: 16px;
    }
  }

  .progress-bar {
    height: 10px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    overflow: hidden;

    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, $green, $gold);
      border-radius: 10px;
      transition: width 0.5s ease;
    }
  }

  .progress-text {
    margin: 10px 0 0;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.6);

    strong { color: $green; }
  }
}

// Stats Grid
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
  background: $card-bg;
  border: 1px solid $border;
  border-radius: 14px;
  transition: transform 0.2s;

  &:hover { transform: translateY(-2px); }

  .stat-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba($green, 0.1);
    border-radius: 12px;
    i { font-size: 20px; color: $green; }
  }

  &.accent .stat-icon { background: rgba(139, 92, 246, 0.1); i { color: #8B5CF6; } }
  &.gold .stat-icon { background: rgba($gold, 0.1); i { color: $gold; } }
  &.green .stat-icon { background: rgba($green, 0.15); i { color: $green; } }

  .stat-info {
    display: flex;
    flex-direction: column;
  }

  .stat-value {
    font-size: 22px;
    font-weight: 800;
    line-height: 1.2;
  }

  .stat-label {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
  }
}

// Payment Info
.payment-info {
  display: flex;
  gap: 12px;
  padding: 16px;
  background: rgba($green, 0.06);
  border: 1px solid rgba($green, 0.15);
  border-radius: 12px;

  > i { color: $green; font-size: 18px; flex-shrink: 0; margin-top: 2px; }

  strong { color: rgba(255, 255, 255, 0.9); }
  p { margin: 4px 0 0; color: rgba(255, 255, 255, 0.6); font-size: 13px; line-height: 1.6; }
}

// Tab Content
.tab-content {
  h2 {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 24px;
    font-weight: 800;
    margin: 0 0 8px;
    i { color: $green; }
  }

  .tab-description {
    color: rgba(255, 255, 255, 0.5);
    font-size: 14px;
    margin: 0 0 24px;
  }
}

// Filter Bar
.filter-bar {
  margin-bottom: 20px;

  select {
    padding: 10px 14px;
    background: $card-bg;
    border: 1px solid $border;
    border-radius: 8px;
    color: #fff;
    font-size: 14px;
    cursor: pointer;

    option { background: #161B22; }
  }
}

// Referrals
.referrals-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.referral-card {
  padding: 16px;
  background: $card-bg;
  border: 1px solid $border;
  border-radius: 12px;

  .referral-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
  }

  .referral-name {
    font-weight: 600;
    font-size: 15px;
  }

  .referral-details {
    display: flex;
    gap: 16px;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
    margin-bottom: 8px;
    flex-wrap: wrap;

    i { margin-right: 4px; }
  }

  .referral-financials {
    display: flex;
    gap: 16px;
    font-size: 14px;
    padding-top: 8px;
    border-top: 1px solid $border;
    flex-wrap: wrap;

    .sale-value { color: rgba(255,255,255,0.7); }
    .commission-value {
      color: $gold;
      font-weight: 600;
      &.paid { color: $green; }
    }
    .paid-tag { color: $green; font-size: 12px; }
    .pending-tag { color: $gold; font-size: 12px; }
  }
}

.status-badge {
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;

  &.status-lead { background: rgba(99, 102, 241, 0.15); color: #818CF8; }
  &.status-contacted { background: rgba(59, 130, 246, 0.15); color: #60A5FA; }
  &.status-negotiating { background: rgba($gold, 0.15); color: $gold; }
  &.status-closed { background: rgba($green, 0.15); color: $green; }
  &.status-onboarding { background: rgba(139, 92, 246, 0.15); color: #A78BFA; }
  &.status-completed { background: rgba($green, 0.2); color: $green; }
  &.status-lost { background: rgba(239, 68, 68, 0.15); color: #F87171; }
}

// Pagination
.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-top: 24px;

  button {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: $card-bg;
    border: 1px solid $border;
    border-radius: 8px;
    color: #fff;
    cursor: pointer;

    &:disabled { opacity: 0.3; cursor: not-allowed; }
    &:hover:not(:disabled) { background: rgba($green, 0.15); }
  }

  span {
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
  }
}

// Links
.links-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.link-card {
  padding: 16px;
  background: $card-bg;
  border: 1px solid $border;
  border-radius: 12px;

  .link-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    font-weight: 600;

    i { color: $green; }
  }

  .link-url {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s;

    &:hover { background: rgba(0, 0, 0, 0.3); }

    span {
      flex: 1;
      font-family: monospace;
      font-size: 13px;
      color: rgba(255, 255, 255, 0.7);
      word-break: break-all;
    }

    .btn-copy {
      flex-shrink: 0;
      padding: 6px 12px;
      background: rgba($green, 0.15);
      border: 1px solid rgba($green, 0.3);
      border-radius: 6px;
      color: $green;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;

      &:hover { background: $green; color: #fff; }
    }
  }
}

// Commission Table
.commission-table-wrapper {
  overflow-x: auto;
  margin-bottom: 24px;
  border-radius: 12px;
  border: 1px solid $border;
}

.commission-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;

  th, td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid $border;
    white-space: nowrap;
  }

  th {
    background: rgba(0, 0, 0, 0.3);
    color: rgba(255, 255, 255, 0.7);
    font-weight: 600;
    font-size: 13px;
  }

  td {
    color: rgba(255, 255, 255, 0.8);
  }

  .tier-cell {
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;

    .tier-icon { font-size: 18px; }
  }

  .commission-cell {
    color: $green;
    font-weight: 700;
  }

  .you-badge {
    font-size: 10px;
    background: $green;
    color: #fff;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 700;
  }

  .current-tier {
    background: rgba($green, 0.06);
    td { color: #fff; }
  }
}

// Custom Simulator
.custom-simulator {
  padding: 20px;
  background: $card-bg;
  border: 1px solid $border;
  border-radius: 12px;
  margin-bottom: 24px;

  h3 {
    font-size: 16px;
    font-weight: 700;
    margin: 0 0 16px;
  }

  .simulator-row {
    display: flex;
    align-items: flex-end;
    gap: 24px;
    flex-wrap: wrap;
  }

  .sim-field {
    label {
      display: block;
      font-size: 13px;
      color: rgba(255, 255, 255, 0.6);
      margin-bottom: 6px;
    }
    input {
      padding: 10px 14px;
      background: rgba(0, 0, 0, 0.2);
      border: 1px solid $border;
      border-radius: 8px;
      color: #fff;
      font-size: 16px;
      width: 200px;

      &:focus { outline: none; border-color: $green; }
    }
  }

  .sim-result {
    display: flex;
    flex-direction: column;
    gap: 4px;

    .sim-tier-badge {
      display: flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 4px;

      .sim-tier-icon { font-size: 18px; }
      .sim-tier-name {
        font-size: 13px;
        font-weight: 700;
        color: $green;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }
    }
    .sim-label {
      font-size: 13px;
      color: rgba(255, 255, 255, 0.5);
    }
    .sim-amount {
      font-size: 28px;
      font-weight: 900;
      color: $green;
    }
  }
}

// Commission Note
.commission-note {
  display: flex;
  gap: 12px;
  padding: 16px;
  background: rgba($green, 0.06);
  border: 1px solid rgba($green, 0.15);
  border-radius: 12px;

  > i { color: $green; font-size: 18px; flex-shrink: 0; margin-top: 2px; }
  p { margin: 4px 0; color: rgba(255, 255, 255, 0.6); font-size: 13px; line-height: 1.6; }
  strong { color: rgba(255, 255, 255, 0.9); }
}

// Ranking
.ranking-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.ranking-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 14px 20px;
  background: $card-bg;
  border: 1px solid $border;
  border-radius: 12px;
  transition: transform 0.2s;

  &:hover { transform: translateX(4px); }

  &.top-3 {
    background: rgba($gold, 0.05);
    border-color: rgba($gold, 0.2);
  }

  .rank-position {
    width: 40px;
    text-align: center;
    font-size: 24px;

    .rank-number {
      font-size: 16px;
      font-weight: 700;
      color: rgba(255, 255, 255, 0.4);
    }
  }

  .rank-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;

    .rank-name {
      font-weight: 600;
      font-size: 15px;
    }

    .rank-tier {
      font-size: 13px;
      color: rgba(255, 255, 255, 0.5);
    }
  }

  .rank-score {
    font-size: 18px;
    font-weight: 800;
    color: $green;
  }
}

// Tiers
.tiers-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 16px;
  margin-bottom: 32px;
}

.tier-card {
  position: relative;
  padding: 24px;
  background: $card-bg;
  border: 2px solid $border;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.3s;

  &.active {
    border-color: $green;
    background: rgba($green, 0.06);
    box-shadow: 0 0 20px rgba($green, 0.1);
  }

  &.locked {
    opacity: 0.5;
  }

  .tier-icon-big {
    font-size: 40px;
    flex-shrink: 0;
  }

  .tier-info {
    h3 {
      font-size: 16px;
      font-weight: 700;
      margin: 0 0 4px;
    }

    .tier-commission {
      color: $green;
      font-weight: 600;
      font-size: 14px;
      margin: 0 0 4px;
    }

    .tier-requirement {
      color: rgba(255, 255, 255, 0.5);
      font-size: 12px;
      margin: 0;
    }
  }

  .tier-current-badge {
    position: absolute;
    top: 10px;
    right: 12px;
    font-size: 11px;
    color: $green;
    font-weight: 600;
    i { margin-right: 4px; }
  }
}

// Tier Rules
.tier-rules {
  padding: 24px;
  background: $card-bg;
  border: 1px solid $border;
  border-radius: 14px;

  h3 {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    margin: 0 0 16px;
    i { color: $green; }
  }

  ul {
    list-style: none;
    padding: 0;
    margin: 0;

    li {
      padding: 6px 0 6px 20px;
      position: relative;
      font-size: 14px;
      color: rgba(255, 255, 255, 0.7);
      line-height: 1.6;

      &::before {
        content: '•';
        position: absolute;
        left: 0;
        color: $green;
        font-weight: bold;
      }

      strong { color: $green; }
    }
  }
}

// Copy Toast
.copy-toast {
  position: fixed;
  bottom: 32px;
  right: 32px;
  padding: 12px 20px;
  background: $green;
  color: #fff;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 8px;
  z-index: 999;
  animation: slideUp 0.3s ease;
}

@keyframes slideUp {
  from { transform: translateY(20px); opacity: 0; }
  to { transform: translateY(0); opacity: 1; }
}

// Empty State
.empty-state {
  text-align: center;
  padding: 48px 24px;
  color: rgba(255, 255, 255, 0.4);

  i { font-size: 48px; margin-bottom: 16px; display: block; }
  p { font-size: 15px; margin: 0; }
}

// ============================================
// MISSÕES
// ============================================

.days-left-badge {
  display: inline-block;
  padding: 2px 10px;
  background: rgba($gold, 0.15);
  color: $gold;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  margin-left: 8px;
}

.missions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.mission-card {
  padding: 20px;
  background: $card-bg;
  border: 1px solid $border;
  border-radius: 14px;
  transition: transform 0.2s, border-color 0.2s;

  &:hover { transform: translateY(-2px); }

  &.completed {
    border-color: rgba($green, 0.3);
    background: rgba($green, 0.04);
  }

  .mission-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
  }

  .mission-icon { font-size: 24px; }

  .mission-category {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 3px 8px;
    border-radius: 6px;

    &.cat-explorer { background: rgba(59, 130, 246, 0.15); color: #60A5FA; }
    &.cat-lead_hunter { background: rgba(139, 92, 246, 0.15); color: #A78BFA; }
    &.cat-closer { background: rgba($gold, 0.15); color: $gold; }
    &.cat-consistency { background: rgba($green, 0.15); color: $green; }
  }

  .mission-done-badge {
    margin-left: auto;
    color: $green;
    font-size: 13px;
    font-weight: 600;
    i { margin-right: 4px; }
  }

  .mission-title {
    font-size: 16px;
    font-weight: 700;
    margin: 0 0 6px;
  }

  .mission-desc {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.5);
    margin: 0 0 14px;
    line-height: 1.5;
  }

  .mission-progress {
    margin-bottom: 12px;

    .mission-progress-bar {
      height: 8px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      overflow: hidden;
      margin-bottom: 6px;

      .mission-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, $green, $gold);
        border-radius: 8px;
        transition: width 0.5s ease;
      }
    }

    .mission-progress-text {
      font-size: 12px;
      color: rgba(255, 255, 255, 0.5);
      font-weight: 600;
    }
  }

  .mission-reward {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: $gold;
    font-weight: 600;

    i { font-size: 14px; }
  }
}

.missions-summary {
  display: flex;
  gap: 16px;
  margin-top: 8px;

  .missions-summary-card {
    flex: 1;
    text-align: center;
    padding: 16px;
    background: $card-bg;
    border: 1px solid $border;
    border-radius: 12px;

    .summary-number {
      display: block;
      font-size: 28px;
      font-weight: 900;
      color: $green;
    }

    .summary-label {
      font-size: 13px;
      color: rgba(255, 255, 255, 0.5);
    }
  }
}

// ============================================
// BADGES / CONQUISTAS
// ============================================

.badges-section-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 18px;
  font-weight: 700;
  margin: 0 0 16px;

  i { color: $gold; }
}

.badges-shelf { margin-bottom: 32px; }

.badges-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 12px;
}

.badge-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 16px;
  background: $card-bg;
  border: 1px solid $border;
  border-radius: 14px;
  transition: transform 0.2s;
  position: relative;

  &:hover { transform: translateY(-2px); }

  &.earned {
    border-color: rgba($gold, 0.3);
    background: rgba($gold, 0.04);
  }

  &.locked {
    opacity: 0.5;
  }

  .badge-icon-big {
    font-size: 36px;
    flex-shrink: 0;
    width: 48px;
    text-align: center;
  }

  .badge-info {
    flex: 1;
    min-width: 0;

    h4 {
      font-size: 14px;
      font-weight: 700;
      margin: 0 0 4px;
    }

    p {
      font-size: 12px;
      color: rgba(255, 255, 255, 0.5);
      margin: 0;
      line-height: 1.4;
    }

    .badge-date {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 11px;
      color: rgba(255, 255, 255, 0.4);
      margin-top: 4px;
    }
  }

  .badge-type-tag {
    position: absolute;
    top: 8px;
    right: 8px;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 700;

    &.type-event { background: rgba(99, 102, 241, 0.2); color: #818CF8; }
    &.type-achievement { background: rgba($gold, 0.2); color: $gold; }
    &.type-legacy { background: rgba($green, 0.2); color: $green; }
  }

  .badge-earned-check {
    color: $green;
    font-size: 18px;
    flex-shrink: 0;
  }

  .badge-locked-icon {
    color: rgba(255, 255, 255, 0.2);
    font-size: 16px;
    flex-shrink: 0;
  }
}

.badges-catalog {
  margin-top: 16px;

  .badge-type-group {
    margin-bottom: 24px;
  }

  .badge-type-title {
    font-size: 15px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.7);
    margin: 0 0 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid $border;
  }
}

// Badges no ranking
.rank-badges {
  display: flex;
  align-items: center;
  gap: 2px;
  margin-right: 8px;

  .rank-badge-icon {
    font-size: 16px;
    cursor: default;
  }

  .rank-badge-more {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.4);
    margin-left: 4px;
    font-weight: 600;
  }
}

// ============================================
// PROFILE MODAL
// ============================================

.profile-modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  z-index: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.profile-modal {
  background: #1e293b;
  border: 1px solid $border;
  border-radius: 20px;
  max-width: 500px;
  width: 100%;
  max-height: 80vh;
  overflow-y: auto;
  padding: 32px;
  position: relative;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
}

.profile-close {
  position: absolute;
  top: 16px;
  right: 16px;
  background: none;
  border: none;
  color: rgba(255, 255, 255, 0.4);
  font-size: 18px;
  cursor: pointer;
  padding: 8px;

  &:hover { color: #fff; }
}

.profile-header-card {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;

  .profile-tier-icon {
    font-size: 48px;
  }

  .profile-info {
    display: flex;
    flex-direction: column;

    h3 {
      font-size: 20px;
      font-weight: 800;
      margin: 0 0 4px;
    }

    .profile-tier-name {
      color: $green;
      font-weight: 600;
      font-size: 14px;
    }

    .profile-member-since {
      font-size: 12px;
      color: rgba(255, 255, 255, 0.4);
      margin-top: 4px;
      i { margin-right: 4px; }
    }
  }
}

.profile-stats-row {
  display: flex;
  gap: 12px;
  margin-bottom: 24px;

  .profile-stat {
    flex: 1;
    text-align: center;
    padding: 16px;
    background: $card-bg;
    border: 1px solid $border;
    border-radius: 12px;

    .profile-stat-value {
      display: block;
      font-size: 22px;
      font-weight: 800;
      color: $green;
    }

    .profile-stat-label {
      font-size: 13px;
      color: rgba(255, 255, 255, 0.5);
    }
  }
}

.profile-badges-section {
  h4 {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 15px;
    font-weight: 700;
    margin: 0 0 12px;
    i { color: $gold; }
  }
}

.profile-badges-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;

  .profile-badge-item {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: rgba($gold, 0.08);
    border: 1px solid rgba($gold, 0.2);
    border-radius: 8px;

    .profile-badge-icon { font-size: 18px; }
    .profile-badge-title { font-size: 12px; font-weight: 600; }
  }
}

.profile-no-badges {
  text-align: center;
  padding: 24px;
  color: rgba(255, 255, 255, 0.4);
  font-size: 14px;
}
</style>
