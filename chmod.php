<?php
function chmod_R($path, $perm) {
 $handle = opendir($path);
 while ( false !== ($file = readdir($handle)) ) {
 if ( ($file !== "..") ) {
 @chmod($path . "/" . $file, $perm);
 if ( !is_file($path."/".$file) && ($file !== ".") )
 chmod_R($path . "/" . $file, $perm);
 }
 }
 closedir($handle);
}

chmod_R(".", 0777);
echo 1;
?>