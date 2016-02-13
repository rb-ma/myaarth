<?php

include 'php-ofc-library/open-flash-chart.php';

//Connect To Database
$hostname='aarthadmin2.db.8935638.hostedresource.com';
$username='aarthadmin2';
$password='Aarth!Log1n';
$dbname='aarthadmin2';

// connect to the database
$con=mysql_connect($hostname,$username, $password);
if(!$con){ 
	system("date >> $log_name");
	system("echo \"$script_name: Unable to connect to databse\n\n\" >> $log_name");
	DIE ('Unable to connect to database! Please try again later.' . "\n");
}
mysql_select_db($dbname, $con);

$g = new graph();
$g->title('Portfolio Performance', '{font-size: 20px; color: #000000; font-family: Calibri}');

$data = array();
$dates = array();

$id = 21;
$max_obs = 10;
$result = mysql_query("SELECT * FROM graph_source WHERE user_id=$id");

$num_rows = mysql_num_rows($result);
if($num_rows <= 20){
	$num_obs = 1;
} else if($num_rows >= 200){
	$num_obs = $num_rows/$max_obs;
}
$count = 0;

while($row = mysql_fetch_array($result)){
	//print_r($row);
	$added = false;
	if(($count % $num_obs) == 0) {
		$data[] = $row['amount'];
		$dates[] = substr($row['last_update'], 0, 10);
		$added = true;
	}
	$count++;
	if($count == $num_rows && !$added){
		$data[] = $row['amount'];
		$dates[] = substr($row['last_update'], 0, 10);
		$added = true;
	}
}

$min = 0.85 * min($data);
$max = 1.15 * max($data);

// set the data
$g->set_data($data);

// new line_dot object
$g->line_dot(3, 5, '#666666', "Total Value");

// x-axis
$g->set_x_labels(dates);
$g->set_x_label_style(10, '#0000FF', 2);

// y-axis
$g->set_y_max($max);
$g->set_y_min($min);
$g->y_label_steps(10);
$g->set_y_legend('Amount ($)', 12, '#336666');

echo $g->render;
?>