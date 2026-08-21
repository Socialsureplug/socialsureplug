<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\SmmCategory;
use App\Models\SmmSubcategory;
use Illuminate\Http\Request;

class SmmSubcategoryController extends Controller
{
    public function index(Request $request)
    {
        $title = 'SMM Subcategories';
        $query = SmmSubcategory::with('smmCategory')->withCount('smmServices')->orderBy('smm_category_id')->orderBy('sort');
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where('name', 'like', "%{$term}%");
        }
        if ($request->filled('category_id')) {
            $query->where('smm_category_id', $request->category_id);
        }
        $subcategories = $query->paginate(15)->withQueryString();
        $categories = SmmCategory::orderBy('sort')->get();
        $search = $request->search ?? '';

        return view('backend.smm.subcategories.index', compact('title', 'subcategories', 'categories', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'smm_category_id' => 'required|exists:smm_categories,id',
            'name' => 'required|string|max:255',
            'sort' => 'nullable|integer|min:0',
        ]);
        SmmSubcategory::create([
            'smm_category_id' => $request->smm_category_id,
            'name' => $request->name,
            'sort' => $request->sort ?? 0,
        ]);

        return redirect()->back()->with('success', 'Subcategory added.');
    }

    public function update(Request $request, $id)
    {
        $sub = SmmSubcategory::findOrFail($id);
        $request->validate([
            'smm_category_id' => 'required|exists:smm_categories,id',
            'name' => 'required|string|max:255',
            'sort' => 'nullable|integer|min:0',
        ]);
        $sub->update($request->only('smm_category_id', 'name', 'sort'));

        return redirect()->back()->with('success', 'Subcategory updated.');
    }

    public function destroy($id)
    {
        SmmSubcategory::findOrFail($id)->delete();

        return redirect()->back()->with('success', 'Subcategory deleted.');
    }
}