const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api/v1'

export async function apiRequest(endpoint, options = {}) {
  const token = localStorage.getItem('scrms_token')
  const locale = localStorage.getItem('scrms_locale') || 'am'

  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'Accept-Language': locale,
    ...(options.headers || {}),
  }

  if (token) {
    headers['Authorization'] = `Bearer ${token}`
  }

  const url = `${API_BASE_URL}${endpoint}`

  try {
    const response = await fetch(url, {
      ...options,
      headers,
    })

    const data = await response.json().catch(() => null)

    if (!response.ok) {
      const errorMessage = data?.message || `HTTP ${response.status}: Failed to fetch`
      const error = new Error(errorMessage)
      error.status = response.status
      error.data = data
      throw error
    }

    return data
  } catch (err) {
    console.error(`API Error [${endpoint}]:`, err)
    throw err
  }
}
