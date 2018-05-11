<?php

$url_map = [
  'develop' => 'http://build-umami.localhost',
];

if (isset($url_map[getenv('SYNETIC_DTAP_ENV')])) {
  $options['l'] = $url_map[getenv('SYNETIC_DTAP_ENV')];
}
