<?php
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    header_remove("X-Powered-By");
    header("X-XSS-Protection: 1");
    header('X-Frame-Options: SAMEORIGIN'); 
    session_start ();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <title>Y0L0 CTF</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="/yoloctf/js/bootstrap.min.css">
  <link rel="stylesheet" href="style.css">
  <script src="/yoloctf/js/jquery.min.js"></script>
  <script src="/yoloctf/js/popper.min.js"></script>
  <script src="/yoloctf/js/bootstrap.min.js"></script>

  <script src="/yoloctf/js/moment.min.js"></script>
	<script src="/yoloctf/js/Chart.min.js"></script>
	<script src="/yoloctf/js/Chart_utils.js"></script>
	<style>
		canvas {
			-moz-user-select: none;
			-webkit-user-select: none;
			-ms-user-select: none;
		}
	</style>


</head>
<body>

<!--- Page Header  -->
<?php
    include "Parsedown.php";
    $Parsedown = new Parsedown();
	include 'header.php'; 

	
?>


<div class="container-fluid">
    <div class="row">
        <!--- Page TOC  -->
        <div class="col-md-auto">
            <?php include 'toc.php' ?>
        </div>

        <!--- Page Content  -->
        <div class="col">
        <div class="container">


       

<?php

function dumpUserFlagDataSet($uid){
	include "ctf_sql.php";
	$count=0;
	$query = "SELECT UID,CHALLID, fdate, isvalid, flag FROM flags WHERE UID='$uid';";
	if ($fresult = $mysqli->query($query)) {
		/* fetch object array */
		while ($frow = $fresult->fetch_assoc()) {
			//UID,CHALLID, fdate, isvalid, flag
			//var_dump($row);
			//printf ("%s (%s) (%s) (%s)</br>", $frow['UID'], $frow['flag'], $frow['isvalid'], $frow['fdate']);
			if ($frow['isvalid']) { 
				$chall = getChallengeById($frow['CHALLID']);
				if ($chall!=null){
					$count+=$chall['value'];
				} else {
					$count++;
				}
			}
			$dd = $frow['fdate'];
			$format = '%Y-%m-%d %H:%M:%S'; // 
			//$dd = '2019-05-18 15:32:15';
			//$d = strptime($dd , $format);
			$d = date_parse($dd);
			//$jsdate = "$d[tm_mon]/$d[tm_mday]/$d[tm_year] $d[tm_hour]:$d[tm_min]";
			$jsdate = "$d[month]/$d[day]/$d[year] $d[hour]:$d[minute]";
			//print_r($d);
			echo " { x: '$jsdate', y: $count},";
		}
		$fresult->close();
	}
}

function getNbUsers(){
	include "ctf_sql.php";
	
	$user_query = "SELECT count(*) as nbusers FROM users;";
	if ($user_result = $mysqli->query($user_query)) {
		$row = $user_result->fetch_assoc();
		//echo "Error: " . $mysqli->error . "<br>";
		//echo $row['nbusers'];
		return $row['nbusers'];
	}
	return 0;
}

function dumpFlagDataSet($pageId){
		include "ctf_sql.php";
		$min = $pageId*20;
		$max = $pageId*20+19;
		$user_query = "SELECT login, UID FROM users LIMIT $min, $max;";
		if ($user_result = $mysqli->query($user_query)) {
			while ($row = $user_result->fetch_assoc()) {
				$uid = $row['UID'];
				$login = $row['login'];
				if ($uid!="") {

					if ($_SESSION['login']===$login){
						$r = 240;
						$g = 20;
						$b = 80;
					} else {
						$r = rand(0, 88);
						$g = 40+rand(0, 80);
						$b = 40+rand(0, 80);
					}
					
					echo "{
						label: '$login',
						backgroundColor: color('rgb($r, $g, $b)').alpha(0.5).rgbString(),
						borderColor: 'rgb($r, $g, $b)',
						fill: false,
						data: [";					
					dumpUserFlagDataSet($uid);
					echo "],
					},";
				}
			}
		
			/* free result set */
			$user_result->close();
		}

		/* close connection */
		$mysqli->close();
	}
?>
        

<?php
	$nbusers = getNbUsers();
	$nbpages = floor($nbusers/20);

	for ($pageid = 0; $pageid <= $nbpages; $pageid++) { 
		echo "
			<div>
			<canvas id='canvas_$pageid'></canvas>
			</div>
		";
	}
	echo "
	<script>
		var timeFormat = 'MM/DD/YYYY HH:mm';

		function newDate(days) {
			return moment().add(days, 'd').toDate();
		}

		function newDateString(days) {
			return moment().add(days, 'd').format(timeFormat);
		}

		var color = Chart.helpers.color;
	";
	for ($pageid = 0; $pageid <= $nbpages; $pageid++) { 
		echo "
		var config_$pageid = {
			type: 'line',
			data: {
				labels: [],
				
				datasets: [	";			
 		dumpFlagDataSet($pageid);
		echo "
				]
			},
			options: {
				title: {
					text: 'Scoreboard'
				},
				scales: {
					xAxes: [{
						type: 'time',
						time: {
							parser: timeFormat,
							// round: 'day'
							tooltipFormat: 'll HH:mm'
						},
						scaleLabel: {
							display: true,
							labelString: 'Date'
						}
					}],
					yAxes: [{
						scaleLabel: {
							display: true,
							labelString: 'Flags'
						}
					}]
				},
			}
		};";
	}	
	echo "window.onload = function() {";
	for ($pageid = 0; $pageid <= $nbpages; $pageid++) { 
		echo "
			var ctx_$pageid = document.getElementById('canvas_$pageid').getContext('2d');
			//window.myLine = new Chart(ctx, config_0);
			l_$pageid = new Chart(ctx_$pageid, config_$pageid);
		};
		";
	}
?>	



	</script>

<?php
    
    
    function file_get_contents_curl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        //curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    if (isset($_SESSION['login'] )) {
        

            

    } else {
        //echo "Merci de vous connecter.";
    }



 
?>
         </div>
        </div>
    </div>
</div>


  
</body>
</html>




