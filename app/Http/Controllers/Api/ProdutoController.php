<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProdutoController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::query()
            ->active()
            ->with('sizes')
            ->orderBy('id');

        if ($colecao = $request->query('colecao')) {
            $query->where('collection', $colecao);
        }

        if ($familia = $request->query('familia')) {
            $query->where('family', $familia);
        }

        if ($request->boolean('destaque')) {
            $query->where('featured', true);
        }

        return ProductResource::collection($query->get());
    }

    public function show(Product $produto): ProductResource
    {
        abort_unless($produto->active, 404);

        return new ProductResource($produto->load('sizes'));
    }
}
