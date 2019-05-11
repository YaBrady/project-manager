<template>
  <el-container  :class="showInvite ? 'showInvite showinviteBox': 'hiddenInvite showinviteBox'">
    <el-aside width="450px" style="position:relative;">
      <div class="openImgBox" :class="showInvite ? 'rightOpenImgBox' : 'leftOpenImgBox'" @click="expendInviteBox">
        <el-image :src="openImg"></el-image>
      </div>
      <el-card class="box-card"  v-loading="isLoading" element-loading-text="加载中">
        <div  class="box-header clearfix">
         <span>参与团队</span>
         <el-image style="float: right; padding: 3px 0" type="text" :src="projectImg"></el-image>
        </div>
        <div class="box-body">
          <el-select :disabled="canNotSelect" v-model="selectTeam" filterable @change="changeSelectTeam" multiple placeholder="请选择参与团队">
            <el-option
              v-for="item in myTeam"
              :key="item.value"
              :label="item.team_name"
              :value="item.team_id">
            </el-option>
          </el-select>
        </div>
        <div  class="box-header clearfix">
         <span>参与人员</span>
         <el-image style="float: right; padding: 3px 0" type="text" :src="projectUserImg"></el-image>
        </div>
        <div class="box-body">
          <div class="teamSetting">
          <ul class="teamMateBox">
            <li v-for="(mate,index) in invites_user" :key="index">
              <div class="mateBox">
                <el-image class="leaderImg" v-if="mate.is_creator" :src="team_leaderImg"></el-image>
                <div  class="deleteImg" @click="removeMate(index)">
                  <el-image v-if="is_creator&&!mate.is_creator" :src="deleteImg"></el-image>
                </div>
                <el-image :src="mate.avatar ? mate.avatar : defaultImg"></el-image>
                <p>{{mate.user_name}}</p>
              </div>
            </li>
          </ul>
          <div v-if="project.is_creator">
            <el-divider content-position="center">邀请伙伴</el-divider>
            <div class="inviteBox">
              <el-image :src="inviteImg"></el-image>
              <h2>邀请你的伙伴！</h2>
              <el-form :model="inviteForm" ref="inviteForm" :rules="inviteRules">
                <el-form-item prop="email">
                  <el-input
                    style="width: 60%;margin-top: 10px;"
                    v-model="inviteForm.email"
                    placeholder="邮箱地址 如：gdkj@163.com">
                  </el-input>
                </el-form-item>
                <el-form-item
                style="padding: 0px 0 15px 0;font-size: 16px;margin-left: 10px;margin-top: 0px;">
                  <el-button @click="commitinviteForm" type="success">邀请加入团队</el-button>
                </el-form-item>
              </el-form>
            </div>
          </div>
        </div>
        </div>
        <div  class="box-header clearfix">
         <span>项目设置</span>
         <el-image style="float: right; padding: 3px 0" type="text" :src="settingImg"></el-image>
        </div>
        <div class="box-body projectBox">
          <el-form style="padding:14px;" ref="form" v-model="form"  status-icon>
            <el-form-item label="项目名称" required >
              <el-input :disabled="canNotSelect" v-model="form.project_name"  placeholder="如：番茄项目..."></el-input>
            </el-form-item>
            <el-form-item label="备注">
              <el-input
                :disabled="canNotSelect"
                type="textarea"
                v-model="form.desc"
                placeholder="如：番茄项目主要用途是...">
              </el-input>
            </el-form-item>
            <el-form-item>
              <el-button :disabled="canNotSelect" @click="commitForm" type="primary">更新</el-button>
              <el-button :disabled="canNotSelect" @click="deleteProject" type="danger">删除项目</el-button>
            </el-form-item>
          </el-form>
        </div>
      </el-card>
    </el-aside>
    <el-container>
     <project-item :project_id="this.project.project_id"></project-item>
    </el-container>
  </el-container>
</template>

<script>
import { ajax, config, container } from '@/utils/helpers';
import item from '@/components/index/item';
import projectImg from '../../assets/project_team.png';
import projectUserImg from '../../assets/project_user.png';
import defaultImg from '../../assets/avatar.png';
import inviteImg from '../../assets/invite.png';
import team_leaderImg from '../../assets/team_leader.png';
import deleteImg from '../../assets/delete.png';
import openImg from '../../assets/open.png';
import settingImg from '../../assets/setting.png';


