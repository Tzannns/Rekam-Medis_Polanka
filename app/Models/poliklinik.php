<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class poliklinik extends Model
{
    use HasFactory;
    protected $table = 'poliklinik';
    protected $fillable = [
        'nama_poliklinik',
    ];

    public function dokter(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(dokter::class, 'poliklinik_id');
    }
}
