<?php

namespace App\Filament\Pages;

use App\Jobs\SendBulkNotification;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Pages\Page;

class SendNotification extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-megaphone';

    protected static string | \UnitEnum | null $navigationGroup = 'Broadcasting';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.send-notification';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('title')
                    ->label('Notification Title')
                    ->required()
                    ->maxLength(255),
                Textarea::make('body')
                    ->label('Message Body')
                    ->required()
                    ->maxLength(5000)
                    ->rows(6),
                Radio::make('target')
                    ->label('Send to')
                    ->options([
                        '' => 'All Users',
                        'customer' => 'Customers Only',
                        'owner' => 'Restaurant Owners Only',
                    ])
                    ->default('')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $this->validate();

        SendBulkNotification::dispatch(
            $this->data['title'],
            $this->data['body'],
            $this->data['target'] ?: null,
        );

        Notification::make()
            ->title('Broadcast queued')
            ->body('Your notification has been queued and will be sent to all selected users.')
            ->success()
            ->send();

        $this->form->fill();
    }
}
