<?php

namespace App\Filament\Resources\WatchResource\Pages;

use App\Filament\Resources\WatchResource; // Pastikan ini mengacu ke WatchResource
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewWatch extends ViewRecord
{
    // Mengaitkan halaman ini dengan WatchResource
    protected static string $resource = WatchResource::class;

    // Mendefinisikan aksi-aksi yang akan muncul di header halaman view
    protected function getHeaderActions(): array
    {
        return [
            // Tombol untuk mengedit record dari halaman view
            Actions\EditAction::make(),
        ];
    }
}