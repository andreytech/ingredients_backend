<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ParseOzon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:parse-ozon-self';

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
        $filesToCategory = [
            1 => 'ozon_aksessuari_dlia_ochisheniya_lica.csv',
            2 => 'nabori_sredstv_dlia_lica.csv',
            3 => 'matiruiushie_salfetki.csv',
            4 => 'kosmeticheskie_instrumenti.csv',
            5 => 'voskovie_poloski.csv',
            6 => 'dorozhnie_flakoni.csv',
            7 => 'uhod_za_gubami.csv',
        ];

        foreach($filesToCategory as $category => $filename) {
            $file = storage_path('import/ozon/' . $filename);

            if(($handle = fopen($file, "r")) === FALSE) {
                $this->info('not found '.$file);
                continue;
            }

            //Skip first line
            fgetcsv($handle, null, ';');
            $count = 0;

            while (($data = fgetcsv($handle, null, ';')) !== FALSE) {
                
                // Remove non-utf-8 chars
                $data = array_map(function($item) {
                    return preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $item);
                }, $data);

                $data = array_map(function($item) {
                    return trim($item);
                }, $data);

                $product = DB::table('products')
                    ->select(DB::raw('id, LENGTH(description) as description_length'))
                    ->where('link', $data[1])
                    ->first();

                if($product) {
                    // var_dump($product->description_length);
                    // var_dump(strlen($data[3]));
                    if($product->description_length >= strlen($data[3])) {
                        continue;
                    }

                    DB::table('products')
                        ->where('id', $product->id)
                        ->update(['description' => $data[3]]);

                    continue;
                }

                $count++;

                $product = new Product();
                $product->name = $data[0];
                $product->brand = $data[2];
                $product->category = $category;
                $product->link = $data[1];
                $product->description = $data[3];
                $product->images = json_encode(array_map('trim', explode(',', $data[4])));
                $product->save();
            }
            fclose($handle);

            $this->info($filename.' - '.$count);

        }
        
    }

}
