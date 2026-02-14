<?php

namespace App\Http\Controllers\Pessoa;

use App\Actions\Pessoa\AdicionarDocumento;
use App\Actions\Pessoa\Criar;
use App\Enums\TipoDocumento;
use App\Enums\TipoPessoa;
use App\Http\Controllers\Controller;
use App\Rules\DocumentoUnico;
use App\Services\Documentos\DocumentoFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Store extends Controller
{
    /**
     * Cria uma nova pessoa.
     */
    public function __invoke(Request $request): JsonResponse
    {
        // Tipo pode vir do body ou do default da rota
        $tipoDefault = $request->route('tipo');
        $tipo = $request->input('tipo', $tipoDefault);
        $tipoPessoa = TipoPessoa::tryFrom($tipo);

        // Obtém o locador do usuário autenticado (para locatários e responsáveis)
        $locador = $request->user()?->locador();

        $rules = [
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'endereco' => ['nullable', 'string', 'max:500'],
            // Campo documento (string única) - obrigatório
            'documento' => [
                'required_without:documentos',
                'nullable',
                'string',
                $tipoPessoa ? new DocumentoUnico($tipoPessoa, $locador) : 'nullable',
            ],
            // Campo documentos (array) - mantido para compatibilidade
            'documentos' => ['nullable', 'array'],
            'documentos.*.tipo' => ['required_with:documentos', 'string', 'in:cpf,cnpj,rg,cnh,passaporte,inscricao_municipal,inscricao_estadual,cad_unico'],
            'documentos.*.numero' => ['required_with:documentos', 'string', 'max:50'],
        ];

        // Se não tem tipo default na rota, exige no body
        if (! $tipoDefault) {
            $rules['tipo'] = ['required', 'string', 'in:locador,locatario,responsavel_fin,responsavel_adm'];
        }

        $validated = $request->validate($rules);

        // Usa tipo do body ou da rota
        $tipo = $validated['tipo'] ?? $tipoDefault;

        $pessoa = DB::transaction(function () use ($request, $validated, $tipo, $locador) {
            $tipoPessoa = TipoPessoa::from($tipo);

            $criarPessoa = new Criar(
                tipo: $tipoPessoa,
                nome: $validated['nome'],
                email: $validated['email'] ?? null,
                telefone: $validated['telefone'] ?? null,
                endereco: $validated['endereco'] ?? null,
                locador: $locador
            );
            $criarPessoa->handle();
            $pessoa = $criarPessoa->getPessoa();

            // Adiciona documento único (novo campo)
            if (! empty($validated['documento'])) {
                $docInfo = DocumentoFactory::processarAuto($validated['documento']);
                $adicionarDocumento = new AdicionarDocumento(
                    pessoa: $pessoa,
                    tipo: $docInfo['tipo'],
                    numero: $docInfo['numero']
                );
                $adicionarDocumento->handle();
            }

            // Adiciona documentos do array (compatibilidade)
            if (! empty($validated['documentos'])) {
                foreach ($validated['documentos'] as $docData) {
                    $tipoDocumento = TipoDocumento::from($docData['tipo']);
                    $adicionarDocumento = new AdicionarDocumento(
                        pessoa: $pessoa,
                        tipo: $tipoDocumento,
                        numero: $docData['numero']
                    );
                    $adicionarDocumento->handle();
                }
            }

            return $pessoa;
        });

        return response()->json([
            'data' => $pessoa->load('documentos'),
            'message' => 'Pessoa criada com sucesso.',
        ], 201);
    }
}
