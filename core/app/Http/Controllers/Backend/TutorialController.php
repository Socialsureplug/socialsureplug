<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Tutorial;
use Illuminate\Http\Request;

class TutorialController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Tutorials & Guides';
        $query = Tutorial::orderBy('sort_order')->orderBy('id');
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where('title', 'like', "%{$term}%");
        }
        $tutorials = $query->paginate(10)->withQueryString();
        $search = $request->search ?? '';
        return view('backend.tutorials.index', compact('title', 'tutorials', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:500',
            'url'   => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        Tutorial::create([
            'title'      => $request->title,
            'url'        => $request->url ?: '#',
            'status'     => 1,
            'sort_order' => $request->sort_order ?? 0,
        ]);
        return redirect()->back()->with('success', 'Tutorial added.');
    }

    public function update(Request $request, $id)
    {
        $tutorial = Tutorial::findOrFail($id);
        $request->validate([
            'title'      => 'required|string|max:500',
            'url'        => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ]);
        $tutorial->update([
            'title'      => $request->title,
            'url'        => $request->url ?: '#',
            'sort_order' => $request->sort_order ?? 0,
        ]);
        return redirect()->back()->with('success', 'Tutorial updated.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $tutorial = Tutorial::findOrFail($id);
        $tutorial->update(['status' => $tutorial->status ? 0 : 1]);
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Status updated.', 'status' => (int) $tutorial->fresh()->status]);
        }
        return redirect()->back()->with('success', 'Status updated.');
    }

    public function destroy($id)
    {
        Tutorial::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Tutorial deleted.');
    }
}
