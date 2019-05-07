import Vue from 'vue';
import Router from 'vue-router';
import { ajax, config, container } from '@/utils/helpers';
import store from '../store';

Vue.use(Router);
const canDebug = process.env.NODE_ENV !== 'production';
// 是否开启vue-devtools
Vue.config.debug = canDebug;
Vue.config.devtools = canDebug;
Vue.config.productionTip = canDebug;

const router = new Router({
  routes: [
    {
      path: '*',
      name: 'error',
      component: () => import('@/pages/error/error'),
      meta: { title: '页面404' },
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('@/pages/login/login'),
      meta: { title: '登陆' },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('@/pages/login/register'),
      meta: { title: '注册' },
    },
    {
      path: '/index',
      name: 'index',
      component: () => import('@/pages/index/index'),
      meta: { title: '首页' },
      children: [
        {
          path: 'home',
          name: 'home',
          component: () => import('@/components/index/home'),
          beforeEnter: (to, from, next) => {
            // 切换首页的tab
            const switchName = to.params.tagActiveName ? to.params.tagActiveName : 'team';
            store.dispatch('setTagActiveName', switchName);
            store.dispatch('setTabActiveName', switchName);
            next();
          },
        },
        {
          path: 'user-info',
          name: 'user-info',
          component: () => import('@/components/index/user-info'),
        },
        {
          path: 'team-detail',
          name: 'team-detail',
          component: () => import('@/components/index/team-detail'),
        },
        {
          path: 'invite-center',
          name: 'invite-center',
          component: () => import('@/components/index/invite-center'),
        },
      ],
    },
  ],
});
// 配置看守器
router.beforeEach(async (to, from, next) => {
  /* 路由发生变化修改页面title */
  if (to.meta.title) {
    document.title = to.meta.title;
  }
  // 装载用户信息
  if (container.hasHeader()) {
    let user = store.getters.getUser;
    if (!user) {
      try {
        const res = await ajax(`${config.appAddress}user`, 'GET', container.getHeader());
        user = res ? res.user : null;
        store.dispatch('setUser', user);
      } catch ($e) {
        if ($e.status === 401 || $e.status === 500) {
          container.clearHeader();
          this.$router.push({ name: 'login' });
        }
      }
    }
  }
  if (to.path !== '/login') {
    // 校验是否有登陆
    if (!container.hasHeader()) {
      // 查看用户信息是否有装载
      next({ name: 'login' });
    }
  }
  if (to.path === '/login') {
    if (container.hasHeader()) {
      next({ name: 'home' });
    }
  }
  next();
});
export default router;
