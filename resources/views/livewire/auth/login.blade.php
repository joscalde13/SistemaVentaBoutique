<div class="flex flex-col gap-6">
    <div class="space-y-1 text-center">
        <h1 class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">{{ __('Iniciar sesión') }}</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('') }}</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="login" class="flex flex-col gap-5">
        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Dirección de correo electrónico')"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@ejemplo.com"
        />

        <!-- Password -->
        <div class="relative">
            <flux:input
                wire:model="password"
                :label="__('Contraseña')"
                type="password"
                required
                autocomplete="current-password"
                :placeholder="__('Contraseña')"
                viewable
            />
        </div>

        <div class="pt-1">
            <flux:button variant="primary" type="submit" class="w-full !rounded-lg !font-medium">{{ __('Iniciar sesión') }}</flux:button>
        </div>
    </form>

    @if (Route::has('register'))
        <div class="border-t border-zinc-100 pt-4 text-center text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-400">
            <flux:link :href="route('register')" wire:navigate>{{ __('Registrarme') }}</flux:link>
        </div>
    @endif
</div>
