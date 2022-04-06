<?php
/*
 * Forms addon for Bear CMS
 * https://github.com/bearcms/forms-addon
 * Copyright (c) Amplilabs Ltd.
 * Free to use under the MIT license.
 */

use BearFramework\App;

$app = App::get();
$context = $app->contexts->get(__DIR__);

$formID = (string)$component->formID;

$outputType = (string) $component->getAttribute('output-type');
$outputType = isset($outputType[0]) ? $outputType : 'full-html';
$isFullHtmlOutputType = $outputType === 'full-html';

$content = '<div' . ($isFullHtmlOutputType ? ' class="bearcms-form-element"' : '') . '>';
if ($isFullHtmlOutputType) {
    $content .= '<component src="form" filename="' . $context->dir . '/components/form.php" formID="' . htmlentities($formID) . '"/>';
}
$content .= '</div>';
echo '<html><head>';
echo '</head><body>';
echo $content;
echo '</body></html>';
