<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LetterRequestResource\Pages;
use App\Filament\Resources\LetterRequestResource\RelationManagers;
use App\Models\LetterRequest;
use App\Models\OutgoingLetter;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LetterRequestResource extends Resource
{
    protected static ?string $model = LetterRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Permohonan Surat';

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
                // Kolom ini disembunyikan dari user karena hanya untuk admin
                Forms\Components\Hidden::make('outgoing_letter_id'),
                // Kolom ini disembunyikan dari user karena diisi otomatis
                Forms\Components\Hidden::make('status')
                    ->default('Menunggu'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('User.name')
                    ->label('Nama Pemohon')
                    ->sortable(),
                Tables\Columns\TextColumn::make('OutgoingLetter.letter_number')
                    ->label('Nomor Surat Keluar')
                    ->numeric()
                    ->sortable(),
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
                Action::make('buatSuratKeluar')
                    ->label('Buat Surat Keluar')
                    ->visible(fn(LetterRequest $record) => $record->status === 'Menunggu')
                    ->form([
                        Forms\Components\TextInput::make('letter_number')
                            ->required(),
                        Forms\Components\DatePicker::make('outgoing_date')
                            ->default(now())
                            ->required(),
                        AdvancedFileUpload::make('file_path')
                            ->label('File Surat')
                            ->acceptedFileTypes(['application/pdf'])
                            ->disk('public')
                            ->directory('outgoing_letters')
                            ->required(),
                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id()),
                    ])
                    ->action(function (array $data, LetterRequest $record) {
                        // 1. Buat record baru di SuratKeluar
                        $suratKeluar = OutgoingLetter::create([
                            'letter_number' => $data['letter_number'],
                            'outgoing_date' => $data['outgoing_date'],
                            'subject' => $record->subject,
                            'recipient' => $record->user->name,
                            'user_id' => $data['user_id'],
                            'file_path' => $data['file_path'],
                        ]);

                        // 2. Update record LetterRequest
                        $record->update([
                            'outgoing_letters_id' => $suratKeluar->id,
                            'status' => 'Selesai',
                        ]);
                    }),
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
