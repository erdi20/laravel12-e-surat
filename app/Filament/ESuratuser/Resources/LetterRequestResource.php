<?php

namespace App\Filament\ESuratuser\Resources;

use App\Filament\ESuratuser\Resources\LetterRequestResource\Pages;
use App\Filament\ESuratuser\Resources\LetterRequestResource\RelationManagers;
use App\Models\LetterRequest;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;

class LetterRequestResource extends Resource
{
    protected static ?string $model = LetterRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Permintaan Surat')
                    ->description('Silakan isi detail permintaan surat Anda.')
                    ->schema([
                        Forms\Components\TextInput::make('subject')
                            ->label('Perihal Surat')
                            ->placeholder('Contoh: Permohonan Peminjaman Ruang Rapat')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('purpose')
                            ->label('Tujuan Surat')
                            ->placeholder('Contoh: Kepada Kepala Bagian Pemasaran')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi/Catatan Tambahan')
                            ->placeholder('Sertakan detail atau lampiran yang diperlukan (jika ada).')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                // Kolom ini disembunyikan dari user karena diisi otomatis
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
                Forms\Components\Hidden::make('status')
                    ->default('Menunggu'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('user_id')
                //     ->numeric()
                //     ->sortable(),
                Tables\Columns\TextColumn::make('outgoing_letters_id')
                    ->visible(false),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Perihal Surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purpose')
                    ->label('Tujuan Surat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status'),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Action::make('download')
                    ->label('Unduh Surat')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (LetterRequest $record) {
                        if ($record->OutgoingLetter) {
                            return Storage::disk('public')->download($record->OutgoingLetter->file_path);
                        }
                        \Filament\Notifications\Notification::make()
                            ->title('Gagal mengunduh')
                            ->body('File surat tidak ditemukan.')
                            ->danger()
                            ->send();
                    })
                    ->visible(fn(LetterRequest $record) => $record->status === 'Selesai'),
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
            'index' => Pages\ManageLetterRequests::route('/'),
        ];
    }
}
