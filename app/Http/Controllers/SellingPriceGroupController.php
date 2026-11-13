<?php

namespace App\Http\Controllers;

use App\SellingPriceGroup;
use App\Exports\ProductPricesExport;
use App\Utils\Util;
use App\Variation;
use App\VariationGroupPrice;
use DB;
use Excel;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Yajra\DataTables\Facades\DataTables;

class SellingPriceGroupController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $commonUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $price_groups = SellingPriceGroup::where('business_id', $business_id)
                        ->select(['name', 'description', 'id', 'is_active']);

            return Datatables::of($price_groups)
                ->addColumn(
                    'action',
                    '<button data-href="{{action(\'App\Http\Controllers\SellingPriceGroupController@edit\', [$id])}}" class="tw-dw-btn tw-dw-btn-xs tw-dw-btn-outline tw-dw-btn-primary btn-modal" data-container=".view_modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                        <button data-href="{{action(\'App\Http\Controllers\SellingPriceGroupController@destroy\', [$id])}}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs tw-dw-btn-error delete_spg_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                        &nbsp;
                        <button data-href="{{action(\'App\Http\Controllers\SellingPriceGroupController@activateDeactivate\', [$id])}}" class="tw-dw-btn tw-dw-btn-outline tw-dw-btn-xs  @if($is_active) tw-dw-btn-error @else tw-dw-btn-success @endif activate_deactivate_spg"><i class="fas fa-power-off"></i> @if($is_active) @lang("messages.deactivate") @else @lang("messages.activate") @endif</button>'
                )
                ->removeColumn('is_active')
                ->removeColumn('id')
                ->rawColumns([2])
                ->make(false);
        }

        return view('selling_price_group.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('selling_price_group.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'description']);
            $business_id = $request->session()->get('user.business_id');
            $input['business_id'] = $business_id;

            $spg = SellingPriceGroup::create($input);

            //Create a new permission related to the created selling price group
            Permission::create(['name' => 'selling_price_group.'.$spg->id]);

            $output = ['success' => true,
                'data' => $spg,
                'msg' => __('lang_v1.added_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

            $output = ['success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function show(SellingPriceGroup $sellingPriceGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $spg = SellingPriceGroup::where('business_id', $business_id)->find($id);

            return view('selling_price_group.edit')
                ->with(compact('spg'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'description']);
                $business_id = $request->session()->get('user.business_id');

                $spg = SellingPriceGroup::where('business_id', $business_id)->findOrFail($id);
                $spg->name = $input['name'];
                $spg->description = $input['description'];
                $spg->save();

                $output = ['success' => true,
                    'msg' => __('lang_v1.updated_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SellingPriceGroup  $sellingPriceGroup
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->user()->business_id;

                $spg = SellingPriceGroup::where('business_id', $business_id)->findOrFail($id);
                $spg->delete();

                $output = ['success' => true,
                    'msg' => __('lang_v1.deleted_success'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());

                $output = ['success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Show interface to download product price excel file.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateProductPrice(){
        if (! auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        return view('selling_price_group.update_product_price');
    }

    /**
     * Exports selling price group prices for all the products in xls format
     *
     * @return \Illuminate\Http\Response
     */
    public function export()
    {
        $business_id = request()->user()->business_id;
        return Excel::download(new ProductPricesExport($business_id), 'product_prices.xlsx');
    }

    /**
     * Imports the uploaded file to database.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request)
    {
        try {
            $notAllowed = $this->commonUtil->notAllowedInDemo();
            if (! empty($notAllowed)) {
                return $notAllowed;
            }

            ini_set('max_execution_time', 0);
            ini_set('memory_limit', -1);

            if (! $request->hasFile('product_group_prices')) {
                throw new \Exception(__('lang_v1.product_price_file_required'));
            }

            $parsedArray = Excel::toArray([], $request->file('product_group_prices'));
            $sheet = $parsedArray[0] ?? [];
            $headers = $sheet[0] ?? [];
            if (empty($headers)) {
                throw new \Exception(__('lang_v1.product_price_headers_invalid'));
            }
            $importedData = array_slice($sheet, 1);
            $businessId = $request->session()->get('user.business_id');
            $priceGroups = SellingPriceGroup::where('business_id', $businessId)->active()->get();

            $normalize = function ($value) {
                $value = preg_replace('/^\\xEF\\xBB\\xBF/', '', (string) $value);
                return mb_strtolower(trim($value), 'UTF-8');
            };
            $headerIndexes = [];
            foreach ($headers as $index => $header) {
                $normalizedHeader = $normalize($header);
                if ($normalizedHeader !== '') {
                    $headerIndexes[$normalizedHeader] = $index;
                }
            }

            $findHeader = function (array $aliases) use ($headerIndexes, $normalize) {
                foreach ($aliases as $alias) {
                    $normalizedAlias = $normalize($alias);
                    if (array_key_exists($normalizedAlias, $headerIndexes)) {
                        return $headerIndexes[$normalizedAlias];
                    }
                }
                return null;
            };
            $productIndex = $findHeader([__('lang_v1.product_price_template_product'), 'Product', 'Producto']);
            $skuIndex = $findHeader([__('lang_v1.product_price_template_sku'), 'SKU']);
            $basePriceIndex = $findHeader([__('lang_v1.product_price_template_base_price'), 'Selling Price Including Tax', 'Precio de venta con impuestos', 'Precio de venta con impuesto']);
            if ($productIndex === null || $skuIndex === null || $basePriceIndex === null) {
                throw new \Exception(__('lang_v1.product_price_headers_invalid'));
            }

            $groupsByName = $priceGroups->keyBy(function ($group) use ($normalize) {
                return $normalize($group->name);
            });
            $importedPriceGroups = [];
            foreach ($headers as $index => $header) {
                if (in_array($index, [$productIndex, $skuIndex, $basePriceIndex], true) || trim((string) $header) === '') {
                    continue;
                }
                $priceGroup = $groupsByName->get($normalize($header));
                if (! $priceGroup) {
                    throw new \Exception(__('lang_v1.price_group_not_found_exception', ['pg' => $header, 'row' => 1]));
                }
                $importedPriceGroups[] = ['index' => $index, 'group' => $priceGroup];
            }

            DB::beginTransaction();
            foreach ($importedData as $rowIndex => $rowData) {
                $rowNumber = $rowIndex + 2;
                $sku = $rowData[$skuIndex] ?? null;
                $variation = Variation::where('sub_sku', $sku)
                    ->join('products', 'products.id', '=', 'variations.product_id')
                    ->where('products.business_id', $businessId)
                    ->select('variations.*')
                    ->first();
                if (! $variation) {
                    throw new \Exception(__('lang_v1.product_not_found_exception', ['sku' => $sku, 'row' => $rowNumber]));
                }

                $basePrice = $rowData[$basePriceIndex] ?? null;
                if ($basePrice !== null && $basePrice !== '' && ! is_numeric($basePrice)) {
                    throw new \Exception(__('lang_v1.price_group_non_numeric_exception', ['row' => $rowNumber]));
                }
                if ($basePrice !== null && $basePrice !== '' && (float) $variation->sell_price_inc_tax != (float) $basePrice) {
                    $variation->sell_price_inc_tax = $basePrice;
                    $tax = $variation->product->product_tax()->first();
                    $taxPercent = $tax ? $tax->amount : 0;
                    $variation->default_sell_price = $this->commonUtil->calc_percentage_base($basePrice, $taxPercent);
                    $variation->profit_percent = $this->commonUtil->get_percent($variation->default_purchase_price, $variation->default_sell_price);
                    $variation->update();
                }

                foreach ($importedPriceGroups as $priceGroupColumn) {
                    $price = $rowData[$priceGroupColumn['index']] ?? null;
                    if ($price !== null && $price !== '' && ! is_numeric($price)) {
                        throw new \Exception(__('lang_v1.price_group_non_numeric_exception', ['row' => $rowNumber]));
                    }
                    if ($price !== null && $price !== '') {
                        VariationGroupPrice::updateOrCreate(
                            ['variation_id' => $variation->id, 'price_group_id' => $priceGroupColumn['group']->id],
                            ['price_inc_tax' => $price]
                        );
                    }
                }
            }
            DB::commit();

            $output = ['success' => 1, 'msg' => __('lang_v1.product_prices_imported_successfully')];
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            \Log::emergency('File:'.$e->getFile().'Line:'.$e->getLine().'Message:'.$e->getMessage());
            $output = ['success' => 0, 'msg' => $e->getMessage()];

            return redirect('update-product-price')->with('notification', $output);
        }

        return redirect('update-product-price')->with('status', $output);
    }

    /**
     * Activate/deactivate selling price group.
     */
    public function activateDeactivate($id)
    {
        if (! auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $spg = SellingPriceGroup::where('business_id', $business_id)->find($id);
            $spg->is_active = $spg->is_active == 1 ? 0 : 1;
            $spg->save();

            $output = ['success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];

            return $output;
        }
    }
}
