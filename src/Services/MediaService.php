<?php

namespace Waad\Media\Services;

use Waad\Media\Media;
use Waad\Media\DTO\FileDTO;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class MediaService
{
    private $model;
    private Collection|array|UploadedFile|Media|int|null $files;
    private $index;
    private string|null $label;
    private string|null $disk;
    private string|null $directory;
    private Collection $result;

    public function __construct($model)
    {
        $this->model = $model;
        $this->result = collect();

        if(property_exists($this->model, 'media_disk')){
            $this->disk = $this->model->media_disk;
        }

        if(property_exists($this->model, 'media_directory')){
            $this->directory = $this->model->media_directory;
        }
    }

    // check is list array or no
    protected function isList()
    {
        if (is_array($this->getFiles()) ||
            ($this->getFiles() instanceof \ArrayAccess && $this->getFiles() instanceof \Traversable) ||
            $this->getFiles() instanceof Collection
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     * @return self
     */
    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @param mixed $files
     * @return self
     */
    public function setFiles($files): self
    {
        $this->files = $files;
        return $this;
    }

    /**
     * @return Collection<UploadedFile>|array<UploadedFile>|array<int>|array<Media>|UploadedFile|Media|int|null
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param int $index
     * @return self
     */
    public function setIndex($index)
    {
        $this->index = $index;
        return $this;
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->index ?? 1;
    }

    /**
     * @return string|null
     */
    public function getLabel()
    {
        return $this->label ?? null;
    }

    /**
     * @param string|null $label
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDisk()
    {
        return $this->disk ?? config('media.disk');
    }

    /**
     * @param string|null $disk
     * @return self
     */
    public function setDisk(string|null $disk)
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDirectory()
    {
        return $this->directory ?? config('media.directory');
    }

    /**
     * @param string|null $directory
     * @return self
     */
    public function setDirectory(string|null $directory)
    {
        $this->directory = $directory;
        return $this;
    }

	/**
	 * @return Collection
	 */
	public function getResult()
    {
		return $this->result;
	}

	/**
	 * @param Media|bool $result
	 * @return self
	 */
	public function setResult(Media|bool $result)
    {
		$this->result->push($result);
		return $this;
	}

    /**
     * set Data from DTO to Array
     *
     * @param FileDTO $fileDto
     * @param bool $set_user
     * @return array
     */
    protected function setData(FileDTO $fileDto, bool $set_user = true)
    {
        $user = auth()->user();

        $data = [
            'base_name' => $fileDto->basename,
            'file_name' => $fileDto->filename,
            'path' => $fileDto->path,
            'index' => $fileDto->index,
            'label' => $fileDto->label,
            'disk' => $fileDto->disk,
            'directory' => $fileDto->directory,
            'mime_type' => $fileDto->mimeType,
            'file_size' => $fileDto->fileSize,
            'approved' => config('media.default_approved', true),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (filled($user) && $set_user) {
            $data['user_id'] = optional($user)->id;
            $data['user_type'] = get_class($user);
        } else {
            $data['user_id'] = null;
            $data['user_type'] = null;
        }

        return $data;
    }
}
