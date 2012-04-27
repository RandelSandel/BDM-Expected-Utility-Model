<?php

//Define Functions to Calculate Votes
function nonperceptualFocalVote($position_i, $position_j, $resources, $salience, $slope) {
	$utility_ii = bcsub(1, abs(bcsub(bcmul($slope, $position_i), bcmul($slope, $position_i))));
	$utility_ij = bcsub(1, abs(bcsub(bcmul($slope, $position_i), bcmul($slope, $position_j))));
	$intensity_ij = bcsub($utility_ii, $utility_ij);
	$vote_ij = bcmul($resources, bcmul($salience, $intensity_ij));
	return $vote_ij;
}

function nonperceptualRivalVote($position_i, $position_j, $resources, $salience, $slope) {
	$utility_ji = bcsub(1, abs(bcsub(bcmul($slope, $position_j), bcmul($slope, $position_i))));
	$utility_jj = bcsub(1, abs(bcsub(bcmul($slope, $position_j), bcmul($slope, $position_j))));
	$intensity_ij = bcsub($utility_ji, $utility_jj);
	$vote_ij = bcmul($resources, bcmul($salience, $intensity_ij));
	return $vote_ij;
}

function nonperceptualThirdVote($position_i, $position_j, $position_k, $resources, $salience, $slope) {
	$utility_ki = bcsub(1, abs(bcsub(bcmul($slope, $position_k), bcmul($slope, $position_i))));
	$utility_kj = bcsub(1, abs(bcsub(bcmul($slope, $position_k), bcmul($slope, $position_j))));
	$intensity_ij = bcsub($utility_ki, $utility_kj);
	$vote_ij = bcmul($resources, bcmul($salience, $intensity_ij));
	return $vote_ij;
}