export default {
  data() {
    return {
      projectImg: '',
      projectUserImg: '',
      myTeam: [],
      selectTeam: [],
      invites_user: [],
      canNotSelect: false,
      defaultImg: '',
      project: {},
      inviteForm:{},
      inviteImg: '',
      deleteImg: '',
      team_leaderImg: '',
      settingImg: '',
      openImg: '',
      isLoading: true,
      inviteForm: {
        email: '',
      },
      inviteRules: {
        email: [
          { type: 'email', message: '请输入正确的邮箱地址', trigger: 'blur' },
        ],
      },
      form: {
        project_name: '',
        desc: '',
      },
      showInvite: true,
      is_creator: false,
    };
  },
  mounted() {
    setTimeout(() => {
      this.showInvite = false;
    }, 5000);
  },
  async beforeMount() {
    // 初始化静态图片资源
    this.projectImg = projectImg;
    this.projectUserImg = projectUserImg;
    this.defaultImg = defaultImg;
    this.inviteImg = inviteImg;
    this.deleteImg = deleteImg;
    this.team_leaderImg = team_leaderImg;
    this.openImg = openImg;
    this.settingImg = settingImg;

    this.project = this.$route.params;
    if (!this.project.project_id) {
      this.$router.push({ name: 'home' });
      return;
    }

    this.form.project_name = this.project.project_name;
    this.form.desc = this.project.desc;

    const project = await ajax(`${config.appAddress}projects/${this.project.project_id}?`, 'GET', container.getHeader());
    console.log(project);

    if (this.project.is_creator) {
      // 若是创建者
      const teamRes = await ajax(`${config.appAddress}teams?only_my_team=1`, 'GET', container.getHeader());
      this.myTeam = teamRes.teams;
    } else {
      // 若不是创建者
      this.myTeam = project.teams;
      this.canNotSelect = true;
    }
    this.is_creator = this.project.is_creator;
    this.selectTeam = project.teamsIndex;
    this.invites_user = project.invites;
    this.isLoading = false;
  },
  methods: {
    expendInviteBox() {
      this.showInvite = !this.showInvite;
    },
    async changeSelectTeam(selectTeam) {
      await ajax(`${config.appAddress}projects/${this.project.project_id}/teams`, 'PUT', container.getHeader(), {teams: selectTeam});
    },
    removeMate(index) {
      const mate = this.invites_user[index];
      this.$confirm('此操作将移除团队成员', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      }).then(async () => {
        await ajax(`${config.appAddress}projects/${this.project.project_id}/project_mate`, 'DELETE', container.getHeader(), { userid: mate.user_id });
        this.invites_user.splice(index, 1);
        this.$message({
          type: 'success',
          message: '删除成功!',
        });
      });
    },
    commitinviteForm() {
      this.$refs.inviteForm.validate(async (valid) => {
        if (!valid) {
          // 校验不通过
          return false;
        }
        try {
          if (!this.inviteForm.email) {
            return true;
          }
          await ajax(`${config.appAddress}projects/${this.project.project_id}/project_mate`, 'POST', container.getHeader(), { 'emails[]': this.inviteForm.email });
          this.$message({
            type: 'success',
            message: '已发送邀请',
          });
        } catch (e) {
          this.$message.error({
            message: '发送邀请失败',
          });
        }
        return true;
      });
    },
    deleteProject() {
      this.$confirm('此操作将永久删除项目', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      }).then(async () => {
        await ajax(`${config.appAddress}projects/${this.project.project_id}`, 'DELETE', container.getHeader());
        this.$message({
          type: 'success',
          message: '删除成功!',
        });
        this.$router.push({ name: 'home', params: { tagActiveName: 'project' } });
      });
    },
    async commitForm() {
      try {
        const requestForm = {
          project_name: this.form.project_name,
          desc: this.form.desc,
        };
        await ajax(`${config.appAddress}projects/${this.project.project_id}`, 'PUT', container.getHeader(), requestForm);
        this.project.project_name = this.form.project_name;
        this.project.desc = this.form.desc;
        this.$message({
          type: 'success',
          message: '更新成功',
        });
      } catch (e) {
        console.log(e);

        this.$message.error({
          message: '更新失败',
        });
      }
    },
  },
  components: {
    'project-item': item,
  },
};
</script>

<style>
.projectBox .el-form-item:last-child {
  text-align:center;
  margin-top: 35px;
  margin-bottom: 5px;
}
.openImgBox{
  position: absolute;
  top: 330px;
  margin-left: 83%;
  width: 45px;
  transition-property: all;
  transition-duration: 0.4s;
  transition-timing-function: ease;
  transition-delay: 0s;
}
.rightOpenImgBox{
  transform: rotate(-180deg);
}
.leftOpenImgBox{
  transform: rotate(0deg);
}
.openImgBox:hover{
  cursor: pointer;
}
.showinviteBox{
  position: absolute;
  transition-property: all;
  transition-duration: 0.7s;
  transition-timing-function: ease;
  transition-delay: 0s;
}
.showInvite{
  left:20px;
}
.hiddenInvite{
  left:-350px;
}
.box-card .el-select{
  margin: 8px 0;
  width: 100%;
}
.box-body{
  padding: 0 10px;
}
.box-card>.el-card__body {
  padding:0;
}
.box-header{
  padding: 10px;
  border-bottom: 1px solid #EBEEF5;
}
.box-body + .box-header{
  border-top: 1px solid #EBEEF5;

}
.clearfix>span{
  padding-top: 13px;
  display: inline-block;
}
.clearfix:before,
.clearfix:after {
  display: table;
  content: "";
}
.clearfix:after {
  clear: both;
}

.box-card {
  width: 330px;
}
.box-card .el-card__header{
  padding:5px 20px;
}

.avatar{
  width: 100%;
}
.avatar:hover{
  cursor: pointer;
}
.inviteBox .el-form-item__error{
  left:20%;
}
.inviteBox {
  margin-top: 50px;
  text-align: center;
}
.inviteBox>h2{
  color: #172b4d;
  font-size: 24px;
  font-weight: bold;
  padding-top: 10px;
  padding-left: 27px;
}
.mateBox .leaderImg{
    position: absolute;
    left: -10px;
    top: -10px;
    width: 25px;
    transform: rotate(-45deg);
}
.mateBox .deleteImg{
    position: absolute;
    right: -10px;
    top: -10px;
    width: 25px;
}
.deleteImg:hover{
  cursor: pointer;
}
.mateBox{
  position: relative;
  width: 70px;
}
.mateBox p{
  text-align: center;
  overflow: hidden;
  text-overflow: ellipsis;
}
.teamMateBox {
  padding: 10px;
  list-style: none;
}
.teamMateBox li {
  float: left;
  margin: 13px;
}
.teamMateBox:after{
  content: '';
  display: block;
  clear:both;
}
</style>
