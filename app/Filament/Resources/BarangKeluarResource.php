<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BarangKeluarResource\Pages;
use App\Models\BarangKeluar;
use App\Models\Watch; // Menggunakan model Watch
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BarangKeluarResource extends Resource
{
    protected static ?string $model = BarangKeluar::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-on-square';
    protected static ?string $navigationGroup = 'Produk & Inventori';
    protected static ?string $modelLabel = 'Barang Keluar';
    protected static ?string $pluralModelLabel = 'Data Barang Keluar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('jam_id') // Relasi ke model Watch
                    ->label('Pilih Jam Tangan')
                    ->relationship('jam', 'name') // 'jam' adalah nama relasi di model BarangKeluar yang menunjuk ke Watch
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(), // Penting untuk memicu validasi stok saat jam dipilih
                Forms\Components\TextInput::make('quantity')
                    ->label('Kuantitas Keluar')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->default(1),
                Forms\Components\TextInput::make('customer_name')
                    ->label('Nama Pelanggan (Opsional)')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('sale_price')
                    ->label('Harga Jual per Unit')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->inputMode('decimal')
                    ->minValue(0.01),
                Forms\Components\DatePicker::make('exit_date')
                    ->label('Tanggal Keluar')
                    ->required()
                    ->default(now()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('jam.name')
                    ->label('Nama Jam Tangan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_id')
                    ->label('ID Pesanan')
                    ->placeholder('Manual'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Kuantitas Keluar')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Nama Pelanggan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Harga Jual')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('exit_date')
                    ->label('Tanggal Keluar')
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
                Tables\Filters\Filter::make('exit_date')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date): Builder => $query->whereDate('exit_date', '>=', $date))
                            ->when($data['to'], fn (Builder $query, $date): Builder => $query->whereDate('exit_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (BarangKeluar $record): bool => $record->order_id === null),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (BarangKeluar $record): bool => $record->order_id === null),
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
            'index' => Pages\ListBarangKeluar::route('/'),
            'create' => Pages\CreateBarangKeluar::route('/create'),
            'view' => Pages\ViewBarangKeluar::route('/{record}'),
            'edit' => Pages\EditBarangKeluar::route('/{record}/edit'),
        ];
    }
}