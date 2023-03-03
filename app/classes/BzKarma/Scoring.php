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

    public function getAllScores(): array
    {
        $bugs = [];

        foreach ($this->bugDetails as $bug) {
            $id = $bug['id'];

            $bugs[$id] = 0;
            $bugs[$id] += $this->karma['severity'][$bug['severity']];

            foreach ($bug['keywords'] as $keyword) {
                if (array_key_exists($keyword, $this->karma['keywords'])) {
                    $bugs[$id] += $this->karma['keywords'][$keyword];
                }
            }

            if (isset($bug['cf_tracking_firefox110']) && $bug['cf_tracking_firefox110'] === '+') {
                $bugs[$id] += 2;
            }

            if (isset($bug['cf_tracking_firefox110']) && $bug['cf_tracking_firefox110'] === 'blocking') {
                $bugs[$id] += 100;
            }

            $bugs[$id] += count($bug['duplicates']) * 2;

        }

        arsort($bugs);

        return $bugs;
    }

    public function getScoreDetails(array $bugDetails): int
    {
        $value = 0;
        $value += $this->karma['severity'][$bugDetails['severity']];

        foreach ($bugDetails['keywords'] as $keyword) {
            if (array_key_exists($keyword, $this->karma['keywords'])) {
                $value += $this->karma['keywords'][$keyword];
            }
        }

        // if (isset($bug['cf_tracking_firefox110']) && $bug['cf_tracking_firefox110'] === '+') {
        //     $bugs[$id] += 2;
        // }

        // if (isset($bug['cf_tracking_firefox110']) && $bug['cf_tracking_firefox110'] === 'blocking') {
        //     $bugs[$id] += 100;
        // }

        $value += count($bugDetails['duplicates']) * 2;

        return $value;
    }
    public function getScore(int $bugNumber): int
    {

    }
}



