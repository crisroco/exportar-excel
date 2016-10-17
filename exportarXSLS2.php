<?php
include_once 'Classes/PHPExcel.php';
require_once("../../config.php");
global $DB;
$phpexcel = new PHPExcel();
$phpexcel->setActiveSheetIndex(0);

//##############################==REPORTE COMPLETO POR ALUMNO Y GRUPOS==###########################################
//Consultas DB

$encuesta=$DB->get_record('course_modules',array('id' => $_GET['id']) ,'id,instance');	
$questionid = $DB->get_records('feedback_item',array('feedback'=>$encuesta->instance),null,'id,name,typ,feedback,presentation');
$fbid = end($questionid);//obtiene ultimo objeto del array
$courseid = $DB->get_records('feedback',array('id' => $fbid->feedback),null,'id as fbid, course');



foreach ($questionid as $key=>$value) {

	$questions = $DB->get_records('feedback_item', array('id' => $value->id), null, 'id, name');
	
}

$course = $courseid[$fbid->feedback]->course;
$questionid = $DB->get_records('feedback_item',array('feedback'=>$encuesta->instance),null,'id,name,typ,feedback,presentation');

//FILTRAR REPORTE POR GRUPOS 
if ($_GET['group'] != 0 ) {

	$sql = "SELECT fv.id, fc.userid, f.timemodified, u.city, u.firstname, u.lastname, u.username, c.fullname, gr.name as grupo, fi.presentation, fv.value, fi.typ
	FROM {user} u
	INNER JOIN {feedback_completed} fc ON fc.userid = u.id
	INNER JOIN {feedback} f ON f.id = fc.feedback
	INNER JOIN {course} c ON f.course = c.id 
	INNER JOIN {groups_members} grm ON  u.id = grm.userid
	INNER JOIN {groups} gr ON  grm.groupid = gr.id
	INNER JOIN {feedback_value} fv ON  fc.id = fv.completed
	INNER JOIN {feedback_item} fi ON  fi.id = fv.item
	WHERE f.id IN (?)
	AND gr.id IN (?)";


	$user = $DB->get_records_sql($sql, array($fbid->feedback,$_GET['group']));
}else{

	$sql = "SELECT fv.id, fc.userid, f.timemodified, u.city, u.firstname, u.lastname, u.username, c.fullname, gr.name as grupo, fi.presentation, fv.value, fi.typ
	FROM {user} u
	INNER JOIN {feedback_completed} fc ON fc.userid = u.id
	INNER JOIN {feedback} f ON f.id = fc.feedback
	INNER JOIN {course} c ON f.course = c.id 
	INNER JOIN {groups_members} grm ON  u.id = grm.userid
	INNER JOIN {groups} gr ON  grm.groupid = gr.id
	INNER JOIN {feedback_value} fv ON  fc.id = fv.completed
	INNER JOIN {feedback_item} fi ON  fi.id = fv.item
	WHERE f.id IN (?)";
	$user = $DB->get_records_sql($sql, array($fbid->feedback));
}

//Hoja 1 - REPOSTE COMPLET0

$objWorkSheet = $phpexcel->getActiveSheet(0)->setTitle('reporte completo'); //Setting index when creating

//titulos de datos
$title = array('Respuesta', 'ID', 'Fecha de Envio', 'Departamento', 'Nombre', 'Apellido', 'Nombre de Usuario', 'Curso', 'Grupo');
$td=0;

foreach ($title as $key => $value) {
	$objWorkSheet->setCellValueByColumnAndRow($td,1, $value);
	$td++;
}

//Datos	estaticos del alumno
$row = 2;
$keytemp = '';
foreach ($user as $key => $value) {
	$colum = 0;

	foreach ($value as $keys => $values) {
		if ($keys == 'value' || $keys == 'presentation' || $keys == 'typ') {
			continue;
		}
		if ($keys == 'timemodified') {

			$values = gmdate("d-m-Y\  H:m", $values);
		}
		if ( $value->userid  != $keytemp) {					
			
			$objWorkSheet->setCellValueByColumnAndRow($colum,$row, $values);
			$colum++;

		}else{
			continue;
		}
		

	}

	if ($value->userid != $keytemp) {
		$row++;
	}

	$row;
	$keytemp = $value->userid;
	
}

//titulo de respuestas
$td2 = 9;
$dato = 'Q';
$valor = 1;

foreach ($questionid as $key => $value ) {
	
	if ($value->typ == 'pagebreak' || $value->typ == 'label' || $value->typ == 'info') {
		unset($questionid[$key]);
		continue;
	}
	//echo $value->typ .'<br>';
	$value = $dato . $valor;
	$objWorkSheet->setCellValueByColumnAndRow($td2,1, $value);
	$td2++;
	$valor++;

}

//Respuestas de preguntas por alumno
$td2 = 9;
$row2 = 2;
$nn = count($questionid);

