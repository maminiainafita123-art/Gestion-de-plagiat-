<?php

namespace App\Helpers;

class CodeHighlighter
{
    /**
     * Surligner les positions correspondantes dans le code.
     * Retourne du HTML sûr avec des <span> de surlignage.
     */
    public static function highlight(string $content, array $positions, int $kgramSize = 10): string
    {
        if (empty($positions) || empty($content)) {
            return e($content);
        }

        // Fusionner les positions proches en segments
        sort($positions);
        $segments = [];
        $currentStart = $positions[0];
        $currentEnd = $positions[0] + $kgramSize;

        foreach ($positions as $pos) {
            if ($pos <= $currentEnd) {
                $currentEnd = max($currentEnd, $pos + $kgramSize);
            } else {
                $segments[] = [$currentStart, $currentEnd];
                $currentStart = $pos;
                $currentEnd = $pos + $kgramSize;
            }
        }
        $segments[] = [$currentStart, $currentEnd];

        // Construire le HTML en respectant les limites
        $result = '';
        $lastPos = 0;
        $contentLen = strlen($content);

        foreach ($segments as [$start, $end]) {
            $start = max(0, min($start, $contentLen));
            $end = max(0, min($end, $contentLen));

            if ($start > $lastPos) {
                $result .= e(substr($content, $lastPos, $start - $lastPos));
            }
            $result .= '<span class="highlight-match">';
            $result .= e(substr($content, $start, $end - $start));
            $result .= '</span>';
            $lastPos = $end;
        }

        if ($lastPos < $contentLen) {
            $result .= e(substr($content, $lastPos));
        }

        return $result;
    }
}