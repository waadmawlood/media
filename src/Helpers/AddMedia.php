<?php

namespace Waad\Media\Helpers;

class AddMedia extends FileMedia
{
    protected $baseNames = [];

    public function __construct($files, $model, $index = 1)
    {
        $this->file = $files;
        $this->model = $model;
        $this->index = $index;
        $this->uploading();
    }

    public function addFileToMedia()
    {
        $user = auth()->user();
        foreach ($this->result as $information) {
            $this->store($information, $user);
        }

        return $this->baseNames;
    }

    protected function store($information, $user = null)
    {
        $this->model->media()->create([
            'base_name' => $information['basename'],
            'file_name' => $information['filename'],
            'index' => $information['index'],
            'mime_type' => $information['mime'],
            'file_size' => $information['file_size'],
            'user_id' => is_null($user) ? null : $user->getKey(),
        ]);

        array_push($this->baseNames, $information['basename']);
    }
}
