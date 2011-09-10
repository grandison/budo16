<?php

$zip = new ZipArchive;

if ($zip->open('orig-social.zip') === TRUE) {

    $zip->extractTo('.');

    $zip->close();

    echo 'ok';

} else {

    echo 'failed';

}

?>

