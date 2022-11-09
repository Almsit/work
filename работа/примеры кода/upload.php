<?php

    include_once "../../inc/db.php";




ini_set("display_errors", 0);

@require_once $_SERVER['DOCUMENT_ROOT'].'/assets/include/excel/PHPExcel.php';
    $objPHPExcel = PHPExcel_IOFactory::load($_FILES['file']['tmp_name']);
    $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

    $ii = 0;
    $arr = array();
    $arr_count = array();
    $or = "";
    foreach ($objWorksheet->getRowIterator() as $row) {

        $code = "";
        $count = "";
        $img = "";
        $name = "";
        $article = "";
        $i = 0;
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);
        foreach ($cellIterator as $cell) {
            if($cell->getValue() != "") {
                if($ii>1){
                    if ($i == 0) {
                        $code = $cell->getValue();


                        if ($or == "") {
                            $or = " t2.mf_productbarcode_code='" . $code . "' ";
                        } else {
                            $or .= " OR t2.mf_productbarcode_code='" . $code . "' ";
                        }


                    } else {
                        $count = $cell->getValue();
                        $arr_count[$code] = $count;
                        //$arr[] = array("img"=>$img, "name"=>$name, "article"=>$article, "count"=>$count, "code"=>$code);
                    }

                    //print_r($cell->getValue()."<br>");
                    $i++;
                }
                $ii++;
            }
        }
        $i = 0;
    }
    //print_r($or);
    //exit();
    $like = " (".$or.") ";
    $order = "";


$group = "GROUP BY t2.mf_productbarcode_code";

//$group = " ";


$mysqli->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))") or die ('Ошибка ' . $mysqli->error);
    $s = $mysqli->query("SELECT t1.mf_product_id, t1.mf_product_name, t1.mf_product_article, t2.mf_productbarcode_code, t3.mf_productimg_url, t4.mf_stock_count, t6.mf_productapi_id  FROM mf_product t1
    LEFT JOIN (SELECT mf_productbarcode_product_id, mf_productbarcode_code FROM mf_productbarcode) t2 ON t1.mf_product_id=t2.mf_productbarcode_product_id 
    LEFT JOIN (SELECT mf_productimg_product_id, mf_productimg_url FROM mf_productimg) t3 ON t1.mf_product_id=t3.mf_productimg_product_id
    LEFT JOIN(SELECT mf_stock_count, mf_stock_product_id, mf_stock_warehouse_id FROM mf_stock) t4 ON t4.mf_stock_product_id=t1.mf_product_id
    LEFT JOIN(SELECT mf_warehouse_id FROM mf_warehouse) t5 ON t4.mf_stock_warehouse_id=mf_warehouse_id LEFT JOIN(SELECT mf_productapi_id, mf_productapi_product_id FROM mf_productapi) t6 ON t6.mf_productapi_product_id=t1.mf_product_id AND t1.mf_product_id=t6.mf_productapi_product_id WHERE " . $like . " ".$group." " . $order . "") or die ('Ошибка ' . $mysqli->error);
/*


 $group = "GROUP BY mf_productbarcode_code";
//$group = " ";

    $mysqli->query("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))") or die ('Ошибка ' . $mysqli->error);
    $s = $mysqli->query("SELECT MAX(t1.mf_product_id) AS mf_product_id, REPLACE(t1.mf_product_name, '', '') AS mf_product_name, REPLACE(t1.mf_product_article, '', '') AS mf_product_article, any_value(t2.mf_productbarcode_code) AS mf_productbarcode_code, t2.mf_productbarcode_id, MIN(t3.mf_productimg_url) AS mf_productimg_url2 FROM mf_product t1
    LEFT JOIN (SELECT mf_productbarcode_id, mf_productbarcode_product_id, mf_productbarcode_code FROM mf_productbarcode) t2 ON t1.mf_product_id=t2.mf_productbarcode_product_id
    LEFT JOIN (SELECT mf_productimg_product_id, mf_productimg_url FROM mf_productimg) t3 ON t1.mf_product_id=t3.mf_productimg_product_id
    LEFT JOIN(SELECT mf_stock_count, mf_stock_product_id, mf_stock_warehouse_id FROM mf_stock) t4 ON t4.mf_stock_product_id=t1.mf_product_id
    LEFT JOIN(SELECT mf_warehouse_id FROM mf_warehouse) t5 ON t4.mf_stock_warehouse_id=mf_warehouse_id WHERE " . $like . " ".$group." " . $order . "") or die ('Ошибка ' . $mysqli->error);



$s = $mysqli->query("SELECT t1.mf_product_id, " . $repl . " AS mf_product_name, " . $repl2 . " AS mf_product_article, MAX(t3.mf_productimg_url) AS mf_productimg_url2, MIN(t4.mf_stock_count) AS mf_stock_count2 FROM mf_product t1 
    LEFT JOIN (SELECT mf_productbarcode_product_id, mf_productbarcode_code FROM mf_productbarcode) t2 ON t1.mf_product_id=t2.mf_productbarcode_product_id 
    LEFT JOIN (SELECT mf_productimg_product_id, mf_productimg_url FROM mf_productimg) t3 ON t1.mf_product_id=t3.mf_productimg_product_id LEFT JOIN(SELECT mf_stock_count, mf_stock_product_id, mf_stock_warehouse_id FROM mf_stock) t4 ON t4.mf_stock_product_id=t1.mf_product_id
    LEFT JOIN(SELECT mf_warehouse_id FROM mf_warehouse) t5 ON t4.mf_stock_warehouse_id=mf_warehouse_id WHERE " . $like . " AND t1.mf_product_partner_id=".$_GET['partner_id']." ".$group." " . $order . " LIMIT 10") or die ('Ошибка ' . $mysqli->error);
*/

    for ($i = 0; $f = $s->fetch_array(MYSQLI_BOTH); $i++) {
        if($f["mf_stock_count"] == "" || $f["mf_stock_count"] == null){
            $f["mf_stock_count"] = 0;
        }
        if($arr_count[$f["mf_productbarcode_code"]] == "" || $arr_count[$f["mf_productbarcode_code"]] == null){
            $arr_count[$f["mf_productbarcode_code"]] = 0;
        }
        $arr[] = array("code"=>$f["mf_productbarcode_code"], "id"=>$f["mf_product_id"]."_".$f["mf_productapi_id"], "name"=>$f["mf_product_name"], "img"=>$f["mf_productimg_url2"], "name"=>$f["mf_product_name"], "article"=>$f["mf_product_article"], "count"=>$f["mf_stock_count"], "count_excel"=>$arr_count[$f["mf_productbarcode_code"]]);
    }

    $array = array(
        "json" => $arr
    );
    print_r(json_encode($array));


