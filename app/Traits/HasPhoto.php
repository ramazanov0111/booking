<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Jetstream\Features;

trait HasPhoto
{
    /**
     * Update the user's profile photo.
     *
     * @param UploadedFile $photo
     * @param  string  $storagePath
     * @return void
     */
    public function updateRoomPhoto(UploadedFile $photo, $storagePath = 'room-photos')
    {
        tap($this->room_image, function ($previous) use ($photo, $storagePath) {
            $this->forceFill([
                'room_image' => $photo->storePublicly(
                    $storagePath, ['disk' => $this->roomPhotoDisk()]
                ),
            ])->save();

            if ($previous) {
                Storage::disk($this->roomPhotoDisk())->delete($previous);
            }
        });
    }

    /**
     * Delete the user's profile photo.
     *
     * @return void
     */
    public function deleteRoomPhoto()
    {
        if (is_null($this->room_image)) {
            return;
        }

        Storage::disk($this->roomPhotoDisk())->delete($this->room_image);

        $this->forceFill([
            'room_image' => null,
        ])->save();
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function roomPhotoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            return $this->room_image
                ? Storage::disk($this->roomPhotoDisk())->url($this->room_image)
                : $this->defaultRoomPhotoUrl();
        });
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultRoomPhotoUrl()
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return mb_substr($segment, 0, 1);
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the disk that profile photos should be stored on.
     *
     * @return string
     */
    protected function roomPhotoDisk()
    {
        return isset($_ENV['VAPOR_ARTIFACT_NAME']) ? 's3' : config('jetstream.room_photo_disk', 'public');
    }
}
