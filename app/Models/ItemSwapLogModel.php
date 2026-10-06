<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemSwapLogModel extends Model
{
    protected $table = 'item_swap_logs';

    public $timestamps = true;

    public $guarded = [];

    public const STATUS_PENDING = 1;

    public const STATUS_RECEIVED = 2;

    public const STATUS_CANCELLED = 3;
}
