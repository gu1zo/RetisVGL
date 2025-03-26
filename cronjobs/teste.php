<?php
namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\Massivas as EntityMassiva;
use \App\Model\Rest\APIInt6;
use \App\Controller\Massiva\Massiva;
use DateTime;
use DateTimeZone;

var_dump(APIInt6::verificaMassivaBydId('13058'));