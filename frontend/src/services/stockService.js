import api from './api'

export const stockIn = (data) => {
  return api.post('/stock/in', data)
}

export const stockOut = (data) => {
  return api.post('/stock/out', data)
}

export const getStockHistory = (params = {}) => {
  return api.get('/stock/history', { params })
}

export const getStockTransaction = (id) => {
  return api.get(`/stock/history/${id}`)
}
