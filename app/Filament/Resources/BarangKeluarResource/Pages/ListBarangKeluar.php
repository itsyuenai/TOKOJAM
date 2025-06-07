<?php

namespace App\Filament\Resources\BarangKeluarResource\Pages; // <-- Pastikan ini benar

use App\Filament\Resources\BarangKeluarResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBarangKeluar extends ListRecords
{
    protected static string $resource = BarangKeluarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}