<?php
function OpenDB()
{
  if($db=sqlite_open(config::$dbfile))
    return $db;
  else
    die($err);
}

function CloseDB($db)
{
  sqlite_close($db);
}

function DeleteEntryByID($db,$id,$isblog)
{
  if($isblog)
    sqlite_query($db,"DELETE FROM blogentry WHERE id=".((int)$id));
  else
    sqlite_query($db,"DELETE FROM staticentry WHERE id=".((int)$id));
}

?>
