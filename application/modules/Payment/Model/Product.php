<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: Product.php 8221 2011-01-15 00:24:02Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 */
class Payment_Model_Product extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;

  protected $_modifiedTriggers = false;

  protected $_extension;

  public function getExtension()
  {
    if( empty($this->extension_type) || empty($this->extension_id) ) {
      return false;
    }
    if( null === $this->_extension ) {
      $this->_extension = Engine_Api::_()->getItem($this->extension_type, $this->extension_id);
    }
    return $this->_extension;
  }

  protected function _postInsert()
  {
    parent::_postInsert();

    // Update sku
    $this->_updateSku();
  }

  protected function _postUpdate()
  {
    parent::_postUpdate();

    // Update sku
    if( empty($this->sku) || !empty($this->_modifiedFields['product_id']) ) {
      $this->_updateSku();
    }
  }

  protected function _updateSku()
  {
    // Generate sku and ensure unique
    $secret = Engine_Api::_()->getApi('settings', 'core')->payment_secret;
    $i = 0;
    $l = 8;
    $sku = null;
    
    do {
      //$padLen = strlen(sprintf('%d', base_convert(str_pad('', $l, 'f'), 16, 10)));
      $sku = str_pad(sprintf('%d', $this->product_id), 8, '0', STR_PAD_LEFT)
          . '^' . $secret . '^' . 'product';
      $sku = base_convert(substr(md5($sku), $i, $l), 16, 10);
      //$sku = sprintf('%d', base_convert(substr(md5($sku), $i, $l), 16, 10));
      //$sku = str_pad($sku, $padLen, '0', STR_PAD_LEFT);
      
      $pId = $this->getTable()
        ->select()
        ->from($this->getTable(), 'product_id')
        ->where('sku = ?', $sku)
        ->limit(1)
        ->query()
        ->fetchColumn();

      if( $pId ) {
        if( $i < 8 ) {
          $i++;
        } else {
          $i = 0;
          $l++;
        }
        $sku = null;
      }

    } while( !$sku );

    // Update with sku
    $this->_data['sku'] = $sku;
    $this->getTable()->update(array(
      'sku' => $sku,
    ), array(
      'product_id = ?' => $this->product_id,
    ));
  }
}