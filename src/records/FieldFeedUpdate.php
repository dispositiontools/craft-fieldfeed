<?php

namespace dispositiontools\fieldfeed\records;

use Craft;
use craft\db\ActiveRecord;
use craft\db\SoftDeleteTrait;

/**
 * Field Feed Update record
 */
class FieldFeedUpdate extends ActiveRecord
{
    use SoftDeleteTrait;

    public static function tableName()
    {
        return '{{%field-feed_updates}}';
    }
}
