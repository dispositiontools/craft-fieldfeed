<?php

namespace dispositiontools\fieldfeed\models;

use Craft;
use craft\base\Model;
use DateTime;

/**
 * Field Feed Update model
 */
class FieldFeedUpdate extends Model
{



    public ?int $id = null;
    public ?DateTime $dateCreated = null;
    public ?DateTime $dateUpdated = null;
    public ?DateTime $dateDeleted = null;
    public ?string $uid = null;
// Custom columns in the table

    public ?int $userId = null;
    public ?int $elementId = null;
    public ?int $siteId = null;
    public ?int $draftId = null;
    public ?int $entryId = null;
    public ?int $sectionId = null;
    public ?int $groupId = null;
    public ?string $fieldHandle = null;
    public ?int $productTypeId = null;
    public ?int $productId = null;
    public ?string $elementTypeClass = null;
    public ?int $elementTypeId = null;
    public ?string $fromValue = null;
    public ?string $value = null;
    public ?string $color = null;
    public ?int $teamTypeId = null;
    public ?string $message = null;
    public ?int $previousHistoryId = null;
    
    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            // ...
        ]);
    }
}
