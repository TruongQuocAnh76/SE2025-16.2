<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Hiển thị danh sách tất cả các tag.
     */
    public function index()
    {
        // Lấy tất cả tag, chỉ cần 'id' và 'name'
        $tags = Tag::select('id', 'name')->orderBy('name')->get();
        return response()->json($tags);
    }

    /**
     * Tạo tag mới.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:tags,name'
        ]);

        $name = trim($request->name);
        $slug = \Illuminate\Support\Str::slug($name);

        $tag = Tag::create([
            'name' => $name,
            'slug' => $slug
        ]);

        return response()->json([
            'message' => 'Tag created successfully',
            'tag' => $tag
        ], 201);
    }
}