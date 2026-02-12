// Tipos para respostas da API
import type { User, Pessoa } from './models'

// Resposta generica da API
export interface ApiResponse<T> {
  data: T
  message?: string
}

// Resposta paginada (padrao Laravel)
export interface PaginatedResponse<T> {
  data: T[]
  meta: PaginationMeta
  links: PaginationLinks
}

export interface PaginationMeta {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number | null
  to: number | null
  path: string
}

export interface PaginationLinks {
  first: string | null
  last: string | null
  prev: string | null
  next: string | null
}

// Erro da API
export interface ApiError {
  message: string
  errors?: Record<string, string[]>
}

// Resposta do login
export interface LoginResponse {
  data: {
    token: string
    user: User
  }
}

// Resposta do /auth/me
export interface MeResponse {
  data: {
    user: User
    locador: Pessoa | null
  }
}

// Parametros para requisicoes paginadas
export interface PaginationParams {
  page?: number
  per_page?: number
  search?: string
  sort_by?: string
  sort_dir?: 'asc' | 'desc'
}

// Resposta de sucesso simples
export interface SuccessResponse {
  message: string
}

// Resposta de delete
export interface DeleteResponse {
  message: string
}
