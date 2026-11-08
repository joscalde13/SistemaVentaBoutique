<div class="flex flex-col gap-6">
    <div class="space-y-1 text-center">
        <h1 class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-100">{{ __('Crear cuenta') }}</h1>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="register" class="flex flex-col gap-4">
        <!-- Name -->
        <flux:input
            wire:model="name"
            :label="__('Nombre')"
            type="text"
            required
            autofocus
            autocomplete="name"
            :placeholder="__('Nombre completo')"
        />

        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Dirección de correo electrónico')"
            type="email"
            required
            autocomplete="email"
            placeholder="email@ejemplo.com"
        />

        <!-- Password -->
        <flux:input
            wire:model="password"
            :label="__('Contraseña')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Contraseña')"
            viewable
        />

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirmar contraseña')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirmar contraseña')"
            viewable
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full !rounded-lg !font-medium">
                {{ __('Crear cuenta') }}
            </flux:button>
        </div>
    </form>

    <div class="border-t border-zinc-100 pt-4 text-center text-sm text-zinc-600 dark:border-zinc-800 dark:text-zinc-400">
        <flux:link :href="route('login')" wire:navigate>{{ __('Iniciar sesión') }}</flux:link>
    </div>
</div>
