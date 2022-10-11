<?php

namespace Spatie\LaravelIgnition\Support;

use Illuminate\Support\Collection;

class StringComparator
{
    /**
     * @param array<int|string, string> $strings
     * @param string $input
     * @param int $sensitivity
     *
     * @return string|null
     */
    public static function findClosestMatch(array $strings, /*string */$input, /*int */$sensitivity = 4)/*: ?string*/
    {
        $sensitivity = backport_type_check('int', $sensitivity);

        $input = backport_type_check('string', $input);

        $closestDistance = -1;

        $closestMatch = null;

        foreach ($strings as $string) {
            $levenshteinDistance = levenshtein($input, $string);

            if ($levenshteinDistance === 0) {
                $closestMatch = $string;
                $closestDistance = 0;

                break;
            }

            if ($levenshteinDistance <= $closestDistance || $closestDistance < 0) {
                $closestMatch = $string;
                $closestDistance = $levenshteinDistance;
            }
        }

        if ($closestDistance <= $sensitivity) {
            return $closestMatch;
        }

        return null;
    }

    /**
     * @param array<int, string> $strings
     * @param string $input
     *
     * @return string|null
     */
    public static function findSimilarText(array $strings, /*string */$input)/*: ?string*/
    {
        $input = backport_type_check('string', $input);

        if (empty($strings)) {
            return null;
        }

        return Collection::make($strings)
            ->sortByDesc(function (/*string */$string) use ($input) {
                $string = backport_type_check('string', $string);

                similar_text($input, $string, $percentage);

                return $percentage;
            })
            ->first();
    }
}
