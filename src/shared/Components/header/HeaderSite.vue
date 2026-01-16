<template>
    <header class="d-flex-cont-between flex-align-center">
        <div class="blockLogo">
            <img class="logo" src="@/assets/img/logo_horizontal.png" alt="">
        </div>
        <i class="dm-none-flex" @click="toogleMobile()">
            <div class="menu">
                <span class="hamburguer"></span>
            </div>
        </i>
        <nav>
            <ul class="d-flex">
                <!-- <li><router-link to="/"><a>Inicio</a></router-link><b></b></li> -->
                <li><a href="#aboutUs">Quem somos</a><b></b></li>
                <li><a href="#ourWorks">Nosso Trabalho</a><b></b></li>
                <li><a href="#whatWeDo">O que fazemos?</a><b></b></li>
                <li><router-link to="/consultoria-gamificacao"><a>Consultoria</a></router-link><b></b></li>
                <li><a href="#contact">Contato</a><b></b></li>
                <!-- <li><a href="#roadmap">Portfolio</a><b></b></li> -->
                <!-- <div class="medias d-flex">
                    <li><a href="https://t.me/paralleliumptbr" target="_blank"><i class="icon icons-telegram"></i></a></li>
                    <li><a href="https://twitter.com/OParallelium" target="_blank"><i class="icon icons-twitter"></i></a></li>
                    <li><a href="https://discord.gg/rzSkWGrVvv" target="_blank"><i class="icon icons-discord"></i></a></li>
                    <li><a href="https://medium.com/@parallelium" target="_blank"><i class="icon icons-medium"></i></a></li>
                </div> -->
            </ul>
        </nav>
        <nav class="mobileMenu">
            <ul>
                <li @click="toogleMobile(), animationHamburguer()"><a href="#aboutUs">Quem somos</a><b></b></li>
                <li @click="toogleMobile(), animationHamburguer()"><a href="#ourWorks">Nosso Trabalho</a><b></b></li>
                <li @click="toogleMobile(), animationHamburguer()"><a href="#whatWeDo">O que fazemos?</a><b></b></li>
                <li @click="toogleMobile(), animationHamburguer()"><router-link to="/consultoria-gamificacao"><a>Consultoria</a></router-link><b></b></li>
                <li @click="toogleMobile(), animationHamburguer()"><a href="#contact">Contato</a><b></b></li>
                <!-- <li @click="toogleMobile(), animationHamburguer()"><a href="#roadmap">Portfolio</a><b></b></li> -->
                <!-- <li @click="toogleMobile(), animationHamburguer()"><a href="https://twitter.com/OParallelium" target="_blank"><i class="icon icons-twitter"></i></a></li>
                <li @click="toogleMobile(), animationHamburguer()"><a href="https://discord.gg/rzSkWGrVvv" target="_blank"><i class="icon icons-discord"></i></a></li>
                <li @click="toogleMobile(), animationHamburguer()"><a href="https://t.me/paralleliumptbr" target="_blank"><i class="icon icons-telegram"></i></a></li>
                <li @click="toogleMobile(), animationHamburguer()"><a href="https://medium.com/@parallelium" target="_blank"><i class="icon icons-medium"></i></a></li> -->
            </ul>
        </nav>
    </header>
</template>

<style lang="scss" src="./Component-Header.scss"></style>

<script src="./Component-Header.js"></script>