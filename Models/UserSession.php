<?php
namespace App\Models;

use App\Models\Model;

final class UserSession extends Model
{
    protected static string $table = 'user_sessions';
    protected static string $primaryKey = 'id';
    protected static array  $fillable = [
        'user_id', 'token_hash', 'expires_at', 'ip', 'user_agent', 'created_at', 'updated_at'
    ];
    protected static bool $timestamps = true;
}
