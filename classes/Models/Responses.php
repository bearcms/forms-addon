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

    /**
     * 
     * @param string $responseID
     * @param string $basename
     * @return string
     */
    public function getFilename(string $responseID, string $basename): string
    {
        $app = App::get();
        $parts = explode('-', $responseID);
        $dataKey = 'bearcms-forms/files/' . $parts[0] . '/' . $parts[1] . '-' . $basename;
        return $app->data->getFilename($dataKey);
    }

    /**
     * 
     * @param string $responseID
     * @param string $basename
     * @param array $options Available values: download=>true
     * @return string
     */
    public function getURL(string $responseID, string $basename, array $options = []): string
    {
        $app = App::get();
        $urlOptions = ['robotsNoIndex' => true];
        if (isset($options['download']) && $options['download'] === true) {
            $urlOptions['download'] = true;
        }
        return $app->assets->getURL($this->getFilename($responseID, $basename), $urlOptions);
    }

    /**
     * 
     * @param string $filename
     * @return string
     */
    private function getFileDataKey(string $filename): string
    {
        return str_replace('appdata://', '', $filename);
    }

    /**
     * 
     * @param string $responseID
     * @param string $basename
     * @param string $source
     * @return void
     */
    public function addFile(string $responseID, string $basename, string $source)
    {
        $newFilename = $this->getFilename($responseID, $basename);
        copy($source, $newFilename);
        $dataKey = $this->getFileDataKey($newFilename);
        \BearCMS\Internal\Data\UploadsSize::add($dataKey, filesize($source));
    }

    /**
     * 
     * @param string $id
     * @return void
     */
    function delete(string $id): void
    {
        $response = $this->get($id);
        if ($response !== null) {
            foreach ($response->value as $value) {
                if (array_search($value['type'], ['image', 'file']) !== false) {
                    if (isset($value['value'])) {
                        foreach ($value['value'] as $filename) {
                            $filenameToDelete = $this->getFilename($id, $filename);
                            if (is_file($filenameToDelete)) {
                                unlink($filenameToDelete);
                            }
                            $dataKey = $this->getFileDataKey($filenameToDelete);
                            \BearCMS\Internal\Data\UploadsSize::remove($dataKey);
                        }
                    }
                }
            }
        }
        parent::delete($id);
    }

    // todo optimize getList when filter by formID and sort by date
}
