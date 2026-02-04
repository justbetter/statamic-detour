<?php

namespace JustBetter\Detour\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use JustBetter\Detour\Contracts\ImportsDetours;

class ImportDetours implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(protected string $file)
    {
        $this->onQueue(config()->string('justbetter.statamic-detour.queue'));
    }

    public function handle(ImportsDetours $contract): void
    {
        $contract->import($this->file);
    }

    public function uniqueId(): string
    {
        return $this->file;
    }
}
