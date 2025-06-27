<?php
// File: app/Filament/Resources/DoctorScheduleResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorScheduleResource\Pages;
use App\Models\DoctorSchedule;
use App\Models\Service;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class DoctorScheduleResource extends Resource
{
    protected static ?string $model = DoctorSchedule::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    
    protected static ?string $navigationGroup = 'Administrasi';
    
    protected static ?string $navigationLabel = 'Jadwal Dokter';
    
    protected static ?string $modelLabel = 'Jadwal Dokter';
    
    protected static ?string $pluralModelLabel = 'Jadwal Dokter';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dokter')
                    ->description('Data dokter dan poli praktik')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('doctor_name')
                                    ->label('Nama Dokter')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('dr. Nama Dokter')
                                    ->helperText('Masukkan nama lengkap dokter'),
                                    
                                Forms\Components\Select::make('service_id')
                                    ->label('Poli')
                                    ->required()
                                    ->relationship('service', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Pilih poli/layanan dari data layanan yang sudah ada')
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nama Poli/Layanan')
                                            ->required(),
                                        Forms\Components\TextInput::make('prefix')
                                            ->label('Prefix Antrian')
                                            ->required()
                                            ->default('A')
                                            ->maxLength(3),
                                        Forms\Components\TextInput::make('padding')
                                            ->label('Padding Nomor')
                                            ->required()
                                            ->numeric()
                                            ->default(3),
                                        Forms\Components\Toggle::make('is_active')
                                            ->label('Status Aktif')
                                            ->default(true),
                                    ])
                                    ->createOptionUsing(function (array $data) {
                                        $service = Service::create($data);
                                        return $service->id;
                                    }),
                            ]),
                    ]),

                Forms\Components\Section::make('Jadwal Praktik')
                    ->description('Atur hari dan jam praktik dokter')
                    ->schema([
                        Forms\Components\CheckboxList::make('days')
                            ->label('Hari Praktik')
                            ->options([
                                'monday' => 'Senin',
                                'tuesday' => 'Selasa',
                                'wednesday' => 'Rabu',
                                'thursday' => 'Kamis',
                                'friday' => 'Jumat',
                                'saturday' => 'Sabtu',
                                'sunday' => 'Minggu',
                            ])
                            ->columns(3)
                            ->required()
                            ->default(['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])
                            ->helperText('Pilih hari-hari praktik dokter'),
                            
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TimePicker::make('start_time')
                                    ->label('Jam Mulai')
                                    ->required()
                                    ->seconds(false)
                                    ->format('H:i')
                                    ->default('08:00'),
                                    
                                Forms\Components\TimePicker::make('end_time')
                                    ->label('Jam Selesai')
                                    ->required()
                                    ->seconds(false)
                                    ->format('H:i')
                                    ->default('16:00')
                                    ->after('start_time'),
                            ]),
                            
                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Jadwal hanya berlaku jika status aktif'),
                    ]),

                Forms\Components\Hidden::make('day_of_week'),
                Forms\Components\Hidden::make('user_id'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('doctor_name')
                    ->label('Nama Dokter')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->limit(30),
                    
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Poli')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->limit(20),
                    
                Tables\Columns\TextColumn::make('formatted_days')
                    ->label('Hari Praktik')
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('time_range')
                    ->label('Jam Praktik')
                    ->badge(),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_id')
                    ->label('Poli')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload(),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua Jadwal')
                    ->trueLabel('Hanya Aktif')
                    ->falseLabel('Hanya Tidak Aktif'),
            ])
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
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDoctorSchedules::route('/'),
            'create' => Pages\CreateDoctorSchedule::route('/create'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count() ?: null;
    }
    
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'success';
    }
}