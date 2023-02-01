<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductCategoryController extends Controller
{

    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');

        $name = $request->input('name');
        $show_product = $request->input('show_product');

        // mengambil data product
        if ($id) {
            try {
                $category = ProductCategory::with(['products'])->findOrFail($id);
            } catch (ModelNotFoundException $e) {
                return ResponseFormatter::error(message: 'Category Product tidak ada', code: 404);
            }
            return ResponseFormatter::success($category, 'Data Category berhasil di ambil');
        }

        $category = ProductCategory::query();

        if ($name) {
            $category->where('name', 'like', '%' . $name . '%');
        }
        if ($show_product) {
            $category->with('products');
        }

        return ResponseFormatter::success($category->paginate($limit), 'Data Category berhasil di ambil');
    }
}
