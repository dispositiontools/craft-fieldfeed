<?php

namespace dispositiontools\fieldfeed;

use Craft;
use Throwable;
use craft\base\Model;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;
use dispositiontools\fieldfeed\fields\FieldFeed as FieldFeedField;
use dispositiontools\fieldfeed\models\Settings;
use dispositiontools\fieldfeed\services\FieldFeedHistory;
use yii\base\Event;

use craft\helpers\ElementHelper;
use craft\events\ModelEvent;
use craft\base\Element;
/**
 * Field Feed plugin
 *
 * @method static FieldFeed getInstance()
 * @method Settings getSettings()
 * @author Disposition Tools <support@disposition.tools>
 * @copyright Disposition Tools
 * @license https://craftcms.github.io/license/ Craft License
 * @property-read FieldFeedHistory $fieldFeedHistory
 */
class FieldFeed extends Plugin
{
    public string $schemaVersion = "1.0.0";
    public bool $hasCpSettings = true;
    public static $plugin;

    public static function config(): array
    {
        return [
            "components" => ["fieldFeedHistory" => FieldFeedHistory::class],
        ];
    }

    public function init(): void
    {
        parent::init();
        self::$plugin = $this;
        $this->attachEventHandlers();

        // Any code that creates an element query or loads Twig should be deferred until
        // after Craft is fully initialized, to avoid conflicts with other plugins/modules
        Craft::$app->onInit(function () {
            // ...
        });
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate("field-feed/_settings.twig", [
            "plugin" => $this,
            "settings" => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)
        Event::on(Fields::class, Fields::EVENT_REGISTER_FIELD_TYPES, function (
            RegisterComponentTypesEvent $event,
        ) {
            $event->types[] = FieldFeedField::class;
        });

        // attach a event for on end propagate

        Event::on(Element::class, Element::EVENT_AFTER_PROPAGATE, function (
            ModelEvent $event,
        ) {
            $element = $event->sender;

            // basic checks
            if (!$element) {
                return;
            }
            if (ElementHelper::isDraftOrRevision($element)) {
                return;
            }

            $elementSerialixedFieldValues = $element->getSerializedFieldValues();

            $fieldHandles = $this->getConfiguredFieldHandlesForElement($element);

            foreach ($fieldHandles as $fieldHandle) {
                if (
                    array_key_exists(
                        $fieldHandle,
                        $elementSerialixedFieldValues,
                    )
                ) {
                    FieldFeed::$plugin->fieldFeedHistory->saveFieldHistoryOnEntryPropogate(
                        $element,
                        $fieldHandle,
                        $elementSerialixedFieldValues[$fieldHandle],
                        $fieldUpdateNotes = false,
                    );
                }
            }
        });

    }

    private function getFieldFeedConfig(): array
    {

        $settings = Craft::$app->config->getConfigFromFile('field-feed');
    
        return $settings;
    }

    private function getConfiguredFieldHandlesForElement(Element $element): array
    {
        $config = $this->getFieldFeedConfig();
        $fieldHandles = $this->normalizeFieldHandles($config['fallbackFieldHandles'] ?? $config['fieldHandles'] ?? []);

        foreach (($config['elementTypes'] ?? []) as $elementType => $rules) {
            if (!is_a($element, $elementType)) {
                continue;
            }

            foreach ($this->normalizeElementTypeRules($rules) as $rule) {
                if ($this->elementMatchesRule($element, $rule)) {
                    $fieldHandles = array_merge($fieldHandles, $this->normalizeFieldHandles($rule['fieldHandles'] ?? []));
                }
            }
        }

        return array_values(array_unique($fieldHandles));
    }

    private function normalizeElementTypeRules(array $rules): array
    {
        if (array_key_exists('fieldHandles', $rules)) {
            return [$rules];
        }

        return array_filter($rules, 'is_array');
    }

    private function normalizeFieldHandles(mixed $fieldHandles): array
    {
        if (is_string($fieldHandles)) {
            $fieldHandles = [$fieldHandles];
        }

        if (!is_array($fieldHandles)) {
            return [];
        }

        return array_values(array_filter($fieldHandles, 'is_string'));
    }

    private function elementMatchesRule(Element $element, array $rule): bool
    {
        foreach (['sectionId', 'typeId', 'siteId'] as $attribute) {
            if (!array_key_exists($attribute, $rule) || $rule[$attribute] === null || $rule[$attribute] === []) {
                continue;
            }

            if (!$this->matchesConfigValue($this->getElementAttributeValue($element, $attribute), $rule[$attribute])) {
                return false;
            }
        }

        return true;
    }

    private function matchesConfigValue(mixed $actualValue, mixed $configuredValue): bool
    {
        $configuredValues = is_array($configuredValue) ? $configuredValue : [$configuredValue];

        foreach ($configuredValues as $value) {
            if ((string)$actualValue === (string)$value) {
                return true;
            }
        }

        return false;
    }

    private function getElementAttributeValue(Element $element, string $attribute): mixed
    {
        try {
            return $element->$attribute ?? null;
        } catch (Throwable) {
            return null;
        }
    }
}
