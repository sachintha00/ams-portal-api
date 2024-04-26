<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OAuthClientsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('oauth_clients')->insert([
            [
                'user_id' => null,
                'name' => 'Personal Access Client',
                'secret' => 'ATNsOoQ917wjdvX9NR2QbcgbZUC3AtSp0q0Q0GVL',
                'provider' => null,
                'redirect' => '',
                'personal_access_client' => true,
                'password_client' => false,
                'revoked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => null,
                'name' => 'Password Grant Client',
                'secret' => 'UIWRrPs42KfQywygfOuBwKx1PtLMaF1xZ7WPXo57',
                'provider' => null,
                'redirect' => '',
                'personal_access_client' => false,
                'password_client' => true,
                'revoked' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}