<?php
// --------- Konfigurácia ---------
date_default_timezone_set("Europe/Bratislava");
define('ARRIVAL_LIST', 'data.json');

// ----------- Funkcie -----------

function getCurrentTime() {
    return date("H:i:s");
}

function getCurrentDate() {
    return date("Y-m-d");
}

function loadArrivals() {
    if (!file_exists(ARRIVAL_LIST)) return [];
    $json = file_get_contents(ARRIVAL_LIST);
    return json_decode($json, true) ?? [];
}

function saveArrivals($arrivals) {
    file_put_contents(ARRIVAL_LIST, json_encode($arrivals));
}

function isLate($time) {
    return $time > "08:00:00" && $time <= "20:00:00";
}

function isTooLate($time) {
    return $time > "20:00:00";
}

function recordArrival() {
    $time = getCurrentTime();
    $date = getCurrentDate();

    if (isTooLate($time)) {
        return; // Nezapisuj príchod po 20:00
    }

    $arrival = [
        "date" => $date,
        "time" => $time,
        "note" => isLate($time) ? "meškanie" : ""
    ];

    $arrivals = loadArrivals();
    $arrivals[] = $arrival;
    saveArrivals($arrivals);
}

function groupArrivalsByDate($arrivals) {
    $grouped = [];
    foreach ($arrivals as $arrival) {
        $grouped[$arrival['date']][] = $arrival;
    }
    return $grouped;
}

// ----------- Spustenie programu -----------

recordArrival();
$arrivals = loadArrivals();
$groupedArrivals = groupArrivalsByDate($arrivals);
$currentTime = getCurrentTime();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Zoznam príchodov</title>
  <link rel="stylesheet" href="style.css"></link>
</head>
<body>
  <div class="header">
    Aktuálny čas: <?= $currentTime ?>
  </div>
  
  <div class="container">
    <?php foreach ($groupedArrivals as $date => $dayArrivals): ?>
      <div class="date-group">
        <div class="date-heading"><?= htmlspecialchars($date) ?></div>
        <?php 
          $index = 1;
          foreach ($dayArrivals as $arrival): ?>
          <div class="arrival">
            <span><?= $index++ ?>. <?= htmlspecialchars($arrival['time']) ?></span>
            <span class="note"><?= htmlspecialchars($arrival['note']) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</body>
</html>