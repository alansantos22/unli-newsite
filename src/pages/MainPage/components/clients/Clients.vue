<template>
    <div class="containerClients allCenter">
        <h3>Marcas que entregamos excelência</h3>
        <hr>
        <ul>
            <li v-for="marca in marcas" :key="marca.name">
                <img :src="require('@/assets/img/marcas/' + marca.image)" :alt="marca.name">
            </li>
        </ul>
    </div>
</template>

<script src="./Component-Clients.js"></script>

<style lang="scss" src="./Component-Clients.scss"></style>