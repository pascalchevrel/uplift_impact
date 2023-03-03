<?php

declare(strict_types=1);

use BzKarma\{Scoring, Utils, Train};

include __DIR__ . '/classes/BzKarma/Scoring.php';
include __DIR__ . '/classes/BzKarma/Utils.php';

$bugs = isset($_GET['bug_id']) && ! empty($_GET['bug_id'])
    ? Utils::getBugsFromString($_GET['bug_id'])
    // 110.0.1 dot release uplifts below
    : [1814780, 1812120, 1805177, 1814696, 1814537, 1813991, 1816160, 1816001, 1816214, 1816191, 1815309, 1816943, 1813498, 1815843, 1763990, 1799684, 1817269];

$bug_list_details = Utils::getBugDetails(
    $bugs,
    [
        'id', 'summary', 'priority', 'severity', 'keywords', 'duplicates',
        'cf_tracking_firefox' . Train::NIGHTLY->value,
        'cf_tracking_firefox' . Train::BETA->value,
        'cf_tracking_firefox' . Train::RELEASE->value,
    ]
);

$bugs = new Scoring($bug_list_details);

if (isset($_GET['scenario']) && ! empty($_GET['scenario'])) {
    switch ((int) $_GET['scenario']) {
        case 2:
            $bugs->karma['priority']['P1'] = 10;
            break;

        default:
            break;
    }
}
/*

echo '<pre>';
var_dump($bug_list_details);
echo '</pre>';

*/
echo '
<h4 style="font-weight:normal">
    Append
    <code>
        ?bug_id=1817192,1811873,1816574,1812680,1814961,1794577,1788004,1817518,1812447
    </code>
     to the url to test your bugs (comma separated bug numbers)
</h4>';

echo '<ul>';
foreach ($bugs->getAllBugsScores() as $key => $value) {
    echo '<li>';
    echo 'Bug <a href="https://bugzilla.mozilla.org/' . $key . '" title="' . $bug_list_details[(int) $key]['summary']. '">' . $key . '</a> : ' . $value;
        echo '<ul>';
            echo '<li>';
            echo '<pre>';
            print_r($bugs->getBugScoreDetails($key));
            echo '</pre>';
            echo '</li>';
        echo '</ul>';
    echo '</li>';
}
echo '</ul>';

echo 'Total: ' . array_sum($bugs->getAllBugsScores());



// TODO
// negative values (riskyness, open regressions)
// add votes
//
?>

<h3> Current scores:</h3>
<pre><?php print_r($bugs->karma); ?></pre>


