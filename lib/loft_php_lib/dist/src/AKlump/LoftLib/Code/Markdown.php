<?php


namespace AKlump\LoftLib\Code;


class Markdown {

    public static function table($rows, $keys = null)
    {
        $build = array();
        $build[] = empty($keys) ? ($keys = array_keys(array_values($rows)[0])) : $keys;
        $build[] = null;
        $build = array_merge($build, $rows);

        return array_reduce($build, function ($carry, $row) use ($keys) {
            if (is_null($row)) {
                $line = '|' . str_repeat('---|', count($keys));
            }
            else {
                $row = array_map(function ($item) {
                    return is_scalar($item) ? $item : json_encode($item);
                }, $row);
                $line = '| ' . implode(' | ', str_replace('|', '\|', $row)) . ' |';
            }

            return $carry . $line . PHP_EOL;
        });
    }
}
