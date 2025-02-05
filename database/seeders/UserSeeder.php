<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $item               = new User();
        $item->key          = Str::uuid()->toString();
        $item->name         = 'Dimas Purbo Choirul Mustaqim';
        $item->nim          = 02052025;
        $item->ymd          = 01122025;
        $item->email        = 'dimas@adhivasindo.com';
        $item->password     = bcrypt('dimasdimas');
        $item->save();
    }
}
