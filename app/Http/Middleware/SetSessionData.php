<?php

namespace App\Http\Middleware;

use App\Business;
use App\Utils\BusinessUtil;
use Closure;
use Illuminate\Support\Facades\Auth;

class SetSessionData
{
    /**
     * Checks if session data is set or not for a user. If data is not set then set it.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $request->session()->has('user')) {
            $business_util = new BusinessUtil;

            $user = Auth::user();
            $session_data = ['id' => $user->id,
                'surname' => $user->surname,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'business_id' => $user->business_id,
                'language' => $user->language,
            ];
            $business = Business::findOrFail($user->business_id);

            $currency = $business->currency;
            $currency_data = ['id' => $currency->id,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'thousand_separator' => $currency->thousand_separator,
                'decimal_separator' => $currency->decimal_separator,
            ];

            $request->session()->put('user', $session_data);
            $request->session()->put('business', $business);
            $request->session()->put('currency', $currency_data);

            //set current financial year to session
            $financial_year = $business_util->getCurrentFinancialYear($business->id);
            $request->session()->put('financial_year', $financial_year);
        }

        // Cache the assigned type configuration so product forms and other views
        // can reuse it without querying the feature table for every field.
        $business = $request->session()->get('business');
        $feature_visibility = null;
        if ($business) {
            $business_type_id = \Illuminate\Support\Facades\Cache::remember(
                'business_type_for_business_'.$business->id,
                now()->addHours(12),
                function () use ($business) {
                    return (int) (Business::whereKey($business->id)->value('business_type_id') ?? 0);
                }
            );
            $business->business_type_id = $business_type_id ?: null;
        }
        if ($business && ! empty($business->business_type_id)) {
            $feature_visibility = \Illuminate\Support\Facades\Cache::remember(
                'business_type_features_'.$business->business_type_id,
                now()->addHours(12),
                function () use ($business) {
                    return \App\BusinessTypeFeature::where('business_type_id', $business->business_type_id)
                        ->get()->keyBy('feature_key')->map(function ($feature) {
                            return [
                                'is_enabled' => (bool) $feature->is_enabled,
                                'hidden_columns' => $feature->hidden_columns ?? [],
                                'hide_in_reports' => (bool) $feature->hide_in_reports,
                                'hide_in_datatables' => (bool) $feature->hide_in_datatables,
                            ];
                        })->toArray();
                }
            );
        }
        $request->session()->put('business_type_features', $feature_visibility);
        if ($business) {
            $request->session()->put('business', $business);
        }

        return $next($request);
    }
}
