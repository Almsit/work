<?php
include_once "../../inc/start.php";
include_once "../../inc/db.php";

ini_set("display_errors", 0);
@require_once $_SERVER['DOCUMENT_ROOT'].'/assets/include/excel/PHPExcel.php';
$objPHPExcel = PHPExcel_IOFactory::load($_FILES['file']['tmp_name']);
$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

$ii = 0;
$arr = array();
$arr_count = array();
$or = "";
$i2 = 0;
$i3 = 0;
$link = [];
$name = [];
$brand = [];
$article = [];
$barcode = [];
$err = false;
foreach ($objWorksheet->getRowIterator() as $row) {

    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    $i = 0;
    if($i3 == 0){
        $i3++;
        continue;
    }
    foreach ($cellIterator as $key=>$cell) {
        //if ($cell->getValue() != "") {
            //if ($i == 0) {
            if ($key == "A" && $cell->getValue() != "") {
                $link[$i2] = $cell->getValue();
                //} else if ($i == 1) {
            } else if ($key == "B" && $cell->getValue() != "") {
                $name[$i2] = $cell->getValue();
            //} else if ($i == 2) {
            } else if ($key == "C" && $cell->getValue() != "") {
                $brand[$i2] = $cell->getValue();
            //} else if ($i == 3) {
            } else if ($key == "D" && $cell->getValue() != "") {
                $article[$i2] = $cell->getValue();
            //} else if ($i == 4) {
            } else if ($key == "E" && $cell->getValue() != "") {
                $barcode[$i2] = $cell->getValue();
            }
        //}
        $i++;
        if($i == 5){
            $i = 0;
        }
    }
    $i2++;
}

