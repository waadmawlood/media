<?php

namespace Waad\Media\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Waad\Media\Media;
use Waad\Media\Services\MediaPrunableService;

class MediaPrune extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'media:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune Media model that are no longer needed';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = config('media.delete_file_after_day');

        if (!is_int($days)) {
            throw new Exception("media delete after day in config/media.php is not integer");
        }

        $media = new (Media::class);
        $model = DB::table($media->getTable());

        $dateSubDays = now()->subDays($days);
        $mediaPrune = new MediaPrunableService($model, $dateSubDays);
        $mediaPrune->all()->paths()->delete();

        $media->onlyTrashed()->where('deleted_at', '<', $dateSubDays)->forceDelete();

        $this->info('Media prune command has done...');

        return 1;
    }
}
