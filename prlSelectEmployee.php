<?php

include('includes/session.inc');
$Title = _('Emloyee Master Record Maintenance');

include('includes/header.inc');

echo '<div class="toplink"><a href="' . $RootPath . '/prlEmployeeMaster.php">' . _('Create a New Employee Record') . '</a></div>';

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_GET['EmployeeID'])) {
	$EmployeeID = $_GET['EmployeeID'];
} elseif (isset($_POST['EmployeeID'])) {
	$EmployeeID = $_POST['EmployeeID'];
}

$PayTypes = array(
	_('Salary'),
	_('Hourly')
);

if (!isset($EmployeeID)) {
	$sql = "SELECT prlemployeemaster.employeeid,
					prlemployeemaster.lastname,
					prlemployeemaster.firstname,
					prlemployeemaster.payperiodid,
					prlemployeemaster.paytype,
					prlemployeemaster.marital,
					prlemployeemaster.birthdate,
					prlemployeemaster.active,
					prlemployeemaster.payperiodid,
					prlpayperiod.payperioddesc
				FROM prlemployeemaster
				INNER JOIN prlpayperiod
					ON prlemployeemaster.payperiodid=prlpayperiod.payperiodid
				ORDER BY lastname,
						firstname";

	$ErrMsg = _('The employee master record could not be retrieved because');
	$result = DB_query($sql, $ErrMsg);

	if (DB_num_rows($result) > 0) {
		echo '<table class="selection">';
		echo '<tr>
				<th class="SortableColumn">' . _('Employee ID') . '</th>
				<th class="SortableColumn">' . _('Last Name ') . '</th>
				<th class="SortableColumn">' . _('First Name') . '</th>
				<th class="SortableColumn">' . _('Pay Type  ') . '</th>
				<th class="SortableColumn">' . _('Marital Status') . '</th>
				<th class="SortableColumn">' . _('Date of Birth') . '</th>
				<th class="SortableColumn">' . _('Status   ') . '</th>
				<th class="SortableColumn">' . _('Pay Period') . '</th>
			</tr>';

		$k = 0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {

			//alternateTableRowColor($k);
			if ($k == 1) {
				echo '<tr class="OddTableRows">';
				$k = 0;
			} else {
				echo '<tr class="EvenTableRows">';
				$k++;
			}
			echo '<td>' . $myrow['employeeid'] . '</td>
    			<td>' . $myrow['lastname'] . '</td>
				<td>' . $myrow['firstname'] . '</td>
				<td>' . $PayTypes[$myrow['paytype']] . '</td>
				<td>' . $myrow['marital'] . '</td>
				<td>' . ConvertSQLDate($myrow['birthdate']) . '</td>
				<td>' . $myrow['active'] . '</td>
				<td>' . $myrow['payperioddesc'] . '</td>
				<td><a href=' . $RootPath . '/prlEmployeeMaster.php?EmployeeID=' . $myrow['employeeid'] . '>' . _('Edit') . '</td></tr>';
		} //END WHILE LIST LOOP
		echo '</table>';
	} else {
		prnMsg( _('No employees have been created. Please create an employee first'), 'info');
	}
}

include('includes/footer.inc');
?>