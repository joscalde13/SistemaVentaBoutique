<x-layouts.app :title="__('Agregar prenda')">
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div><a href="{{ route('products.index') }}" class="text-sm font-medium text-amber-800 hover:underline">← Volver al inventario</a><h1 class="mt-3 text-3xl font-semibold tracking-tight text-zinc-900">Agregar prenda</h1><p class="mt-1 text-sm text-zinc-500">Registra la información, precios y existencia inicial.</p></div>
        @include('products._form', ['action' => route('products.store'), 'method' => 'POST', 'product' => null])
    </div>
</x-layouts.app>
