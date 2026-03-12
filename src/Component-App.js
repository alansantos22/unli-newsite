import AppHeader from '@/shared/Components/HeaderModern.vue';
import AppFooter from '@/shared/Components/FooterModern.vue';
import { useAffiliateTracking } from '@/core/composables/useAffiliateTracking';

export default {
  name: 'App',
  components: {
      AppHeader,
      AppFooter
  },
  created() {
    // Detectar parâmetro ?ref= de afiliado e salvar no cookie
    const { detectAndSave } = useAffiliateTracking();
    detectAndSave();
  }
}