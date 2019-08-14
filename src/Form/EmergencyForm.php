<?php

namespace Drupal\iq_emergency\Form;


use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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
        $form['admin_theme']['admin_theme'] = [
            '#type' => 'select',
            '#options' => [0 => $this->t('Default theme')] + $theme_options,
            '#title' => $this->t('Administration theme'),
            '#description' => $this->t('Choose "Default theme" to always use the same theme as the rest of the site.'),
            '#default_value' => $this->config('system.theme')->get('admin'),
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
