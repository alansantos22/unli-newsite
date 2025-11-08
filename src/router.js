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
    }
]