
UPDATE
  `engine4_authorization_permissions`
SET
  `params` = CONCAT(`params`, ', blockquote')
WHERE
  `type` = 'forum' &&
  `name` = 'commentHtml' &&
  `value` = 3 &&
  `params` != '' &&
  `params` NOT LIKE '%blockquote%'
  ;

-- Corrects manage tab to default to first tab in admin
UPDATE `engine4_core_menuitems` SET `params` = '{"route":"admin_default","module":"forum","controller":"manage"}' WHERE `name` = 'core_admin_main_plugins_forum' LIMIT 1;
