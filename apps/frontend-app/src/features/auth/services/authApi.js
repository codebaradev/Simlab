import api from "../../../services/api";

export const login = async (username, password) => {
  const response = await api.post('/login', { username, password });
  return response.data;
};

export const getMe = async () => {
  const response = await api.get('/me');
  return response.data;
};

export const logout = async () => {
  await api.post('/logout');
};