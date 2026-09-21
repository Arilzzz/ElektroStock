import api from './api'

export const getBrands = () => {
  return api.get('/brands')
}

export const getBrand = (id) => {
  return api.get(`/brands/${id}`)
}

export const createBrand = (data) => {
  return api.post('/brands', data)
}

export const updateBrand = (id, data) => {
  return api.put(`/brands/${id}`, data)
}

export const deleteBrand = (id) => {
  return api.delete(`/brands/${id}`)
}
