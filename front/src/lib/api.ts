// Cliente HTTP baseado em fetch - v2
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

interface RequestConfig extends RequestInit {
  params?: Record<string, string | number | boolean | undefined>
}

// Funcao para obter token do localStorage
function getToken(): string | null {
  return localStorage.getItem('auth_token')
}

// Funcao para setar token no localStorage
export function setToken(token: string): void {
  localStorage.setItem('auth_token', token)
}

// Funcao para remover token do localStorage
export function removeToken(): void {
  localStorage.removeItem('auth_token')
}

// Funcao para verificar se ha token
export function hasToken(): boolean {
  return !!getToken()
}

// Construir URL com query params
function buildUrl(endpoint: string, params?: RequestConfig['params']): string {
  const url = new URL(`${API_BASE_URL}${endpoint}`)

  if (params) {
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        url.searchParams.append(key, String(value))
      }
    })
  }

  return url.toString()
}

// Funcao base para requisicoes
async function request<T>(endpoint: string, config: RequestConfig = {}): Promise<T> {
  const { params, ...fetchConfig } = config

  const token = getToken()

  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    ...config.headers,
  }

  if (token) {
    ;(headers as Record<string, string>)['Authorization'] = `Bearer ${token}`
  }

  const response = await fetch(buildUrl(endpoint, params), {
    ...fetchConfig,
    headers,
  })

  // Tratar resposta vazia (204 No Content)
  if (response.status === 204) {
    return {} as T
  }

  const data = await response.json()

  if (!response.ok) {
    // Passa todos os campos da resposta de erro (message, errors, error_type, etc)
    const error = {
      ...data,
      message: data.message || 'Erro na requisicao',
    }
    throw error
  }

  return data
}

// Funcao para download de arquivo (blob)
async function requestBlob(endpoint: string, params?: RequestConfig['params']): Promise<Blob> {
  const token = getToken()

  const headers: HeadersInit = {
    Accept: '*/*',
  }

  if (token) {
    ;(headers as Record<string, string>)['Authorization'] = `Bearer ${token}`
  }

  const response = await fetch(buildUrl(endpoint, params), {
    method: 'GET',
    headers,
  })

  if (!response.ok) {
    const data = await response.json()
    const error = {
      ...data,
      message: data.message || 'Erro na requisicao',
    }
    throw error
  }

  return response.blob()
}

// Funcao para upload de FormData
async function requestFormData<T>(endpoint: string, formData: FormData): Promise<T> {
  const token = getToken()

  const headers: HeadersInit = {
    Accept: 'application/json',
    // Nao define Content-Type para FormData - o browser faz automaticamente com boundary
  }

  if (token) {
    ;(headers as Record<string, string>)['Authorization'] = `Bearer ${token}`
  }

  const response = await fetch(`${API_BASE_URL}${endpoint}`, {
    method: 'POST',
    headers,
    body: formData,
  })

  if (response.status === 204) {
    return {} as T
  }

  const data = await response.json()

  if (!response.ok) {
    const error = {
      ...data,
      message: data.message || 'Erro na requisicao',
    }
    throw error
  }

  return data
}

// Metodos HTTP
export const api = {
  get: <T>(endpoint: string, params?: RequestConfig['params']) =>
    request<T>(endpoint, { method: 'GET', params }),

  post: <T>(endpoint: string, data?: unknown) =>
    request<T>(endpoint, {
      method: 'POST',
      body: data ? JSON.stringify(data) : undefined,
    }),

  put: <T>(endpoint: string, data?: unknown) =>
    request<T>(endpoint, {
      method: 'PUT',
      body: data ? JSON.stringify(data) : undefined,
    }),

  patch: <T>(endpoint: string, data?: unknown) =>
    request<T>(endpoint, {
      method: 'PATCH',
      body: data ? JSON.stringify(data) : undefined,
    }),

  delete: <T>(endpoint: string) => request<T>(endpoint, { method: 'DELETE' }),

  // Download de arquivo (retorna Blob)
  getBlob: (endpoint: string, params?: RequestConfig['params']) =>
    requestBlob(endpoint, params),

  // Upload de arquivo via FormData
  postFormData: <T>(endpoint: string, formData: FormData) =>
    requestFormData<T>(endpoint, formData),
}

export default api
