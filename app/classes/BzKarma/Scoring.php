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
    /*
        This array contains our uplift value business logic.
     */
    public array $karma = [
        'priority' => [
            'P1' => 5,
            'P2' => 4,
            'P3' => 3,
            'P4' => 2,
            'P5' => 1,
            '--' => 0,
        ],
        'severity' => [
            'S1'  => 8,
            'S2'  => 4,
            'S3'  => 2,
            'S4'  => 1,
            'N/A' => 0,
            '--'  => 0,
        ],
        'keywords' => [
            'topcrash-startup' => 10,
            'topcrash'         => 5,
            'dataloss'         => 3,
            'crash'            => 1,
            'regression'       => 1,
            'perf'             => 1,
        ],
        'duplicates'  =>  2, // Points for each duplicate
        'regressions' => -2, // Negative Points for regressions
        'tracking_firefox_nightly' => [
            'blocking' => 100,
            '+'        => 4,
            '?'        => 2,
            '-'        => 0,
            '---'      => 0,
        ],
        'tracking_firefox_beta' => [
            'blocking' => 100,
            '+'        => 4,
            '?'        => 2,
            '-'        => 0,
            '---'      => 0,
        ],
        'tracking_firefox_release' => [
            'blocking' => 100,
            '+'        => 4,
            '?'        => 2,
            '-'        => 0,
            '---'      => 0,
        ],
        'webcompat' => [
            'P1' => 5,
            'P2' => 4,
            'P3' => 3,
            '---' => 0,
        ],
    ];

    /*
        This array stores the bug data provided by the Bugzilla rest API
        The list of fields retrieved are:

        id, summary, priority, severity, keywords, duplicates, regressions, cf_webcompat_priority,
        cf_tracking_firefox_nightly, cf_tracking_firefox_beta, cf_tracking_firefox_release

        The fields actually retrieved for tracking requests have release numbers, ex:
        cf_tracking_firefox112, cf_tracking_firefox111, cf_tracking_firefox110

        See Bug 1819638 - JSON API should support release aliases (_nightly / _beta / _release) for the cf_tracking_firefoxXXX and cf_status_firefoxXXX fields - https://bugzil.la/1819638
     */
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

    /*
        This is the method that contains the business logic.
     */
    public function getBugScoreDetails(int $bug): array
    {
        $keywords_value = 0;

        foreach ($this->bugsData[$bug]['keywords'] as $keyword) {
            if (array_key_exists($keyword, $this->karma['keywords'])) {
                $keywords_value += $this->karma['keywords'][$keyword];
            }
        }

        /*
            The cf_webcompat_priority field is not available for all components so we need
            to check for its availability and we set it to a 0 karma if it doesn't exist.
         */
        $webcompat = isset($this->bugsData[$bug]['cf_webcompat_priority'])
            ? $this->karma['webcompat'][$this->bugsData[$bug]['cf_webcompat_priority']]
            : 0;

        $impact = [
            /*
                Severity and Priority fields had other values in the past like normal, trivial…
                We ignore these values for now.
             */
            'priority'    => $this->karma['priority'][$this->bugsData[$bug]['priority']] ?? 0,
            'severity'    => $this->karma['severity'][$this->bugsData[$bug]['severity']] ?? 0,
            'keywords'    => $keywords_value,
            'duplicates'  => count($this->bugsData[$bug]['duplicates']) * $this->karma['duplicates'],
            'regressions' => count($this->bugsData[$bug]['regressions']) * $this->karma['regressions'],
            'webcompat'   => $webcompat,
            /*
                If a bug is tracked across all our releases, it is likely higher value
             */
            'tracking_firefox'. Train::NIGHTLY->value =>
                $this->karma['tracking_firefox_nightly'][$this->bugsData[$bug]['cf_tracking_firefox'. Train::NIGHTLY->value]] ?? 0,
            'tracking_firefox'. Train::BETA->value =>
                $this->karma['tracking_firefox_beta'][$this->bugsData[$bug]['cf_tracking_firefox'. Train::BETA->value]] ?? 0,
            'tracking_firefox'. Train::RELEASE->value =>
                $this->karma['tracking_firefox_release'][$this->bugsData[$bug]['cf_tracking_firefox'. Train::RELEASE->value]] ?? 0,
        ];

        return $impact;
    }

    public function getBugScore(int $bug): int {
        return array_sum($this->getBugScoreDetails($bug));
    }
}



