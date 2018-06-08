<?php

$pathFormat = '%s/active-experiment';
$baseUrlsFileName = __DIR__ . '/site_routes.csv';
$experimentInfoFileName = __DIR__ . '/active_experiments.json';

$baseUrlsCSV = file_get_contents($baseUrlsFileName);
if ($baseUrlsCSV === FALSE) {
  throw new \Exception('Routing file not found');
}
$baseUrls = explode(',', $baseUrlsCSV);

$experimentInfo = [];

foreach ($baseUrls as $key => $baseUrl) {
  $fullUrl = sprintf($pathFormat, $baseUrl);
  $curl    = curl_init($fullUrl);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
  $result = curl_exec($curl);
  $code   = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  if ($code === 200) {
    $result = json_decode($result, TRUE);
    if (!empty($result)) {
      $experimentInfo[$baseUrl] = $result;
    }
  } else {
    // If we don't get a 200, remove the site route so it doesn't get called each time.
    echo sprintf('Could not reach site "%s", removing from list', $baseUrl);
    unset($baseUrls[$key]);
  }
  curl_close($curl);
}

// Rewrite the site-routes to remove any sites that were not reached through cron.
file_put_contents($baseUrlsFileName, implode(',', $baseUrls));

// Rewrite the experiment info file with up to date information.
file_put_contents($experimentInfoFileName, json_encode($experimentInfo));
