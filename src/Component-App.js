import { defineAsyncComponent } from 'vue';
import { useAffiliateTracking } from '@/core/composables/useAffiliateTracking';

export default {
  name: 'App',
  components: {
      AppHeader: defineAsyncComponent(() => import('@/shared/Components/HeaderModern.vue')),
      AppFooter: defineAsyncComponent(() => import('@/shared/Components/FooterModern.vue'))
  },
  computed: {
    showHeader() {
      return !this.$route.meta?.hideHeader;
    },
    showFooter() {
      return !this.$route.meta?.hideFooter;
    }
  },
  created() {
    // Detectar parâmetro ?ref= de afiliado e salvar no cookie
    const { detectAndSave } = useAffiliateTracking();
    detectAndSave();
  }
}