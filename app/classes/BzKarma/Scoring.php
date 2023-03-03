<?php

declare(strict_types=1);

namespace BzKarma;

enum Train: string {
    case NIGHTLY = '112';
    case BETA    = '111';
    case RELEASE = '110';
}

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
        'tracking_firefox_nightly' => [
            'blocking' => 50,
            '+' => 8,
            '?' => 2,
            '-' => 0,
            '---' => 0,
        ],
        'tracking_firefox_beta' => [
            'blocking' => 50,
            '+' => 8,
            '?' => 2,
            '-' => 0,
            '---' => 0,
        ],
        'tracking_firefox_release' => [
            'blocking' => 50,
            '+' => 8,
            '?' => 2,
            '-' => 0,
            '---' => 0,
        ],
    ];

    public array $bugsData;

    public function __construct(array $bugsData)
    {
        $this->bugsData = $bugsData;
    }

    public function getAllBugsScores(): array
    {
        $bugs = [];

        foreach ($this->bugsData as $bug => $details) {
           $bugs[$bug] = $this->getBugScore($bug);
        }

        arsort($bugs);

        return $bugs;
    }

    public function getBugScoreDetails(int $bug): array
    {
        $keywords_value = 0;

        foreach ($this->bugsData[$bug]['keywords'] as $keyword) {
            if (array_key_exists($keyword, $this->karma['keywords'])) {
                $keywords_value += $this->karma['keywords'][$keyword];
            }
        }

        $impact = [
            'priority'   => $this->karma['priority'][$this->bugsData[$bug]['priority']],
            'severity'   => $this->karma['severity'][$this->bugsData[$bug]['severity']],
            'keywords'   => $keywords_value,
            'duplicates' => count($this->bugsData[$bug]['duplicates']) * $this->karma['duplicates'],
            'tracking_firefox'. Train::NIGHTLY->value =>
                $this->karma['tracking_firefox_nightly'][$this->bugsData[$bug]['cf_tracking_firefox'. Train::NIGHTLY->value]],
            'tracking_firefox'. Train::BETA->value =>
                $this->karma['tracking_firefox_beta'][$this->bugsData[$bug]['cf_tracking_firefox'. Train::BETA->value]],
            'tracking_firefox'. Train::RELEASE->value =>
                $this->karma['tracking_firefox_release'][$this->bugsData[$bug]['cf_tracking_firefox'. Train::RELEASE->value]],
        ];

        return $impact;
    }


    public function getBugScore(int $bug): int {
        return array_sum($this->getBugScoreDetails($bug));
    }
}



