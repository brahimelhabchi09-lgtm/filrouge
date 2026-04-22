<?php

namespace App\Domain\RejectTeacherReason\Entity;

use DateTime;

class RejectTeacherReason
{
    private ?int $id;
    private string $message;
    private int $teacherId;
    private int $generatedReportId;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        string $message,
        int $teacherId,
        int $generatedReportId,
        ?int $id = null,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->message = $message;
        $this->teacherId = $teacherId;
        $this->generatedReportId = $generatedReportId;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
        $this->updatedAt = new DateTime();
    }

    public function getTeacherId(): int
    {
        return $this->teacherId;
    }

    public function getGeneratedReportId(): int
    {
        return $this->generatedReportId;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }
}