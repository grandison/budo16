<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Subscription.php 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_Form_Admin_Signup_Subscription extends Engine_Form
{
  public function init()
  {
    // Get step and step number
    $stepTable = Engine_Api::_()->getDbtable('signup', 'user');
    $stepSelect = $stepTable->select()->where('class = ?', str_replace('_Form_Admin_', '_Plugin_', get_class($this)));
    $step = $stepTable->fetchRow($stepSelect);
    $stepNumber = 1 + $stepTable->select()
      ->from($stepTable, new Zend_Db_Expr('COUNT(signup_id)'))
      ->where('`order` < ?', $step->order)
      ->query()
      ->fetchColumn()
      ;
    $stepString = $this->getView()->translate('Step %1$s', $stepNumber);
    $this->setDisableTranslator(true);


    // Custom
    $this->setTitle($this->getView()->translate('%1$s: Choose Subscription', $stepString));


    // Element: enable
    $this->addElement('Radio', 'enable', array(
      'label' => 'Choose Subscription Plan',
      'description' => 'Do you want your users to be able to choose a ' .
        'subscription plan upon signup?',
      'multiOptions' => array(
        '1' => 'Yes, give users the option to choose upon signup.',
        '0' => 'No, do not allow users to choose upon signup.',
      ),
    ));

    // Element: submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));

    // Populate
    $this->populate(array(
      'enable' => $step->enable,
    ));
  }
}