<?php
	$files = scandir('.');
?>

<!doctype html>
<html>
  <head>
    <title>API V1</title>
  </head>
	<style>
		#cont {
			font-family: Arial, Helvetica, sans-serif;
			border-collapse: collapse;
			width: 100%;
		}

		#cont td, #customers th {
			border: 1px solid #ddd;
			padding: 8px;
		}

		#cont tr:nth-child(even){background-color: #f2f2f2;}

		#cont tr:hover {background-color: #ddd;}

		#cont th {
			padding-top: 12px;
			padding-bottom: 12px;
			text-align: left;
			background-color: #04AA6D;
			color: white;
		}
	</style>
  <body>
    <table id="cont">
			<thead>
				<tr>
					<th>&nbsp;&nbsp;API</th>
					<th>&nbsp;&nbsp;API DOC</th>
				</tr>
				
				<?php
					try {
						if (file_exists("./.api_state")) {
							$content = str_replace(
								array("\r\n", "\n", "\r"),
								'',
								file_get_contents('./.api_state')
							);
							$content = explode(',', preg_replace('/,$/', '', $content));
							$apiArr = array();
							foreach($content as $key => $value) {
								$value = explode(':', $value);
								$fileName = $value[0];
								$apiState = $value[1];
								$docState = $value[2];
								$apiArr[$fileName] = array(
									'state' => array(
										'api' => $apiState,
										'doc' => $docState
									)
								);
							}
						}
					} catch (Exception $e) {
						echo "Error: " . $e->getMessage();
					}

					foreach($files as $file) {
					if (!preg_match('/^\./', $file) && !preg_match('/^(readme|info)\.php$/', $file)
								&& $file != "." && $file != ".." && $file != "Includes") {
				?>

				<tr>
					<td>
					<?php 
						if (isset($apiArr)) {
							if (isset($apiArr[$file])) {
								echo '[' . $apiArr[$file]['state']['api'] . '] ';
							} else {
								$appendLine = PHP_EOL . $file . ':' . 'TODO' . ':' . 'TODO,' . PHP_EOL;
								file_put_contents('./.api_state', $appendLine, FILE_APPEND);
								echo '[TODO]';
							}
						}
						echo $file; 
					?>
					</td>
					<td>
						<?php 
						  if (isset($apiArr)) {
								if (isset($apiArr[$file])) {
									echo '[' . $apiArr[$file]['state']['doc'] . '] ';
								} else {
									echo '[TODO]';
								}
							}
						?>
						<a href="<?php echo $file;?>/API_Doc.html">v1/<?php echo $file;?>/</a>
					</td>
				</tr>

				<?php
					}
				}
				?>

			</thead>
		</table>
  </body>
</html>