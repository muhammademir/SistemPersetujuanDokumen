<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();
    $password = Hash::make('password'); 
    $faker = fake('id_ID');

    // Dua akun demo untuk penilai technical test
    User::create([
        'name' => 'Demo Pemohon', 'email' => 'pemohon@demo.test',
        'password' => $password, 'email_verified_at' => $now,
    ])->assignRole('pemohon');

    User::create([
        'name' => 'Demo Penilai', 'email' => 'penilai@demo.test',
        'password' => $password, 'email_verified_at' => $now,
    ])->assignRole('penilai');

    foreach ([['pemohon', 1000], ['penilai', 1000]] as [$role, $total]) {
        $rows = [];
        for ($i = 1; $i <= $total; $i++) {
            $rows[] = [
                'name'              => $faker->name(),
                'email'             => "{$role}{$i}@example.test",
                'password'          => $password,
                'email_verified_at' => $now,
                'is_active'         => true,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        // Assign role secara massal lewat pivot Spatie
        $roleId = DB::table('roles')->where('name', $role)->value('id');
        $ids = DB::table('users')->where('email', 'like', "{$role}%@example.test")->pluck('id');

        $pivot = $ids->map(fn ($id) => [
            'role_id' => $roleId, 'model_type' => User::class, 'model_id' => $id,
        ])->all();

        foreach (array_chunk($pivot, 1000) as $chunk) {
            DB::table('model_has_roles')->insert($chunk);
        }
    }
    }
}
