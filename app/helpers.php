<?php

if (!function_exists('terbilang')) {
    /**
     * Convert a non-negative integer to Indonesian words.
     * e.g. 504167 → "lima ratus empat ribu seratus enam puluh tujuh"
     */
    function terbilang(int $n): string
    {
        if ($n < 0) {
            return 'minus ' . terbilang(abs($n));
        }

        $kata = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan',
                 'sepuluh', 'sebelas'];

        if ($n === 0)  return 'nol';
        if ($n < 12)  return $kata[$n];
        if ($n < 20)  return $kata[$n - 10] . ' belas';
        if ($n < 100) {
            $sisa = $n % 10;
            return $kata[(int)($n / 10)] . ' puluh' . ($sisa ? ' ' . $kata[$sisa] : '');
        }
        if ($n < 200)  return 'seratus'   . ($n % 100  ? ' ' . terbilang($n % 100)  : '');
        if ($n < 1000) return $kata[(int)($n / 100)] . ' ratus' . ($n % 100 ? ' ' . terbilang($n % 100) : '');
        if ($n < 2000) return 'seribu'    . ($n % 1000   ? ' ' . terbilang($n % 1000)   : '');
        if ($n < 1_000_000)     return terbilang((int)($n / 1_000))       . ' ribu'   . ($n % 1_000       ? ' ' . terbilang($n % 1_000)       : '');
        if ($n < 1_000_000_000) return terbilang((int)($n / 1_000_000))   . ' juta'   . ($n % 1_000_000   ? ' ' . terbilang($n % 1_000_000)   : '');
        return                         terbilang((int)($n / 1_000_000_000)) . ' miliar' . ($n % 1_000_000_000 ? ' ' . terbilang($n % 1_000_000_000) : '');
    }
}
