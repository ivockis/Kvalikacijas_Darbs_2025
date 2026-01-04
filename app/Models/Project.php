<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Project
 *
 * Represents a user-created project within the application.
 * This model serves as the central point connecting users, images,
 * categories, tools, and social features (ratings, comments, likes).
 */
class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * This acts as a security mechanism to prevent unintended or malicious
     * modification of sensitive fields (e.g., 'id' or 'user_id') when
     * using mass assignment methods like `create()` or `update()`.
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'materials',
        'estimated_hours',
        'is_public',
        'is_blocked',
    ];

    /**
     * The attributes that should be cast to native types.
     * For example, `is_public` is cast from 0/1 to a boolean `true`/`false`.
     * This provides automatic type conversion when the model is retrieved.
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
        'is_blocked' => 'boolean',
        'estimated_hours' => 'integer',
    ];

    /**
     * The accessors to append to the model's array or JSON form.
     * In this case, 'cover_image_url' will be automatically included
     * by calling the `getCoverImageUrlAttribute()` method.
     * @var array
     */
    protected $appends = ['cover_image_url'];

    /**
     * Defines a "belongs to" inverse one-to-many relationship with the User model.
     * Each project belongs to a single user.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Defines a "many to many" relationship with the Category model.
     * A project can belong to multiple categories, and a category can have many projects.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Defines a "many to many" relationship with the Tool model.
     * A project can use multiple tools, and a tool can be used in many projects.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tools()
    {
        return $this->belongsToMany(Tool::class);
    }

    /**
     * Defines a "one to many" relationship with the Image model.
     * A project can have multiple associated images.
     * The `whereNotNull('project_id')` ensures only images explicitly tied to a project are returned.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(Image::class)->whereNotNull('project_id');
    }

    /**
     * Defines a "one to many" relationship with the Complaint model.
     * A project can have multiple complaints filed against it.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    /**
     * Defines a "many to many" relationship with the User model via the 'likes' pivot table.
     * This shows which users have liked this project.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likers()
    {
        return $this->belongsToMany(User::class, 'likes');
    }

    /**
     * Defines a "one to many" relationship with the Rating model.
     * A project can receive multiple ratings from different users.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Defines a "one to many" relationship with the Comment model.
     * A project can have multiple comments from different users.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Custom method to safely and atomically set a specific image as the cover image for this project.
     * An atomic operation means either all actions succeed or none do, ensuring data integrity.
     *
     * @param Image $newCoverImage The image to be set as the new cover.
     * @throws \InvalidArgumentException If the provided image does not belong to this project.
     * @return void
     */
    public function setCoverImage(Image $newCoverImage)
    {
        // First, ensure the newCoverImage is actually associated with this project.
        if ($newCoverImage->project_id !== $this->id) {
            throw new \InvalidArgumentException("Image #{$newCoverImage->id} does not belong to Project #{$this->id}.");
        }

        // Use a database transaction to ensure data integrity.
        \Illuminate\Support\Facades\DB::transaction(function () use ($newCoverImage) {
            // Set all other images for this project to not be the cover image.
            $this->images()->update(['is_cover' => false]);

            // Refresh the model instance to get the latest data from the database,
            // including the change from the mass update above.
            $newCoverImage->refresh();

            // Set the new image as the cover image.
            $newCoverImage->is_cover = true;
            $newCoverImage->save();
        });
    }

    /**
     * Defines a "one to one" relationship to retrieve the project's cover image.
     * It specifically looks for an image where `is_cover` is `true`.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function coverImage()
    {
        return $this->hasOne(Image::class)->where('is_cover', true)->whereNotNull('project_id');
    }

    /**
     * Accessor method to get the full URL of the project's cover image.
     * If no cover image is set, it returns a default placeholder URL.
     * Automatically invoked when accessing `$project->cover_image_url`.
     * @return string
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->coverImage) {
            return asset('storage/' . $this->coverImage->path);
        }

        // Returns a placeholder image if no cover is explicitly set.
        return 'https://via.placeholder.com/400x300.png?text=No+Image';
    }

    /**
     * Accessor method that dynamically calculates and returns the project's average rating.
     * Automatically invoked when accessing `$project->average_rating`.
     * @return int
     */
    public function getAverageRatingAttribute()
    {
        // Calculates the average value from all associated ratings and rounds it to the nearest integer.
        // If there are no ratings, `avg()` returns `null`, which `round()` then converts to 0.
        return round($this->ratings()->avg('rating'));
    }
}