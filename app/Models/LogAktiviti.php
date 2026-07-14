<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktiviti extends Model
{
    protected $table = 'log_aktiviti';

    protected $fillable = [
        'user_id',
        'aktiviti',
        'item_id',
        'data_lama',
        'data_baru',
    ];

    protected $casts = [
        'data_lama' => 'array',
        'data_baru' => 'array',
    ];

    /**
     * Get the user who performed the activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the item associated with the activity (if it still exists).
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Inventori::class, 'item_id');
    }
}
