<?php
/*
mysqli_report(MYSQLI_REPORT_ALL);
$link = mysqli_connect("localhost", "root", "root", "prueba");

if (!$link) {
    echo "Error: Unable to connect to MySQL." . PHP_EOL;
    echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}

echo "Success: A proper connection to MySQL was made! The my_db database is great." . PHP_EOL;
echo "holaaaaaaaaa!";

$datos=mysqli_query($link,"SELECT id FROM users");
//$reporte=mysqli_free_result($datos);

echo "<pre>";
print_r($datos);
//print_r($hola2);
echo "</pre>";
*/

$pregunta=array('1','2','3','4','5');
$largo=sizeof($pregunta);
$datos=array('1','1','2','3','3','3','3','5','5');
echo "<pre>";
print_r($datos);
echo "</pre>";

$conjunto=array();





do{
	foreach ($datos as $key=>$value) {
		$contador=0;
		if ($value==$largo) {
		 	$contador++;
		 }
		 $suma+=$contador;
	}
	array_push($conjunto,$suma);
	$suma=0;
	$largo--;
} while ( $largo > 0);


echo "<pre>";
print_r($conjunto);
echo "</pre>";

echo  'total encontrado ' . $suma .'</br>';
echo $largo .'</br>';

print_r(array_count_values($datos));


