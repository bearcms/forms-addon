<?php

/*
 * Forms addon for Bear CMS
 * https://github.com/bearcms/forms-addon
 * Copyright (c) Amplilabs Ltd.
 * Free to use under the MIT license.
 */

namespace BearCMS\Forms\Models\Forms;

use BearFramework\App;
use BearFramework\Models\Model;

/**
 * @property string|null $id
 * @property string|null $name
 * @property array $fields
 * @property string|null $onSubmit
 * @property string|null $onSubmitMessage
 * @property string|null $onSubmitRedirectURL
 * @property string|null $submitButtonText
 * @property string|null $notifyEmails
 * @property DateTime|null $lastChangeDate
 * @property-read DateTime|null $lastUpdateDate
 * @property-read DateTime|null $lastResponseDate
 * @property-read int|null $responsesCount
 * @property-read array|null $responsesSummary
 */
class Form extends Model
{

    function __construct()
    {
        $this
            ->defineProperty('id', [
                'type' => '?string'
            ])
            ->defineProperty('name', [
                'type' => '?string'
            ])
            ->defineProperty('fields', [
                'type' => 'array'
            ])
            ->defineProperty('onSubmit', [
                'type' => '?string'
            ])
            ->defineProperty('onSubmitMessage', [
                'type' => '?string'
            ])
            ->defineProperty('onSubmitRedirectURL', [
                'type' => '?string'
            ])
            ->defineProperty('submitButtonText', [
                'type' => '?string'
            ])
            ->defineProperty('notifyEmails', [
                'type' => '?string'
            ])
            ->defineProperty('notifyEmailsAddReplyTo', [
                'type' => '?bool',
                'init' => function () {
                    return false;
                }
            ])
            ->defineProperty('notifyEmailsSubject', [
                'type' => '?string'
            ])
            ->defineProperty('lastChangeDate', [
                'type' => '?DateTime'
            ])
            ->defineProperty('lastUpdateDate', [
                'type' => '?DateTime',
                'readonly' => true,
                'init' => function () {
                    $dates = [];
                    if ($this->lastChangeDate !== null) {
                        $dates[] = $this->lastChangeDate->getTimestamp();
                    }
                    if ($this->lastResponseDate !== null) {
                        $dates[] = $this->lastResponseDate->getTimestamp();
                    }
                    if (empty($dates)) {
                        return null;
                    }
                    $dateTime = new \DateTime();
                    $dateTime->setTimestamp((string)max($dates));
                    return $dateTime;
                }
            ])
            ->defineProperty('lastResponseDate', [
                'type' => '?DateTime',
                'readonly' => true,
                'init' => function () {
                    if ($this->id !== null) {
                        $app = App::get();
                        $responses = $app->forms->responses->getList()
                            ->filterBy('formID', $this->id)
                            ->sortBy('id', 'desc') // todo optmize sort by id
                            ->slice(0, 1);
                        if ($responses->count() > 0) {
                            $response = $responses[0];
                            return $response->date;
                        }
                    }
                    return null;
                }
            ])
            ->defineProperty('responsesCount', [
                'type' => '?int',
                'readonly' => true,
                'init' => function () {
                    if ($this->id !== null) {
                        $app = App::get();
                        $responses = $app->forms->responses->getList()
                            ->filterBy('formID', $this->id); // todo optimize for count or cache
                        return $responses->count();
                    }
                }
            ])
            ->defineProperty('responsesSummary', [
                'type' => '?array',
                'readonly' => true,
                'init' => function () {
                    if ($this->id !== null) {
                        $app = App::get();
                        $responsesSummary = [];
                        $responses = $app->forms->responses->getList() // todo cache maybe
                            ->filterBy('formID', $this->id);
                        foreach ($responses as $response) {
                            foreach ($response->value as $value) {
                                if (isset($value['value'])) {
                                    $addOptionValue = function ($optionValue) use ($value, &$responsesSummary): void {
                                        $valueKey = md5($value['name']);
                                        if (!isset($responsesSummary[$valueKey])) {
                                            $responsesSummary[$valueKey] = [
                                                'name' => $value['name'],
                                                'responses' => []
                                            ];
                                        }
                                        $responsesSummary[$valueKey]['responses'][] = $optionValue;
                                    };
                                    if (is_array($value['value'])) {
                                        foreach ($value['value'] as $optionValue) {
                                            if (mb_strlen($optionValue) > 0) {
                                                $addOptionValue($optionValue);
                                            }
                                        }
                                    } elseif (is_string($value['value'])) {
                                        if (mb_strlen($value['value']) > 0) {
                                            $addOptionValue($value['value']);
                                        }
                                    }
                                }
                            }
                        }
                        foreach ($responsesSummary as $key => $value) {
                            $countedValues = array_count_values($value['responses']);
                            arsort($countedValues);
                            $responsesData = [];
                            foreach ($countedValues as $name => $count) {
                                $responsesData[] = ['name' => $name, 'count' => $count];
                            }
                            $responsesSummary[$key]['responses'] = $responsesData;
                        }
                        return [
                            'count' => $responses->count(),
                            'values' => array_values($responsesSummary)
                        ];
                    }
                }
            ]);
    }
}
