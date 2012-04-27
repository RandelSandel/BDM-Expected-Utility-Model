<?
include 'median_voter_calculator.php';
include 'risk_calculator.php';
include 'simulation.php';

function coordinateLocation($x, $y) {
	$arctangent = rad2deg(atan2($y, $x));

	if(bccomp($x, 0) == 0 and bccomp($y, 0) == 0) {
		$theta = "0";
	} else {
		if(bccomp($arctangent, 0) == -1) {
			$theta = bcadd(360, $arctangent);
		} else {
			if(bccomp($arctangent, 0) == 1) {
				$theta = "$arctangent";
			}
		}
	}

	if(bccomp($theta, 0) == 0) {
		$octant = "0";
	} else {
		if(bccomp($theta, 45) == -1) {
			$octant = "1";
		} else {
			if(bccomp($theta, 45) == 1 and bccomp($theta, 90) == -1) {
				$octant = "2";
			} else {
				if(bccomp($theta, 90) == 1 and bccomp($theta, 135) == -1) {
					$octant = "3";
				} else {
					if(bccomp($theta, 135) == 1 and bccomp($theta, 180) == -1) {
						$octant = "4";
					} else {
						if(bccomp($theta, 180) == 1 and bccomp($theta, 225) == -1) {
							$octant = "5";
						} else {
							if(bccomp($theta, 225) == 1 and bccomp($theta, 270) == -1) {
								$octant = "6";
							} else {
								if(bccomp($theta, 270) == 1 and bccomp($theta, 315) == -1) {
									$octant = "7";
								} else {
									if(bccomp($theta, 315) == 1 and bccomp($theta, 360) == -1) {
										$octant = "8";
									}
								}
							}
						}
					}
				}
			}
		}
	}
	
	if(bccomp($octant, 0) == 0) {
		$outcome_sign = "";
		$outcome = "No Issue";
	} else {
		if(bccomp($octant, 1) == 0) {
			$outcome_sign = "+";
			$outcome = "Conflict";
		} else {
			if(bccomp($octant, 2) == 0) {
				$outcome_sign = "−";
				$outcome = "Conflict";
			} else {
				if(bccomp($octant, 3) == 0) {
					$outcome_sign = "−";
					$outcome = "Compromise";
				} else {
					if(bccomp($octant, 4) == 0) {
						$outcome_sign = "−";
						$outcome = "Compel";
					} else {
						if(bccomp($octant, 5) == 0 or bccomp($octant, 6) == 0) {
							$outcome_sign = "";
							$outcome = "Status Quo";
						} else {
							if(bccomp($octant, 7) == 0) {
								$outcome_sign = "+";
								$outcome = "Compel";
							} else {
								if(bccomp($octant, 8) == 0) {
									$outcome_sign = "+";
									$outcome = "Compromise";
								}
							}
						}
					}
				}
			}
		}
	}
	
	if(bccomp($x, 1) == 1)
		$x = 1;
	
	if(bccomp($x, -1) == -1)
		$x = -1;
	
	if(bccomp($y, 1) == 1)
		$y = 1;
	
	if(bccomp($y, -1) == -1)
		$y = -1;
	
	if($outcome == "No Issue") {
		$probability = 0;
	} else {
		if($outcome == "Conflict") {
			$probability_i = bcdiv(bcadd(1, $x), 2);
			$probability_j = bcdiv(bcadd(1, $y), 2);
			$probability = bcmul($probability_i, $probability_j);
		} else {
			if($outcome == "Compromise") {
				if($outcome_sign == "+") {
					$probability_i = bcdiv(bcadd(1, $x), 2);
					$probability_j = bcsub(1, bcdiv(bcadd(1, $y), 2));
					$probability = bcmul($probability_i, $probability_j);
				}
				if($outcome_sign == "−") {
					$probability_i = bcsub(1, bcdiv(bcadd(1, $x), 2));
					$probability_j = bcdiv(bcadd(1, $y), 2);
					$probability = bcmul($probability_i, $probability_j);
				}
			} else {
				if($outcome == "Compel") {
					if($outcome_sign == "+") {
						$probability_i = bcdiv(bcadd(1, $x), 2);
						$probability_j = bcsub(1, bcdiv(bcadd(1, $y), 2));
						$probability = bcmul($probability_i, $probability_j);
					}
					if($outcome_sign == "−") {
						$probability_i = bcsub(1, bcdiv(bcadd(1, $x), 2));
						$probability_j = bcdiv(bcadd(1, $y), 2);
						$probability = bcmul($probability_i, $probability_j);
					}
				} else {
					if($outcome == "Status Quo") {
						$probability_i = bcsub(1, bcdiv(bcadd(1, $x), 2));
						$probability_j = bcsub(1, bcdiv(bcadd(1, $y), 2));
						$probability = bcmul($probability_i, $probability_j);
					}
				}
			}
		}
	}
	
	$location['probability'] = $probability;
	$location['outcome sign'] = $outcome_sign;
	$location['outcome'] = $outcome;
	
	return $location;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Expected Utility Model</title>
<style type="text/css">
h1 {
	font-family: "Lucida Sans Unicode", "Lucida Grande", sans-serif;
	font-size: 24px;
	text-align: center;
}
.input {
	font-family: "Lucida Sans Unicode", "Lucida Grande", sans-serif;
	font-size: 12px;
}
.output {
	font-family: "Lucida Sans Unicode", "Lucida Grande", sans-serif;
	font-size: 12px;
	border: thin solid #000;
}
.border {
	border: thin solid #000;
}
.left_border {
	border-left-width: thin;
	border-left-style: solid;
	border-left-color: #000;
}
.right_border {
	border-right-width: thin;
	border-right-style: solid;
	border-right-color: #000;
}
.left_right_border {
	border-right-width: thin;
	border-left-width: thin;
	border-right-style: solid;
	border-left-style: solid;
	border-right-color: #000;
	border-left-color: #000;
}
.bottom_border {
	border-bottom-width: thin;
	border-bottom-style: solid;
	border-bottom-color: #000;
}
.bottom_left_border {
	border-bottom-width: thin;
	border-left-width: thin;
	border-bottom-style: solid;
	border-left-style: solid;
	border-bottom-color: #000;
	border-left-color: #000;
}
.bottom_right_border {
	border-right-width: thin;
	border-bottom-width: thin;
	border-right-style: solid;
	border-bottom-style: solid;
	border-right-color: #000;
	border-bottom-color: #000;
}
.bottom_left_right_border {
	border-right-width: thin;
	border-bottom-width: thin;
	border-left-width: thin;
	border-right-style: solid;
	border-bottom-style: solid;
	border-left-style: solid;
	border-right-color: #000;
	border-bottom-color: #000;
	border-left-color: #000;
}
#sign {
	padding-left: 10px;
}
#outcome {
	padding-right: 10px;
}
#expected_utility {
	padding-right: 10px;
}
#name {
	padding-left: 10px;
}
#form1 {
	text-align: center;
}
</style>
</head>

