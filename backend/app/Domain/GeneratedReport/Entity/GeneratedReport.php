<?php

namespace App\Domain\Report\Entity;

use App\Domain\Report\ValueObject\Priority;
use App\Domain\Report\ValueObject\Status;

class GeneratedReport {
    private ?int $id;
    private string $message;
    private Priority $priority;
    private Status $status;
    private int $reportsCount;

    public function __construct(
        string $message,
        Priority $priority,
        Status $status,
        int $reportsCount,
        ?int $id = null
    ) {
        $this->id = $id ?? null;
        $this->message = $message;
        $this->priority = $priority;
        $this->status = $status;
        $this->reportsCount = $reportsCount;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getPriority(): Priority
    {
        return $this->priority;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function getReportsCount(): int
    {
        return $this->reportsCount;
    }
}
