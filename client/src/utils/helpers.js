import $ from 'jquery';
import { config } from '../env';

/**
 * ajax请求传输
 * @param {*} url 请求url
 * @param {*} type 请求类型，默认是GET
 * @param {*} postData formdata
 * @param {*} isMultipart 是否是表单上传
 */
const ajax = function ajax(url, type = 'GET', headers = null, postData = null, isMultipart = false) {
  return new Promise((resolve, reject) => {
    const obj = {
      type,
      url,
      data: postData,
      dataType: 'json',
      xhrFields: {
        withCredentials: true,
      },
      success(resp) {
        resolve(resp);
      },
      error: (error) => {
        if (error.status === 403) {
          // alert('请先登录');
          window.location.href = `${config.appName}login`;
        }
        reject(error);
      },
    };
    if (headers) {
      obj.headers = headers;
    }
    if (isMultipart) {
      obj.processData = false;
      obj.contentType = false;
    }
    $.ajax(obj);
  });
};

const deepCopy = function deepCopy(obj) {
  if (typeof obj !== 'object') {
    return obj;
  }
  const newobj = {};
  Object.keys(obj).forEach((key) => {
    newobj[key] = deepCopy(obj[key]);
  });
  return newobj;
};
class Container {
  constructor() {
    this.container = window.localStorage;
  }
  set(key = '', value = '') {
    return this.container.setItem(key, value);
  }
  get(key = '') {
    return this.container.getItem(key);
  }
  setHeader(tokenType = '', accessToken = '') {
    return this.container.setItem('Authorization', `${tokenType} ${accessToken}`);
  }
  getHeader() {
    return {
      Authorization: this.container.getItem('Authorization'),
    };
  }
}

export {
  ajax,
  deepCopy,
  config,
  Container,
};
