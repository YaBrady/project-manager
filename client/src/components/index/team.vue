<template>
  <div class="teamListBox">
    <ul class="itemUl">
      <li v-for="(team,index) in teams" :key="index" @click="toTeamDetail(index)">
        <div :class="team.is_creator?'itemBox':'itemBox participant'"  :title="team.team_name">
          <p class="teamName" v-if="!is_add(index)">{{team.team_name}}</p>
          <p class="teamTime" v-if="!is_add(index)">{{team.created_at}}</p>
          <p class="addTeam"  v-if="is_add(index)">+</p>
        </div>
      </li>
    </ul>
    <el-dialog  class="newTeamDialog" title="新增团队" :visible.sync="dialogFormVisible">
      <el-form :model="form" ref="form" :rules="rules" status-icon >
        <el-form-item class="newTeamItem" label="团队名称" prop="name" >
          <el-input v-model="form.name" autocomplete="off"></el-input>
        </el-form-item>
        <el-form-item class="newTeamItem" label="备注" >
          <el-input v-model="form.desc"   type="textarea" autocomplete="off"></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer">
        <el-button type="primary" @click="createTeam">创 建</el-button>
      </div>
  </el-dialog>

  </div>
</template>

<script>
import { ajax, config, container } from '@/utils/helpers';
import { mapActions } from 'vuex';

export default {
  data() {
    return {
      teams: [],
      dialogFormVisible: false,
      form: {
        name: '',
        desc: '',
      },
      rules: {
        name: [
          { required: true, message: '请输入团队名称', trigger: 'blur' },
        ],
      },
    };
  },
  methods: {
    createTeam() {
      this.$refs.form.validate(async (valid) => {
        if (!valid) {
          // 校验不通过
          return false;
        }
        try {
          const data = {
            team_name: this.form.name,
            desc: this.form.desc,
          };
          const res = await ajax(`${config.appAddress}teams`, 'POST', container.getHeader(), data);
          data.team_id = res.team_id;
          data.user_name = res.user_name;
          data.is_creator = 1;
          data.created_at = res.created_at;
          this.setTagActiveName('team_detail');
          this.$message({
            type: 'success',
            message: '创建成功',
          });
          this.$router.push(
            {
              name: 'team-detail',
              params: data,
            },
          );
        } catch (e) {
          this.$message.error({
            message: '创建失败',
          });
        }
        return true;
      });
    },
    is_add(index) {
      return this.teams[index].is_add;
    },
    toTeamDetail(item) {
      const team = this.teams[item];
      if (team.is_add) {
        this.dialogFormVisible = true;
        return;
      }
      this.setTagActiveName('team_detail');
      this.$router.push(
        {
          name: 'team-detail',
          params: team,
        },
      );
    },
    ...mapActions({
      setTagActiveName: 'setTagActiveName',
    }),
  },
  async mounted() {
    const res = await ajax(`${config.appAddress}teams`, 'GET', container.getHeader());
    this.teams = res.teams;
    this.teams.splice(this.teams.length, 0, {
      is_add: true,
    });
  },
};
</script>

<style>
.newTeamDialog .el-form-item__error{
  top: 20px;
  left: unset;
  right: 2px;
}
.teamListBox .el-dialog__header {
  font-size: 14px;
  font-weight: 200;
  text-align: center;
}
.teamListBox .el-dialog{
  width: 380px;
}
.newTeamItem textarea{
  min-height: 80px !important;
}
.newTeamItem{
  font-weight: 700;
  color: #172b4d;
  font-size: 14px;
  line-height: 16px;
  margin-bottom:0;
}
.addTeam{
  text-align: center;
  font-size: xx-large;
  padding: 25px 0;
}
.itemUl{
  list-style: none;
}
.itemUl li{
  float: left;
  margin:0 0 21px 21px;
  transition: all .2s;
}
.itemUl li:hover{
  box-shadow: 5px 9px 12px 0 rgba(0, 0, 0, 0.1);
  cursor: pointer;
}
.itemUl:after{
  content: '';
  display: block;
  clear:both;
}
.itemBox{
  min-height: 88px;
  width: 142px;
  background: #03A9F4;
  border-radius: 4px;
  color: #fff;
}
.participant {
  background: #607d8b;
}
.itemBox .teamName{
  text-align: center;
  padding: 15px;
  font-size: 1.4rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.itemBox .teamTime{
  text-align: right;
  padding: 6px;
  font-size: 0.9rem;
}
</style>
