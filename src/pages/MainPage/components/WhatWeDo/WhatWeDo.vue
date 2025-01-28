<template>
    <div class="containerWhatWeDo allCenter">
        <h2>O que podemos oferecer para sua empresa</h2>
        <div v-for="service in services" :key="service.title" class="itemBox">
            <h3 class="titleService">{{service.title}}</h3>
            <ul class="itensList">
                <li v-for="item in service.items" :key="item" class="item">
                    {{item}}
                </li>
            </ul>
        </div>
    </div>
</template>

<script src="./Component-WhatWeDo.js"></script>

<style lang="scss" src="./Component-WhatWeDo.scss"></style>./Component-WhatWeDo.js./Component-WhatWeDo.js