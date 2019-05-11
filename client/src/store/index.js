import Vue from 'vue';
import Vuex from 'vuex';
// 注入vuex
Vue.use(Vuex);
const store = new Vuex.Store({
  // 挂在数据
  state: {
    user: null,
    tagActiveName: 'team',
    tabActiveName: 'team',
    hasInvite: false,
    nowItem: '',
    itemId: 0,
    selectItemCp: null,
  },
  // 直接获取的数据
  getters: {
    getUser: state => state.user,
  },
  // methods
  mutations: {
    setUser(state, user) {
      state.user = user;
    },
    setTagActiveName(state, tagActiveName) {
      state.tagActiveName = tagActiveName;
    },
    setTabActiveName(state, tabActiveName) {
      state.tabActiveName = tabActiveName;
    },
    removeUser(state) {
      state.user = null;
    },
    setInvite(state, hasInvite) {
      state.hasInvite = hasInvite;
    },
    setNowItem(state, nowItem) {
      state.nowItem = nowItem;
    },
    setItemId(state, itemId) {
      state.itemId = itemId;
    },
    setSelectItemCp(state, selectItemCp) {
      state.selectItemCp = selectItemCp;
    },
  },
  // 触发动作
  actions: {
    setUser(context, user) {
      context.commit('setUser', user);
    },
    removeUser(context) {
      context.commit('removeUser');
    },
    setTagActiveName(context, tagActiveName) {
      context.commit('setTagActiveName', tagActiveName);
    },
    setTabActiveName(context, tabActiveName) {
      context.commit('setTabActiveName', tabActiveName);
    },
    setInvite(context, hasInvite) {
      context.commit('setInvite', hasInvite);
    },
    setNowItem(context, nowItem) {
      context.commit('setNowItem', nowItem);
    },
    setItemId(context, itemId) {
      context.commit('setItemId', itemId);
    },
    setSelectItemCp(context, selectItemCp) {
      context.commit('setSelectItemCp', selectItemCp);
    },
  },
});
export default store;
