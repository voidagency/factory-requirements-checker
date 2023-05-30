<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Factory\Requirements\Requirement;
use Factory\Requirements\Helpers\FactoryHelpers;
use Factory\Requirements\FactoryRequirements;
use Factory\Requirements\ProjectRequirements;

if (file_exists($autoloader = __DIR__ . '/../../../autoload.php')) {
    require_once $autoloader;
} elseif (file_exists($autoloader = __DIR__ . '/../vendor/autoload.php')) {
    require_once $autoloader;
} elseif (!class_exists('Factory\Requirements\Requirement', false)) {
    require_once dirname(__DIR__) . '/src/Requirement.php';
    require_once dirname(__DIR__) . '/src/RequirementCollection.php';
    require_once dirname(__DIR__) . '/src/PhpConfigRequirement.php';
    require_once dirname(__DIR__) . '/src/FactoryRequirements.php';
    require_once dirname(__DIR__) . '/src/ProjectRequirements.php';
    require_once dirname(__DIR__) . '/src/Helpers/FactoryHelpers.php';
}

$lineSize = 70;
$args = array();
$isVerbose = false;
foreach ($argv as $arg) {
    if ('-v' === $arg || '-vv' === $arg || '-vvv' === $arg) {
        $isVerbose = true;
    } else {
        $args[] = $arg;
    }
}

$factoryHelper = new FactoryHelpers();

$factoryRequirements = new FactoryRequirements();
$requirements = $factoryRequirements->getRequirements();

$factoryHelper->echo_title('> Checking Vactory Drupal requirements');

// specific directory to check?
$dir = isset($args[1]) ? $args[1] : (file_exists(getcwd() . '/composer.json') ? getcwd() . '/composer.json' : null);
if (null !== $dir) {
    $projectRequirements = new ProjectRequirements($dir);
    $requirements = array_merge($requirements, $projectRequirements->getRequirements());
}


echo '> PHP is using the following php.ini file:' . PHP_EOL;
if ($iniPath = get_cfg_var('cfg_file_path')) {
    $factoryHelper->echo_style('green', $iniPath);
} else {
    $factoryHelper->echo_style('yellow', 'WARNING: No configuration file (php.ini) used by PHP!');
}

echo PHP_EOL . PHP_EOL;

echo '> Checking Factory requirements:' . PHP_EOL . PHP_EOL;

$messages = array();
foreach ($requirements as $req) {
    if ($helpText = get_error_message($req, $lineSize)) {
        if ($isVerbose) {
            $factoryHelper->echo_style('red', '[ERROR] ');
            echo $req->getTestMessage() . PHP_EOL;
        } else {
            $factoryHelper->echo_style('red', 'E');
        }

        $messages['error'][] = $helpText;
    } else {
        if ($isVerbose) {
            $factoryHelper->echo_style('green', '[OK] ');
            echo $req->getTestMessage() . PHP_EOL;
        } else {
            $factoryHelper->echo_style('green', '.');
        }
    }
}

$checkPassed = empty($messages['error']);

foreach ($factoryRequirements->getRecommendations() as $req) {
    if ($helpText = get_error_message($req, $lineSize)) {
        if ($isVerbose) {
            $factoryHelper->echo_style('yellow', '[WARN] ');
            echo $req->getTestMessage() . PHP_EOL;
        } else {
            $factoryHelper->echo_style('yellow', 'W');
        }

        $messages['warning'][] = $helpText;
    } else {
        if ($isVerbose) {
            $factoryHelper->echo_style('green', '[OK] ');
            echo $req->getTestMessage() . PHP_EOL;
        } else {
            $factoryHelper->echo_style('green', '.');
        }
    }
}

if ($checkPassed) {
    $factoryHelper->echo_block('success', 'OK', 'Your system is ready to run Vactory Drupal projects');
} else {
    $factoryHelper->echo_block('error', 'ERROR', 'Your system is not ready to run Vactory Drupal projects');

    $factoryHelper->echo_title('Fix the following mandatory requirements', 'red');

    foreach ($messages['error'] as $helpText) {
        echo ' * ' . $helpText . PHP_EOL;
    }
}

if (!empty($messages['warning'])) {
    $factoryHelper->echo_title('Optional recommendations to improve your setup', 'yellow');

    foreach ($messages['warning'] as $helpText) {
        echo ' * ' . $helpText . PHP_EOL;
    }
}

echo PHP_EOL;
$factoryHelper->echo_style('title', 'Note');
echo '  The command console can use a different php.ini file' . PHP_EOL;
$factoryHelper->echo_style('title', '~~~~');
echo '  than the one used by your web server.' . PHP_EOL;
echo '      Please check that both the console and the web server' . PHP_EOL;
echo '      are using the same PHP version and configuration.' . PHP_EOL;
echo PHP_EOL;

exit($checkPassed ? 0 : 1);


function get_error_message(Requirement $requirement, $lineSize)
{
    if ($requirement->isFulfilled()) {
        return;
    }

    $errorMessage = wordwrap($requirement->getTestMessage(), $lineSize - 3, PHP_EOL . '   ') . PHP_EOL;
    $errorMessage .= '   > ' . wordwrap($requirement->getHelpText(), $lineSize - 5, PHP_EOL . '   > ') . PHP_EOL;

    return $errorMessage;
}