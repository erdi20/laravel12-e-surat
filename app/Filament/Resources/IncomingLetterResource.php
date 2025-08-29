<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomingLetterResource\Pages;
use App\Filament\Resources\IncomingLetterResource\RelationManagers;
use App\Models\IncomingLetter;
use Asmit\FilamentUpload\Enums\PdfViewFit;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Directory;

class IncomingLetterResource extends Resource
{
    protected static ?string $model = IncomingLetter::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Surat Masuk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detail Surat Masuk')
                    ->description('Lengkapi informasi mengenai surat yang Anda terima.')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('letter_number')
                                    ->label('Nomor Surat')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: 123/IX/2023'),
                                DatePicker::make('incoming_date')
                                    ->label('Tanggal Surat Masuk')
                                    ->default(now())
                                    ->required(),
                            ]),
                        TextInput::make('sender')
                            ->label('Pengirim')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Nama atau Instansi Pengirim'),
                        TextInput::make('subject')
                            ->label('Perihal')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Pemberitahuan Rapat Tahunan'),
                    ]),
                Section::make('Unggah Dokumen')
                    ->description('Silakan unggah dokumen surat dalam format PDF.')
                    ->schema([
                        AdvancedFileUpload::make('file_path')
                            ->label('File PDF')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('incoming_letters')
                            ->required()
                            ->placeholder('Tarik dan lepas file di sini atau klik untuk mengunggah.')
                            ->pdfPreviewHeight(400)
                            ->pdfDisplayPage(1)
                            ->pdfToolbar(true)
                            ->pdfZoomLevel(100)
                            ->pdfNavPanes(true),
                    ]),
                Hidden::make('user_id')
                    ->default(fn() => auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('User.name')
                    ->label('Pembuat')
                    ->sortable(),
                Tables\Columns\TextColumn::make('letter_number')
                    ->label('Nomor Surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Perihal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sender')
                    ->label('Pengirim')
                    ->searchable(),
                Tables\Columns\TextColumn::make('incoming_date')
                    ->label('Tanggal Surat Masuk')
                    // ->date()
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
            'index' => Pages\ManageIncomingLetters::route('/'),
        ];
    }
}
