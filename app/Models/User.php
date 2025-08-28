<?php
namespace App\Models;

use App\Models\Model;

class User extends Model
{
	protected static string $table = 'users';
	protected static array $fillable = ['name', 'last_name', 'email', 'password'];
	protected static bool $timestamps = true;

	public static function createSecure(array $data): self
    {
        if (!empty($data['password'])) {
            $data['password'] = password_hash((string)$data['password'], PASSWORD_DEFAULT);
        }
        return parent::create($data);
    }

    public function verifyPassword(string $plain): bool
    {
        return password_verify($plain, (string)($this->password ?? ''));
    }
}
