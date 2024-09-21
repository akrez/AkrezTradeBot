<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Coin
 *
 * @property int $id
 * @property int|null $chat_id
 * @property int|null $coin_id
 * @property string|null $coin_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Coin extends Model
{
    use HasFactory;

    protected $table = 'coins';

    protected $guarded = [
        'created_at',
        'updated_at',
    ];
}
