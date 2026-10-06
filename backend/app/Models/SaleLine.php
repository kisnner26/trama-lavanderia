<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['business_id', 'sale_id', 'service_id', 'service_name', 'billing_unit', 'quantity_milli', 'price_minor', 'total_minor', 'requires_finish'])]
class SaleLine extends Model
{
    protected function casts(): array
    {
        return ['quantity_milli' => 'integer', 'price_minor' => 'integer', 'total_minor' => 'integer', 'requires_finish' => 'boolean'];
    }

    public function quantity(): string
    {
        $fraction = rtrim(str_pad((string) ($this->quantity_milli % 1000), 3, '0', STR_PAD_LEFT), '0');

        return (string) intdiv($this->quantity_milli, 1000).($fraction === '' ? '' : '.'.$fraction);
    }
}
