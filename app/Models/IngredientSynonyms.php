<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientSynonyms extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'language',
    ];

    const language_en = 1;
    const language_ru = 2;
    
    const languages = [
        self::language_en => 'EN',
        self::language_ru => 'RU',
    ];

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
