<?php
	session_start();
	
	$title = "Rosetta Admin - Login";
	$stage = "login";
	
    $configUrl = dirname(__FILE__) . '/rosetta-config.php';
	require $configUrl;
	
    if(array_key_exists('isLoggedIn', $_SESSION)) {
        if($_SESSION['isLoggedIn'] != "true") {
			die("Authentication failed.");
		}
	}
	
	$selectedDictionary = "";
	
	//process previously submitted form data
	if(isset($_POST) && !empty($_POST)) {
		$stage = $_POST['stage'];
		
		switch($stage) {
			case "login": {
				$title = "Rosetta Admin - Select Dictionary";
				$txtPassword = $_POST['txtPassword'];
				
				if($txtPassword == ROSETTA_PASSWORD) { 
					$stage = "selectDictionary"; //display the next stage
				}
				
				break;
			}
			
			case "selectDictionary": {
				$title = "Rosetta Admin - Edit Data";

				$stage = "editKeys";
				$selectedDictionary = ROSETTA_DICT_PATH . $_POST['ddlDictionary'];
				
				break;
			}
			
			case "editKeys": {
				$title = "Rosetta Admin - Select Dictionary";
				$stage = "selectDictionary";

				$selectedDictionary = ROSETTA_DICT_PATH . $_POST['hDictionary'];
				//echo $selectedDictionary . "<br/>";

				$xml = simplexml_load_file($selectedDictionary);
				
				$keys = array_keys($_POST);
				foreach($keys as $key) {
					$value = $_POST[$key];

					$string = $xml->xpath('/codex/strings/string[@id = "' . $key . '"]');            
					$string[0][0]= $value;					

				}

				//write back to the file
				if(!$xml->asXML($selectedDictionary))
					die("Couldn't update file");

				break;
			}
		}
	}
?>

<html>
	<head>
		<title><?php echo $title; ?></title>
	</head>
	
	<body>
		<h1><?php echo $title; ?></h1>
		
		<form action = "<?php echo $_SERVER['PHP_SELF']; ?>" method = "POST">
			<input type = "hidden" id = "stage" name = "stage" value = "<?php echo $stage; ?>"/>
<?php
		switch($stage) {
			case "login": {
?>
				<label for = 'txtPassword'>Password: </label>
				<input id = 'txtPassword' name = 'txtPassword' type = 'password'/>
				<input type = 'submit' name = 'submit' value = 'Next'/>
<?php				
				break;
			}
			
			case "selectDictionary": {

				$files = scandir(ROSETTA_DICT_PATH);
				//var_dump($files);
?>
				<select name = 'ddlDictionary' id = 'ddlDictionary'>
<?php
					foreach($files as $file) {

						if($file == "." || $file == "..")
							continue;
?>
						<option><?php echo $file; ?></option>
<?php
					}
?>
				</select>
				<input type = "submit" name = "submit" value = "Next"/>
<?php
				break;
			}
			
			case "editKeys": {
				//echo "Path: $selectedDictionary <br/>";

				$xml = simplexml_load_file($selectedDictionary);
				$strings = $xml->strings;

?>
				<table>
					<tr>
						<th>ID</th>
						<th>Value</th>
					</tr>
<?php
				foreach($strings->children() as $string) {
					if($string['type'] == 'hidden') //skip hidden elements
						continue;
?>
					<tr>
						<td><?php echo $string['id']; ?></td>
						<td><input id = "<?php echo $string['id']; ?>" name = "<?php echo $string['id']; ?>" type = "text" value = "<?php echo $string; ?>"/></td>
					</tr>
<?php	
				}
?>
				</table>

				<input type = "hidden" name = "hDictionary" value = "<?php echo $_POST['ddlDictionary']; ?>"/>
				<input type = "submit" name = "submit" value = "Save and return"/>
<?php
				break;
			}
		}
?>
		</form>
	</body>
</html>