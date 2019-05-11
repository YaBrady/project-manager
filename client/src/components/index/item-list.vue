<template>
  <el-card class="item-box-card">
    <h2>{{listData.list ? listData.title : ''}} ({{listData.list ? listData.list.length-1 : 0}})</h2>
    <div style="padding: 10px 0;"></div>
    <el-card class="item-list-box-card" v-for="(item,index) in listData?  listData.list: []" :key="index" shadow="hover">
      <div @click="showItemDetail(index)" >
        <div class="item-title">
          <div style="padding: 8px 15px 9px 15px;" class="newInputText inputText" v-if="(listData.uniqueKey+index != nowItem) && item.is_add" @click="showItemInput($event, index, item)">+</div>
          <div style="padding: 8px 15px 9px 15px;"     v-if="(listData.uniqueKey+index != nowItem) && !item.is_add"> {{item.item_name}}</div>
          <el-input class="itemListInput" @blur="updateItemName(item)" v-if="listData.uniqueKey+index == nowItem" v-model="item.item_name" placeholder="输入条目标题"></el-input>
        </div>
        <div class="item-info" v-if="!item.is_add">
          <i class="el-icon-tickets" v-if="item.list_sum">
            {{item.list_finish}}/{{item.list_sum}}
          </i>
          <i class="el-icon-link">
          {{item.file_sum}}
          </i>
          <i class="el-icon-edit-outline">
          {{item.comment_sum}}
          </i>
          <i class="el-icon-time">
          {{item.time}}
          </i>
        </div>
      </div>
    </el-card>

    <el-dialog title="条目详情" class="item-box-dialog"  :visible.sync="dialogFormVisible">
      <el-dialog
      width="30%"
      title="提示"
      :visible.sync="innerVisible"
      append-to-body>
        <span>此操作会永久删除条目</span>
  <span slot="footer" class="dialog-footer">
    <el-button @click="innerVisible = false">取 消</el-button>
    <el-button type="danger" @click="deleteTrueItem">确 定</el-button>
  </span>
    </el-dialog>

      <div style="display:flex;" v-loading="detailLoading">
        <div class="item-box-dialog-main">
          <div class="dialogHeader">
            <el-image :src="editImg" >
            </el-image>
               <div style="    width: 300px;padding: 12px 0 12px 16px;" class="dialog-title" @click="showItemInput($event, `${selectIndex}${selectItem.id}`, selectItem)"    v-if="`${this.listData.uniqueKey}${selectIndex}${selectItem.id}` != nowItem"> {{selectItem.item_name}}</div>
               <el-input class="itemListInput" @blur="updateItemName(selectItem)" v-if="`${this.listData.uniqueKey}${selectIndex}${selectItem.id}` == nowItem" v-model="selectItem.item_name" placeholder="输入条目标题"></el-input>
          </div>

          <div class="dialogHeader">
            <el-image :src="descImg" >
            </el-image>
            <div style="margin-left:14px;">
              <p class="dialogHeaderDesc">描述</p>
              <el-input type="textarea"  @blur="updateItemDesc(selectItem)" style="width: 450px;"  v-model="selectItem.desc" placeholder="该条目的作用是..."></el-input>
            </div>
          </div>
          <div class="dialogHeader">
            <el-image :src="statusImg" >
            </el-image>
            <div style="margin-left:14px;">
              <p class="dialogHeaderDesc">项目状态</p>
              <div>
                <el-radio-group @change="changeStatus" v-model="selectItem.status">
                  <el-radio-button label="0" >待处理</el-radio-button>
                  <el-radio-button label="1">准备中</el-radio-button>
                  <el-radio-button label="2">进行中</el-radio-button>
                  <el-radio-button label="3">已完成</el-radio-button>
                </el-radio-group>
              </div>
            </div>
          </div>
          <div class="dialogHeader" v-if="dateShow">
            <el-image :src="dateImg" >
            </el-image>
            <div style="margin-left:14px;">
              <p class="dialogHeaderDesc">添加到期日</p>
              <div>
                 <el-date-picker
                  v-model="selectTime"
                  type="daterange"
                  format="yyyy-MM-dd"
                  range-separator="至"
                  @change="selectTimeEvent"
                  start-placeholder="开始日期"
                  end-placeholder="结束日期"
                  >
                </el-date-picker>
              </div>
            </div>
          </div>
          <div class="dialogHeader" v-if="stepShow">
            <el-image :src="completeImg" >
            </el-image>
            <div style="margin-left:14px;">
              <p class="dialogHeaderDesc">步骤条</p>
              <div>
                <div style="width: 450px;">
                  <el-progress :percentage="getProgress()" :status="getProgressStatus"></el-progress>
                </div>
                <div v-for="(item,index) in steps">
                  <div class="stopBox">
                    <el-checkbox  @change="completeStep(item)"  style="    padding: 10px 0px 10px 0;" true-label=1 false-label=0 v-model="item.status"></el-checkbox>
                    <el-input @blur="postItemContent(item)" autofocus style="width: 79%" v-model="item.list_content" placeholder="请输入步骤所需工作..."></el-input>
                    <i class="el-icon-close itemClose" @click="deleteIteemContent(item, index)"></i>
                  </div>
                </div>
                <div style="margin-top:10px;">
                  <el-button type="info" style="margin-right: 30px;" @click="addStep">添加步骤</el-button>
                    <i class="el-icon-tickets">
                      {{finishStep}}/{{steps.length}}
                    </i>
                </div>
              </div>
            </div>
          </div>
            <div class="dialogHeader" v-if="fileShow">
            <el-image :src="fileImg" >
            </el-image>
            <div style="margin-left:14px;">
              <p class="dialogHeaderDesc">附件列表</p>
              <div style="width: 510px;">
                  <el-table
                    :data="fileData"
                    style="width: 100%"
                    @selection-change="handleSelectionChange"
                    >
                    <el-table-column
                      type="selection"
                      width="35">
                    </el-table-column>
                    <el-table-column
                      prop="origin_name"
                      label="文件名"
                      sortable
                      width="140">
                      <template slot-scope="scope">
                        <i class="el-icon-link"></i>
                        <el-link type="primary" :href="scope.row.url">
                          {{ scope.row.origin_name }}
                        </el-link>
                      </template>
                    </el-table-column>
                    <el-table-column
                      prop="name"
                      label="用户名"
                      sortable
                      width="180">
                    </el-table-column>
                    <el-table-column
                      prop="created_at"
                      label="上传时间"
                      >
                    </el-table-column>
                  </el-table>
              </div>
              <el-button type="danger" @click="deleteFiles" style="margin-top:16px;d">删除所选</el-button>
            </div>
          </div>
          <div class="dialogHeader" >
            <el-image :src="talkImg" >
            </el-image>
            <div style="margin-left:14px;position:relative;">
            <p class="dialogHeaderDesc">发表评论</p>
            <el-form :model="commentform" ref="commentform" :rules="commentRule">
              <el-form-item  prop="comment_content">
               <el-input type="textarea" class="commentBox" style="width: 450px;"  v-model="commentform.comment_content" placeholder="说点什么好呢..."></el-input>
              </el-form-item>
              <div class="dialogCommentBox">
                <el-image  class="commenAvatar" :src="user.avatar ? user.avatar : defaultImg">
                <el-image  slot="error"  class="commenAvatar"  :src="defaultImg"></el-image>
              </el-image>
              </div>
            <div style="margin-top: 9px;">
              <el-button type="info" @click="toComment">发表</el-button>
            </div>
            </el-form>
            </div>
          </div>
          <div class="dialogHeader" >
            <el-image :src="commentImg" >
            </el-image>
            <div style="margin-left:14px;position:relative;">
            <p class="dialogHeaderDesc">评论列表</p>
              <div>
                <div v-for="(comment,index) in comments" class="comment-outletbox">
                  <div class="comment-box">
                    <div>
                      <div style="width:32px">
                      <el-image :src="comment.avatar">
                        <el-image slot="error"  class="indexHeader-Image"  :src="defaultImg"></el-image>
                      </el-image>
                      </div>
                      <p>{{comment.name}}</p>
                      <p>{{comment.created_at}}</p>
                    </div>
                    <div style="padding-left: 5px;margin-left: 5px;border-left: 1px solid #a08f8f;">
                      <p>发表了：{{comment.comment}}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="funListBox">
          <div class="dialogHeader" >
            <el-image :src="listImg" >
            </el-image>
            <div style="margin-left:14px;position:relative;">
            <p class="dialogHeaderDesc">功能列表</p>
            </div>
          </div>

          <ul class="funList">
            <li><el-button type="success" icon="el-icon-date" @click="attachDate">到期日</el-button></li>
            <li><el-button type="success" icon="el-icon-tickets" @click="attachStep">步骤</el-button></li>
            <li><el-button type="success" icon="el-icon-link" @click="depatch">附件</el-button></li>
            <li><el-button type="danger" icon="el-icon-link" @click="deleteItem">删除条目</el-button></li>
          </ul>
        </div>
      </div>
    </el-dialog>
      <input type="file" id="uploadItemInput" @change="chooseFile2($event)" hidden>
  </el-card>
