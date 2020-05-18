<?php

require __DIR__.'/../src/CedulaVE.php';

use MegaCreativo\API\CedulaVE;

$data = CedulaVE::info('V', '4747476', false);

echo '<pre>'.print_r($data, true).'</pre>';
