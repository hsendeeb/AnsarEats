<?php

namespace App\Filament\Resources\Restaurants\Tables;

use App\Mail\SubscriptionExtendedMail;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class RestaurantsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('address')
                    ->limit(40)
                    ->toggleable(),
                IconColumn::make('is_open')
                    ->boolean()
                    ->label('Open'),

                // ── Subscription columns ──────────────────────────
                TextColumn::make('subscription_ends_at')
                    ->label('Subscription Ends')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->placeholder('—')
                    ->color(fn ($record) => match (true) {
                        ! $record->subscription_ends_at => 'gray',
                        $record->subscription_ends_at->isPast() => 'danger',
                        $record->subscriptionExpiresWithinDays(2) => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('subscription_status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->subscriptionStatusLabel())
                    ->color(fn (string $state) => match ($state) {
                        'Active' => 'success',
                        'Expiring soon' => 'warning',
                        'Expired' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('subscription')
                    ->label('Subscription')
                    ->options([
                        'active' => 'Active',
                        'expiring' => 'Expiring Soon (≤ 2 days)',
                        'expired' => 'Expired',
                        'none' => 'No Subscription',
                    ])
                    ->query(function ($query, array $data) {
                        return match ($data['value'] ?? null) {
                            'active' => $query->where('subscription_ends_at', '>', now()),
                            'expiring' => $query
                                ->where('subscription_ends_at', '>', now())
                                ->where('subscription_ends_at', '<=', now()->addDays(2)),
                            'expired' => $query
                                ->whereNotNull('subscription_ends_at')
                                ->where('subscription_ends_at', '<=', now()),
                            'none' => $query->whereNull('subscription_ends_at'),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                Action::make('extend')
                    ->label('Extend 1 Month')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Extend Subscription')
                    ->modalDescription(fn ($record) => "Extend subscription for \"{$record->name}\" by 1 month? An email will be sent to the owner.")
                    ->action(function ($record): void {
                        $record->extendSubscription(1);

                        $owner = $record->user;
                        if ($owner && $owner->email) {
                            Mail::to($owner->email)->queue(
                                new SubscriptionExtendedMail($owner, $record->fresh())
                            );
                        }

                        Notification::make()
                            ->title('Subscription extended until ' . $record->fresh()->subscription_ends_at->format('M j, Y'))
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('subscription_ends_at', 'asc');
    }
}
