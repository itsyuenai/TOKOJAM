<?php

namespace App\Filament\Resources\WatchResource\Pages;

use App\Filament\Resources\WatchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWatch extends EditRecord
{
    protected static string $resource = WatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
