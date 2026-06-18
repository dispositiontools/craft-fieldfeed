<?php

namespace dispositiontools\fieldfeed\events;

use dispositiontools\fieldfeed\models\FieldFeedUpdate as FieldFeedUpdateModel;
use yii\base\Event;
use craft\base\element;
use craft\elements\User;
/**
 * @since 1.0.0
 */
class FieldValueUpdatedEvent extends Event
{
    /**
     * @var FieldFeedUpdateModel
     */
    public FieldFeedUpdateModel $fieldFeedUpdateModel;
    public element $element;
    public ?User $user = null;
}
