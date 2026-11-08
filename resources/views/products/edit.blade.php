<x-layouts.app :title="__('Editar prenda')">
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div><a href="{{ route('products.show', $product) }}" class="text-sm font-medium text-amber-800 hover:underline">← Volver al detalle</a><h1 class="mt-3 text-3xl font-semibold tracking-tight text-zinc-900">Editar prenda</h1><p class="mt-1 text-sm text-zinc-500">Las existencias se modifican desde movimientos de inventario.</p></div>
        @include('products._form', ['action' => route('products.update', $product), 'method' => 'PUT', 'product' => $product])
    </div>
</x-layouts.app>
