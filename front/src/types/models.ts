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
export type ContratoStatus = 'rascunho' | 'aguardando_pagamento' | 'ativo' | 'finalizado' | 'cancelado'

// Tipo de cobranca (enum no backend)
export type TipoCobranca = 'antecipado_stripe' | 'antecipado_pix' | 'recorrente_stripe' | 'recorrente_manual' | 'sem_cobranca'

// Status do pagamento (enum no backend)
export type StatusPagamento = 'pendente' | 'pago' | 'atrasado' | 'cancelado' | 'reembolsado'

// Origem do pagamento (enum no backend)
export type OrigemPagamento = 'stripe' | 'pix' | 'manual'

export interface Contrato {
  id: string // UUID
  codigo: string
  data_inicio: string
  data_termino: string
  valor_total: number
  status: ContratoStatus
  tipo_cobranca: TipoCobranca
  observacoes: string | null
  documento_assinado_path: string | null
  locador_id: string
  locatario_id: string
  stripe_subscription_id: string | null
  stripe_customer_id: string | null
  stripe_checkout_id: string | null
  dia_vencimento: number | null
  created_at: string
  updated_at: string
  // Relacionamentos opcionais
  locador?: Pessoa
  locatario?: Pessoa
  itens?: ContratoItem[]
  pagamentos?: Pagamento[]
}

export interface Pagamento {
  id: string // UUID
  valor: string // Decimal formatado
  desconto_comercial: string // Decimal formatado
  valor_final: number // Calculado: valor - desconto_comercial
  data_vencimento: string
  data_pagamento: string | null
  status: StatusPagamento
  status_label: string
  origem: OrigemPagamento
  origem_label: string
  stripe_payment_id: string | null
  observacoes: string | null
  contrato_id: string
  created_at: string
  updated_at: string
}

export interface PagamentoResumo {
  total_contrato: string
  total_pago: string
  total_pendente: string
  total_atrasado: string
  qtd_pagamentos: number
  qtd_pagos: number
  qtd_pendentes: number
  qtd_atrasados: number
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

// Tipo de aditivo (enum no backend)
export type TipoAditivo = 'prorrogacao' | 'acrescimo' | 'reducao' | 'alteracao_valor'

// Status do aditivo (enum no backend)
export type StatusAditivo = 'rascunho' | 'ativo' | 'cancelado'

export interface ContratoAditivo {
  id: string // UUID
  tipo: TipoAditivo
  descricao: string | null
  data_vigencia: string
  valor_ajuste: number | null
  nova_data_termino: string | null
  conceder_reembolso: boolean
  status: StatusAditivo
  stripe_price_anterior_id: string | null
  stripe_invoice_item_id: string | null
  data_termino_anterior: string | null
  valor_total_anterior: number | null
  contrato_id: string
  created_at: string
  updated_at: string
  // Relacionamentos opcionais
  itens?: ContratoAditivoItem[]
}

export interface ContratoAditivoItem {
  id: string // UUID
  quantidade_alterada: number
  valor_unitario: number | null
  contrato_aditivo_id: string
  tipo_ativo_id: string
  created_at: string
  updated_at: string
  // Relacionamentos opcionais
  tipo_ativo?: TipoAtivo
}
