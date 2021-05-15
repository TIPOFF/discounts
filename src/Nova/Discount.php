<?php

declare(strict_types=1);

namespace Tipoff\Discounts\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Currency;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Panel;
use Tipoff\Discounts\Rules\DiscountCode;
use Tipoff\Support\Enums\AppliesTo;
use Tipoff\Support\Nova\BaseResource;
use Tipoff\Support\Nova\Filters\EnumFilter;

class Discount extends BaseResource
{
    public static $model = \Tipoff\Discounts\Models\Discount::class;

    public static $title = 'name';

    public static $search = [
        'name',
        'code',
    ];

    public static $group = 'Operations Units';

    /** @psalm-suppress UndefinedClass */
    protected array $filterClassList = [

    ];

    public function filters(Request $request)
    {
        return array_merge(parent::filters($request), [
            EnumFilter::make('applies_to', AppliesTo::class),
        ]);
    }

    public function fieldsForIndex(NovaRequest $request)
    {
        return array_filter([
            ID::make()->sortable(),
            Text::make('Name')->sortable(),
            Text::make('Code')->sortable(),
            Currency::make('Amount')->asMinorUnits()->sortable(),
            Number::make('Percent')->sortable(),
            Number::make('Max Usage')->sortable(),
            \Tipoff\Support\Nova\Fields\Enum::make('Applies To')
                ->attach(AppliesTo::class),
            Date::make('Expires At', 'expires_at')->sortable(),
        ]);
    }

    public function fields(Request $request)
    {
        return array_filter([
            Text::make('Name')->rules('required'),
            Text::make('Code')
                ->rules([new DiscountCode(), 'required'])
                ->creationRules('unique:discounts,code')
                ->updateRules('unique:discounts,code,{{resourceId}}'),
            Currency::make('Amount')->asMinorUnits()
                ->step('0.01')
                ->resolveUsing(function ($value) {
                    return $value / 100;
                })
                ->fillUsing(function ($request, $model, $attribute) {
                    $model->$attribute = $request->$attribute * 100;
                })
                ->rules(function () {
                    return [
                        'required_if:percent,null,0',
                    ];
                }),
            Number::make('Percent')
                ->rules(function () {
                    return [
                        'required_if:amount,null,0',
                    ];
                }),
            \Tipoff\Support\Nova\Fields\Enum::make('Applies To')
                ->attach(AppliesTo::class)
                ->rules('required'),
            Number::make('Max Usage')
                ->rules(['integer', 'min:1', 'required'])
                ->nullable(),
            Boolean::make('Auto Apply'),
            Date::make('Expires At', 'expires_at')->nullable(),

            nova('order') ? BelongsToMany::make('Orders', 'orders', nova('order')) : null,

            new Panel('Data Fields', $this->dataFields()),
        ]);
    }

    protected function dataFields(): array
    {
        return array_merge(
            parent::dataFields(),
            $this->creatorDataFields(),
            $this->updaterDataFields(),
        );
    }

    protected static function afterValidation(NovaRequest $request, $validator)
    {
        $percent = $request->post('percent');
        $amount = $request->post('amount');

        if (! empty($amount) && ! empty($percent)) {
            $validator
                ->errors()
                ->add(
                    'amount',
                    'A fee cannot have both an amount & percent.'
                );
            $validator
                ->errors()
                ->add(
                    'percent',
                    'A fee cannot have both an amount & percent.'
                );
        }
    }
}
