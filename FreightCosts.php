<?php

include('includes/session.php');
$Title = _('Freight Costs Maintenance');
include('includes/header.php');
include('includes/CountriesArray.php');

if (isset($_GET['LocationFrom'])) {
	$LocationFrom = $_GET['LocationFrom'];
} elseif (isset($_POST['LocationFrom'])) {
	$LocationFrom = $_POST['LocationFrom'];
}
if (isset($_GET['ShipperID'])) {
	$ShipperID = $_GET['ShipperID'];
} elseif (isset($_POST['ShipperID'])) {
	$ShipperID = $_POST['ShipperID'];
}
if (isset($_GET['SelectedFreightCost'])) {
	$SelectedFreightCost = $_GET['SelectedFreightCost'];
} elseif (isset($_POST['SelectedFreightCost'])) {
	$SelectedFreightCost = $_POST['SelectedFreightCost'];
}

if (!isset($LocationFrom) or !isset($ShipperID)) {
	echo '<div class="centre"><p class="page_title_text"><img src="'.$RootPath.'/css/'.$_SESSION['Theme'].'/images/supplier.png" title="' .
		_('Freight Costs') . '" alt="" />' . ' ' . $Title . '</p></div>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$SQL = "SELECT shippername, shipper_id FROM shippers";
	$ShipperResults = DB_query($SQL);

	echo '<table class="selection">
		<tr>
			<td>' . _('Select A Freight Company to set up costs for') . '</td>
			<td><select name="ShipperID">';

	while ($MyRow = DB_fetch_array($ShipperResults)) {
		echo '<option value="' . $MyRow['shipper_id'] . '">' . $MyRow['shippername'] . '</option>';
	}
	echo '</select></td></tr>
			<tr>
				<td>' . _('Select the warehouse') . ' (' . _('ship from location') . ')</td>
				<td><select name="LocationFrom">';

	$SQL = "SELECT locationname,
					loctions.loccode
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canupd=1";
	$LocationResults = DB_query($SQL);

	while ($MyRow = DB_fetch_array($LocationResults)) {
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}

	echo '</select>
			</td>
		</tr>
	</table>';

	echo '<div class="centre">
			<input type="submit" value="' . _('Accept') . '" name="Accept" />
		</div>';
	echo '</form>';

} else {

	$SQL = "SELECT shippername FROM shippers WHERE shipper_id = '" . $ShipperID . "'";
	$ShipperResults = DB_query($SQL);
	$MyRow = DB_fetch_row($ShipperResults);
	$ShipperName = $MyRow[0];
	$SQL = "SELECT locationname FROM locations WHERE loccode = '" . $LocationFrom . "'";
	$LocationResults = DB_query($SQL);
	$MyRow = DB_fetch_row($LocationResults);
	$LocationName = $MyRow[0];
	if (isset($ShipperID)){
		$Title .= ' ' . _('For') . ' ' . $ShipperName;
	}
	if (isset($LocationFrom)){
		$Title .= ' ' . _('From') . ' ' . $LocationName;
	}

	echo '<div class="centre"><p class="page_title_text"><img src="'.$RootPath.'/css/'.$_SESSION['Theme'].'/images/supplier.png" title="' .
		_('Freight Costs') . '" alt="" />' . ' ' . $Title . '</p></div>';

}


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	//first off validate inputs sensible

	if (trim($_POST['DestinationCountry']) == '' ) {
		$_POST['DestinationCountry'] = $CountriesArray[$_SESSION['CountryOfOperation']];
	}
	if (trim($_POST['CubRate']) == '') {
		$_POST['CubRate'] = 0;
	}
	if (trim($_POST['KGRate']) == '') {
		$_POST['KGRate'] = 0;
	}
	if (trim($_POST['MAXKGs']) == '') {
		$_POST['MAXKGs'] = 0;
	}
	if (trim($_POST['MAXCub']) == '') {
		$_POST['MAXCub'] = 0;
	}
	if (trim($_POST['FixedPrice']) == '') {
		$_POST['FixedPrice'] = 0;
	}
	if (trim($_POST['MinimumChg']) == '') {
		$_POST['MinimumChg'] = 0;
	}

	if (!is_double((double) $_POST['CubRate']) or !is_double((double) $_POST['KGRate']) or !is_double((double) $_POST['MAXKGs']) or !is_double((double) $_POST['MAXCub']) or !is_double((double) $_POST['FixedPrice']) or !is_double((double) $_POST['MinimumChg'])) {
		$InputError = 1;
		prnMsg(_('The entries for Cubic Rate, KG Rate, Maximum Weight, Maximum Volume, Fixed Price and Minimum charge must be numeric'), 'warn');
	}

	if (isset($SelectedFreightCost) and $InputError != 1) {

		$SQL = "UPDATE freightcosts
				SET	locationfrom='" . $LocationFrom . "',
					destinationcountry='" . $_POST['DestinationCountry'] . "',
					destination='" . $_POST['Destination'] . "',
					shipperid='" . $ShipperID . "',
					cubrate='" . $_POST['CubRate'] . "',
					kgrate ='" . $_POST['KGRate'] . "',
					maxkgs ='" . $_POST['MAXKGs'] . "',
					maxcub= '" . $_POST['MAXCub'] . "',
					fixedprice = '" . $_POST['FixedPrice'] . "',
					minimumchg= '" . $_POST['MinimumChg'] . "'
			WHERE shipcostfromid='" . $SelectedFreightCost . "'";

		$Msg = _('Freight cost record updated');

	} elseif ($InputError != 1) {

		/*Selected freight cost is null cos no item selected on first time round so must be adding a record must be submitting new entries */
		$LocationFrom = stripslashes($LocationFrom);
		$SQL = "INSERT INTO freightcosts (locationfrom,
											destinationcountry,
											destination,
											shipperid,
											cubrate,
											kgrate,
											maxkgs,
											maxcub,
											fixedprice,
											minimumchg)
										VALUES (
											'" . $LocationFrom . "',
											'" . $_POST['DestinationCountry'] . "',
											'" . $_POST['Destination'] . "',
											'" . $ShipperID . "',
											'" . $_POST['CubRate'] . "',
											'" . $_POST['KGRate'] . "',
											'" . $_POST['MAXKGs'] . "',
											'" . $_POST['MAXCub'] . "',
											'" . $_POST['FixedPrice'] . "',
											'" . $_POST['MinimumChg'] . "'
										)";

		$Msg = _('Freight cost record inserted');

	}
	//run the SQL from either of the above possibilites
	$ErrMsg = _('The freight cost record could not be updated because');
	$Result = DB_query($SQL, $ErrMsg);

	prnMsg($Msg, 'success');

	unset($SelectedFreightCost);
	unset($_POST['Destination']);
	unset($_POST['DestinationCountry']);
	unset($_POST['CubRate']);
	unset($_POST['KGRate']);
	unset($_POST['MAXKGs']);
	unset($_POST['MAXCub']);
	unset($_POST['FixedPrice']);
	unset($_POST['MinimumChg']);

} elseif (isset($_GET['delete'])) {

	$SQL = "DELETE FROM freightcosts WHERE shipcostfromid='" . $SelectedFreightCost . "'";
	$Result = DB_query($SQL);
	prnMsg(_('Freight cost record deleted'), 'success');
	unset($SelectedFreightCost);
	unset($_GET['delete']);
}

