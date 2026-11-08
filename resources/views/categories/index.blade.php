<x-layouts.app :title="__('Categorías')">
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-medium uppercase tracking-[0.18em] text-amber-700">Catálogo</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900">Categorías</h1>
            <p class="mt-1 text-sm text-zinc-500">Organiza las prendas y administra las divisiones de tu tienda.</p>
        </div>
        @error('category')<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>@enderror
        @if (session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif

                <section class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-zinc-900">Nueva categoría</h2>
            <p class="mt-1 text-sm text-zinc-500">Las categorías pueden ampliarse conforme crezca tu catálogo.</p>
            <form action="{{ route('categories.store') }}" method="POST" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-start">@csrf
                <div class="flex-1">
                    <input name="name" required maxlength="120" value="{{ old('name') }}" placeholder="Nombre de la categoría" aria-label="Nombre" class="w-full rounded-xl border border-zinc-900 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:ring-2 focus:ring-zinc-900/20">
                    @error('name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
                </div>
                <button class="rounded-xl bg-zinc-900 px-6 py-2.5 text-sm font-semibold text-white hover:bg-zinc-700">Crear categoría</button>
            </form>
        </section>

            <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <form method="GET" class="flex gap-2 border-b border-zinc-100 p-4">
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Buscar categoría..." class="min-w-0 flex-1 rounded-xl border border-zinc-900 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:ring-2 focus:ring-zinc-900/20">
                    <button class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-medium text-white">Buscar</button>
                </form>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500"><tr><th class="px-5 py-3">Categoría</th><th class="px-5 py-3">Prendas</th><th class="px-5 py-3">Unidades</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3 text-right">Acción</th></tr></thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse ($categories as $category)
                                <tr>
                                    <td class="px-5 py-4">
                                        <form id="category-{{ $category->id }}" action="{{ route('categories.update', $category) }}" method="POST" class="space-y-2">@csrf @method('PUT')
                                            <input name="name" value="{{ $category->name }}" required maxlength="120" class="w-full rounded-lg border-0 bg-transparent px-3 py-2 text-sm font-medium text-zinc-900 outline-none ring-0 focus:bg-white focus:ring-2 focus:ring-zinc-900/20">
                                        </form>
                                    </td>
                                    <td class="px-5 py-4 text-zinc-600">{{ $category->products_count }}</td>
                                    <td class="px-5 py-4 font-medium text-zinc-900">{{ (int) $category->products_sum_stock }}</td>
                                    <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $category->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-zinc-100 text-zinc-500' }}">{{ $category->is_active ? 'Activa' : 'Inactiva' }}</span></td>
                                    <td class="space-y-2 px-5 py-4 text-right">
                                        
                                        <form action="{{ route('categories.toggle', $category) }}" method="POST">@csrf @method('PATCH')<button class="text-xs text-zinc-500 hover:text-zinc-900">{{ $category->is_active ? 'Desactivar' : 'Activar' }}</button></form>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('¿Eliminar esta categoría?')">@csrf @method('DELETE')<button class="inline-flex items-center rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-200 transition hover:bg-red-600 hover:text-white hover:ring-red-600">Eliminar</button></form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-12 text-center text-zinc-500">No hay categorías registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-zinc-100 p-4">{{ $categories->links() }}</div>
        </section>
    </div>
</x-layouts.app>
