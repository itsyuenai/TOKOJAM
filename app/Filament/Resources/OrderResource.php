<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Watch;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Manajemen Pesanan';
    protected static ?string $modelLabel = 'Pesanan';
    protected static ?string $pluralModelLabel = 'Daftar Pesanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(self::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(self::getTableColumns())
            ->filters(self::getTableFilters())
            ->actions(self::getTableActions())
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function getFormSchema(): array
    {
        return [
            self::getOrderDetailsSection(),
            self::getOrderItemsSection(),
            self::getOrderSummarySection(),
        ];
    }

    private static function getOrderDetailsSection(): Section
    {
        return Section::make('Detail Pesanan')
            ->columns(2)
            ->schema([
                TextInput::make('customer_name')
                    ->label('Nama Pelanggan')
                    ->maxLength(255)
                    ->nullable()
                    ->default('Guest')
                    ->columnSpan(1),
                Select::make('status')
                    ->label('Status Pesanan')
                    ->options(self::getStatusOptions())
                    ->default('pending')
                    ->required()
                    ->columnSpan(1),
            ]);
    }

    private static function getOrderItemsSection(): Section
    {
        return Section::make('Item Pesanan')
            ->description('Tambahkan jam tangan yang dipesan.')
            ->schema([
                self::getOrderItemsRepeater(),
            ]);
    }

    private static function getOrderItemsRepeater(): Repeater
    {
        return Repeater::make('orderItems')
            ->relationship('orderItems')
            ->label('Daftar Item')
            ->schema(self::getOrderItemSchema())
            ->columns(6)
            ->defaultItems(1)
            ->minItems(1)
            ->addable()
            ->cloneable()
            ->deletable()
            ->reorderable()
            ->live()
            ->afterStateUpdated(function (Get $get, Set $set) {
                $set('total_amount', self::calculateTotalAmount($get('orderItems')));
            })
            ->columnSpanFull();
    }

    private static function getOrderItemSchema(): array
    {
        return [
            Select::make('jam_id')
                ->label('Pilih Jam Tangan')
                ->options(fn (): array => Watch::query()->pluck('name', 'id')->all())
                ->searchable()
                ->preload()
                ->required()
                ->reactive()
                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                    if ($state) {
                        $watch = Watch::find($state);

                        if ($watch) {
                            $quantity = (float) ($get('quantity') ?? 1);

                            $set('price_per_item', $watch->price);
                            $set('item_total', $quantity * $watch->price);
                        }
                    }

                    $set('../../total_amount', self::calculateTotalAmount($get('../../orderItems') ?? []));
                })
                ->columnSpan(2),

            TextInput::make('quantity')
                ->label('Kuantitas')
                ->numeric()
                ->required()
                ->default(1)
                ->minValue(1)
                ->reactive()
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                    $pricePerItem = (float) ($get('price_per_item') ?? 0);

                    $set('item_total', (float) $state * $pricePerItem);
                    $set('../../total_amount', self::calculateTotalAmount($get('../../orderItems') ?? []));
                })
                ->columnSpan(1),

            TextInput::make('price_per_item')
                ->label('Harga per Unit')
                ->numeric()
                ->prefix('Rp')
                ->disabled()
                ->dehydrated(true)
                ->extraAttributes([
                    'class' => 'font-medium text-gray-700 dark:text-gray-300',
                ])
                ->columnSpan(2),

            TextInput::make('item_total')
                ->label('Subtotal Item')
                ->numeric()
                ->prefix('Rp')
                ->inputMode('decimal')
                ->disabled()
                ->dehydrated(false)
                ->live()
                ->afterStateHydrated(function (Get $get, Set $set) {
                    $pricePerItem = (float) ($get('price_per_item') ?? 0);
                    $quantity = (float) ($get('quantity') ?? 0);

                    $set('item_total', $pricePerItem * $quantity);
                })
                ->formatStateUsing(function ($state, Get $get) {
                    $pricePerItem = (float) ($get('price_per_item') ?? 0);
                    $quantity = (float) ($get('quantity') ?? 0);

                    return $pricePerItem * $quantity;
                })
                ->extraAttributes(['class' => 'font-bold text-lg text-primary-600 dark:text-primary-400'])
                ->columnSpan(1),
        ];
    }

    private static function getOrderSummarySection(): Section
    {
        return Section::make('Ringkasan Pesanan')
            ->schema([
                TextInput::make('total_amount')
                    ->label('Total Harga Keseluruhan')
                    ->numeric()
                    ->prefix('Rp')
                    ->inputMode('decimal')
                    ->disabled()
                    ->dehydrated(true)
                    ->default(0)
                    ->live(debounce: 500)
                    ->afterStateHydrated(function (Get $get, Set $set) {
                        $set('total_amount', self::calculateTotalAmount($get('orderItems')));
                    })
                    ->extraAttributes([
                        'class' => 'text-right font-bold text-3xl text-success-600 dark:text-success-400',
                    ]),
            ])
            ->columnSpanFull();
    }

    /**
     * Helper method untuk menghitung total amount dari array order items.
     */
    protected static function calculateTotalAmount(?array $items): float
    {
        $total = 0;

        if (is_array($items)) {
            foreach ($items as $item) {
                if (isset($item['jam_id']) && isset($item['quantity'])) {
                    $watch = Watch::find($item['jam_id']);

                    if ($watch) {
                        $total += ($watch->price * $item['quantity']);
                    }
                }
            }
        }

        return (float) $total;
    }

    private static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('id')
                ->label('ID Pesanan')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('customer_name')
                ->label('Nama Pelanggan')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('total_amount')
                ->label('Total Harga')
                ->money('IDR')
                ->sortable(),
            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'pending' => 'warning',
                    'completed' => 'success',
                    'cancelled' => 'danger',
                    'processing' => 'info',
                    default => 'gray',
                })
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Tanggal Pesanan')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\TextColumn::make('updated_at')
                ->label('Terakhir Diperbarui')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    private static function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('status')
                ->label('Filter Status')
                ->options(self::getStatusOptions()),
            Tables\Filters\Filter::make('created_at')
                ->form([
                    Forms\Components\DatePicker::make('created_from')
                        ->label('Dari Tanggal'),
                    Forms\Components\DatePicker::make('created_until')
                        ->label('Sampai Tanggal'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['created_from'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        );
                }),
        ];
    }

    private static function getTableActions(): array
    {
        return [
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
            self::copyOrderAction(),
        ];
    }

    private static function copyOrderAction(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('copyOrder')
            ->label('Salin Pesanan')
            ->icon('heroicon-o-document-duplicate')
            ->color('info')
            ->action(function (Order $record): void {
                $newOrder = $record->replicate();
                $newOrder->customer_name = $record->customer_name . ' (Salinan)';
                $newOrder->status = 'pending';
                $newOrder->save();

                foreach ($record->orderItems as $item) {
                    $newItem = $item->replicate();
                    $newItem->order_id = $newOrder->id;
                    $newItem->jam_id = $item->jam_id;
                    $newItem->save();
                }

                Notification::make()
                    ->title('Pesanan berhasil disalin!')
                    ->body("Pesanan #{$record->id} berhasil disalin menjadi Pesanan #{$newOrder->id}.")
                    ->success()
                    ->send();

                redirect(static::getUrl('edit', ['record' => $newOrder->id]));
            })
            ->requiresConfirmation()
            ->tooltip('Salin pesanan ini untuk membuat pesanan baru berdasarkan yang sudah ada.');
    }

    private static function getStatusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            'processing' => 'Diproses',
        ];
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'customer_name',
            'id',
        ];
    }
}
