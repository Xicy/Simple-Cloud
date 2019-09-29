<?php

use App\User;
use Illuminate\Database\Seeder;

class UserSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [

            ['id' => 1, 'name' => 'Admin', 'email' => 'admin@admin.com', 'password' => '$2y$10$ut/j7z9KpLJ1M4Ox3wcTN.e5ovCaIDbfSB4TxC6rwZoaZnQgftvmi', 'role_id' => 1, 'remember_token' => '',],

        ];

        foreach ($items as $item) {
            User::create($item);
        }
    }
}
