
/* This query was removed for changes in 4.1.0 */
/*
UPDATE `engine4_core_tasks`
SET `module` = 'video'
WHERE `plugin` = 'Video_Plugin_Task_Encode';
*/

/* This query was removed for changes in 4.1.0 */
/*
UPDATE `engine4_core_tasks`
SET `priority` = 90
WHERE `plugin` = 'Video_Plugin_Task_Encode' ;
*/

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`) VALUES
('video_processed_failed', 'video', 'Your {item:$object:video} has failed to process.', 0, '');

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('notify_video_processed_failed', 'video', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[sender_title],[sender_link],[sender_photo],[object_title],[object_link],[object_photo],[object_description]');