if (!isset($SelectedFreightCost) and isset($LocationFrom) and isset($ShipperID)) {


	$SQL = "SELECT shipcostfromid,
					destinationcountry,
					destination,
					cubrate,
					kgrate,
					maxkgs,
					maxcub,
					fixedprice,
					minimumchg
				FROM freightcosts
				WHERE freightcosts.locationfrom = '" . $LocationFrom . "'
				AND freightcosts.shipperid = '" . $ShipperID . "'
				ORDER BY destinationcountry,
						destination,
						maxkgs,
						maxcub";

	$Result = DB_query($SQL);

	echo '<br /><table class="selection">
					<tr>
						<th>' . _('Destination') . '</th>
						<th>' . _('Country') . '</th>
						<th>' . _('Cubic Rate') . '</th>
						<th>' . _('KG Rate') . '</th>
						<th>' . _('MAX KGs') . '</th>
						<th>' . _('MAX Volume') . '</th>
						<th>' . _('Fixed Price') . '</th>
						<th>' . _('Minimum Charge') . '</th>
					</tr>';

	$k = 0; //row counter to determine background colour

	while ($MyRow = DB_fetch_array($Result)) {

		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}


		printf('<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td><a href="%s&amp;SelectedFreightCost=%s&amp;LocationFrom=%s&amp;ShipperID=%s">' . _('Edit') . '</a></td>
				<td><a href="%s&amp;SelectedFreightCost=%s&amp;LocationFrom=%s&amp;ShipperID=%s&amp;delete=yes" onclick="return MakeConfirm(\'' . _('Are you sure you wish to delete this freight cost') . '\', \'Confirm Delete\', this);">' . _('Delete') . '</a></td>
			</tr>',
				$MyRow['destinationcountry'],
				$MyRow['destination'],
				locale_number_format($MyRow['cubrate'], $_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($MyRow['kgrate'], $_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($MyRow['maxkgs'], 2),
				locale_number_format($MyRow['maxcub'], 3),
				locale_number_format($MyRow['fixedprice'], $_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($MyRow['minimumchg'], $_SESSION['CompanyRecord']['decimalplaces']),
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
				$MyRow['shipcostfromid'],
				$LocationFrom,
				$ShipperID,
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?',
				$MyRow['shipcostfromid'],
				$LocationFrom,
				$ShipperID);

	}

	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

if (isset($SelectedFreightCost)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?LocationFrom=' . $LocationFrom . '&amp;ShipperID=' . $ShipperID . '">' . _('Show all freight costs for') . ' ' . $ShipperName . ' ' . _('from') . ' ' . $LocationName . '</a></div>';
}

if (isset($LocationFrom) and isset($ShipperID)) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedFreightCost)) {
		//editing an existing freight cost item

		$SQL = "SELECT locationfrom,
						destinationcountry,
						destination,
						shipperid,
						cubrate,
						kgrate,
						maxkgs,
						maxcub,
						fixedprice,
						minimumchg
					FROM freightcosts
					WHERE shipcostfromid='" . $SelectedFreightCost . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$LocationFrom = $MyRow['locationfrom'];
		$_POST['DestinationCountry'] = $MyRow['destinationcountry'];
		$_POST['Destination'] = $MyRow['destination'];
		$ShipperID = $MyRow['shipperid'];
		$_POST['CubRate'] = $MyRow['cubrate'];
		$_POST['KGRate'] = $MyRow['kgrate'];
		$_POST['MAXKGs'] = $MyRow['maxkgs'];
		$_POST['MAXCub'] = $MyRow['maxcub'];
		$_POST['FixedPrice'] = $MyRow['fixedprice'];
		$_POST['MinimumChg'] = $MyRow['minimumchg'];

		echo '<input type="hidden" name="SelectedFreightCost" value="' . $SelectedFreightCost . '" />';

	} else {
		$_POST['FixedPrice'] = 0;
		$_POST['MinimumChg'] = 0;

	}
	echo '<input type="hidden" name="LocationFrom" value="' . $LocationFrom . '" />';
	echo '<input type="hidden" name="ShipperID" value="' . $ShipperID . '" />';

	if (!isset($_POST['DestinationCountry'])) {
		$_POST['DestinationCountry'] = $CountriesArray[$_SESSION['CountryOfOperation']];
	}
	if (!isset($_POST['Destination'])) {
		$_POST['Destination'] = '';
	}
	if (!isset($_POST['CubRate'])) {
		$_POST['CubRate'] = '';
	}
	if (!isset($_POST['KGRate'])) {
		$_POST['KGRate'] = '';
	}
	if (!isset($_POST['MAXKGs'])) {
		$_POST['MAXKGs'] = '';
	}
	if (!isset($_POST['MAXCub'])) {
		$_POST['MAXCub'] = '';
	}

	echo '<br /><table class="selection">';
	echo '<tr>
			<th colspan="2">' . _('For Deliveries From') . ' ' . $LocationName . ' ' . _('using') . ' ' . $ShipperName . '</th>
		</tr>';
	echo '<tr>
			<td>' . _('Destination Country') . ':</td>
			<td><select name="DestinationCountry">';
	foreach ($CountriesArray as $CountryEntry => $CountryName){
		if (isset($_POST['DestinationCountry']) and (strtoupper($_POST['DestinationCountry']) == strtoupper($CountryName))){
			echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName .'</option>';
		} else {
			echo '<option value="' . $CountryName . '">' . $CountryName .'</option>';
		}
	}
	echo '</select></td>
		</tr>';

	echo'<tr>
			<td>' . _('Destination Zone') . ':</td>
			<td><input type="text" required="required" maxlength="20" size="20" name="Destination" value="' . $_POST['Destination'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Rate per Cubic Metre') . ':</td>
			<td><input type="text" name="CubRate" class="number" size="6" required="required" maxlength="5" value="' . $_POST['CubRate'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Rate Per KG') . ':</td>
			<td><input type="text" name="KGRate" class="number" size="6" required="required" maxlength="5" value="' . $_POST['KGRate'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Maximum Weight Per Package (KGs)') . ':</td>
			<td><input type="text" name="MAXKGs" class="number" size="8" required="required" maxlength="7" value="' . $_POST['MAXKGs'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Maximum Volume Per Package (cubic metres)') . ':</td>
			<td><input type="text" name="MAXCub" class="number" size="8" required="required" maxlength="7" value="' . $_POST['MAXCub'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Fixed Price (zero if rate per KG or Cubic)') . ':</td>
			<td><input type="text" name="FixedPrice" class="number" size="11" required="required" maxlength="10" value="' . $_POST['FixedPrice'] . '" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Minimum Charge (0 is N/A)') . ':</td>
			<td><input type="text" name="MinimumChg" class="number" size="11" required="required" maxlength="10" value="' . $_POST['MinimumChg'] . '" /></td>
		</tr>';

	echo '</table><br />';

	echo '<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>';
	echo '</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
?>