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

    private $ingredients_with_comma = null;

    private $ozonMainCategories = [
        'Очищение и умывание' => 12,
        'Увлажнение и питание' => 13,
        'Маски' => 14,
        'Лосьоны' => 15,
        'Антивозрастной уход' => 16,
        'Средства для проблемной кожи' => 17,
    ];

    private $skipItems = true;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        // Product::truncate();
        // DB::table('ingredient_product')->truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->ingredients = IngredientSynonym::select('ingredient_id', 'name')->orderByRaw('CHAR_LENGTH(name) DESC')->pluck('ingredient_id', 'name')->toArray();

        foreach ($this->ingredients as $name => $id) {
            if (mb_stristr($name, ',') !== false) {
                $this->ingredients_with_comma[$name] = $id;
                unset($this->ingredients[$name]);
            }
        }

        // $this->handleCategory('ozon', null, 'ozon_main_1.csv', function ($data) {
        //     $fields['images'] = [];
        //     $fields['images'] = array_merge($fields['images'], $this->handleImages($data, 9, 14));
        //     $fields['images'] = array_merge($fields['images'], $this->handleImages($data, 34, 36));
        //     $fields['images'] = array_merge($fields['images'], $this->handleImages($data, 38, 39));
        //     $fields['images'] = array_merge($fields['images'], $this->handleImages($data, 41, 45));
        //     $fields['description'] = empty($data[7]) ? '' : $data[7];
        //     $fields['link'] = empty($data[0]) ? '' : $data[0];
        //     $fields['name'] = empty($data[5]) ? '' : $data[5];
        //     $fields['brand'] = empty($data[3]) ? '' : $data[3];
        //     $fields['ingredients'] = empty($data[8]) ? '' : $data[8];

        //     return $fields;
        // });

        $this->handleCategory('ozon', null, 'ozon_main_2.csv', function ($data) {
            $fields['images'] = [];
            $fields['images'] = array_merge($fields['images'], $this->handleImages($data, 9, 11));
            $fields['images'] = array_merge($fields['images'], $this->handleImages($data, 30, 41));
            $fields['images'] = array_merge($fields['images'], $this->handleImages($data, 46, 46));
            $fields['images'] = array_merge($fields['images'], $this->handleImages($data, 48, 48));
            $fields['images'] = array_merge($fields['images'], $this->handleImages($data, 59, 63));
            $fields['description'] = empty($data[7]) ? '' : $data[7];
            $fields['link'] = empty($data[0]) ? '' : $data[0];
            $fields['name'] = empty($data[5]) ? '' : $data[5];
            $fields['brand'] = empty($data[3]) ? '' : $data[3];
            $fields['ingredients'] = empty($data[8]) ? '' : $data[8];

            return $fields;
        });

        $this->handleCategory('ozon', null, 'ozon_main_3.csv', function ($data) {
            $fields['images'] = [];
            $fields['images'] = array_merge($fields['images'], $this->handleImages($data, 9, 10));
            $fields['images'] = array_merge($fields['images'], $this->handleImages($data, 22, 23));
            $fields['images'] = array_merge($fields['images'], $this->handleImages($data, 27, 28));
            $fields['images'] = array_merge($fields['images'], $this->handleImages($data, 31, 39));
            $fields['description'] = empty($data[7]) ? '' : $data[7];
            $fields['link'] = empty($data[0]) ? '' : $data[0];
            $fields['name'] = empty($data[5]) ? '' : $data[5];
            $fields['brand'] = empty($data[3]) ? '' : $data[3];
            $fields['ingredients'] = empty($data[8]) ? '' : $data[8];

            return $fields;
        });
        
        $filesToCategory = [
            1 => 'aksessuari.csv',
            2 => 'nabori.csv',
            3 => 'salfetki.csv',
            4 => 'instrumenti.csv',
            5 => 'voskovie_poloski.csv',
        ];

        foreach ($filesToCategory as $category => $filename) {
            $this->handleCategory('ozon', $category, $filename, function ($data) {
                $fields['images'] = [];
                $fields['images'] = array_merge($fields['images'], $this->handleImages($data, 7, 13));
                $fields['description'] = $data[5];
                $fields['link'] = $data[0];
                $fields['name'] = $data[4];
                $fields['brand'] = $data[2];
                $fields['ingredients'] = empty($data[6]) ? '' : $data[6];

                return $fields;
            });
        }

        $filesToCategory = [
            8 => 'nabori.csv',
            9 => 'uhod_za_gubami.csv',
            10 => 'dorozhnie_flakoni.csv',
            11 => 'aksessuari.csv',
        ];

        foreach ($filesToCategory as $category => $filename) {
            $this->handleCategory('yandex', $category, $filename, function ($data, $category = null) {
                $fields['images'] = [];
                $fields['images'] = array_merge($fields['images'], $this->handleImages($data, 6, 25));
                $fields['description'] = $data[3];
                $fields['link'] = $data[4];
                $fields['name'] = $data[5];
                $fields['brand'] = $data[26];
                if ($category === 8) {
                    // nabori
                    $fields['ingredients'] = empty($data[40]) ? '' : $data[40];
                }
                if ($category === 9) {
                    // uhod_za_gubami
                    $fields['ingredients'] = empty($data[39]) ? '' : $data[39];
                }

                return $fields;
            });
        }
    }

    public function handleCategory($platform, $category, $filename, $handleFields)
    {
        $file = storage_path("import/{$platform}/" . $filename);

        if (($handle = fopen($file, "r")) === FALSE) {
            $this->info('not found ' . $file);
            return;
        }

        //Skip first line
        fgetcsv($handle, null, ';');
        $count = 0;

        while (($data = fgetcsv($handle, null, ';')) !== FALSE) {
            // var_dump($data);
            // exit;

            $data = array_map(function ($item) {
                return trim($item);
            }, $data);

            if ($category === null) {
                if (isset($this->ozonMainCategories[$data[1]])) {
                    $ozonCategory = $this->ozonMainCategories[$data[1]];
                }

                $this->handleItem($ozonCategory, $data, $handleFields);
            } else {
                $this->handleItem($category, $data, $handleFields);
            }

            $count++;

            if($count % 1000 === 0) {
                $this->info($count);
            }
        }
        fclose($handle);

        $this->info($filename . ' - ' . $count);
    }

    private function handleItem($category, $data, $handleFields)
    {
        $ingredients = '';
        $images = [];

        extract($handleFields($data, $category));

        if(!$link || !$name) {
            return;
        }
        
        if($link === 'https://www.ozon.ru/product/774501180/?oos_search=false') {
            $this->skipItems = false;
        }

        if($this->skipItems) {
            return;
        }
        // $product = DB::select('SELECT id FROM products WHERE `link` = ?', [$link]);

        // if ($product) {
        //     return;
        // }

        // Remove non-utf-8 chars
        $data = array_map(function ($item) {
            return preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $item);
        }, $data);
        
        $description = strip_tags($description);
        $description = str_replace('Показать полностью', '', $description);

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

        if ($description && substr_count($description, ',') > 10) {
            $this->handleIngredients($description, $product);
        }
    }

    public function handleIngredients($str, $product)
    {
        if (!$str) {
            return;
        }
        $str = strip_tags(mb_strtolower($str));
        $str = str_replace('&nbsp;', ' ', $str);
        $str = str_replace('*', '', $str);
        // $str = preg_replace('/[а-яё]/iu', '', $str);

        foreach ($this->ingredients_with_comma as $ingredient => $ingredientId) {
            if (mb_strstr($str, $ingredient) !== false) {
                $this->addIngredient($product, $ingredientId);
            }
        }

        $delimiters = ['.', ';', ':', '(', ')', '!', '?'];
        $str = str_replace($delimiters, ',', $str);

        $ingredients = explode(',', $str);
        if (!$ingredients) {
            return;
        }

        foreach ($ingredients as $ingredient) {
            $ingredient = trim($ingredient);

            // if(preg_match("/[а-яё]/iu", $ingredient)) {
            //     continue;
            // }
            if (strlen($ingredient) < 3) {
                continue;
            }
            // No English letters
            // if(!preg_match("/[a-z]/iu", $ingredient)) {
            //     continue;
            // }
            // var_dump($ingredient);
            if (isset($this->ingredients[$ingredient])) {
                $this->addIngredient($product, $this->ingredients[$ingredient]);
                continue;
            }

            if (mb_strpos($ingredient, '/') !== false) {
                $ingredients = array_map(function ($item) {
                    return trim($item);
                }, explode('/', $ingredient));
                foreach ($ingredients as $ingredient) {
                    if (isset($this->ingredients[$ingredient])) {
                        $this->addIngredient($product, $this->ingredients[$ingredient]);
                    }
                }
            }


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
        }
    }

    private function addIngredient($product, $ingredientId)
    {
        $product->ingredients()->syncWithoutDetaching($ingredientId);
    }

    public function handleImages($data, $from, $to)
    {
        $images = [];
        for ($i = $from; $i <= $to; $i++) {
            if (!empty($data[$i])) {
                $images[] = $data[$i];
            }
        }

        return $images;
    }
}
