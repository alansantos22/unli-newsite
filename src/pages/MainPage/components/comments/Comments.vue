<template>
    <div class="containerComments allCenter">
        <swiper
            :loop="true"
            :autoplay="{
                delay: 2500,
                disableOnInteraction: false,
            }"
            :pagination="{
                clickable: true,
            }"
            :breakpoints="{
                '640': {
                    slidesPerView: 1,
                    spaceBetween: 30,
                },
                '1024': {
                    slidesPerView: 2,
                    spaceBetween: 50,
                }
            }"
            :navigation="true"
            :modules="modules"
            @swiper="onSwiper"
            @slideChange="onSlideChange"
            class="unli-swiper"
        >
            <swiper-slide v-for="comment in comments" :key="comment.name">
                <div class="comment">
                    {{ comment.comment }}
                    <div class="description">
                        <!-- <img src="~@/assets/img/marcas/goIsland.png" alt=""> -->
                        <span>
                            {{ comment.name }} - {{ comment.position }}
                            <br>
                            <strong>
                                {{ comment.company }}
                            </strong>
                        </span>
                    </div>
                </div>
            </swiper-slide>
        </swiper>
    </div>
</template>

<script src="./Component-Comments.js"></script>

<style lang="scss" src="./Component-Comments.scss"></style>