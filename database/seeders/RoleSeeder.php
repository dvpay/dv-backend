<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * RoleSeeder
 */
class RoleSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run(): void
	{
		if (!Role::all()->count()) {
			foreach (UserRole::values() as $role) {
				Role::updateOrCreate(['name' => $role]);
			}
		}

		$users = User::all();

		foreach ($users as $user) {
			$user->assignRole(UserRole::Admin->value);

			if ($user->is_admin) {
				$user->assignRole(UserRole::Root->value);
			}
		}
	}
}
