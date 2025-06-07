<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCategory extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    // Anda bisa menambahkan aksi atau kustomisasi di sini jika diperlukan
    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(), // Mengizinkan edit dari halaman view
        ];
    }
}