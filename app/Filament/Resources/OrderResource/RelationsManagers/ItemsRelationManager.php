<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Watch; // Penting: Pastikan model Watch diimport

class ItemsRelationManager extends RelationManager
{
    // Properti ini harus sesuai dengan nama metode relasi di model Order
    // Contoh: di model Order.php, harus ada public function items() { return $this->hasMany(OrderItem::class); }
    protected static string $relationship = 'items';

    // Mendefinisikan form untuk membuat atau mengedit OrderItem
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('jam_id') // Kolom foreign key ke tabel watches
                    ->label('Produk Jam Tangan')
                    // Menggunakan relasi 'jam' di model OrderItem yang menunjuk ke model Watch
                    ->relationship('jam', 'name')
                    ->searchable() // Memungkinkan pencarian produk
                    ->preload() // Memuat semua opsi di awal (untuk daftar produk yang tidak terlalu banyak)
                    ->required() // Wajib diisi
                    ->reactive() // Penting agar perubahan ini (pemilihan produk) memicu afterStateUpdated
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        // Saat produk dipilih, secara otomatis mengisi 'price_per_item' dengan harga jual jam
                        $watch = Watch::find($state);
                        if ($watch) {
                            $set('price_per_item', $watch->price);
                        }
                    }),
                Forms\Components\TextInput::make('quantity')
                    ->label('Kuantitas')
                    ->numeric() // Hanya menerima angka
                    ->required() // Wajib diisi
                    ->minValue(1) // Kuantitas minimal 1
                    ->reactive() // Penting agar perubahan ini memicu validasi stok dan perhitungan total
                    ->rules([ // Menambahkan validasi kustom untuk stok
                        function (Forms\Get $get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $watchId = $get('jam_id');
                                if (!$watchId) {
                                    // Lewati validasi jika produk jam belum dipilih
                                    return;
                                }
                                $watch = Watch::find($watchId);
                                // Memastikan kuantitas tidak melebihi stok yang tersedia
                                if ($watch && $value > $watch->stock) {
                                    $fail("Kuantitas ({$value}) melebihi stok yang tersedia ({$watch->stock}) untuk " . $watch->name . ".");
                                }
                            };
                        },
                    ]),
                Forms\Components\TextInput::make('price_per_item')
                    ->label('Harga per Unit')
                    ->numeric()
                    ->required()
                    ->prefix('Rp') // Menambahkan prefix mata uang
                    ->inputMode('decimal') // Memungkinkan input desimal
                    ->readOnly(), // Tidak bisa diubah manual, diisi otomatis
            ])
            ->columns(3); // Menampilkan form dalam 3 kolom
    }

    // Mendefinisikan tabel untuk menampilkan OrderItem
    public function table(Table $table): Table
    {
        return $table
            // Menggunakan nama jam tangan sebagai judul setiap record di daftar
            ->recordTitleAttribute('jam.name')
            ->columns([
                Tables\Columns\TextColumn::make('jam.name')
                    ->label('Produk Jam Tangan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Kuantitas')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_item')
                    ->label('Harga per Unit')
                    ->money('IDR') // Format sebagai mata uang Rupiah
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Subtotal')
                    // Menghitung subtotal dengan mengalikan kuantitas dan harga per item
                    ->getStateUsing(fn ($record) => $record->quantity * $record->price_per_item)
                    ->money('IDR') // Format sebagai mata uang Rupiah
                    ->sortable(),
            ])
            ->filters([
                // Anda bisa menambahkan filter di sini jika diperlukan
            ])
            // Aksi-aksi yang tersedia di header tabel (di atas daftar item)
            ->headerActions([
                Tables\Actions\CreateAction::make(), // Mengizinkan pembuatan item baru
            ])
            // Aksi-aksi yang tersedia di setiap baris tabel
            ->actions([
                Tables\Actions\EditAction::make(), // Mengizinkan pengeditan item
                Tables\Actions\DeleteAction::make(), // Mengizinkan penghapusan item
            ])
            // Aksi-aksi massal yang tersedia (untuk item yang dipilih)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(), // Mengizinkan penghapusan banyak item
                ]),
            ]);
    }
}