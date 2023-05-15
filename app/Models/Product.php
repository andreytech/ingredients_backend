<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    const categories = [
        1 => 'Озон - Аксессуры для очищения лица',
        2 => 'Озон - Наборы средств для лица',
        3 => 'Озон - Матирующие салфетки',
        4 => 'Озон - Косметические инструменты',
        5 => 'Озон - Восковые полоски',
        // 6 => 'Озон - Дорожные флаконы',
        // 7 => 'Озон - Уход за губами',
        8 => 'ЯМ - Наборы',
        9 => 'ЯМ - Уход за губами',
        10 => 'ЯМ - Дорожные флаконы',
        11 => 'ЯМ - Аксессуары',
    ];

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class);
    }
}
