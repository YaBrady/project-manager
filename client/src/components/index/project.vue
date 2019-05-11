<template>
    <div class="projectListBox">
    <ul class="itemUl">
      <li v-for="(project,index) in projects" :key="index" @click="toProjectDetail(index)">
        <div
        :class="project.is_creator?'itemBox':'itemBox participant'"
        :title="project.project_name">
          <p class="projectName" v-if="!is_add(index)">{{project.project_name}}</p>
          <p class="projectTime" v-if="!is_add(index)">{{project.created_at}}</p>
          <p class="addProject"  v-if="is_add(index)">+</p>
        </div>
      </li>
    </ul>
    <el-dialog  class="newProjectDialog" title="新增项目" :visible.sync="dialogFormVisible">
      <el-form :model="form" ref="form" :rules="rules" status-icon >
        <el-form-item class="newProjectItem" label="项目名称" prop="name" >
          <el-input v-model="form.name" autocomplete="off"></el-input>
        </el-form-item>
        <el-form-item class="newProjectItem" label="备注" >
          <el-input v-model="form.desc"   type="textarea" autocomplete="off"></el-input>
        </el-form-item>
      </el-form>
      <div slot="footer">
        <el-button type="primary" @click="createProject">创 建</el-button>
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
      dialogFormVisible: false,
      projects: [],
      form: {
        name: '',
        desc: '',
      },
      rules: {
        name: [
          { required: true, message: '请输入项目名称', trigger: 'blur' },
        ],
      },
    };
  },
  async mounted() {
    const res = await ajax(`${config.appAddress}projects`, 'GET', container.getHeader());
    this.projects = res.projects;
    this.projects.splice(this.projects.length, 0, {
      is_add: true,
    });
  },
  methods: {
    async createProject() {
      this.$refs.form.validate(async (valid) => {
        if (!valid) {
          // 校验不通过
          return false;
        }
        try {
          const data = {
            project_name: this.form.name,
            desc: this.form.desc,
          };
          const res = await ajax(`${config.appAddress}projects`, 'POST', container.getHeader(), data);
          data.project_id = res.project_id;
          data.user_name = res.user_name;
          data.is_creator = 1;
          data.created_at = res.created_at;
          this.setTagActiveName('project_detail');
          this.$message({
            type: 'success',
            message: '创建成功',
          });
          this.$router.push(
            {
              name: 'project-detail',
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
      return this.projects[index].is_add;
    },
    async toProjectDetail(item) {
      const project = this.projects[item];
      if (project.is_add) {
        this.dialogFormVisible = true;
        return;
      }
      this.setTagActiveName('project_detail');
      this.$router.push(
        {
          name: 'project-detail',
          params: project,
        },
      );
    },
    ...mapActions({
      setTagActiveName: 'setTagActiveName',
    }),
  },
};
</script>

<style>
.newProjectDialog .el-form-item__error{
  top: 20px;
  left: unset;
  right: 2px;
}
.projectListBox .el-dialog__header {
  font-size: 14px;
  font-weight: 200;
  text-align: center;
}
.projectListBox .el-dialog{
  width: 380px;
}
.newProjectItem textarea{
  min-height: 80px !important;
}
.newProjectItem{
  font-weight: 700;
  color: #172b4d;
  font-size: 14px;
  line-height: 16px;
  margin-bottom:0;
}
.addProject{
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
.itemBox .projectName{
  text-align: center;
  padding: 15px;
  font-size: 1.4rem;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.itemBox .projectTime{
  text-align: right;
  padding: 6px;
  font-size: 0.9rem;
}
</style>
