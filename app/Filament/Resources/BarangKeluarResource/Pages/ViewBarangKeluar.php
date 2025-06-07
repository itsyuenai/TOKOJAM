<?php

    namespace App\Filament\Resources\BarangKeluarResource\Pages;

    use App\Filament\Resources\BarangKeluarResource;
    use Filament\Actions;
    use Filament\Resources\Pages\ViewRecord;

    class ViewBarangKeluar extends ViewRecord
    {
        protected static string $resource = BarangKeluarResource::class;

        // Anda bisa menambahkan aksi atau kustomisasi di sini jika diperlukan
        protected function getHeaderActions(): array
        {
            return [
                Actions\EditAction::make(), // Mengizinkan edit dari halaman view
            ];
        }
    }
    