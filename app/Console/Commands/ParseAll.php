<?php

namespace App\Console\Commands;

use App\Models\Ingredient;
use App\Models\IngredientSynonym;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ParseAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parse-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $ingredients = null;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Product::truncate();
        DB::table('ingredient_product')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->ingredients = IngredientSynonym::select('ingredient_id', 'name')->orderByRaw('CHAR_LENGTH(name) DESC')->pluck('ingredient_id', 'name')->toArray();
        // $text = '';
        // foreach($this->ingredients as $item => $id) {
        //     $text .= "$id - $item\n";
        // }
        // file_put_contents('test.txt', $text);
        // var_dump($this->ingredients);exit;

        // $product = Product::find(8112);
        // $this->handleIngredients($product->properties, $product);
        // exit;

        $filesToCategory = [
            'ozon' => [
                1 => 'aksessuari.csv',
                2 => 'nabori.csv',
                3 => 'salfetki.csv',
                4 => 'instrumenti.csv',
                5 => 'voskovie_poloski.csv',
            ],
            'yandex' => [
                8 => 'nabori.csv',
                9 => 'uhod_za_gubami.csv',
                10 => 'dorozhnie_flakoni.csv',
                11 => 'aksessuari.csv',
            ],
        ];

        foreach($filesToCategory as $platform => $items) {
            foreach($items as $category => $filename) {
                $this->handleCategory($platform, $category, $filename);
            }
        }
    }

    public function handleCategory($platform, $category, $filename)
    {
        $file = storage_path("import/{$platform}/" . $filename);
    
        if(($handle = fopen($file, "r")) === FALSE) {
            $this->info('not found '.$file);
            return;
        }

        //Skip first line
        fgetcsv($handle, null, ';');
        $count = 0;

        while (($data = fgetcsv($handle, null, ';')) !== FALSE) {
            // var_dump($data);exit;
            
            // Remove non-utf-8 chars
            $data = array_map(function($item) {
                return preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $item);
            }, $data);

            $data = array_map(function($item) {
                return trim($item);
            }, $data);

            $ingredients = '';
            $images = [];

            if($platform === 'ozon') {
                for($i = 7; $i <= 13; $i++) {
                    if($data[$i]) {
                        $images[] = $data[$i];
                    }
                }
                $description = $data[5];
                $link = $data[0];
                $name = $data[4];
                $brand = $data[2];
                $ingredients = empty($data[6])?'':$data[6];
            }

            if($platform === 'yandex') {
                for($i = 6; $i <= 25; $i++) {
                    if($data[$i]) {
                        $images[] = $data[$i];
                    }
                }
                $description = $data[3];
                $link = $data[4];
                $name = $data[5];
                $brand = $data[26];
                if($category === 8) {
                    // nabori
                    $ingredients = empty($data[40])?'':$data[40];
                }
                if($category === 9) {
                    // uhod_za_gubami
                    $ingredients = empty($data[39])?'':$data[39];
                }
            }

            $description = strip_tags($description);
            $description = str_replace('Показать полностью', '', $description);

            $product = DB::table('products')
                ->select(DB::raw('id'))
                ->where('link', $link)
                ->first();

            if($product) {
                continue;
            }

            $count++;

            $product = new Product();
            $product->name = $name;
            $product->brand = $brand;
            $product->category = $category;
            $product->link = $link;
            $product->description = $description;
            $product->images = json_encode($images);
            $product->properties = $ingredients;
            $product->save();

            $this->handleIngredients($ingredients, $product);

            if($description && substr_count($description, ',') > 10) {
                $this->handleIngredients($description, $product);
            }
        }
        fclose($handle);

        $this->info($filename.' - '.$count);

    }

    public function handleIngredients($str, $product)
    {
        if(!$str) {
            return;
        }
        $str = strip_tags(mb_strtolower($str));
        $str = str_replace('&nbsp;', ' ', $str);
        $str = str_replace('*', '', $str);
        // $str = preg_replace('/[а-яё]/iu', '', $str);
        $delimiters = ['.', ';', ':', '(', ')', '!', '?', '/'];
        $str = str_replace($delimiters, ',', $str);

        $ingredients = explode(',', $str);
        if(!$ingredients) {
            return;
        }

        foreach($ingredients as $ingredient) {
            $ingredient = trim($ingredient);

            // if(preg_match("/[а-яё]/iu", $ingredient)) {
            //     continue;
            // }
            if(strlen($ingredient) < 3) {
                continue;
            }
            // No English letters
            // if(!preg_match("/[a-z]/iu", $ingredient)) {
            //     continue;
            // }
            // var_dump($ingredient);
            if(!isset($this->ingredients[$ingredient])) {
                continue;
            }
            $ingredient_id = $this->ingredients[$ingredient];
            // var_dump($ingredient_id);
            // if(strlen(preg_replace('/[^a-z]/iu', '', $ingredient)) < 4) {
            //     continue;
            // }
            // if(substr_count($ingredient, ' ') > 3) {
            //     continue;
            // }
            // $words = explode(' ', $ingredient);
            // if(!array_reduce($words, function ($res, $word) { return $res && (strlen($word) >= 4); }, true)) {
            //     continue;
            // }

            // $ingredientObj = Ingredient::firstOrCreate([
            //     'name' => $ingredient,
            // ]);
    
            $product->ingredients()->syncWithoutDetaching($ingredient_id);
        }
    }
}
