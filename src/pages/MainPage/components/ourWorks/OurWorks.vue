<template>
    <div class="containerOurWorks allCenter">
        <h2>Conheça nosso trabalho</h2>
        <div class="groupTelao">
            <div class="telao" :style="{backgroundImage: `url(${require('@img/workCards/' + workCard.current.image)})`}">
                <span class="category" :class="workCard.current.category.toLowerCase()">{{workCard.current.category.replace('_', ' ')}}</span>
                <p class="subtitle">{{workCard.current.subtitle}}</p>
            </div>
            <div class="dm-none-flex">
                {{workCard.current.subtitle}}
            </div>
            <ul>
                <li class="workCard d-flex" v-for="(worksCard, index) in workCard.list" :key="worksCard.category" :class="[worksCard.category.toLowerCase(), worksCard.name === workCard.current.name ? 'active' : '']" @click="activeWorkCard(index)">
                    <div class="workCardImage" :style="{backgroundImage: `url(${require('@img/workCards/' + worksCard.image)})`}"></div>
                    <!-- <img :src="require('@img/workCards/' + worksCard.image)" alt="" class="workCardImage"> -->
                    <span class="title">{{worksCard.name}}</span>
                </li>
            </ul>
        </div>
    </div>
</template>

<script src="./Component-OurWorks.js"></script>

<style lang="scss" src="./Component-OurWorks.scss"></style>./Component-OurWorks.js./Component-OurWorks.js