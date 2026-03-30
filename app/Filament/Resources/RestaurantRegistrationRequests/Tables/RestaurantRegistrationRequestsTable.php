<?php

namespace App\Filament\Resources\RestaurantRegistrationRequests\Tables;

use App\Models\Restaurant;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class RestaurantRegistrationRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('restaurant_name')
                    ->label('Restaurant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Applicant')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('address')
                    ->limit(40)
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reviewed_at')
                    ->label('Reviewed')
                    ->dateTime()
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('rejection_reason')
                    ->label('Rejection Reason')
                    ->limit(40)
                    ->wrap()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => $record->status === 'pending')
                    ->action(function ($record): void {
                        if ($record->status !== 'pending') {
                            Notification::make()
                                ->title('This request is already reviewed.')
                                ->warning()
                                ->send();

                            return;
                        }

                        if (! $record->user?->restaurant) {
                            Restaurant::create([
                                'user_id' => $record->user_id,
                                'name' => $record->restaurant_name,
                                'description' => $record->description,
                                'logo' => $record->logo,
                                'cover_image' => $record->cover_image,
                                'address' => $record->address,
                                'latitude' => $record->latitude,
                                'longitude' => $record->longitude,
                                'phone' => $record->phone,
                                'delivery_fee' => $record->delivery_fee,
                                'is_open' => $record->is_open,
                                'operating_hours' => $record->operating_hours,
                            ]);
                        }

                        $record->user?->update(['role' => 'owner']);

                        $record->update([
                            'status' => 'approved',
                            'rejection_reason' => null,
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Restaurant request approved.')
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Reason for rejection')
                            ->required()
                            ->rows(4),
                    ])
                    ->visible(fn ($record): bool => $record->status === 'pending')
                    ->action(function (array $data, $record): void {
                        if ($record->status !== 'pending') {
                            Notification::make()
                                ->title('This request is already reviewed.')
                                ->warning()
                                ->send();

                            return;
                        }

                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'reviewed_by' => Auth::id(),
                            'reviewed_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Restaurant request rejected.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
