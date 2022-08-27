<?php
/**
 * @file
 * Contains \Drupal\custom_event\Form\EventForm.
 */
namespace Drupal\custom_event\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\file\Entity\File;
class EventForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_event_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['event_title'] = [
      '#type' => 'textfield',
      '#title' => t('Event Title'),
      '#required' => TRUE,
    ];
    $form['event_description'] = [
        '#type' => 'textarea',
        '#title' => t('Event Description'),
        '#required' => TRUE,
    ];
    $form['event_banner_image'] = [
        '#type' => 'file',
        '#title' => t('Event Banner Image'),
        '#required' => TRUE,
    ];
      $form['event_banner_image'] = [
        '#type' => 'managed_file',
        '#required' => TRUE,
        '#title' => t('Event Banner Image'),
        '#name' => 'event_banner_image',
        '#description' => t('Banner Image for this event'),
        '#upload_validators' => [
          'file_validate_extensions' => ['gif png jpg jpeg']
          ],
        '#upload_location' => 'public://'
      ];
    $form['event_banner_description'] = [
       '#type' => 'textarea',
        '#title' => t('Event Banner Description'),
        '#required' => TRUE,
    ];
    $form['event_start_date'] = [
      '#type' => 'date',
      '#title' => t('Start Date'),
      '#required' => TRUE,
    ];

    $form['event_end_date'] = [
        '#type' => 'date',
        '#title' => t('End Date'),
        '#required' => TRUE,
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {

     if ($form_state->getValue('event_title')=='') {
      $form_state->setErrorByName('event_title', $this->t('Please enter event title'));
    } 
    if ($form_state->getValue('event_description')=='') {
      $form_state->setErrorByName('event_description', $this->t('Please enter event description'));
    } 
    if ($form_state->getValue('event_start_date')=='') {
      $form_state->setErrorByName('event_title', $this->t('Please enter event start date'));
    } 
    if ($form_state->getValue('event_end_date')=='') {
      $form_state->setErrorByName('event_end_date', $this->t('Please enter event end date'));
    } 
    if($form_state->getValue('event_start_date')>$form_state->getValue('event_end_date'))
    {
      $form_state->setErrorByName('event_end_date', $this->t('Event end date must be grater that event start date'));
    }

  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // drupal_set_message($this->t('@can_name ,Your application is being submitted!', array('@can_name' => $form_state->getValue('candidate_name'))));
    /* foreach ($form_state->getValues() as $key => $value) {
       drupal_set_message($key . ': ' . $value);
     } */

       /*  $file_data = $form_state->getValue('event_banner_image');
		 $file = \Drupal\file\Entity\File::load($file_data[0] );
		 $file_name = $file->getFilename();
		 $file->setPermanent();
		 $file->save();

         var_dump($file);*/

        
         $fid = $form_state->getValue('event_banner_image')[0];
         $file = File::load($fid);
         $file->setPermanent();
         $file->save();

     $paragraph = Paragraph::create([
        'type' => 'banner',   // paragraph type machine name
        'field_banner_description' => [   // paragraph's field machine name
            'value' => $form_state->getValue('event_description'), // body field value
            'format' => 'full_html',         // body text format
        ],
        'field_banner_image'=> ['target_id' => $fid,'alt'=>"Banner Image"]
    ]);


    $paragraph->save();

    $currentUserId = \Drupal::currentUser()->id();
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    
     $node = Node::create([
        'type' => 'event',
        'title' => $form_state->getValue('event_title'),
        'body'  => $form_state->getValue('event_description'),
        'field_banner'  => [
                [
                'target_id'          => $paragraph->id(),
                'target_revision_id' => $paragraph->getRevisionId(),
                ]
            ],
        'langcode' => $currentUserId,
        'uid' => $currentUserId,
        'status' => 1,
        'field_event_date' => [
            'value'     =>    $form_state->getValue('event_start_date'),
            'end_value' => $form_state->getValue('event_end_date')
          ],
      ]);
      $node->save();

      \Drupal::messenger()->addMessage('Event added successfully', 'status');

     // \Drupal::messenger()->addStatus(t('Successful message.'));
    }


}