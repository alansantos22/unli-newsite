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
    }
]