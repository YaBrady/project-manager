<template>
  <el-container style="margin:2% auto;">
    <el-aside width="400px" style="margin: 0 0 0 28%;">
      <el-card class="infoCard">
        <div class="infoCardHeader"  @click="upload" >
          <el-image class="avatar" :src="user.avatar ? user.avatar : defaultImg">
            <el-image slot="error"  class="avatar"  :src="defaultImg"></el-image>
          </el-image>
        </div>
          <p class="infoCardUserName">{{user.name}}</p>
          <el-divider></el-divider>
          <p class="infoContent">
            <i style="color:#FF5722;" class="el-icon-message"></i>{{user.email}}
          </p>
          <el-divider></el-divider>
          <p class="infoContent">
            <i style="color:#03A9F4;" class="el-icon-time"></i>创建于{{user.created_at}}
          </p>
      </el-card>
    </el-aside>
    <el-container>
      <el-card class="updateCard">
        <div class="updateCardHeader">
          <h2>Setting</h2>
        </div>
        <el-form  ref="form" :model="form" :rules="rules" status-icon  style="padding: 14px;">
        <el-form-item label="邮箱地址">
            <el-input v-model="user.email" disabled placeholder="如：gdkj@163.com"></el-input>
        </el-form-item>
        <el-form-item label="昵称" prop="name">
            <el-input v-model="form.name" placeholder="如：小辣椒"></el-input>
        </el-form-item>
        <el-form-item label="新密码" prop="password">
            <el-input
            type="password"
            autocomplete="off"
            v-model="form.password" ></el-input>
        </el-form-item>
        <el-form-item label="确认新密码" prop="checkPassword">
          <el-input
          type="password"
          autocomplete="off"
          v-model="form.checkPassword" ></el-input>
        </el-form-item>
        <el-form-item>
          <el-button @click="commitForm" type="primary">更新</el-button>
          <el-button @click="resetForm">重置</el-button>
        </el-form-item>
        </el-form>
      </el-card>
    </el-container>
    <input type="file" id="uploadInput" @change="chooseFile($event)" hidden>
  </el-container>
</template>

<script>
import { ajax, config, container } from '@/utils/helpers';
import { mapState, mapActions } from 'vuex';
import defaultImg from '../../assets/avatar.png';

export default {
  data() {
    const passCheck = (rule, value, callback) => {
      if (this.form.password !== '') {
        if (value === '') {
          callback(new Error('请重新输入密码'));
        }
        if (value !== this.form.password) {
          callback(new Error('两次密码不一致'));
        }
      }
      callback();
    };
    return {
      fileInput: null,
      defaultImg: '',
      form: {
        name: '',
        password: '',
        checkPassword: '',
      },
      rules: {
        name: [
          { required: true, message: '请输入昵称', trigger: 'blur' },
          { min: 3, max: 10, message: '请输入3-10个字符', trigger: 'blur' },
        ],
        checkPassword: [
          { validator: passCheck, trigger: 'change' },
        ],
      },
    };
  },
  beforeMount() {
    this.form.name = this.user.name;
    this.defaultImg = defaultImg;
  },
  mounted() {
    this.fileInput = document.getElementById('uploadInput');
  },
  computed: {
    ...mapState({
      user: state => state.user,
    }),
  },
  methods: {
    ...mapActions({
      setUser: 'setUser',
    }),
    async chooseFile(event) {
      const file = event.target.files[0];
      try {
        let requestForm = new FormData();
        requestForm.append('file', file);
        let res = await ajax(`${config.appAddress}file`, 'POST', container.getHeader(), requestForm, true);
        requestForm = {
          file_id: res.file_id,
        };
        res = await ajax(`${config.appAddress}user`, 'POST', container.getHeader(), requestForm);
        this.user.avatar = res.user.avatar;
        this.$message({
          type: 'success',
          message: '更换头像成功',
        });
      } catch (e) {
        this.$message.error({
          message: '更换头像失败',
        });
      }
    },
    upload() {
      this.fileInput.click();
    },
    resetForm() {
      this.$refs.form.resetFields();
    },
    commitForm() {
      this.$refs.form.validate(async (valid) => {
        if (!valid) {
          return false;
        }
        try {
          const requestForm = {
            name: this.form.name,
            password: this.form.password,
          };
          await ajax(`${config.appAddress}user`, 'POST', container.getHeader(), requestForm);
          this.user.name = this.form.name;
          this.$message({
            type: 'success',
            message: '更新成功',
          });
          this.$router.push({ name: 'home' });
        } catch (e) {
          this.$message.error({
            message: '更新失败',
          });
        }
        return true;
      });
    },
  },
};
</script>

<style>
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
.updateCard h2{
  padding: 20px 14px;
  color: #fff;
  font-size: 2.5rem;
  font-weight: 400;
}
.updateCardHeader {
  background: #03A9F4;
}
.infoCard .el-card__body,.updateCard .el-card__body{
  padding: 0;
}
.infoCard{
  width:290px;
}
.updateCard{
  width:400px;
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
.updateCard .el-form-item:last-child {
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
