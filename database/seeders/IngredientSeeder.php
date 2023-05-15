<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class IngredientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ingredients = [
            'Aqua',
            'Glycerin',
            'Glycerin Stearate',
            'Butyrospermum Parkii Butter',
            'Cetyl Palmitate',
            'Olus Oil',
            'Celyl Alcohol',
            'Isopropyl Palmitate',
            'Aloe Barbadensis Leaf Juice Powder',
            'Dimethicone',
            'Sodium Polyacrylate',
            'Phenoxyethanol',
            'Ethylhexylglycerin',
            'Parfum',
            /// 2
            'Sodium Benzoate',
            'Propylene Glycol',
            'Sorbitol',
            'Poloxamer 407',
            'Sodium Lauryl Sulfate',
            'Eucalyptol',
            'Benzoic Acid',
            'Methyl Salicylate',
            'Thymol',
            'Sodium Saccharin',
            'Sodium Fluoride',
            'Menthol',
            'Sucralose',
            'CI 42053',
            'фторид натрия',
            /// 3
            'Paraffinum Liquidum',
            'mineral oil',
            'Cera Microcristallina',
            'Microcrystalline wax',
            'Lanolin Alocohol',
            'Eticerit',
            'Paraffin',
            'Panthenol',
            'Decyl Oleate',
            'Octyldodecanol',
            'Alumimum Stearates',
            'Citric Acid',
            'Magnesium Sulfate',
            'Magnesium Stearate',
            'Limonene',
            'Geraniol',
            'гераниол',
            'Hydroxycitronellal',
            'Linalool',
            'Citronellol',
            'Benzyl Benzoate',
            'Cinnamyl alcohol',
            ///// 4
            'Ceteareth',
            'cetearyl alcohol',
            'emulsifying wax',
            'Cocos Nucifera oil',
            'niacinamide',
            'ascorbyl palmitate',
            'tocopheryl acetate',
            'd-panthenol',
            'hyaluronic acid',
            'diazolidinyl urea',
            'methylparaben',
            'ethylparaben',
            'propylparaben',
            ///// 5
            'vaselin',
            'triethanolamine',
            'propanediol',
            'caprylic',
            'capric triglyceride',
            'dicaprylyl carbonate',
            'dicaprylyl ether',
        ];
        
        Ingredient::insert(array_map(fn($ingredient) => ['name' => $ingredient], $ingredients));
    }
}
