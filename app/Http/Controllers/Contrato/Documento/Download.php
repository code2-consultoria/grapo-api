<?php

namespace App\Http\Controllers\Contrato\Documento;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Services\Contrato\GeradorDocumento;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Download extends Controller
{
    /**
     * Gera e retorna o documento DOCX do contrato.
     */
    public function __invoke(Request $request, string $id): BinaryFileResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $query = Contrato::with(['locador.documentos', 'locatario.documentos', 'itens.tipoAtivo']);

        if ($user->isCliente() && $locador) {
            $query->where('locador_id', $locador->id);
        }

        $contrato = $query->findOrFail($id);

        // Verifica se o contrato pertence ao locador do usuário
        if ($user->isCliente() && $locador && $contrato->locador_id !== $locador->id) {
            abort(403, 'Você não tem permissão para acessar este contrato.');
        }

        // Verifica se tem locador e locatário definidos
        if (! $contrato->locador_id || ! $contrato->locatario_id) {
            abort(422, 'Contrato deve ter locador e locatário definidos para gerar documento.');
        }

        $gerador = new GeradorDocumento($contrato);
        $caminhoArquivo = $gerador->gerar();

        return response()->download(
            $caminhoArquivo,
            "contrato-{$contrato->codigo}.docx",
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]
        )->deleteFileAfterSend(true);
    }
}
