<?php

namespace App\Domain\AI;

interface PriorityAnalyzer{
    public function analyze(string $newMessage, array $generatedReports): mixed;
}
