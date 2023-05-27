<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Ingredient extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Ingredient>
     */
    public static $model = \App\Models\Ingredient::class;

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
        'id', 'name',
    ];

    public static $perPageViaRelationship = 100;

    public static $tableStyle = 'tight';

    public static function label(): string
    {
        return 'Ингредиенты';
    }

    public static function singularLabel(): string
    {
        return 'Ингредиент';
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
                })->onlyOnIndex()
                ->sortable(),

            Text::make('COSING Ref. No.', 'cosing_ref_no')->hideFromIndex(),
            Text::make('CAS No.', 'cas_no')->hideFromIndex(),
            Text::make('EC No.', 'ec_no')->hideFromIndex(),
            Text::make('Function', 'function'),
            Textarea::make('Описание', 'description')->hideFromIndex(),
            HasMany::make('Синонимы', 'ingredient_synonyms', IngredientSynonym::class),
            BelongsToMany::make('Товары', 'products', Product::class),
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
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    public function lenses(NovaRequest $request)
    {
        return [
            new Lenses\IngredientProductsCount(),
        ];
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
