<?php

namespace App\Helpers\array;

function nullValuesToEmptyString(array $input): array
{
    $output = [];

    foreach ($input as $k => $v) {
        if ($v === null) {
            $v = "";
        } elseif (is_array($v)) {
            $v = nullValuesToEmptyString($v);
        }

        $output[$k] = $v;
    }

    return $output;
}
