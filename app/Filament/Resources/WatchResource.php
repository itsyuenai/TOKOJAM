<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WatchResource\Pages; // Sesuaikan namespace pages
use App\Models\Watch; // Ganti dari App\Models\Jam ke App\Models\Watch
use App\Models\Category; // Import model Category
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str; // Import Str untuk slug jika diperlukan di sini

class WatchResource extends Resource // Ganti nama kelas dari JamResource ke WatchResource
{
    protected static ?string $model = Watch::class; // Pastikan model yang digunakan adalah Watch

    protected static ?string $navigationIcon = 'heroicon-o-watch';
    protected static ?string $navigationGroup = 'Produk & Inventori';
    protected static ?string $modelLabel = 'Jam Tangan'; // Sesuaikan label
    protected static ?string $pluralModelLabel = 'Katalog Jam Tangan'; // Sesuaikan label

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Jam Tangan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('category_id') // Tambahkan field kategori
                    ->label('Kategori')
                    ->relationship('category', 'name') // Relasi ke model Category dengan kolom 'name'
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('price')
                    ->label('Harga Jual')
                    ->numeric()
                    ->required()
                    ->prefix('Rp')
                    ->inputMode('decimal')
                    ->minValue(0.01), // Harga tidak boleh nol atau negatif
                Forms\Components\FileUpload::make('image') // Tambahkan field gambar
                    ->label('Gambar Produk')
                    ->image() // Memastikan hanya file gambar yang diunggah
                    ->directory('watch-images') // Simpan gambar di storage/app/public/watch-images
                    ->nullable() // Gambar tidak wajib
                    ->disk('public'), // Menggunakan disk 'public'
                Forms\Components\TextInput::make('stock')
                    ->label('Stok Saat Ini')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->readOnly() // Stok akan diupdate melalui Barang Masuk/Keluar/Order
                    ->minValue(0), // Stok tidak boleh negatif
                Forms\Components\TextInput::make('sku')
                    ->label('SKU (Stock Keeping Unit)')
                    ->unique(ignoreRecord: true) // SKU harus unik, kecuali saat edit record yang sama
                    ->maxLength(255)
                    ->nullable(), // SKU bisa opsional
                Forms\Components\TextInput::make('rating') // Tambahkan field rating
                    ->label('Rating (0.0 - 5.0)')
                    ->numeric()
                    ->step(0.1) // Langkah 0.1 untuk nilai desimal
                    ->minValue(0)
                    ->maxValue(5)
                    ->default(0),
                Forms\Components\TextInput::make('reviews_count') // Tambahkan field jumlah review
                    ->label('Jumlah Review')
                    ->numeric()
                    ->minValue(0)
                    ->default(0)
                    ->readOnly(), // Jumlah review biasanya diupdate otomatis oleh sistem (misal: saat review baru ditambahkan)
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->maxLength(65535)
                    ->columnSpanFull(), // Menggunakan seluruh lebar kolom
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image') // Tampilkan gambar di tabel
                    ->label('Gambar')
                    ->disk('public') // Pastikan disk 'public' dikonfigurasi
                    ->circular() // Bentuk gambar lingkaran
                    ->defaultImageUrl(url('/images/placeholder.png')), // Gambar placeholder jika tidak ada
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Jam Tangan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name') // Tampilkan nama kategori
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga Jual')
                    ->money('IDR') // Format sebagai mata uang Rupiah
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->sortable()
                    ->color(fn (int $state): string => match (true) { // Beri warna berdasarkan stok
                        $state < 5 => 'danger', // Stok rendah
                        $state < 20 => 'warning', // Stok sedang
                        default => 'success', // Stok aman
                    }),
                Tables\Columns\TextColumn::make('rating') // Tampilkan rating
                    ->label('Rating')
                    ->numeric()
                    ->formatStateUsing(fn (float $state) => number_format($state, 1)),
                Tables\Columns\TextColumn::make('reviews_count') // Tampilkan jumlah review
                    ->label('Reviews')
                    ->numeric(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id') // Tambahkan filter berdasarkan kategori
                    ->label('Filter Kategori')
                    ->options(Category::all()->pluck('name', 'id')->toArray()),
                Tables\Filters\Filter::make('stock_level') // Contoh filter kustom stok
                    ->form([
                        Forms\Components\Select::make('level')
                            ->options([
                                'low' => 'Stok Rendah (< 5)',
                                'medium' => 'Stok Sedang (5-19)',
                                'high' => 'Stok Aman (>= 20)',
                            ])
                            ->label('Level Stok'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['level'] === 'low', fn (Builder $query) => $query->where('stock', '<', 5))
                            ->when($data['level'] === 'medium', fn (Builder $query) => $query->whereBetween('stock', [5, 19]))
                            ->when($data['level'] === 'high', fn (Builder $query) => $query->where('stock', '>=', 20));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            // Anda bisa menambahkan relation manager di sini jika ingin mengelola barang masuk/keluar
            // atau order item langsung dari halaman detail Watch.
            // Contoh: RelationManagers\BarangMasukRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            // Pastikan nama halaman sesuai dengan nama Resource yang baru
            'index' => Pages\ListWatches::route('/'),
            'create' => Pages\CreateWatch::route('/create'),
            'view' => Pages\ViewWatch::route('/{record}'),
            'edit' => Pages\EditWatch::route('/{record}/edit'),
        ];
    }
}