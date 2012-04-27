<?php

//Define Functions to Calculate Votes and Utilities
function perceptualFocalVote($position_i, $position_j, $risk, $resources, $salience, $slope) {
	$distance_ii = abs(bcsub(bcmul($slope, $position_i), bcmul($slope, $position_i)));
	if(bccomp($distance_ii, 0) == 0) {
		$utility_ii = 1;
	} else {
		$utility_ii = trim(`echo "1 - (e($risk * l($distance_ii)))" | bc -l`);
	}
	$distance_ij = abs(bcsub(bcmul($slope, $position_i), bcmul($slope, $position_j)));
	if(bccomp($distance_ij, 0) == 0) {
		$utility_ij = 1;
	} else {
		$utility_ij = trim(`echo "1 - (e($risk * l($distance_ij)))" | bc -l`);
	}
	$intensity_ij = bcsub($utility_ii, $utility_ij);
	$vote_ij = bcmul($resources, bcmul($salience, $intensity_ij));
	return $vote_ij;
}

function perceptualRivalVote($position_i, $position_j, $risk, $resources, $salience, $slope) {
	$distance_ji = abs(bcsub(bcmul($slope, $position_j), bcmul($slope, $position_i)));
	if(bccomp($distance_ji, 0) == 0) {
		$utility_ji = 1;
	} else {
		$utility_ji = trim(`echo "1 - (e($risk * l($distance_ji)))" | bc -l`);
	}
	$distance_jj = abs(bcsub(bcmul($slope, $position_j), bcmul($slope, $position_j)));
	if(bccomp($distance_jj, 0) == 0) {
		$utility_jj = 1;
	} else {
		$utility_jj = trim(`echo "1 - (e($risk * l($distance_jj)))" | bc -l`);
	}
	$intensity_ij = bcsub($utility_ji, $utility_jj);
	$vote_ij = bcmul($resources, bcmul($salience, $intensity_ij));
	return $vote_ij;
}

function perceptualThirdVote($position_i, $position_j, $position_k, $risk, $resources, $salience, $slope) {
	$distance_ki = abs(bcsub(bcmul($slope, $position_k), bcmul($slope, $position_i)));
	if(bccomp($distance_ki, 0) == 0) {
		$utility_ki = 1;
	} else {
		$utility_ki = trim(`echo "1 - (e($risk * l($distance_ki)))" | bc -l`);
	}
	$distance_kj = abs(bcsub(bcmul($slope, $position_k), bcmul($slope, $position_j)));
	if(bccomp($distance_kj, 0) == 0) {
		$utility_kj = 1;
	} else {
		$utility_kj = trim(`echo "1 - (e($risk * l($distance_kj)))" | bc -l`);
	}
	$intensity_ij = bcsub($utility_ki, $utility_kj);
	$vote_ij = bcmul($resources, bcmul($salience, $intensity_ij));
	return $vote_ij;
}

function perceptualUtility($position_i, $position_j, $risk, $slope) {
	$distance_ij = abs(bcsub(bcmul($slope, $position_i), bcmul($slope, $position_j)));
	if(bccomp($distance_ij, 0) == 0) {
		$utility_ij = 1;
	} else {
		$utility_ij = trim(`echo "1 - (e($risk * l($distance_ij)))" | bc -l`);
	}
	return $utility_ij;
}

