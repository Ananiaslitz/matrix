<?php

namespace Ananiaslitz\Matrix\Model;

use Hyperf\Database\Model\Model;
use Ananiaslitz\Matrix\Model\Traits\UsesWardenConnection;

class Tenant extends Model
{
    use UsesWardenConnection;

    protected array $fillable = [
        'name',
        'domain',
        'database',
        'host',
        'port',
        'username',
        'password',
        'config',
    ];
}