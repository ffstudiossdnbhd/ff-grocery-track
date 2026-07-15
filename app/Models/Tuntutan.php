<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tuntutan extends Model
{
    protected $table = 'tuntutan';

    protected $fillable = [
        'user_id',
        'nama_item',
        'tag',
        'nilai_tuntutan',
        'tarikh_beli',
        'minggu_tuntutan',
        'status',
    ];

    protected $casts = [
        'tarikh_beli' => 'date',
        'nilai_tuntutan' => 'decimal:2',
    ];

    /**
     * Get the user who submitted the claim.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
