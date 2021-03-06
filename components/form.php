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

$formID = (string)$component->formID;

$formModel = $app->forms->forms->get($formID);

$fields = [];
if ($formModel !== null) {
    foreach ($formModel->fields as $field) {
        if (!isset($field['id']) || !isset($field['type'])) {
            continue;
        }
        $id = $field['id'];
        $type = $field['type'];
        $fieldName = 'form-field-' . $field['id'];
        if (isset($field['required']) && (int)$field['required'] > 0) {
            $form->constraints->setRequired($fieldName);
        }
        if (isset($field['textMinLength'])) {
            $fieldMinLength = (int)$field['textMinLength'];
            if ($fieldMinLength > 0) {
                $form->constraints->setMinLength($fieldName, $fieldMinLength);
            }
        }
        if (isset($field['textMaxLength'])) {
            $fieldMaxLength = (int)$field['textMaxLength'];
            if ($fieldMaxLength > 0) {
                $form->constraints->setMaxLength($fieldName, $fieldMaxLength);
            }
        }
        if ($type === 'name') {
            $form->constraints->setMaxLength($fieldName, 200);
            // todo set alphabetical
        }
        if ($type === 'email') {
            $form->constraints->setEmail($fieldName);
        }
        if ($type === 'phone') {
            $form->constraints->setPhone($fieldName);
        }
        if ($type === 'openedList') {
            $form->constraints->setValidator($fieldName, function ($value) use ($field) {
                if (strlen($value) === 0) {
                    return true;
                }
                $listMultiSelect = isset($field['listMultiSelect']) && (int)$field['listMultiSelect'] > 0;
                $listAddOption = isset($field['listAddOption']) && (int)$field['listAddOption'] > 0;
                $listOptions = isset($field['listOptions']) && is_array($field['listOptions']) ? $field['listOptions'] : [];
                $validValues = [];
                foreach ($listOptions as $listOption) {
                    $validValues[] = md5($listOption);
                }
                if ($listMultiSelect) {
                    $value = json_decode($value, true);
                    if (!is_array($value)) {
                        $value = [];
                    }
                    $notValidValues = array_diff($value, $validValues);
                    if (empty($notValidValues)) {
                        return true;
                    }
                    $notValidValues = array_values($notValidValues);
                    if ($listAddOption) {
                        if (sizeof($notValidValues) === 1) {
                            return true;
                        }
                    }
                } else {
                    if (array_search($value, $validValues) !== false) {
                        return true;
                    }
                    if ($listAddOption) {
                        return true;
                    }
                }
                return false;
            });
        } elseif ($type === 'closedList') {
            $form->constraints->setValidator($fieldName, function ($value) use ($field) {
                if (strlen($value) === 0) {
                    return true;
                }
                $listOptions = isset($field['listOptions']) && is_array($field['listOptions']) ? $field['listOptions'] : [];
                $validValues = [];
                foreach ($listOptions as $listOption) {
                    $validValues[] = md5($listOption);
                }
                if (array_search($value, $validValues) !== false) {
                    return true;
                }
                return false;
            });
        }
        $fields[] = $field;
    }
}

$form->onSubmit = function ($values) use (&$form, $app, $fields, $formID, $formModel) {
    if (Utilities::$disabled) {
        $form->throwError(__('bearcms-forms.form.disabled'));
    }
    $resultJS = '';
    $response = [];
    foreach ($fields as $field) {
        $id = $field['id'];
        $type = $field['type'];
        $value = null;

        $fieldName = 'form-field-' . $id;
        $fieldValue = isset($values[$fieldName]) ? $values[$fieldName] : '';
        if (strlen($fieldValue) > 100000) { // for protection
            $form->throwError(__('bearcms-forms.form.field.valueLengthProtection'));
        }
        if ($type === 'text') {
            $value = $fieldValue;
        } elseif ($type === 'textarea') {
            $value = $fieldValue;
        } elseif ($type === 'name') {
            $value = $fieldValue;
        } elseif ($type === 'email') {
            $value = $fieldValue;
        } elseif ($type === 'phone') {
            $value = $fieldValue;
        } elseif ($type === 'openedList') {
            $listMultiSelect = isset($field['listMultiSelect']) && (int)$field['listMultiSelect'] > 0;
            $listOptions = isset($field['listOptions']) && is_array($field['listOptions']) ? $field['listOptions'] : [];
            $value = $fieldValue;
            $clientValues = [];
            foreach ($listOptions as $listOptionIndex => $listOption) {
                $clientValues[md5($listOption)] = $listOption;
            }
            if ($listMultiSelect) {
                $value = strlen($value) > 0 ? json_decode($value, true) : [];
                if (!is_array($value)) {
                    $value = [];
                }
                foreach ($value as $i => $valuePart) {
                    if (isset($clientValues[$valuePart])) {
                        $value[$i] = $clientValues[$valuePart];
                    }
                }
            } else {
                if (isset($clientValues[$value])) {
                    $value = $clientValues[$value];
                }
            }
        } elseif ($type === 'closedList') {
            $value = $fieldValue;
            $listOptions = isset($field['listOptions']) && is_array($field['listOptions']) ? $field['listOptions'] : [];
            $clientValues = [];
            foreach ($listOptions as $listOptionIndex => $listOption) {
                $clientValues[md5($listOption)] = $listOption;
            }
            if (isset($clientValues[$value])) {
                $value = $clientValues[$value];
            }
        }
        $response[] = [
            'id' => $id,
            'type' => $type,
            'value' => $value,
            'name' => isset($field['name']) ? $field['name'] : '',
        ];
    }
    $responseModel = $app->forms->responses->make();
    $responseModel->formID = $formID;
    $responseModel->date = new DateTime();
    $responseModel->value = $response;
    $responseID = $app->forms->responses->add($responseModel);

    $onSubmit = $formModel->onSubmit;
    if ($onSubmit === 'message') {
        $resultJS = 'alert(' . json_encode($formModel->onSubmitMessage) . ');';
    } elseif ($onSubmit === 'redirect') {
        $resultJS = 'window.location.assign(' . json_encode($formModel->onSubmitRedirectURL) . ');';
    } else {
        $resultJS = 'alert("Successfully submitted!");';
    }

    Utilities::addNewResponseNotificationsTask($formModel, $responseID);

    return $resultJS;
};

