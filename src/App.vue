<template>
  <div class="containerMain">
    <AppHeader v-if="showHeader" />
    <main>
      <router-view />
    </main>
    <AppFooter v-if="showFooter" />
  </div>
</template>

<script src="./Component-App.js"></script>

<style lang="scss" src="./App.scss"></style>
