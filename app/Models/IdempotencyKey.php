<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class IdempotencyKey extends Model
{
    public $timestamps = false;

    protected $fillable = ['key'];
}
