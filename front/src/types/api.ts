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

// Dashboard
export interface DashboardResponse {
  data: DashboardData
}

export interface DashboardData {
  financeiro: DashboardFinanceiro
  operacional: DashboardOperacional
  alertas: DashboardAlerta[]
}

export interface DashboardFinanceiro {
  receita_total: number
  contratos_ativos: number
  receita_media_mensal: number
  contratos_a_vencer: ContratoAVencer[]
}

export interface ContratoAVencer {
  id: string
  codigo: string
  data_termino: string
  dias_restantes: number
  valor_total: number
  locatario: string | null
}

export interface DashboardOperacional {
  estoque_total: number
  estoque_disponivel: number
  estoque_alocado: number
  taxa_ocupacao: number
  lotes_por_status: {
    disponivel: number
    indisponivel: number
    esgotado: number
  }
  top_ativos: TopAtivo[]
}

export interface TopAtivo {
  id: string
  nome: string
  quantidade: number
}

export interface DashboardAlerta {
  tipo: 'warning' | 'info' | 'destructive'
  titulo: string
  mensagem: string
  icone: string
  detalhes?: unknown
}
