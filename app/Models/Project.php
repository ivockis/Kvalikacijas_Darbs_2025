<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'materials',
        'creation_time',
        'is_public',
        'is_blocked',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function tools()
    {
        return $this->belongsToMany(Tool::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function likers()
    {
        return $this->belongsToMany(User::class, 'likes');
    }
}
