<?php

/*
 * Forms addon for Bear CMS
 * https://github.com/bearcms/forms-addon
 * Copyright (c) Amplilabs Ltd.
 * Free to use under the MIT license.
 */

BearFramework\Addons::register('bearcms/forms-addon', __DIR__, [
    'require' => [
        'bearcms/bearframework-addon',
        'bearframework/localization-addon',
        'bearframework/models-addon',
        'bearframework/tasks-addon',
        'bearframework/emails-addon',
        'ivopetkov/form-bearframework-addon',
        'ivopetkov/form-elements-bearframework-addon'
    ]
]);
