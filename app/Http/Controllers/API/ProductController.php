<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Get Product
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');

        $name = $request->input('name');
        $tag = $request->input('tag');
        $description = $request->input('description');
        $categories = $request->input('categories');

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        // mengambil data product from id
        if ($id) {
            try {
                $product = Product::with(['category', 'galleries'])->findOrFail($id);
            } catch (ModelNotFoundException $e) {
                return ResponseFormatter::error(message: 'Data product tidak ada', code: $e->getCode());
            }
            return ResponseFormatter::success($product, 'Data berhasil di ambil');
        }

        $product = Product::with(['category', 'galleries']);

        if ($name) {
            $product->where('name', 'like', '%' . $name . '%');
        }
        if ($description) {
            $product->where('description', 'like', '%' . $description . '%');
        }
        if ($tag) {
            $product->where('tag', 'like', '%' . $tag . '%');
        }
        if ($price_from) {
            $product->where('price', '>=', $price_from);
        }
        if ($price_to) {
            $product->where('price', '<=', $price_to);
        }
        if ($categories) {
            $product->where('categories', $categories);
        }

        return ResponseFormatter::success($product->paginate($limit), 'Data berhasil di ambil');
    }
}
