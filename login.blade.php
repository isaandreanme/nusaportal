<x-filament-panels::page.simple>
    
<h3 class="text-sm font-semibold text-center" style="color: #09b8a7; text-transform: uppercase;">DEMO</h3>
    @if (filament()->hasRegistration())
    <x-slot name="subheading">
        {{ __('filament-panels::pages/auth/login.actions.register.before') }}

        {{ $this->registerAction }}
    </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()" />

        <!-- Tombol Autofill untuk setiap pengguna -->
        <div class="mt-4 space-y-4">
            <h3 class="text-sm font-semibold text-center" style="color: #09b8a7; text-transform: uppercase;">Masuk Sebagai</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button type="button" onclick="autofillLogin('admin@gmail.com', 'password')" class="autofill-btn">Admin</button>
                <button type="button" onclick="autofillLogin('pendaftaran@gmail.com', 'password')" class="autofill-btn">Petugas Pendaftaran</button>
                <button type="button" onclick="autofillLogin('prosescpmi@gmail.com', 'password')" class="autofill-btn">Petugas Proses</button>
                <button type="button" onclick="autofillLogin('marketing@gmail.com', 'password')" class="autofill-btn">Petugas Marketing</button>
                <br>
                <button type="button" onclick="autofillLogin('agency@gmail.com', 'password')" class="autofill-btn">Agency</button>
                <button type="button" onclick="autofillLogin('cpmi@gmail.com', 'password')" class="autofill-btn">CPMI</button>
            </div>
        </div>
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
    <!-- JavaScript untuk mengisi otomatis form login -->
    <script>
        function autofillLogin(email, password) {
            // Mengisi nilai email dan password ke dalam input form
            const emailInput = document.querySelector('input[type="email"]');
            const passwordInput = document.querySelector('input[type="password"]');

            emailInput.value = email;
            passwordInput.value = password;

            // Memicu event 'input' agar Livewire atau Filament mengenali perubahan
            emailInput.dispatchEvent(new Event('input'));
            passwordInput.dispatchEvent(new Event('input'));
        }
    </script>


    <!-- CSS untuk styling tombol autofill dan teks -->
    <style>
        /* Gaya tombol Autofill */
        .autofill-btn {
            padding: 0.5rem;
            /* Padding dikurangi */
            font-size: 0.875rem;
            /* Ukuran font kecil (Tailwind's 'text-sm') */
            font-weight: 600;
            border-radius: 0.375rem;
            background-color: #ed0000;
            /* Warna tombol diperbarui */
            color: white;
            text-align: center;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
            text-transform: uppercase;
            /* Huruf kapital */
        }

        /* Efek hover */
        .autofill-btn:hover {
            background-color: #078f89;
            /* Warna yang lebih gelap saat dihover */
        }

        /* Gaya tombol di mode gelap */
        body.dark .autofill-btn {
            background-color: #09b8a7;
            /* Konsisten untuk mode gelap */
        }

        body.dark .autofill-btn:hover {
            background-color: #078f89;
        }

        /* Gaya heading untuk pilihan Autofill */
        h3 {
            color: #09b8a7;
            /* Warna yang diminta */
            text-transform: uppercase;
            /* Huruf kapital */
            text-align: center;
            /* Teks rata tengah */
            font-size: 0.875rem;
            /* Ukuran font kecil (Tailwind's 'text-sm') */
            padding: 0.5rem;
            /* Menambahkan padding untuk box */
            border: 1px solid #09b8a7;
            /* Border untuk membuat box */
            border-radius: 0.375rem;
            /* Opsional: sudut melengkung */
            background-color: #f0fdfa;
            /* Opsional: warna background yang cerah */
        }

        /* Gaya heading di mode gelap */
        body.dark h3 {
            color: #09b8a7;
            /* Warna yang sama di mode gelap */
            border-color: #09b8a7;
            background-color: #022c22;
            /* Opsional: warna background untuk mode gelap */
        }
    </style>
</x-filament-panels::page.simple>