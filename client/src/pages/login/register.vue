<template>
  <div>
     <el-card class="registerCard registerBox">
       <div class="registerCardHeader">
        <h1 style="display:inline-block;width:20%;">注册</h1>
        <div style="widht:70%;padding: 11px 0;float:right;text-align:right;">
          <el-button
          style="margin-right:10px;"
          @click="home"
          icon="el-icon-s-home"
          circle>
          </el-button>
        </div>
       </div>
       <el-form  ref="form" :model="form" :rules="rules" status-icon  style="padding: 14px;">
        <el-form-item label="邮箱地址" :error="form.error_tip" prop="email">
            <el-input v-model="form.email" placeholder="如：gdkj@163.com"></el-input>
        </el-form-item>
        <el-form-item label="昵称" prop="name">
            <el-input v-model="form.name" placeholder="如：小辣椒"></el-input>
        </el-form-item>
        <el-form-item label="密码" prop="password">
            <el-input
            type="password"
            autocomplete="off"
            v-model="form.password" ></el-input>
        </el-form-item>
        <el-form-item label="确认密码" prop="checkPassword">
          <el-input
          type="password"
          autocomplete="off"
          v-model="form.checkPassword" ></el-input>
        </el-form-item>
        <el-form-item>
          <el-button @click="commitForm" type="primary">注册</el-button>
          <el-button @click="resetForm">重置</el-button>
        </el-form-item>
       </el-form>
    </el-card>
  </div>
</template>

<script>
import { ajax, config, container } from '@/utils/helpers';

export default {
  data() {
    const passCheck = (rule, value, callback) => {
      if (value === '') {
        callback(new Error('请重新输入密码'));
      }
      if (value !== this.form.password) {
        callback(new Error('两次密码不一致'));
      }
      callback();
    };
    return {
      form: {
        email: '',
        name: '',
        password: '',
        checkPassword: '',
        error_tip: '',
      },
      rules: {
        email: [
          { required: true, message: '请输入邮箱地址', trigger: 'blur' },
          { type: 'email', message: '请输入正确的邮箱地址', trigger: 'blur' },
        ],
        name: [
          { required: true, message: '请输入昵称', trigger: 'blur' },
          { min: 3, max: 10, message: '请输入3-10个字符', trigger: 'blur' },
        ],
        password: [
          { required: true, message: '请输入密码', trigger: 'blur' },
        ],
        checkPassword: [
          { validator: passCheck, trigger: 'change' },
        ],
      },
    };
  },
  methods: {
    home() {
      this.$router.push({ path: '/login' });
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
            email: this.form.email,
          };
          const res = await ajax(`${config.appAddress}register`, 'POST', null, requestForm);
          container.setHeader(res.meta.token_type, res.meta.access_token);
          this.$message({
            message: '注册成功',
            type: 'success',
          });
          this.$router.push({ name: 'home' });
        } catch (e) {
          if (e.status === 400) {
            this.$message.error({
              message: '注册失败',
            });
            this.form.error_tip = '邮箱地址已经被注册';
          }
        }
        return true;
      });
    },
  },
};
</script>

<style>
.registerBox{
  margin: 10% auto 0 auto;
}
.registerBox .el-card__body{
  padding: 0;
}
.registerCard{
  width:360px;
}
.registerCard h1 {
  padding: 10px 14px;
  color: #fff;
  font-weight: 400;
}
.registerCardHeader {
  background: #03A9F4;
}
.registerCard .el-form-item{
  margin-bottom:15px;
}
.registerCard .el-form-item:last-child {
  text-align:center;
  margin-top: 35px;
  margin-bottom: 5px;
}
</style>
