<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class AuditLogWidget extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->query(AuditLog::query()->latest())
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Data/Hora')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Usuário')
                    ->placeholder('Sistema'),
                TextColumn::make('action')
                    ->label('Ação')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'access' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('table_name')
                    ->label('Entidade'),
                TextColumn::make('record_id')
                    ->label('ID'),
            ])
            ->paginated([5])
            ->defaultPaginationPageOption(5);
    }

    public function render()
    {
        return view('livewire.admin.audit-log-widget');
    }
}
