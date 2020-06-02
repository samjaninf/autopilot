<?php

namespace App\Jobs;

use Exception;
use App\Sysuser;
use Illuminate\Bus\Queueable;
use App\Playbooks\UserTestPlaybook;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UserTestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The sysuser instance.
     *
     * @var Sysuser
     */
    public $sysuser;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 360;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Sysuser $sysuser)
    {
        $this->sysuser = $sysuser;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->sysuser->isTesting()) {
            return $this->delete();
        }

        if ($this->sysuser->server->isReady() && !$this->sysuser->isBusy()) {
            $this->sysuser->markAsTesting();

            $task = $this->sysuser->run(
                new UserTestPlaybook($this->sysuser)
            );

            if ($task->successful()) {
                $this->sysuser->markAsReady();
                return $this->delete();
            }

            $this->sysuser->markAsError();
        }

        $this->release(30);
    }

    /**
     * Handle a job failure.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed($exception)
    {
        $this->sysuser->markAsError();
    }
}
