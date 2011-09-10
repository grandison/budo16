<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: fields.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     John
 */
?>
<?php if( $this->valueCount <= 0 ): ?>
  <script type="text/javascript">
    $('tab_link_user_profile_fields').style.visibility = 'hidden';
    $('tab_link_user_profile_fields').style.display = 'none';
  </script>
<?php return;
endif; ?>

<?php
if( count($this->fields) > 0 ):
  $headingActive = false;
  $lastHeadingTitle = $this->translate('Missing heading');
  $lastContents = '';
  foreach( $this->fields as $index => $info ):

    // Start new heading
    if( $info['heading'] ):
      if( !empty($lastContents) ): ?>
        <div class="profile_fields">
          <h4><?php echo $lastHeadingTitle; ?></h4>
          <ul>
            <?php echo $lastContents; ?>
          </ul>
        </div><?php
        $lastContents = '';
      endif;
      $lastHeadingTitle = $info['label'];
    endif;

    // Do fields
    if( !$info['heading'] ):
      if    ($info['alias'] == 'first_name'):
      elseif($info['alias'] == 'last_name'):
        // @todo displayname doesn't always get set; debug further.
        if (!empty($this->user->displayname)): 
         $lastContents .= '<li>Name: '.$this->user->displayname.'</li>';
        endif;
      elseif( ($info['type'] == 'birthdate') || ($info['type'] == 'date') ):
        $date_formatted = $this->date($info['value']);
        if ($date_formatted):
          $lastContents .= "<li>{$info['label']}: $date_formatted</li>"; endif;
      elseif( strlen($info['value']) > 120 ):
        $lastContents .= '<li><b>'.$info['label'].'</b><br /> '.$this->string()->chunk($info['value']).'</li>';
      else:
        $lastContents .= '<li>'.$info['label'].': '.$this->string()->chunk($info['value']).'</li>';
      endif;
    endif;

  endforeach;

  if( !empty($lastContents) ): ?>
    <div class="profile_fields">
      <h4><?php echo $lastHeadingTitle; ?></h4>
      <ul>
        <?php echo $lastContents; ?>
      </ul>
    </div><?php
    $lastContents = '';
  endif;


else: // This will force the container to be generated if no fields 

  echo '&nbsp;';

endif;