foreach ($user as $key => $value) {
	if ($value->typ == 'pagebreak' || $value->typ == 'label' || $value->typ == 'info') {
		
		continue;
	}

	$valortemp = '';
	
	$presentation =  explode('|', $value->presentation);

	$valorestemp = array();

	
	foreach ($presentation as $keys => $values) {
		
		if ($value->typ == 'textfield' || $value->typ == 'textarea' || $value->typ == 'numeric') {
			$valortemp = $value->value;
			$valorestemp['coment'] = $valortemp;

		}elseif ($value->typ == 'multichoice') {
			$valorestemp =explode('|', $value->value);

			foreach ($valorestemp as $k => $val) {
				$valorestemp[$val] = $val;
				unset($valorestemp[$k]);
			}

		}			
		
	}

	if (empty($valorestemp)) {
		$valorestemp['emty'] = '-';
	}	
	
	$temp = '';
	foreach ($valorestemp as $key => $value) {
		
		switch ($key) {
			case '1':
			$valo = '1 : A';
			break;

			case '2':
			$valo = '2 : B';
			break;

			case '3':
			$valo = '3 : C';
			break;

			case '4':
			$valo = '4 : D';
			break;

			case '5':
			$valo = '5 : E';
			break;

			case '6':
			$valo = '6 : F';
			break;

			case '7':
			$valo = '7 : G';
			break;	
			
			case 'coment':
			$valo = $value;
			break;
			case 'emty':
			$valo = $value;
			break;
			default:
				# code...
			break;
		}
		if (count($valorestemp)>1) {
			$temp .= '| ' .$valo . ' |';
		}else{
			$temp .= $valo;
		}
		
		
		
    }
		$objWorkSheet->setCellValueByColumnAndRow($td2,$row2, $temp)
					;
        $td2++;

	
		$spaces = $td2-$nn;	
		if ($spaces == 9) {
			$row2++;
			$td2 = 9;
		}	
}//die();

//estilos de hojas

$objWorkSheet->getColumnDimension('A')->setAutoSize(true);
$objWorkSheet->getColumnDimension('B')->setAutoSize(true);
$objWorkSheet->getColumnDimension('C')->setAutoSize(true);
$objWorkSheet->getColumnDimension('D')->setAutoSize(true);
$objWorkSheet->getColumnDimension('E')->setAutoSize(true);
$objWorkSheet->getColumnDimension('F')->setAutoSize(true);
$objWorkSheet->getColumnDimension('G')->setAutoSize(true);
$objWorkSheet->getColumnDimension('H')->setAutoSize(true);
$objWorkSheet->getColumnDimension('I')->setAutoSize(true);
$objWorkSheet->getColumnDimension('J')->setAutoSize(true);
$objWorkSheet->getColumnDimension('K')->setAutoSize(true);
$objWorkSheet->getColumnDimension('L')->setAutoSize(true);
$objWorkSheet->getColumnDimension('M')->setAutoSize(true);
//fin primera hoja de reporte



//##############################==REPORTE GRAFICO POR PREGUNTA==###########################################
//Hoja del gráfico

$encuesta=$DB->get_record('course_modules',array('id'=>$_GET['id']),'id,instance');

$objWorkSheet = $phpexcel->createSheet(1)->setTitle('reporte grafico');


if($_GET['group'] != 0){
	$grupo = groups_get_members($_GET['group']);

	$sql_compl = "SELECT id,userid,feedback FROM {feedback_completed}";
	$flag = true;
	$cond = '';
	foreach ($grupo as $key => $value) {
		if(!$flag){
			$cond .= ' OR userid = ' . $value->id;
		}else{
			$cond .= ' WHERE userid = ' . $value->id;
		}
		$flag = false;
	}

	$users_comp = $DB->get_records_sql($sql_compl . $cond);

}

