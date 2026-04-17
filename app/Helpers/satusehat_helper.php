<?php
function convertTimeSatset($waktu)
{
    if (empty($waktu)) return null;

    try {
        $DateTime = new \DateTime($waktu, new \DateTimeZone('Asia/Jakarta'));
        return $DateTime->format("Y-m-d\TH:i:sP");
    } catch (Exception $e) {
        return null;
    }
}

function hariIndo($hariInggris)
{
    $map = [
        'Sunday'    => 'Minggu',
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu',
    ];
    return $map[$hariInggris] ?? $hariInggris;
}

function getTemperatureInterpretation(float $suhu): array
    {
        if ($suhu > 37) {
            return ['code' => 'H', 'display' => 'High', 'text' => 'Di atas Nilai Referensi'];
        }
        if ($suhu < 36) {
            return ['code' => 'L', 'display' => 'Low', 'text' => 'Di bawah Nilai Referensi'];
        }
        return ['code' => 'N', 'display' => 'Normal', 'text' => 'Di antara Nilai Referensi'];
    }