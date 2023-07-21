<?php

namespace PixlMint\CMS\Helpers;

class Stopwatch
{
    private float $startTime;
    private float $endTime;

    public static function startNew(): Stopwatch
    {
        $watch = new Stopwatch();
        $watch->start();
        return $watch;
    }

    public function start(): void
    {
        $this->startTime = microtime(true);
    }

    public function stop(): float
    {
        $this->endTime = microtime(true);

        return $this->endTime - $this->startTime;
    }
}