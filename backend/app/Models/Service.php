<?php

namespace App\Models;

use App\Enums\BillingUnit;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['business_id', 'name', 'billing_unit', 'price_minor', 'requires_finish'])]
class Service extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['billing_unit' => BillingUnit::class, 'price_minor' => 'integer', 'requires_finish' => 'boolean'];
    }

    public function formattedPrice(): string
    {
        return intdiv($this->price_minor, 100).'.'.str_pad((string) ($this->price_minor % 100), 2, '0', STR_PAD_LEFT);
    }
}
