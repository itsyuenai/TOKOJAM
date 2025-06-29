<?php

namespace App\Filament\Resources\BarangMasukResource\Pages;

use App\Filament\Resources\BarangMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBarangMasuk extends ViewRecord
{
    protected static string $resource = BarangMasukResource::class;

    // Anda bisa menambahkan aksi atau kustomisasi di sini jika diperlukan
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(), // Mengizinkan edit dari halaman view
        ];
    }
}
