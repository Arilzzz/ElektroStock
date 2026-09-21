/**
 * Format number to Indonesian Rupiah (IDR) currency format
 * @param {number|string} value
 * @returns {string} e.g. "Rp 4.500.000"
 */
export function formatCurrency(value) {
  if (value === null || value === undefined || isNaN(Number(value))) {
    return 'Rp 0'
  }

  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(Number(value))
}

export default formatCurrency
