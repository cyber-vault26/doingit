 <?php
    date_default_timezone_set('Africa/Dar_es_Salaam');
//     ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
          function openConn() : mysqli{
   
                    $dbuser = "sbiaccountco_mangowolight_user";

               $dbhost = "localhost";
               
               $dbname ="sbiaccountco_mangowolight"; 
               $dbpassword = "Mangowolight@2026";
               $conn =  mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);
               return $conn;
          }
          function closeConn(mysqli $conn){
               $conn->close();
          }
?>