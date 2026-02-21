<?php

namespace App\Http\Controllers\Contrato\Documento;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Assinado extends Controller
{
    /**
     * Retorna o documento assinado para download.
     */
    public function __invoke(Request $request, string $id): BinaryFileResponse
    {
        $user = $request->user();
        $locador = $user->locador();

        $contrato = Contrato::findOrFail($id);

        // Verifica se o contrato pertence ao locador do usuário
        if ($user->isCliente() && $locador && $contrato->locador_id !== $locador->id) {
            abort(403, 'Você não tem permissão para acessar este contrato.');
        }

        // Verifica se há documento assinado
        if (! $contrato->temDocumentoAssinado()) {
            abort(404, 'Documento assinado não encontrado.');
        }

        $caminho = Storage::disk('local')->path($contrato->documento_assinado_path);

        if (! file_exists($caminho)) {
            abort(404, 'Documento assinado não encontrado.');
        }

        return response()->download(
            $caminho,
            "contrato-{$contrato->codigo}-assinado.pdf",
            [
                'Content-Type' => 'application/pdf',
            ]
        );
    }
}
