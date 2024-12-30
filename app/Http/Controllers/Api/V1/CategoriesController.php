<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    public function getCategories()
    {
        $categories = Category::all();

        return response()->json(['data' => $categories]);
    }
}
