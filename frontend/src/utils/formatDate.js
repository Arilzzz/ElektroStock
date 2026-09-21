/**
 * Format datetime string to Indonesian readable date/time
 * @param {string|Date} dateValue
 * @param {boolean} includeTime
 * @returns {string} e.g. "21 Sep 2026, 10:00"
 */
export function formatDate(dateValue, includeTime = false) {
  if (!dateValue) return '-'

  const date = new Date(dateValue)
  if (isNaN(date.getTime())) return '-'

  const options = {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    ...(includeTime
      ? {
          hour: '2-digit',
          minute: '2-digit',
        }
      : {}),
  }

  return new Intl.DateTimeFormat('id-ID', options).format(date)
}

export default formatDate
