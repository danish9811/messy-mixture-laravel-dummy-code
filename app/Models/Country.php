<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model {
    use HasFactory;

    protected $guarded = [];

    public function currency() {
        return $this->hasOne(Currency::class);
    }

    public function states() {
        return $this->hasMany(State::class);
    }

    public function timezones() {
        return $this->belongsToMany(Timezone::class, 'country_timezone',);
    }

}
