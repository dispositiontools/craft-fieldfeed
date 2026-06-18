<?php

namespace dispositiontools\fieldfeed\services;

use Craft;
use yii\base\Component;
use dispositiontools\fieldfeed\models\FieldFeedUpdate as FieldFeedUpdateModel;
use dispositiontools\fieldfeed\records\FieldFeedUpdate as FieldFeedUpdateRecord;
use dispositiontools\fieldfeed\events\FieldValueUpdatedEvent;
use craft\elements\Entry as EntryElement;
use craft\commerce\elements\Product as ProductElement;

/**
 * Field Feed History service
 */
class FieldFeedHistory extends Component
{

    /**
     * @event FieldUpdatedEvent
     */
    public const EVENT_FIELD_VALUE_UPDATED = 'afterFieldValueUpdated';

    public function testSaveFeedHistory()
    {   
        $FieldFeedUpdateModel = new FieldFeedUpdateModel();
        $FieldFeedUpdateModel->fromValue = "Frank";
        $FieldFeedUpdateModel->value = "James";
        $this->saveFeedHistory($FieldFeedUpdateModel);
    }

    // ->fieldFeedHistory->saveFeedHistory($FieldFeedUpdateModel);
    public function saveFeedHistory(FieldFeedUpdateModel $model): FieldFeedUpdateModel
    {
        $isNew = !$model->id;
        
        if (!$isNew) {
            $record = FieldFeedUpdateRecord::findOne(['id' => $model->id]);
        } else {
            $record = new FieldFeedUpdateRecord();
        }
            
            
        $fieldsToUpdate = [
                'message',
                'fromValue',
                'value',

                'userId',
                'elementId',
                'siteId',
                'draftId',
                'entryId',
                'sectionId',
                'groupId',
                'fieldHandle',
                'productTypeId',
                'productId',
                'elementTypeClass',
                'elementTypeId',
     

                'color',
                'teamTypeId',

            ];
            
        foreach ($fieldsToUpdate as $handle) {
            if (property_exists($model, $handle)) {
                $record->$handle = $model->$handle;
            }
        }
        
        $record->validate();
        $model->addErrors($record->getErrors());
        

        $record->save(false);

        if ($isNew) {
            $model->id = $record->id;
        }
        
        return  $model;
    }


    public function getFieldFeed($element, $fieldHandle){

       
        $records = FieldFeedUpdateRecord::find()
            ->where([
                'elementId' => $element->id,
                 'siteId' => $element->siteId,
                'fieldHandle' => $fieldHandle,
            ])
            ->orderBy(['dateCreated' => SORT_DESC])
            ->asArray()
            ->all();

        return array_map(static fn(array $record): FieldFeedUpdateModel => new FieldFeedUpdateModel($record), $records);
    }


    // FieldFeed::$plugin->fieldFeedHistory->saveFieldHistoryOnEntryPropogate($element, $fieldHandle, $fieldValue, $fieldUpdateNotes = false);
    public function saveFieldHistoryOnEntryPropogate($element, $fieldHandle, $fieldValue, $fieldUpdateNotes = false)
    {

        // get last value;

        $recordQuery = FieldFeedUpdateRecord::find()
        ->where([
            "elementId"=>$element->id,
            "fieldHandle"=>$fieldHandle,
            ])
        ->limit(1);
        
        $record = $recordQuery->orderBy("dateCreated desc")->one();
        //ray($record);
        
        // create new model to get ready for 
        $fieldHistoryModel = new FieldFeedUpdateModel();
        if($record)
        {
            $oldModel = new FieldFeedUpdateModel($record->getAttributes());
            if($oldModel->value == $fieldValue )
            {
                // if the value is the same then lets just return as we don't need to save anything
                return;
            }
            else{
                $fieldHistoryModel->fromValue = $oldModel->value;
                $fieldHistoryModel->previousHistoryId = $oldModel->id;
            }
        }


        // get logged in user
        $user = Craft::$app->getUser()->getIdentity();
        if($user)
        {
            $fieldHistoryModel->userId = $user->id;
        }
       

        $fieldHistoryModel->elementId = $element->id;


        if($element instanceof EntryElement)
        {
            $fieldHistoryModel->entryId = $element->id;
            $fieldHistoryModel->sectionId = $element->sectionId;
            $fieldHistoryModel->elementTypeId = $element->typeId;
        }

        //craft\commerce\elements\Product
   
        if($element instanceof ProductElement)
        {
            $fieldHistoryModel->productId = $element->id;
            $fieldHistoryModel->productTypeId = $element->typeId;
            $fieldHistoryModel->elementTypeId = $element->typeId;
        }
    

        

        $fieldHistoryModel->fieldHandle = $fieldHandle;
        $fieldHistoryModel->value = $fieldValue;
        $fieldHistoryModel->siteId = $element->siteId;
        $fieldHistoryModel->elementTypeClass = get_class($element);

        $fieldHistoryModel = $this->saveFeedHistory($fieldHistoryModel);

        // Fire the Field Value Updated event
        if ($this->hasEventHandlers(self::EVENT_FIELD_VALUE_UPDATED)) {
            $this->trigger(self::EVENT_FIELD_VALUE_UPDATED, new FieldValueUpdatedEvent([
                'fieldFeedUpdateModel' => $fieldHistoryModel,
                'element' => $element,
                'user' => $user
            ]));
        }

        return;

    }
}
