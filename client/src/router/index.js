import Vue from 'vue';
import Router from 'vue-router';

Vue.use(Router);

export default new Router({
  routes: [
    {
      path: '*',
      name: 'error',
      component: () => import('@/components/error/error'),
      meta: { title: '页面404' },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/components/login/login'),
      meta: { title: '登陆' },
    },
    {
      path: '/index',
      name: 'index',
      component: () => import('@/components/index/index'),
      meta: { title: '首页' },
    },
  ],
});
