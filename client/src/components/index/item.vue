<template>
  <ul class="proItemBox" v-loading="itemBoxLoading">
    <li>
      <project-item-list v-on:changeStatus="changeStatus" :listData="listData[0]"></project-item-list>
    </li>
    <li>
      <project-item-list v-on:changeStatus="changeStatus" :listData="listData[1]"></project-item-list>
    </li>
    <li>
      <project-item-list v-on:changeStatus="changeStatus" :listData="listData[2]"></project-item-list>
    </li>
    <li>
      <project-item-list v-on:changeStatus="changeStatus"  :listData="listData[3]"></project-item-list>
    </li>
  </ul>
</template>

<script>
import { ajax, config, container } from '@/utils/helpers';
import item_list from '@/components/index/item-list';
import { mapState, mapActions} from 'vuex';

export default {
  props: ['project_id'],
  components: {
    'project-item-list': item_list,
  },
  data() {
    return {
      undoList: [],
      preList: [],
      doingList: [],
      endList: [],
      listData: [{},{},{},{}],
      itemBoxLoading: true,
    };
  },
  async beforeMount() {
    if (!this.project_id) {
      this.$router.push({ name: 'home', params: { tagActiveName: 'project' } });
      return;
    }
    const res = await ajax(`${config.appAddress}projects/${this.project_id}/items`, 'GET', container.getHeader());
    this.undoList = res.undoList;
    this.undoList.push({
      is_add: true,
      item_name: '',
    });
    this.preList = res.preList;
    this.preList.push({
      is_add: true,
      item_name: '',
    });
    this.doingList = res.doingList;
    this.doingList.push({
      is_add: true,
      item_name: '',
    });
    this.endList = res.endList;
    this.endList.push({
      is_add: true,
      item_name: '',
    });

    this.listData = [
      {
        list: this.undoList,
        project_id: this.project_id,
        status: 0,
        uniqueKey: 'undo',
        title: '待处理',
      },
      {
        list: this.preList,
        project_id: this.project_id,
        status: 1,
        uniqueKey: 'pre',
        title: '准备中',
      },
      {
        list: this.doingList,
        project_id: this.project_id,
        status: 2,
        uniqueKey: 'doing',
        title: '进行中',
      },
      {
        list: this.endList,
        project_id: this.project_id,
        status: 3,
        uniqueKey: 'end',
        title: '已完成',
      },
    ]
    this.itemBoxLoading = false;
  },
  computed: {
    ...mapState({
      nowItem: state => state.nowItem
    }),
  },
  mounted() {
    $(document).ready(() => {
      $(document).on("click", (function(method) {
      return (e) => {
        if($(e.target).closest(".itemListInput").length == 0 ){
          method();
        }
      };
      })(this.cleanInputStatus));
    })
  },
  methods: {
    ...mapActions({
      setNowItem: 'setNowItem'
    }),
    cleanInputStatus() {
      this.setNowItem('');
    },
    changeStatus(originStatus, index, toStatus) {
      toStatus = Number(toStatus);
      const origin = this.listData[originStatus].list;
      const item = origin[index];
      const to = this.listData[toStatus].list;
      let position = to.length;
      this.listData[toStatus].list.every((element,index) => {
        console.log(index,element.time,item.time);
        if (element.time && element.time <= item.time) {
          position = index;
          return false;
        }
      });
      if (position == to.length) {
        position --;
      }
      to.splice(position, 0, item);
      origin.splice(index, 1);
    }
  },
};
</script>

<style>


.proItemBox {
  list-style: none;
  min-width: 1600px;
}
.proItemBox>li {
  float: left;
  width: 24%;
}
.proItemBox:after{
  content: '';
  display: inline-block;
  clear:both;
}
.proItemBox>li+li {
  margin-left: 15px;
}
</style>
