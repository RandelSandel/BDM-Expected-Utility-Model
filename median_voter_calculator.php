<?php
function calculateMedianVoterPosition($data) {
	//Calculate Weighted Median Position
	foreach($data as $actor) {														//Define Power Distribution
		$power = bcmul($actor['resources'], $actor['salience']);
		$power_distribution[] = array("position" => $actor['position'], "power" => $power);
	}
	
	if(is_array($power_distribution) && count($power_distribution) > 0) {			//Sort Power Distribution
		foreach(array_keys($power_distribution) as $key)
			$temp[$key] = $power_distribution[$key]['position'];
		($order = 'asc')? asort($temp) : arsort($temp);
		foreach(array_keys($temp) as $key)
			(is_numeric($key))? $sorted_power_distribution[] = $power_distribution[$key] : $sorted[$key] = $power_distribution[$key];
	}
	
	foreach($sorted_power_distribution as $sorted_position) {						//Define Consolidated List of Positions
		$sorted_positions[] = $sorted_position['position'];
	}
	$unique_positions = array_unique($sorted_positions);
	foreach($unique_positions as $unique_position) {
		$consolidated_positions[] = $unique_position;
	}
	
	$num_positions = count($consolidated_positions) - 1;							//Consolidate Power Distribution
	for($index = 0; $index <= $num_positions; ++$index) {
		$unique_position = $consolidated_positions[$index];
		foreach($sorted_power_distribution as $actor) {
			if(bccomp($actor['position'], $unique_position) == 0) {
				$consolidated_power_distribution[$index]['position'] = $actor['position'];
				if(isset($consolidated_power_distribution[$index]['power'])) {
					$consolidated_power_distribution[$index]['power'] = bcadd($consolidated_power_distribution[$index]['power'], $actor['power']);
				} else {
					$consolidated_power_distribution[$index]['power'] = $actor['power'];
				}
			}
		}
	}
	
	foreach($consolidated_power_distribution as $actor) {							//Define Cumulative Power Distribution
		if(isset($cumulative_power)) {
			$cumulative_power = bcadd($cumulative_power, $actor['power']);
		} else {
			$cumulative_power = $actor['power'];
		}
		$cumulative_power_distribution[] = array("position" => $actor['position'], "power" => $actor['power'], "cumulative power" => $cumulative_power);
	}
	
	$last_actor = end($cumulative_power_distribution);								//Find Total Power
	$total_power = $last_actor['cumulative power'];
	
	$distribution_midpoint = bcdiv($total_power, 2);								//Find Distribution Midpoint
	
	for($index = 0; !isset($first_position_after_midpoint); ++$index) {				//Find First Position After Midpoint
		$actor = $cumulative_power_distribution[$index];
		if(bccomp($actor['cumulative power'], $distribution_midpoint) == 1) {
			$first_position_after_midpoint = $actor['position'];
		}
	}
	
	foreach($cumulative_power_distribution as $actor) {
		if(bccomp($actor['cumulative power'], $distribution_midpoint) == -1) {		//Find Cumulative Power and Position Before Midpoint
			$position_before_midpoint = $actor['position'];
			$cumulative_power_before_midpoint = $actor['cumulative power'];
		}
		if(bccomp($actor['cumulative power'], $distribution_midpoint) == 1) {		//Find Cumulative Power and Position After Midpoint
			if(bccomp($first_position_after_midpoint,$actor['position']) == 0) {
				$position_after_midpoint = $actor['position'];
				if(isset($power_after_midpoint)) {
					$power_after_midpoint = bcadd($power_after_midpoint, $actor['power']);
				} else {
					$power_after_midpoint = $actor['power'];
				}
			}
		}
	}
	
	if(!isset($position_before_midpoint)) {											//Calculate Weighted Median Position
		$lowest_data = reset($cumulative_power_distribution);
		$weighted_median_position = $lowest_data['position'];
	}
	else {
		if(!isset($position_after_midpoint)) {
			$highest_data = end($cumulative_power_distribution);
			$weighted_median_position = $highest_data['position'];
		}
		else {
			$weighted_median_position = bcadd(bcmul(bcdiv(bcsub($distribution_midpoint, $cumulative_power_before_midpoint), $power_after_midpoint), bcsub($position_after_midpoint, $position_before_midpoint)), $position_before_midpoint);
		}
	}
	
	//Find Voter Position Closest to the Weighted Median Position
	foreach($consolidated_power_distribution as $actor) {							//Define List of Voter Positions
		$voter_positions[] = $actor['position'];
	}
	
	foreach($voter_positions as $voter_position) {									//Find Median Voter Position
		if(bccomp($voter_position, $weighted_median_position) <= 0) {
			$position_distance = bcsub($weighted_median_position, $voter_position);
		}
		else {
			$position_distance = bcsub($voter_position, $weighted_median_position);
		}
		$voter_position_distances[] = array("position" => $voter_position, "distance" => $position_distance);
		$voter_distances[] = $position_distance;
	}
	$smallest_voter_distance = min($voter_distances);
	
	$haystack = $voter_position_distances;
	$needle = $smallest_voter_distance;
	$index = 'distance';
	$aIt = new RecursiveArrayIterator($haystack);
    $it = new RecursiveIteratorIterator($aIt);
    while($it->valid()) {
        if (((isset($index) and ($it->key() == $index)) or (!isset($index))) and ($it->current() == $needle)) { 
            $median_voter_key = $aIt->key();
		}
		$it->next(); 
	}

	$median_voter_position = $voter_position_distances[$median_voter_key]['position'];
	return $median_voter_position;
}


function currentMedianVoterPosition($data) {
	//Calculate Current Median Voter Position
	$current_median_voter_position = calculateMedianVoterPosition($data);
	return $current_median_voter_position;
}


function improvedMedianVoterPosition($data, $focal_data, $rival_data) {	
	//Search for the Rival Actor in the Data and Replace its Position with that of the Focal Actor's Position
	$haystack = $data;
	$needle = $rival_data['name'];
	$index = 'name';
	$aIt = new RecursiveArrayIterator($haystack);
    $it = new RecursiveIteratorIterator($aIt);
    while($it->valid()) {
        if (((isset($index) and ($it->key() == $index)) or (!isset($index))) and ($it->current() == $needle)) { 
            $rival_key = $aIt->key();
		}
		$it->next(); 
	}
	$data[$rival_key]['position'] = $focal_data['position'];
	
	//Calculate Improved Median Voter Position
	$improved_median_voter_position = calculateMedianVoterPosition($data);
	return $improved_median_voter_position;
}


function worsenedMedianVoterPosition($data, $focal_data, $rival_data) {
	//Search for the Focal Actor in the Data and Replace its Position with that of the Rival Actor's Position
	$haystack = $data;
	$needle = $focal_data['name'];
	$index = 'name';
	$aIt = new RecursiveArrayIterator($haystack);
    $it = new RecursiveIteratorIterator($aIt);
    while($it->valid()) {
        if (((isset($index) and ($it->key() == $index)) or (!isset($index))) and ($it->current() == $needle)) {
            $focal_key = $aIt->key();
		}
		$it->next();
	}
	$data[$focal_key]['position'] = $rival_data['position'];
	
	//Calculate Worsened Median Voter Position
	$worsened_median_voter_position = calculateMedianVoterPosition($data);
	return $worsened_median_voter_position;
}
?>