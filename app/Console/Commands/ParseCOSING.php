<?php

namespace App\Console\Commands;

use App\Models\Ingredient;
use App\Models\IngredientSynonym;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ParseCOSING extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parse-cosing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private $translations = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // $this->parse(true);
        // $this->parse(false);

        $file = storage_path("import/synonyms_translations.csv");
    
        if(($handle = fopen($file, "r")) === FALSE) {
            $this->info('not found '.$file);
            return;
        }
        
        while (($data = fgetcsv($handle, null, ';')) !== FALSE) {
            $data = array_map(function($item) {
                return mb_strtolower(trim($item));
            }, $data);

            if($data[0] === $data[1]) {
                continue;
            }

            $this->translations[$data[1]][] = $data[0];
        }

        foreach($this->translations as $ruTranslation => $synonyms) {
            $result = IngredientSynonym::select('ingredient_id')
                ->whereIn('name', $synonyms)
                ->groupBy('ingredient_id')
                ->orderByRaw('COUNT(ingredient_id) DESC')
                ->first();

            $synonym = new IngredientSynonym();
            $synonym->name = $ruTranslation;
            $synonym->ingredient_id = $result->ingredient_id;
            $synonym->language = IngredientSynonym::language_ru;
            $synonym->save();
        }
    }

    public function parse($excludeCI)
    {
        $file = storage_path("import/COSING_Ingredients-Fragrance Inventory_v2.csv");
    
        if(($handle = fopen($file, "r")) === FALSE) {
            $this->info('not found '.$file);
            return;
        }

        //Skip first line
        fgetcsv($handle);
        $count = 0;

        while (($data = fgetcsv($handle)) !== FALSE) {
            // var_dump($data);exit;

            $data = array_map(function($item) {
                return mb_strtolower(trim($item));
            }, $data);

            if(empty($data[1])) {
                continue;
            }

            $ingredientName = $data[1];
            $inn_name = $data[2];
            $inn_name = trim(strtok($inn_name, "["));
            $eur_name = $data[3];

            if($this->wordsCount($ingredientName) > 10) {
                continue;
            }

            if($excludeCI) {
                if(strpos($ingredientName, 'CI ') === 0) {
                    continue;
                }
            }else {
                if(strpos($ingredientName, 'CI ') !== 0) {
                    continue;
                }
            }
            
            $ingredient = new Ingredient();
            $ingredient->name = $ingredientName;
            $ingredient->cosing_ref_no = $data[0];
            $ingredient->cas_no = $data[4];
            $ingredient->ec_no = $data[5];
            $ingredient->description = $data[6];
            $ingredient->function = $data[8];

            $synonyms = [$ingredientName];
            if($inn_name && !in_array($inn_name, $synonyms)) {
                $synonyms[] = $inn_name;
            }
            if($eur_name) {
                $synonyms = array_unique(array_merge($synonyms, array_map(function($item) {
                    return trim($item);
                }, explode('/', $eur_name))));
            }

            foreach($synonyms as $key => $synonymName) {
                
            }

            foreach($synonyms as $key => $synonymName) {
                $synonym = IngredientSynonym::where('name', $synonymName)->first();
                if($synonym) {
                    $this->info('skipped ingredient - '.$ingredientName);
                    unset($synonyms[$key]);
                    $ingredient = Ingredient::find($synonym->ingredient_id);
                    break;
                }
            }



            $ingredient->save();

            foreach($synonyms as $synonymName) {
                $ingredient->ingredient_synonyms()->create([
                    'name' => $synonymName,
                    'language' => IngredientSynonym::language_en,
                ]);
            }
        }
        fclose($handle);

        // $this->info($filename.' - '.$count);
    }

    public function wordsCount($str)
    {
        $delimiters = ['.', ';', ':', '(', ')', '!', '?', '/',];
        $str = str_replace($delimiters, ',', $str);

        $words = explode(',', $str);
        return count($words);
    }
}
