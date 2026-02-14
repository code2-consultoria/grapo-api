<?php

namespace App\Rules;

use App\Enums\TipoPessoa;
use App\Models\Documento;
use App\Models\Pessoa;
use App\Services\Documentos\DocumentoFactory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;

/**
 * Valida se documento é CPF/CNPJ válido e único conforme regras de negócio.
 *
 * - Locadores: documento não pode existir em outro locador
 * - Locatários: documento não pode existir em outro locatário do mesmo locador
 */
class DocumentoUnico implements ValidationRule
{
    public function __construct(
        private TipoPessoa $tipoPessoa,
        private ?Pessoa $locador = null,
        private ?string $pessoaIdIgnorar = null
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            $fail('O documento é obrigatório.');

            return;
        }

        // Valida formato e dígitos verificadores
        try {
            $tipo = DocumentoFactory::detectarTipo($value);
        } catch (InvalidArgumentException $e) {
            $fail($e->getMessage());

            return;
        }

        if (! DocumentoFactory::validarAuto($value)) {
            $mensagem = DocumentoFactory::mensagemErro($tipo);
            $fail($mensagem);

            return;
        }

        // Limpa o número para buscar no banco
        $numeroLimpo = DocumentoFactory::limpar($tipo, $value);

        // Verifica unicidade
        $query = Documento::where('numero', $numeroLimpo)
            ->where('tipo', $tipo->value);

        // Ignora a própria pessoa em caso de edição
        if ($this->pessoaIdIgnorar) {
            $query->where('pessoa_id', '!=', $this->pessoaIdIgnorar);
        }

        // Aplica regra de unicidade conforme tipo de pessoa
        switch ($this->tipoPessoa) {
            case TipoPessoa::Locador:
                // Locadores: documento único entre todos os locadores
                $query->whereHas('pessoa', function ($q) {
                    $q->where('tipo', TipoPessoa::Locador->value);
                });
                $mensagemDuplicado = 'Já existe um locador com este documento.';
                break;

            case TipoPessoa::Locatario:
            case TipoPessoa::ResponsavelFinanceiro:
            case TipoPessoa::ResponsavelAdministrativo:
                // Locatários/Responsáveis: documento único dentro do mesmo locador
                if ($this->locador) {
                    $query->whereHas('pessoa', function ($q) {
                        $q->where('locador_id', $this->locador->id);
                    });
                }
                $mensagemDuplicado = 'Já existe uma pessoa com este documento vinculada a este locador.';
                break;
        }

        if ($query->exists()) {
            $fail($mensagemDuplicado);
        }
    }
}
