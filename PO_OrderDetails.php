<?php

include('includes/session.inc');

if (isset($_GET['OrderNo'])) {
	$Title = _('Reviewing Purchase Order Number') . ' ' . $_GET['OrderNo'];
	$_GET['OrderNo'] = (int) $_GET['OrderNo'];
} else {
	$Title = _('Reviewing A Purchase Order');
}
include('includes/header.inc');

if (isset($_GET['FromGRNNo'])) {

	$SQL = "SELECT purchorderdetails.orderno
				FROM purchorderdetails
				INNER JOIN grns
					ON purchorderdetails.podetailitem=grns.podetailitem
				WHERE grns.grnno='" . $_GET['FromGRNNo'] . "'";

	$ErrMsg = _('The search of the GRNs was unsuccessful') . ' - ' . _('the SQL statement returned the error');
	$OrderResult = DB_query($SQL, $ErrMsg);

	$OrderRow = DB_fetch_row($OrderResult);
	$_GET['OrderNo'] = $OrderRow[0];
	echo '<br /><h3>' . _('Order Number') . ' ' . $_GET['OrderNo'] . '</h3>';
}

if (!isset($_GET['OrderNo'])) {

	prnMsg(_('This page must be called with a purchase order number to review'), 'error');

	echo '<table class="table_index">
			<tr>
				<td class="menu_group_item">
					<li><a href="' . $RootPath . '/PO_SelectPurchOrder.php">' . _('Outstanding Purchase Orders') . '</a></li>
				</td>
			</tr>
		</table>';
	include('includes/footer.inc');
	exit;
}

$ErrMsg = _('The order requested could not be retrieved') . ' - ' . _('the SQL returned the following error');
$OrderHeaderSQL = "SELECT purchorders.*,
							suppliers.supplierid,
							suppliers.suppname,
							suppliers.currcode,
							locations.locationname,
							currencies.decimalplaces AS currdecimalplaces
						FROM purchorders
						INNER JOIN locationusers
							ON locationusers.loccode=purchorders.intostocklocation
							AND locationusers.userid='" .  $_SESSION['UserID'] . "'
							AND locationusers.canview=1
						INNER JOIN locations
							ON locations.loccode=purchorders.intostocklocation
						INNER JOIN suppliers
							ON purchorders.supplierno = suppliers.supplierid
						INNER JOIN currencies
							ON suppliers.currcode = currencies.currabrev
						WHERE purchorders.orderno = '" . $_GET['OrderNo'] . "'";

$GetOrdHdrResult = DB_query($OrderHeaderSQL, $ErrMsg);

if (DB_num_rows($GetOrdHdrResult) != 1) {
	if (DB_num_rows($GetOrdHdrResult) == 0) {
		prnMsg(_('Unable to locate this PO Number') . ' ' . $_GET['OrderNo'] . '. ' . _('Please look up another one') . '. ' . _('The order requested could not be retrieved') . ' - ' . _('the SQL returned either 0 or several purchase orders'), 'error');
	} else {
		prnMsg(_('The order requested could not be retrieved') . ' - ' . _('the SQL returned either several purchase orders'), 'error');
	}
	echo '<table class="table_index">
			<tr>
				<td class="menu_group_item">
					<li><a href="' . $RootPath . '/PO_SelectPurchOrder.php">' . _('Outstanding Purchase Orders') . '</a></li>
				</td>
			</tr>
		</table>';

	include('includes/footer.inc');
	exit;
}
// the checks all good get the order now

$MyRow = DB_fetch_array($GetOrdHdrResult);

if (!isset($MyRow['realname'])) {
	$MyRow['realname'] = $_SESSION['UsersRealName'];
}

/* SHOW ALL THE ORDER INFO IN ONE PLACE */
echo '<div class="toplink">
		<a href="' . $RootPath . '/PO_SelectPurchOrder.php">' . _('Outstanding Sales Orders') . '</a>
	</div>';
echo '<p class="page_title_text noPrint" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/supplier.png" title="' . _('Purchase Order') . '" alt="" />' . ' ' . $Title . '</p>';

echo '<table class="selection" cellpadding="2">
		<tr>
			<th colspan="8"><b>' . _('Order Header Details') . '</b></th>
		</tr>
		<tr>
			<td style="text-align:left">' . _('Supplier Code') . '</td>
			<td><a href="SelectSupplier.php?SupplierID=' . urlencode(stripslashes($MyRow['supplierid'])) . '">' . $MyRow['supplierid'] . '</a></td>
			<td style="text-align:left">' . _('Supplier Name') . '</td>
			<td><a href="SelectSupplier.php?SupplierID=' . urlencode(stripslashes($MyRow['supplierid'])) . '">' . $MyRow['suppname'] . '</a></td>
		</tr>
		<tr>
			<td style="text-align:left">' . _('Ordered On') . '</td>
			<td>' . ConvertSQLDate($MyRow['orddate']) . '</td>
			<td style="text-align:left">' . _('Delivery Address 1') . '</td>
			<td>' . $MyRow['deladd1'] . '</td>
		</tr>
		<tr>
			<td style="text-align:left">' . _('Order Currency') . '</td>
			<td>' . $MyRow['currcode'] . '</td>
			<td style="text-align:left">' . _('Delivery Address 2') . '</td>
			<td>' . $MyRow['deladd2'] . '</td>
		</tr>
		<tr>
			<td style="text-align:left">' . _('Exchange Rate') . '</td>
			<td>' . $MyRow['rate'] . '</td>
			<td style="text-align:left">' . _('Delivery Address 3') . '</td>
			<td>' . $MyRow['deladd3'] . '</td>
		</tr>
		<tr>
			<td style="text-align:left">' . _('Deliver Into Location') . '</td>
			<td>' . $MyRow['locationname'] . '</td>
			<td style="text-align:left">' . _('Delivery Address 4') . '</td>
			<td>' . $MyRow['deladd4'] . '</td>
		</tr>
		<tr>
			<td style="text-align:left">' . _('Initiator') . '</td>
			<td>' . $MyRow['realname'] . '</td>
			<td style="text-align:left">' . _('Delivery Address 5') . '</td>
			<td>' . $MyRow['deladd5'] . '</td>
		</tr>
		<tr>
			<td style="text-align:left">' . _('Requisition Ref') . '.</td>
			<td>' . $MyRow['requisitionno'] . '</td>
			<td style="text-align:left">' . _('Delivery Address 6') . '</td>
			<td>' . $MyRow['deladd6'] . '</td>
		</tr>
		<tr>
			<td style="text-align:left">' . _('Printing') . '</td>
			<td colspan="3">';

