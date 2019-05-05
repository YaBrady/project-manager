<template>
  <div>
    {{name}}
  </div>
</template>

<script>
import { ajax, config, Container } from '@/utils/helpers';

export default {
  data() {
    return {
      name: 'asdf',
      container: null,
      user: null,
    };
  },
  async mounted() {
    this.container = new Container();
    const res = await ajax(`${config.appAddress}user`, 'GET', this.container.getHeader());
    this.user = res ? res.user : null;
  },
};
</script>

<style>

</style>
