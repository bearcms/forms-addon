<?php

/*
 * Forms addon for Bear CMS
 * https://github.com/bearcms/forms-addon
 * Copyright (c) Amplilabs Ltd.
 * Free to use under the MIT license.
 */

namespace BearCMS\Forms\Models;

use BearCMS\Forms\Models\Responses\Response;
use BearFramework\App;
use BearFramework\Models\ModelsRepository;
use BearFramework\Models\ModelsRepositoryCreateTrait;

/**
 * 
 */
class Responses extends ModelsRepository
{
    use ModelsRepositoryCreateTrait;

    /**
     * 
     */
    public function __construct()
    {
        $this->setModel(Response::class, 'id');
        $this->useAppDataDriver('bearcms-forms/responses/', function (string $id) {
            $parts = explode('-', $id);
            return $parts[0] . '/' . $parts[1] . '.json';
        });
        $this->setIDGenerator(function (Response $response) {
            $formID = (string)$response->formID;
            if (mb_strlen($formID) === 0) {
                throw new \Exception('The formID property is required!');
            }
            $responses = $this->getList()
                ->filterBy('formID', $formID)
                ->sortBy('id', 'desc') // todo optmize sort by id
                ->slice(0, 1);
            if ($responses->count() > 0) {
                $lastResponse = $responses[0];
                $number = (int)str_replace($formID . '-', '', $lastResponse->id);
                $number++;
            } else {
                $number = 1;
            }
            return $formID . '-' . str_pad($number, 10, '0', STR_PAD_LEFT);
        });
    }

    // todo optimize getList when filter by formID and sort by date
}
