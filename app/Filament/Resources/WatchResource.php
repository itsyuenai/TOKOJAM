<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WatchResource\Pages;
use App\Models\Watch;
use App\Models\WatchCategory; // <<< CHANGE THIS LINE from App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

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
                    ->maxLength(255),

                // <<< UNCOMMENT AND USE THIS CORRECT OPTION 2
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name') // 'category' refers to the relationship method in Watch model
                    ->required(),
                // <<< REMOVE OR COMMENT OUT THIS INCORRECT OPTION 1
                // Forms\Components\TextInput::make('category_name')
                //     ->label('Kategori')
                //     ->required()
                //     ->maxLength(255)
                //     ->placeholder('Ketik nama kategori...')
                //     ->datalist(
                //         Category::pluck('name')->unique()->toArray() // Autocomplete dari kategori yang ada
                //     ),

                Forms\Components\TextInput::make('price')
                    ->label('Harga')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                Forms\Components\TextInput::make('stock')
                    ->label('Stok')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->label('Gambar Produk')
                    ->image()
                    ->directory('product-images')
                    ->nullable(),
                // If you have image_url as a separate column, you might handle it differently
                // Forms\Components\TextInput::make('image_url')
                //     ->label('URL Gambar')
                //     ->maxLength(255)
                //     ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Jam Tangan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name') // <<< CHANGE THIS LINE to category.name
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
                Tables\Columns\ImageColumn::make('image')
                    ->label('Gambar')
                    ->circular(),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
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
                    ->relationship('category', 'name'), // 'category' refers to the relationship method in Watch model
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
        ];
    }
}