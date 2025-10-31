<?php

namespace App\Models\Comprof;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Datastaf extends Model
{
    protected $table = 'datastaf_tabel';

    protected $fillable = [
        'name',
        'jabatan',
        'profile_image',
        'description',
        'education',
        'status',
        'social_facebook',
        'social_twitter',
        'social_linkedin',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $appends = ['profile_image_url', 'clean_description'];

    public function getProfileImageUrlAttribute()
    {
        if ($this->profile_image) {
            return Storage::disk('public')->exists($this->profile_image)
                ? asset('storage/' . $this->profile_image)
                : asset('images/default-profile.png');
        }

        return asset('images/default-profile.png');
    }

    public function getCleanDescriptionAttribute()
    {
        if (empty($this->description)) {
            return '';
        }

        $description = $this->description;

        // Remove specific unwanted tags but keep formatting tags
        $description = preg_replace('/<p[^>]*>/', '', $description); // Remove opening <p>
        $description = str_replace('</p>', '<br>', $description);   // Replace closing </p> with <br>
        $description = str_replace('&nbsp;', ' ', $description);    // Replace &nbsp; with space

        // Remove empty tags and attributes
        $description = preg_replace('/<(\w+)[^>]*>\s*<\/\1>/', '', $description);

        // Clean up multiple line breaks
        $description = preg_replace('/(<br\s*\/?>\s*){2,}/', '<br><br>', $description);

        // Allow only specific tags
        $allowedTags = '<br><strong><b><em><i><u><a><span><font><ul><ol><li>';
        $description = strip_tags($description, $allowedTags);

        // Return empty if cleaned content has no meaningful data
        if (empty(trim(strip_tags($description, '<img>')))) {
            return '';
        }

        return trim($description);
    }
}
