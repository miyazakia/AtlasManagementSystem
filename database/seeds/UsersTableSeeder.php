<?php

use Illuminate\Database\Seeder;
use App\Models\Users\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('users')->insert([
            'over_name' => 'sakana',
            'under_name' => 'kawano',
            'over_name_kana' => 'サカナ',
            'under_name_kana' => 'カワノ',
            'mail_address' => 'sakana@co.jp',
            'sex' => '1',
            'birth_day' => '2000-01-04',
            'role' => '1',
            'password' => bcrypt('00000000'),
        ]);

    }
}
