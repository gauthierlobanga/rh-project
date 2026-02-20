<?php

namespace App\Filament\Resources\NotificationAssurances\Pages;

use App\Filament\Resources\NotificationAssurances\NotificationAssuranceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNotificationAssurances extends ListRecords
{
    protected static string $resource = NotificationAssuranceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
