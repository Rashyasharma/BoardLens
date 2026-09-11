<?php
require 'vendor/autoload.php';
$r = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile('D:\Rashya Sharma\CIE\Other Docs\CIE ALL Broadsheets\Results file\Electronic Results File for June 2023.xls');
$r->setReadDataOnly(true);
$s = $r->load('D:\Rashya Sharma\CIE\Other Docs\CIE ALL Broadsheets\Results file\Electronic Results File for June 2023.xls');
$d = $s->getActiveSheet()->toArray();
foreach($d as $row) {
    if(strpos(json_encode($row), 'MOLLY') !== false) print_r($row);
}