function calculateRisk($data, $hypothetical_precision, $utility_interval) {
	//Find Maximum and Minimum Position
	foreach($data as $actor) {
		$positions[] = $actor['position'];
	}
	$min_position = min($positions);
	$max_position = max($positions);
	
	//Define Array of Games
	$num_actors = count($data) - 1;
	for($rival_index = 0; $rival_index <= $num_actors; ++$rival_index) {
		$rival_actor = $data[$rival_index];
		for($focal_index = 0; $focal_index <= $num_actors; ++$focal_index) {
			$focal_actor = $data[$focal_index];
			if($focal_actor['name'] != $rival_actor['name']) {
				$games[$rival_index][$focal_index][] = $data[$focal_index];
				$games[$rival_index][$focal_index][] = $data[$rival_index];
				for($third_index = 0; $third_index <= $num_actors; ++$third_index) {
					$third_actor = $data[$third_index];
					if($third_actor['name'] != $focal_actor['name'] and $third_actor['name'] != $rival_actor['name']) {
						$games[$rival_index][$focal_index][] = $data[$third_index];
					}
				}
			}
		}
	}
	
	foreach($games as $rival_games) {
		if(!isset($security_index)) {
			$security_index = 0;
		} else {
			++$security_index;
		}
		for($hypothetical_position = $min_position; bccomp($hypothetical_position, $max_position) <= 0; $hypothetical_position = bcadd($hypothetical_position, $hypothetical_precision)) {
			foreach($rival_games as $game) {
				//Remember Rival Actor's Actual Position
				if(!isset($actual_position)) {
					$actual_position = $game[1]['position'];
				}
				//Replace Rival Actor's Position with Hypothetical Position
				$game[1]['position'] = $hypothetical_position;
				
				//Calculate Slope
				foreach($game as $actor) {
					$current_positions[] = $actor['position'];
				}
				if($utility_interval == "zero_one") {
					$slope = bcdiv(1, bcsub(max($positions), min($positions)));
				}
				if($utility_interval == "negative_one_one") {
					$slope = bcdiv(1, bcdiv(bcsub(max($positions), min($positions)), 2));
				}
				
				//Calculate Votes
				$votes = NULL;
				$num_actors = count($game) - 1;
				for($index = 0; $index <= $num_actors; ++$index) {
					if($index == 0) {
						$votes[] = nonperceptualFocalVote(
							$game[0]['position'], 
							$game[1]['position'], 
							$game[0]['resources'], 
							$game[0]['salience'], 
							$slope);
					}
					if($index == 1) {
						$votes[] = nonperceptualRivalVote(
							$game[0]['position'], 
							$game[1]['position'], 
							$game[1]['resources'], 
							$game[1]['salience'], 
							$slope);
					}
					if($index > 1) {
						$votes[] = nonperceptualThirdVote(
							$game[0]['position'], 
							$game[1]['position'], 
							$game[$index]['position'], 
							$game[$index]['resources'], 
							$game[$index]['salience'], 
							$slope);
					}
				}

				//Challenge j
				$probability_resistance = $game[1]['salience'];
				$probability_no_resistance = bcsub(1, $probability_resistance);

				$absolute_vote_sum = 0;
				$vote_sum = 0;
				foreach($votes as $vote) {
					$absolute_vote_sum = bcadd($absolute_vote_sum, abs($vote));
					if(bccomp($vote, 0) == 1) {
						$vote_sum = bcadd($vote_sum, $vote);
					}
				}

				if(bccomp($absolute_vote_sum, 0) != 0) {
					$probability_winning = bcdiv($vote_sum, $absolute_vote_sum);
					$probability_losing = bcsub(1, $probability_winning);
				} else {
					$probability_winning = 0;
					$probability_losing = 0;
				}
				
				$utility_ij = bcsub(1, abs(bcsub(bcmul($slope, $game[0]['position']), bcmul($slope, $game[1]['position']))));
				$utility_success = bcsub(1, $utility_ij);
				$utility_failure = bcsub($utility_ij, 1);

				$expected_utility_winning = bcmul($probability_winning, $utility_success);
				$expected_utility_losing = bcmul($probability_losing, $utility_failure);
				$expected_utility_resistance = bcmul($probability_resistance, bcadd($expected_utility_winning, $expected_utility_losing));

				$expected_utility_no_resistance = bcmul($probability_no_resistance, $utility_success);

				$expected_utility_challenging = bcadd($expected_utility_resistance, $expected_utility_no_resistance);

				//Not Challenge j
				$probability_sq_remaining = 0.5;
				$probability_sq_changing = bcsub(1, $probability_sq_remaining);
				$probability_sq_improving = 0.5;
				$probability_sq_worsening = bcsub(1, $probability_sq_improving);

				$current_median_voter_position = currentMedianVoterPosition($game);
				//$improved_median_voter_position = improvedMedianVoterPosition($game, $game[0], $game[1]);
				$worsened_median_voter_position = worsenedMedianVoterPosition($game, $game[0], $game[1]);
				$improved_median_voter_position = $worsened_median_voter_position;

				$utility_im_current = bcsub(1, abs(bcsub(bcmul($slope, $game[0]['position']), bcmul($slope, $current_median_voter_position))));
				//$utility_im_improved = bcsub(1, abs(bcsub(bcmul($slope, $game[0]['position']), bcmul($slope, $improved_median_voter_position))));
				$utility_im_worsened = bcsub(1, abs(bcsub(bcmul($slope, $game[0]['position']),bcmul($slope, $worsened_median_voter_position))));
				$utility_im_improved = $utility_im_worsened;
				$utility_sq_remaining = 0;
				$utility_sq_improving = bcsub($utility_im_improved, $utility_im_current);
				$utility_sq_worsening = bcsub($utility_im_worsened, $utility_im_current);

				$expected_utility_sq_improving = bcmul($probability_sq_improving, $utility_sq_improving);
				$expected_utility_sq_worsening = bcmul($probability_sq_worsening, $utility_sq_worsening);
				$expected_utility_sq_changing = bcmul($probability_sq_changing, bcadd($expected_utility_sq_improving, $expected_utility_sq_worsening));

				$expected_utility_sq_remaining = bcmul($probability_sq_remaining, $utility_sq_remaining);

				$expected_utility_not_challenging = bcadd($expected_utility_sq_changing, $expected_utility_sq_remaining);

				//Challenge or Not Challenge j
				if(isset($expected_utility)) {
					$expected_utility = bcadd($expected_utility, bcsub($expected_utility_challenging, $expected_utility_not_challenging));
				} else {
					$expected_utility = bcsub($expected_utility_challenging, $expected_utility_not_challenging);
				}

				$name = $game[1]['name'];
				$resources = $game[1]['resources'];
				$salience = $game[1]['salience'];
			}
			$security[] = array("position" => $game[1]['position'], "expected utility" => $expected_utility);
			$data_security_profiles[$security_index] = array(
				"name" => $name, 
				"resources" => $resources, 
				"salience" => $salience, 
				"position" => $actual_position, 
				"security" => $security);
			$expected_utility = 0;
		}
		unset($actual_position);
		unset($security);
	}

	foreach($data_security_profiles as $actor) {
		foreach($actor['security'] as $security) {
			$security_profile[] = $security['expected utility'];
			if(bccomp($security['position'], $actor['position']) == 0) {
				$current_security = $security['expected utility'];
			}
		}
		$max_security = max($security_profile);
		$min_security = min($security_profile);
		$raw_risk = bcdiv(bcsub(bcsub(bcmul(2, $current_security), $max_security), $min_security), bcsub($max_security, $min_security));
		$risk = bcdiv(bcsub(1, bcdiv($raw_risk, 3)),bcadd(1, bcdiv($raw_risk, 3)));
		$computed_data[] = array(
			"name" => $actor['name'], 
			"resources" => $actor['resources'], 
			"salience" => $actor['salience'], 
			"position" => $actor['position'], 
			"risk" => $risk);
		unset($security_profile);
	}
	return $computed_data;
}
?>