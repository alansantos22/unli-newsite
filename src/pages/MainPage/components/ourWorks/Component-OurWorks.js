export default {
    data() {
        return {
            workCard: {
                list: [
                    {
                        image: "parallelium.png",
                        name: "Parallelium",
                        category: "Web3",
                        subtitle: "Parallelium foi um jogo desenvolvido para o mercado Web3, onde criamos o marketplace na rede BSC, com contratos para a mintagem de NFTS e compras para o jogo, se tornando uma referência nesse mercado"
                    },
                    {
                        image: "goisland2.png",
                        name: "Go Island",
                        category: "Jogos",
                        subtitle: "Go Island é um jogo social multiplayer, focado no público infantil, com diversos minigames, como battle royale, sobrevivência, pega base e policia e ladrão, onde apresentamos na Flow Games After Party, para diversos influenciadores e pessoas importantes do mercado"
                    },
                    {
                        image: "dreamside.png",
                        name: "Dreamside",
                        category: "Jogos",
                        subtitle: "Dreamside é o novo jogo da empresa, focado nas histórias desenvolvidas por Alan Reis, é um mundo dos sonhos, onde os jogadores podem jogar da forma que quiserem, como um taverneiro, um caçador de recompensas, ou um mago lendário, tudo é possivel em Dreamside"
                    },
                    {
                        image: "suzano-game.jpeg",
                        name: "Suzano Rock in Rio",
                        category: "Gameficação",
                        subtitle: "Trabalho de gameficação, apresentado no Rock in Rio pela Suzano, para ativação de pessoas que estavam no evento, com um jogo voltado a reciclagem e consciência ambiental"
                    },
                    {
                        image: "corona-virus-game.jpg",
                        name: "Zona de contágio",
                        category: "Gameficação",
                        subtitle: "Trabalho de gameficação realizado por nossos parceiros da Grave Yardgirl 3d, focado em ajudar pessoas a entenderem melhor como se prevenir da doença"
                    },
                    {
                        image: "hope-crane.jpg",
                        name: "Hadley's Hope Crane",
                        category: "Modelagem_3D",
                        subtitle: "Trabalho de gameficação realizado por nossos parceiros da Grave Yardgirl 3d. Asset criado para VR, simulando um dia em Hadely's Hope, baseado no filme Aliens(1986)"
                    },
                    {
                        image: "raiox.jpeg",
                        name: "Raio X",
                        category: "Realidade_Aumentada",
                        subtitle: "Raio X é um conceito de tecnologia que desenvolvemos para a Descomplique Eventos, onde as pessoas andam com a tela e ela interage com o banner de fundo, apresentando informações referentes a posição que ela está"
                    },
                    {
                        image: "gorila.jpeg",
                        name: "Mascote Ponte Preta",
                        category: "Realidade_Aumentada",
                        subtitle: "AR desenvolvido para apresentar novos projetos com o mascote para o Museu do Ponte Preta"
                    }
                ],
                current: {
                    image: "parallelium.png",
                    name: "Parallelium",
                    category: "Web3",
                    subtitle: "Parallelium foi um jogo desenvolvido para o mercado Web3, onde criamos o marketplace na rede BSC, com contratos para a mintagem de NFTS e compras para o jogo, se tornando uma referência nesse mercado"
                }
            },
        }
    },
    methods:{
        activeWorkCard(index) {
            this.workCard.current = this.workCard.list[index];
        }
    }
}