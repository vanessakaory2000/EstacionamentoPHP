<?php

declare(strict_types=1);
require_once __DIR__ . '/../vendor/autoload.php';

use Infra\Connection;
use Domain\Repository\ParkingSessionRepository;
use Domain\Services\CarService;
use Domain\Services\MotoService;
use Domain\Services\TruckService;
use Application\Flow\RegEntry;
use Application\Flow\RegExit;
use Application\Flow\GetReport;
use Application\Flow\ExportReportPdf;
use Application\Flow\ListOpenSessions;
use Infra\WebController;

// Inicializar dependências
$connection = new Connection();
$repository = new ParkingSessionRepository($connection);

$pricingStrategies = [
    new CarService(),
    new MotoService(),
    new TruckService(),
];

$regEntry = new RegEntry($repository);
$regExit = new RegExit($repository, $pricingStrategies);
$getReport = new GetReport($repository);
$exportPdf = new ExportReportPdf();
$listOpenSessions = new ListOpenSessions($repository);

$controller = new WebController($regEntry, $regExit, $getReport, $exportPdf, $listOpenSessions);

// Rotear requisições
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/parking';

if (str_starts_with($uri, $basePath)) {
    $path = substr($uri, strlen($basePath));
    if ($path === '' || $path === false) {
        $path = '/';
    }
} else {
    $path = $uri;
}

match ($path) {
    '/api/entry' => $controller->handleEntry(),
    '/api/exit' => $controller->handleExit(),
    '/api/report' => $controller->handleReport(),
    '/api/open-sessions' => $controller->handleOpenSessions(),
    '/index.js' => $controller->handleAppScript(),
    default => $controller->handlePage(),
};
