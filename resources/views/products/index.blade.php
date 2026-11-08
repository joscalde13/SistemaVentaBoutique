<x-layouts.app :title="__('Inventario / Prendas')">
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div><p class="text-sm font-medium uppercase tracking-[0.18em] text-amber-700">Catálogo</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900">Inventario / Prendas</h1><p class="mt-1 text-sm text-zinc-500">Consulta existencias, costos y precios de venta.</p></div>
            <a href="{{ route('products.create') }}" class="rounded-xl bg-zinc-900 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-zinc-700">+ Agregar prenda</a>
        </div>
        @if (session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
        <section class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            <form method="GET" class="grid gap-3 border-b border-zinc-100 p-4 sm:grid-cols-2 lg:grid-cols-5">
                <input type="hidden" name="view" value="{{ request('view', 'table') }}">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Nombre" class="h-12 rounded-xl border border-zinc-900 bg-white px-4 text-base text-zinc-900 outline-none placeholder:text-zinc-400 focus:ring-2 focus:ring-zinc-900/20 sm:col-span-2 lg:col-span-2">
                <select name="category_id" class="rounded-xl border border-zinc-900 bg-white px-4 py-2.5 text-sm text-zinc-900 outline-none placeholder:text-zinc-400 focus:ring-2 focus:ring-zinc-900/20 sm:col-span-2 lg:col-span-2"><option value="">Todas las categorías</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>@endforeach</select>
                
                
                <button class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-medium text-white">Aplicar filtros</button>
            </form>
          
            @if (request('view') === 'cards')
                <div class="grid gap-4 p-4 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse ($products as $product)
                        <article class="overflow-hidden rounded-xl border border-zinc-200 bg-white">
                            @if ($product->image_path)<img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="h-48 w-full object-cover">@else<div class="flex h-48 items-center justify-center bg-amber-50 text-5xl font-semibold text-amber-800">{{ mb_substr($product->name, 0, 1) }}</div>@endif
                            <div class="space-y-3 p-4">
                                <div class="flex items-start justify-between gap-3"><div><a href="{{ route('products.show', $product) }}" class="font-semibold text-zinc-900 hover:underline">{{ $product->name }}</a><p class="mt-1 text-xs text-zinc-500">{{ $product->category->name }}</p></div><span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs text-zinc-600">{{ $product->status }}</span></div>
                                <dl class="grid grid-cols-2 gap-3 text-xs"><div><dt class="text-zinc-400">Talla / color</dt><dd class="mt-1 text-zinc-700">{{ $product->size ?: '—' }} / {{ $product->color ?: '—' }}</dd></div><div><dt class="text-zinc-400">Existencias</dt><dd class="mt-1 font-medium text-zinc-800">{{ $product->stock }}</dd></div><div><dt class="text-zinc-400">Compra</dt><dd class="mt-1 text-zinc-700">{{ $currencySymbol }} {{ number_format((float) $product->purchase_price, 2) }}</dd></div><div><dt class="text-zinc-400">Venta</dt><dd class="mt-1 font-medium text-zinc-900">{{ $currencySymbol }} {{ number_format((float) $product->sale_price, 2) }}</dd></div><div><dt class="text-zinc-400">Descuento máximo</dt><dd class="mt-1 text-zinc-700">{{ $currencySymbol }} {{ number_format((float) $product->max_discount, 2) }}</dd></div></dl>
                                <a href="{{ route('products.edit', $product) }}" class="block border-t border-zinc-100 pt-3 text-sm font-medium text-amber-800 hover:underline">Editar prenda</a>
                            </div>
                        </article>
                    @empty
                        <p class="col-span-full py-12 text-center text-sm text-zinc-500">No se encontraron prendas.</p>
                    @endforelse
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500"><tr><th class="px-5 py-3">Prenda</th><th class="px-5 py-3">Categoría</th><th class="px-5 py-3">Talla / color</th><th class="px-5 py-3">Compra</th><th class="px-5 py-3">Venta</th><th class="px-5 py-3">Existencia</th><th class="px-5 py-3">Estado</th><th class="px-5 py-3"></th></tr></thead>
                    <tbody class="divide-y divide-zinc-100">
                        @forelse ($products as $product)
                            <tr class="hover:bg-zinc-50/70">
                                <td class="px-5 py-4 font-medium text-zinc-900"><a href="{{ route('products.show', $product) }}" class="hover:underline">{{ $product->name }}</a></td>
                                <td class="px-5 py-4 text-zinc-600">{{ $product->category->name }}</td>
                                <td class="px-5 py-4 text-zinc-600">{{ $product->size ?: '—' }} / {{ $product->color ?: '—' }}</td>
                                <td class="px-5 py-4 text-zinc-600">{{ $currencySymbol }} {{ number_format((float) $product->purchase_price, 2) }}</td>
                                <td class="px-5 py-4 font-medium text-zinc-900">{{ $currencySymbol }} {{ number_format((float) $product->sale_price, 2) }}</td>
                                <td class="px-5 py-4 text-zinc-700">{{ $product->stock }}</td>
                                <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $product->status === 'Disponible' ? 'bg-emerald-50 text-emerald-700' : ($product->status === 'Inactiva' ? 'bg-zinc-100 text-zinc-500' : 'bg-amber-50 text-amber-800') }}">{{ $product->status }}</span></td>
                                <td class="whitespace-nowrap px-5 py-4 text-right"><a href="{{ route('products.edit', $product) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-zinc-900 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-zinc-700"><svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.86 4.49 1.69-1.69a1.875 1.875 0 1 1 2.65 2.65L7.5 19.15 3 21l1.85-4.5L16.86 4.49Z"/></svg>Editar</a>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}" class="ml-1.5 inline" onsubmit="return confirm('¿Eliminar esta prenda? Se borrará también su inversión. Las ventas ya hechas se conservan con su nombre y precios.')">@csrf @method('DELETE')<button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-200 transition hover:bg-red-600 hover:text-white hover:ring-red-600"><svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.35 9m-4.78 0L9.26 9m9.97-3.21c.34.05.68.11 1.02.17m-1.02-.17-1.08 13.9a2.25 2.25 0 0 1-2.24 2.08H8.08a2.25 2.25 0 0 1-2.24-2.08L4.77 5.79m14.46 0a48.1 48.1 0 0 0-3.48-.4m-12 .57c.34-.06.68-.12 1.02-.17m0 0a48.1 48.1 0 0 1 3.48-.4m7.5 0v-.92c0-1.18-.91-2.16-2.09-2.2a51.96 51.96 0 0 0-3.32 0c-1.18.04-2.09 1.02-2.09 2.2v.92m7.5 0a48.67 48.67 0 0 0-7.5 0"/></svg>Eliminar</button></form></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-14 text-center text-zinc-500">No se encontraron prendas. <a class="font-medium text-amber-800" href="{{ route('products.create') }}">Registra la primera.</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @endif
            <div class="border-t border-zinc-100 p-4">{{ $products->links() }}</div>
        </section>
    </div>
</x-layouts.app>
