<?php

namespace App\Http\Controllers\Contrato\Documento;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class Upload extends Controller
{
    /**
     * Faz upload do documento assinado (PDF).
     */
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'documento' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10MB
        ], [
            'documento.required' => 'O documento é obrigatório.',
            'documento.mimes' => 'O documento deve ser um arquivo PDF.',
            'documento.max' => 'O documento deve ter no máximo 10MB.',
        ]);

        $user = $request->user();
        $locador = $user->locador();

        $contrato = Contrato::findOrFail($id);

        // Verifica se o contrato pertence ao locador do usuário
        if ($user->isCliente() && $locador && $contrato->locador_id !== $locador->id) {
            abort(403, 'Você não tem permissão para acessar este contrato.');
        }

        // Remove documento anterior se existir
        if ($contrato->documento_assinado_path) {
            Storage::disk('local')->delete($contrato->documento_assinado_path);
        }

        // Faz upload do novo documento
        $arquivo = $request->file('documento');
        $nomeArquivo = "contrato-{$contrato->codigo}-assinado-".time().'.pdf';
        $caminho = $arquivo->storeAs('contratos/assinados', $nomeArquivo, 'local');

        // Atualiza o contrato
        $contrato->documento_assinado_path = $caminho;
        $contrato->save();

        return response()->json([
            'message' => 'Documento enviado com sucesso.',
            'data' => [
                'documento_assinado_path' => $caminho,
                'documento_assinado_url' => "/storage/{$caminho}",
            ],
        ]);
    }
}
