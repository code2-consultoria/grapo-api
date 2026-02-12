<?php

namespace App\Actions\Pessoa;

use App\Contracts\Command;
use App\Enums\TipoDocumento;
use App\Exceptions\DocumentoInvalidoException;
use App\Models\Documento;
use App\Models\Pessoa;
use App\Services\Documentos\DocumentoFactory;

/**
 * Adiciona um documento a uma pessoa.
 */
class AdicionarDocumento implements Command
{
    private Documento $documento;

    public function __construct(
        private Pessoa $pessoa,
        private TipoDocumento $tipo,
        private string $numero
    ) {}

    /**
     * Executa a adição do documento.
     *
     * @throws DocumentoInvalidoException
     */
    public function handle(): void
    {
        // Valida o documento
        if (! DocumentoFactory::validar($this->tipo, $this->numero)) {
            throw new DocumentoInvalidoException(
                DocumentoFactory::mensagemErro($this->tipo)
            );
        }

        // Limpa a formatação do número
        $numeroLimpo = DocumentoFactory::limpar($this->tipo, $this->numero);

        $this->documento = new Documento([
            'tipo' => $this->tipo,
            'numero' => $numeroLimpo,
        ]);

        $this->documento->pessoa()->associate($this->pessoa);
        $this->documento->save();
    }

    /**
     * Retorna o documento criado.
     */
    public function getDocumento(): Documento
    {
        return $this->documento;
    }
}
