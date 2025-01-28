<template>
    <div class="containerWhoIAm allCenter">
        <h2>Quer conhecer mais?</h2>
        <span>Veja a <strong>entrevista</strong> completa do nosso <strong>CEO</strong> <strong>Alan Reis</strong> no <strong>Flow Games</strong>, durante a <strong>BGS</strong></span>
        <video controls="controls" v-if="showInterview"><source src="~@/assets/videos/flowgamesAlanReis.mp4" /></video>
    </div>
</template>

<script src="./Component-WhoIAm.js"></script>

<style lang="scss" src="./Component-WhoIAm.scss"></style>