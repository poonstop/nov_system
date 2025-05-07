<br />
<b>Warning</b>:  Undefined array key "id" in <b>C:\xampp\htdocs\nov_system\public\backup_restore.php</b> on line <b>157</b><br />
<br />
<b>Fatal error</b>:  Uncaught TypeError: mysqli_prepare(): Argument #1 ($mysql) must be of type mysqli, null given in C:\xampp\htdocs\nov_system\public\backup_restore.php:72
Stack trace:
#0 C:\xampp\htdocs\nov_system\public\backup_restore.php(72): mysqli_prepare(NULL, 'INSERT INTO use...')
#1 C:\xampp\htdocs\nov_system\public\backup_restore.php(157): logUserAction(NULL, 'BACKUP_DOWNLOAD...', 'Downloaded back...')
#2 {main}
  thrown in <b>C:\xampp\htdocs\nov_system\public\backup_restore.php</b> on line <b>72</b><br />
