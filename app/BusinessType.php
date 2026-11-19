<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BusinessType extends Model
{
    protected $fillable = ['name', 'slug', 'is_active'];

    public function features()
    {
        return $this->hasMany(BusinessTypeFeature::class);
    }

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }
}
