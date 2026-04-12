<template>
  <div class="admin-panel">
    <!-- Sidebar -->
    <aside class="sidebar" :class="{ collapsed: sidebarCollapsed }">
      <div class="sidebar-header">
        <span class="logo-icon">🏰</span>
        <h2 v-show="!sidebarCollapsed">Torre de Controle</h2>
        <button class="collapse-btn" @click="sidebarCollapsed = !sidebarCollapsed">
          <i :class="sidebarCollapsed ? 'fas fa-angles-right' : 'fas fa-angles-left'"></i>
        </button>
      </div>

      <nav class="sidebar-nav">
        <button
          v-for="item in menuItems"
          :key="item.id"
          class="nav-item"
          :class="{ active: activeTab === item.id }"
          @click="activeTab = item.id"
          :title="item.label"
        >
          <i :class="item.icon"></i>
          <span v-show="!sidebarCollapsed">{{ item.label }}</span>
        </button>
      </nav>

      <div class="sidebar-footer">
        <div class="admin-info" v-show="!sidebarCollapsed">
          <span class="admin-name">{{ adminUser?.name }}</span>
          <span class="admin-role">{{ roleLabel }}</span>
        </div>
        <button class="nav-item logout" @click="handleLogout" title="Sair">
          <i class="fas fa-sign-out-alt"></i>
          <span v-show="!sidebarCollapsed">Sair</span>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="content-header">
        <h1>{{ currentMenuLabel }}</h1>
        <span class="date-badge">{{ todayFormatted }}</span>
      </header>

      <div class="content-body">
        <!-- Dashboard Overview -->
        <AdminOverview v-if="activeTab === 'overview'" />

        <!-- Gestão de Afiliados -->
        <AdminAffiliates v-if="activeTab === 'affiliates'" />

        <!-- Gestão de SDRs -->
        <AdminSDRs v-if="activeTab === 'sdrs'" />

        <!-- Clientes -->
        <AdminClients v-if="activeTab === 'clients'" />

        <!-- Financeiro -->
        <AdminFinance v-if="activeTab === 'finance'" />

        <!-- Gamificação -->
        <AdminGamification v-if="activeTab === 'gamification'" />

        <!-- Webinar Leads -->
        <AdminWebinarLeads v-if="activeTab === 'webinar'" />
      </div>
    </main>
  </div>
</template>

<script>
import { useAdminAuth } from '@/core/composables/useAdminAuth';
import AdminOverview from './modules/AdminOverview.vue';
import AdminAffiliates from './modules/AdminAffiliates.vue';
import AdminSDRs from './modules/AdminSDRs.vue';
import AdminClients from './modules/AdminClients.vue';
import AdminFinance from './modules/AdminFinance.vue';
import AdminGamification from './modules/AdminGamification.vue';
import AdminWebinarLeads from './modules/AdminWebinarLeads.vue';

export default {
  name: 'AdminDashboard',
  components: { AdminOverview, AdminAffiliates, AdminSDRs, AdminClients, AdminFinance, AdminGamification, AdminWebinarLeads },
  data() {
    return {
      activeTab: 'overview',
      sidebarCollapsed: false,
      menuItems: [
        { id: 'overview',      label: 'Dashboard',     icon: 'fas fa-chart-line' },
        { id: 'affiliates',    label: 'Afiliados',     icon: 'fas fa-users' },
        { id: 'sdrs',          label: 'SDRs',          icon: 'fas fa-headset' },
        { id: 'clients',       label: 'Clientes',      icon: 'fas fa-building' },
        { id: 'finance',       label: 'Financeiro',    icon: 'fas fa-wallet' },
        { id: 'gamification',  label: 'Gamificação',   icon: 'fas fa-trophy' },
        { id: 'webinar',       label: 'Webinar Leads', icon: 'fas fa-chalkboard-teacher' },
      ]
    };
  },
  computed: {
    adminUser() {
      const { adminUser } = useAdminAuth();
      return adminUser.value;
    },
    roleLabel() {
      const roles = { super_admin: 'Super Admin', manager: 'Gerente', viewer: 'Visualizador' };
      return roles[this.adminUser?.role] || '';
    },
    currentMenuLabel() {
      return this.menuItems.find(i => i.id === this.activeTab)?.label || '';
    },
    todayFormatted() {
      return new Date().toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }
  },
  async created() {
    const { validate } = useAdminAuth();
    const valid = await validate();
    if (!valid) {
      this.$router.push('/admin');
    }
  },
  methods: {
    handleLogout() {
      const { logout } = useAdminAuth();
      logout();
      this.$router.push('/admin');
    }
  }
};
</script>

<style lang="scss" scoped>
.admin-panel {
  display: flex;
  min-height: 100vh;
  background: #0a0a14;
  color: #e2e8f0;
}

// ===== Sidebar =====
.sidebar {
  width: 260px;
  background: rgba(255, 255, 255, 0.02);
  border-right: 1px solid rgba(255, 255, 255, 0.06);
  display: flex;
  flex-direction: column;
  transition: width 0.3s ease;
  flex-shrink: 0;

  &.collapsed {
    width: 72px;
  }
}

.sidebar-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 20px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);

  .logo-icon { font-size: 24px; }

  h2 {
    font-size: 16px;
    font-weight: 700;
    color: #f59e0b;
    margin: 0;
    white-space: nowrap;
  }

  .collapse-btn {
    margin-left: auto;
    background: none;
    border: none;
    color: #64748b;
    cursor: pointer;
    padding: 4px 6px;
    border-radius: 6px;
    font-size: 12px;

    &:hover { color: #f59e0b; background: rgba(255,255,255,0.05); }
  }
}

.sidebar-nav {
  flex: 1;
  padding: 12px 8px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border: none;
  background: none;
  color: #94a3b8;
  font-size: 14px;
  cursor: pointer;
  border-radius: 10px;
  transition: all 0.2s;
  text-align: left;
  white-space: nowrap;

  i { width: 20px; text-align: center; font-size: 15px; }

  &:hover {
    color: #f1f5f9;
    background: rgba(255, 255, 255, 0.04);
  }

  &.active {
    color: #f59e0b;
    background: rgba(245, 158, 11, 0.1);
    font-weight: 600;
  }

  &.logout {
    color: #ef4444;
    &:hover { background: rgba(239, 68, 68, 0.1); }
  }
}

.sidebar-footer {
  padding: 12px 8px;
  border-top: 1px solid rgba(255, 255, 255, 0.06);

  .admin-info {
    padding: 8px 16px 12px;

    .admin-name {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #e2e8f0;
    }

    .admin-role {
      font-size: 11px;
      color: #f59e0b;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
  }
}

// ===== Main Content =====
.main-content {
  flex: 1;
  overflow-y: auto;
  min-width: 0;
}

.content-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 24px 32px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);

  h1 {
    font-size: 22px;
    font-weight: 700;
    margin: 0;
    color: #f1f5f9;
  }

  .date-badge {
    font-size: 13px;
    color: #64748b;
    text-transform: capitalize;
  }
}

.content-body {
  padding: 24px 32px;
}

// ===== Responsive =====
@media (max-width: 768px) {
  .sidebar {
    width: 72px;

    h2, span:not(.logo-icon), .admin-info { display: none !important; }
  }

  .content-header { padding: 16px 20px; }
  .content-body { padding: 16px 20px; }
}
</style>
