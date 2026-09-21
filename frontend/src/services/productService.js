import api from './api'
export { getCategories } from './categoryService'
export { getBrands } from './brandService'

export const getProducts = (params = {}) => {
  return api.get('/products', { params })
}

export const getProduct = (id) => {
  return api.get(`/products/${id}`)
}

export const createProduct = (data) => {
  const isFormData = data instanceof FormData
  return api.post('/products', data, {
    headers: isFormData ? { 'Content-Type': 'multipart/form-data' } : undefined,
  })
}

export const updateProduct = (id, data) => {
  const isFormData = data instanceof FormData
  if (isFormData) {
    if (!data.has('_method')) {
      data.append('_method', 'PUT')
    }
    return api.post(`/products/${id}`, data, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  }
  return api.put(`/products/${id}`, data)
}

export const deleteProduct = (id) => {
  return api.delete(`/products/${id}`)
}