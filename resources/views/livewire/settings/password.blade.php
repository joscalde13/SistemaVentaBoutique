<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Actualizar contraseña')" :subheading="__('Asegúrate de que tu cuenta esté utilizando una contraseña larga y aleatoria para mantenerla segura')">
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                :label="__('Contraseña actual')" type="password"
                required
                autocomplete="current-password"
            />
            <flux:input
                wire:model="password"
                :label="__('Nueva contraseña')"
                type="password"
                required
                autocomplete="new-password"
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirmar contraseña')"
                type="password"
                required
                autocomplete="new-password"
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Guardar') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('Contraseña actualizada.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
