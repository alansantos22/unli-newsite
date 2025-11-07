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
                        image: "bobs-gameficacao.png",
                        name: "Sistema de criação de eventos",
                        category: "Gameficação",
                        subtitle: "Outro trabalho bem sucedido, onde criamos um sistema para criação de eventos gameficados, com quiz interativos, desafios e recompensas, onde foi um sucesso de engajamento e interação com o público"
                    },
                    {
                        image: "fishbay-print.png",
                        name: "Fishbay",
                        category: "Sites",
                        subtitle: "Nosso lançamento mais recente, Fishbay é um game web browser, focado em criar um ambiente tranquilo e divertido"
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