<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Галерея номеров
 *
 * @property int id
 * @property int room_id - ID номера
 * @property string filename - Название изображения
 * @property Carbon|null created_at - Дата создания
 * @property Carbon|null updated_at - Дата обновления
 *
 * @property-read Room|null product - Номер
 *
 */
class RoomImage extends Model
{
    public const TABLE = 'room_images';

    /** @var string */
    protected $table = self::TABLE;
    protected $guarded = false;

    protected $fillable = [
        'room_id',
        'filename',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'room_id' => 'integer',
        'filename' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @return string
     */
    public function getImageUrlAttribute(): string
    {
        return $this->filename ? url('storage/' . $this->filename) : '';
    }

    /**
     * @return belongsTo
     */
    public function room(): belongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }
}

