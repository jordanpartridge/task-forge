<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Illuminate\Support\HtmlString;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null
                            ),

                        Forms\Components\TextInput::make('key')
                            ->prefix('TF-')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Project identifier (e.g., 1 becomes TF-1)')
                            ->columnStart(1),

                        Forms\Components\Select::make('status')
                            ->options([
                                'active'    => 'Active',
                                'paused'    => 'Paused',
                                'completed' => 'Completed',
                            ])
                            ->default('active')
                            ->required()
                            ->columnStart(2),

                        Forms\Components\TextInput::make('slug')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(Project::class, 'slug', ignoreRecord: true)
                            ->columnStart(1),

                        Forms\Components\Select::make('priority')
                            ->options([
                                'low'    => 'Low',
                                'medium' => 'Medium',
                                'high'   => 'High',
                            ])
                            ->default('medium')
                            ->required()
                            ->columnStart(2),

                        Forms\Components\RichEditor::make('description')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'bulletList',
                                'orderedList',
                                'redo',
                                'undo',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('owner_id')
                            ->relationship('owner', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Owner'),

                        Forms\Components\Select::make('lead_id')
                            ->relationship('lead', 'name')
                            ->searchable()
                            ->preload()
                            ->label('Lead'),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start date'),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due date'),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Tables\Grouping\Group::make('status')
                    ->label('Status')
                    ->collapsible(),
                Tables\Grouping\Group::make('priority')
                    ->label('Priority'),
                Tables\Grouping\Group::make('owner.name')
                    ->label('Owner'),
                Tables\Grouping\Group::make('due_date')
                    ->label('Due Date')
                    ->date(),
            ])
            ->defaultGroup('status')
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'active'    => 'Active',
                        'paused'    => 'Paused',
                        'completed' => 'Completed',
                    ])
                    ->sortable(),

                Tables\Columns\SelectColumn::make('priority')
                    ->options([
                        'low'    => 'Low',
                        'medium' => 'Medium',
                        'high'   => 'High',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('owner.name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable()
                    ->color(fn(Project $record): string => $record->due_date?->isPast() ? 'danger' : 'success'
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'active'    => 'Active',
                        'paused'    => 'Paused',
                        'completed' => 'Completed',
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->multiple()
                    ->options([
                        'low'    => 'Low',
                        'medium' => 'Medium',
                        'high'   => 'High',
                    ]),

                Tables\Filters\Filter::make('overdue')
                    ->query(fn(Builder $query): Builder => $query->where('due_date', '<', now())
                        ->where('status', '!=', 'completed')
                    ),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit'   => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
