<template>
  <el-container style="margin:2% auto;">
    <el-aside width="400px" style="margin: 0 0 0 25%;">
      <el-card class="infoCard">
        <div class="infoCardHeader"  @click="upload()" >
          <el-image class="avatar" :src="defaultTeamImg"></el-image>
        </div>
          <p class="infoCardUserName">{{team.team_name}}</p>
          <el-divider></el-divider>
          <p class="infoContent">
            <i style="color:#03A9F4;" class="el-icon-s-custom"></i>创建人: {{team.user_name}}
          </p>
          <el-divider></el-divider>
          <p class="infoContent">
            <i style="color:#03A9F4;" class="el-icon-time"></i>创建于{{team.created_at}}
          </p>
          <el-divider></el-divider>
          <div class="infoContent">
            <div>
              <i style="color:#FF5722;" class="el-icon-edit-outline"></i>备注
            </div>
            <p style="font-size: 0.8rem;margin-top:8px;">{{team.desc}}</p>
          </div>
      </el-card>
    </el-aside>
    <el-container>
      <div class="teamSetting">
        <el-card class="teamCard"  v-loading="isLoading" element-loading-text="加载中">
          <div class="teamCardHeader">
            <h2>团队成员</h2>
          </div>
          <ul class="teamMateBox">
            <li v-for="(mate,index) in teamMate" :key="index">
              <div class="mateBox">
                <el-image class="leaderImg" v-if="mate.is_creator" :src="team_leaderImg"></el-image>
                <div  class="deleteImg" @click="removeMate(index)">
                  <el-image v-if="is_creator&&!mate.is_creator" :src="deleteImg"></el-image>
                </div>
                <el-image :src="mate.avatar ? mate.avatar : defaultImg">
                   <el-image slot="error"    :src="defaultImg"></el-image>
                </el-image>
                <p>{{mate.user_name}}</p>
              </div>
            </li>
          </ul>
          <el-divider content-position="center" v-if="!canShow">邀请伙伴</el-divider>
          <div class="inviteBox" v-if="!canShow">
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
        </el-card>
        <el-card class="teamCard teamConfig">
          <div class="teamCardHeader">
            <h2>团队设置</h2>
          </div>
          <el-form style="padding:14px;" ref="form" v-model="form"  status-icon>
            <el-form-item label="团队名称" required >
              <el-input v-model="form.team_name" :disabled="canShow"  placeholder="如：项目突击队..."></el-input>
            </el-form-item>
            <el-form-item label="备注">
              <el-input
              :disabled="canShow"
                type="textarea"
                v-model="form.desc"
                placeholder="如：项目突击队主要用途是...">
              </el-input>
            </el-form-item>
            <el-form-item>
              <el-button :disabled="canShow" @click="commitForm" type="primary">更新</el-button>
              <el-button :disabled="canShow" @click="deleteTeam" type="danger">删除团队</el-button>
            </el-form-item>
          </el-form>
        </el-card>
      </div>
    </el-container>
  </el-container>
</template>

<script>
import { ajax, config, container } from '@/utils/helpers';
import defaultImg from '../../assets/avatar.png';
import defaultTeamImg from '../../assets/team.png';
import teamLeaderImg from '../../assets/team_leader.png';
import deleteImg from '../../assets/delete.png';
import inviteImg from '../../assets/invite.png';

export default {
  data() {
    return {
      defaultImg: '',
      defaultTeamImg: '',
      team_leaderImg: '',
      deleteImg: '',
      inviteImg: '',
      is_creator: false,
      team: {},
      canShow: true,
      isLoading: true,
      form: {
        team_name: '',
        desc: '',
      },
      inviteForm: {
        email: '',
      },
      inviteRules: {
        email: [
          { type: 'email', message: '请输入正确的邮箱地址', trigger: 'blur' },
        ],
      },
      teamMate: [],
    };
  },
  async beforeMount() {
    this.defaultImg = defaultImg;
    this.defaultTeamImg = defaultTeamImg;
    this.team_leaderImg = teamLeaderImg;
    this.deleteImg = deleteImg;
    this.inviteImg = inviteImg;

    this.team = this.$route.params;
    if (!this.team.team_id) {
      this.$router.push({ name: 'home' });
      return;
    }
    this.form.team_name = this.team.team_name;
    this.is_creator = this.team.is_creator;
    this.canShow = !this.is_creator;
    this.form.desc = this.team.desc;
    // 加载团队成员
    const res = await ajax(`${config.appAddress}teams/${this.team.team_id}`, 'GET', container.getHeader());
    this.teamMate = res.team_mates;
    this.isLoading = false;
  },
  methods: {
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
          await ajax(`${config.appAddress}teams/${this.team.team_id}/team_mate`, 'POST', container.getHeader(), { 'emails[]': this.inviteForm.email });
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
    deleteTeam() {
      this.$confirm('此操作将永久解散团队', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      }).then(async () => {
        await ajax(`${config.appAddress}teams/${this.team.team_id}`, 'DELETE', container.getHeader());
        this.$message({
          type: 'success',
          message: '删除成功!',
        });
        this.$router.push({ name: 'home', params: { tagActiveName: 'team' } });
      });
    },
    removeMate(index) {
      const mate = this.teamMate[index];
      this.$confirm('此操作将移除团队成员', '提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning',
      }).then(async () => {
        await ajax(`${config.appAddress}teams/${this.team.team_id}/team_mate`, 'DELETE', container.getHeader(), { userid: mate.user_id });
        this.teamMate.splice(index, 1);
        this.$message({
          type: 'success',
          message: '删除成功!',
        });
      });
    },
    async commitForm() {
      try {
        const requestForm = {
          team_name: this.form.team_name,
          desc: this.form.desc,
        };
        await ajax(`${config.appAddress}teams/${this.team.team_id}`, 'PUT', container.getHeader(), requestForm);
        this.team.team_name = this.form.team_name;
        this.team.desc = this.form.desc;
        this.$message({
          type: 'success',
          message: '更新成功',
        });
      } catch (e) {
        this.$message.error({
          message: '更新失败',
        });
      }
    },
  },
};
</script>

<style>
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
.teamSetting {
  display: flex;
  flex-direction: column;
  width: 100%;
}
.infoCardHeader {
  width: 290px;
  height: 290px;
}
.infoContent{
  padding:13px 10px;
}
.infoContent i{
  margin-right: 10px;
}
.teamCardHeader>h2{
  padding: 14px 14px;
  color: #fff;
  font-size: 1.5rem;
  font-weight: 400;
}
.teamCardHeader {
  background: #03A9F4;
}
.infoCard .el-card__body,.teamCard .el-card__body{
  padding: 0;
}
.infoCard{
  width:290px;
}
.teamCard{
  width: 66%;
  min-width: 400px;
}
.teamCard+.teamCard{
  margin-top: 15px;
}
.infoCard .el-divider{
  margin:0;
}
.infoCardUserName {
  text-align: center;
  margin: 15px 0;
  font-weight: 100;
  font-size: 1.3rem;
}
.infoCard .el-form-item{
  margin-bottom:15px;
}
.teamCard .el-form-item:last-child {
  text-align:center;
  margin-top: 35px;
  margin-bottom: 5px;
}
.avatar{
  width: 100%;
}
.avatar:hover{
  cursor: pointer;
}
</style>
