<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    // Anda bisa menambahkan aksi atau kustomisasi di sini jika diperlukan
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(), // Mengizinkan edit dari halaman view
        ];
    }
}