<?php

namespace App\Enums;

enum StockStatus: string
{
    case InStock = 'in_stock';
    case LowStock = 'low_stock';
    case OutOfStock = 'out_of_stock';

    public static function fromStock(int $stock): self
    {
        return match (true) {
            $stock <= 0 => self::OutOfStock,
            $stock <= config('inventory.low_stock_threshold') => self::LowStock,
            default => self::InStock,
        };
    }
}
