<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\City;

class RegionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Les 24 gouvernorats de Tunisie
        $regions = [
            'Tunis', 'Ariana', 'Ben Arous', 'Manouba', 'Nabeul', 'Zaghouan', 
            'Bizerte', 'Béja', 'Jendouba', 'Le Kef', 'Siliana', 'Sousse', 
            'Monastir', 'Mahdia', 'Sfax', 'Kairouan', 'Kasserine', 'Sidi Bouzid', 
            'Gabès', 'Médenine', 'Tataouine', 'Gafsa', 'Tozeur', 'Kébili'
        ];

        // Créer les régions
        foreach ($regions as $regionName) {
            Region::create(['name' => $regionName]);
        }

        // Villes par gouvernorat avec shipping_cost = 8.000
        $cities = [
            'Tunis' => [
                ['name' => 'Tunis'], ['name' => 'Le Bardo'], ['name' => 'La Goulette'], 
                ['name' => 'Carthage'], ['name' => 'Sidi Bou Said'], ['name' => 'La Marsa']
            ],
            'Ariana' => [
                ['name' => 'Ariana'], ['name' => 'Raoued'], ['name' => 'Sidi Thabet'], 
                ['name' => 'Kalâat el-Andalous'], ['name' => 'Soukra']
            ],
            'Ben Arous' => [
                ['name' => 'Ben Arous'], ['name' => 'El Mourouj'], ['name' => 'Hammam Lif'], 
                ['name' => 'Radès'], ['name' => 'Mégrine'], ['name' => 'Mornag']
            ],
            'Manouba' => [
                ['name' => 'Manouba'], ['name' => 'Denden'], ['name' => 'Douar Hicher'], 
                ['name' => 'Oued Ellil'], ['name' => 'Borj El Amri']
            ],
            'Nabeul' => [
                ['name' => 'Nabeul'], ['name' => 'Hammamet'], ['name' => 'Korba'], 
                ['name' => 'Kelibia'], ['name' => 'Menzel Temime']
            ],
            'Zaghouan' => [
                ['name' => 'Zaghouan'], ['name' => 'El Fahs'], ['name' => 'Nadhour'], 
                ['name' => 'Bir Mcherga'], ['name' => 'Zriba']
            ],
            'Bizerte' => [
                ['name' => 'Bizerte'], ['name' => 'Menzel Bourguiba'], ['name' => 'Ras Jebel'], 
                ['name' => 'Mateur'], ['name' => 'Tinja']
            ],
            'Béja' => [
                ['name' => 'Béja'], ['name' => 'Testour'], ['name' => 'Téboursouk'], 
                ['name' => 'Nefza'], ['name' => 'Mejez El Bab']
            ],
            'Jendouba' => [
                ['name' => 'Jendouba'], ['name' => 'Aïn Draham'], ['name' => 'Tabarka'], 
                ['name' => 'Bou Salem'], ['name' => 'Fernana']
            ],
            'Le Kef' => [
                ['name' => 'Le Kef'], ['name' => 'Tajerouine'], ['name' => 'Kalaat Senan'], 
                ['name' => 'Dahmani'], ['name' => 'Nebeur']
            ],
            'Siliana' => [
                ['name' => 'Siliana'], ['name' => 'El Krib'], ['name' => 'Gaâfour'], 
                ['name' => 'Kesra'], ['name' => 'Makthar']
            ],
            'Sousse' => [
                ['name' => 'Sousse'], ['name' => 'Hammam Sousse'], ['name' => 'Akouda'], 
                ['name' => 'Kalâa Kebira'], ['name' => 'Msaken'], ['name' => 'Enfidha']
            ],
            'Monastir' => [
                ['name' => 'Monastir'], ['name' => 'Jemmal'], ['name' => 'Zeramdine'], 
                ['name' => 'Bekalta'], ['name' => 'Ksar Hellal']
            ],
            'Mahdia' => [
                ['name' => 'Mahdia'], ['name' => 'El Jem'], ['name' => 'Ksour Essef'], 
                ['name' => 'Chebba'], ['name' => 'Sidi Alouane']
            ],
            'Sfax' => [
                ['name' => 'Sfax'], ['name' => 'Sakiet Ezzit'], ['name' => 'Chihia'], 
                ['name' => 'El Ain'], ['name' => 'Gremda'], ['name' => 'Mahres']
            ],
            'Kairouan' => [
                ['name' => 'Kairouan'], ['name' => 'Haffouz'], ['name' => 'Sbikha'], 
                ['name' => 'Chebika'], ['name' => 'Nasrallah']
            ],
            'Kasserine' => [
                ['name' => 'Kasserine'], ['name' => 'Sbeitla'], ['name' => 'Thala'], 
                ['name' => 'Feriana'], ['name' => 'Foussana']
            ],
            'Sidi Bouzid' => [
                ['name' => 'Sidi Bouzid'], ['name' => 'Regueb'], ['name' => 'Jilma'], 
                ['name' => 'Menzel Bouzaiane'], ['name' => 'Bir El Hafey']
            ],
            'Gabès' => [
                ['name' => 'Gabès'], ['name' => 'Ghannouch'], ['name' => 'Mareth'], 
                ['name' => 'Matmata'], ['name' => 'Métouia']
            ],
            'Médenine' => [
                ['name' => 'Médenine'], ['name' => 'Zarzis'], ['name' => 'Djerba Houmt Souk'], 
                ['name' => 'Djerba Midoun'], ['name' => 'Ben Guerdane']
            ],
            'Tataouine' => [
                ['name' => 'Tataouine'], ['name' => 'Ghomrassen'], ['name' => 'Bir Lahmar'], 
                ['name' => 'Remada']
            ],
            'Gafsa' => [
                ['name' => 'Gafsa'], ['name' => 'Métlaoui'], ['name' => 'Redeyef'], 
                ['name' => 'El Ksar'], ['name' => 'Moularès']
            ],
            'Tozeur' => [
                ['name' => 'Tozeur'], ['name' => 'Nefta'], ['name' => 'Degache'], 
                ['name' => 'Tameghza']
            ],
            'Kébili' => [
                ['name' => 'Kébili'], ['name' => 'Douz'], ['name' => 'Souk Lahad']
            ],
        ];

        // Ajouter les villes aux régions
        foreach ($cities as $regionName => $regionCities) {
            $region = Region::where('name', $regionName)->first();
            if ($region) {
                foreach ($regionCities as $cityData) {
                    City::create([
                        'region_id' => $region->id,
                        'name' => $cityData['name'],
                        'shipping_cost' => 8.000
                    ]);
                }
            }
        }
    }
}
