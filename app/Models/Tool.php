<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'comment', 'approved'];

    protected $casts = [
        'approved' => 'boolean',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }
}
