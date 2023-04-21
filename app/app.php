<?php

declare(strict_types=1);

use BzKarma\Scoring;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

// We always work with UTF8 encoding
mb_internal_encoding('UTF-8');

// Make sure we have a timezone set
date_default_timezone_set('UTC');


define('INSTALL_ROOT', dirname(__DIR__, 1) . '/');
// Application globals paths
const TEMPLATES = INSTALL_ROOT . 'app/templates/';

// Autoloading of classes (both /vendor/ and /app/classes/)
require_once INSTALL_ROOT . 'vendor/autoload.php';

// Initialize our Templating system
$twig_loader = new FilesystemLoader(TEMPLATES);
$twig = new Environment($twig_loader);
$card_title = 'Bugs';

$bugs = isset($_GET['bug_id']) && ! empty($_GET['bug_id']) && (int) $_GET['bug_id'] !== 0
    ? Utils::getBugsFromString($_GET['bug_id'])
    // 110.0.1 dot release uplifts below
    : [1814780, 1812120, 1805177, 1814696, 1814537, 1813991, 1816160, 1816001, 1816214, 1816191, 1815309, 1816943, 1813498, 1815843, 1763990, 1799684, 1817269];


if (isset($_GET['bug_id']) && ! empty($_GET['bug_id']) && (int) $_GET['bug_id'] !== 0) {
    $bugs = Utils::getBugsFromString($_GET['bug_id']);
} else {
    $bugs = Utils::getJson('https://bugzilla.mozilla.org/rest/bug?include_fields=id&f1=flagtypes.name&o1=substring&v1=approval-mozilla-beta%3F')['bugs'];

    if (empty($bugs)) {
        $bugs = [1814780, 1812120, 1805177, 1814696, 1814537, 1813991, 1816160, 1816001, 1816214, 1816191, 1815309, 1816943, 1813498, 1815843, 1763990, 1799684, 1817269];
        $card_title = '110.0.1 dot release uplifts below';
    } else {
        $bugs = array_column($bugs, 'id');
        $card_title = 'Bugs requested for Beta uplift';
    }
}



$bug_list_details = Utils::getBugDetails(
    $bugs,
    [
        'id', 'type', 'summary', 'priority', 'severity', 'keywords',
         'duplicates', 'regressions', 'cf_webcompat_priority', 'cc',
        'cf_tracking_firefox' . Train::NIGHTLY->value,
        'cf_tracking_firefox' . Train::BETA->value,
        'cf_tracking_firefox' . Train::RELEASE->value,
        'cf_status_firefox' . Train::NIGHTLY->value,
        'cf_status_firefox' . Train::BETA->value,
        'cf_status_firefox' . Train::RELEASE->value,
        'cf_performance_impact'
    ]
);

$bugs = new Scoring($bug_list_details, 111);

if (isset($_GET['scenario']) && ! empty($_GET['scenario'])) {
    switch ((int) $_GET['scenario']) {
        case 2:
            $bugs->karma['priority']['P1'] = 10;
            break;

        default:
            break;
    }
}

$details = [];
foreach ($bugs->getAllBugsScores() as $key => $value) {
    $details[$key] = $bugs->getBugScoreDetails($key);
}

$bugs_summary = [];
foreach ($bug_list_details as $key => $value) {
    $bugs_summary[$key] = $value['summary'];
}


$data = [
    'bugs_score'   => $bugs->getAllBugsScores(),
    'bugs_details' => $details,
    'total'        => array_sum($bugs->getAllBugsScores()),
    'scoring'      => $bugs->karma,
    'bugs_summary' => $bugs_summary,
    'title'        => $card_title,
];

print $twig->render('base.html.twig', $data);


