<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['business_id', 'sale_id', 'created_by', 'request_key', 'amount_minor', 'method', 'reference'])]
class Payment extends Model
{
    protected function casts(): array
    {
        return ['amount_minor' => 'integer'];
    }
}
