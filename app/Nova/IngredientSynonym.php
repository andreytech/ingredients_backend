<?php

namespace App\Nova;

use App\Nova\Filters\HaveIngredients;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class IngredientSynonym extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\IngredientSynonym>
     */
    public static $model = \App\Models\IngredientSynonym::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name'
    ];

    public static $perPageViaRelationship = 100;

    public static $tableStyle = 'tight';

    public static function label(): string
    {
        return 'Синонимы ингредиентов';
    }

    public static function singularLabel(): string
    {
        return 'Синоним ингредиента';
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),
            
            Text::make('Название', 'name')
                ->rules('required')
                ->hideFromIndex(),

            Text::make('Название', 'name')
                ->displayUsing(function($text) {
                    $charsToShow = 70;
                    if (strlen($text) > $charsToShow) {
                        return mb_substr($text, 0, $charsToShow) . '...';
                    }
                })->onlyOnIndex(),

            Select::make('Язык', 'language')
                ->options(\App\Models\IngredientSynonym::languages)
                ->displayUsingLabels()
                ->filterable(),

            BelongsTo::make('Ингредиент', 'ingredient', Ingredient::class),
        ];
    }

    /**
     * Get the cards available for the request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function cards(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function filters(NovaRequest $request)
    {
        return [new HaveIngredients];
        // return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function actions(NovaRequest $request)
    {
        return [];
    }
}
