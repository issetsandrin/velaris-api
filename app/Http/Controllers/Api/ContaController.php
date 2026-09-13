<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContatoRequest;
use App\Http\Requests\EnderecoRequest;
use App\Http\Resources\AddressResource;
use App\Http\Resources\ContactResource;
use App\Http\Resources\OrderResource;
use App\Models\Address;
use App\Models\Contact;
use App\Services\ContatoService;
use App\Services\EnderecoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ContaController extends Controller
{
    public function __construct(
        private readonly EnderecoService $enderecos,
        private readonly ContatoService $contatos,
    ) {}

    public function enderecos(Request $request): AnonymousResourceCollection
    {
        return AddressResource::collection($request->user()->addresses);
    }

    public function criarEndereco(EnderecoRequest $request): JsonResponse
    {
        $address = $this->enderecos->criar($request->user(), $request->validated());

        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    public function atualizarEndereco(EnderecoRequest $request, Address $endereco): AddressResource
    {
        abort_unless($endereco->user_id === $request->user()->id, 404);

        return new AddressResource($this->enderecos->atualizar($endereco, $request->validated()));
    }

    public function removerEndereco(Request $request, Address $endereco): Response
    {
        abort_unless($endereco->user_id === $request->user()->id, 404);

        $this->enderecos->remover($endereco);

        return response()->noContent();
    }

    public function contatos(Request $request): AnonymousResourceCollection
    {
        return ContactResource::collection($request->user()->contacts);
    }

    public function criarContato(ContatoRequest $request): JsonResponse
    {
        $contact = $this->contatos->criar($request->user(), $request->validated());

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function atualizarContato(ContatoRequest $request, Contact $contato): ContactResource
    {
        abort_unless($contato->user_id === $request->user()->id, 404);

        return new ContactResource($this->contatos->atualizar($contato, $request->validated()));
    }

    public function removerContato(Request $request, Contact $contato): Response
    {
        abort_unless($contato->user_id === $request->user()->id, 404);

        $this->contatos->remover($contato);

        return response()->noContent();
    }

    public function pedidos(Request $request): AnonymousResourceCollection
    {
        return OrderResource::collection($request->user()->orders()->with('items')->get());
    }
}
