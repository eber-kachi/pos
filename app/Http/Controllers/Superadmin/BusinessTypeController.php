<?php

namespace App\Http\Controllers\Superadmin;

use App\Business;
use App\BusinessType;
use App\BusinessTypeFeature;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BusinessTypeController extends Controller
{
    private $features = [
        'product_imei' => ['label' => 'IMEI o número de serie', 'columns' => ['enable_sr_no']],
        'product_weight' => ['label' => 'Peso del producto', 'columns' => ['weight']],
        'product_preparation_time' => ['label' => 'Tiempo de preparación', 'columns' => ['preparation_time_in_minutes']],
        'restaurant_tables' => ['label' => 'Mesas del restaurante', 'columns' => ['res_table_id']],
        'warranties' => ['label' => 'Gestión de garantías', 'columns' => ['warranty_id']],
        'sales_commission_agents' => ['label' => 'Agentes de comisión de ventas', 'columns' => ['sales_commission_agent_id']],
        'sales_quotations' => ['label' => 'Cotizaciones de venta', 'columns' => ['type']],
    ];

    private function ensureSuperadmin()
    {
        abort_unless(auth()->user() && auth()->user()->can('superadmin'), 403, 'Unauthorized action.');
    }

    public function index()
    {
        $this->ensureSuperadmin();
        $types = BusinessType::withCount('businesses')->orderBy('name')->get();
        return view('superadmin.business_types.index', compact('types'));
    }

    public function create()
    {
        $this->ensureSuperadmin();
        $type = new BusinessType;
        $features = $this->features;
        $featureSettings = [];
        return view('superadmin.business_types.form', compact('type', 'features', 'featureSettings'));
    }

    public function store(Request $request)
    {
        $this->ensureSuperadmin();
        $data = $request->validate(['name' => 'required|string|max:191|unique:business_types,name']);
        $type = BusinessType::create(['name' => $data['name'], 'slug' => Str::slug($data['name']).'-'.Str::random(5)]);
        $this->saveFeatures($request, $type);
        return redirect()->action([self::class, 'index'])->with('status', ['success' => true, 'msg' => 'Tipo de negocio creado.']);
    }

    public function edit($id)
    {
        $this->ensureSuperadmin();
        $type = BusinessType::with('features')->findOrFail($id);
        $features = $this->features;
        $featureSettings = $type->features->keyBy('feature_key')->toArray();
        return view('superadmin.business_types.form', compact('type', 'features', 'featureSettings'));
    }

    public function update(Request $request, $id)
    {
        $this->ensureSuperadmin();
        $type = BusinessType::findOrFail($id);
        $data = $request->validate(['name' => ['required', 'string', 'max:191', Rule::unique('business_types')->ignore($type->id)]]);
        $type->update(['name' => $data['name']]);
        $this->saveFeatures($request, $type);
        return redirect()->action([self::class, 'index'])->with('status', ['success' => true, 'msg' => 'Tipo de negocio actualizado.']);
    }

    private function saveFeatures(Request $request, BusinessType $type)
    {
        foreach ($this->features as $key => $feature) {
            $columns = array_values(array_filter(array_map('trim', explode(',', $request->input('hidden_columns.'.$key, implode(',', $feature['columns']))))));
            BusinessTypeFeature::updateOrCreate(
                ['business_type_id' => $type->id, 'feature_key' => $key],
                [
                    'is_enabled' => $request->boolean('features.'.$key),
                    'hidden_columns' => $columns,
                    'hide_in_reports' => $request->boolean('hide_in_reports.'.$key),
                    'hide_in_datatables' => $request->boolean('hide_in_datatables.'.$key),
                ]
            );
        }
        Cache::forget('business_type_features_'.$type->id);
    }

    public function assignments()
    {
        $this->ensureSuperadmin();
        $businesses = Business::orderBy('name')->get();
        $types = BusinessType::where('is_active', true)->orderBy('name')->pluck('name', 'id');
        return view('superadmin.business_types.assignments', compact('businesses', 'types'));
    }

    public function assign(Request $request, $businessId)
    {
        $this->ensureSuperadmin();
        $data = $request->validate(['business_type_id' => 'nullable|exists:business_types,id']);
        $business = Business::findOrFail($businessId);
        $business->business_type_id = $data['business_type_id'] ?? null;
        $business->save();
        Cache::forget('business_type_for_business_'.$business->id);
        Cache::forget('business_type_features_'.(int) $business->business_type_id);
        if ((int) session('business.id') === (int) $business->id) {
            $request->session()->put('business', $business);
            $request->session()->forget('business_type_features');
        }
        return back()->with('status', ['success' => true, 'msg' => 'Tipo asignado al negocio.']);
    }
}
