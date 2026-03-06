<?php

namespace App\Model;

class IntervalHorraire
{
    private \DateInterval $interval;

    public function __construct(string $duration)
    {
        $this->interval = new \DateInterval($duration);
    }

    public function toDateInterval(): \DateInterval
    {
        return $this->interval;
    }

    public function toSeconds(): int
    {
        return ($this->interval->d * 86400)
            + ($this->interval->h * 3600)
            + ($this->interval->i * 60)
            + $this->interval->s;
    }

    public static function createFromDateInterval(\DateInterval $interval): self
    {
        $totalSeconds = abs(
            ($interval->d * 86400)
            + ($interval->h * 3600)
            + ($interval->i * 60)
            + $interval->s
        );

        return new self('PT' . $totalSeconds . 'S');
    }
}
