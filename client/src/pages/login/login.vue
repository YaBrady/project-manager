<template>
  <div>
    <div class="loginBox">
      <h1>登入Pro-Manager</h1>
      <el-form ref="form" status-icon :rules="rules" :model="form" label-width="80px">
        <el-form-item :error="form.error_tip" label="邮箱地址" prop="email" >
          <el-input
          v-model="form.email"
          placeholder="如：gdkj@163.com">
          </el-input>
        </el-form-item>
        <el-form-item :error="form.error_tip" label="密码" prop="password">
          <el-input
            type="password"
            autocomplete="off"
            v-model="form.password"
            placeholder="如：......">
          </el-input>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSubmit">登陆</el-button>
        </el-form-item>

        <el-row>
          <el-col :offset="20">
            <el-link class="clickLink" type="primary" @click="register">还没账号？</el-link>
          </el-col>
          <el-col :offset="20">
            <el-link class="clickLink" type="primary">找回密码</el-link>
          </el-col>
        </el-row>
      </el-form>
    </div>
  </div>
</template>

<script>
import { ajax, config, container } from '@/utils/helpers';

export default {
  data() {
    return {
      form: {
        password: '',
        email: '',
        error_tip: '',
      },
      rules: {
        email: [
          { required: true, message: '请输入邮箱地址', trigger: 'blur' },
          { type: 'email', message: '请输入正确的邮箱地址', trigger: 'blur' },
        ],
        password: [
          { required: true, message: '请输入密码', trigger: 'blur' },
        ],
      },
    };
  },
  methods: {
    onSubmit() {
      this.$refs.form.validate(async (valid) => {
        if (!valid) {
          // 校验不通过
          return false;
        }
        setTimeout(async () => {
          // 登陆
          const loginForm = {
            email: this.form.email,
            password: this.form.password,
          };
          try {
            const res = await ajax(`${config.appAddress}login`, 'POST', null, loginForm);
            container.setHeader(res.meta.token_type, res.meta.access_token);
            this.$message({
              message: '登陆成功',
              type: 'success',
            });
            this.$router.push({ name: 'home' });
          } catch (e) {
            if (e.status === 401) {
              this.form.error_tip = '请检查密码与账号是否有误';
            }
          }
        }, 1000);
        return true;
      });
    },
    register() {
      this.$router.push({ name: 'register' });
    },
  },
};

</script>

<style>
  .loginBox {
    margin: 10% auto;
    width: 600px;
    text-align: left;
  }

  .clickLink {
    font-size: 20px;
    margin-bottom: 13px;
  }
  .loginBox h1{
    margin:25px 0;
  }
</style>
