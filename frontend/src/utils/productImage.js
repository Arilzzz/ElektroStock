/**
 * Utility untuk mendapatkan URL absolut dari gambar produk secara aman dan konsisten.
 * Mendukung full URL (http/https), Blob URL (pratinjau upload baru), dan path penyimpanan storage Laravel.
 */
export const getProductImageUrl = (imageOrProduct) => {
  if (!imageOrProduct) return null

  // Jika input berupa objek produk
  let path = imageOrProduct
  if (typeof imageOrProduct === 'object') {
    if (imageOrProduct.image_url) {
      return imageOrProduct.image_url
    }
    path = imageOrProduct.image
  }

  if (!path || typeof path !== 'string') return null

  // Jika sudah merupakan URL lengkap atau blob
  if (
    path.startsWith('http://') ||
    path.startsWith('https://') ||
    path.startsWith('blob:') ||
    path.startsWith('data:')
  ) {
    return path
  }

  // Base URL backend Laravel
  const apiBase = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'
  const backendBase = apiBase.replace(/\/api\/?$/, '')

  // Hapus awalan '/' atau '/storage/' ganda jika ada
  const cleanPath = path.replace(/^\/?(storage\/)?/, '')

  return `${backendBase}/storage/${cleanPath}`
}
