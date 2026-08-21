<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SmmCategory;
use Illuminate\Http\Request;

class SmmCategoryController extends Controller
{
    public function index(Request $request)
    {
        $title = 'SMM Categories';
        $query = SmmCategory::withCount('smmServices')->orderBy('sort');
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where('name', 'like', "%{$term}%");
        }
        $categories = $query->paginate(10)->withQueryString();
        $search = $request->search ?? '';
        return view('backend.smm.categories.index', compact('title', 'categories', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255', 'sort' => 'nullable|integer|min:0']);
        SmmCategory::create([
            'name' => $request->name,
            'sort' => $request->sort ?? 0,
        ]);
        return redirect()->back()->with('success', 'Category added.');
    }

    public function update(Request $request, $id)
    {
        $cat = SmmCategory::findOrFail($id);
        $request->validate(['name' => 'required|string|max:255', 'sort' => 'nullable|integer|min:0']);
        $cat->update($request->only('name', 'sort'));
        return redirect()->back()->with('success', 'Category updated.');
    }

    public function destroy($id)
    {
        SmmCategory::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Category deleted.');
    }
}