echo '<html>';
echo '<body>';

if (!empty($fields)) {

    $content = '';

    echo '<form onsubmitsuccess="this.reset();(new Function(event.result))();">';

    foreach ($fields as $field) {
        $id = $field['id'];
        $type = $field['type'];
        $name = isset($field['name']) ? $field['name'] : '';

        $fieldName = 'form-field-' . $id;

        $fieldAttributes = 'name="' . htmlentities($fieldName) . '" label="' . htmlentities($name) . '"';
        if (isset($field['hint']) && strlen($field['hint']) > 0) {
            $fieldAttributes .= ' hint="' . htmlentities($field['hint']) . '"';
        }
        if ($type === 'text') {
            echo '<div class="bearcms-form-element-field-text-container">';
            echo '<form-element-textbox ' . $fieldAttributes . '/>';
            echo '</div>';
        } elseif ($type === 'textarea') {
            echo '<div class="bearcms-form-element-field-textarea-container">';
            echo '<form-element-textarea ' . $fieldAttributes . '/>';
            echo '</div>';
        } elseif ($type === 'name') {
            echo '<div class="bearcms-form-element-field-name-container">';
            echo '<form-element-textbox ' . $fieldAttributes . '/>';
            echo '</div>';
        } elseif ($type === 'email') {
            echo '<div class="bearcms-form-element-field-email-container">';
            echo '<form-element-textbox ' . $fieldAttributes . '/>';
            echo '</div>';
        } elseif ($type === 'phone') {
            echo '<div class="bearcms-form-element-field-phone-container">';
            echo '<form-element-textbox ' . $fieldAttributes . '/>';
            echo '</div>';
        } elseif ($type === 'openedList') {
            $listMultiSelect = isset($field['listMultiSelect']) && (int)$field['listMultiSelect'] > 0;
            $listAddOption = isset($field['listAddOption']) && (int)$field['listAddOption'] > 0;
            $listOptions = isset($field['listOptions']) && is_array($field['listOptions']) ? $field['listOptions'] : [];
            $tagName = $listMultiSelect ? 'form-element-checkbox-list' : 'form-element-radio-list';
            echo '<div class="' . ($listMultiSelect ? 'bearcms-form-element-field-opened-list-multi-select-container' : 'bearcms-form-element-field-opened-list-single-select-container') . '">';
            echo '<' . $tagName . ' ' . $fieldAttributes . '>';
            foreach ($listOptions as $listOption) {
                echo '<option value="' . md5($listOption) . '">' . htmlspecialchars($listOption) . '</option>';
            }
            if ($listAddOption) {
                echo '<option type="textbox" placeholder="' . htmlentities(__('bearcms-forms.form.field.otherListOption')) . '"></option>';
            }
            echo '</' . $tagName . '>';
            echo '</div>';
        } elseif ($type === 'closedList') {
            echo '<div class="bearcms-form-element-field-closed-list-container">';
            echo '<form-element-select ' . $fieldAttributes . '>';
            if (isset($field['listOptions']) && is_array($field['listOptions'])) {
                echo '<option value=""></option>';
                foreach ($field['listOptions'] as $listOption) {
                    if (is_string($listOption)) {
                        echo '<option value="' . htmlentities(md5($listOption)) . '">' . htmlspecialchars($listOption) . '</option>';
                    }
                }
            }
            echo '</form-element-select>';
            echo '</div>';
        }
    }

    $submitButtonText = (string)$formModel->submitButtonText;
    echo '<div class="bearcms-form-element-submit-button-container">';
    echo '<form-element-submit-button text="' . htmlentities((isset($submitButtonText[0]) ? $submitButtonText : __('bearcms-forms.form.field.submitButtonText'))) . '" waitingText="' . htmlentities(__('bearcms-forms.form.field.submitButtonWatingText')) . '"/>';
    echo '</div>';

    echo '</form>';
}

echo '</body>';
echo '</html>';
