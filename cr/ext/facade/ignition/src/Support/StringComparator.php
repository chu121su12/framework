<?php

namespace Facade\Ignition\Support;

use Illuminate\Support\Collection;

class StringComparator
{
    public static function findClosestMatch(array $strings, /*string */$input, /*int */$sensitivity = 4)/*: ?string*/
    {
        $input = cast_to_string($input);
        $sensitivity = cast_to_int($sensitivity);

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

    public static function findSimilarText(array $strings, /*string */$input)/*: ?string*/
    {
        $input = cast_to_string($input);

        if (empty($strings)) {
            return null;
        }

        return Collection::make($strings)
            ->sortByDesc(function (/*string */$string) use ($input) {
                $string = cast_to_string($string);
                similar_text($input, $string, $percentage);

                return $percentage;
            })
            ->first();
    }
}
