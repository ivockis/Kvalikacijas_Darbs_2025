<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 *
 * Represents a user of the application. This model manages user authentication,
 * profile data, and relationships to other entities like projects, ratings, and comments.
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     * These fields can be filled using mass assignment, providing a security measure.
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'surname',
        'username',
        'email',
        'password',
        'is_admin',
        'is_blocked',
        'profile_image_id',
    ];

    /**
     * The accessors to append to the model's array form.
     * 'profile_image_url' will be automatically included when the model is converted to an array or JSON.
     * @var array
     */
    protected $appends = ['profile_image_url'];

    /**
     * The attributes that should be hidden for serialization.
     * These fields are not included when the model is converted to JSON or arrays.
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     * Defines how certain attributes are cast to native PHP types when retrieved from the database.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed', // Hashes the password for security.
            'is_admin' => 'boolean',
            'is_blocked' => 'boolean',
        ];
    }

    /**
     * Defines a one-to-many relationship with the Project model.
     * A user can create many projects.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Defines a one-to-many relationship with the Complaint model.
     * A user can submit multiple complaints.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    /**
     * Defines a many-to-many relationship with the Project model via the 'likes' pivot table.
     * Shows which projects this user has liked.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function likedProjects()
    {
        return $this->belongsToMany(Project::class, 'likes');
    }

    /**
     * Defines a one-to-one relationship with the Image model for the user's profile picture.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profileImage()
    {
        return $this->hasOne(Image::class, 'id', 'profile_image_id');
    }

    /**
     * Accessor method to get the URL of the user's profile image.
     * If no profile image is set, it returns a default avatar URL.
     * Automatically invoked when accessing `$user->profile_image_url`.
     * @return string
     */
    public function getProfileImageUrlAttribute()
    {
        if ($this->profileImage) {
            return asset('storage/' . $this->profileImage->path);
        }
        // Returns a default placeholder image if no profile image is explicitly set.
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name . ' ' . $this->surname) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Defines a one-to-many relationship with the Rating model.
     * A user can submit multiple ratings for different projects.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }

    /**
     * Defines a one-to-many relationship with the Comment model.
     * A user can submit multiple comments on different projects.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}