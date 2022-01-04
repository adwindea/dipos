<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Tenant;
use App\Models\RoleHierarchy;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::create([
            'name' => 'Cafe',
            'address' => 'Semarang',
            'phone' => '081234567890',
            'email' => 'ardana@aiotku.com',
            'sub' => 'cafe'
        ]);

        $user = User::create([
            'name' => 'Ardana',
            'email' => 'ardana@aiotku.com',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
            'remember_token' => Str::random(10),
            'menuroles' => 'user,admin',
            'status' => 'Active',
            'sub' => $tenant->sub,
            'tenant_id' => $tenant->id
        ]);

        $user->assignRole('user');
        $user->assignRole('admin');
    }
}
