<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessTypeFeature extends Model
{
    protected $fillable = ['business_type_id', 'feature_key', 'is_enabled', 'hidden_columns', 'hide_in_reports', 'hide_in_datatables'];

    protected $casts = [
        'is_enabled' => 'boolean',
        'hidden_columns' => 'array',
        'hide_in_reports' => 'boolean',
        'hide_in_datatables' => 'boolean',
    ];
}
