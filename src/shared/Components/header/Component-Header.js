export default {
    data() {
        return {
            headerMobile: true
        }
    },
    methods: {
        toogleMobile(){
            if(this.headerMobile == true)
                document.querySelector(".mobileMenu").style.display = 'flex';
            else {
                document.querySelector(".mobileMenu").style.display = 'none';
            }
            this.headerMobile = !this.headerMobile;
        },
        animationHamburguer() {
            document.getElementById("menu-hamburguer").checked = false;
        }
    },
    mounted() {
        const el = this;
        let viewportHeight = window.innerHeight;
        let calcViewportHeight = viewportHeight / 100 * 30;
        let active = false;
        window.onscroll = function() {
            if (document.body.scrollTop > (viewportHeight/2) || document.documentElement.scrollTop > (viewportHeight/2)) {
                document.querySelector("header").classList.add("scroll");
            } else {
                document.querySelector("header").classList.remove("scroll");
            }
            if (document.body.scrollTop > (viewportHeight - (calcViewportHeight)) || document.documentElement.scrollTop > (viewportHeight - (calcViewportHeight))) {
                document.querySelector(".containerAboutUs .lines").classList.add("scroll");
            }
            if (document.body.scrollTop > (viewportHeight - (calcViewportHeight) + 700) || document.documentElement.scrollTop > (viewportHeight - (calcViewportHeight) + 700)) {
                document.querySelector(".groupTelao").classList.add("scroll");
            }
            if (document.body.scrollTop > (viewportHeight - (calcViewportHeight) + (700 + 954)) || document.documentElement.scrollTop > (viewportHeight - (calcViewportHeight) + (700 + 954))) {
                document.querySelector(".containerWhatWeDo").classList.add("scroll");
            }
            if (document.body.scrollTop > (viewportHeight - (calcViewportHeight) + (700 + 954)) || document.documentElement.scrollTop > (viewportHeight - (calcViewportHeight) + (700 + 954)) && active === false) {
                el.$store.commit("changeShowInterview", true);
                active = true;
            }
        };
    }
}