/*
print_r($link);
print_r("<br>");
print_r($name);
print_r("<br>");
print_r($brand);
print_r("<br>");
print_r($article);
print_r("<br>");
print_r($barcode);
print_r("<br>");
*/
if(count($name)!=count($brand) || count($brand)!=count($article) || count($article)!=count($barcode) || count($barcode)!=count($name)){
   $err = true;
}
if(count($barcode) == 0){
    $err = true;
}
if($err == false){


    $i = 0;
    for($i = 0; $i<count($barcode); $i++){
        //if($name[$i] == "" || $brand[$i] == "" || $article[$i] == "" || $barcode[$i] == ""){
        if($barcode[$i] == ""){
            //print_r("Заполнены не все поля - ".$name[$i]."---------".$brand[$i]."---------".$article[$i]."---------".$barcode[$i]);
            //exit();
        }
        $brand_id = add_brand($brand[$i], '2');
        if (is_numeric($brand_id)) {

            //$mysqli->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))") or die ('Ошибка ' . $mysqli->error);
            //$s = $mysqli->query("SELECT * FROM mf_product WHERE `mf_product_brand`='".$brand[$i]."' AND `mf_product_name`='".$name[$i]."' AND `mf_product_article`='".$article[$i]."' ") or die ('Ошибка ' . $mysqli->error);

            $check = 0;
            //$mf_product = $s->fetch_array(MYSQLI_BOTH);
           /* if($mysqli->affected_rows > 0) {
                $check = "1";
            }*/
            $s = $mysqli->query("SELECT mf_productbarcode_product_id FROM mf_productbarcode WHERE `mf_productbarcode_code`='".trim($barcode[$i])."' ") or die ('Ошибка ' . $mysqli->error);
            if($mysqli->affected_rows > 0) {
                $check = "1";
            }
            //if($check != 0) {
            if($check == 1) {
                $mf_product_barcode = $s->fetch_array(MYSQLI_BOTH);
                $product[$i]["err"] = "true";
                $product[$i]["mp_mf_short"] = $mf_mp_short;
                $product[$i]["name"] = $name[$i];
                $product[$i]["brand"] = $brand[$i];
                $product[$i]["article"] = $article[$i];
                $product[$i]["barcode"] = trim($barcode[$i]);
                $product[$i]["id"] = $mf_product_barcode["mf_productbarcode_product_id"];
                $product[$i]["hash"] = hash_url('product_edit'.$mf_product_barcode["mf_productbarcode_product_id"]);
                $product[$i]["text"] = "";
                //echo "Уже существует ".$check;
                //exit();
                continue;
            } else {
                $s = $mysqli->query("SELECT mf_mp_name, mf_mp_short FROM `mf_mp` ") or die ('Ошибка ' . $mysqli->error);
                $mf_mp_short = "ff";
               for(;$mf_mf = $s->fetch_array(MYSQLI_BOTH);){
                   $m1 = preg_replace("/\./is", "", $mf_mf["mf_mp_name"]);
                   $m2 = preg_replace("/\./is", "", $link[$i]);
                   if(preg_match("/".$m1."/is", $m2)){
                       $mf_mp_short = $mf_mf["mf_mp_short"];
                   }
               }







                $mysqli->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))") or die ('Ошибка ' . $mysqli->error);
                $s = $mysqli->query("INSERT INTO `api_ff_product` SET api_ff_product_inn='" . $_GET['mf_product_inn'] . "', api_ff_product_mp='" . $mf_mp_short . "', api_ff_product_brand='" . $brand[$i] . "', api_ff_product_name='" . $name[$i] . "', api_ff_product_barcodes='" . $barcode[$i] . "', api_ff_product_img='123', api_ff_product_barcodes_id='123', api_ff_product_img_id='123', api_ff_product_article='" . $article[$i] . "', api_ff_product_create_datetime='" . date("Y-m-d H:i:s") . "'") or die ('Ошибка ' . $mysqli->error);



                $mf_product_q = mysqli_query($GLOBALS['mysq_connect_link'], "INSERT INTO `mf_product` SET
                    `mf_product_partner_id`='" . mysqli_real_escape_string($GLOBALS['mysq_connect_link'], $_GET['mf_partner_id']) . "',
                    `mf_product_master_id`='" . mysqli_real_escape_string($GLOBALS['mysq_connect_link'], $_GET['mf_user_master_id']) . "',
                    `mf_product_brand`='" . trim(mysqli_real_escape_string($GLOBALS['mysq_connect_link'], $brand[$i])) . "',
                    `mf_product_brand_id`='" . mysqli_real_escape_string($GLOBALS['mysq_connect_link'], $brand_id) . "',
                    `mf_product_name`='" . trim(mysqli_real_escape_string($GLOBALS['mysq_connect_link'], $name[$i])) . "',
                    `mf_product_article`='" . mysqli_real_escape_string($GLOBALS['mysq_connect_link'], $article[$i]) . "',
                    `mf_product_create_datetime`='" . date("Y-m-d H:i:s") . "',
                    `mf_product_create_user_id`='" . $_GET['mf_user_id'] . "'
                    ;");
                $mf_product_ID = ($mf_product_q) ? mysqli_insert_id($GLOBALS['mysq_connect_link']) : false;
                $mpproduct_id = $mf_product_ID;



                $product[$i]["err"] = "false";
                $product[$i]["mp_mf_short"] = $mf_mp_short;
                $product[$i]["name"] = $name[$i];
                $product[$i]["brand"] = $brand[$i];
                $product[$i]["article"] = $article[$i];
                $product[$i]["barcode"] = $barcode[$i];
                $product[$i]["id"] = $mf_product_ID;

                $product[$i]["hash"] = hash_url('product_edit'.$mf_product_ID);
                $product[$i]["text"] = "";

                $mysqli->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))") or die ('Ошибка ' . $mysqli->error);
                $s = $mysqli->query("INSERT INTO `mf_productapi` SET mf_productapi_product_id='" . $mf_product_ID . "', mf_productapi_product_mpproduct_id='" . $mpproduct_id . "', mf_productapi_product_mp_sku='', mf_productapi_mp='" . $mf_mp_short . "'") or die ('Ошибка ' . $mysqli->error);

                $str = "";
                $productImg_ID = 0;



                $productapi_ID = $mysqli->insert_id;

                $mysqli->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))") or die ('Ошибка ' . $mysqli->error);
                $s = $mysqli->query("INSERT INTO `mf_productbarcode` SET mf_productbarcode_product_id='" . $mf_product_ID . "', mf_productbarcode_code='" . $barcode[$i] . "', mf_productbarcode_productapi_id='" . $productapi_ID . "'") or die ('Ошибка ' . $mysqli->error);

                $productBarcodes_ID = $mysqli->insert_id;



                $mysqli->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))") or die ('Ошибка ' . $mysqli->error);
                $s = $mysqli->query("UPDATE api_ff_product SET api_ff_product_img='" . $str . "', api_ff_product_barcodes_id='" . $productBarcodes_ID . "', api_ff_product_img_id='" . $productImg_ID . "' WHERE 	api_ff_product_id='" . $mpproduct_id . "'") or die ('Ошибка ' . $mysqli->error);



            }
        }
    }
} else {
    $product[0]["err"] = "true";
    $product[0]["text"] = "В файле заполнены не все поля или файл пуст";
}
$arr["json"] = $product;
print_r(json_encode($arr));