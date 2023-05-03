<?php

namespace App\Http\Controllers;

use App\Builders\ProductQueryBuilder;
use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\CategoryParent;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductPosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'filter.size' => 'sometimes|required|string',
            'search' => 'sometimes|required|string',
            'searchBy' => 'sometimes|required|in:id,title,sku',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $query = QueryBuilder::for(Product::class)
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('title'),
                AllowedFilter::partial('sku'),
                AllowedFilter::callback('size', function ($query, $size) {
                    if (is_array($size)) {
                        // $query->whereHas('productPositions', function ($q) use ($sizesArray) {
                        //     $q->whereIn('size', $sizesArray);
                        // });
                        foreach ($size as $s) {
                            $query->orWhereJsonContains('productPositions', ['size' => $s]);
                        }
                    } else {
                        $sizes = explode(',', $size);
                        foreach ($sizes as $s) {
                            $query->orWhereJsonContains('positions', ['size' => $s]);
                        }
                    }
                })
            ])->allowedSorts([
                'id', 'title', 'sku', AllowedSort::field('price', 'positions.price'),
                AllowedSort::field('size', 'positions.size'),
            ]);

        if ($request->has('search') && $request->has('searchBy')) {
            $search = $request->input('search');
            $searchBy = $request->input('searchBy');
            $query->where($searchBy, 'like', "%{$search}%");
        }
        $products = $query->paginate(10);
        return response()->json($products);
    }
    // public function index(Request $request)
    // {
    //     $query = Product::query();

    //     // Filter by size
    //     $sizes = $request->get('filter.size');
    //     if ($sizes) {
    //         $sizesArray = explode(',', $sizes);
    //         $query->whereHas('productPositions', function ($q) use ($sizesArray) {
    //             $q->whereIn('size', $sizesArray);
    //         });
    //     }

    //     // Search by ID, title, or SKU
    //     $search = $request->input('search');
    //     $searchBy = $request->input('searchBy');
    //     if ($search && $searchBy) {
    //         $query->where($searchBy, 'LIKE', '%' . $search . '%');
    //     }

    //     $products = $query->get();

    //     return response()->json($products);
    // }
    public function create(ProductRequest $request)
    {
        // Parse category path
        $categories = $request->input('categories', []);
        $categoryIds = [];
        foreach ($categories as $categoryPath) {
            $categoryIds[] = $this->parseCategoryPath($categoryPath);
        }

        // Create the product
        $product = Product::create([
            'title' => $request->input('title'),
            'sku' => $request->input('sku'),
            'slug' => $request->input('slug'),
            'brand_id' => $request->input('brand_id')
        ]);

        // Insert categories into product_categories table


        foreach ($categoryIds as $categoryId) {
            $category = Category::find($categoryId);
            if ($category) {
                $categoryPath = $category->category_path ?? '';
                if ($categoryPath === '') {
                    // If the category_path is empty, construct it from the category's ancestors
                    $categoryPath = $this->buildCategoryPath($category);
                }
                $product->categories()->attach($categoryId, [
                    'category_path' => $categoryPath,
                    'parent_category_id' => $category->parent_id,
                ]);
            }
        }


        foreach ($categoryIds as $categoryId) {
            $product->categories()->attach($categoryId, [
                'category_path' => $this->getCategoryPath($categoryId),
                'parent_category_id' => $this->getParentCategoryId($categoryId),
            ]);
        }


        // Assign categories
        // $categories = $request->input('categories');
        // if ($categories) {

        //     foreach ($categories as $category) {
        //         $categoryIds = explode('.', $category);
        //         $categoryId = end($categoryIds);
        //         $parentCategoryId = count($categoryIds) > 1 ? $categoryIds[count($categoryIds) - 2] : null;
        //         $categoryPath = implode('.', $categoryIds);

        //         ProductCategory::create([
        //             'product_id' => $product->id,
        //             'category_id' => $categoryId,
        //             'parent_category_id' => $parentCategoryId,
        //             'category_path' => $categoryPath,
        //         ]);
        //     }
        // foreach ($categories  as $categoryId) {
        //     $category = Category::findOrFail($categoryId);
        //     $productCategory = new ProductCategory();
        //     $productCategory->product_id = $product->id;
        //     $productCategory->category_id = $categoryId;
        //     $productCategory->parent_category_id = $category->parent_category_id;
        //     $productCategory->category_path = $category->parent_category_id ? $category->parent_category->category_path . '.' . $categoryId : $categoryId;
        //     $productCategory->save();
        // }


        // $categoryIds = $this->parseCategories($categories);
        // $productCategories = [];
        // foreach ($categoryIds as $categoryId) {
        //     $productCategories[] = new ProductCategory([
        //         'category_id' => $categoryId,
        //         'category_path' => $this->getCategoryPath($categoryId)
        //     ]);
        // }
        // $product->productCategories()->saveMany($productCategories);
        // }
        // Create product positions
        $positions = $request->input('positions');
        if ($positions) {
            $productPositions = [];
            foreach ($positions as $position) {
                $productPositions[] = new ProductPosition([
                    'size' => $position['size'],
                    'price' => $position['price']
                ]);
            }
            $product->productPositions()->saveMany($productPositions);
        }
        return response()->json(['message' => 'Product created successfully'], 201);
    }
    private function parseCategoryPath($categoryPath)
    {
        // Split path into category IDs
        $categoryIds = explode('.', $categoryPath);

        // Insert parents into category_parents table
        $parentCategoryId = null;
        foreach ($categoryIds as $categoryId) {
            if ($parentCategoryId !== null) {
                CategoryParent::firstOrCreate([
                    'parent_id' => $parentCategoryId,
                    'category_id' => $categoryId,
                ]);
            }
            $parentCategoryId = $categoryId;
        }

        // Return the last category ID
        return $categoryId;
    }

    private function getParentCategoryId($categoryId)
    {
        $category = Category::find($categoryId);
        return $category ? $category->parent_id : null;
    }

    private function getCategoryPath($categoryId)
    {
        $category = Category::find($categoryId);
        return $category ? $category->category_path : '';
    }
    public function update(ProductRequest $request, $id)
    {
        // $validated = $request->validate([
        //     'title' => 'required|string|max:255',
        //     'slug' => 'required|string|unique:products,id,' . $id . '|max:255',
        //     'sku' => 'required|string|unique:products,id,' . $id . '|max:255',
        //     'brand_id' => 'required|integer|exists:brands,id'
        // ]);


        $product = Product::find($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        // Update the product
        $product->title = $request->input('title', $product->title);
        $product->sku = $request->input('sku', $product->sku);
        $product->slug = $request->input('slug', $product->slug);
        $product->brand_id = $request->input('brand_id', $product->brand_id);

        // Assign categories
        $categories = $request->input('categories');
        if ($categories) {
            $product->categories()->sync($categories);
        }

        // Update product positions
        $positions = $request->input('positions');
        if ($positions) {
            $product->productPositions()->delete();
            foreach ($positions as $positionData) {
                $position = new ProductPosition();
                $position->price = $positionData['price'];
                $position->size = $positionData['size'];
                $product->productPositions()->save($position);
            }
        }

        $product->save();

        return response()->json($product, 200);
        // $product = Product::find($id);
        // if (!$product) {
        //     return response()->json(['error' => 'Product not found.'], 404);



        //     $product->update($validated);

        // $categories = $request->input('categories');
        // if ($categories) {
        //     $categoryIds = $this->parseCategories($categories);
        //     $productCategories = [];
        //     foreach ($categoryIds as $categoryId) {
        //         $productCategories[] = new ProductCategory([
        //             'category_id' => $categoryId,
        //             'category_path' => $this->getCategoryPath($categoryId)
        //         ]);
        //     }
        //     $product->productCategories()->delete();
        //     $product->productCategories()->saveMany($productCategories);
        // }

        // $positions = $request->input('positions');
        // if ($positions) {
        //     $productPositions = [];
        //     foreach ($positions as $position) {
        //         $productPositions[] = new ProductPosition([
        //             'size' => $position['size'],
        //             'price' => $position['price']
        //         ]);
        //     }
        //     $product->productPositions()->delete();
        //     $product->productPositions()->saveMany($productPositions);
        // }

        // return response()->json($product);
    }

    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    private function parseCategories($categories)
    {
        $categoryIds = [];
        foreach ($categories as $category) {
            $ids = explode('.', $category);
            $categoryId = end($ids);
            $categoryIds[] = $categoryId;
        }
        return $categoryIds;
    }

    // private function getCategoryPath($categoryId)
    // {
    //     $category = Category::findOrFail($categoryId);
    //     $path = '';
    //     while ($category) {
    //         $path = $category->id . ($path ? '.' : '') . $path;
    //         $category = $category->parent;
    //     }
    //     return $path;
    // }
}
