<template>
<el-container>
  <!-- 顶栏 -->
  <el-header class="cleanPadding">
    <index-header></index-header>
  </el-header>
  <!-- 主体 -->
  <el-main>
    <router-view/>
  </el-main>
  <!-- 脚部 -->
  <el-footer>footer</el-footer>
</el-container>
</template>

<script>
import indexHeader from '@/components/index/header';

export default {
  components: {
    'index-header': indexHeader,
  },
};
</script>

<style>
.cleanPadding{
  padding: 0;
  height: auto !important;
}
</style>
