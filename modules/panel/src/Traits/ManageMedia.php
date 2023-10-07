<?php

namespace Fpaipl\Panel\Traits;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Activitylog\Contracts\Activity;

trait ManageMedia
{
    // public function isSingleFileCollection()
    // {
    //     return $this->getFirstMedia('primary');
    // }

    public function removeMedia($data)
    {
        if (is_array($data)) {
            foreach ($data as $media) {
                $this->deleteImage($media);
            }
        } else {
            if (!empty($data)) {
                $this->deleteImage($data);
            }
        }
    }

    private function deleteImage($media)
    {
        $media = Media::findOrFail($media);
        return $media->delete();
    }

    public function addSingleMediaToModal($media)
    {
        $mediaModel = $this
            ->addMedia($media)
            ->preservingOriginal()
            ->toMediaCollection($this->getMediaCollectionName('primary'));

        if ($mediaModel) {
            $this->MediaLog($mediaModel);
        }
    }

    public function addMultipleMediaToModel($media)
    {
        foreach ($media as $image) {

            $mediaModel = $this
                ->addMedia($image)
                ->preservingOriginal()
                ->toMediaCollection($this->getMediaCollectionName('secondary'));

            if ($mediaModel) {
                $this->MediaLog($mediaModel);
            }
        }
    }

    public function addSingleMediaToModalFromUrl($media)
    {
        $mediaModel = $this
            ->addMediaFromUrl($media)
            ->preservingOriginal()
            ->toMediaCollection($this->getMediaCollectionName('primary'));

        if ($mediaModel) {
            $this->MediaLog($mediaModel);
        }
    }

    public function addMultipleMediaToModelFromUrl($media)
    {
        $mediaModel = $this
            ->addMediaFromUrl($media)
            ->preservingOriginal()
            ->toMediaCollection($this->getMediaCollectionName('secondary'));

        if ($mediaModel) {
            $this->MediaLog($mediaModel);
        }
    }

    private function MediaLog($mediaModel)
    {
        activity()
            ->performedOn($mediaModel)
            ->event('upload')
            ->withProperties(json_encode($mediaModel))
            ->tap(function (Activity $activity) {
                $activity->log_name = 'media_log';
            })
            ->log(get_class($this) . '_' . $this->id);
    }
}