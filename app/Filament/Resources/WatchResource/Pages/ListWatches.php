<?php

namespace App\Filament\Resources\WatchResource\Pages; // <-- Pastikan ini benar

use App\Filament\Resources\WatchResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWatches extends ListRecords
{
    protected static string $resource = WatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}