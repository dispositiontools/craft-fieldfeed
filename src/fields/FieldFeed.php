<?php

namespace dispositiontools\fieldfeed\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Cp;
use craft\helpers\Html;
use craft\helpers\StringHelper;
use dispositiontools\fieldfeed\FieldFeed as FieldFeedPlugin;
/**
 * Field Feed Field field type
 */
class FieldFeed extends Field implements PreviewableFieldInterface
{
    public string $historyFieldHandle = '';

    public static function displayName(): string
    {
        return Craft::t('field-feed', 'Field Feed');
    }

    public static function valueType(): string
    {
        return 'mixed';
    }

    public static function hasContentColumn(): bool
    {
        return false;
    }

    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'historyFieldHandle' => Craft::t('field-feed', 'History field'),
        ]);
    }

    protected function defineRules(): array
    {
        return array_merge(parent::defineRules(), [
            ['historyFieldHandle', 'string'],
            ['historyFieldHandle', 'in', 'range' => $this->configuredHistoryFieldHandles()],
        ]);
    }

    public function getSettingsHtml(): ?string
    {
        $fieldHandles = $this->configuredHistoryFieldHandles();
        $options = array_map(
            fn(string $handle): array => ['label' => $handle, 'value' => $handle],
            $fieldHandles,
        );

        if (empty($options)) {
            $options[] = [
                'label' => Craft::t('field-feed', 'No field handles configured'),
                'value' => '',
            ];
    }

        return Cp::selectFieldHtml([
            'label' => Craft::t('field-feed', 'Show history from'),
            'instructions' => Craft::t('field-feed', 'Choose the field handle this Field Feed field should display history for.'),
            'id' => 'history-field-handle',
            'name' => 'historyFieldHandle',
            'value' => $this->historyFieldHandle,
            'options' => $options,
            'disabled' => empty($fieldHandles),
            'errors' => $this->getErrors('historyFieldHandle'),
        ]);
    }

    private function configuredHistoryFieldHandles(): array
    {
        $config = Craft::$app->getConfig()->getConfigFromFile('field-feed') ?? [];

        if (!is_array($config)) {
            return [];
        }

        $handles = [];
        $this->collectFieldHandles($config, $handles);

        $handles = array_values(array_unique(array_filter($handles, 'is_string')));
        sort($handles, SORT_NATURAL | SORT_FLAG_CASE);

        return $handles;
    }

    private function collectFieldHandles(array $config, array &$handles): void
    {
        foreach ($config as $key => $value) {
            if ($key === 'fieldHandles' || $key === 'fallbackFieldHandles') {
                array_push($handles, ...$this->normalizeFieldHandles($value));
                continue;
            }

            if (is_array($value)) {
                $this->collectFieldHandles($value, $handles);
            }
        }
    }

    private function normalizeFieldHandles(mixed $fieldHandles): array
    {
        if (is_string($fieldHandles)) {
            return [$fieldHandles];
        }

        if (!is_array($fieldHandles)) {
            return [];
        }

        return array_values(array_filter($fieldHandles, 'is_string'));
    }

    public function normalizeValue(mixed $value, ?ElementInterface $element = null): mixed
    {
        return $value;
    }

    protected function inputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        //ray($element, $this);
        $models = FieldFeedPlugin::$plugin->fieldFeedHistory->getFieldFeed($element, $this->historyFieldHandle);
        ray($models);
        $html = "";
        if($models){
            foreach($models as $model){
                $html .= $model->fromValue . " -> ". $model->value;
            }
        }
        return $html;
        return Html::textarea($this->handle, $value);
    }

    public function getElementValidationRules(): array
    {
        return [];
    }

    protected function searchKeywords(mixed $value, ElementInterface $element): string
    {
        return StringHelper::toString($value, ' ');
    }

    public function getElementConditionRuleType(): array|string|null
    {
        return null;
    }

    public function modifyElementsQuery(ElementQueryInterface $query, mixed $value): void
    {
        parent::modifyElementsQuery($query, $value);
    }
}
