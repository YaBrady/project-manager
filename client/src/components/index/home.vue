<template>
  <div class="homeBox">
    <el-tabs
    class="homeTabs"
    v-model="tabActiveName"
    :tab-position="tabPosition"
    @tab-click="triggerTags">
      <el-tab-pane name="team" label="团队管理">
        <teams></teams>
      </el-tab-pane>
      <el-tab-pane name="project" label="项目管理">
        <projects></projects>
      </el-tab-pane>
    </el-tabs>
  </div>
</template>

<script>
import teams from '@/components/index/team';
import projects from '@/components/index/project';
import { mapActions, mapState } from 'vuex';

export default {
  data() {
    return {
      tabPosition: 'left',
    };
  },
  components: {
    teams,
    projects,
  },
  computed: {
    tabActiveName: {
      get() {
        return this.tabActiveNameVux;
      },
      set(tag) {
        this.triggerTags(tag);
      },
    },
    ...mapState({
      tabActiveNameVux: state => state.tabActiveName,
    }),
  },
  methods: {
    triggerTags(tag) {
      this.setTagActiveName(tag.name);
    },
    ...mapActions({
      setTagActiveName: 'setTagActiveName',
    }),
  },
};
</script>

<style>
.homeBox{
  margin:8% 0;
  padding:0 0 0 30%;
}
.homeTabs .el-tabs__content{
  max-width: 575px;
}
</style>
