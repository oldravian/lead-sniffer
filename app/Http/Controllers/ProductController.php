<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ProductController extends Controller
{
    public function index(): InertiaResponse
    {
        return Inertia::render('products/Index', [
            'appName' => config('app.name'),
            'products' => Product::query()->latest()->get(),
        ]);
    }

    public function show(int $id): InertiaResponse
    {
        return Inertia::render('products/Show', [
            'product' => Product::query()->findOrFail($id),
        ]);
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        $product = Product::query()->create($data);

        return response()->json($product, 201);
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        $this->ensureUserOwnsProduct($request, $product);

        $product->update($request->validated());

        return response()->json($product);
    }

    public function destroy(Request $request, Product $product): Response
    {
        $this->ensureUserOwnsProduct($request, $product);

        $product->delete();

        return response()->noContent();
    }

    protected function ensureUserOwnsProduct(Request $request, Product $product): void
    {
        if ($product->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
