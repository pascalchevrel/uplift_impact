<?php
declare(strict_types=1);

function getJson(string $url): array
{
    $data = file_get_contents($url);
    return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
}

$bugs = [
    1814780,
    1812120,
    1805177,
    1814696,
    1814537,
    1813991,
    1816160,
    1816001,
    1816214,
    1816191,
    1815309,
    1816943,
    1813498,
    1815843,
    1763990,
    1799684,
    1817269,
];


if (isset($_GET['bug_id']) && ! empty($_GET['bug_id'])) {
    // Convert the list of comma-separated bug numbers to an array
    $bugs = explode(',', $_GET['bug_id']);

    // Turn all strings as integers for security, invalid numbers are cast to 0
    $bugs = array_map('intval', $bugs);

    // Filter out all the 0 values and potential negative ones to keep only valid bug numbers
    $bugs = array_filter($bugs, 'ctype_digit');

    // Remove duplicate values
    $bugs = array_unique($bugs);

    // Reorder array keys now that we have removed items
    $bugs = array_values($bugs);
} else {
    echo '<h4 style="font-weight:normal">Append <code>?bug_id=1817192,1811873,1816574,1812680,1814961,1794577,1788004,1817518,1812447</code> to the url to test your bugs (comma separated bug numbers)</h4>';
}

$bug_list_details = getJson('https://bugzilla.mozilla.org/rest/bug?include_fields=id,summary,priority,severity,keywords,duplicates,cf_tracking_firefox_release,cf_tracking_firefox_beta,cf_tracking_firefox_nightly,cf_tracking_firefox_nightly&bug_id=' . implode('%2C', $bugs))['bugs'];

$bug_summaries = array_column($bug_list_details, 'summary', 'id');
/*
echo '<pre>';
var_dump($bug_list_details);
var_dump($bug_summaries);
echo '</pre>';*/

$priority = [
    'P1' => 4,
    'P2' => 3,
    'P3' => 2,
    'P4' => 1,
    '--' => 0,
];

$severity = [
    'S1' => 8,
    'S2' => 4,
    'S3' => 2,
    'S4' => 1,
    '--' => 0,
];

$keywords = [
    'topcrash'   => 5,
    'dataloss'   => 3,
    'crash'      => 1,
    'regression' => 1,
    'perf'       => 1,
];

$karma = [
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

$bugs = [];
foreach ($bug_list_details as $bug) {
    $id = $bug['id'];

    $bugs[$id] = 0;
    $bugs[$id] += $karma['severity'][$bug['severity']];

    foreach ($bug['keywords'] as $keyword) {
        if (array_key_exists($keyword, $karma['keywords'])) {
            $bugs[$id] += $karma['keywords'][$keyword];
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


echo '<ul>';
foreach ($bugs as $key => $value) {
    echo '<li>';
    echo 'Bug <a href="https://bugzilla.mozilla.org/' . $key . '" title="' . $bug_summaries[(int) $key]. '">' . $key . '</a> : ' . $value;
    echo '</li>';
    // code...
}
echo '</ul>';

echo 'Total: ' . array_sum($bugs);

// TODO
// negative values (riskyness, open regressions)
// add votes
//
?>
<h3> Current scores:</h3>
<pre>
$priority = [
    'P1' => 4,
    'P2' => 3,
    'P3' => 2,
    'P4' => 1,
    '--' => 0,
];

$severity = [
    'S1' => 8,
    'S2' => 4,
    'S3' => 2,
    'S4' => 1,
    '--' => 0,
];

$keywords = [
    'topcrash'   => 5,
    'dataloss'   => 3,
    'crash'      => 1,
    'regression' => 1,
    'perf'       => 1,
];

$karma = [
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

