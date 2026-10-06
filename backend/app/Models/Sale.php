<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['business_id', 'branch_id', 'customer_id', 'created_by', 'request_key', 'request_hash', 'customer_name', 'customer_phone', 'business_name', 'branch_name', 'currency', 'timezone', 'total_minor', 'notes'])]
class Sale extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['total_minor' => 'integer'];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(SaleLine::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(SaleEvent::class)->orderBy('id');
    }

    public function number(): string
    {
        return 'tr-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function paidMinor(): int
    {
        return (int) $this->payments()->sum('amount_minor');
    }

    public static function money(int $minor): string
    {
        return intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }
}