if ($MyRow['dateprinted'] == '') {
	echo '<i>' . _('Not yet printed') . '</i> &nbsp; &nbsp; ';
	echo '[<a class="ButtonLink" href="PO_PDFPurchOrder.php?OrderNo=' . urlencode($_GET['OrderNo']) . '">' . _('Print') . '</a>]';
} else {
	echo _('Printed on') . ' ' . ConvertSQLDate($MyRow['dateprinted']) . '&nbsp; &nbsp;';
	echo '[<a class="ButtonLink" href="PO_PDFPurchOrder.php?OrderNo=' . urlencode($_GET['OrderNo']) . '">' . _('Print a Copy') . '</a>]';
}

echo '</td></tr>';
echo '<tr>
		<td style="text-align:left">' . _('Status') . '</td>
		<td>' . _($MyRow['status']) . '</td>
	</tr>
	<tr>
		<td style="text-align:left">' . _('Comments') . '</td>
		<td colspan="3">' . $MyRow['comments'] . '</td>
	</tr>
	<tr>
		<td>' . _('Status Coments') . '</td>
		<td colspan="5" style="display:table">' . str_replace('<br />', '', html_entity_decode($MyRow['stat_comment'])) . '</td>
 	</tr>';

echo '</table>';

$CurrDecimalPlaces = $MyRow['currdecimalplaces'];

echo '<br />';
/*Now get the line items */
$ErrMsg = _('The line items of the purchase order could not be retrieved');
$LineItemsSQL = "SELECT purchorderdetails.*,
						stockmaster.decimalplaces
				FROM purchorderdetails
				LEFT JOIN stockmaster
				ON purchorderdetails.itemcode=stockmaster.stockid
				WHERE purchorderdetails.orderno = '" . $_GET['OrderNo'] . "'
				ORDER BY itemcode";
/*- ADDED: Sort by our item code -*/

$LineItemsResult = DB_query($LineItemsSQL, $ErrMsg);


echo '<table class="selection" cellpadding="0">';
echo '<tr>
		<th colspan="8"><b>' . _('Order Line Details') . '</b></th>
	</tr>';
echo '<tr>
		<th>' . _('Item Code') . '</th>
		<th>' . _('Item Description') . '</th>
		<th>' . _('Ord Qty') . '</th>
		<th>' . _('Qty Recd') . '</th>
		<th>' . _('Qty Inv') . '</th>
		<th>' . _('Ord Price') . '</th>
		<th>' . _('Chg Price') . '</th>
		<th>' . _('Reqd Date') . '</th>
	</tr>';

$k = 0; //row colour counter
$OrderTotal = 0;
$RecdTotal = 0;

while ($MyRow = DB_fetch_array($LineItemsResult)) {

	$OrderTotal += ($MyRow['quantityord'] * $MyRow['unitprice']);
	$RecdTotal += ($MyRow['quantityrecd'] * $MyRow['unitprice']);

	$DisplayReqdDate = ConvertSQLDate($MyRow['deliverydate']);
	if ($MyRow['decimalplaces'] != NULL) {
		$DecimalPlaces = $MyRow['decimalplaces'];
	} else {
		$DecimalPlaces = 2;
	}
	// if overdue and outstanding quantities, then highlight as so
	if (($MyRow['quantityord'] - $MyRow['quantityrecd'] > 0) AND Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat']), $DisplayReqdDate)) {
		echo '<tr class="OsRow">';
	} else {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
	}

	printf('<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td>%s</td>
		</tr>', $MyRow['itemcode'], stripslashes($MyRow['itemdescription']), locale_number_format($MyRow['quantityord'], $DecimalPlaces), locale_number_format($MyRow['quantityrecd'], $DecimalPlaces), locale_number_format($MyRow['qtyinvoiced'], $DecimalPlaces), locale_number_format($MyRow['unitprice'], $CurrDecimalPlaces), locale_number_format($MyRow['actprice'], $CurrDecimalPlaces), $DisplayReqdDate);

}

echo '<tr><td><br /></td>
	</tr>
	<tr><td colspan="4" class="number">' . _('Total Order Value Excluding Tax') . '</td>
	<td colspan="2" class="number">' . locale_number_format($OrderTotal, $CurrDecimalPlaces) . '</td></tr>';
echo '<tr>
	<td colspan="4" class="number">' . _('Total Order Value Received Excluding Tax') . '</td>
	<td colspan="2" class="number">' . locale_number_format($RecdTotal, $CurrDecimalPlaces) . '</td></tr>';
echo '</table>';

echo '<br />';

include('includes/footer.inc');
?>