function simulate($data, $utility_interval) {
	//Calculate Slope
	foreach($data as $actor) {
		$positions[] = $actor['position'];
	}
	if($utility_interval == "zero_one") {
		$slope = bcdiv(1, bcsub(max($positions), min($positions)));
	}
	if($utility_interval == "negative_one_one") {
		$slope = bcdiv(1, bcdiv(bcsub(max($positions), min($positions)), 2));
	}
	
	//Calculate Current Median Voter Position
	$current_median_voter_position = currentMedianVoterPosition($data);
	
	//Define Array of Games
	$num_actors = count($data) - 1;
	for($focal_index = 0; $focal_index <= $num_actors; ++$focal_index) {
		$focal_actor = $data[$focal_index];
		for($rival_index = 0; $rival_index <= $num_actors; ++$rival_index) {
			$rival_actor = $data[$rival_index];
			if($focal_actor['name'] != $rival_actor['name']) {
				$games[$focal_index][$rival_index][] = $data[$focal_index];
				$games[$focal_index][$rival_index][] = $data[$rival_index];
				for($third_index = 0; $third_index <= $num_actors; ++$third_index) {
					$third_actor = $data[$third_index];
					if($third_actor['name'] != $focal_actor['name'] and $third_actor['name'] != $rival_actor['name']) {
						$games[$focal_index][$rival_index][] = $data[$third_index];
					}
				}
			}
		}
	}
	
	foreach($games as $focal_games) {
		foreach($focal_games as $game) {
			//Calculate Votes from Focal Actor's (i) Perspective
			//i vs. j
			$votes_i_ij = NULL;
			$num_actors = count($game) - 1;
			for($index = 0; $index <= $num_actors; ++$index) {
				if($index == 0) {
					$votes_i_ij[] = perceptualFocalVote(
						$game[0]['position'], 
						$game[1]['position'], 
						$game[0]['risk'], 
						$game[0]['resources'], 
						$game[0]['salience'], 
						$slope);
				}
				if($index == 1) {
					$votes_i_ij[] = perceptualRivalVote(
						$game[0]['position'], 
						$game[1]['position'], 
						$game[0]['risk'], 
						$game[1]['resources'], 
						$game[1]['salience'], 
						$slope);
				}
				if($index > 1) {
					$votes_i_ij[] = perceptualThirdVote(
						$game[0]['position'], 
						$game[1]['position'], 
						$game[$index]['position'], 
						$game[0]['risk'], 
						$game[$index]['resources'], 
						$game[$index]['salience'], 
						$slope);
				}
			}
			
			//j vs. i
			$votes_i_ji = NULL;
			for($index = 0; $index <= $num_actors; ++$index) {
				if($index == 1) {
					$votes_i_ji[] = perceptualFocalVote(
						$game[1]['position'], 
						$game[0]['position'], 
						$game[0]['risk'], 
						$game[1]['resources'], 
						$game[1]['salience'], 
						$slope);
				}
				if($index == 0) {
					$votes_i_ji[] = perceptualRivalVote(
						$game[1]['position'], 
						$game[0]['position'], 
						$game[0]['risk'], 
						$game[0]['resources'], 
						$game[0]['salience'], 
						$slope);
				}
				if($index > 1) {
					$votes_i_ji[] = perceptualThirdVote(
						$game[1]['position'], 
						$game[0]['position'], 
						$game[$index]['position'], 
						$game[0]['risk'], 
						$game[$index]['resources'], 
						$game[$index]['salience'], 
						$slope);
				}
			}
			
			//Calculate Votes from Rival Actor's (j) Perspective
			//i vs. j
			$votes_j_ij = NULL;
			for($index = 0; $index <= $num_actors; ++$index) {
				if($index == 0) {
					$votes_j_ij[] = perceptualFocalVote(
						$game[0]['position'], 
						$game[1]['position'], 
						$game[1]['risk'], 
						$game[0]['resources'], 
						$game[0]['salience'], 
						$slope);
				}
				if($index == 1) {
					$votes_j_ij[] = perceptualRivalVote(
						$game[0]['position'], 
						$game[1]['position'], 
						$game[1]['risk'], 
						$game[1]['resources'], 
						$game[1]['salience'], 
						$slope);
				}
				if($index > 1) {
					$votes_j_ij[] = perceptualThirdVote(
						$game[0]['position'], 
						$game[1]['position'], 
						$game[$index]['position'], 
						$game[1]['risk'], 
						$game[$index]['resources'], 
						$game[$index]['salience'], 
						$slope);
				}
			}
			
			//j vs. i
			$votes_j_ji = NULL;
			for($index = 0; $index <= $num_actors; ++$index) {
				if($index == 1) {
					$votes_j_ji[] = perceptualFocalVote(
						$game[1]['position'], 
						$game[0]['position'], 
						$game[1]['risk'], 
						$game[1]['resources'], 
						$game[1]['salience'], 
						$slope);
				}
				if($index == 0) {
					$votes_j_ji[] = perceptualRivalVote(
						$game[1]['position'], 
						$game[0]['position'], 
						$game[1]['risk'], 
						$game[0]['resources'], 
						$game[0]['salience'], 
						$slope);
				}
				if($index > 1) {
					$votes_j_ji[] = perceptualThirdVote(
						$game[1]['position'], 
						$game[0]['position'], 
						$game[$index]['position'], 
						$game[1]['risk'], 
						$game[$index]['resources'], 
						$game[$index]['salience'], 
						$slope);
				}
			}
			
			//Calculate Expected Utility of Challenging or Not Challenging from Focal Actor's (i) Perspective
			//i vs. j
			//Challenge
			$probability_resistance = $game[1]['salience'];
			$probability_no_resistance = bcsub(1, $probability_resistance);

			$absolute_vote_sum = 0;
			$vote_sum = 0;
			foreach($votes_i_ij as $vote) {
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

			$utility_i_ij = perceptualUtility($game[0]['position'], $game[1]['position'], $game[0]['risk'], $slope);
			$utility_success = bcsub(1, $utility_i_ij);
			$utility_failure = bcsub($utility_i_ij, 1);

			$expected_utility_winning = bcmul($probability_winning, $utility_success);
			$expected_utility_losing = bcmul($probability_losing, $utility_failure);
			$expected_utility_resistance = bcmul($probability_resistance, bcadd($expected_utility_winning, $expected_utility_losing));

			$expected_utility_no_resistance = bcmul($probability_no_resistance, $utility_success);

			$expected_utility_challenging = bcadd($expected_utility_resistance, $expected_utility_no_resistance);

			//Not Challenge
			$probability_sq_remaining = 0.5;
			$probability_sq_changing = bcsub(1, $probability_sq_remaining);
			$probability_sq_improving = 0.5;
			$probability_sq_worsening = bcsub(1, $probability_sq_improving);

			//$improved_median_voter_position_ij = improvedMedianVoterPosition($game, $game[0], $game[1]);
			$worsened_median_voter_position_ij = worsenedMedianVoterPosition($game, $game[0], $game[1]);
			$improved_median_voter_position_ij = $worsened_median_voter_position_ij;

			$utility_im_current = perceptualUtility($game[0]['position'], $current_median_voter_position, $game[0]['risk'], $slope);
			//$utility_im_improved = perceptualUtility($game[0]['position'], $improved_median_voter_position_ij, $game[0]['risk'], $slope);
			$utility_im_worsened = perceptualUtility($game[0]['position'], $worsened_median_voter_position_ij, $game[0]['risk'], $slope);
			$utility_sq_remaining = 0;
			//$utility_sq_improving = bcsub($utility_im_improved, $utility_im_current);
			$utility_sq_worsening = bcsub($utility_im_worsened, $utility_im_current);
			$utility_sq_improving = $utility_sq_worsening;
			
			$expected_utility_sq_improving = bcmul($probability_sq_improving, $utility_sq_improving);
			$expected_utility_sq_worsening = bcmul($probability_sq_worsening, $utility_sq_worsening);
			$expected_utility_sq_changing = bcmul($probability_sq_changing, bcadd($expected_utility_sq_improving, $expected_utility_sq_worsening));

			$expected_utility_sq_remaining = bcmul($probability_sq_remaining, $utility_sq_remaining);

			$expected_utility_not_challenging = bcadd($expected_utility_sq_changing, $expected_utility_sq_remaining);

			//Challenge or Not Challenge
			$expected_utility_i_ij = bcsub($expected_utility_challenging, $expected_utility_not_challenging);
			
			//j vs. i
			//Challenge
			$probability_resistance = $game[0]['salience'];
			$probability_no_resistance = bcsub(1, $probability_resistance);

			$absolute_vote_sum = 0;
			$vote_sum = 0;
			foreach($votes_i_ji as $vote) {
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

			$utility_i_ji = perceptualUtility($game[1]['position'], $game[0]['position'], $game[0]['risk'], $slope);
			$utility_success = bcsub(1, $utility_i_ji);
			$utility_failure = bcsub($utility_i_ji, 1);

			$expected_utility_winning = bcmul($probability_winning, $utility_success);
			$expected_utility_losing = bcmul($probability_losing, $utility_failure);
			$expected_utility_resistance = bcmul($probability_resistance, bcadd($expected_utility_winning, $expected_utility_losing));

			$expected_utility_no_resistance = bcmul($probability_no_resistance, $utility_success);

			$expected_utility_challenging = bcadd($expected_utility_resistance, $expected_utility_no_resistance);

			//Not Challenge
			$probability_sq_remaining = 0.5;
			$probability_sq_changing = bcsub(1, $probability_sq_remaining);
			$probability_sq_improving = 0.5;
			$probability_sq_worsening = bcsub(1, $probability_sq_improving);

			//$improved_median_voter_position_ji = improvedMedianVoterPosition($game, $game[1], $game[0]);
			$worsened_median_voter_position_ji = worsenedMedianVoterPosition($game, $game[1], $game[0]);
			$improved_median_voter_position_ji = $worsened_median_voter_position_ji;

			$utility_jm_current = perceptualUtility($game[1]['position'], $current_median_voter_position, $game[0]['risk'], $slope);
			//$utility_jm_improved = perceptualUtility($game[1]['position'], $improved_median_voter_position_ji, $game[0]['risk'], $slope);
			$utility_jm_worsened = perceptualUtility($game[1]['position'], $worsened_median_voter_position_ji, $game[0]['risk'], $slope);
			$utility_sq_remaining = 0;
			//$utility_sq_improving = bcsub($utility_jm_improved, $utility_jm_current);
			$utility_sq_worsening = bcsub($utility_jm_worsened, $utility_jm_current);
			$utility_sq_improving = $utility_sq_worsening;
			
			$expected_utility_sq_improving = bcmul($probability_sq_improving, $utility_sq_improving);
			$expected_utility_sq_worsening = bcmul($probability_sq_worsening, $utility_sq_worsening);
			$expected_utility_sq_changing = bcmul($probability_sq_changing, bcadd($expected_utility_sq_improving, $expected_utility_sq_worsening));

			$expected_utility_sq_remaining = bcmul($probability_sq_remaining, $utility_sq_remaining);

			$expected_utility_not_challenging = bcadd($expected_utility_sq_changing, $expected_utility_sq_remaining);

			//Challenge or Not Challenge
			$expected_utility_i_ji = bcsub($expected_utility_challenging, $expected_utility_not_challenging);
			
			
			//Calculate Expected Utility of Challenging or Not Challenging from Rival Actor's (j) Perspective
			//i vs. j
			//Challenge
			$probability_resistance = $game[1]['salience'];
			$probability_no_resistance = bcsub(1, $probability_resistance);

			$absolute_vote_sum = 0;
			$vote_sum = 0;
			foreach($votes_j_ij as $vote) {
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

			$utility_j_ij = perceptualUtility($game[0]['position'], $game[1]['position'], $game[1]['risk'], $slope);
			$utility_success = bcsub(1, $utility_j_ij);
			$utility_failure = bcsub($utility_j_ij, 1);

			$expected_utility_winning = bcmul($probability_winning, $utility_success);
			$expected_utility_losing = bcmul($probability_losing, $utility_failure);
			$expected_utility_resistance = bcmul($probability_resistance, bcadd($expected_utility_winning, $expected_utility_losing));

			$expected_utility_no_resistance = bcmul($probability_no_resistance, $utility_success);

			$expected_utility_challenging = bcadd($expected_utility_resistance, $expected_utility_no_resistance);

			//Not Challenge
			$probability_sq_remaining = 0.5;
			$probability_sq_changing = bcsub(1, $probability_sq_remaining);
			$probability_sq_improving = 0.5;
			$probability_sq_worsening = bcsub(1, $probability_sq_improving);

			$utility_im_current = perceptualUtility($game[0]['position'], $current_median_voter_position, $game[1]['risk'], $slope);
			//$utility_im_improved = perceptualUtility($game[0]['position'], $improved_median_voter_position_ij, $game[1]['risk'], $slope);
			$utility_im_worsened = perceptualUtility($game[0]['position'], $worsened_median_voter_position_ij, $game[1]['risk'], $slope);
			$utility_sq_remaining = 0;
			//$utility_sq_improving = bcsub($utility_im_improved, $utility_im_current);
			$utility_sq_worsening = bcsub($utility_im_worsened, $utility_im_current);
			$utility_sq_improving = $utility_sq_worsening;
			
			$expected_utility_sq_improving = bcmul($probability_sq_improving, $utility_sq_improving);
			$expected_utility_sq_worsening = bcmul($probability_sq_worsening, $utility_sq_worsening);
			$expected_utility_sq_changing = bcmul($probability_sq_changing, bcadd($expected_utility_sq_improving, $expected_utility_sq_worsening));

			$expected_utility_sq_remaining = bcmul($probability_sq_remaining, $utility_sq_remaining);

			$expected_utility_not_challenging = bcadd($expected_utility_sq_changing, $expected_utility_sq_remaining);

			//Challenge or Not Challenge
			$expected_utility_j_ij = bcsub($expected_utility_challenging, $expected_utility_not_challenging);
			
			//j vs. i
			//Challenge
			$probability_resistance = $game[0]['salience'];
			$probability_no_resistance = bcsub(1, $probability_resistance);

			$absolute_vote_sum = 0;
			$vote_sum = 0;
			foreach($votes_j_ji as $vote) {
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

			$utility_j_ji = perceptualUtility($game[1]['position'], $game[0]['position'], $game[1]['risk'], $slope);
			$utility_success = bcsub(1, $utility_j_ji);
			$utility_failure = bcsub($utility_j_ji, 1);

			$expected_utility_winning = bcmul($probability_winning, $utility_success);
			$expected_utility_losing = bcmul($probability_losing, $utility_failure);
			$expected_utility_resistance = bcmul($probability_resistance, bcadd($expected_utility_winning, $expected_utility_losing));

			$expected_utility_no_resistance = bcmul($probability_no_resistance, $utility_success);

			$expected_utility_challenging = bcadd($expected_utility_resistance, $expected_utility_no_resistance);

			//Not Challenge
			$probability_sq_remaining = 0.5;
			$probability_sq_changing = bcsub(1, $probability_sq_remaining);
			$probability_sq_improving = 0.5;
			$probability_sq_worsening = bcsub(1, $probability_sq_improving);

			$utility_jm_current = perceptualUtility($game[1]['position'], $current_median_voter_position, $game[1]['risk'], $slope);
			//$utility_jm_improved = perceptualUtility($game[1]['position'], $improved_median_voter_position_ji, $game[1]['risk'], $slope);
			$utility_jm_worsened = perceptualUtility($game[1]['position'], $worsened_median_voter_position_ji, $game[1]['risk'], $slope);
			$utility_sq_remaining = 0;
			//$utility_sq_improving = bcsub($utility_jm_improved, $utility_jm_current);
			$utility_sq_worsening = bcsub($utility_jm_worsened, $utility_jm_current);
			$utility_sq_improving = $utility_sq_worsening;
			
			$expected_utility_sq_improving = bcmul($probability_sq_improving, $utility_sq_improving);
			$expected_utility_sq_worsening = bcmul($probability_sq_worsening, $utility_sq_worsening);
			$expected_utility_sq_changing = bcmul($probability_sq_changing, bcadd($expected_utility_sq_improving, $expected_utility_sq_worsening));

			$expected_utility_sq_remaining = bcmul($probability_sq_remaining, $utility_sq_remaining);

			$expected_utility_not_challenging = bcadd($expected_utility_sq_changing, $expected_utility_sq_remaining);

			//Challenge or Not Challenge
			$expected_utility_j_ji = bcsub($expected_utility_challenging, $expected_utility_not_challenging);
			
			//Pass on Calculations to Array
			$simulation[] = array(
				"i" => array(
					"name" => $game[0]['name'], 
					"ij" => $expected_utility_i_ij, 
					"ji" => $expected_utility_i_ji), 
				"j" => array(
					"name" => $game[1]['name'], 
					"ij" => $expected_utility_j_ij, 
					"ji" => $expected_utility_j_ji));
		}
	}
	return $simulation;
}
?>