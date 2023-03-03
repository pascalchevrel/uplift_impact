<?php

declare(strict_types=1);

namespace BzKarma;

class Utils
{

    /**
     * Transform a string of bugs into a list of valid integers
     *
     *  @return array<int> $bugs
     */
    public static function getBugsFromString(string $commaSeparatedList, string $separator = ','): array
    {
        // Convert the list of comma-separated bug numbers to an array
        $bugs = explode($separator, $commaSeparatedList);

        // Filter out all strings that can't be turned into valid bug numbers
        $bugs = array_filter($bugs, 'ctype_digit');

        // Turn all strings as integers for security
        $bugs = array_map('intval', $bugs);

        // Remove duplicates
        $bugs = array_unique($bugs);

        // Reorder array keys now that we have removed items
        $bugs = array_values($bugs);

        return $bugs;
    }

    public static function getJson(string $url): mixed
    {
        $data = file_get_contents($url);
        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }


    public static function getBugDetails(array $bugList, array $bugFields): array
    {
        $data = self::getJson(
            'https://bugzilla.mozilla.org/rest/bug?include_fields='
            . implode(',', $bugFields)
            . '&bug_id=' . implode('%2C', $bugList)
        )['bugs'];

        // Replace numeric keys by the real bug number
        $data = array_combine(array_column($data, 'id'), $data);

        return $data;
    }


    /**
     * Utility function to output Json data
     * @param array<mixed> $data
     */
    public static function renderJson(array $data): void
    {
        // Output a JSON or JSONP representation of search results
        $json = new Json();

        if (array_key_exists('error', $data)) {
            print_r($json->outputError($data['error']));
        } else {
            print_r($json->outputContent($data, $_GET['callback'] ?? false));
        }
    }
}