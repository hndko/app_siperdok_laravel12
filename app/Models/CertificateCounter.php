<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateCounter extends Model
{
    protected $fillable = [
        'year',
        'month',
        'next_number',
    ];
}
