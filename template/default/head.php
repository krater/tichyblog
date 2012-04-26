<?php

$ent=new entry();
$ent->ReadFromDB($db,$in);

echo "<title>".$ent->GetTitle()." - Blog Tite</title>\n";

echo '<link href="'.config::$path.'template/'.config::$template.'/tichyblog.css" rel="stylesheet" type="text/css" />';
?>
<!--[if IE 5]>
<style type="text/css">
.tichyblog #menu { width: 230px; }
</style>
<![endif]--><!--[if IE]>
<style type="text/css">
.tichyblog #mainContent { zoom: 1; }
</style>
<![endif]-->
