<?php

namespace Drupal\custom_event\Commands;

use Drush\Commands\DrushCommands;

class CustomDrushCommand extends DrushCommands {

    /**
     * Drush command that displays the given text.
     *
     * @param string $eventCount
     *   Argument with message to be displayed.
     * @command event_status_update_commands:updateEvent
     * @aliases event-status-update  custom-event-update
     * @usage event_status_update_commands:eventCount
     */
    public function updateEvent($eventCount =10) {
      $statusMessage=$eventCount." events updated successfully";

        $events = \Drupal::database()->select('event_notification_tracker', 'ent')
        ->fields('ent', array('id', 'nid'))
        ->condition('status',0)
        ->execute()->fetchAllAssoc('id');
        
        foreach ($events as $row => $event) {
          $currEvent = \Drupal::entityTypeManager()->getStorage('node')->load($event->nid);
          $authorId = $currEvent->getOwner()->id();
          $authordetails = \Drupal\user\Entity\User::load($authorId);
          $userEmail =  $authordetails->get('mail')->value;
         // $statusMessage.= $userEmail;

          $updateEvent=\Drupal::database()->update('event_notification_tracker')
                        ->fields(array('status' => 1))
                        ->condition('id',$event->id)
                        ->execute();

          if($updateEvent)
          {
            //Sending mail to author
            $mailManager = \Drupal::service('plugin.manager.mail');
            $module = 'custom_event';
            $key = 'event_update';
            $to = $userEmail;
            $params['message'] = "Your event is id ".$event->nid. " have been updated";
            $params['node_title'] = $currEvent->get('title')->value;
            $replyTo="noreply@netsolution.com";
            $langcode = \Drupal::currentUser()->getPreferredLangcode();
            $send = true;
            $result = $mailManager->mail($module, $key, $to, $langcode, $params,$replyTo, $send);
  
          }
          
         
        }


      $this->output()->writeln($statusMessage);
    }

  }
