<?php

namespace App\Console\Commands;

use App\Models\Ingredient;
use App\Models\IngredientSynonyms;
use Illuminate\Console\Command;

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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->parse(true);
        $this->parse(false);
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
                return strtolower(trim($item));
            }, $data);

            if(empty($data[1])) {
                continue;
            }

            $ingredientName = $data[1];
            $inn_name = $data[2];
            $inn_name = trim(substr($inn_name, 0, strpos($inn_name, "[")));
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
                $synonym = IngredientSynonyms::where('name', $synonymName)->first();
                if($synonym) {
                    $this->info('skipped ingredient - '.$ingredientName);
                    unset($synonyms[$key]);
                    $ingredient = Ingredient::find($synonym->ingredient_id);
                }
            }

            $ingredient->save();

            foreach($synonyms as $synonymName) {
                $ingredient->ingredient_synonyms()->create([
                    'name' => $synonymName,
                    'language' => IngredientSynonyms::language_en,
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
