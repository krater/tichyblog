<?php
function templ_outentry($link,$path,$pic,$title,$description,$time)
{
  if(empty($pic))
  {
    echo '<hr><table class="overview" cellspacing="0"><tr>'.
         '<td class="pic"></td>'.
         '<td class="desc">'.
            '<h2><a href="'.$link.'">'.$title.'</a></h2>'.date(DATE_RFC822,$time).
            '<p>'.$description.'</p>'.
         '</td>'.
         '</tr></table>';
  }
  else
  {
    echo '<hr><table class="overview" cellspacing="0"><tr>'.
         '<td class="pic"><a href="'.$link.'"><img src="'.$path.$pic.'" alt=""></a></td>'.
         '<td class="desc">'.
            '<h2><a href="'.$link.'">'.$title.'</a></h2>'.date(DATE_RFC822,$time).
            '<p>'.$description.'</p>'.
         '</td>'.
         '</tr></table>';
  }
}
?>
