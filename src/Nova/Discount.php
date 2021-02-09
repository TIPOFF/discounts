<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Tipoff\Discounts\Rules\DiscountCode;
use Tipoff\Support\Enums\AppliesTo;
use Tipoff\Support\Nova\Resource;
use Tipoff\Support\Rules\Enum;

class Discount extends Resource
{
    public static $model = \Tipoff\Discounts\Models\Discount::class;

    public static $title = 'name';

    public static $search = [
        'name',
        'code',
    ];

    public static $group = 'Operations Units';

    public function fieldsForIndex(NovaRequest $request)
    {
        return [
            ID::make()->sortable(),
            Text::make('Name')->sortable(),
            Text::make('Code')->sortable(),
            Currency::make('Amount')->asMinorUnits()->sortable(),
            Number::make('Percent')->sortable(),
            Number::make('Max Usage')->sortable(),
            Date::make('Expires At', 'expires_at')->sortable(),
        ];
    }

    public function fields(Request $request)
    {
        return [
            Text::make('Name'),
            Text::make('Code')
                ->rules([new DiscountCode()]),
            Currency::make('Amount')->asMinorUnits()
                ->step('0.01')
                ->resolveUsing(function ($value) {
                    return $value / 100;
                })
                ->fillUsing(function ($request, $model, $attribute) {
                    $model->$attribute = $request->$attribute * 100;
                })
                ->rules('required_without:percent')
                ->nullable(),
            Number::make('Percent')
                ->rules('required_without:amount')
                ->nullable(),
            Select::make('Applies To')
                ->options(
                    config('discounts.applications')
                )
                ->rules([new Enum(AppliesTo::class)])
                ->required(),
            Number::make('Max Usage')
                ->rules(['integer', 'min:1'])
                ->nullable(),
            Boolean::make('Auto Apply'),
            Date::make('Expires At', 'expires_at')->nullable(),

            HasMany::make('Orders', 'orders', app()->getAlias('order')),

            new Panel('Data Fields', $this->dataFields()),
        ];
    }

    protected function dataFields()
    {
        return [
            ID::make(),
            BelongsTo::make('Created By', 'creator', app()->getAlias('user'))->exceptOnForms(),
            DateTime::make('Created At')->exceptOnForms(),
            BelongsTo::make('Updated By', 'updater', app()->getAlias('user'))->exceptOnForms(),
            DateTime::make('Updated At')->exceptOnForms(),
        ];
    }

    public function cards(Request $request)
    {
        return [];
    }

    public function filters(Request $request)
    {
        return [];
    }

    public function lenses(Request $request)
    {
        return [];
    }

    public function actions(Request $request)
    {
        return [];
    }
}
