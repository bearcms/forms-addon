<?php

/*
 * Forms addon for Bear CMS
 * https://github.com/bearcms/forms-addon
 * Copyright (c) Amplilabs Ltd.
 * Free to use under the MIT license.
 */

use BearCMS\Forms;

/**
 * @runTestsInSeparateProcesses
 */
class FormsTest extends BearCMS\AddonTests\PHPUnitTestCase
{
    /**
     * 
     */
    public function testBasics()
    {
        $app = $this->getApp();

        $this->assertTrue($app->forms instanceof Forms);
    }
}
