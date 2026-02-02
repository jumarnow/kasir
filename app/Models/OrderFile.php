<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderFile extends Model
{
    use HasFactory;

    const TYPE_DESIGN = 'design';
    const TYPE_PRINT_READY = 'print_ready';

    protected $fillable = [
        'transaction_id',
        'uploaded_by',
        'file_type',
        'file_path',
        'file_name',
        'original_name',
        'mime_type',
        'file_size',
        'notes',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Check if file is a design file
     */
    public function isDesign(): bool
    {
        return $this->file_type === self::TYPE_DESIGN;
    }

    /**
     * Check if file is print-ready
     */
    public function isPrintReady(): bool
    {
        return $this->file_type === self::TYPE_PRINT_READY;
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' bytes';
    }

    /**
     * Get file type label
     */
    public function getFileTypeLabelAttribute(): string
    {
        return match ($this->file_type) {
            self::TYPE_DESIGN => 'File Desain',
            self::TYPE_PRINT_READY => 'File Siap Cetak',
            default => $this->file_type,
        };
    }

    /**
     * Get file type options
     */
    public static function getFileTypeOptions(): array
    {
        return [
            self::TYPE_DESIGN => 'File Desain',
            self::TYPE_PRINT_READY => 'File Siap Cetak',
        ];
    }
}
