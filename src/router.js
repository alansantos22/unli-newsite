export default [
    {
        path: "/",
        name: "Home",
        component: () => import("./pages/MainPage/MainPage.new.vue")
    },
    {
        path: "/old",
        name: "OldHome",
        component: () => import("./pages/MainPage/MainPage.vue")
    },
    {
        path: "/exemplo",
        name: "Example",
        component: () => import("./pages/MainPage/MainPageModern.example.vue")
    },
    {
        path: "/projeto/:id",
        name: "ProjectDetails",
        component: () => import("./pages/Portfolio/ProjectDetails.vue")
    },
    {
        path: "/consultoria-gamificacao",
        name: "ConsultoriaGamificacao",
        component: () => import("./pages/ConsultoriaGamificacao/ConsultoriaGamificacao.vue")
    },
    {
        path: "/site-vitrine",
        name: "SiteVitrine",
        component: () => import("./pages/SiteVitrine/SiteVitrine.vue")
    },
    {
        path: "/configurador",
        name: "ConfiguradorPage",
        component: () => import("./pages/ConfiguradorPage/ConfiguradorPage.vue")
    },
    {
        path: "/pagamento/validar",
        name: "PaymentValidation", 
        component: () => import("./pages/PaymentValidation.vue")
    },
    {
        path: "/sucesso",
        name: "PaymentSuccess",
        component: () => import("./pages/PaymentSuccess.vue")
    },
    {
        path: "/setup",
        name: "OnboardingSetup",
        component: () => import("./pages/OnboardingSetup/OnboardingSetup.vue"),
        meta: {
            title: 'Configure seu Site | Unli',
            hideHeader: true,
            hideFooter: true
        }
    },

    // ============================================
    // SDR Panel Routes
    // ============================================
    {
        path: "/sdr",
        name: "SDRLogin",
        component: () => import("./pages/SDRPanel/SDRLogin.vue"),
        meta: { hideHeader: true, hideFooter: true }
    },
    {
        path: "/sdr/dashboard",
        name: "SDRDashboard",
        component: () => import("./pages/SDRPanel/SDRDashboard.vue"),
        meta: { hideHeader: true, hideFooter: true, requiresSDR: true }
    },
    {
        path: "/sdr/calculadora",
        name: "SDRCalculadora",
        component: () => import("./pages/SDRPanel/SDRCalculadora.vue"),
        meta: { hideHeader: true, hideFooter: true, requiresSDR: true }
    },
    {
        path: "/sdr/nova-venda",
        name: "SDRNovaVenda",
        component: () => import("./pages/SDRPanel/SDRNovaVenda.vue"),
        meta: { hideHeader: true, hideFooter: true, requiresSDR: true }
    },
    {
        path: "/sdr/clientes",
        name: "SDRClientes",
        component: () => import("./pages/SDRPanel/SDRClientes.vue"),
        meta: { hideHeader: true, hideFooter: true, requiresSDR: true }
    },
    {
        path: "/sdr/cliente/:id",
        name: "SDRClienteDetalhe",
        component: () => import("./pages/SDRPanel/SDRClienteDetalhe.vue"),
        meta: { hideHeader: true, hideFooter: true, requiresSDR: true }
    },
    {
        path: "/sdr/configuracoes",
        name: "SDRConfiguracoes",
        component: () => import("./pages/SDRPanel/SDRConfiguracoes.vue"),
        meta: { hideHeader: true, hideFooter: true, requiresSDR: true }
    },

    // ============================================
    // Affiliate Panel Routes
    // ============================================
    {
        path: "/afiliados",
        name: "AffiliateLogin",
        component: () => import("./pages/AffiliatePanel/AffiliateLogin.vue"),
        meta: { hideFooter: true }
    },
    {
        path: "/afiliados/registro",
        name: "AffiliateRegister",
        component: () => import("./pages/AffiliatePanel/AffiliateRegister.vue"),
        meta: { hideFooter: true }
    },
    {
        path: "/afiliados/dashboard",
        name: "AffiliateDashboard",
        component: () => import("./pages/AffiliatePanel/AffiliateDashboard.vue"),
        meta: { hideFooter: true, requiresAffiliate: true }
    },

    // ============================================
    // Admin Panel Routes (Torre de Controle)
    // ============================================
    {
        path: "/admin",
        name: "AdminLogin",
        component: () => import("./pages/AdminPanel/AdminLogin.vue"),
        meta: { hideHeader: true, hideFooter: true }
    },
    {
        path: "/admin/dashboard",
        name: "AdminDashboard",
        component: () => import("./pages/AdminPanel/AdminDashboard.vue"),
        meta: { hideHeader: true, hideFooter: true, requiresAdmin: true }
    },

    // ============================================
    // Legal Pages
    // ============================================
    {
        path: "/politica-de-privacidade",
        name: "PrivacyPolicy",
        component: () => import("./pages/PrivacyPolicy/PrivacyPolicy.vue"),
        meta: { title: 'Política de Privacidade | Unli Studios' }
    }
]