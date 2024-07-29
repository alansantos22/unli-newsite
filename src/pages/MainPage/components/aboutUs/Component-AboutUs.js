export default {
    methods:{
        writerMachineEffect() {
            var i = 0;
            var tag = document.querySelector(".containerAboutUs .columns .column p");
            var html = document.querySelector(".containerAboutUs .columns .column p").innerHTML;
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