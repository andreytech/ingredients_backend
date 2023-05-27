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
        12 => 'Озон - Очищение и умывание',
        13 => 'Озон - Увлажнение и питание',
        14 => 'Озон - Маски',
        15 => 'Озон - Лосьоны',
        16 => 'Озон - Антивозрастной уход',
        17 => 'Озон - Средства для проблемной кожи',
    ];

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class);
    }
}
