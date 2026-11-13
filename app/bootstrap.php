<?php
declare(strict_types=1);

const BASE_PATH = __DIR__ . '/..';

date_default_timezone_set('Asia/Ho_Chi_Minh');
mb_internal_encoding('UTF-8');

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/Workflow.php';
require_once __DIR__ . '/Routes.php';
require_once __DIR__ . '/PageData.php';
require_once __DIR__ . '/Content.php';
require_once __DIR__ . '/Dashboard.php';
require_once __DIR__ . '/Presentation.php';
require_once __DIR__ . '/Media.php';
require_once __DIR__ . '/QuotationDetails.php';
require_once __DIR__ . '/Config.php';

$config = appConfiguration(BASE_PATH);
Database::connect($config);
ensureQuotationSchema();

require_once __DIR__ . '/CommerceSchema.php';
require_once __DIR__ . '/Commercial.php';
require_once __DIR__ . '/Commerce.php';
require_once __DIR__ . '/Recovery.php';
require_once __DIR__ . '/OnlinePayment.php';
require_once __DIR__ . '/CatalogFilters.php';

ensureCommerceSchema();
