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
        path: "/setup",
        name: "OnboardingWizard",
        component: () => import("./shared/Components/OnboardingWizard.vue")
    },
    {
        path: "/test-checkout",
        name: "TestCheckout",
        component: () => import("./pages/TestCheckout/TestCheckout.vue")
    },
    {
        path: "/checkout/:orderId",
        name: "Checkout",
        component: () => import("./shared/Components/CheckoutPayment.vue")
    }
]