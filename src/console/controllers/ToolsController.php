<?php

namespace dispositiontools\fieldfeed\console\controllers;

use Craft;
use craft\console\Controller;
use yii\console\ExitCode;

/**
 * Tools controller
 */
class ToolsController extends Controller
{
    public $defaultAction = 'index';

    public function options($actionID): array
    {
        $options = parent::options($actionID);
        switch ($actionID) {
            case 'index':
                // $options[] = '...';
                break;
        }
        return $options;
    }

    /**
     * field-feed/tools command
     */
    public function actionIndex(): int
    {
        // ...
        return ExitCode::OK;
    }

}
