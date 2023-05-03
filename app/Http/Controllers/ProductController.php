<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductAllResource;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\ProductServices;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    public function index(ProductRequest $request)
    {
         $query = QueryBuilder::for(Product::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('title'),
                AllowedFilter::partial('sku'),
                AllowedFilter::callback('size', function ($query, $size) {
                    if (is_array($size)) {
                        foreach ($size as $s) {
                            $query->whereHas('positions', function ($q) use ($size) {
                                $q->whereIn('size', $size);
                            });
                        }
                    } else {
                        $sizes = explode(',', $size);
                        $query->whereHas('positions', function ($q) use ($sizes) {
                            $q->whereIn('size', $sizes);
                        });
                    }
                })
            ])->allowedSorts([
                'id', 'title', 'sku', 'size', AllowedSort::field('price', 'positions.price'),
                AllowedSort::field('size', 'positions.size'),
            ]);
        if ($request->has('search') && $request->has('searchBy')) {
            $search = $request->input('search');
            $searchBy = $request->input('searchBy');
            $query->orwhere($searchBy, 'like', "%{$search}%");
        }
        return  $this->productRepository->getProducts($query);
    }
    public function show(Product $product)
    {
        return new ProductAllResource($product->load(['brand', 'categories', 'positions']));
    }
    public function store(ProductRequest $request): JsonResponse
    {
        try {
            // Create the product
            $data = [
                'title' => $request->input('title'),
                'sku' => $request->input('sku'),
                'slug' => $request->input('slug'),
                'brand_id' => $request->input('brand_id')
            ];
            $product = $this->productRepository->createProduct($data);
            // Parse category path
            $categories = $request->input('categories');
            if ($categories) {
                foreach ($categories as $categoryPath) {
                    $categoryIds = (new ProductServices)->getCategoryIdsFromPath($categoryPath);
                    $product->categories()->attach($categoryIds['category_id'], [
                        'parent_category_id' => $categoryIds['parent_category_id'],
                        'category_path' => $categoryPath,    'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                }
            }
            //Create product positions
            $positions = $request->input('positions');
            if ($positions) {
                foreach ($positions as $positionData) {
                    $product->positions()->create($positionData);
                }
            }
            return response()->json(['message' => 'Product created successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while creating the product.'], 500);
        }
    }

    public function update(ProductRequest $request, $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }
        try {
            $data = $request->only(['title', 'slug', 'sku', 'brand_id']);
            // Update the product
            $this->productRepository->updateProduct($product, $data);
            // Parse category path
            $categories = $request->input('categories');
            $product->categories()->detach();
            foreach ($categories as $categoryPath) {
                $categoryIds = (new ProductServices)->getCategoryIdsFromPath($categoryPath);
                if ($categoryIds['category_id']) {
                    $product->categories()->attach($categoryIds['category_id'], [
                        'parent_category_id' => $categoryIds['parent_category_id'],
                        'category_path' => $categoryPath
                    ]);
                }
            }
            // Update the product positions
            $positions = $request->input('positions');
            $product->positions()->delete();
            foreach ($positions as $position) {
                $product->positions()->create($position);
            }
            return response()->json(['message' => 'Product updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while updating the product.'], 500);
        }
    }
    public function destroy(ProductRequest $request): JsonResponse
    {
        $product = Product::find($request->validated()['id']);
        $this->productRepository->deleteProduct($product);
        return response()->json(['message' => 'Product deleted successfully.'], 200);
    }
}
