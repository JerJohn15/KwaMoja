<?php

include('includes/session.inc');

$Title = _('Authorise Internal Stock Requests');
$ViewTopic = 'Inventory';
$BookMark = 'AuthoriseRequest';

include('includes/header.inc');

echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['UpdateAll'])) {
	foreach ($_POST as $key => $value) {
		if (mb_substr($key, 0, 6) == 'status') {
			$RequestNo = mb_substr($key, 6);
			$SQL = "UPDATE stockrequest
					SET authorised='1'
					WHERE dispatchid='" . $RequestNo . "'";
			$Result = DB_query($SQL);
		}
		if (strpos($key, 'cancel')) {
			$CancelItems = explode('cancel', $key);
			$SQL = "UPDATE stockrequestitems
						SET completed=1
						WHERE dispatchid='" . $CancelItems[0] . "'
							AND dispatchitemsid='" . $CancelItems[1] . "'";
			$Result = DB_query($SQL);
		}
	}
}

/* Retrieve the requisition header information
 */
$SQL = "SELECT stockrequest.dispatchid,
				locations.locationname,
				stockrequest.despatchdate,
				stockrequest.narrative,
				departments.description,
				w1.realname as authoriser,
				w2.realname as initiator,
				w1.email
			FROM stockrequest
			INNER JOIN departments
				ON stockrequest.departmentid=departments.departmentid
			INNER JOIN locations
				ON stockrequest.loccode=locations.loccode
			INNER JOIN www_users as w2
				ON w2.userid=stockrequest.userid
			INNER JOIN www_users as w1
				ON w1.userid=departments.authoriser
			WHERE stockrequest.authorised=0
				AND stockrequest.closed=0
				AND w1.userid='" . $_SESSION['UserID'] . "'";
$Result = DB_query($SQL);

echo '<form onSubmit="return VerifyForm(this);" method="post" class="noPrint" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';

/* Create the table for the purchase order header */
echo '<tr>
		<th>' . _('Request Number') . '</th>
		<th>' . _('Department') . '</th>
		<th>' . _('Initiator') . '</th>
		<th>' . _('Location Of Stock') . '</th>
		<th>' . _('Requested Date') . '</th>
		<th>' . _('Narrative') . '</th>
		<th>' . _('Authorise') . '</th>
	</tr>';

while ($MyRow = DB_fetch_array($Result)) {

	echo '<tr>
			<td>' . $MyRow['dispatchid'] . '</td>
			<td>' . $MyRow['description'] . '</td>
			<td>' . $MyRow['initiator'] . '</td>
			<td>' . $MyRow['locationname'] . '</td>
			<td>' . ConvertSQLDate($MyRow['despatchdate']) . '</td>
			<td>' . $MyRow['narrative'] . '</td>
			<td><input type="checkbox" name="status' . $MyRow['dispatchid'] . '" /></td>
		</tr>';
	$linesql = "SELECT stockrequestitems.dispatchitemsid,
						stockrequestitems.stockid,
						stockrequestitems.decimalplaces,
						stockrequestitems.uom,
						stockmaster.description,
						stockrequestitems.quantity
				FROM stockrequestitems
				INNER JOIN stockmaster
					ON stockmaster.stockid=stockrequestitems.stockid
				WHERE dispatchid='" . $MyRow['dispatchid'] . "'
					AND completed=0";
	$lineresult = DB_query($linesql);

	echo '<tr>
			<td></td>
			<td colspan="5" align="left">
				<table class="selection" align="left">
				<tr>
					<th>' . _('Product') . '</th>
					<th>' . _('Quantity Required') . '</th>
					<th>' . _('Units') . '</th>
					<th>' . _('Cancel Line') . '</th>
				</tr>';

	while ($linerow = DB_fetch_array($lineresult)) {
		echo '<tr>
				<td>' . $linerow['description'] . '</td>
				<td class="number">' . locale_number_format($linerow['quantity'], $linerow['decimalplaces']) . '</td>
				<td>' . $linerow['uom'] . '</td>
				<td><input type="checkbox" name="' . $MyRow['dispatchid'] . 'cancel' . $linerow['dispatchitemsid'] . '" /></td>
			</tr>';
	} // end while order line detail
	echo '</table>
			</td>
		</tr>';
} //end while header loop
echo '</table>';
echo '<div class="centre"><input type="submit" name="UpdateAll" value="' . _('Update') . '" /></div>
	</form>';

include('includes/footer.inc');
?>