</template>

<script>
import { ajax, config, container } from '@/utils/helpers';
import { mapState, mapActions} from 'vuex';
import editImg from '../../assets/edit.png';
import descImg from '../../assets/desc.png';
import talkImg from '../../assets/chat.png';
import defaultImg from '../../assets/avatar.png';
import statusImg from '../../assets/status.png';
import listImg from '../../assets/list.png';
import dateImg from '../../assets/calendar.png';
import completeImg from '../../assets/complete.png';
import fileImg from '../../assets/file.png';
import commentImg from '../../assets/comments.png';

export default {
  props: ['listData'],
  data() {
    return {
      dialogFormVisible: false,
      commentform: {
        comment_content: '',
      },
      commentRule: {
        comment_content: [
          { required: true, message: '评论内容不可为空', trigger: 'blur' }
        ]
      },
      selectItem: {
        item_name: '',
      },
      selectIndex: -1,
      editImg: '',
      descImg: '',
      talkImg: '',
      defaultImg: '',
      statusImg: '',
      listImg: '',
      dateImg: '',
      completeImg: '',
      fileImg: '',
      commentImg: '',
      detailLoading: true,
      dateShow: false,
      selectTime: [],
      fileInput: null,
      stepShow: true,
      steps: [],
      finishStep: 0,
      fileShow: true,
      fileData: [],
      deletesFiles: [],
      innerVisible: false,
    };
  },
  computed: {
    ...mapState({
      nowItem: state => state.nowItem,
      user: state => state.user,
      itemId: state => state.itemId,
      selectItemCp: state => state.selectItemCp,
    }),
    getProgressStatus() {
      const progress = this.getProgress();
      if (progress<30) {
        return 'exception';
      } else if(progress == 100) {
        return 'success';
      }
    }
  },
  mounted() {
    this.fileInput = document.getElementById('uploadItemInput');
  },
  beforeMount() {
    this.editImg = editImg;
    this.descImg = descImg;
    this.talkImg = talkImg;
    this.defaultImg = defaultImg;
    this.statusImg = statusImg;
    this.listImg = listImg;
    this.dateImg = dateImg;
    this.fileImg = fileImg;
    this.commentImg = commentImg;
    this.completeImg = completeImg;
  },
  methods: {
    ...mapActions({
      setNowItem: 'setNowItem',
      setItemId: 'setItemId',
      setSelectItemCp: 'setSelectItemCp',
    }),
    async deleteTrueItem() {
      await ajax(`${config.appAddress}projects/${this.listData.project_id}/items/${this.itemId}`, 'DELETE', container.getHeader());
      this.$message({
        type: 'success',
        message: '删除成功!',
      });
      this.innerVisible = false;
      this.listData.list.splice(this.selectIndex, 1)
      this.dialogFormVisible = false;
      this.selectIndex = -1;
      this.selectTime = null;
    },
    handleSelectionChange(val) {
      this.deletesFiles = val;
    },
    async deleteItem() {
      console.log(this.selectItem);

      if (this.selectItem) {
        this.innerVisible = true;
      }
    },
    async deleteFiles() {
      if (this.deletesFiles.length) {
        const fileids = [];
        console.log(this.deletesFiles);
        console.log(this.selectItem);

        for(const item of this.deletesFiles) {
          console.log('1');

        }
        this.deletesFiles.forEach(element => {
          fileids.push(element.id);
          this.selectItemCp.files.every((element2,index) =>{
            if (element2.id === element.id) {
              this.selectItem.files.splice(index, 1);
              return false;
            }
          })
        });

        this.listData.list[this.selectIndex].file_sum -= fileids.length;
        const res = await ajax(`${config.appAddress}projects/${this.listData.project_id}/items/${this.itemId}/files`, 'DELETE', container.getHeader(),  {fileids});

      }
      this.$message({
        type: 'success',
        message: '操作成功',
      });
    },
    async postItemContent(item) {
      const requestForm = {
        list_content: item.list_content,
        status: item.status,
      };
      if (!item.id) {
        const res = await ajax(`${config.appAddress}projects/${this.listData.project_id}/items/${this.itemId}/lists`, 'POST', container.getHeader(),  requestForm);
        item.id = res.list_id;
      } else {
        await ajax(`${config.appAddress}projects/${this.listData.project_id}/items/${this.itemId}/lists/${item.id}`, 'PUT', container.getHeader(),  requestForm);
      }
    },
    async deleteItemContent(item, index) {
      if (!item.id) {
        return;
      }
      await ajax(`${config.appAddress}projects/${this.listData.project_id}/items/${this.itemId}/lists/${item.id}`, 'DELETE', container.getHeader());
      if (item.status) {
        this.finishStep --;
      }
      this.steps.splice(index, 1);
    },
    getProgress() {
      if (this.steps.length == 0 ){
        return 0;
      }
      return Math.round((this.finishStep / this.steps.length)*100);
    },
    async completeStep(item) {
      if(item.status === "1") {
        this.finishStep ++;
      } else if (item.status == "0"){
        this.finishStep --;
      }
      if (item.id) {
        await ajax(`${config.appAddress}projects/${this.listData.project_id}/items/${this.itemId}/lists/${item.id}`, 'PUT', container.getHeader(), {status: item.status });
      }
    },
    addStep() {
      this.steps.push({
        status: 0,
        list_: '',
      });
    },
    depatch() {
      this.fileInput.click();
    },
    async selectTimeEvent() {
      const endtime = this.selectTime[1];
      const item = this.selectItem;
      if (new Date(item.time)>endtime){
        this.$message.error({
          message: '结束时间不能小于条目创建时间',
        });
        const itemTime = new Date(item.time);
        this.selectTime=[];
        this.selectTime.push(itemTime);
        this.selectTime.push(itemTime);
      } else {
        const end_time = (new Date(endtime)).toLocaleDateString().split('/');
        await ajax(`${config.appAddress}projects/${this.listData.project_id}/items/${this.selectItem.id}`, 'PUT', container.getHeader(), { end_time:`${end_time[2]}-${end_time[0]}-${end_time[1]}` });
        this.listData.list[this.selectIndex].end_time = `${end_time[2]}-${end_time[0]}-${end_time[1]}`;
        this.selectItem.end_time = `${end_time[2]}-${end_time[0]}-${end_time[1]}`;
        this.$message({
          type: 'success',
          message: '添加到期日成功',
        });
      }

    },
    attachStep() {
      this.stepShow = !this.stepShow;
    },
    async attachDate() {
      const item = this.selectItem;
      this.selectTime=[];
      if (this.dateShow && item.end_time) {
        this.dateShow = false;
        if (item.end_time !== '0000-00-00 00:00:00') {
          await ajax(`${config.appAddress}projects/${this.listData.project_id}/items/${this.selectItem.id}`, 'PUT', container.getHeader(), { end_time: '0000-00-00 00:00:00' });
          this.listData.list[this.selectIndex].end_time = '0000-00-00 00:00:00';
          this.selectItem.end_time = '0000-00-00 00:00:00';
        }
      } else {
        const starttime = new Date(item.time);
        this.selectTime.push(starttime);
        let endtime = starttime;
        if (item.end_time !== '0000-00-00 00:00:00') {
          endtime = new Date(item.end_time);
        }
        this.selectTime.push(endtime)
        this.dateShow = true;
      }
    },
    async chooseFile2(event) {
      const file = event.target.files[0];
      try {
        let requestForm = new FormData();
        requestForm.append('file', file);
        let res = await ajax(`${config.appAddress}file`, 'POST', container.getHeader(), requestForm, true);
        requestForm = {
          file_id: res.file_id
        };
        res = await ajax(`${config.appAddress}projects/${this.listData.project_id}/items/${this.itemId}`, 'PUT', container.getHeader(),  requestForm);
        console.log(this.selectItemCp);
        this.selectItemCp.files.unshift({
          url: res.file.url,
          origin_name: res.file.origin_name,
          created_at: res.file.created_at,
          name: res.file.name,
        });
        // 添加到附件列表中
        this.$message({
          type: 'success',
          message: '添加附件成功',
        });
      } catch (e) {
        console.log(e);

        this.$message.error({
          message: '添加附件失败',
        });
      }
    },
    toComment() {
        this.$refs.commentform.validate(async (valid) => {
        if (!valid) {
          // 校验不通过
          return false;
        }
        try {
          if (!this.commentform.comment_content) {
            return true;
          }
          const res = await ajax(`${config.appAddress}projects/${this.listData.project_id}/items/${this.selectItem.id}/comment`, 'POST', container.getHeader(), { 'comment': this.commentform.comment_content });
          this.$message({
            type: 'success',
            message: '评论成功',
          });
          console.log(this.listData);
          console.log(this.selectIndex);

          this.listData.list[this.selectIndex].comment_sum++;
          this.selectItem.comments.unshift({
            comment: this.commentform.comment_content,
            avatar: res.avatar,
            name: res.name,
            created_at: res.time,
          })
        } catch (e) {
          console.log(e);

          this.$message.error({
            message: '评论失败',
          });
        }
        return true;
      });
    },
    async changeStatus() {
      const item = this.selectItem
      await ajax(`${config.appAddress}projects/${this.listData.project_id}/items/${item.id}`, 'PUT', container.getHeader(), {status: item.status});
      this.$emit('changeStatus',this.listData.status, this.selectIndex, item.status);
      this.dialogFormVisible = false;
    },
    async updateItemDesc(item) {
      await ajax(`${config.appAddress}projects/${this.listData.project_id}/items/${item.id}`, 'PUT', container.getHeader(), {desc: item.desc});
    },
    async updateItemName(item) {
      if (!item.item_name) {
        return
      }
      if (item.is_add) {
        const res = await ajax(`${config.appAddress}projects/${this.listData.project_id}/items`, 'POST', container.getHeader(), {item_name: item.item_name, status: this.listData.status});
        item.id = res.item_id;
        item.list_sum = 0;
        item.list_finish = 0;
        item.comment_sum = 0;
        item.file_sum = 0;
        item.time = res.time;
        delete item.is_add;
        this.listData.list.push({
          is_add: true,
          item_name: '',
        });
      } else {
        await ajax(`${config.appAddress}projects/${this.listData.project_id}/items/${item.id}`, 'PUT', container.getHeader(), {item_name: item.item_name});
      }
    },
    showItemInput(e,index, item) {
      if (item.is_add) {
        item.item_name = '';
      }
      e.stopPropagation();
      this.setNowItem(this.listData.uniqueKey + index);
    },
    async showItemDetail(index) {
      const item = this.listData.list[index];
      console.log(item);

      const itemTime = new Date(item.time);
      this.selectTime=[];
      this.selectTime.push(itemTime);
      let end_time = itemTime;

      if (item.end_time) {
        if (item.end_time !== '0000-00-00 00:00:00') {
          end_time = new Date(item.end_time.substr(0, 10));
          this.dateShow = true;
        }
      } else {
        this.dateShow = false;
      }
      this.selectTime.push(end_time);

      if (!item.is_add) {
        this.dialogFormVisible = true;
        this.detailLoading = true;
        const itemDetail = await ajax(`${config.appAddress}projects/${this.listData.project_id}/items/${item.id}`, 'GET', container.getHeader());
        this.selectItem = itemDetail.item;
        this.setSelectItemCp(this.selectItem);
        this.selectIndex = index;
        this.detailLoading = false;
        this.finishStep = this.selectItem.finishList;
        this.setItemId(item.id);
        if (this.selectItem.lists.length) {
          this.stepShow = true;
          this.steps = this.selectItem.lists;
        } else {
          this.stepShow = false;
        }
        if (this.selectItem.files.length) {
          this.fileShow = true;
          this.fileData = this.selectItem.files;
        } else {
          this.fileShow = true;
        }
        this.comments = this.selectItem.comments;
      }
    },
  }
}
</script>

