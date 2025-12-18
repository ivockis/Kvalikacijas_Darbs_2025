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
        'estimated_hours',
        'is_public',
        'is_blocked',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_blocked' => 'boolean',
        'estimated_hours' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['cover_image_url'];

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
        return $this->hasMany(Image::class)->whereNotNull('project_id'); // Only images explicitly tied to a project
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function likers()
    {
        return $this->belongsToMany(User::class, 'likes');
    }

    /**
     * Set a specific image as the cover image for this project.
     * Ensures only one image is marked as cover.
     */
    public function setCoverImage(Image $newCoverImage)
    {
        // First, ensure the newCoverImage is actually associated with this project.
        if ($newCoverImage->project_id !== $this->id) {
            throw new \InvalidArgumentException("Image #{$newCoverImage->id} does not belong to Project #{$this->id}.");
        }

        // Set all other images for this project to not be the cover
        $this->images()->where('id', '!=', $newCoverImage->id)->update(['is_cover' => false]);

        // Set the new image as the cover
        $newCoverImage->is_cover = true;
        $newCoverImage->save();
    }

    /**
     * Get the project's cover image.
     */
    public function coverImage()
    {
        return $this->hasOne(Image::class)->where('is_cover', true)->whereNotNull('project_id');
    }

    /**
     * Get the URL of the cover image, or a default placeholder.
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->coverImage) {
            return asset('storage/' . $this->coverImage->path);
        }

        // Return a default placeholder image if no cover is set
        return 'https://via.placeholder.com/400x300.png?text=No+Image';
    }
}