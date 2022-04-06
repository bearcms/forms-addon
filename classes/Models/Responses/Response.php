<?php

/*
 * Forms addon for Bear CMS
 * https://github.com/bearcms/forms-addon
 * Copyright (c) Amplilabs Ltd.
 * Free to use under the MIT license.
 */

namespace BearCMS\Forms\Models\Responses;

use BearFramework\App;
use BearFramework\Models\Model;

/**
 * @property string|null $id
 * @property string|null $formID
 * @property DateTime|null $date
 * @property array $value
 * @property-read string|null $formName
 * @property-read string|null $valueSummary
 * @property-read array|null $valueDetails
 */
class Response extends Model
{
    function __construct()
    {
        $this
            ->defineProperty('id', [
                'type' => '?string'
            ])
            ->defineProperty('formID', [
                'type' => '?string',
            ])
            ->defineProperty('date', [
                'type' => '?DateTime'
            ])
            ->defineProperty('value', [
                'type' => 'array'
            ])
            ->defineProperty('formName', [
                'type' => '?string',
                'readonly' => true,
                'init' => function () {
                    if ($this->formID !== null) {
                        $app = App::get();
                        $form = $app->forms->forms->get($this->formID);
                        return $form->name;
                    }
                    return null;
                }
            ])
            ->defineProperty('valueSummary', [
                'type' => '?string',
                'readonly' => true,
                'init' => function () {
                    $getSingleLineText = function (string $text) {
                        $text = str_replace(["\n\r", "\r\n", "\n"], ' ', $text);
                        for ($i = 0; $i < 2; $i++) {
                            $text = str_replace('  ', ' ', $text);
                        }
                        return trim($text);
                    };
                    $valueSummary = [];
                    foreach ($this->value as $value) {
                        if (isset($value['value'])) {
                            if (is_array($value['value'])) {
                                if (!empty($value['value'])) {
                                    $valueSummary[] = $getSingleLineText(implode(', ', $value['value']));
                                }
                            } elseif (is_string($value['value'])) {
                                if (strlen($value['value']) > 0) {
                                    $valueSummary[] = $getSingleLineText($value['value']);
                                }
                            }
                        }
                    }
                    return substr(implode(', ', $valueSummary), 0, 1000);
                }
            ])
            ->defineProperty('valueDetails', [
                'type' => '?array',
                'readonly' => true,
                'init' => function () {
                    $valueDetails = [];
                    foreach ($this->value as $value) {
                        if (isset($value['value'])) {
                            $partValue = '';
                            if (is_array($value['value'])) {
                                if (!empty($value['value'])) {
                                    $partValue = implode(', ', $value['value']);
                                }
                            } elseif (is_string($value['value'])) {
                                if (strlen($value['value']) > 0) {
                                    $partValue = $value['value'];
                                }
                            }
                            if ($partValue !== '') {
                                $valueDetails[] = [
                                    'name' => $value['name'],
                                    'value' => $partValue
                                ];
                            }
                        }
                    }
                    return $valueDetails;
                }
            ]);
    }
}
