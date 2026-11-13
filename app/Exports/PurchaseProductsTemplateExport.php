<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class PurchaseProductsTemplateExport implements FromArray
{
    public function array(): array
    {
        return [[
            __('lang_v1.purchase_template_product_name', [], 'es'),
            __('lang_v1.purchase_template_sku', [], 'es'),
            __('lang_v1.purchase_template_quantity', [], 'es'),
            __('lang_v1.purchase_template_unit_cost', [], 'es'),
            __('lang_v1.purchase_template_discount', [], 'es'),
            __('lang_v1.purchase_template_tax', [], 'es'),
            __('lang_v1.purchase_template_lot_number', [], 'es'),
            __('lang_v1.purchase_template_manufacturing_date', [], 'es'),
            __('lang_v1.purchase_template_expiry_date', [], 'es'),
        ]];
    }
}
