<?php

namespace App\Filament\Resources\NotificationAssurances\Pages;

use App\Filament\Resources\NotificationAssurances\NotificationAssuranceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNotificationAssurance extends EditRecord
{
    protected static string $resource = NotificationAssuranceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
