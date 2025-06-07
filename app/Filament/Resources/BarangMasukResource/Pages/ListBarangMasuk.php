<?php

namespace App\Filament\Resources\BarangMasukResource\Pages;

use App\Filament\Resources\BarangMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBarangMasuk extends ListRecords // <-- Nama kelas dikoreksi, hapus 's' di akhir
{
    protected static string $resource = BarangMasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}