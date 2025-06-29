<?php

namespace App\Filament\Resources;
 
use App\Filament\Resources\WatchResource\Pages;
use App\Models\Watch;
use App\Models\WatchCategory; // Pastikan ini benar, sesuaikan dengan nama model kategori Anda
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str; // Pastikan ini ada jika Anda menggunakan Str

class WatchResource extends Resource
{
    protected static ?string $model = Watch::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Produk & Inventori';
    protected static ?string $modelLabel = 'Jam Tangan';
    protected static ?string $pluralModelLabel = 'Katalog Jam Tangan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Jam Tangan')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true), // Tambahkan unique agar tidak ada nama jam tangan yang sama

                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name') // 'category' refers to the relationship method in Watch model
                    ->searchable() // Mempermudah pencarian kategori
                    ->preload()    // Memuat semua kategori di awal (hati-hati jika kategori sangat banyak)
                    ->createOptionForm([ // Opsi untuk membuat kategori baru langsung dari sini
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255)
                            ->unique(table: WatchCategory::class),
                    ])
                    ->createOptionUsing(function (array $data) {
                        return WatchCategory::create($data)->id;
                    })
                    ->required(),

                Forms\Components\TextInput::make('price')
                    ->label('Harga')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->inputMode('decimal') // Untuk input desimal yang lebih baik
                    ->minValue(0.01), // Harga minimal

                Forms\Components\TextInput::make('stock')
                    ->label('Stok')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0), // Stok tidak boleh negatif

                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->nullable(), // Deskripsi bisa kosong

                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->nullable(), // SKU bisa kosong

                Forms\Components\FileUpload::make('image')
                    ->label('Gambar Produk')
                    ->image()
                    ->directory('product-images') // Simpan di storage/app/public/product-images
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Gambar')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-watch.png')), // Gambar default jika tidak ada
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Jam Tangan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Sembunyikan secara default
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
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->preload()
                    ->searchable(),
                Tables\Filters\Filter::make('stock_level')
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
            'index' => Pages\ListWatches::route('/'),
            'create' => Pages\CreateWatch::route('/create'),
            'view' => Pages\ViewWatch::route('/{record}'),
            'edit' => Pages\EditWatch::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'sku',
        ];
    }
}