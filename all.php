<?php
error_reporting(E_ALL);
ob_start();
print "Content-type: text/html\n\n";
set_time_limit(0);
ini_set('memory_limit', '256M');
date_default_timezone_set('America/New_York');
require_once("lib/presentation.php");
require_once("lib/lib_v2.php");
require_once("lib/ChromePhp.php");
include 'lib/open-flash-chart-2-Lug-Wyrm-Charmer/php-ofc-library/open-flash-chart.php';
/*
UserManagement::InitSession();
if(!isset($_SESSION['User'])) {
	header("Location: /");
	die();	
}
UserManagement::SetInactiveLogoutTime(1000);
*/
$colors = array('#000000', '#0000CC', '#990000', '#663300', '#0066FF', '#333333', '#FF3300', '#990066', '#666699', '#996633');
$DB = new Database();
//$total_data = $DB->trackTotal_getAllIndividualData($_SESSION['User']->user_id);
$total_data = $DB->trackTotal_getAllIndividualData(74);
ChromePhp::log($total_data);
return;

$num_data_points = count($total_data);
////ChromePhp::log($num_data_points . " -- " . floor($num_data_points/260) . " years, " . floor($num_data_points/22)-floor($num_data_points/260) . " months, " . floor($num_data_points/5)-floor($num_data_points/22)-floor($num_data_points/260) . " weeks");

$chart = new open_flash_chart();

// x legend properties
$amt_stride = 1;
$date_stride = 1;
if($num_data_points <= 10){
	// <= 10 (can show dates); date_stride = 1; amt_stride = 1;
	$x_legend = new x_legend('Date (mm/dd/yyyy)');
	$x_legend->set_style('{font-size:18px; font-family:Calibri; color:#121212}');
	$chart->set_x_legend($x_legend);
} else if ($num_data_points <= 20){
	// 10 < n <= 20 (can show mm/yy); date_stride = 1; amt_stride = 1;
	$date_stride = 2;
	$x_legend = new x_legend('Date (mm/dd)');
	$x_legend->set_style('{font-size:18px; font-family:Calibri; color:#121212}');
	$chart->set_x_legend($x_legend);
} else if ($num_data_points <= 30){
	// 20 < n <= 30;
	$date_stride = 2;
	$x_legend = new x_legend('Date (mm/dd)');
	$x_legend->set_style('{font-size:18px; font-family:Calibri; color:#121212}');
	$chart->set_x_legend($x_legend);
} else if ($num_data_points <= 50){
	$date_stride = 5;
	$x_legend = new x_legend('Date (mm/dd)');
	$x_legend->set_style('{font-size:18px; font-family:Calibri; color:#121212}');
	$chart->set_x_legend($x_legend);
} else if ($num_data_points <= 100){
	$amt_stride = 2;
	$date_stride = 10;
	$x_legend = new x_legend('Date (mm/dd)');
	$x_legend->set_style('{font-size:18px; font-family:Calibri; color:#121212}');
	$chart->set_x_legend($x_legend);
} else if ($num_data_points <= 200){
	$amt_stride = 5;
	$date_stride = 15;
	$x_legend = new x_legend('Date (mm/yy)');
	$x_legend->set_style('{font-size:18px; font-family:Calibri; color:#121212}');
	$chart->set_x_legend($x_legend);
} else if ($num_data_points <= 500){
	////ChromePhp::log("$num_data_points is between 200 and 500(inclusive)");
	$amt_stride = 10;
	$date_stride = 30;
	$x_legend = new x_legend('Date (Q`yy)');
	$x_legend->set_style('{font-size:18px; font-family:Calibri; color:#121212}');
	$chart->set_x_legend($x_legend);
} else if ($num_data_points <= 1000) {
	$amt_stride = 15;
	$date_stride = 65;
	$x_legend = new x_legend('Date (Q`yy)');
	$x_legend->set_style('{font-size:18px; font-family:Calibri; color:#121212}');
	$chart->set_x_legend($x_legend);
} else {
	$amt_stride = 30;
	$date_stride = 120;
	$x_legend = new x_legend('Date (yyyy)');
	$x_legend->set_style('{font-size:18px; font-family:Calibri; color:#121212}');
	$chart->set_x_legend($x_legend);
}

// have to find all the various stocks that have made up this guy's portfolio over the time he's been on MA
$stocks = array();
foreach($total_data as $data_item){
	if(array_search($data_item->symbol_name, $stocks) == false){
		$stocks[] = $data_item->symbol_name;	
	}
}

