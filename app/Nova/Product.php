<?php

namespace App\Nova;

use App\Nova\Filters\HaveIngredients;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Status;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Product extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Product>
     */
    public static $model = \App\Models\Product::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    public static $tableStyle = 'tight';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id', 'name', 'brand', 'description', 'properties'
    ];

    public static $perPageViaRelationship = 100;

    public static function label(): string
    {
        return 'Товары';
    }

    public static function singularLabel(): string
    {
        return 'Товар';
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


            Text::make('Ссылка', function () {
                    return "<a href='{$this->link}' target='__blank' style='text-decoration: underline;'>Ссылка</a>";
                })->asHtml(),

            Text::make('Бренд', 'brand'),

            Select::make('Категория', 'category')
                ->options(\App\Models\Product::categories)
                ->displayUsingLabels()
                ->filterable(),

            Text::make('Фото', function () use ($request) {
                    $images = json_decode($this->images);
                    if(!$images) return '';
                    if ($request->isResourceIndexRequest()) {
                        return "<img src='{$images[0]}' style='height:50px' />";
                    }else {
                        $result = '';
                        foreach($images as $image) {
                            $result .= "<a href='{$image}' target='__blank' style='float:left; margin: 5px'>
                            <img src='{$image}' style='height:200px' />
                            </a>";
                        }
                        return $result;
                    }
                })->asHtml()
                ,

            Textarea::make('Описание', 'description')
                // ->rules('required', 'min:5', 'max:500')
                ->hideFromIndex()
                ,
            Textarea::make('Состав текстом', 'properties')
                // ->rules('required', 'min:5', 'max:500')
                ->hideFromIndex()
                ,
            
            BelongsToMany::make('Состав', 'ingredients', Ingredient::class),
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
