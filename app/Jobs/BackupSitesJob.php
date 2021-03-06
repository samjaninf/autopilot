<?php

namespace App\Jobs;

use App\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class BackupSitesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sites = Site::where('status', 'ready')->get();

        foreach ($sites as $sites) {
            if ($sites->server->backupConfigured()) {
                $sites->backups()->create();
            }
        }
    }
}
