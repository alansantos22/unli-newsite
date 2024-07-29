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
        let viewportHeight = window.innerHeight;
        let calcViewportHeight = viewportHeight / 100 * 30;
        let iframeYoutube = `<iframe src="https://www.youtube.com/embed/JkTkHzMdsww?si=mx8AxJ3Re4b-hZyk" title="YouTube video player"
        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>`;
        let active = false;
        window.onscroll = function() {
            if (document.body.scrollTop > (viewportHeight/2) || document.documentElement.scrollTop > (viewportHeight/2)) {
                document.querySelector("header").classList.add("scroll");
            } else {
                document.querySelector("header").classList.remove("scroll");
            }
            if (document.body.scrollTop > (viewportHeight - (calcViewportHeight)) || document.documentElement.scrollTop > (viewportHeight - (calcViewportHeight))) {
                document.querySelector(".containerAboutUs .columns").classList.add("scroll");
            }
            if (document.body.scrollTop > (viewportHeight - (calcViewportHeight) + 700) || document.documentElement.scrollTop > (viewportHeight - (calcViewportHeight) + 700)) {
                document.querySelector(".groupTelao").classList.add("scroll");
            }
            if (document.body.scrollTop > (viewportHeight - (calcViewportHeight) + (700 + 954)) || document.documentElement.scrollTop > (viewportHeight - (calcViewportHeight) + (700 + 954))) {
                document.querySelector(".containerWhatWeDo .columns").classList.add("scroll");
            }
            if (document.body.scrollTop > (viewportHeight - (calcViewportHeight) + (700 + 954)) || document.documentElement.scrollTop > (viewportHeight - (calcViewportHeight) + (700 + 954)) && active === false) {
                document.querySelector(".containerWhoIAm .iframe").innerHTML = iframeYoutube;
                active = true;
            }
        };
    }
}