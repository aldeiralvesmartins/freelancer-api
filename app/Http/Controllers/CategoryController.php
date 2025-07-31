<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
     public function index()
    {
        // Pega todas as categorias ordenadas por nome
        $categories = Category::orderBy('name')->get();

        return response()->json($categories);
    }
}
