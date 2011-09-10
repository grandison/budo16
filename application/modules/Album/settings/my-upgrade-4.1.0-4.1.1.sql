
UPDATE `engine4_storage_files`
  RIGHT JOIN `engine4_album_photos`
  ON `engine4_album_photos`.`file_id` = `engine4_storage_files`.`file_id`
  SET
    `parent_type` = 'album_photo',
    `parent_id` = `engine4_album_photos`.`photo_id`,
    `user_id` = `engine4_album_photos`.`owner_id`
  WHERE
    `engine4_storage_files`.`file_id` IS NOT NULL ;

UPDATE `engine4_storage_files`
  LEFT JOIN `engine4_storage_files` as `files2`
  ON `engine4_storage_files`.`parent_file_id` = `files2`.`file_id`
  SET
    `engine4_storage_files`.`parent_type` = `files2`.`parent_type`,
    `engine4_storage_files`.`parent_id` = `files2`.`parent_id`,
    `engine4_storage_files`.`user_id` = `files2`.`user_id`
  WHERE
    `engine4_storage_files`.`parent_file_id` IS NOT NULL ;
