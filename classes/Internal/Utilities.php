<?php

/*
 * Forms addon for Bear CMS
 * https://github.com/bearcms/forms-addon
 * Copyright (c) Amplilabs Ltd.
 * Free to use under the MIT license.
 */

namespace BearCMS\Forms\Internal;

use BearCMS\Forms\Models\Forms\Form;
use BearFramework\App;

/**
 * @internal
 */
class Utilities
{

    /**
     * 
     */
    static public $notificationEmailsSenderEmail = null;

    /**
     * 
     * @var boolean
     */
    static public $disabled = false;

    /**
     * 
     * @param Form $form
     * @param string $responseID
     * @return void
     */
    static function addNewResponseNotificationsTask(Form $form, string $responseID): void
    {
        $app = App::get();
        $notifyEmails = (string)$form->notifyEmails;
        if (mb_strlen($notifyEmails) > 0 || \BearCMS\Internal\Config::hasFeature('NOTIFICATIONS')) {
            $app->tasks->add('bearcms-forms-send-new-response-notification', [
                'responseID' => $responseID
            ]);
        }
    }

    /**
     * 
     * @param string $responseID
     * @return void
     */
    static function sendNewResponseNotifications(string $responseID): void
    {
        $app = App::get();
        $response = $app->forms->responses->get($responseID);
        if ($response !== null) {
            $form = $app->forms->forms->get($response->formID);
            if ($form !== null) {
                \BearCMS\Internal\Localization::setAdminLocale();
                if (\BearCMS\Internal\Config::hasFeature('NOTIFICATIONS')) {
                    $notification = $app->notifications->make(__('bearcms-forms.emails.notification.title'), $form->name . ":\n" . $response->valueSummary);
                    $notification->clickURL = $app->urls->get() . '#admin-open-forms';
                    $notification->type = 'bearcms-form-response-new';
                    $app->notifications->send('bearcms-user-administrator', $notification);
                }
                $notifyEmails = (string)$form->notifyEmails;
                if (mb_strlen($notifyEmails) > 0) {
                    $senderEmail = Utilities::$notificationEmailsSenderEmail;
                    if ($senderEmail !== null) {
                        $formName = $response->formName;
                        $recipients = explode(';', str_replace(',', ';', $notifyEmails));
                        foreach ($recipients as $recipient) {
                            $recipient = trim($recipient);
                            if (mb_strlen($recipient) === 0) {
                                continue;
                            }
                            $email = $app->emails->make();
                            $email->sender->email = $senderEmail;
                            $email->recipients->add($recipient);
                            $subject = (string)$form->notifyEmailsSubject;
                            $email->subject = isset($subject[0]) ? $subject : sprintf(__('bearcms-forms.emails.notifyEmail.subject'), $formName);

                            if ($form->notifyEmailsAddReplyTo) {
                                foreach ($response->value as $value) {
                                    if (isset($value['value'], $value['type']) && $value['type'] === 'email' && strlen($value['value']) > 0) {
                                        if (filter_var($value['value'], FILTER_VALIDATE_EMAIL)) {
                                            $email->replyToRecipients->add($value['value']);
                                            break;
                                        }
                                    }
                                }
                            }

                            $html = '<html><body>';

                            // $html .= '<strong>' . __('bearcms-forms.emails.notifyEmail.Form') . ':</strong><br>';
                            // $html .= htmlspecialchars($formName);
                            // $html .= '<br><br>';

                            $html .= '<strong>' . __('bearcms-forms.emails.notifyEmail.Date') . '</strong>:<br>' . $app->localization->formatDate($response->date, ['date', 'time', 'year']);

                            if (isset($response->valueDetails)) {
                                //$html .= '<strong>' . __('bearcms-forms.emails.notifyEmail.Response') . ':</strong><br>';
                                foreach ($response->valueDetails as $value) {
                                    $html .= '<br><br><strong>' . htmlspecialchars($value['name']) . '</strong>:<br>' . nl2br(htmlspecialchars($value['value']));
                                }
                            }

                            $html .= '</body></html>';

                            $email->content->add($html, 'text/html');
                            $email->content->add(strip_tags($html), 'text/plain', 'utf-8');
                            $app->emails->send($email);
                        }
                    }
                }
                \BearCMS\Internal\Localization::restoreLocale();
            }
        }
    }
}
