import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.timeout = 15000;

const apiToken = localStorage.getItem('siperdok_token');

if (apiToken) {
  window.axios.defaults.headers.common.Authorization = `Bearer ${apiToken}`;
}

window.axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('siperdok_token');
      delete window.axios.defaults.headers.common.Authorization;

      if (!['/login', '/register'].includes(window.location.pathname)) {
        window.location.assign('/login');
      }
    }

    return Promise.reject(error);
  },
);
