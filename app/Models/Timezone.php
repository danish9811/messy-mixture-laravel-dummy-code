<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timezone extends Model {
    use HasFactory;

    protected $guarded = ['id'];

    public function countries() {
        return $this->belongsToMany(Country::class, 'country_timezone');
    }

}