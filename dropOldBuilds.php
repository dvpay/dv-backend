<?php

declare(strict_types=1);

function drop(?string $dateEnd = null): void
{
    try {
        if (!$dateEnd) {
            $timestamp = time() - 86400;
        } else {
            $dt = new DateTime($dateEnd);
            $timestamp = $dt->format('U');
        }

        $path = '/home/server/backend/builds';
        $builds = scandir($path);

        foreach ($builds as $build) {
            if ($build == '.' || $build == '..') {
                continue;
            }

            if (filectime("$path/$build") < $timestamp) {
                system("rm -rf $path/$build");
                echo "$build << drop!\n";
            }
        }

        echo ">>> DONE <<< \n";
    } catch (Throwable $e) {
        echo $e->getMessage();
    }
}

drop($argv[1] ?? null);
