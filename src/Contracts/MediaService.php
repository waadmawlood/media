<?php

namespace Waad\Media\Contracts;
use Illuminate\Support\Collection;
use Waad\Media\Media;

interface MediaService
{
    public function upload(string $collection, string $disk): Media|Collection|null;
    public function sync(string $collection, string $disk): Media|Collection|null;
    public function collection(string $collection): static;
    public function disk(string $disk): static;
    public function bucket(string $bucket): static;
    public function label(string $label): static;
    public function index(int $index): static;
}
