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
    }
}