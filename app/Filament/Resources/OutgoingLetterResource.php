<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutgoingLetterResource\Pages;
use App\Filament\Resources\OutgoingLetterResource\RelationManagers;
use App\Models\OutgoingLetter;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OutgoingLetterResource extends Resource
{
    protected static ?string $model = OutgoingLetter::class;

    protected static ?string $navigationGroup = 'Manajemen Dokumen';

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $label = 'Surat Keluar';

    protected static ?string $navigationLabel = 'Surat Keluar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Surat')
                    ->description('Masukkan detail utama dari surat.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('letter_number')
                                    ->label('Nomor Surat')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: 123/A/2023')
                                    ->live(onBlur: true)
                                    ->afterStateHydrated(function ($state, $component) {}),
                                DatePicker::make('outgoing_date')
                                    ->label('Tanggal Keluar')
                                    ->default(now())
                                    ->required(),
                                TextInput::make('recipient')
                                    ->label('Penerima')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Nama atau Instansi Penerima'),
                                TextInput::make('subject')
                                    ->label('Perihal')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Undangan Rapat'),
                            ]),
                    ]),
                Section::make('Unggah Dokumen')
                    ->description('Silakan unggah file PDF surat.')
                    ->schema([
                        AdvancedFileUpload::make('file_path')
                            ->label('File PDF')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('outgoing_letters')
                            ->disk('public')
                            ->required()
                            ->placeholder('Tarik dan lepas file di sini atau klik untuk mengunggah.')
                            ->getUploadedFileNameForStorageUsing(function (Get $get, $file) {
                                $letterNumber = $get('letter_number');
                                $extension = $file->getClientOriginalExtension();
                                $safeLetterNumber = Str::slug($letterNumber);
                                return $safeLetterNumber . '.' . $extension;
                            }),
                    ]),
                Hidden::make('user_id')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('User.name')
                    ->label('Pembuat')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('letter_number')
                    ->label('Nomor Surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Perihal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('recipient')
                    ->label('Penerima')
                    ->searchable(),
                Tables\Columns\TextColumn::make('outgoing_date')
                    ->label('Tanggal Keluar')
                    ->sortable(),
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
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Action::make('download')
                        ->label('Unduh')
                        ->icon('heroicon-o-document-arrow-down')
                        ->action(function ($record) {
                            return Storage::disk('public')->download($record->file_path);
                        })
                        ->visible(fn($record) => !empty($record->file_path))
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOutgoingLetters::route('/'),
        ];
    }
}