// one line for each stock
$values = array();
for($i = 0; $i < count($stocks); $i++){
	$values[] = array();
}


// construct data correctly
$max = $total_data[0]->amount;
$min = $total_data[0]->amount;

$num_updated = 0;
$num_portfolios_processed = 0;

for($i = 0; $i < count($total_data); ){
	$num_updated = 0;
	$portfolio_id = $total_data[$i]->portfolio_id;
	
	while($portfolio_id == $total_data[$i]->portfolio_id){
		
		$val = $total_data[$i]->last_change;
		$val2= $total_data[$i]->sofyr_return;
		$val3= $total_data[$i]->first_return;
		$amt = $total_data[$i]->amount;
		
		$year 	= $data_item->year;
		$month	= $data_item->month;
		$day	= $data_item->day;
		
		if($total_data[$i]->amount > $max){
			$max = $total_data[$i]->amount;	
		}
		if($total_data[$i]->amount < $min){
			$min = $total_data[$i]->amount;	
		}
		
		if(($amt_count%$amt_stride) == 0){	
			$dot = new dot($amt);
			if($val > 0.0){
				$values[array_search($total_data[$i]->symbol_name, $stocks)] = $dot->colour('#006600')->tooltip("$month/$day/$year\n$amt\nDay's Return: $val%\nYTD Return: $val2%\nReturn (Inception): $val3%");
			} else {
				$values[array_search($total_data[$i]->symbol_name, $stocks)] = $dot->colour('#FF0000')->tooltip("$month/$day/$year\n$amt\nDay's Return: $val%\nYTD Return: $val2%\nReturn (Inception): $val3%");
			}
		}
		$amt_count++;
		
		
		$i++;
		$num_updated++;
	}
	$num_portfolios_processed++;
	
	if(($num_updated != count($stocks)) && (($amt_count%$amt_stride) == 0)){
		// somebody did not have data for a given portfolio_id	
		for($j = 0; $j < count($stocks); $j++){
			if(count($values[$j]) != $num_portfolios_processed){
				array_push($values[$j], NULL);	
			}
		}
	}
}

// construct x-axis appropriately
$x_labels = array();
$date_count = 0;

foreach($total_data[0] as $data_item){		
	if(($date_count%$amt_stride) == 0){
		if(($date_count%$date_stride) == 0){
			if($date_stride == 1){
				$x_labels[] = "$month/$day/$year";
			} else if($date_stride <= 2){
				$year %= 2000;
				$x_labels[] = "$month/$day";
			} else if($date_stride <= 5){
				$year %= 2000;
				$x_labels[] = "$month/$day";
			} else if($date_stride <= 10){
				$year %= 2000;
				$x_labels[] = "$month/$year";
			} else if($date_stride <= 15){
				$year %= 2000;
				$label = "Q";
				if($month <= 3){
					$label .= "1`";	
				} else if($month <= 6){
					$label .= "2`";	
				} else if($month <= 9){
					$label .= "3`";	
				} else {
					$label .= "4`";	
				}
				$label .= $year;
				$x_labels[] = $label;	
			} else {
				////ChromePhp::log("Adding data");
				$x_labels[] = $year;	
			}
		} else {
			$x_labels[] = '';	
		}
	}
	$date_count++;
}
	
// line properties
$lines = array();
for($i = 0; $i < count($stocks); $i++){
	$lines[] = new line();
	$lines[$i]->set_values($values[$i]);
	$lines[$i]->set_width(2);
	$lines[$i]->set_colour($colors[$i]);
	$chart->add_element($lines[$i]);
}


// title properties
$title = new title("Total Portfolio Performance");
$title->set_style( "{font-size: 25px; font-family: Calibri; font-weight: bold; color: #121212; text-align: center;}" );
$chart->set_title( $title );


// y axis properties
$y = new y_axis();
$y_min = 0.0; $y_max = 0.0;
$y_min = floor(0.995*$min);
$y_max = ceil(1.005*$max);
$y->set_grid_colour('#EFEFEF');
$y->set_range($y_min, $y_max, round(($y_max-$y_min)/8.0));
$chart->set_y_axis($y);


// y legend properties
$y_legend = new y_legend('Amount ($)');
$y_legend->set_style('{font-size:15px; font-family:Calibri; color:#121212}');
$chart->set_y_legend($y_legend);


// x axis properties
$x = new x_axis();
$x->set_labels_from_array($x_labels);
$x->grid_colour('#EFEFEF');
$chart->set_x_axis($x);


// background properties
$chart->set_bg_colour('#FFFFFF');


echo $chart->toPrettyString();

?>