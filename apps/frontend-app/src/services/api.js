import axios from 'axios';

const APP_URL = 'http://localhost:8000/api'

const api = axios.create({
  baseURL: APP_URL, 
  withCredentials: false, 
});

export default api;