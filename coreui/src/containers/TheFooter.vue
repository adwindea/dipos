<template>
  <CFooter :fixed="false">
    <div>
      <!-- <a href="https://coreui.io" target="_blank">CoreUI</a>
      <span class="ml-1">&copy; {{new Date().getFullYear()}} creativeLabs.</span> -->
    </div>
    <div class="ml-auto">
      <router-link to="/">
        Dawn Idea
      </router-link>
      <span class="ml-1">&copy; {{new Date().getFullYear()}} All right reserved.</span>
      <!-- <span class="mr-1">Powered by</span>
      <a href="https://coreui.io/vue" target="_blank">CoreUI for Vue</a> -->
    </div>
  </CFooter>
</template>

<script>
export default {
  name: 'TheFooter'
}
</script>
