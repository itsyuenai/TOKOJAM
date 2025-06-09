<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
     protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Pesanan berhasil dibuat!')
            ->body('Pesanan baru telah berhasil ditambahkan ke sistem.');
    }
}
