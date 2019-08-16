<?php

namespace Drupal\iq_emergency\Form;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkBase;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\node\Entity\Node;
use Drupal\system\Entity\Menu;

/**
 * Class EmergencyForm.
 *
 * @package Drupal\iq_emergency\Form
 */
class EmergencyForm extends ConfigFormBase
{

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'iq_emergency_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('iq_emergency.settings');
        $emergency_mode = $config->get('emergency_mode');
        $form['emergency_mode'] = [
            '#type' => 'checkbox',
            '#title' => 'Emergency mode',
            '#description' => 'Check this box if the website is in emergency mode.',
            '#default_value' => $emergency_mode
        ];
        // @todo Get autocomplete for pages instead of select.
        $nids = \Drupal::entityQuery('node')->condition('type','page')->execute();
        $nodes =  Node::loadMultiple($nids);
        $options = [];
        /** @var Node $node */
        foreach ($nodes as $node) {
            $options[$node->id()] = $node->label();
        }


        $form['emergency_page']  = [
            '#type' => 'entity_autocomplete',
            '#title' => 'Emergency page',
            '#target_type' => 'node',
            '#selection_settings' => ['target_bundles' =>[ 'page' => 'page']],
            '#validate_reference' => FALSE,
            '#maxlength' => 1024,
        ];

        $form['actions']['#type'] = 'actions';
        $form['actions']['submit'] = [
            '#type' => 'submit',
            '#value' => $this->t('Save'),
            '#button_type' => 'primary',
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {

        $this->config('iq_emergency.settings')
            ->set('emergency_mode', $form_state->getValue('emergency_mode'))
            ->set('emergency_page', $form_state->getValue('emergency_page'))
            ->save();

        parent::submitForm($form, $form_state);
    }

    /**
     * Get Editable config names.
     *
     * @inheritDoc
     */
    protected function getEditableConfigNames()
    {
        return ['iq_emergency.settings'];
    }

}
