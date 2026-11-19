<?php

namespace App\Exports;

use App\SellingPriceGroup;
use App\Variation;
use Maatwebsite\Excel\Concerns\FromArray;

class ProductPricesExport implements FromArray
{
    protected $businessId;

    public function __construct($businessId)
    {
        $this->businessId = $businessId;
    }

    public function array(): array
    {
        $priceGroups = SellingPriceGroup::where('business_id', $this->businessId)->active()->get();
        $variations = Variation::join('products as p', 'variations.product_id', '=', 'p.id')
            ->join('product_variations as pv', 'variations.product_variation_id', '=', 'pv.id')
            ->where('p.business_id', $this->businessId)
            ->whereIn('p.type', ['single', 'variable'])
            ->select('sub_sku', 'p.name as product_name', 'variations.name as variation_name', 'p.type', 'variations.id', 'pv.name as product_variation_name', 'sell_price_inc_tax')
            ->with(['group_prices'])
            ->get();

        $rows = [[
            __('lang_v1.product_price_template_product', [], 'es'),
            __('lang_v1.product_price_template_sku', [], 'es'),
            __('lang_v1.product_price_template_base_price', [], 'es'),
        ]];
        foreach ($priceGroups as $priceGroup) {
            $rows[0][] = $priceGroup->name;
        }

        foreach ($variations as $variation) {
            $row = [
                $variation->type == 'single'
                    ? $variation->product_name
                    : $variation->product_name.' - '.$variation->product_variation_name.' - '.$variation->variation_name,
                $variation->sub_sku,
                $variation->sell_price_inc_tax,
            ];

            foreach ($priceGroups as $priceGroup) {
                $groupPrice = $variation->group_prices->firstWhere('price_group_id', $priceGroup->id);
                $row[] = $groupPrice ? $groupPrice->price_inc_tax : '';
            }
            $rows[] = $row;
        }

        return $rows;
    }
}
