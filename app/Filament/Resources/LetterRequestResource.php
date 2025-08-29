<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LetterRequestResource\Pages;
use App\Filament\Resources\LetterRequestResource\RelationManagers;
use App\Models\LetterRequest;
use App\Models\OutgoingLetter;
use Asmit\FilamentUpload\Forms\Components\AdvancedFileUpload;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Forms;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class LetterRequestResource extends Resource
{
    protected static ?string $model = LetterRequest::class;

    protected static ?string $navigationGroup = 'Manajemen Dokumen';

    protected static ?string $navigationIcon = 'heroicon-o-document-plus';

    protected static ?string $label = 'Permohonan Surat';

    protected static ?string $navigationLabel = 'Permohonan Surat';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Permintaan Surat')
                    ->description('Silakan isi detail permintaan surat Anda.')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->label('Nama Pemohon')
                            ->placeholder('Contoh: Permohonan Peminjaman Ruang Rapat')
                            ->required(),
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
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
                Forms\Components\Hidden::make('outgoing_letter_id'),
                Forms\Components\Hidden::make('status')
                    ->default('Menunggu'),
            ]);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->user_type !== 'admin';
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
                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->selectablePlaceholder('Pilih Status')
                    ->options([
                        'Menunggu' => 'Menunggu',
                        'Diproses' => 'Diproses',
                        'Selesai' => 'Selesai',
                        'Ditolak' => 'Ditolak'
                    ]),
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
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Action::make('buatSuratKeluar')
                        ->label('Buat Surat Keluar')
                        ->visible(fn(LetterRequest $record) => $record->status === 'Menunggu')
                        ->form([
                            Forms\Components\TextInput::make('letter_number')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateHydrated(function ($state, $component) {}),
                            Forms\Components\DatePicker::make('outgoing_date')
                                ->default(now())
                                ->required(),
                            AdvancedFileUpload::make('file_path')
                                ->label('File Surat')
                                ->acceptedFileTypes(['application/pdf'])
                                ->disk('public')
                                ->directory('outgoing_letters')
                                ->required()
                                ->getUploadedFileNameForStorageUsing(function (Get $get, $file) {
                                    $letterNumber = $get('letter_number');
                                    $extension = $file->getClientOriginalExtension();
                                    $safeLetterNumber = Str::slug($letterNumber);
                                    return $safeLetterNumber . '.' . $extension;
                                }),
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
