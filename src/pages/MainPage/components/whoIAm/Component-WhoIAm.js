export default {
    computed: {
        showInterview() {
            return this.$store.state.GeneralModule.showInterview;
        }
    },
    methods:{
        writerMachineEffect() {
            var i = 0;
            var tag = document.querySelector(".phrase span");
            var html = document.querySelector(".phrase span").innerHTML;
            var speed = 50;
        
            function typeWriter() {
                if (i <= html.length) {
                    tag.innerHTML = html.slice(0 , i + 1);
                    i++;
                    setTimeout(typeWriter, speed);
                }
            }
            typeWriter();
        }
    }
}