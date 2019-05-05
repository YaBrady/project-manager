<template>
  <div>
    <div class="loginBox">
      <h1>登入Pro-Manager</h1>

      <el-form ref="form" :model="form" label-width="80px">
        <el-form-item label="邮箱地址">
          <el-input
          :class="form.error ? 'inputError' : ''"
          v-model="form.email"
          placeholder="如：gdkj@163.com">
          </el-input>
        </el-form-item>
        <el-form-item label="密码">
          <el-input
            type="password"
            :class="form.error ? 'inputError' : ''"
            autocomplete="off"
            v-model="form.password"
            placeholder="如：......">
          </el-input>
        </el-form-item>
        <el-form-item>
          <p style="color:#F56C6C;">{{form.tip}}</p>
          <el-button type="primary" @click="onSubmit">登陆</el-button>
        </el-form-item>

        <el-row>
          <el-col :offset="20">
            <el-link class="clickLink" type="primary">还没账号？</el-link>
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
import { ajax, config, Container } from '@/utils/helpers';

export default {
  data() {
    return {
      form: {
        name: '',
        password: '',
        email: '',
        tip: '',
        error: false,
      },
      container: null,
    };
  },
  mounted() {
    this.container = new Container();
  },
  methods: {
    async onSubmit() {
      // 登陆
      const loginForm = {
        email: this.form.email,
        password: this.form.password,
      };
      try {
        const res = await ajax(`${config.appAddress}login`, 'POST', null, loginForm);
        this.container.setHeader(res.meta.token_type, res.meta.access_token);
        this.$router.push({ name: 'index' });
      } catch (e) {
        if (e.status === 401) {
          this.form.error = true;
          this.form.tip = '请检查密码与账号是否有误';
        }
      }
    },
  },
};

</script>

<style>
  .loginBox {
    margin: 100px auto;
    width: 600px;
    text-align: left;
  }

  .clickLink {
    font-size: 20px;
    margin-bottom: 13px;
  }

  .inputError input.el-input__inner{
    border: 1px solid red !important;
  }

</style>
