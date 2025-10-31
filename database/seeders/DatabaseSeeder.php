<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\MenuSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([RoleSeeder::class,
                            MenuSeeder::class,
                            RoleMenuSeeder::class,
                            KaryawanSeeder::class,
                            MemberSeeder::class,
                            RightAccessSeeder::class,
                            DivisiSeeder::class,
                            SubDivisiSeeder::class,
                            PosisiSeeder::class,
                            KaryawanSeeder::class,
                            AccClassSeeder::class,
                            AccSubclassSeeder::class,
                            CaraBayarSeeder::class,
                            LiburNasionalSeeder::class,
                            RoleSeeder::class,
                            ShiftSeeder::class,
                            WarehouseSeeder::class,
                            LeaveRequestSeeder::class,
                        ]);


        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        //  \App\Models\User::factory()->create([
        //      'name' => 'John',
        //      'last_name' => 'Doe',
        //      'password' => 'password',
        //      'email' => 'test@example.com',
        //  ]);
    }
}
