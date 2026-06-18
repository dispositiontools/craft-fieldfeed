<?php

namespace dispositiontools\fieldfeed\migrations;

use Craft;
use craft\db\Migration;
use dispositiontools\fieldfeed\records\FieldFeedUpdate as FieldFeedUpdateRecord;
/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Place installation code here...
        // Place installation code here...
        if ($this->createTables()) {
            //$this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            //$this->insertDefaultData();
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        // Place uninstallation code here...
        $this->removeTables();
        return true;
    }



    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables(): void
    {
        $this->dropTableIfExists(FieldFeedUpdateRecord::tableName());
    }


          /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys(): void
    {


      // teams_teams table
    
          $this->addForeignKey(
              $this->db->getForeignKeyName(FieldFeedUpdateRecord::tableName(), 'siteId'),
              FieldFeedUpdateRecord::tableName(),
              'siteId',
              '{{%sites}}',
              'id',
              'CASCADE',
              'CASCADE'
          );


    // teams_members table
        $this->addForeignKey(
            $this->db->getForeignKeyName(FieldFeedUpdateRecord::tableName(), 'userId'),
            FieldFeedUpdateRecord::tableName(),
            'userId',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName(FieldFeedUpdateRecord::tableName(), 'elementId'),
            FieldFeedUpdateRecord::tableName(),
            'elementId',
            '{{%elements}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

   
    }


     /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables(): bool
    {
        $tablesCreated = false;



        // teams table
            $tableSchema = Craft::$app->db->schema->getTableSchema(FieldFeedUpdateRecord::tableName());
            if ($tableSchema === null) {
                $tablesCreated = true;
                $this->createTable(
                    FieldFeedUpdateRecord::tableName(),
                    [
                        'id' => $this->primaryKey(),
                        'dateCreated' => $this->dateTime()->notNull(),
                        'dateUpdated' => $this->dateTime()->notNull(),
                        'dateDeleted' => $this->dateTime()->defaultValue(NULL),
                        'uid' => $this->uid(),
                    // Custom columns in the table

                      	'userId' 	=> $this->integer()->defaultValue(NULL),
                        'elementId' 	=> $this->integer()->defaultValue(NULL),
                        'siteId' 	=> $this->integer()->defaultValue(NULL),
                        'draftId' 	=> $this->integer()->defaultValue(NULL),
                        'entryId' 	=> $this->integer()->defaultValue(NULL),
                        'sectionId' => $this->integer()->defaultValue(NULL),
                        'groupId' => $this->integer()->defaultValue(NULL),
                        'fieldHandle' => $this->string()->defaultValue(NULL),
                        'productTypeId' => $this->integer()->defaultValue(NULL),
                        'productId' 	=> $this->integer()->defaultValue(NULL),
                        'elementTypeClass' 	=> $this->string(256)->defaultValue(NULL),
                        'elementTypeId' 	=> $this->integer()->defaultValue(NULL),
                        'fromValue' 	=> $this->string(512)->defaultValue(NULL),
                        'value' 	=> $this->string(512)->defaultValue(NULL),
                        'color' 	=> $this->string(6)->defaultValue(NULL),
                      	'teamTypeId' 	=> $this->integer()->defaultValue(NULL),
                      	'message' 	=> $this->text()->defaultValue(NULL),

                        'previousHistoryId' 	=> $this->integer()->defaultValue(NULL),

                        
                        

                    ]
                );
            }

            return true;

    }
}
