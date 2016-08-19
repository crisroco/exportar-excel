<?php

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

header('Content-Disposition: attachment;filename="graph.xlsx"');
header('Cache-Control: max-age=0');

include_once 'Classes/PHPExcel.php';

require_once("../../config.php");

global $DB;

$phpexcel = new PHPExcel();

$phpexcel->setActiveSheetIndex(0);
$sheet = $phpexcel->getActiveSheet();



//#############################################################################

// etiquetas de pregunta
$questions = $DB->get_records('feedback_item',array('position'=>1),null,'id, name');

$data=array();
foreach ($questions as $key => $value){
    array_push($data,$value->name);
}
$row=1;
foreach($data as $point) {
    $sheet->setCellValueByColumnAndRow(0, $row++, $point);
}


//etiquetas de opciones
$options = $DB->get_records('feedback_item',array('position'=>1),null,'id,presentation');
foreach ($options as $key => $value){

    $data=explode('|', $value->presentation);
}
    //elimina saltos de linea tabulaciones y caracteres especiales -> mberegi_replace("[\n|\r|\n\r|\t||\x0B]", "",$string);
$row = 2;
foreach($data as $point) {
$sheet->setCellValueByColumnAndRow(1, $row++, mberegi_replace("[\n|\r|\n\r|\t||\x0B]", "",$point));
}


//cantidad de veces marcados
$datalength=sizeof($data);
$valores = $DB->get_records('feedback_value',array('item'=>1),null,'id, value');
$datas=array();
foreach ($valores as $key => $value){
    array_push($datas,$value->value);
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
$row = 2;
foreach($valores as $point) {
    $sheet->setCellValueByColumnAndRow(2, $row++, $point);
}

//########################################################################################

$values = new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$C$1:$C$15');
$categories = new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$B$1:$B$15');

$series = new PHPExcel_Chart_DataSeries(
PHPExcel_Chart_DataSeries::TYPE_BARCHART,       // plotType
+PHPExcel_Chart_DataSeries::GROUPING_CLUSTERED,  // plotGrouping
array(0),                                       // plotOrder
array(),                                        // plotLabel
array($categories),                             // plotCategory
array($values)                                  // plotValues
);
$series->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_HORIZONTAL);

$layout = new PHPExcel_Chart_Layout();
$plotarea = new PHPExcel_Chart_PlotArea($layout, array($series));
$xTitle = new PHPExcel_Chart_Title('Respuestas');
$yTitle = new PHPExcel_Chart_Title('');

$chart = new PHPExcel_Chart('sample', null, null, $plotarea, true,0,$xTitle,$yTitle);

$chart->setTopLeftPosition('E1');
$chart->setBottomRightPosition('L15');

$sheet->addChart($chart);

$writer = PHPExcel_IOFactory::createWriter($phpexcel, 'Excel2007');
$writer->setIncludeCharts(TRUE);
$writer->save('php://output');