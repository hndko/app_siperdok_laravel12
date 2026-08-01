import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const apiToken = localStorage.getItem('siperdok_token');

if (apiToken) {
  window.axios.defaults.headers.common.Authorization = `Bearer ${apiToken}`;
}
