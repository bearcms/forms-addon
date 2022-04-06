<?php

/*
 * Forms addon for Bear CMS
 * https://github.com/bearcms/forms-addon
 * Copyright (c) Amplilabs Ltd.
 * Free to use under the MIT license.
 */

namespace BearCMS\Forms\Models;

use BearCMS\Forms\Models\Forms\Form;
use BearFramework\Models\ModelsRepository;
use BearFramework\Models\ModelsRepositoryCreateTrait;
use BearFramework\App;

/**
 * 
 */
class Forms extends ModelsRepository
{

    use ModelsRepositoryCreateTrait;

    /**
     * 
     */
    public function __construct()
    {
        $this->setModel(Form::class, 'id');
        $this->useAppDataDriver('bearcms-forms/forms/');
    }

    /**
     * 
     * @param string $id
     * @return void
     */
    function delete(string $id): void
    {
        $app = App::get();
        $responsesRepository = $app->forms->responses;
        $responsesToDelete = $responsesRepository->getList()
            ->filterBy('formID', $id)
            ->sliceProperties(['id']);
        foreach ($responsesToDelete as $responseToDelete) {
            $responsesRepository->delete($responseToDelete->id);
        }
        parent::delete($id);
    }
}