<body>
<h1>Expected Utility Model</h1>
<p>&nbsp;</p>
<?
if(!isset($_POST['next1']) && !isset($_POST['next2'])) {
?>
<p align="center" class="input">Number of actors:</p>
<form id="step1" name="step1" method="post" action="">
  <p align="center">
    <input name="num_actors" type="text" id="num_actors" size="2" />
  </p>
  <p align="center">
    <input name="next1" type="submit" id="next1" value="Next" />
  </p>
</form>
<?
}
if(isset($_POST['next1'])) {
?>
<form id="step2" name="step2" method="post" action="">
  <table border="0" align="center" class="input">
    <tr>
      <td width="200">Actor</td>
      <td width="75">Resources</td>
      <td width="75">Salience</td>
      <td width="75">Position</td>
    </tr>
<?
	$num_actors = $_POST['num_actors'];
	
	for($count = 1; $count <= $num_actors; ++$count) {
?>
	<tr>
  	  <td width="200"><input name="name[]" type="text" size="25" /></td>
  	  <td width="75"><input name="resources[]" type="text" size="2" /></td>
  	  <td width="75"><input name="salience[]" type="text" size="2" /></td>
  	  <td width="75"><input name="position[]" type="text" size="2" /></td>
	</tr>
<?
	}
	
?>
	<tr>
	  <td><div align="center">
		<p>Precision:</p>
		<p>
		  <input name="precision" type="text" value="1" size="2" />
		</p>
	  </div></td>
	  <td colspan="3"><div align="center">
		<p>Utility Interval:</p>
		<p>
		  <label>
		    <input type="radio" name="utility_interval" value="zero_one" checked="checked" />
			(0, 1)</label>
		  <label>
			<input type="radio" name="utility_interval" value="negative_one_one" />
			(-1, 1)</label>
	    </p>
	  </div></td>
	</tr>
  </table>
  <p align="center">
    <input type="submit" name="next2" id="next2" value="Next" />
  </p>
</form>
<?
}
if(isset($_POST['next2'])) {
	$name = $_POST['name'];
	$resources = $_POST['resources'];
	$salience = $_POST['salience'];
	$position = $_POST['position'];
	for($index = 0; isset($name[$index]); ++$index) {
		$data[] = array("name" => $name[$index],"resources" => $resources[$index],"salience" => $salience[$index],"position" => $position[$index]);
	}
	
	bcscale(20);
	
	$hypothetical_precision = $_POST['precision'];
	$utility_interval = $_POST['utility_interval'];
	$risk_data = calculateRisk($data, $hypothetical_precision, $utility_interval);
	$simulation = simulate($risk_data, $utility_interval);
	
	function bcround($number, $scale = 3) {
		$fix = "5";
		for($i = 0; $i < $scale; $i++)
			$fix="0$fix";
		$number = bcadd($number, "0.$fix", $scale+1);
		return bcdiv($number, "1.0", $scale);
	}
	
	$index = 1;
	$num_actors = count($data) - 1;
	foreach($simulation as $focal_actor) {
		if($index == 1) {
			$index += 1;
			
			$focal_name = $focal_actor['i']['name'];
			$rival_name = $focal_actor['j']['name'];
			
			$focal_ij = sprintf("%01.3f", abs(bcround($focal_actor['i']['ij'])));
			$focal_ji = sprintf("%01.3f", abs(bcround($focal_actor['i']['ji'])));
			$rival_ij = sprintf("%01.3f", abs(bcround($focal_actor['j']['ij'])));
			$rival_ji = sprintf("%01.3f", abs(bcround($focal_actor['j']['ji'])));
			
			if(bccomp($focal_actor['i']['ij'], 0) == 1) {
				$focal_ij_sign = "+";
			} else {
				if(bccomp($focal_actor['i']['ij'], 0) == -1) {
					$focal_ij_sign = "−";
				} else {
					if(bccomp($focal_actor['i']['ij'], 0) == 0) {
						$focal_ij_sign = "";
					}
				}
			}
			
			if(bccomp($focal_actor['i']['ji'], 0) == 1) {
				$focal_ji_sign = "+";
			} else {
				if(bccomp($focal_actor['i']['ji'], 0) == -1) {
					$focal_ji_sign = "−";
				} else {
					if(bccomp($focal_actor['i']['ji'], 0) == 0) {
						$focal_ji_sign = "";
					}
				}
			}
			
			if(bccomp($focal_actor['j']['ij'], 0) == 1) {
				$rival_ij_sign = "+";
			} else {
				if(bccomp($focal_actor['j']['ij'], 0) == -1) {
					$rival_ij_sign = "−";
				} else {
					if(bccomp($focal_actor['j']['ij'], 0) == 0) {
						$rival_ij_sign = "";
					}
				}
			}
			
			
			if(bccomp($focal_actor['j']['ji'], 0) == 1) {
				$rival_ji_sign = "+";
			} else {
				if(bccomp($focal_actor['j']['ji'], 0) == -1) {
					$rival_ji_sign = "−";
				} else {
					if(bccomp($focal_actor['j']['ji'], 0) == 0) {
						$rival_ji_sign = "";
					}
				}
			}
			
			$focal_location = coordinateLocation($focal_actor['i']['ij'], $focal_actor['i']['ji']);
			$focal_probability = sprintf("%01.3f", abs(bcround($focal_location['probability'])));
			$focal_outcome_sign = $focal_location['outcome sign'];
			$focal_outcome = $focal_location['outcome'];
			
			$rival_location = coordinateLocation($focal_actor['j']['ij'], $focal_actor['j']['ji']);
			$rival_probability = sprintf("%01.3f", abs(bcround($rival_location['probability'])));
			$rival_outcome_sign = $rival_location['outcome sign'];
			$rival_outcome = $rival_location['outcome'];
			
			$objective_location = coordinateLocation($focal_actor['i']['ij'], $focal_actor['j']['ji']);
			$objective_probability = sprintf("%01.3f", abs(bcround($objective_location['probability'])));
			$objective_outcome_sign = $objective_location['outcome sign'];
			$objective_outcome = $objective_location['outcome'];
			
?>
	<table width="1024" border="0" cellpadding="0" cellspacing="0" class="output" align="center">
	  <tr>
	    <td colspan="22" class="bottom_border"><div align="center">Focal Actor (<em>i</em> ): <? echo"$focal_name"; ?></div></td>
	  </tr>
	  <tr>
	    <td width="224">&nbsp;</td>
	    <td colspan="7" class="bottom_left_border"><div align="center"><em>i</em>'s Perspective</div></td>
	    <td colspan="7" class="bottom_left_border"><div align="center"><em>j</em>'s Perspective</div></td>
	    <td colspan="7" class="bottom_left_border"><div align="center">Objective Perspective</div></td>
	  </tr>
	  <tr>
	    <td class="bottom_border"><div align="center">Rival Actor ( <em>j</em> )</div></td>
	    <td colspan="2" class="bottom_left_border"><div align="center"><em>i</em> vs. <em>j</em></div></td>
	    <td colspan="2" class="bottom_border"><div align="center"><em>j</em> vs. <em>i</em></div></td>
	    <td width="37" class="bottom_border"><div align="center">Probability</div></td>
	    <td colspan="2" class="bottom_border"><div align="center">Outcome</div></td>
	    <td colspan="2" class="bottom_left_border"><div align="center"><em>i</em> vs. <em>j</em></div></td>
	    <td colspan="2" class="bottom_border"><div align="center"><em>j</em> vs. <em>i</em></div></td>
	    <td width="37" class="bottom_border"><div align="center">Probability</div></td>
	    <td colspan="2" class="bottom_border"><div align="center">Outcome</div></td>
	    <td colspan="2" class="bottom_left_border"><div align="center"><em>i</em> vs. <em>j</em></div></td>
	    <td colspan="2" class="bottom_border"><div align="center" id="comparison"><em>j</em> vs. <em>i</em></div></td>
	    <td width="37" class="bottom_border"><div align="center">Probability</div></td>
	    <td colspan="2" class="bottom_border"><div align="center">Outcome</div></td>
	  </tr>
	  <tr>
	    <td id="name"><? echo"$rival_name"; ?></td>
	    <td width="22" class="left_border" id="sign"><div align="right"><? echo"$focal_ij_sign"; ?></div></td>
	    <td width="36"><? echo"$focal_ij"; ?></td>
	    <td width="20" id="sign"><div align="right"><? echo"$focal_ji_sign"; ?></div></td>
	    <td width="46" id="expected_utility"><? echo"$focal_ji"; ?></td>
	    <td><div align="center"><? echo"$focal_probability"; ?></div></td>
	    <td width="20" id="sign"><div align="right"><? echo"$focal_outcome_sign"; ?></div></td>
	    <td width="87" id="outcome"><? echo"$focal_outcome"; ?></td>
	    <td width="22" class="left_border" id="sign"><div align="right"><? echo"$rival_ij_sign"; ?></div></td>
	    <td width="36"><? echo"$rival_ij"; ?></td>
	    <td width="20" id="sign"><div align="right"><? echo"$rival_ji_sign"; ?></div></td>
	    <td width="46" id="expected_utility"><? echo"$rival_ji"; ?></td>
	    <td><div align="center"><? echo"$rival_probability"; ?></div></td>
	    <td width="20" id="sign"><div align="right"><? echo"$rival_outcome_sign"; ?></div></td>
	    <td width="83" id="outcome"><? echo"$rival_outcome"; ?></td>
	    <td width="22" class="left_border" id="sign"><div align="right"><? echo"$focal_ij_sign"; ?></div></td>
	    <td width="36"><? echo"$focal_ij"; ?></td>
	    <td width="20" id="sign"><div align="right"><? echo"$rival_ji_sign"; ?></div></td>
	    <td width="46" id="expected_utility"><? echo"$rival_ji"; ?></td>
	    <td><div align="center"><? echo"$objective_probability"; ?></div></td>
	    <td width="20" id="sign"><div align="right"><? echo"$objective_outcome_sign"; ?></div></td>
	    <td width="83" id="outcome"><? echo"$objective_outcome"; ?></td>
	  </tr>
<?
		} else {
			if($index < $num_actors) {
				$index += 1;
				
				$rival_name = $focal_actor['j']['name'];
				
				$focal_ij = sprintf("%01.3f", abs(bcround($focal_actor['i']['ij'])));
				$focal_ji = sprintf("%01.3f", abs(bcround($focal_actor['i']['ji'])));
				$rival_ij = sprintf("%01.3f", abs(bcround($focal_actor['j']['ij'])));
				$rival_ji = sprintf("%01.3f", abs(bcround($focal_actor['j']['ji'])));
				
				if(bccomp($focal_actor['i']['ij'], 0) == 1) {
					$focal_ij_sign = "+";
				} else {
					if(bccomp($focal_actor['i']['ij'], 0) == -1) {
						$focal_ij_sign = "−";
					} else {
						if(bccomp($focal_actor['i']['ij'], 0) == 0) {
							$focal_ij_sign = "";
						}
					}
				}
				
				if(bccomp($focal_actor['i']['ji'], 0) == 1) {
					$focal_ji_sign = "+";
				} else {
					if(bccomp($focal_actor['i']['ji'], 0) == -1) {
						$focal_ji_sign = "−";
					} else {
						if(bccomp($focal_actor['i']['ji'], 0) == 0) {
							$focal_ji_sign = "";
						}
					}
				}
				
				if(bccomp($focal_actor['j']['ij'], 0) == 1) {
					$rival_ij_sign = "+";
				} else {
					if(bccomp($focal_actor['j']['ij'], 0) == -1) {
						$rival_ij_sign = "−";
					} else {
						if(bccomp($focal_actor['j']['ij'], 0) == 0) {
							$rival_ij_sign = "";
						}
					}
				}
				
				if(bccomp($focal_actor['j']['ji'], 0) == 1) {
					$rival_ji_sign = "+";
				} else {
					if(bccomp($focal_actor['j']['ji'], 0) == -1) {
						$rival_ji_sign = "−";
					} else {
						if(bccomp($focal_actor['j']['ji'], 0) == 0) {
							$rival_ji_sign = "";
						}
					}
				}
				
				$focal_location = coordinateLocation($focal_actor['i']['ij'], $focal_actor['i']['ji']);
				$focal_probability = sprintf("%01.3f", abs(bcround($focal_location['probability'])));
				$focal_outcome_sign = $focal_location['outcome sign'];
				$focal_outcome = $focal_location['outcome'];

				$rival_location = coordinateLocation($focal_actor['j']['ij'], $focal_actor['j']['ji']);
				$rival_probability = sprintf("%01.3f", abs(bcround($rival_location['probability'])));
				$rival_outcome_sign = $rival_location['outcome sign'];
				$rival_outcome = $rival_location['outcome'];

				$objective_location = coordinateLocation($focal_actor['i']['ij'], $focal_actor['j']['ji']);
				$objective_probability = sprintf("%01.3f", abs(bcround($objective_location['probability'])));
				$objective_outcome_sign = $objective_location['outcome sign'];
				$objective_outcome = $objective_location['outcome'];
?>
  <tr>
    <td id="name"><? echo"$rival_name"; ?></td>
    <td class="left_border" id="sign"><div align="right"><? echo"$focal_ij_sign"; ?></div></td>
    <td><? echo"$focal_ij"; ?></td>
    <td id="sign"><div align="right"><? echo"$focal_ji_sign"; ?></div></td>
    <td id="expected_utility"><? echo"$focal_ji"; ?></td>
    <td><div align="center"><? echo"$focal_probability"; ?></div></td>
    <td id="sign"><div align="right"><? echo"$focal_outcome_sign"; ?></div></td>
    <td id="outcome"><? echo"$focal_outcome"; ?></td>
    <td class="left_border" id="sign"><div align="right"><? echo"$rival_ij_sign"; ?></div></td>
    <td><? echo"$rival_ij"; ?></td>
    <td width="20" id="sign"><div align="right"><? echo"$rival_ji_sign"; ?></div></td>
    <td id="expected_utility"><? echo"$rival_ji"; ?></td>
    <td><div align="center"><? echo"$rival_probability"; ?></div></td>
    <td id="sign"><div align="right"><? echo"$rival_outcome_sign"; ?></div></td>
    <td id="outcome"><? echo"$rival_outcome"; ?></td>
    <td class="left_border" id="sign"><div align="right"><? echo"$focal_ij_sign"; ?></div></td>
    <td><? echo"$focal_ij"; ?></td>
    <td id="sign"><div align="right"><? echo"$rival_ji_sign"; ?></div></td>
    <td id="expected_utility"><? echo"$rival_ji"; ?></td>
    <td><div align="center"><? echo"$objective_probability"; ?></div></td>
    <td id="sign"><div align="right"><? echo"$objective_outcome_sign"; ?></div></td>
    <td id="outcome"><? echo"$objective_outcome"; ?></td>
  </tr>
<?
			} else {
				$index = 1;
				
				$rival_name = $focal_actor['j']['name'];
				
				$focal_ij = sprintf("%01.3f", abs(bcround($focal_actor['i']['ij'])));
				$focal_ji = sprintf("%01.3f", abs(bcround($focal_actor['i']['ji'])));
				$rival_ij = sprintf("%01.3f", abs(bcround($focal_actor['j']['ij'])));
				$rival_ji = sprintf("%01.3f", abs(bcround($focal_actor['j']['ji'])));
				
				if(bccomp($focal_actor['i']['ij'], 0) == 1) {
					$focal_ij_sign = "+";
				} else {
					if(bccomp($focal_actor['i']['ij'], 0) == -1) {
						$focal_ij_sign = "−";
					} else {
						if(bccomp($focal_actor['i']['ij'], 0) == 0) {
							$focal_ij_sign = "";
						}
					}
				}
				
				if(bccomp($focal_actor['i']['ji'], 0) == 1) {
					$focal_ji_sign = "+";
				} else {
					if(bccomp($focal_actor['i']['ji'], 0) == -1) {
						$focal_ji_sign = "−";
					} else {
						if(bccomp($focal_actor['i']['ji'], 0) == 0) {
							$focal_ji_sign = "";
						}
					}
				}
				
				if(bccomp($focal_actor['j']['ij'], 0) == 1) {
					$rival_ij_sign = "+";
				} else {
					if(bccomp($focal_actor['j']['ij'], 0) == -1) {
						$rival_ij_sign = "−";
					} else {
						if(bccomp($focal_actor['j']['ij'], 0) == 0) {
							$rival_ij_sign = "";
						}
					}
				}
				
				if(bccomp($focal_actor['j']['ji'], 0) == 1) {
					$rival_ji_sign = "+";
				} else {
					if(bccomp($focal_actor['j']['ji'], 0) == -1) {
						$rival_ji_sign = "−";
					} else {
						if(bccomp($focal_actor['j']['ji'], 0) == 0) {
							$rival_ji_sign = "";
						}
					}
				}
				
				$focal_location = coordinateLocation($focal_actor['i']['ij'], $focal_actor['i']['ji']);
				$focal_probability = sprintf("%01.3f", abs(bcround($focal_location['probability'])));
				$focal_outcome_sign = $focal_location['outcome sign'];
				$focal_outcome = $focal_location['outcome'];

				$rival_location = coordinateLocation($focal_actor['j']['ij'], $focal_actor['j']['ji']);
				$rival_probability = sprintf("%01.3f", abs(bcround($rival_location['probability'])));
				$rival_outcome_sign = $rival_location['outcome sign'];
				$rival_outcome = $rival_location['outcome'];

				$objective_location = coordinateLocation($focal_actor['i']['ij'], $focal_actor['j']['ji']);
				$objective_probability = sprintf("%01.3f", abs(bcround($objective_location['probability'])));
				$objective_outcome_sign = $objective_location['outcome sign'];
				$objective_outcome = $objective_location['outcome'];
?>
	  <tr>
	    <td id="name"><? echo"$rival_name"; ?></td>
	    <td class="left_border" id="sign"><div align="right"><? echo"$focal_ij_sign"; ?></div></td>
	    <td><? echo"$focal_ij"; ?></td>
	    <td id="sign"><div align="right"><? echo"$focal_ji_sign"; ?></div></td>
	    <td id="expected_utility"><? echo"$focal_ji"; ?></td>
	    <td><div align="center"><? echo"$focal_probability"; ?></div></td>
	    <td id="sign"><div align="right"><? echo"$focal_outcome_sign"; ?></div></td>
	    <td id="outcome"><? echo"$focal_outcome"; ?></td>
	    <td class="left_border" id="sign"><div align="right"><? echo"$rival_ij_sign"; ?></div></td>
	    <td><? echo"$rival_ij"; ?></td>
	    <td width="20" id="sign"><div align="right"><? echo"$rival_ji_sign"; ?></div></td>
	    <td id="expected_utility"><? echo"$rival_ji"; ?></td>
	    <td><div align="center"><? echo"$rival_probability"; ?></div></td>
	    <td id="sign"><div align="right"><? echo"$rival_outcome_sign"; ?></div></td>
	    <td id="outcome"><? echo"$rival_outcome"; ?></td>
	    <td class="left_border" id="sign"><div align="right"><? echo"$focal_ij_sign"; ?></div></td>
	    <td><? echo"$focal_ij"; ?></td>
	    <td id="sign"><div align="right"><? echo"$rival_ji_sign"; ?></div></td>
	    <td id="expected_utility"><? echo"$rival_ji"; ?></td>
	    <td><div align="center"><? echo"$objective_probability"; ?></div></td>
	    <td id="sign"><div align="right"><? echo"$objective_outcome_sign"; ?></div></td>
	    <td id="outcome"><? echo"$objective_outcome"; ?></td>
	  </tr>
	</table>
<br />
<br />
<?
			}
		}
	}
?>
<?
}
?>
</body>
</html>