<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: php.tpl 7250 2010-09-01 07:42:35Z john $
 * @author     John
 */
?>

<h2>
  <?php echo $this->translate('Server Information') ?>
</h2>

<br />

<style type="text/css">
  #phpinfo td, #phpinfo th, #phpinfo h1, #phpinfo h2 {font-family: sans-serif;}
  #phpinfo pre {margin: 0px; font-family: monospace;}
  #phpinfo a:link {color: #000099; text-decoration: none; background-color: #ffffff;}
  #phpinfo a:hover {text-decoration: underline;}
  #phpinfo table {border-collapse: collapse;}
  #phpinfo .center {text-align: center;}
  #phpinfo .center table { margin-left: auto; margin-right: auto; text-align: left;}
  #phpinfo .center th { text-align: center !important; }
  #phpinfo td, th { padding: 3px; border: 1px solid #ddd; font-size: 75%; vertical-align: baseline;}
  #phpinfo h1 {font-size: 150%;}
  #phpinfo h2 {font-size: 125%;}
  #phpinfo .p {text-align: left;}
  #phpinfo .e {background-color: #E9F4FA; min-width: 150px; font-weight: bold; color: #000000;}
  #phpinfo .h {background-color: #739eb9; font-weight: bold; color: #fff;}
  #phpinfo .h h1 {font-weight: bold; color: #fff;}
  #phpinfo .v {background-color: #F4F4F4; color: #000000;}
  #phpinfo .vr {background-color: #cccccc; text-align: right; color: #000000;}
  #phpinfo img {float: right;}
  #phpinfo .v img {border: 1px solid #444;}
  #phpinfo hr {width: 600px; background-color: #cccccc; border: 0px; height: 1px; color: #000000;}
</style>

<div id="phpinfo">
  <?php echo $this->content; ?>
</div>