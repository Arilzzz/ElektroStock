import api from './api'

export const getStockReport = (params = {}) => {
  return api.get('/reports/stock', { params })
}

export const getTransactionReport = (params = {}) => {
  return api.get('/reports/transactions', { params })
}

export const getProfitReport = (params = {}) => {
  return api.get('/reports/profit', { params })
}
