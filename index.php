<?php

/*
 * Forms addon for Bear CMS
 * https://github.com/bearcms/forms-addon
 * Copyright (c) Amplilabs Ltd.
 * Free to use under the MIT license.
 */

use BearCMS\Forms\Internal\Utilities;
use BearFramework\App;

$app = App::get();

$app->bearCMS->addons
    ->register('bearcms/forms-addon', function (\BearCMS\Addons\Addon $addon) use ($app) {
        $addon->initialize = function (array $options) use ($app) {
            $disabled = isset($options['disabled']) ? (bool)$options['disabled'] : false;

            $context = $app->contexts->get(__DIR__);
            $context->assets
                ->addDir('assets');
            $app->assets
                ->addDir('appdata://bearcms-forms/files/');

            $app->localization
                ->addDictionary('en', function () use ($context) {
                    return include $context->dir . '/locales/en.php';
                })
                ->addDictionary('bg', function () use ($context) {
                    return include $context->dir . '/locales/bg.php';
                });

            $context->classes
                ->add('BearCMS\Forms', 'classes/Forms.php')
                ->add('BearCMS\Forms\*', 'classes/*.php');

            Utilities::$disabled = $disabled;
            Utilities::$notificationEmailsSenderEmail = isset($options['notificationEmailsSenderEmail']) ? $options['notificationEmailsSenderEmail'] : null;

            \BearCMS\Internal\Config::$appSpecificServerData['m4ka041'] = 1;
            \BearCMS\Internal\Config::$appSpecificServerData['m4ka042'] = $disabled;

            $app->shortcuts
                ->add('forms', function () {
                    return new \BearCMS\Forms();
                });

            $type = new \BearCMS\Internal\ElementType('form', 'bearcms-form-element', $context->dir . '/components/bearcmsFormElement.php');
            $type->properties = [
                [
                    'id' => 'formID',
                    'type' => 'string'
                ]
            ];
            \BearCMS\Internal\ElementsTypes::add($type);

            \BearCMS\Internal\Themes::$elementsOptions['form'] = function ($options, $idPrefix, $parentSelector, $context, $details) {
                $groupForm = $options->addGroup(__("bearcms-forms.themes.options.Form"));

                $groupFormFields = $groupForm->addGroup(__("bearcms-forms.themes.options.FormFields"));

                $addFieldLabel = function ($fieldGroup, string $name, string $attributeValue, string $containerClassName) use ($idPrefix, $parentSelector) {
                    $labelGroup = $fieldGroup->addGroup(__("bearcms-forms.themes.options.Label"));
                    $labelGroup->addOption($idPrefix . $name . "LabelCSS", "css", '', [
                        "cssTypes" => ["cssText", "cssTextShadow", "cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                        "cssOptions" => array_diff(isset($details['cssOptions']) ? $details['cssOptions'] : [], ["*/focusState"]),
                        "cssOutput" => [
                            ["rule", $parentSelector . ' .' . $containerClassName . ' [data-form-element-type="' . $attributeValue . '"] [data-form-element-component="label"]', "display:block;box-sizing:border-box;word-break:break-word;"],
                            ["selector", $parentSelector . ' .' . $containerClassName . ' [data-form-element-type="' . $attributeValue . '"] [data-form-element-component="label"]']
                        ],
                        "defaultvalue" => '{"font-family":"Arial","font-size":"14px","line-height":"160%","color":"#000","padding-bottom":"4px"}'
                    ]);
                };
                $addFieldHint = function ($fieldGroup, string $name, string $attributeValue, string $containerClassName) use ($idPrefix, $parentSelector) {
                    $hintGroup = $fieldGroup->addGroup(__("bearcms-forms.themes.options.Hint"));
                    $hintGroup->addOption($idPrefix . $name . "HintCSS", "css", '', [
                        "cssTypes" => ["cssText", "cssTextShadow", "cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                        "cssOptions" => array_diff(isset($details['cssOptions']) ? $details['cssOptions'] : [], ["*/focusState"]),
                        "cssOutput" => [
                            ["rule", $parentSelector . ' .' . $containerClassName . ' [data-form-element-type="' . $attributeValue . '"] [data-form-element-component="hint"]', "display:block;box-sizing:border-box;word-break:break-word;"],
                            ["selector", $parentSelector . ' .' . $containerClassName . ' [data-form-element-type="' . $attributeValue . '"] [data-form-element-component="hint"]']
                        ],
                        "defaultvalue" => '{"font-family":"Arial","font-size":"12px","line-height":"140%","color":"#000","padding-bottom":"7px"}'
                    ]);
                };
                $addFieldContainer = function ($fieldGroup, string $name, string $className) use ($idPrefix, $parentSelector) {
                    $containerGroup = $fieldGroup->addGroup(__("bearcms-forms.themes.options.Container"));
                    $containerGroup->addOption($idPrefix . $name . "ContainerCSS", "css", '', [
                        "cssTypes" => ["cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                        "cssOptions" => array_diff(isset($details['cssOptions']) ? $details['cssOptions'] : [], ["*/focusState"]),
                        "cssOutput" => [
                            ["rule", $parentSelector . " ." . $className, "display:block;box-sizing:border-box;"],
                            ["selector", $parentSelector . " ." . $className]
                        ],
                        "defaultvalue" => '{"padding-bottom":"15px"}'
                    ]);
                };

                // Text
                $fieldText = $groupFormFields->addGroup(__("bearcms-forms.themes.options.FieldText"));
                $fieldText->addOption($idPrefix . "FormFieldTextCSS", "css", '', [
                    "cssTypes" => ["cssText", "cssTextShadow", "cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => isset($details['cssOptions']) ? $details['cssOptions'] : [],
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-text-container [data-form-element-type="textbox"] [data-form-element-component="input"]', "display:block;box-sizing:border-box;border:0;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-text-container [data-form-element-type="textbox"] [data-form-element-component="input"]']
                    ],
                    "defaultvalue" => '{"font-family":"Arial","font-size":"14px","color":"#000","width":"100%","height":"40px","line-height":"38px","padding-left":"13px","padding-right":"13px","background-color":"#ffffff","border-top":"1px solid #555","border-bottom":"1px solid #555","border-right":"1px solid #555","border-left":"1px solid #555","border-top-left-radius":"2px","border-top-right-radius":"2px","border-bottom-left-radius":"2px","border-bottom-right-radius":"2px"}'
                ]);
                $addFieldLabel($fieldText, 'FormFieldText', 'textbox', 'bearcms-form-element-field-text-container');
                $addFieldHint($fieldText, 'FormFieldText', 'textbox', 'bearcms-form-element-field-text-container');
                $addFieldContainer($fieldText, 'FormFieldText', 'bearcms-form-element-field-text-container');

                // Textarea
                $fieldTextarea = $groupFormFields->addGroup(__("bearcms-forms.themes.options.FieldTextarea"));
                $fieldTextarea->addOption($idPrefix . "FormFieldTextareaCSS", "css", '', [
                    "cssTypes" => ["cssText", "cssTextShadow", "cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => isset($details['cssOptions']) ? $details['cssOptions'] : [],
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-textarea-container [data-form-element-type="textarea"] [data-form-element-component="textarea"]', "display:block;box-sizing:border-box;border:0;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-textarea-container [data-form-element-type="textarea"] [data-form-element-component="textarea"]']
                    ],
                    "defaultvalue" => '{"font-family":"Arial","font-size":"14px","color":"#000","width":"100%","height":"140px","line-height":"24px","padding-left":"13px","padding-right":"13px","padding-top":"8px","padding-bottom":"8px","background-color":"#ffffff","border-top":"1px solid #555","border-bottom":"1px solid #555","border-right":"1px solid #555","border-left":"1px solid #555","border-top-left-radius":"2px","border-top-right-radius":"2px","border-bottom-left-radius":"2px","border-bottom-right-radius":"2px"}'
                ]);
                $addFieldLabel($fieldTextarea, 'FormFieldTextarea', 'textarea', 'bearcms-form-element-field-textarea-container');
                $addFieldHint($fieldTextarea, 'FormFieldTextarea', 'textarea', 'bearcms-form-element-field-textarea-container');
                $addFieldContainer($fieldTextarea, 'FormFieldTextarea', 'bearcms-form-element-field-textarea-container');

                // Name
                $fieldName = $groupFormFields->addGroup(__("bearcms-forms.themes.options.FieldName"));
                $fieldName->addOption($idPrefix . "FormFieldNameCSS", "css", '', [
                    "cssTypes" => ["cssText", "cssTextShadow", "cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => isset($details['cssOptions']) ? $details['cssOptions'] : [],
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-name-container [data-form-element-type="textbox"] [data-form-element-component="input"]', "display:block;box-sizing:border-box;border:0;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-name-container [data-form-element-type="textbox"] [data-form-element-component="input"]']
                    ],
                    "defaultvalue" => '{"font-family":"Arial","font-size":"14px","color":"#000","width":"100%","height":"40px","line-height":"38px","padding-left":"13px","padding-right":"13px","background-color":"#ffffff","border-top":"1px solid #555","border-bottom":"1px solid #555","border-right":"1px solid #555","border-left":"1px solid #555","border-top-left-radius":"2px","border-top-right-radius":"2px","border-bottom-left-radius":"2px","border-bottom-right-radius":"2px"}'
                ]);
                $addFieldLabel($fieldName, 'FormFieldName', 'textbox', 'bearcms-form-element-field-name-container');
                $addFieldHint($fieldName, 'FormFieldName', 'textbox', 'bearcms-form-element-field-name-container');
                $addFieldContainer($fieldName, 'FormFieldName', 'bearcms-form-element-field-name-container');

                // Email
                $fieldEmail = $groupFormFields->addGroup(__("bearcms-forms.themes.options.FieldEmail"));
                $fieldEmail->addOption($idPrefix . "FormFieldEmailCSS", "css", '', [
                    "cssTypes" => ["cssText", "cssTextShadow", "cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => isset($details['cssOptions']) ? $details['cssOptions'] : [],
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-email-container [data-form-element-type="textbox"] [data-form-element-component="input"]', "display:block;box-sizing:border-box;border:0;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-email-container [data-form-element-type="textbox"] [data-form-element-component="input"]']
                    ],
                    "defaultvalue" => '{"font-family":"Arial","font-size":"14px","color":"#000","width":"100%","height":"40px","line-height":"38px","padding-left":"13px","padding-right":"13px","background-color":"#ffffff","border-top":"1px solid #555","border-bottom":"1px solid #555","border-right":"1px solid #555","border-left":"1px solid #555","border-top-left-radius":"2px","border-top-right-radius":"2px","border-bottom-left-radius":"2px","border-bottom-right-radius":"2px"}'
                ]);
                $addFieldLabel($fieldEmail, 'FormFieldEmail', 'textbox', 'bearcms-form-element-field-email-container');
                $addFieldHint($fieldEmail, 'FormFieldEmail', 'textbox', 'bearcms-form-element-field-email-container');
                $addFieldContainer($fieldEmail, 'FormFieldEmail', 'bearcms-form-element-field-email-container');

                // Phone
                $fieldPhone = $groupFormFields->addGroup(__("bearcms-forms.themes.options.FieldPhone"));
                $fieldPhone->addOption($idPrefix . "FormFieldPhoneCSS", "css", '', [
                    "cssTypes" => ["cssText", "cssTextShadow", "cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => isset($details['cssOptions']) ? $details['cssOptions'] : [],
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-phone-container [data-form-element-type="textbox"] [data-form-element-component="input"]', "display:block;box-sizing:border-box;border:0;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-phone-container [data-form-element-type="textbox"] [data-form-element-component="input"]']
                    ],
                    "defaultvalue" => '{"font-family":"Arial","font-size":"14px","color":"#000","width":"100%","height":"40px","line-height":"38px","padding-left":"13px","padding-right":"13px","background-color":"#ffffff","border-top":"1px solid #555","border-bottom":"1px solid #555","border-right":"1px solid #555","border-left":"1px solid #555","border-top-left-radius":"2px","border-top-right-radius":"2px","border-bottom-left-radius":"2px","border-bottom-right-radius":"2px"}'
                ]);
                $addFieldLabel($fieldPhone, 'FormFieldPhone', 'textbox', 'bearcms-form-element-field-phone-container');
                $addFieldHint($fieldPhone, 'FormFieldPhone', 'textbox', 'bearcms-form-element-field-phone-container');
                $addFieldContainer($fieldPhone, 'FormFieldPhone', 'bearcms-form-element-field-phone-container');

                // Single select opened list
                $fieldOpenedListSingleSelect = $groupFormFields->addGroup(__("bearcms-forms.themes.options.FieldOpenedListSingleSelect"));
                $addFieldLabel($fieldOpenedListSingleSelect, 'FormFieldOpenedListSingleSelect', 'radio-list', 'bearcms-form-element-field-opened-list-single-select-container');
                $addFieldHint($fieldOpenedListSingleSelect, 'FormFieldOpenedListSingleSelect', 'radio-list', 'bearcms-form-element-field-opened-list-single-select-container');
                $fieldOpenedListSingleSelectOption = $fieldOpenedListSingleSelect->addGroup(__("bearcms-forms.themes.options.FieldOpenedListSingleSelectOption"));
                $fieldOpenedListSingleSelectOptionButton = $fieldOpenedListSingleSelectOption->addGroup(__("bearcms-forms.themes.options.FieldOpenedListSingleSelectOptionButton"));
                $fieldOpenedListSingleSelectOptionButton->addOption($idPrefix . "FormFieldOpenedListSingleSelectOptionButtonCSS", "css", '', [
                    "cssTypes" => ["cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => isset($details['cssOptions']) ? $details['cssOptions'] : [],
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-opened-list-single-select-container [data-form-element-type="radio-list"] [data-form-element-component="radio-list-option-input"]', "flex:0 0 auto;align-self:start;appearance:none;-webkit-appearance:none;box-sizing:border-box;margin:0;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-opened-list-single-select-container [data-form-element-type="radio-list"] [data-form-element-component="radio-list-option-input"]']
                    ],
                    "defaultvalue" => '{"width":"40px","height":"40px","background-color":"#ffffff","border-top":"1px solid #555","border-bottom":"1px solid #555","border-right":"1px solid #555","border-left":"1px solid #555","border-top-left-radius":"50%","border-top-right-radius":"50%","border-bottom-left-radius":"50%","border-bottom-right-radius":"50%"}'
                ]);
                $fieldOpenedListSingleSelectOptionButtonChecked = $fieldOpenedListSingleSelectOption->addGroup(__("bearcms-forms.themes.options.FieldOpenedListSingleSelectOptionButtonChecked"));
                $fieldOpenedListSingleSelectOptionButtonChecked->addOption($idPrefix . "FormFieldOpenedListSingleSelectOptionButtonCheckedCSS", "css", '', [
                    "cssTypes" => ["cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => isset($details['cssOptions']) ? $details['cssOptions'] : [],
                    "cssOutput" => [
                        ["selector", $parentSelector . ' .bearcms-form-element-field-opened-list-single-select-container [data-form-element-type="radio-list"] [data-form-element-component="radio-list-option-input"]:checked']
                    ],
                    "defaultvalue" => '{"background-image":"url(addon:bearcms\/forms-addon:assets\/radio.svg)","background-position":"center center","background-repeat":"no-repeat","background-size":"30px 30px"}'
                ]);
                $fieldOpenedListSingleSelectOptionText = $fieldOpenedListSingleSelectOption->addGroup(__("bearcms-forms.themes.options.FieldOpenedListSingleSelectOptionText"));
                $fieldOpenedListSingleSelectOptionText->addOption($idPrefix . "FormFieldOpenedListSingleSelectOptionTextCSS", "css", '', [
                    "cssTypes" => ["cssText", "cssTextShadow", "cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => array_diff(isset($details['cssOptions']) ? $details['cssOptions'] : [], ["*/focusState"]),
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-opened-list-single-select-container [data-form-element-type="radio-list"] [data-form-element-component="radio-list-option-label"]', "align-self:start;display:block;box-sizing:border-box;word-break:break-word;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-opened-list-single-select-container [data-form-element-type="radio-list"] [data-form-element-component="radio-list-option-label"]']
                    ],
                    "defaultvalue" => '{"font-family":"Arial","font-size":"14px","line-height":"24px","color":"#000","padding-top":"8px","padding-left":"10px"}'
                ]);
                $fieldOpenedListSingleSelectOptionTextbox = $fieldOpenedListSingleSelectOption->addGroup(__("bearcms-forms.themes.options.FieldOpenedListSingleSelectOptionTextbox"));
                $fieldOpenedListSingleSelectOptionTextbox->addOption($idPrefix . "FormFieldOpenedListSingleSelectOptionTextboxCSS", "css", '', [
                    "cssTypes" => ["cssText", "cssTextShadow", "cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => isset($details['cssOptions']) ? $details['cssOptions'] : [],
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-opened-list-single-select-container [data-form-element-type="radio-list"] [data-form-element-component="radio-list-option-textbox"]', "align-self:start;display:inline-block;box-sizing:border-box;border:0;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-opened-list-single-select-container [data-form-element-type="radio-list"] [data-form-element-component="radio-list-option-textbox"]']
                    ],
                    "defaultvalue" => '{"font-family":"Arial","font-size":"14px","color":"#000","width":"200px","height":"40px","line-height":"38px","margin-left":"5px","padding-left":"13px","padding-right":"13px","background-color":"#ffffff","border-top":"1px solid #555","border-bottom":"1px solid #555","border-right":"1px solid #555","border-left":"1px solid #555","border-top-left-radius":"2px","border-top-right-radius":"2px","border-bottom-left-radius":"2px","border-bottom-right-radius":"2px"}'
                ]);
                $fieldOpenedListSingleSelectOptionContainer = $fieldOpenedListSingleSelectOption->addGroup(__("bearcms-forms.themes.options.Container"));
                $fieldOpenedListSingleSelectOptionContainer->addOption($idPrefix . "FormFieldOpenedListSingleSelectOptionContainerCSS", "css", '', [
                    "cssTypes" => ["cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => array_diff(isset($details['cssOptions']) ? $details['cssOptions'] : [], ["*/focusState"]),
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-opened-list-single-select-container [data-form-element-type="radio-list"] [data-form-element-component="radio-list-option"] > label', "display:inline-flex;flex-direction:row;"],
                        ["rule", $parentSelector . ' .bearcms-form-element-field-opened-list-single-select-container [data-form-element-type="radio-list"] [data-form-element-component="radio-list-option"]', "display:flex;box-sizing:border-box;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-opened-list-single-select-container [data-form-element-type="radio-list"] [data-form-element-component="radio-list-option"]']
                    ],
                    "defaultvalue" => '{"padding-bottom":"5px"}' // todo except the last one
                ]);
                $addFieldContainer($fieldOpenedListSingleSelect, 'FormFieldOpenedListSingleSelect', 'bearcms-form-element-field-opened-list-single-select-container');

                // Multi select opened list
                $fieldOpenedListMultiSelect = $groupFormFields->addGroup(__("bearcms-forms.themes.options.FieldOpenedListMultiSelect"));
                $addFieldLabel($fieldOpenedListMultiSelect, 'FormFieldOpenedListMultiSelect', 'checkbox-list', 'bearcms-form-element-field-opened-list-multi-select-container');
                $addFieldHint($fieldOpenedListMultiSelect, 'FormFieldOpenedListMultiSelect', 'checkbox-list', 'bearcms-form-element-field-opened-list-multi-select-container');
                $fieldOpenedListMultiSelectOption = $fieldOpenedListMultiSelect->addGroup(__("bearcms-forms.themes.options.FieldOpenedListMultiSelectOption"));
                $fieldOpenedListMultiSelectOptionButton = $fieldOpenedListMultiSelectOption->addGroup(__("bearcms-forms.themes.options.FieldOpenedListMultiSelectOptionButton"));
                $fieldOpenedListMultiSelectOptionButton->addOption($idPrefix . "FormFieldOpenedListMultiSelectOptionButtonCSS", "css", '', [
                    "cssTypes" => ["cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => isset($details['cssOptions']) ? $details['cssOptions'] : [],
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-opened-list-multi-select-container [data-form-element-type="checkbox-list"] [data-form-element-component="checkbox-list-option-input"]', "flex:0 0 auto;align-self:start;appearance:none;-webkit-appearance:none;box-sizing:border-box;margin:0;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-opened-list-multi-select-container [data-form-element-type="checkbox-list"] [data-form-element-component="checkbox-list-option-input"]']
                    ],
                    "defaultvalue" => '{"width":"40px","height":"40px","background-color":"#ffffff","border-top":"1px solid #555","border-bottom":"1px solid #555","border-right":"1px solid #555","border-left":"1px solid #555","border-top-left-radius":"2px","border-top-right-radius":"2px","border-bottom-left-radius":"2px","border-bottom-right-radius":"2px"}'
                ]);
                $fieldOpenedListMultiSelectOptionButtonChecked = $fieldOpenedListMultiSelectOption->addGroup(__("bearcms-forms.themes.options.FieldOpenedListMultiSelectOptionButtonChecked"));
                $fieldOpenedListMultiSelectOptionButtonChecked->addOption($idPrefix . "FormFieldOpenedListMultiSelectOptionButtonCheckedCSS", "css", '', [
                    "cssTypes" => ["cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => isset($details['cssOptions']) ? $details['cssOptions'] : [],
                    "cssOutput" => [
                        ["selector", $parentSelector . ' .bearcms-form-element-field-opened-list-multi-select-container [data-form-element-type="checkbox-list"] [data-form-element-component="checkbox-list-option-input"]:checked']
                    ],
                    "defaultvalue" => '{"background-image":"url(addon:bearcms\/forms-addon:assets\/checkbox.svg)","background-position":"center center","background-repeat":"no-repeat","background-size":"20px 20px"}'
                ]);
                $fieldOpenedListMultiSelectOptionText = $fieldOpenedListMultiSelectOption->addGroup(__("bearcms-forms.themes.options.FieldOpenedListMultiSelectOptionText"));
                $fieldOpenedListMultiSelectOptionText->addOption($idPrefix . "FormFieldOpenedListMultiSelectOptionTextCSS", "css", '', [
                    "cssTypes" => ["cssText", "cssTextShadow", "cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => array_diff(isset($details['cssOptions']) ? $details['cssOptions'] : [], ["*/focusState"]),
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-opened-list-multi-select-container [data-form-element-type="checkbox-list"] [data-form-element-component="checkbox-list-option-label"]', "align-self:start;display:block;box-sizing:border-box;word-break:break-word;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-opened-list-multi-select-container [data-form-element-type="checkbox-list"] [data-form-element-component="checkbox-list-option-label"]']
                    ],
                    "defaultvalue" => '{"font-family":"Arial","font-size":"14px","line-height":"24px","color":"#000","padding-top":"8px","padding-left":"10px"}'
                ]);
                $fieldOpenedListMultiSelectOptionTextbox = $fieldOpenedListMultiSelectOption->addGroup(__("bearcms-forms.themes.options.FieldOpenedListMultiSelectOptionTextbox"));
                $fieldOpenedListMultiSelectOptionTextbox->addOption($idPrefix . "FormFieldOpenedListMultiSelectOptionTextboxCSS", "css", '', [
                    "cssTypes" => ["cssText", "cssTextShadow", "cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => isset($details['cssOptions']) ? $details['cssOptions'] : [],
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-opened-list-multi-select-container [data-form-element-type="checkbox-list"] [data-form-element-component="checkbox-list-option-textbox"]', "align-self:start;display:inline-block;box-sizing:border-box;border:0;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-opened-list-multi-select-container [data-form-element-type="checkbox-list"] [data-form-element-component="checkbox-list-option-textbox"]']
                    ],
                    "defaultvalue" => '{"font-family":"Arial","font-size":"14px","color":"#000","width":"200px","height":"40px","line-height":"38px","margin-left":"5px","padding-left":"13px","padding-right":"13px","background-color":"#ffffff","border-top":"1px solid #555","border-bottom":"1px solid #555","border-right":"1px solid #555","border-left":"1px solid #555","border-top-left-radius":"2px","border-top-right-radius":"2px","border-bottom-left-radius":"2px","border-bottom-right-radius":"2px"}'
                ]);
                $fieldOpenedListMultiSelectOptionContainer = $fieldOpenedListMultiSelectOption->addGroup(__("bearcms-forms.themes.options.Container"));
                $fieldOpenedListMultiSelectOptionContainer->addOption($idPrefix . "FormFieldOpenedListMultiSelectOptionContainerCSS", "css", '', [
                    "cssTypes" => ["cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => array_diff(isset($details['cssOptions']) ? $details['cssOptions'] : [], ["*/focusState"]),
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-opened-list-multi-select-container [data-form-element-type="checkbox-list"] [data-form-element-component="checkbox-list-option"] > label', "display:inline-flex;flex-direction:row;"],
                        ["rule", $parentSelector . ' .bearcms-form-element-field-opened-list-multi-select-container [data-form-element-type="checkbox-list"] [data-form-element-component="checkbox-list-option"]', "display:flex;box-sizing:border-box;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-opened-list-multi-select-container [data-form-element-type="checkbox-list"] [data-form-element-component="checkbox-list-option"]']
                    ],
                    "defaultvalue" => '{"padding-bottom":"5px"}' // todo except the last one
                ]);
                $addFieldContainer($fieldOpenedListMultiSelect, 'FormFieldOpenedListMultiSelect', 'bearcms-form-element-field-opened-list-multi-select-container');

                // Closed list
                $fieldClosedList = $groupFormFields->addGroup(__("bearcms-forms.themes.options.FieldClosedList"));
                $fieldClosedList->addOption($idPrefix . "FormFieldClosedListCSS", "css", '', [
                    "cssTypes" => ["cssText", "cssTextShadow", "cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => isset($details['cssOptions']) ? $details['cssOptions'] : [],
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-closed-list-container [data-form-element-type="select"] [data-form-element-component="select"]', "display:block;appearance:none;-webkit-appearance:none;box-sizing:border-box;border:0;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-closed-list-container [data-form-element-type="select"] [data-form-element-component="select"]']
                    ],
                    "defaultvalue" => '{"font-family":"Arial","font-size":"14px","color":"#000","width":"100%","height":"40px","line-height":"38px","padding-left":"13px","padding-right":"13px","background-color":"#ffffff","border-top":"1px solid #555","border-bottom":"1px solid #555","border-right":"1px solid #555","border-left":"1px solid #555","border-top-left-radius":"2px","border-top-right-radius":"2px","border-bottom-left-radius":"2px","border-bottom-right-radius":"2px"}'
                ]);
                $addFieldLabel($fieldClosedList, 'FormFieldClosedList', 'select', 'bearcms-form-element-field-closed-list-container');
                $addFieldHint($fieldClosedList, 'FormFieldClosedList', 'select', 'bearcms-form-element-field-closed-list-container');
                $addFieldContainer($fieldClosedList, 'FormFieldClosedList', 'bearcms-form-element-field-closed-list-container');

                // Image
                $fieldImage = $groupFormFields->addGroup(__("bearcms-forms.themes.options.FieldImage"));
                $fieldImage->addOption($idPrefix . "FormFieldImageCSS", "css", '', [
                    "cssTypes" => ["cssText", "cssTextShadow", "cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => isset($details['cssOptions']) ? $details['cssOptions'] : [],
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-image-container [data-form-element-component="button"]', "display:block;box-sizing:border-box;border:0;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-image-container [data-form-element-component="button"]']
                    ],
                    "defaultvalue" => '{"font-family":"Arial","font-size":"14px","color":"#000","width":"100%","height":"40px","line-height":"38px","padding-left":"13px","padding-right":"13px","background-color":"#ffffff","border-top":"1px solid #555","border-bottom":"1px solid #555","border-right":"1px solid #555","border-left":"1px solid #555","border-top-left-radius":"2px","border-top-right-radius":"2px","border-bottom-left-radius":"2px","border-bottom-right-radius":"2px"}'
                ]);
                $addFieldLabel($fieldImage, 'FormFieldImage', 'image', 'bearcms-form-element-field-image-container');
                $addFieldHint($fieldImage, 'FormFieldImage', 'image', 'bearcms-form-element-field-image-container');
                $addFieldContainer($fieldImage, 'FormFieldImage', 'bearcms-form-element-field-image-container');

                // File
                $fieldFile = $groupFormFields->addGroup(__("bearcms-forms.themes.options.FieldFile"));
                $fieldFile->addOption($idPrefix . "FormFieldFileCSS", "css", '', [
                    "cssTypes" => ["cssText", "cssTextShadow", "cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => isset($details['cssOptions']) ? $details['cssOptions'] : [],
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-field-file-container [data-form-element-component="button"]', "display:block;box-sizing:border-box;border:0;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-field-file-container [data-form-element-component="button"]']
                    ],
                    "defaultvalue" => '{"font-family":"Arial","font-size":"14px","color":"#000","width":"100%","height":"40px","line-height":"38px","padding-left":"13px","padding-right":"13px","background-color":"#ffffff","border-top":"1px solid #555","border-bottom":"1px solid #555","border-right":"1px solid #555","border-left":"1px solid #555","border-top-left-radius":"2px","border-top-right-radius":"2px","border-bottom-left-radius":"2px","border-bottom-right-radius":"2px"}'
                ]);
                $addFieldLabel($fieldFile, 'FormFieldFile', 'file', 'bearcms-form-element-field-file-container');
                $addFieldHint($fieldFile, 'FormFieldFile', 'file', 'bearcms-form-element-field-file-container');
                $addFieldContainer($fieldFile, 'FormFieldFile', 'bearcms-form-element-field-file-container');

                // Submit button
                $submitButton = $groupForm->addGroup(__("bearcms-forms.themes.options.SubmitButton"));
                $submitButton->addOption($idPrefix . "FormSubmitButtonCSS", "css", '', [
                    "cssTypes" => ["cssText", "cssTextShadow", "cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => isset($details['cssOptions']) ? $details['cssOptions'] : [],
                    "cssOutput" => [
                        ["rule", $parentSelector . ' .bearcms-form-element-submit-button-container [data-form-element-type="submit-button"] [data-form-element-component="button"]', "box-sizing:border-box;cursor:pointer;display:inline-block;text-decoration:none;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100%;"],
                        ["selector", $parentSelector . ' .bearcms-form-element-submit-button-container [data-form-element-type="submit-button"] [data-form-element-component="button"]']
                    ],
                    "defaultvalue" => '{"background-color":"#333333","font-size":"14px","font-family":"Arial","height":"40px","line-height":"40px","padding-left":"13px","padding-right":"13px","color":"#ffffff","border-top-left-radius":"2px","border-top-right-radius":"2px","border-bottom-left-radius":"2px","border-bottom-right-radius":"2px"}'
                ]);
                $submitButtonContainerGroup = $submitButton->addGroup(__("bearcms-forms.themes.options.Container"));
                $submitButtonContainerGroup->addOption($idPrefix . "FormSubmitButtonContainerCSS", "css", '', [
                    "cssTypes" => ["cssPadding", "cssMargin", "cssBorder", "cssRadius", "cssShadow", "cssBackground", "cssSize"],
                    "cssOptions" => array_diff(isset($details['cssOptions']) ? $details['cssOptions'] : [], ["*/focusState"]),
                    "cssOutput" => [
                        ["rule", $parentSelector . " .bearcms-form-element-submit-button-container", "display:block;box-sizing:border-box;"],
                        ["selector", $parentSelector . " .bearcms-form-element-submit-button-container"]
                    ]
                ]);

                //
            };

            // Forms

            \BearCMS\Internal\ServerCommands::add('formsFormsGetList', function (array $data) use ($app) {
                $list = $app->forms->forms->getList();
                $list = \BearCMS\Internal\ServerCommands::applyListModifications($list, $data['modifications']);
                return $list->toArray();
            });

            \BearCMS\Internal\ServerCommands::add('formsFormsGetCount', function (array $data) use ($app) {
                return $app->forms->forms->getList()->count();
            });

            \BearCMS\Internal\ServerCommands::add('formsFormsGet', function (array $data) use ($app) {
                $model = $app->forms->forms->get($data['id']);
                if ($model !== null) {
                    return $model->toArray(is_array($data['properties']) ? ['properties' => $data['properties']] : []);
                }
                return null;
            });

            \BearCMS\Internal\ServerCommands::add('formsFormsSet', function (array $data) use ($app) {
                $model = $app->forms->forms->makeFromArray($data['data']);
                $model->id = $data['id'];
                $app->forms->forms->set($model);
            });

            \BearCMS\Internal\ServerCommands::add('formsFormsAdd', function (array $data) use ($app) {
                $model = $app->forms->forms->makeFromArray($data['data']);
                $app->forms->forms->add($model);
            });

            \BearCMS\Internal\ServerCommands::add('formsFormsDelete', function (array $data) use ($app) {
                $app->forms->forms->delete($data['id']);
            });

            \BearCMS\Internal\ServerCommands::add('formsFormsExists', function (array $data) use ($app) {
                return $app->forms->forms->exists($data['id']);
            });

            // Responses

            \BearCMS\Internal\ServerCommands::add('formsResponsesGetList', function (array $data) use ($app) {
                $list = $app->forms->responses->getList();
                $list = \BearCMS\Internal\ServerCommands::applyListModifications($list, $data['modifications']);
                return $list->toArray();
            });

            \BearCMS\Internal\ServerCommands::add('formsResponsesGetCount', function (array $data) use ($app) {
                return $app->forms->responses->getList()->count();
            });

            \BearCMS\Internal\ServerCommands::add('formsResponsesGet', function (array $data) use ($app) {
                $model = $app->forms->responses->get($data['id']);
                if ($model !== null) {
                    return $model->toArray(is_array($data['properties']) ? ['properties' => $data['properties']] : []);
                }
                return null;
            });

            \BearCMS\Internal\ServerCommands::add('formsResponsesDelete', function (array $data) use ($app) {
                $app->forms->responses->delete($data['id']);
            });

            \BearCMS\Internal\ServerCommands::add('formsResponsesExists', function (array $data) use ($app) {
                return $app->forms->responses->exists($data['id']);
            });

            // Notifications
            $app->tasks
                ->define('bearcms-forms-send-new-response-notification', function ($data) {
                    Utilities::sendNewResponseNotifications($data['responseID']);
                });
        };
    });
