<?php

use Factory\Requirements\Helpers\FactoryHelpers;

if (file_exists($autoloader = __DIR__ . '/../../../autoload.php')) {
    require_once $autoloader;
} elseif (file_exists($autoloader = __DIR__ . '/../vendor/autoload.php')) {
    require_once $autoloader;
} elseif (!class_exists('Factory\Requirements\Requirement', false)) {
    require_once dirname(__DIR__) . '/src/Helpers/FactoryHelpers.php';
}

$factoryHelper = new FactoryHelpers();

// Color codes
$RED = "\033[0;31m";
$GREEN = "\033[0;32m";
$NC = "\033[0m"; // No color

$factoryHelper->echo_title('> Checking Vactory V4 (NextJs) requirements');

$errors = [];

// Check if Node.js is installed
if (!command_exists('node')) {
    $errors[] = "Node.js is not installed. Please install Node.js (https://nodejs.org) before proceeding.";
}

// Check Node.js version
$nodeVersion = trim(shell_exec('node --version'));
$requiredNodeVersion = 'v16.0.0'; // Update this to the minimum required Node.js version for Next.js 13

if (!preg_match('/^v\d+\.\d+\.\d+/', $nodeVersion)) {
    $errors[] = "Unable to determine Node.js version.";
}

if (version_compare($nodeVersion, $requiredNodeVersion, '<')) {
    $errors[] = "Your Node.js version ($nodeVersion) is below the minimum required version ($requiredNodeVersion). Please upgrade Node.js before proceeding.";
}

// Check if Yarn is installed
if (!command_exists('yarn')) {
    $errors[] = "Yarn is not installed. Please install Yarn (https://yarnpkg.com) before proceeding.";
}

// Check Yarn version
$yarnVersion = trim(shell_exec('yarn --version'));
$requiredYarnVersion = '1.22.0'; // Update this to the minimum required Yarn version for Next.js 13

if (!preg_match('/^\d+\.\d+\.\d+/', $yarnVersion)) {
    $errors[] = "Unable to determine Yarn version.";
}

if (version_compare($yarnVersion, $requiredYarnVersion, '<')) {
    $errors[] = "Your Yarn version ($yarnVersion) is below the minimum required version ($requiredYarnVersion). Please upgrade Yarn before proceeding.";
}

// Check if pm2 is installed and the version is greater than 5.x.x
$pm2Version = trim(shell_exec('pm2 --version'));
$requiredPm2Version = '5.0.0'; // Update this to the minimum required pm2 version

if (!preg_match('/^\d+\.\d+\.\d+/', $pm2Version)) {
    $errors[] = "Unable to determine pm2 version.";
}

if (version_compare($pm2Version, $requiredPm2Version, '<')) {
    $errors[] = "Your pm2 version ($pm2Version) is below the minimum required version ($requiredPm2Version). Please upgrade pm2 before proceeding.";
}

// Check if redis-cli is installed and the version is greater than 6.x.x
$redisVersion = trim(shell_exec('redis-cli --version'));
$requiredRedisVersion = '6.0.0'; // Update this to the minimum required redis-cli version

if (!preg_match('/^\d+\.\d+\.\d+/', $redisVersion)) {
    $errors[] = "Unable to determine redis-cli version.";
}

if (version_compare($redisVersion, $requiredRedisVersion, '<')) {
    $errors[] = "Your redis-cli version ($redisVersion) is below the minimum required version ($requiredRedisVersion). Please upgrade redis-cli before proceeding.";
}

// Check if .htaccess file exists
$useBasicAuth = false;
$username = '';
$password = '';

// Get endpoint from user input
$endpoint = '';
while (!is_valid_url($endpoint)) {
    $endpoint = readline("Enter the Back-End url to check the connectivity status between back and front (e.g., https://backend.vactory.lecontenaire.com/fr): ");
}

$useBasicAuth = readline("Do you want to include Basic Authentication? (y/n): ");
if (strtolower($useBasicAuth) === 'y') {
    $username = readline("Enter the username: ");
    $password = readline("Enter the password: ");
}

// Check cURL request to the endpoint
$curl = curl_init($endpoint);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, true);
curl_setopt($curl, CURLOPT_NOBODY, true);

if ($useBasicAuth) {
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
}

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if ($httpCode !== 200) {
    $errors[] = "cURL request to $endpoint failed with HTTP code $httpCode.";
}

// Print service status and versions
if (empty($errors)) {
    $factoryHelper->echo_block('success', 'OK', 'Congratulations! Your environment is ready to launch Vactory V4 (NextJs) app.');
    $factoryHelper->echo_title("Service Status", 'green');
    echo "- Node.js: Installed (Version: $nodeVersion)" . PHP_EOL;
    echo "- Yarn: Installed (Version: $yarnVersion)" . PHP_EOL;
    echo "- pm2: Installed (Version: $pm2Version)" . PHP_EOL;
    echo "- redis-cli: Installed (Version: $redisVersion)" . PHP_EOL;
    echo "- cURL request to $endpoint success." . PHP_EOL;
} else {
    $factoryHelper->echo_block('error', 'ERROR', 'Your system is not ready to run Vactory V4 (NextJs) app');
    $factoryHelper->echo_title("Fix the following mandatory requirements", 'red');
    foreach ($errors as $error) {
        echo " * $error" . PHP_EOL;
    }
}

/**
 * Checks if a command exists on the system.
 *
 * @param string $command The command to check.
 * @return bool True if the command exists, false otherwise.
 */
function command_exists($command)
{
    $whereIsCommand = 'command -v';
    $output = shell_exec("$whereIsCommand $command");
    return !empty($output);
}

/**
 * Checks if a string is a valid URL.
 *
 * @param string $url The string to check.
 * @return bool True if the string is a valid URL, false otherwise.
 */
function is_valid_url($url)
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}