<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model {
    use HasFactory;

    protected $guarded = ['id'];

    public function country() {
        return $this->belongsTo(Country::class);
    }

    public function currencyRates() {
        return $this->hasMany(CurrencyRate::class);
    }

}