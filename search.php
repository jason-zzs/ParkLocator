<?php
	
	$pdo = new PDO('mysql:host=127.0.0.1:57342;dbname=localdb', 'azure', '6#vWHD_$');
	
	
	// Create datalist options for suburb input field from park database
	function createSuburbOptions() {
		global $pdo;
		
		$suburbs = $pdo->query('SELECT Suburb FROM parks GROUP BY Suburb ORDER BY Suburb;');
		
		foreach ($suburbs as $suburb) {
			echo '<option value="'. $suburb['Suburb'] .'">'. $suburb['Suburb'] .'</option>';
		}
	}
	
	// Create datalist options for park name input field from park database
	function createNameOptions() {
		global $pdo;
		
		$names = $pdo->query('SELECT Suburb, Name FROM parks ORDER BY Name;');
		
		foreach ($names as $name) {
			echo '<option value="'. $name['Name'] .'" class="'. $name['Suburb'] .'">'. $name['Name'] .'</option>';
		}
	}
	
	// Create selections for minimum rating option field from park database
	function createRatingOptions() {
		echo '<option value="" selected>Select a min rating</option>';
			
		for ($rate = 0; $rate <= 5; $rate += 0.5) {
			echo '<option value="'. $rate .'"';
			echo '>'. $rate .'</option>';
		}
	}
	
?>

<!DOCTYPE html>
<html>

	<head>
	
		<link href="CSS/style.css" rel="stylesheet" type="text/css"/>
		<link href="CSS/searchStyle.css" rel="stylesheet" type="text/css"/>
		<script type="text/javascript" src="JS/searchScript.js"></script>
		<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA6gFjy8jz6k01nXpBTFU7HBZR02TXNK1Y&callback=defaultMap"></script>
		<title>
		Search
		</title>
	
	
	</head>
	
	<body onload="defaultMap(); getLocation();">
	
		<div id="wrapper">
		
			<div id="header">
				Park Locator
				<?php
				session_start();
				if (isset($_SESSION['username'])){
					echo '<button onclick="javascript:window.location.href=\'Logout.php\'">Logout</button>';
				}
				?>
			</div>
			
			<div id="menu">
				<a href="home.php">Home</a>
				<a class= "active" href="search.php">Search</a>
				<a href="Login.php">Login</a>
				<a href="Registration.php">Register</a>
			</div>
			
			<div id="main">
				<div id="map"></div>
				
				<div id="search">
					<p id="searchTitle">Search for a park:</p>
					
					<form method="get" action="processSearch.php">
					<div id="location">
						<p>Your Location is: </p>
						<p id="status"></p>
						<input type="text" name="lat" hidden>
						<input type="text" name="lon" hidden>
					</div>
					
					<input type="submit" id="nearSearch" name="search near parks" value="Search near parks" disabled>
					</form>
					<br>
					<br>
					<span>or</span>
					
					<form name="searchForm" onsubmit="return (searchValidation());" action="processSearch.php" method="get">
					<fieldset>
					<legend id="searchSubTitle">Search by options:</legend>
						<input type="text" list="suburb" name="suburb" placeholder="Enter a suburb">
						<datalist id="suburb">
							<?php
								createSuburbOptions();
							?>
						</datalist>
						<br>
						<input type="text" list="park" name="park" placeholder="Enter a park name">
						<datalist id="park">
							<?php
								createNameOptions();
							?>
						</datalist>
						<br>
						<select name="rating">
							<?php
								createRatingOptions();
							?>
						</select>
						<br><br>
						<input type="text" name="opLat" hidden>
						<input type="text" name="opLon" hidden>
						
						<input type="submit" id="optionSearch" value="Search">
					</fieldset>
					</form>
				</div>
			</div>
			
			<div id="footer">
				
			</div>
		
		</div>
	
	</body>
	
</html>