<template>
  <div class="sdr-calculadora-wrapper">
    <header class="sdr-calc-header">
      <router-link to="/sdr/dashboard" class="btn-back">
        <i class="fas fa-arrow-left"></i> Dashboard
      </router-link>
      <h1><i class="fas fa-calculator"></i> Configurador de Site</h1>
    </header>

    <SiteConfigurator :sdr-mode="true" initial-product="site_complete" />
  </div>
</template>

<script>
import SiteConfigurator from '@/shared/Components/SiteConfigurator.vue';

export default {
  name: 'SDRCalculadora',
  components: { SiteConfigurator }
};
</script>

<style lang="scss" scoped>
.sdr-calculadora-wrapper {
  min-height: calc(100vh - 80px);
  background: #f3f4f6;
  color: #1a1d23;
  margin-top: 80px;
}

.sdr-calc-header {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 24px;
  background: linear-gradient(135deg, #0F0F1A 0%, #1A1A2E 100%);
  border-bottom: 3px solid #8B5CF6;

  .btn-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: rgba(255, 255, 255, 0.7);
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    padding: 6px 12px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    transition: all 0.2s;
    white-space: nowrap;

    &:hover {
      color: #fff;
      border-color: rgba(255, 255, 255, 0.4);
      background: rgba(255, 255, 255, 0.08);
    }
  }

  h1 {
    font-size: 18px;
    margin: 0;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;

    i { color: #8B5CF6; }
  }
}
</style>
