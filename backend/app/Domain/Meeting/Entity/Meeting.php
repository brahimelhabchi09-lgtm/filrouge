<?php

namespace App\Domain\Meeting\Entity;

use DateTimeInterface;

class Meeting
{
    private ?int $id;
    private string $title;
    private string $description;
    private DateTimeInterface $date;
    private string $link;
    private ?string $pdfPath;
    private int $adminId;
    private int $requestMeetingId;
    private DateTimeInterface $createdAt;
    private DateTimeInterface $updatedAt;

    public function __construct(
        string $title,
        string $description,
        DateTimeInterface $date,
        string $link,
        int $adminId,
        int $requestMeetingId,
        ?int $id = null,
        ?string $pdfPath = null,
        ?DateTimeInterface $createdAt = null,
        ?DateTimeInterface $updatedAt = null
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->date = $date;
        $this->link = $link;
        $this->pdfPath = $pdfPath;
        $this->adminId = $adminId;
        $this->requestMeetingId = $requestMeetingId;
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->updatedAt = $updatedAt ?? new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDate(): DateTimeInterface
    {
        return $this->date;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getPdfPath(): ?string
    {
        return $this->pdfPath;
    }

    public function getAdminId(): int
    {
        return $this->adminId;
    }

    public function getRequestMeetingId(): int
    {
        return $this->requestMeetingId;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }
}
