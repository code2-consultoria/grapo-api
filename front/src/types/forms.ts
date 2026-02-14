// Tipos para formularios
import type { TipoPessoa, LoteStatus, ContratoStatus, TipoCobranca, OrigemPagamento } from './models'

// Formulario de login
export interface LoginForm {
  email: string
  password: string
}

// Formulario de registro
export interface RegisterForm {
  name: string
  email: string
  password: string
  password_confirmation: string
}

// Formulario de pessoa (locatario, responsavel, etc)
export interface PessoaForm {
  tipo: TipoPessoa
  nome: string
  documento: string
  email: string
  telefone: string
  endereco: string
  documentos?: DocumentoForm[]
}

// Formulario de documento
export interface DocumentoForm {
  tipo: 'cpf' | 'cnpj' | 'rg' | 'cnh' | 'outro'
  numero: string
}

// Formulario de tipo de ativo
export interface TipoAtivoForm {
  nome: string
  descricao: string
  unidade_medida: string
  valor_mensal_sugerido: number | string
}

// Formulario de lote
export interface LoteForm {
  codigo: string
  tipo_ativo_id: string
  quantidade_total: number | string
  fornecedor?: string
  valor_total?: number | string
  valor_frete?: number | string
  forma_pagamento?: string
  nf?: string
  data_aquisicao?: string
  status?: LoteStatus
}

// Formulario de contrato
export interface ContratoForm {
  locatario_id: string
  data_inicio: string
  data_termino: string
  observacoes?: string
  status?: ContratoStatus
  tipo_cobranca?: TipoCobranca
}

// Formulario de pagamento manual
export interface PagamentoForm {
  valor: number | string
  data_vencimento: string
  data_pagamento?: string
  origem: OrigemPagamento
  observacoes?: string
}

// Periodo de aluguel
export type PeriodoAluguel = 'diaria' | 'mensal'

// Formulario de item de contrato
export interface ContratoItemForm {
  tipo_ativo_id: string
  quantidade: number | string
  valor_unitario: number | string
  periodo_aluguel: PeriodoAluguel
}

// Tipo generico para erros de validacao
export type FormErrors<T> = Partial<Record<keyof T, string>>

// Estado generico de formulario
export interface FormState<T> {
  values: T
  errors: FormErrors<T>
  isSubmitting: boolean
  isDirty: boolean
}
