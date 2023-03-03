<?php

declare(strict_types=1);

namespace BzKarma;

class Scoring
{
    public array $karma = [
        'priority' => [
            'P1' => 4,
            'P2' => 3,
            'P3' => 2,
            'P4' => 1,
            '--' => 0,
        ],
        'severity' => [
            'S1' => 8,
            'S2' => 4,
            'S3' => 2,
            'S4' => 1,
            '--' => 0,
        ],
        'keywords' => [
            'topcrash'   => 5,
            'dataloss'   => 3,
            'crash'      => 1,
            'regression' => 1,
            'perf'       => 1,
        ],
        'duplicates' => 2, // Points for each duplicate
    ];

    public array $bugDetails;

    public function __construct(array $bugDetails)
    {
        $this->bugDetails = $bugDetails;
    }

    public function getAllBugsScores(): array
    {
        $bugs = [];

        foreach ($this->bugDetails as $bug => $details) {
           $bugs[$bug] = $this->getBugScore($bug);
        }

        arsort($bugs);

        return $bugs;
    }

    public function getBugScoreDetails(int $bug): array
    {
        $keywords_value = 0;

        foreach ($this->bugDetails[$bug]['keywords'] as $keyword) {
            if (array_key_exists($keyword, $this->karma['keywords'])) {
                $keywords_value += $this->karma['keywords'][$keyword];
            }
        }

        $impact = [
            'priority'   => $this->karma['priority'][$this->bugDetails[$bug]['priority']],
            'severity'   => $this->karma['severity'][$this->bugDetails[$bug]['severity']],
            'keywords'   => $keywords_value,
            'duplicates' => count($this->bugDetails[$bug]['duplicates']) * $this->karma['duplicates'],
        ];

        return $impact;
    }


    public function getBugScore(int $bug): int {
        return array_sum($this->getBugScoreDetails($bug));
    }
}



