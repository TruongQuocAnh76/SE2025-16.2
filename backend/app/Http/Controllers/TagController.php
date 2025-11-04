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
}