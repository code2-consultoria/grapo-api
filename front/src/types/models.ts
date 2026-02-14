// Tipos base correspondendo aos models do Laravel

export interface User {
  id: number
  name: string
  email: string
  papel: 'admin' | 'cliente'
  ativo: boolean
  email_verified_at: string | null
  created_at: string
  updated_at: string
}

// Tipos de pessoa (enum no backend)
export type TipoPessoa = 'locador' | 'locatario' | 'responsavel_fin' | 'responsavel_adm'

export interface Pessoa {
  id: string // UUID
  tipo: TipoPessoa
  nome: string
  email: string | null
  telefone: string | null
  endereco: string | null
  ativo: boolean
  locador_id: string | null
  tipo_pessoa: 'PF' | 'PJ' | null
  created_at: string
  updated_at: string
  // Relacionamentos opcionais
  locador?: Pessoa
  documentos?: Documento[]
  contratos_como_locatario?: Contrato[]
}

export interface Documento {
  id: string // UUID
  tipo: 'cpf' | 'cnpj' | 'rg' | 'cnh' | 'outro'
  numero: string
  pessoa_id: string
  created_at: string
  updated_at: string
}

export interface TipoAtivo {
  id: string // UUID
  nome: string
  descricao: string | null
  unidade_medida: string
  valor_mensal_sugerido: number
  valor_diaria_sugerido: number // Accessor calculado: (valor_mensal * 1.10) / 30
  locador_id: string
  created_at: string
  updated_at: string
}

// Status do lote (enum no backend)
export type LoteStatus = 'disponivel' | 'indisponivel' | 'esgotado'

export interface Lote {
  id: string // UUID
  codigo: string
  quantidade_total: number
  quantidade_disponivel: number
  custo_aquisicao: number | null
  custo_unitario: number | null // Accessor: (valor_total + valor_frete) / quantidade_total
  data_aquisicao: string | null
  status: LoteStatus
  fornecedor: string | null
  valor_total: number | null
  valor_frete: number | null
  forma_pagamento: string | null
  nf: string | null
  tipo_ativo_id: string
  locador_id: string
  created_at: string
  updated_at: string
  // Relacionamentos opcionais
  tipo_ativo?: TipoAtivo
}

// Status do contrato (enum no backend)
export type ContratoStatus = 'rascunho' | 'ativo' | 'finalizado' | 'cancelado'

export interface Contrato {
  id: string // UUID
  codigo: string
  data_inicio: string
  data_termino: string
  valor_total: number
  status: ContratoStatus
  observacoes: string | null
  locador_id: string
  locatario_id: string
  created_at: string
  updated_at: string
  // Relacionamentos opcionais
  locador?: Pessoa
  locatario?: Pessoa
  itens?: ContratoItem[]
}

// Periodo de aluguel
export type PeriodoAluguel = 'diaria' | 'mensal'

export interface ContratoItem {
  id: string // UUID
  quantidade: number
  valor_unitario: number
  periodo_aluguel: PeriodoAluguel
  valor_total_item: number
  contrato_id: string
  tipo_ativo_id: string
  created_at: string
  updated_at: string
  // Relacionamentos opcionais
  tipo_ativo?: TipoAtivo
}

// Vinculo entre User e Locador
export interface VinculoTime {
  id: string // UUID
  user_id: number
  locador_id: string
  created_at: string
  updated_at: string
}
