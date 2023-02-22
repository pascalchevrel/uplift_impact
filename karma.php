<?php
declare(strict_types=1);

function getJson(string $url): array
{
    $data = file_get_contents($url);
    return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
}

$bugs = [
    '1814780',
    '1812120',
    '1805177',
    '1814696',
    '1814537',
    '1813991',
    '1816160',
    '1816001',
    '1816214',
    '1816191',
    '1815309',
    '1816943',
    '1813498',
    '1815843',
    '1763990',
    '1799684',
    '1817269',
];

$bug_list_details = getJson('https://bugzilla.mozilla.org/rest/bug?include_fields=id,summary,priority,severity,keywords,cf_tracking_firefox110&bug_id=' . implode('%2C', $bugs))['bugs'];

$bug_summaries = array_column($bug_list_details, 'summary', 'id');

// header('Content-Type: text/plain; charset=utf-8');
/*echo '<pre>';
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
    'topcrash'      => 5,
    'dataloss'      => 3,
    'crash'         => 1,
    'regression'    => 1,
    'perf'          => 1,
];

$bugs_value = [];

foreach ($bug_list_details as $value) {
    $bugs_value[$value['id']] = 0;
    $bugs_value[$value['id']] = $bugs_value[$value['id']] + $severity[$value['severity']];

    foreach ($value['keywords'] as $keyword) {
        if (array_key_exists($keyword, $keywords)) {
            $bugs_value[$value['id']] = $bugs_value[$value['id']] + $keywords[$keyword];
        }
    }

    if ($value['cf_tracking_firefox110'] === '+') {
        $bugs_value[$value['id']] = $bugs_value[$value['id']] + 2;
    }

    if ($value['cf_tracking_firefox110'] === 'blocking') {
        $bugs_value[$value['id']] = $bugs_value[$value['id']] + 100;
    }
}

echo '<ul>';
foreach ($bugs_value as $key => $value) {
    echo '<li>';
    echo 'Bug <a href="https://bugzilla.mozilla.org/' . $key . '" title="' . $bug_summaries[(int) $key]. '">' . $key . '</a> : ' . $value;
    echo '</li>';
    // code...
}
echo '</ul>';

echo 'Total: ' . array_sum($bugs_value);