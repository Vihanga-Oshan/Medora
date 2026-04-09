<?php
$m = new mysqli('localhost','root','1234','medoradb',3306);
if ($m->connect_error) { echo 'connect_error: '.$m->connect_error.PHP_EOL; exit; }
$tables = ['medicines','categories','category'];
foreach ($tables as $t) {
  $rs = $m->query("SHOW TABLES LIKE '$t'");
  echo "[$t] exists=" . (($rs && $rs->num_rows>0)?'yes':'no') . PHP_EOL;
  if ($rs && $rs->num_rows>0) {
    $c = $m->query("SHOW COLUMNS FROM `$t`");
    while($row=$c->fetch_assoc()){echo ' - '.$row['Field'].' ('.$row['Type'].')'.PHP_EOL;}
  }
}
?>
