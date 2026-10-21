<?php

namespace App\Models;

use App\Enums\StockStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'price',
        'stock',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getStockStatusAttribute(): StockStatus
    {
        return StockStatus::fromStock($this->stock);
    }

    /**
     * Search name, code and description.
     * % and _ are escaped so they are matched literally.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = addcslashes($term, '%_\\');

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('code', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        $threshold = config('inventory.low_stock_threshold');

        return match ($status) {
            StockStatus::OutOfStock->value => $query->where('stock', 0),
            StockStatus::LowStock->value => $query->whereBetween('stock', [1, $threshold]),
            StockStatus::InStock->value => $query->where('stock', '>', $threshold),
            default => $query,
        };
    }
}
