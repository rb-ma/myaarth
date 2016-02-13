<?php
ob_start();
date_default_timezone_set('America/New_York');
require_once("lib/presentation.php");
require_once("lib/lib_v2.php");
require_once("lib/ChromePhp.php");
include 'lib/open-flash-chart-2-Lug-Wyrm-Charmer/php-ofc-library/open-flash-chart.php';

UserManagement::InitSession();
if(!isset($_SESSION['User']) || !isset($_GET['i'])) {
	header("Location: index.php");
	die();	
}
UserManagement::SetInactiveLogoutTime(30);

$DB = new Database();
$total_data = $DB->trackPortfolio_getData($_SESSION['User']->user_id, $_GET['i']);
//$total_data = $DB->trackIndividual_getData($_SESSION['User']->user_id, 21121);
$num_data_points = count($total_data);
////ChromePhp::log($num_data_points);

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
} else if ($num_data_points <= 250){
	$amt_stride = 5;
	$date_stride = 15;
	$x_legend = new x_legend('Date (mm/yy)');
	$x_legend->set_style('{font-size:18px; font-family:Calibri; color:#121212}');
	$chart->set_x_legend($x_legend);
}

$line = new line();

$returns 	= array();
$x_labels 	= array();

$amt_max = $total_data[0]->amount;
$amt_min = $total_data[0]->amount;

$amt_count = 0;
$date_count = 0;

foreach($total_data as $data_item){
	$val = round($data_item->last_change, 2);
	$val2= round($data_item->sofyr_return, 2);
	$val3= round($data_item->first_return, 2);
	$amt = round($data_item->amount, 2);
	//ChromePhp::log($data_item);	
	$year 	= $data_item->year;
	$month	= $data_item->month;
	$day	= $data_item->day;
	
	if(($amt_count%$amt_stride) == 0){	
		$dot = new dot($amt);
		if($val > 0.0){
			$returns[] = $dot->colour('#006600')->tooltip("$month/$day/$year\n$$amt\nYesterday: $val%\nYear-to-Date: $val2%\nInception: $val3%");
		} else {
			$returns[] = $dot->colour('#FF0000')->tooltip("$month/$day/$year\n$$amt\nYesterday: $val%\nYear-to-Date: $val2%\nInception: $val3%");
		}
	}
	$amt_count++;
	
	if($amt > $amt_max){
		$amt_max = $amt;	
	}
	if($amt < $amt_min){
		$amt_min = $amt;	
	}
	
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
$d = new hollow_dot();
$d->size(5)->halo_size(0)->colour('#3D5C56');
$line->set_default_dot_style($d);
$line->set_values($returns);
$line->set_width(2);
$line->set_colour('#668053');
$chart->add_element( $line );


// title properties
$portfolio_type = array("","Unknown","401(k)","Traditional IRA","Roth IRA","SIMPLE IRA","SEP-IRA","Solo 401(k)","Roth 401(k)","403(b)","Other");
$type_idx = $_GET['i'];
$type_idx = ($type_idx==0)?$type_idx:$type_idx-1;
$title = new title("Individual Portfolio Performance (" . $portfolio_type[$type_idx] . ")");
$title->set_style( "{font-size: 25px; font-family: Calibri; font-weight: bold; color: #121212; text-align: center;}" );
$chart->set_title( $title );


// y axis properties
$y = new y_axis();
$y_min = 0.0; $y_max = 0.0;
$y_min = floor(0.995*$amt_min);
$y_max = ceil(1.005*$amt_max);
$y->set_grid_colour('#EFEFEF');
$y->set_range($y_min, $y_max, round(($y_max-$y_min)/5.0));
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

// menu
$m = new ofc_menu("#E0E0FF", "#707070");
$m->values(array(new ofc_menu_item('Toggle view','toggle')));
//$chart->set_menu($m);
echo $chart->toPrettyString();
//echo $chart_data;
?>