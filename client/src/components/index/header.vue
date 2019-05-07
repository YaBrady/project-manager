<template>
  <ul class="indexHeader">
    <div class="logoBox" @click="home">
      <el-image :src="logo" title="首页 "></el-image>
    </div>
    <el-breadcrumb class="tags" separator-class="el-icon-arrow-right">
      <el-breadcrumb-item
      v-for="(list,index) in  breadcrumbModel[tagActiveName]"
      :key="index"
      :to="list.route">
        {{list.title}}
      </el-breadcrumb-item>
    </el-breadcrumb>
    <li><el-button plain @click="logout">登出</el-button></li>
    <li><el-button plain @click="toSetting">设置</el-button></li>
    <li>
      <el-badge :is-dot="hasInvite" class="item">
        <el-button @click="toInviteCenter">消息</el-button>
      </el-badge>
    </li>
    <li class="indexHeaderLiSplit"><el-link @click="toSetting">{{user.name}}</el-link></li>
    <li>
      <el-image  class="indexHeader-Image"  :src="user.avatar ? user.avatar : defaultImg">
      </el-image>
    </li>
  </ul>
</template>

<script>
import { ajax, config, container } from '@/utils/helpers';
import { mapState, mapActions } from 'vuex';
import defaultImg from '../../assets/avatar.png';
import logo from '../../assets/logo.png';

// Tabs索引
const [home, team, project, setting, teamDetail, inviteCenter] = [
  {
    title: '首页',
    route: { name: 'home' },
  },
  {
    title: '团队管理',
    route: { name: 'home', params: { tagActiveName: 'team' } },
  },
  {
    title: '项目管理',
    route: { name: 'home', params: { tagActiveName: 'project' } },
  },
  {
    title: '设置',
    route: { name: 'user-info' },
  },
  {
    title: '团队详情',
    route: { name: 'team-detail' },
  },
  {
    title: '消息中心',
    route: { name: 'invite-center' },
  },
];

export default {
  data() {
    return {
      defaultImg: '',
      logo: '',
      invites: [],
      breadcrumbModel: {
        team: [home, team],
        team_detail: [home, team, teamDetail],
        project: [home, project],
        setting: [home, setting],
        inviteCenter: [home, inviteCenter],
      },
    };
  },
  computed: {
    ...mapState({
      user: state => state.user,
      tagActiveName: state => state.tagActiveName,
      hasInvite: state => state.hasInvite,
    }),
  },
  beforeMount() {
    this.defaultImg = defaultImg;
    this.logo = logo;
  },
  methods: {
    toInviteCenter() {
      this.setTagActiveName('inviteCenter');
      this.$router.push({ name: 'invite-center' });
    },
    home() {
      this.$router.push({ name: 'home' });
    },
    toSetting() {
      this.setTagActiveName('setting');
      this.$router.push({ name: 'user-info' });
    },
    async logout() {
      const res = await ajax(`${config.appAddress}logout`, 'POST', container.getHeader());
      if (res.message === 'ok') {
        container.clearHeader();
        this.removeUser();
        this.$router.push({ name: 'login' });
      }
    },
    ...mapActions({
      removeUser: 'removeUser',
      setTagActiveName: 'setTagActiveName',
      setInvite: 'setInvite',
    }),
  },
  async mounted() {
    // 获取通知
    const res = await ajax(`${config.appAddress}home`, 'GET', container.getHeader());
    this.invites = res.invites;
    if (this.invites.length) {
      this.setInvite(true);
    }
    // 点击回调
    const notifyCallback = function notifyCallback(index) {
      return () => {
        const invite = this.invites[index];
        this.$confirm(`${invite.user_name}邀请你进入${invite.invitable_name}`, '提示', {
          confirmButtonText: '加入',
          cancelButtonText: '拒绝',
          type: 'warning',
        }).then(async () => {
          await ajax(`${config.appAddress}invites/${invite.id}`, 'PUT', container.getHeader(), { status: 1 });
          this.$message({
            type: 'success',
            message: '已接收邀请!',
          });
        }).catch(async () => {
          await ajax(`${config.appAddress}invites/${invite.id}`, 'PUT', container.getHeader(), { status: 2 });
          this.$message({
            type: 'success',
            message: '已拒绝邀请!',
          });
        });
      };
    };
    this.invites.forEach((invite, index) => {
      setTimeout(() => {
        this.$notify({
          title: '团队邀请',
          message: `${invite.user_name}邀请你加入他的${invite.invitable_name}`,
          position: 'bottom-left',
          type: 'warning',
          duration: 5000,
          onClick: (notifyCallback)(index),
        });
      }, 300);
    });
  },
};
</script>

<style>
.tags{
  position: absolute;
  width: 450px;
  margin-left: 17%;
  top: 41%;
  font-size: 1.1rem;
}
.logoBox{
  position: absolute;
  width: 91px;
  height: 91px;
  margin-left: 2%;
  top: 0;
}
.logoBox .el-image{
  width: 81px;
  padding: 5px;
}
.logoBox .el-image:hover{
  cursor: pointer;
}
.indexHeader{
  position: relative;
  list-style:none;
  padding: 25px 5%;
  border-bottom: 1px solid #dcdfe6;
  background: #fff;
}
.indexHeader li{
  float: right;
  padding: 0 5px;
  font-size: 14px;
  font-weight: 400;
}
.indexHeader:after{
  clear: both;
  content: '';
  display: block;
}
.indexHeader li.indexHeaderLiSplit{
  margin-right:15px;
  margin-left:10px;
  padding:10px 0 11px 0;
}
.indexHeader-Image{
  width: 40px;
}
</style>