<style>
.comment-box{
  display: flex;
}
.comment-outletbox+.comment-outletbox{
  margin-top:15px;
}
.itemClose{
  padding: 13px 0 13px 6px;
}
.itemClose:hover{
  cursor: pointer;
  color:tomato;
}
.stopBox{
  display: flex;
  padding: 4px 0;
}
.funList{
  list-style: none;
}
.funList li + li {
  margin-top:5px;
}
.funListBox{
  margin-left: 135px;
  border-left: 1px solid #d7dcda;
  padding-left: 50px;
}
.dialogCommentBox {
  position: absolute;
  top: 50px;
  left: -50px;
  border-radius: 50%;
  overflow: hidden;
}
.commenAvatar{
  height: 40px !important;
  width: 40px !important;
  padding: 0 !important;
}
.commentBox .el-textarea__inner{
  height:100px;
}
.dialogHeaderDesc{
  font-size: 20px;padding: 5px 0 18px 0;font-weight: 600;
}
.dialogHeader+.dialogHeader{
  margin-top:30px;
}
.dialogHeader {
  display: flex;
}
.dialogHeader .el-image{
  height: 32px;
  padding-top: 4px;
}
.dialog-title{
  margin-left:12px;
}
.item-box-card .el-dialog{
  min-width: 940px;
}
.item-box-dialog .dialog-title:hover{
  cursor: pointer;
}
.item-box-dialog .itemListInput{
  width: 300px;
  margin-left:12px;
}
.item-box-dialog {
  min-width: 650px;
}
.item-info{
  font-size: 13px;
  text-align: right;
}
.item-info i+i{
  margin-left:10px;
}
.item-title .el-input{
  font-size: 20px;
}

.item-title{
    padding: 5px 0;
    font-size: 20px;
    font-weight: 100;
}

.item-box-card h2 {
  font-weight: 100;
  font-size: 16px;
  text-align: center;
  margin-bottom: 20px;
}
.item-list-box-card:hover{
  cursor: pointer;
}
.item-list-box-card .el-card__body{
  padding:5px 5px 5px 10px;
}
.item-list-box-card + .item-list-box-card{
  margin-top: 5px;
}
.newInputText{
  text-align: center;
  color: #2597ff;
  font-size: 2rem;
}
.inputText:hover{
  cursor: pointer;
}
</style>
