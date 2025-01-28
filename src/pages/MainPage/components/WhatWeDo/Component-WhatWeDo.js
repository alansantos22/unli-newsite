export default {
    data() {
        return {
            services: [
                {
                    title: "Desenvolvimento de software",
                    items: [
                        "Criação de sites",
                        "Sistemas web",
                        "Smart Contracts Web3",
                        "Criação de criptomoedas",
                        "Criação de NFTs",
                        "Marketplace Web3"
                    ]
                },
                {
                    title: "Gameficação",
                    items: [
                        "Realidade Aumentada (AR)",
                        "Experiências imersivas",
                        "Consultoria de gameficação para empresas"
                    ]
                },
                {
                    title: "Jogos",
                    items: [
                        "Jogos para ativação em eventos",
                        "Jogos educacionais",
                        "Jogos mobile",
                        "Jogos multiplayer"
                    ]
                }
            ]
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