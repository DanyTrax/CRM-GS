<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'rate',
        'source',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'rate' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Obtiene la TRM activa del día
     */
    public static function getActiveRate(\Carbon\Carbon $date = null): ?float
    {
        $date = $date ?? \Carbon\Carbon::now();
        
        return static::where('date', '<=', $date)
            ->where('is_active', true)
            ->orderBy('date', 'desc')
            ->value('rate');
    }
}
