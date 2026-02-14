<?php

namespace App\Enums;

enum TipoAditivo: string
{
    case Prorrogacao = 'prorrogacao';
    case Acrescimo = 'acrescimo';
    case Reducao = 'reducao';
    case AlteracaoValor = 'alteracao_valor';

    public function label(): string
    {
        return match ($this) {
            self::Prorrogacao => 'Prorrogação',
            self::Acrescimo => 'Acréscimo',
            self::Reducao => 'Redução',
            self::AlteracaoValor => 'Alteração de Valor',
        };
    }

    public function descricao(): string
    {
        return match ($this) {
            self::Prorrogacao => 'Estende o prazo de vigência do contrato',
            self::Acrescimo => 'Adiciona novos itens ao contrato',
            self::Reducao => 'Remove itens do contrato',
            self::AlteracaoValor => 'Altera o valor total do contrato',
        };
    }

    public function alteraItens(): bool
    {
        return $this === self::Acrescimo || $this === self::Reducao;
    }

    public function alteraPrazo(): bool
    {
        return $this === self::Prorrogacao;
    }

    public function alteraValor(): bool
    {
        return $this === self::AlteracaoValor;
    }
}
