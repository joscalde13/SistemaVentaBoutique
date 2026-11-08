<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SaleDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->withSum('products', 'stock')
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search')->toString().'%'))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        Category::create($data);

        return to_route('categories.index')->with('success', 'La categoría se creó correctamente.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $category->update($data);

        return to_route('categories.index')->with('success', 'La categoría se actualizó correctamente.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists() || SaleDetail::query()->where('category_id', $category->id)->exists()) {
            return to_route('categories.index')->withErrors(['category' => 'No se puede eliminar: la categoría tiene prendas o ventas asociadas. Puedes desactivarla.']);
        }

        $category->delete();

        return to_route('categories.index')->with('success', 'La categoría se eliminó.');
    }

    public function toggle(Category $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return to_route('categories.index')->with('success', 'Se actualizó el estado de la categoría.');
    }
}
