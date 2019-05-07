<template>
  <div class="timelineBox">
    <el-timeline>
      <el-timeline-item
      :class="list.status ?'notImportant':''"
      :type="getInviteStatusPoint(list.status)"
      v-for="(list,index) in invites.data"
      :key="index"
      :timestamp="list.updated_at"
      placement="top">
        <el-card>
          <h4>{{list.isProjectInvite?'项目':'团队'}}邀请</h4>
          <h5>状态：
            <span :class="getInviteStatusPoint(list.status)">
              {{getInviteStatusName(list.status)}}
              </span>
          </h5>
          <p style="margin-top:5px;">{{list.user_name}}邀请你加入{{list.invitable_name}}</p>
        </el-card>
        <div v-if="!list.status" class="inviteHandleBox">
          <el-button type="success" @click="accessInvite(index)">接受</el-button>
          <el-button type="danger" @click="refuseInvite(index)">拒绝</el-button>
        </div>
      </el-timeline-item>
    </el-timeline>
    <el-pagination
      layout="prev, pager, next"
      :total="invites.total"
      :page-size="invites.per_page"
      @current-change="prevPage"
    >
    </el-pagination>
  </div>
</template>

<script>
import { ajax, config, container } from '@/utils/helpers';
import { mapState } from 'vuex';

export default {
  data() {
    return {
      invites: [],
    };
  },
  computed: {
    ...mapState({
      user: state => state.user,
    }),
  },
  async beforeMount() {
    const res = await ajax(`${config.appAddress}invites`, 'GET', container.getHeader());
    this.invites = res;
  },
  methods: {
    async prevPage(page) {
      const res = await ajax(`${config.appAddress}invites?page=${page}`, 'GET', container.getHeader());
      this.invites = res;
    },
    async accessInvite(index) {
      const invite = this.invites.data[index];
      await ajax(`${config.appAddress}invites/${invite.id}`, 'PUT', container.getHeader(), { status: 1 });
      window.location.reload();
    },
    async refuseInvite(index) {
      const invite = this.invites.data[index];
      await ajax(`${config.appAddress}invites/${invite.id}`, 'PUT', container.getHeader(), { status: 2 });
      window.location.reload();
    },
    getInviteStatusName(status) {
      if (status) {
        if (status === 1) return '已接受';
        return '已拒绝';
      }
      return '未处理';
    },
    getInviteStatusPoint(status) {
      if (status) {
        if (status === 1) return 'success';
        return 'danger';
      }
      return 'warning';
    },
  },
};
</script>

<style>
.inviteHandleBox{
  position: absolute;
  top: 58px;
  right: 20px;
}
.timelineBox{
  width: 720px;
  margin-left: 30%;
  margin-top: 32px;
}
.notImportant{
  opacity: 0.5;
}
.success{
  color: #67C23A;
}
.danger{
  color: #F56C6C;
}
.warning{
  color: #E6A23C;
}
</style>
