<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComplaintResource\Pages;
use App\Models\Complaint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ComplaintResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Layanan Pelanggan';
    protected static ?string $modelLabel = 'Keluhan Pelanggan';
    protected static ?string $pluralModelLabel = 'Keluhan Pelanggan';

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
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Informasi Pelanggan')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('customer_name')
                        ->label('Nama Pelanggan')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\TextInput::make('order_reference')
                        ->label('Referensi Order')
                        ->placeholder('-')
                        ->disabled()
                        ->dehydrated(false)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Detail Keluhan')
                ->schema([
                    Forms\Components\Textarea::make('message')
                        ->label('Pesan Keluhan')
                        ->rows(7)
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Textarea::make('admin_reply')
                        ->label('Balasan Admin')
                        ->rows(5)
                        ->maxLength(5000)
                        ->nullable(),
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(Complaint::statusOptions())
                        ->required()
                        ->native(false),
                ]),
        ];
    }

    private static function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('customer_name')
                ->label('Nama Pelanggan')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('email')
                ->label('Email')
                ->searchable()
                ->toggleable(),
            Tables\Columns\TextColumn::make('order_reference')
                ->label('Referensi Order')
                ->searchable()
                ->placeholder('-'),
            Tables\Columns\TextColumn::make('message')
                ->label('Pesan')
                ->limit(60)
                ->wrap()
                ->searchable(),
            Tables\Columns\TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    Complaint::STATUS_OPEN => 'gray',
                    Complaint::STATUS_IN_PROGRESS => 'info',
                    Complaint::STATUS_RESOLVED => 'success',
                    default => 'gray',
                })
                ->formatStateUsing(fn (string $state): string => Complaint::statusOptions()[$state] ?? $state)
                ->sortable(),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Dikirim')
                ->dateTime()
                ->sortable(),
        ];
    }

    private static function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('status')
                ->label('Status')
                ->options(Complaint::statusOptions()),
            Tables\Filters\Filter::make('unresolved')
                ->label('Belum Selesai')
                ->query(fn (Builder $query): Builder => $query->whereIn('status', [
                    Complaint::STATUS_OPEN,
                    Complaint::STATUS_IN_PROGRESS,
                ])),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComplaints::route('/'),
            'view' => Pages\ViewComplaint::route('/{record}'),
            'edit' => Pages\EditComplaint::route('/{record}/edit'),
        ];
    }
}
