<template>

  <div v-show="notification.display">
    <div class="alert alert-{{ notification.type }}">
      <button type="button" class="close" v-on:click="hideNotification()">&times;</button>
      {{ notification.message }}
    </div>
  </div>

</template>

<script>
export default {
    ready () {
        this.notification.display = 0;
    },
    props: {
        'notification': '',
    },
    methods: {
        hideNotification() {
            this.notification.display = 0;
        }
    }
}
</script>
