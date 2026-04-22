<?php

namespace App\Domain\Report\Entity;

class Report {
    private ?int $id;
    private string $title;
    private string $description;
    private int $studentId;
    private int $categoryId;
    private ?int $generatedReportId;

    public function __construct(
        string $title,
        string $description,
        int $studentId,
        int $categoryId,
        ?int $generatedReportId = null,
        ?int $id = null
    ) {
        $this->id = $id ?? null;
        $this->title = $title;
        $this->description = $description;
        $this->studentId = $studentId;
        $this->categoryId = $categoryId;
        $this->generatedReportId = $generatedReportId ?? null;
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

    public function getStudentId(): int
    {
        return $this->studentId;
    }

    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    public function getGeneratedReportId(): ?int
    {
        return $this->generatedReportId;
    }
}
