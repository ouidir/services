<?php

namespace App\Entity;

class Execution
{
    private int $status = 0;
    private mixed $maintenance = null;
    private ?\DateTime $date = null;

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getMaintenance(): mixed
    {
        return $this->maintenance;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }
}
