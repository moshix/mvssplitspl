<?php
/*
MIT License

Copyright (c) 2020 David Asta

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

# **** DEFINES ****
define('APP_NAME', 'MVS Spool Viewer');
define('APP_VERSION', 'v1.0.0');

# **** GLOBAL VARIABLES ****
$sysname = exec("uname -n");
$path = "./MVS";   ######## CHANGE THIS TO YOUR DIRECTORY FOLDER
$directories = array();
$types = array();
$dirs = glob($path . '/*', GLOB_ONLYDIR);

foreach($dirs as $dir){
	array_push($directories, $dir);
	array_push($types, 'd');
	$subdirs = glob($dir . '/*', GLOB_ONLYDIR);
	foreach($subdirs as $subdir){
		array_push($directories, $subdir);
		array_push($types, 's');
	}
}

# **** FUNCTIONS ****
############################################################
# Returns a date formatted as dd/mm/yy
function formatDate($date){
	return substr($date, 4, 2) . '/' . substr($date, 2, 2) . '/' . substr($date, 0, 2);
}

############################################################
# Returns a date formatted as dd/mm/yy
function formatTime($time){
	return substr($time, 0, 2) . ':' . substr($time, 2, 2) . ':' . substr($time, 4, 2);
}

############################################################
# Returns a description for job types J, S and T
function getJobType($info){
	$type = substr($info, 0, 1);
	
	if($type == 'J') return 'JOB';
	elseif($type == 'S') return 'STC';
	elseif($type == 'T') return 'TSU';
}

############################################################
# Returns the job number (basically strips out the first
#   character, which is the job type
function getJobNumber($info){
	return substr($info, 1, strlen($info) - 1);
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<link rel='icon' type='image/gif' href='/favicon.ico' />
		<link rel="stylesheet" type="text/css" href="style.css">
		<?php echo("<title>" . APP_NAME . "</title>"); ?>
	</head>
	<body>
		<!-- HEADER -->
		<div class="title">
			<center>
				<table>
					<tr><td class="app"><?php echo APP_NAME . ' ' . APP_VERSION; ?></td></tr>
					<tr><td class="app"><h1><?php echo($sysname); ?></h1></td></tr>
				</table>
			</center>
		</div> <!-- HEADER end -->
		<!-- SPOOL FILES -->
		<div>
		<table>
			<tr>
				<th>Date</th>
				<th>Time</th>
				<th>Class</th>
				<th>Printer</th>
				<th>Room</th>
				<th>Job Type</th>
				<th>Job Number</th>
				<th>Job Name</th>
				<th>File</th>
				<th></th>
			</tr>
			<?php
				for($i = 0; $i < sizeof($directories); $i++){
					$dir = $directories[$i];
					$dirname = explode("/", $dir);
					$dirlast = $dirname[sizeof($dirname) - 1];
					echo("<tr>");
					if($types[$i] == 's'){
						echo("<td></td>");
					}

					$files = array_diff(scandir($dir), array('.', '..'));
					$filecount = sizeof($files);

					if($filecount == 0){
						if($types[$i] == 's') echo("<td class='dir' colspan='8'>" . $dirlast . " (" . $filecount .  ")" . "</td>");
						else echo("<td class='dir' colspan='9'>" . $dirlast . " (" . $filecount .  ")" . "</td>");

						echo("<td><a href='purge.php?ftype=dir&fname=" . $dir . "'><img src='purge.png' width = '20' height = '20'/></a></td>");
					}else{
						echo("<td class='dir' colspan='10'>" . $dirlast . " (" . $filecount .  ")" . "</td>");
					}
					echo("</tr>");

					foreach($files as $filename) {
						if(strpos($filename, 'pdf') > 0){
							$jobinfo = explode("_", $filename);
							$jobdate = formatDate($jobinfo[0]);
							$jobtime = formatTime($jobinfo[1]);
							$jobclass = $jobinfo[2];
							$jobprntroom = explode("-",$jobinfo[3]);
							$jobprinter = $jobprntroom[0];
							if(count($jobprntroom) > 1){
								$jobroom = $jobprntroom[1];
							}else{
								$jobroom = '';
							}
							$jobtype = getJobType($jobinfo[4]);
							$jobnumber = getJobNumber($jobinfo[4]);
							$jobnameext = explode(".", $jobinfo[5]);
							$jobname = $jobnameext[0];
							$jobextension = $jobnameext[1];

							echo("<tr>");
							echo("<td>" . $jobdate . "</td>");
							echo("<td>" . $jobtime . "</td>");
							echo("<td align='center'>" . $jobclass . "</td>");
							echo("<td>" . $jobprinter . "</td>");
							echo("<td>" . $jobroom . "</td>");
							echo("<td align='center'>" . $jobtype . "</td>");
							echo("<td align='center'>" . $jobnumber . "</td>");
							echo("<td>" . $jobname . "</td>");
							echo("<td>" . "<a href='" . $dir . "/" . $filename . "' target='_blank'>" . $filename . "</td>");
							echo("<td><a href='purge.php?ftype=file&fname=" . $dir . "/" . $filename . "'><img src='purge.png' width = '20' height = '20'/></a></td>");
							echo("</tr>");
						}
					}
				}
			?>
		</table>
		</div> <!-- SPOOL FILES end -->
	</body>
</html>