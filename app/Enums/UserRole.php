<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * RBAC user roles
 */
enum UserRole: string
{
    case Root = 'root';
    case Admin = 'admin';
	//case User = 'user';
	case Support = 'support';

	/**
	 * @return array
	 */
	public static function getRoleList(): array
	{
		return [
            self::Root->value => self::Root->value,
			//self::User->value    => self::User->value,
			self::Admin->value   => self::Admin->value,
			self::Support->value => self::Support->value,
		];
	}

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
