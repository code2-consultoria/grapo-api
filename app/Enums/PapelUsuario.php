<?php

namespace App\Enums;

enum PapelUsuario: string
{
    case Admin = 'admin';
    case Cliente = 'cliente';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Cliente => 'Cliente',
        };
    }

    public function descricao(): string
    {
        return match ($this) {
            self::Admin => 'Colaborador da Grapo com acesso a todos os tenants',
            self::Cliente => 'Dono/operador do tenant (locador)',
        };
    }
}
