<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Watch; // Menggunakan model Watch
use App\Models\BarangKeluar; // Untuk mencatat barang keluar saat order dibuat
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB; // Untuk transaksi database

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Laporan & Transaksi';
    protected static ?string $modelLabel = 'Pesanan';
    protected static ?string $pluralModelLabel = 'Laporan Pesanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer_name')
                    ->label('Nama Pelanggan')
                    ->maxLength(255)
                    ->nullable(),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Total Jumlah')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->inputMode('decimal')
                    ->readOnly() // Jumlah dihitung dari item pesanan
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->label('Status Pesanan')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->default('pending')
                    ->required(),

                // Repeater untuk item pesanan
                Forms\Components\Repeater::make('items')
                    ->relationship('items') // Relasi ke OrderItem
                    ->schema([
                        Forms\Components\Select::make('jam_id') // Relasi ke model Watch
                            ->label('Produk Jam Tangan')
                            ->relationship('jam', 'name') // 'jam' adalah nama relasi di model OrderItem ke Watch
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive() // Penting agar perubahan ini memicu update harga dan validasi stok
                            ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                // Saat produk dipilih, set harga per item ke harga jual produk
                                $watch = Watch::find($state);
                                if ($watch) {
                                    $set('price_per_item', $watch->price);
                                    // Validasi awal untuk quantity
                                    if ($get('quantity') && $get('quantity') > $watch->stock) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Stok tidak mencukupi untuk ' . $watch->name)
                                            ->danger()
                                            ->send();
                                        $set('quantity', $watch->stock); // Set ke stok maksimal
                                    }
                                }
                            }),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Kuantitas')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->reactive()
                            ->rules([ // Validasi stok saat kuantitas diubah
                                function (Forms\Get $get, Forms\Set $set, $state) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get, $set, $state) {
                                        $watchId = $get('jam_id');
                                        if (!$watchId) {
                                            return; // Lewati validasi jika jam belum dipilih
                                        }
                                        $watch = Watch::find($watchId);
                                        if ($watch && $value > $watch->stock) {
                                            $fail("Kuantitas ({$value}) melebihi stok yang tersedia ({$watch->stock}) untuk " . $watch->name . ".");
                                            // Opsional: set kembali kuantitas ke stok maksimal
                                            // $set($attribute, $watch->stock);
                                        }
                                    };
                                },
                            ]),
                        Forms\Components\TextInput::make('price_per_item')
                            ->label('Harga per Unit')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->inputMode('decimal')
                            ->readOnly(), // Harga ini akan diisi otomatis dari harga jual jam
                    ])
                    ->columns(3) // Tampilan 3 kolom di repeater
                    ->defaultItems(1) // Minimal 1 item saat membuat pesanan baru
                    ->cloneable() // Bisa di-duplikasi
                    ->minItems(1) // Minimal satu item di keranjang
                    ->reorderableWithButtons() // Bisa diatur ulang urutannya
                    ->collapsible() // Bisa diciutkan
                    ->grid(2) // 2 kolom di tampilan repeater
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                        // Hitung ulang total amount setiap kali item di repeater berubah
                        $total = 0;
                        foreach ($get('items') as $item) {
                            $total += ($item['quantity'] ?? 0) * ($item['price_per_item'] ?? 0);
                        }
                        $set('total_amount', $total);
                    }),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Nama Pelanggan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Jumlah')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Pesanan')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('to'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                            ->when($data['to'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
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
            RelationManagers\ItemsRelationManager::class, // Mengelola OrderItem di dalam detail Order
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    // Hook untuk update stok Jam Tangan saat Order dibuat
    public static function afterCreate(Order $record): void
    {
        DB::transaction(function () use ($record) {
            foreach ($record->items as $item) {
                $watch = Watch::find($item->jam_id);
                if ($watch) {
                    if ($watch->stock < $item->quantity) {
                        throw new \Exception("Stok tidak mencukupi untuk " . $watch->name);
                    }
                    $watch->decrement('stock', $item->quantity);
                    // Catat sebagai barang keluar
                    BarangKeluar::create([
                        'jam_id' => $watch->id,
                        'quantity' => $item->quantity,
                        'customer_name' => $record->customer_name,
                        'sale_price' => $item->price_per_item,
                        'exit_date' => now(),
                    ]);
                }
            }
        });
    }

    // Hook untuk menangani update order
    // Ini akan lebih kompleks karena harus membandingkan item lama dan baru.
    // Untuk kesederhanaan, disarankan untuk tidak mengedit order yang sudah diselesaikan/memodifikasi stok di sini,
    // melainkan melalui BarangMasuk/BarangKeluar terpisah atau fitur pembatalan/pengembalian yang spesifik.
    // Jika Anda mengubah status menjadi 'cancelled', Anda bisa mengembalikan stok di sini.
    public static function afterUpdate(Order $record, array $oldData): void
    {
        if ($record->isDirty('status') && $record->status === 'cancelled') {
            DB::transaction(function () use ($record) {
                foreach ($record->items as $item) {
                    $watch = Watch::find($item->jam_id);
                    if ($watch) {
                        $watch->increment('stock', $item->quantity);
                        // Opsional: hapus record BarangKeluar terkait atau buat BarangMasuk baru
                        // jika pembatalan order berarti barang dikembalikan ke stok.
                    }
                }
            });
        }
    }

    // Hook untuk mengembalikan stok saat order dihapus
    public static function afterDelete(Order $record): void
    {
        DB::transaction(function () use ($record) {
            foreach ($record->items as $item) {
                $watch = Watch::find($item->jam_id);
                if ($watch) {
                    $watch->increment('stock', $item->quantity);
                    // Opsional: hapus record BarangKeluar terkait jika order dihapus total
                }
            }
        });
    }
}