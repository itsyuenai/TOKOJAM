<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangMasukResource\Pages;
use App\Models\BarangMasuk;
use App\Models\Watch; // Menggunakan model Watch
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BarangMasukResource extends Resource
{
    protected static ?string $model = BarangMasuk::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-on-square';
    protected static ?string $navigationGroup = 'Produk & Inventori';
    protected static ?string $modelLabel = 'Barang Masuk';
    protected static ?string $pluralModelLabel = 'Data Barang Masuk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('jam_id') // Relasi ke model Watch
                    ->label('Pilih Jam Tangan')
                    ->relationship('jam', 'name') // 'jam' adalah nama relasi di model BarangMasuk yang menunjuk ke Watch
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('quantity')
                    ->label('Kuantitas Masuk')
                    ->numeric()
                    ->required()
                    ->minValue(1) // Kuantitas minimal 1
                    ->default(1),
                Forms\Components\TextInput::make('supplier')
                    ->label('Supplier')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('purchase_price')
                    ->label('Harga Beli per Unit')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->inputMode('decimal')
                    ->minValue(0.01),
                Forms\Components\DatePicker::make('entry_date')
                    ->label('Tanggal Masuk')
                    ->required()
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jam.name') // Tampilkan nama jam tangan dari relasi
                    ->label('Nama Jam Tangan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Kuantitas Masuk')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('supplier')
                    ->label('Supplier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Harga Beli')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Tanggal Masuk')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jam_id')
                    ->label('Filter Jam Tangan')
                    ->options(Watch::all()->pluck('name', 'id')->toArray()),
                Tables\Filters\Filter::make('entry_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date): Builder => $query->whereDate('entry_date', '>=', $date))
                            ->when($data['to'], fn (Builder $query, $date): Builder => $query->whereDate('entry_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(), // Tambahkan aksi delete
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBarangMasuk::route('/'),
            'create' => Pages\CreateBarangMasuk::route('/create'),
            'view' => Pages\ViewBarangMasuk::route('/{record}'),
            'edit' => Pages\EditBarangMasuk::route('/{record}/edit'),
        ];
    }

    // Hooks untuk update stok Jam Tangan
    public static function afterCreate(BarangMasuk $record): void
    {
        $watch = Watch::find($record->jam_id);
        if ($watch) {
            $watch->increment('stock', $record->quantity);
        }
    }

    public static function afterUpdate(BarangMasuk $record, array $oldData): void
    {
        $watch = Watch::find($record->jam_id);
        if ($watch) {
            $oldQuantity = $oldData['quantity'] ?? 0;
            $diff = $record->quantity - $oldQuantity;
            $watch->increment('stock', $diff); // Menyesuaikan stok berdasarkan perubahan kuantitas
        }
    }

    public static function afterDelete(BarangMasuk $record): void
    {
        $watch = Watch::find($record->jam_id);
        if ($watch) {
            $watch->decrement('stock', $record->quantity); // Mengurangi stok saat record dihapus
        }
    }
}