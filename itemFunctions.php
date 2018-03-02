<?php
	
function displayReviews(){
	
	$pdo = new PDO('mysql:host=127.0.0.1:57342;dbname=localdb', 'azure', '6#vWHD_$');
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	try {
			//echo $_SESSION['park id'];
			$entry = $pdo->prepare('SELECT * from reviews WHERE itemid = :itemid');
			$entry->bindValue(':itemid', $_SESSION['park id']);
			$entry->execute();
			$reviewCount = $entry->rowCount();
			
			
			
	}	catch (PDOException $e) {
			echo $e->getMessage();
			
		}
	$defaultReviews = array("0" => "<div>
						<h4>User: laens464 &nbsp &nbsp &nbsp &nbsp Rating: 4 / 5</h4>
						This is a nice park!
						</div>", "1" => "<div>
						<h4>User: rrrrs0 &nbsp &nbsp &nbsp &nbsp Rating: 3 / 5</h4>
						The park is fine.
						</div>", "2" => "<div>
						<h4>User: jmie_dsd &nbsp &nbsp &nbsp &nbsp Rating: 3.5 / 5</h4>
						Good environment.
						</div>");
	//maxiumum of three reviews are displayed on the item page
	if ($reviewCount >= 3){
		$reviewsNeeded = 3;
	} else {
		$reviewsNeeded = $reviewCount;
	}
	$defaultsNeeded = 3-$reviewCount;
	$Reviews = array();
	$Users = array();
	$Ratings = array();
		
	foreach($entry as $field){
				$Reviews[] = $field['review'];
				$Users[] = $field['username'];
				$Ratings[] = $field['rating'];
	}
	
	$slicedReviews = array_slice($Reviews, 0, $reviewsNeeded);
	$slicedUsers = array_slice($Users, 0, $reviewsNeeded);
	$slicedRatings = array_slice($Ratings, 0, $reviewsNeeded);
	
	

	if ($reviewsNeeded > 0){
		for ($x = 0; $x < $reviewsNeeded; $x++){
		echo "<div> <h4> User:\"$slicedUsers[$x]\"";
		echo "&nbsp &nbsp &nbsp &nbsp";
		echo "Rating:\"$slicedRatings[$x]\"";
		echo "</h4>\"$slicedReviews[$x]\"";
		echo "</div>";
	
		}
	}
	
	if ($defaultsNeeded > 0){
		for ($x = 0; $x < $defaultsNeeded; $x++){
			echo $defaultReviews[$x];
		
		
		}
	
	}
}
	
	

?>