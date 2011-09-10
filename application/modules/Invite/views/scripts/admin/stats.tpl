<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2010 Webligo Developments
 * @license    http://www.socialengine.net/license/
 * @version    $Id: stats.tpl 7244 2010-09-01 01:49:53Z john $
 * @author     Steve
 */
?>

<style type="text/css">
h3 {
  margin: 20px 0 0 0;
}

table {
  border: 1px solid #000;
  width: 50%;
}

th {
  font-weight: bold;
  background: #e3e3e3;
  padding: 3px;
  text-align: right;
}

tr td {
  border-top: 1px solid #000;
  padding: 3px;
  text-align: right;
}
</style>
<h1><?php echo $this->translate("Inviters") ?></h1>
<h2><?php echo $this->translate("Movers and Shakers") ?></h2>
<h3><?php echo $this->translate("Top 10 Inviters") ?><br /><small><em><?php echo $this->translate("Most invites sent out") ?></em></small></h3>
<table cellpadding="0" cellspacing="0" border="1" width="100%">
  <thead>
    <tr>
      <th><?php echo $this->translate("Username") ?></th>
      <th><?php echo $this->translate("Invites") ?></th>
      <th><?php echo $this->translate("Recruits") ?></th>
      <th><?php echo $this->translate("Conversion Ratio") ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($this->top_inviters as $invitee): ?>
    <tr><td><?php echo $invitee['username'] ?></td>
        <td><?php echo $invitee['invited'] ?></td>
        <td><?php echo $invitee['recruited'] ?></td>
        <td><?php echo $invitee['invited'] ? round(100*($invitee['recruited']/$invitee['invited'])) : 0 ?> %</td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h3><?php echo $this->translate("Top 10 Recruiters") ?><br /><small><em><?php echo $this->translate("Most members that joined from their invites") ?></em></small></h3>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
  <thead>
    <tr>
      <th><?php echo $this->translate("Username") ?></th>
      <th><?php echo $this->translate("Invites") ?></th>
      <th><?php echo $this->translate("Recruits") ?></th>
      <th><?php echo $this->translate("Conversion Ratio") ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($this->top_recruiters as $recruiters): ?>
    <tr><td><?php echo $recruiters['username'] ?></td>
        <td><?php echo $recruiters['invited'] ?></td>
        <td><?php echo $recruiters['recruited'] ?></td>
        <td><?php echo ($recruiters['invited'] ? round(100*($recruiters['recruited']/$recruiters['invited'])) : 0) ?> %</td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