$questionid = $DB->get_records('feedback_item',array('feedback'=>$encuesta->instance),null,'id,name,typ,presentation');

    $espacio = 0;
    foreach ($questionid as $key=>$value) {

    	if ($value->typ=='label' || $value->typ=='info' || $value->typ=='textarea' || $value->typ=='textfield'|| $value->typ=='pagebreak' || $value->typ=='numeric') {
    		continue;
    	}
    //etiquetas preguntas
    //$questions = $DB->get_records('feedback_item', array('id' => $value->id), null, 'id, name');
    	$data = array();
    //foreach ($questions as $key => $value) {
    	array_push($data, $value->name);
    //}
    	$row=$espacio+1;
    	$styleArray = array(
    		'font'  => array(
    			'bold'  => true,
    			'color' => array('rgb' => '808080'),
    			'size'  => 13,
    			'name'  => 'Verdana'
    			));

    	$objWorkSheet->getStyle("B$row:C$row")->applyFromArray($styleArray);


    	foreach($data as $point) {
    		$objWorkSheet->setCellValueByColumnAndRow(1, $row++, $point);

    	}

    //etiquetas de opciones
    //$options = $DB->get_records('feedback_item',array('id'=>$value->id),null,'id,presentation');
    //foreach ($options as $key => $value){

    	$data2=explode('|', $value->presentation);
    //}
    //elimina saltos de linea tabulaciones y caracteres especiales -> mberegi_replace("[\n|\r|\n\r|\t||\x0B]", "",$string);
    	$row = $espacio+2;
    	foreach($data2 as $point) {
    		if (strpos($point,"####")>0){
    			$point=substr($point, 2);
    		}
    		if (strpos($point,">>>>>")>0){
    			$point=substr($point, 1);
    		}
    		$objWorkSheet->setCellValueByColumnAndRow(1, $row++, mberegi_replace("[\n|\r|\n\r|\t||\x0B|>>>>>|####]", "",$point));
    	}

    //cantidad de veces marcados
    	$datalength=sizeof($data2);
    	$nespacios=$datalength;

    	if($_GET['group'] != 0){
    		$sql_gr_tmp = '';
    		$fl = true;
    		foreach ($users_comp as $k => $v) {
    			if($fl){
    				$sql_gr_tmp .= 'completed = ' . $v->id;
    			}else{
    				$sql_gr_tmp .= ' OR completed = ' . $v->id;
    			}

    			$fl = false;
    		}
    		$tmp_sql = "select id, completed, value from {feedback_value} WHERE item = " . $value->id . " AND (" . $sql_gr_tmp . ")";

    	}else{
    		$tmp_sql = "select id, completed, value from {feedback_value} WHERE item = " . $value->id ;
    	}

    	$values = $DB->get_records_sql($tmp_sql);
    	$datas=array();
    	foreach ($values as $key => $value){
    		$resp=explode('|', $value->value);
    		foreach ($resp as $key => $value) {
    			array_push($datas,$value);
    		}
    	}
    	$valores=array();
    	do{
    		$suma=0;
    		foreach ($datas as $key=>$value) {
    			$contador=0;
    			if ($value==$datalength) {
    				$contador++;
    			}
    			$suma+=$contador;
    		}
    		array_push($valores,$suma);
    		$datalength--;
    	} while ( $datalength > 0);

    	$valores=array_reverse($valores);
    	$row = $espacio+2;
    	foreach($valores as $point) {
    		$objWorkSheet->setCellValueByColumnAndRow(2, $row++, $point);
    	}

 //estilos de celdas
    	$objWorkSheet->getColumnDimension('B')->setAutoSize(true);
    	$objWorkSheet->getColumnDimension('C')->setAutoSize(true);
    	$objWorkSheet->getStyle('B1:C400')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    	$objWorkSheet->getStyle('B1:C400')->getAlignment()->setWrapText(true);
//ingresando grafico
    	$n1=$espacio+1;
    	$n2=$espacio+15;
    	$dato1='E'. $n1;
    	$dato2='J' . $n2;
    	$objWorkSheet->setCellValueByColumnAndRow(2, $n1, 'Cantidad de veces  marcada');
    	$dato3='Worksheet!B'. ($espacio+2) .':B'. ($espacio+1+$nespacios);
    	$dato4='Worksheet!C'. ($espacio+2) .':C'. ($espacio+1+$nespacios);
    	$categories = new PHPExcel_Chart_DataSeriesValues('String', $dato3);
    	$values = new PHPExcel_Chart_DataSeriesValues('String', $dato4);
    	$series = new PHPExcel_Chart_DataSeries(
PHPExcel_Chart_DataSeries::TYPE_PIECHART,       // plotType
+PHPExcel_Chart_DataSeries::GROUPING_CLUSTERED,  // plotGrouping
array(0),                                       // plotOrder
array(),                     // plotLabel
array($categories),                             // plotCategory
array($values)                                  // plotValues
);

    	$series->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_VERTICAL);
    	$layout = new PHPExcel_Chart_Layout();
    	$layout->setShowVal(TRUE);
//$layout->setShowPercent(TRUE);
    	$plotarea = new PHPExcel_Chart_PlotArea($layout, array($series));
/*No necesary in PIECHART
$xTitle = new PHPExcel_Chart_Title('Respuestas');
$yTitle = new PHPExcel_Chart_Title('');*/
$legend = new PHPExcel_Chart_Legend('', NULL, false);
$chart = new PHPExcel_Chart('sample', null, $legend, $plotarea, true,0,null,null);
$chart->setTopLeftPosition($dato1);
$chart->setBottomRightPosition($dato2);
$objWorkSheet->addChart($chart);

$espacio+=$nespacios+14;
}
//Fin hoja del gráfico



$writer = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
$writer->setIncludeCharts(TRUE);	
$hoy = date("j_F_Y");
$other = $DB->get_record('feedback',array('id'=>$encuesta->instance),'id,name');
$nombre = $other->name;
$filename = 'Reporte_'. $nombre ."_".$hoy.'.xlsx';
$writer->save($filename);
header("Content-disposition: attachment; filename=$filename");
header("Content-type:xlsx");
readfile("$filename");
unlink($filename);