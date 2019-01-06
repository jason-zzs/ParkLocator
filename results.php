<?php
	
	$pdo = new PDO('mysql:host=127.0.0.1:8080;dbname=localdb', 'azure', '6#vWHD_$');
	
	// The algorithm to calculate the distance between two locations in kilometres
	function getDistance($lat1, $lon1, $lat2, $lon2) {
		$R = 6371;
		$dLat = deg2rad($lat2 - $lat1);
		$dLon = deg2rad($lon2 - $lon1);
		$a = sin($dLat / 2) * sin($dLat / 2) +
			cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
			sin($dLon / 2) * sin($dLon / 2);
		$c = 2 * atan2(sqrt($a), sqrt(1-$a)); 
		$d = $R * $c;
		
		return $d;
	}
	
	// Get all parks' id which are near the user
	function getNearParksId() {
		global $pdo;
		$allParks = $pdo->query('SELECT id, Latitude, Longitude FROM parks ORDER BY Name;');
		$ids = array();
		
		// Choose only parks within 2 kilometres of the user
		foreach ($allParks as $park) {
			if (getDistance($park['Latitude'], $park['Longitude'], $_GET['lat'], $_GET['lon']) <= 2) array_push($ids, $park['id']);
		}
		
		return $ids;
	}
	
	// Calculate the average rating of an item
	function getRating($id) {
		global $pdo;
		$ratings = $pdo->query('SELECT rating FROM reviews WHERE itemid = '. $id .';');
		$numRatings = count($ratings);
		
		if ($numRatings == 0) return 0;
		else {
			$rates = 0;
			foreach ($ratings as $rating) {
				$rates += $rating['rating'];
			}
			return ($rates / $numRatings);
		}
	}
	
	// Get all parks satisfy the searching options as array of results 
	function getResults() {
		global $pdo;
		$results = array();
		
		// When the users click to search near parks
		if (isset($_GET['lat']) && isset($_GET['lat'])) {
			$ids = getNearParksId();
			
			foreach ($ids as $id) {
				$rawResults = $pdo->query('SELECT id, Name, Suburb, Street, Latitude, Longitude FROM parks WHERE parks.id = '. $id. ' ORDER BY Name;');
				// Write all data corresponding to the id to the results
				foreach ($rawResults as $result) {
					array_push($results, $result);
				}
			}
		}
		// Whe the user choose te search by options
		else {
			$rawResults = array();
			$results = array();
			
			$_GET['suburb'] = htmlspecialchars($_GET['suburb']);
			$_GET['park'] = htmlspecialchars($_GET['park']);
			
			// When both the suburb and park fields are filled
			if (!empty($_GET['suburb']) && !empty($_GET['park'])) {
				$rawResults = $pdo->query('SELECT id, Name, Suburb, Street, Latitude, Longitude FROM parks WHERE Suburb LIKE "%'. $_GET['suburb'] .'%" AND Name LIKE "%'. $_GET['park'] .'%" ORDER BY Name;');
			}
			// When only the suburb field is filled
			else if (!empty($_GET['suburb'])) {
				$rawResults = $pdo->query('SELECT id, Name, Suburb, Street, Latitude, Longitude FROM parks WHERE Suburb LIKE "%'. $_GET['suburb'] .'%" ORDER BY Name;');
			}
			// When only the park field is filled
			else if (!empty($_GET['park'])) {
				$rawResults = $pdo->query('SELECT id, Name, Suburb, Street, Latitude, Longitude FROM parks WHERE Name LIKE "%'. $_GET['park'] .'%" ORDER BY Name;');
			}
			// When neither suburb nor park field is filled (only rating field is filled)
			else $rawResults = $pdo->query('SELECT id, Name, Suburb, Street, Latitude, Longitude FROM parks ORDER BY Name;');
			
			// Address the results with ratings
			if (isset($_GET['rating']) && count($rawResults) > 0) {
				$minRating = $_GET['rating'];
				// When the rating field is not filled, it equals zero minimum rating
				if (empty($minRating)) $minRating = 0;
				
				// Screen all results and leave those with ratings equivalent or greater than the minimum rating
				foreach ($rawResults as $result) {
					if (getRating($result['id']) >= $minRating) array_push($results, $result);
				}
			}
			else $results = $rawResults;
		}
		
		return $results;
	}
	
	// Count the number of results
	function getNumResults() {
		$results = getResults();
		$counter = 0;
		
		foreach ($results as $result) {
			$counter++;
		}
		
		return $counter;
	}
	
	// Display how many results in the page
	function showNumResults() {
		$num = getNumResults();
		if ($num > 1) echo '<span>There are '. $num .' results:</span>';
		else if ($num == 1) echo '<span>There is '. $num .' result:</span>';
		else echo '<span>There is no result</span>';
	}
	
	// Create a list of results in a table
	function createResults() {
		$results = getResults();
		
		if (count($results) != 0) {
			echo '<tr>';
			echo '<th>Park Name</th>';
			echo '<th>Suburb</th>';
			echo '<th>Street</th>';
			echo '<th>Distance</th>';
			echo '</tr>';
		
			foreach ($results as $result) {
				$lat = $result['Latitude'];
				$lon = $result['Longitude'];
				
				// Get distance base on location coordination
				if (isset($_GET['opLat']) && isset($_GET['opLon'])) {
					if (!empty($_GET['opLat']) && !empty($_GET['opLon'])) $dist = round(getDistance($lat, $lon, $_GET['opLat'], $_GET['opLon']), 3).' km';
					else $dist = 'N/A'; 
				}
				else if (isset($_GET['lat']) && isset($_GET['lon'])) {
					if (!empty($_GET['lat']) && !empty($_GET['lon'])) $dist = round(getDistance($lat, $lon, $_GET['lat'], $_GET['lon']), 3).' km';
					else $dist = 'N/A'; 
				}
				// When geolocation is uable
				else $dist = 'N/A';
				
				// Write a set of table rows
				echo '<tr class="corRows" id="'. $lat .','. $lon .'">';
				echo '<td class="parks" id="'. $result['Name'] .','. $result['id'] .'"><a href="item.php?id='. $result['id'] .'&distance='. $dist .'&lat='. $lat .'&lon='. $lon .'">'. $result['Name'] .'</a></td>';
				echo '<td class="suburbs" id="'. $result['Suburb'] .'">'. $result['Suburb'] .'</td>';
				echo '<td class="streets" id="'. $result['Street'] .'">'. $result['Street'] .'</td>';
				echo '<td class="distances" id="'. $dist .'">'. $dist .'</td>';
				echo '</tr>';
			}
		}
		// When there's no result found
		else {
			echo '<span>No park is found</span>';
		}
	}
	
	// Write the onload statement to html
	function createOnload() {
		// When location is obtained
		if (isset($_GET['lat']) && isset($_GET['lon'])) {
			if (!empty($_GET['lat']) && !empty($_GET['lon'])) {
				$lat = $_GET['lat'];
				$lon = $_GET['lon'];
			}
			// When JavaScript is fail to disable search button and the location is empty, use Brisbane city's coordination as centre
			else {
				$lat = -27.46967;
				$lon = 153.02508;
			}
		}
		else if (isset($_GET['opLat']) && isset($_GET['opLon'])) {
			if (!empty($_GET['opLat']) && !empty($_GET['opLon'])) {
				$lat = $_GET['opLat'];
				$lon = $_GET['opLon'];
			}
			// When location is unavailable, use Brisbane city's coordination
			else {
				$lat = -27.46967;
				$lon = 153.02508;
			}
		}
		// When location is unavailable, use Brisbane city's coordination
		else {
			$lat = -27.46967;
			$lon = 153.02508;
		}
		
		echo 'onload="mapForRes('. $lat .', '. $lon .');"';
	}
	
?>

<!DOCTYPE html>
<html>

	<head>
	
		<link href="CSS/style.css" rel="stylesheet" type="text/css"/>
		<link href="CSS/resStyle.css" rel="stylesheet" type="text/css"/>
		<script type="text/javascript" src="JS/resultScript.js"></script>
		<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA6gFjy8jz6k01nXpBTFU7HBZR02TXNK1Y&callback=mapForRes"></script>
		<title>
			Results
		</title>
	
	
	</head>
	
	<body <?php createOnload(); ?>>
	
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
			
			<div id = "contentres">
				<div id="mapres"></div>
				
				<div id="results">
				<p>Results</p>
				<?php showNumResults(); ?>
				<br>
				<div id="resultsTable">
				<table>
					<?php
						createResults();
					?>
				</table>
				</div>
				
				<button onclick="javascript:window.location.href='search.php'">Search again</button>
				</div>
			</div>
			
			<div id="footer">
				
			</div>
		
		</div>
	
	</body>
	
</html>