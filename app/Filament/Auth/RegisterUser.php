<?php
namespace App\Filament\Auth;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Pages\Auth\Register;

class RegisterUser extends Register
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                Hidden::make('user_type')
                    ->default('member')
                // ->dehydrated(false)
            ])
            ->statePath('data');
    }
}
