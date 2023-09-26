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

$getFileFieldMaxSize = function (array $field) use ($app): int {
    if (isset($field['maxSize']) && strlen($field['maxSize']) > 0) {
        return (int)$app->localization->formatBytes($field['maxSize'], ['bytes']);
    }
    $max = \BearCMS\Internal\Data\Uploads::getMaxUploadSize();
    if ($max !== null) {
        return $max;
    }
    return 1 * 1024 * 1024;
};

$getFileAllowedExtensions = function (array $field): array {
    if (isset($field['allowedExtensions']) && is_array($field['allowedExtensions'])) {
        return $field['allowedExtensions'];
    }
    return [];
};

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
                if (mb_strlen($value) === 0) {
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
                if (mb_strlen($value) === 0) {
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

$form->onSubmit = function ($values) use (&$form, $app, $fields, $formID, $formModel, $getFileFieldMaxSize, $getFileAllowedExtensions) {
    if (Utilities::$disabled) {
        $form->throwError(__('bearcms-forms.form.disabled'));
    }

    if (!$app->rateLimiter->logIP('bearcms-form', ['2/m', '20/h'])) {
        $form->throwError(__('bearcms-forms.form.tooMany'));
    }

    $filesToCreate = [];

    $resultJS = '';
    $response = [];
    foreach ($fields as $field) {
        $id = $field['id'];
        $type = $field['type'];
        $value = null;

        $fieldName = 'form-field-' . $id;
        $fieldValue = isset($values[$fieldName]) ? $values[$fieldName] : '';
        if (mb_strlen($fieldValue) > 100000) { // for protection
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
                $value = mb_strlen($value) > 0 ? json_decode($value, true) : [];
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
        } elseif ($type === 'image' || $type === 'file') {
            $files = json_decode($fieldValue, true);
            $value = [];
            if (is_array($files)) {
                foreach ($files as $file) {
                    if (is_file($file['filename'])) {
                        $tempFilename = $file['filename'];
                        $tempFileSize = filesize($tempFilename);
                        $extension = strtolower(pathinfo($file['value'], PATHINFO_EXTENSION));
                        $allowedExtensions = $getFileAllowedExtensions($field);
                        if (!empty($allowedExtensions) && array_search($extension, $allowedExtensions) === false) {
                            $form->throwElementError($fieldName, __('bearcms-forms.form.fileInvalidExtension'));
                        }
                        if ($tempFileSize > \BearCMS\Internal\Data\Uploads::getUploadsFreeSpace()) {
                            $form->throwElementError($fieldName, __('bearcms-forms.form.cantUploadNow'));
                        }
                        $maxSize = $getFileFieldMaxSize($field);
                        if ($tempFileSize > $maxSize) {
                            $form->throwElementError($fieldName, sprintf(__('bearcms-forms.form.fileTooBig'), $app->localization->formatBytes($maxSize)));
                        }
                        $newBasename = 'file' . (sizeof($filesToCreate) + 1) . ($extension !== '' ? '.' . $extension : '');
                        $filesToCreate[$tempFilename] = $newBasename;
                        $value[] = $newBasename;
                    } else {
                        $form->throwElementError($fieldName, __('bearcms-forms.form.cantUploadNow'));
                    }
                }
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

    foreach ($filesToCreate as $tempFilename => $newBasename) {
        $app->forms->responses->addFile($responseID, $newBasename, $tempFilename);
        unlink($tempFilename);
    }

    $onSubmit = $formModel->onSubmit;
    if ($onSubmit === 'message') {
        $resultJS = 'alert(' . json_encode($formModel->onSubmitMessage, JSON_THROW_ON_ERROR) . ');';
    } elseif ($onSubmit === 'redirect') {
        $resultJS = 'window.location.assign(' . json_encode($formModel->onSubmitRedirectURL, JSON_THROW_ON_ERROR) . ');';
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
        if (isset($field['hint']) && mb_strlen($field['hint']) > 0) {
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
            echo '<form-element-textbox ' . $fieldAttributes . ' inputType="email"/>';
            echo '</div>';
        } elseif ($type === 'phone') {
            echo '<div class="bearcms-form-element-field-phone-container">';
            echo '<form-element-textbox ' . $fieldAttributes . ' inputType="tel"/>';
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
        } elseif ($type === 'image') {
            echo '<div class="bearcms-form-element-field-image-container">';
            echo '<form-element-image ' . $fieldAttributes . ' maxSize="' . $getFileFieldMaxSize($field) . '"/>';
            echo '</div>';
        } elseif ($type === 'file') {
            echo '<div class="bearcms-form-element-field-file-container">';
            echo '<form-element-file ' . $fieldAttributes . ' maxSize="' . $getFileFieldMaxSize($field) . '" accept="' . join(',', $getFileAllowedExtensions($field)) . '"/>';
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
