<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $message_id
 * @property string $file_name
 * @property string $file_path
 * @property string $file_type
 * @property string $mime_type
 * @property int $file_size
 * @property string|null $thumbnail_path
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class MessageAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'file_size',
        'thumbnail_path',
    ];

    protected $appends = ['file_url', 'thumbnail_url', 'formatted_size', 'is_image'];

    // Relationships
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        if (str_starts_with($this->file_path, 'http')) {
            return $this->file_path;
        }
        return url('api/storage/' . $this->file_path);
    }

    public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail_path) {
            return null;
        }
        if (str_starts_with($this->thumbnail_path, 'http')) {
            return $this->thumbnail_path;
        }
        return url('api/storage/' . $this->thumbnail_path);
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function getIsImageAttribute()
    {
        return in_array($this->file_type, ['image', 'photo']) || 
               str_starts_with($this->mime_type, 'image/');
    }

    // Scopes
    public function scopeImages($query)
    {
        return $query->where(function ($q) {
            $q->where('file_type', 'image')
              ->orWhere('mime_type', 'like', 'image/%');
        });
    }

    public function scopeDocuments($query)
    {
        return $query->where('file_type', 'document');
    }
}



