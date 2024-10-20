<?php

namespace App\Filament\Resources\PendaftaranResource\Pages;

use App\Filament\Resources\PendaftaranResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Pendaftaran;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction;
use Illuminate\Support\Facades\Auth;

class CreatePendaftaran extends CreateRecord
{
    protected static string $resource = PendaftaranResource::class;
    protected static ?string $title = 'BUAT PENDAFTARAN';


    protected function getCreatedNotification(): ?Notification
    {
        $data = $this->record;

        // Buat tombol "View" dengan tipe yang benar
        $viewButton = NotificationAction::make('Lihat')
            ->url(PendaftaranResource::getUrl('view', ['record' => $data]));

        // Dapatkan nama pengguna yang sedang masuk
        $editor = Auth::user();
        $editorName = $editor ? $editor->name : 'Unknown';

        // Ambil semua penerima notifikasi
        $recipients = User::all();

        // Buat notifikasi dengan tombol "View"
        $notification = Notification::make()
            ->title('PENDAFTARAN')
            ->body("<strong>{$data->nama}</strong> Berhasil Ditambahkan
                    <br>
                    Oleh <strong>{$editorName}</strong>")
            ->actions([$viewButton])
            ->persistent()
            ->success()
            ->duration(1000);

        // Kirim notifikasi ke semua penerima
        foreach ($recipients as $recipient) {
            $notification->sendToDatabase($recipient);
        }

        return $notification;
    }
    protected function getRedirectUrl(): string
    {
        $record = $this->record;
        return $this->getResource()::getUrl('edit', ['record' => $record]);
    }
}
