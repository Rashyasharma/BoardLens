<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\School;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InitialUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if school already exists, if not create it
        $school = School::firstOrCreate(
            ['school_code' => 'IN016'],
            [
                'id' => Str::uuid(),
                'school_name' => 'IN016 Centre',
                'address' => 'Sample Address',
            ]
        );

        // Check if user already exists, if not create it
        User::firstOrCreate(
            ['username' => 'IN016'],
            [
                'id' => Str::uuid(),
                'name' => 'IN016 Administrator',
                'email' => 'officer@school.edu',
                'password' => Hash::make('CIE@2609Rashya'),
                'role' => 'admin',
                'school_id' => $school->id,
            ]
        );
    }
}
