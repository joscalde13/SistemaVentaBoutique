<x-layouts.app :title="__('Configuración del negocio')">
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        <div><p class="text-sm font-medium uppercase tracking-[0.18em] text-amber-700">Preferencias</p><h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-900">Configuración del negocio</h1><p class="mt-1 text-sm text-zinc-500">Estos datos aparecen en los comprobantes de venta.</p></div>
        @if (session('success'))<div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form action="{{ route('business-settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">@csrf @method('PUT')
            <div class="grid gap-5 md:grid-cols-2">
                <label class="text-sm font-medium text-zinc-700 md:col-span-2">Nombre del negocio *
                    <input name="business_name" required maxlength="180" value="{{ old('business_name', $settings->business_name) }}" class="mt-1.5 w-full rounded-xl border-zinc-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                </label>
                <label class="text-sm font-medium text-zinc-700">Dirección
                    <input name="address" maxlength="255" value="{{ old('address', $settings->address) }}" class="mt-1.5 w-full rounded-xl border-zinc-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                </label>
                <label class="text-sm font-medium text-zinc-700">Teléfono
                    <input name="phone" maxlength="40" value="{{ old('phone', $settings->phone) }}" class="mt-1.5 w-full rounded-xl border-zinc-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                </label>
                <label class="text-sm font-medium text-zinc-700">Correo electrónico
                    <input type="email" name="email" maxlength="255" value="{{ old('email', $settings->email) }}" class="mt-1.5 w-full rounded-xl border-zinc-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                </label>
                <label class="text-sm font-medium text-zinc-700">Moneda ISO *
                    <input name="currency_code" required maxlength="3" minlength="3" value="{{ old('currency_code', $settings->currency_code) }}" class="mt-1.5 w-full rounded-xl border-zinc-200 text-sm uppercase focus:border-amber-500 focus:ring-amber-500">
                </label>
                <label class="text-sm font-medium text-zinc-700">Símbolo de moneda *
                    <input name="currency_symbol" required maxlength="5" value="{{ old('currency_symbol', $settings->currency_symbol) }}" class="mt-1.5 w-full rounded-xl border-zinc-200 text-sm focus:border-amber-500 focus:ring-amber-500">
                </label>
                <label class="text-sm font-medium text-zinc-700">Logo <span class="font-normal text-zinc-400">(opcional)</span>
                    <input type="file" name="logo" accept="image/*" class="mt-1.5 block w-full rounded-xl border border-zinc-200 text-sm file:mr-3 file:border-0 file:bg-zinc-100 file:px-3 file:py-2">
                    @if ($settings->logo_path)<img src="{{ asset('storage/'.$settings->logo_path) }}" alt="Logo actual" class="mt-3 h-16 w-16 rounded-lg object-contain">@endif
                </label>
            </div>
            <div class="flex justify-end border-t border-zinc-100 pt-5"><button class="rounded-xl bg-zinc-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-zinc-700">Guardar configuración</button></div>
        </form>
    </div>
</x-layouts.app>
