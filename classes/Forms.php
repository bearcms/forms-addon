<?php

/*
 * Forms addon for Bear CMS
 * https://github.com/bearcms/forms-addon
 * Copyright (c) Amplilabs Ltd.
 * Free to use under the MIT license.
 */

namespace BearCMS;

/**
 * @property-read \BearCMS\Forms\Models\Forms $forms
 * @property-read \BearCMS\Forms\Models\Responses $responses
 */
class Forms
{

    use \IvoPetkov\DataObjectTrait;

    /**
     * 
     */
    public function __construct()
    {
        $this
            ->defineProperty('forms', [
                'init' => function () {
                    return new \BearCMS\Forms\Models\Forms();
                },
                'readonly' => true
            ])
            ->defineProperty('responses', [
                'init' => function () {
                    return new \BearCMS\Forms\Models\Responses();
                },
                'readonly' => true
            ]);
    }
}
