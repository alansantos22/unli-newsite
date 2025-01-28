// Import Swiper Vue.js components
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Autoplay, Pagination, Navigation } from 'swiper/modules';

// Import Swiper styles
import 'swiper/css';

export default {
    data() {
        return {
            comments: [
                {
                    name: "Marcos Maziero",
                    position: "CEO",
                    company: "Gameshark",
                    comment: "Trabalhamos com a Unli no desenvolvimento do nosso antigo sistema de venda de imóveis, Welchome, e também, participamos em sociedade no desenvolvimento do Parallelium, qualidade sem igual, sempre nos ajudaram com qualquer dúvida que tivessemos, recomendo"
                },
                {
                    name: "Higor Gimenez",
                    position: "CEO",
                    company: "Softvision",
                    comment: "O Alan participou do desenvolvimento do Go Island, um líder muito focado, que nos ajudou bastante com o desenvolvimento do jogo"
                },
                {
                    name: "João Antônio",
                    position: "Marketing",
                    company: "",
                    comment: "Contratei a empresa depois de uma recomendação de um amigo, e não me arrependo, entregaram o trabalho rápido, por um preço justo, e sempre tirando as minha dúvidas"
                },
                {
                    name: "Vinicius Viper",
                    position: "CEO",
                    company: "Descomplica Eventos",
                    comment: "Contratamos a Unli para desenvolver um novo produto para nossos clientes, deu super certo, o suporte foi excelente e conseguimos entregar um evento de sucesso"
                },
            ]
        }
    },
    components: {
      Swiper,
      SwiperSlide,
    },
    setup() {
      return {
        modules: [Autoplay, Pagination, Navigation],
      };
    }
}