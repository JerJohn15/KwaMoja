<?php

include('includes/session.php');
$Title = _('Search Purchase Orders');
include('includes/header.php');

if (isset($_GET['SelectedStockItem'])) {
	$SelectedStockItem = stripslashes($_GET['SelectedStockItem']);
} elseif (isset($_POST['SelectedStockItem'])) {
	$SelectedStockItem = $_POST['SelectedStockItem'];
}
if (isset($_GET['OrderNumber'])) {
	$OrderNumber = $_GET['OrderNumber'];
} elseif (isset($_POST['OrderNumber'])) {
	$OrderNumber = $_POST['OrderNumber'];
}
if (isset($_GET['SelectedSupplier'])) {
	$SelectedSupplier = stripslashes($_GET['SelectedSupplier']);
} elseif (isset($_POST['SelectedSupplier'])) {
	$SelectedSupplier = stripslashes($_POST['SelectedSupplier']);
}

echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title;
if (isset($SelectedSupplier)) {
	echo ' ' . _('for Supplier') . ': ' . $SelectedSupplier;
	echo '<input type="hidden" name="SelectedSupplier" value="' . $SelectedSupplier . '" />';
} //isset($SelectedSupplier)
if (isset($SelectedStockItem)) {
	if (isset($SelectedSupplier)) {
		echo ' ' . _('and') . ' ';
	}
	echo ' ' . _('for stock item') . ': ' . $SelectedStockItem;
	echo '<input type="hidden" name="SelectedStockItem" value="' . $SelectedStockItem . '" />';
} //isset($SelectedStockItem)
echo '</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($_POST['ResetPart'])) {
	unset($SelectedStockItem);
}
if (isset($OrderNumber) and $OrderNumber != '') {
	if (!is_numeric($OrderNumber)) {
		prnMsg(_('The Order Number entered') . ' <u>' . _('MUST') . '</u> ' . _('be numeric'), 'error');
		unset($OrderNumber);
	} else {
		echo _('Order Number') . ' - ' . $OrderNumber;
	}
} else {
	if (isset($SelectedSupplier)) {
		echo '<input type="hidden" name="SelectedSupplier" value="' . $SelectedSupplier . '" />';
	}
}
if (isset($_POST['SearchParts'])) {
	if ($_POST['Keywords'] and $_POST['StockCode']) {
		prnMsg(_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}
		//insert wildcard characters in spaces
	$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd) AS qord
				FROM stockmaster
				INNER JOIN locstock
					ON stockmaster.stockid = locstock.stockid
				INNER JOIN purchorderdetails
					ON stockmaster.stockid=purchorderdetails.itemcode
				WHERE purchorderdetails.completed=0
					AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units
				ORDER BY stockmaster.stockid";
	$ErrMsg = _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL, $ErrMsg, $DbgMsg);
}
/* Not appropriate really to restrict search by date since user may miss older
 * ouststanding orders
 * $OrdersAfterDate = Date("d/m/Y",Mktime(0,0,0,Date("m")-2,Date("d"),Date("Y")));
 */
if (!isset($OrderNumber) or $OrderNumber == "") {
	echo '<table class="selection"><tr><td>';
	echo _('Order Number') . ': <input type="text" class="integer" name="OrderNumber" autofocus="autofocus" maxlength="8" size="9" /> ' . _('Into Stock Location') . ':<select name="StockLocation"> ';
	$SQL = "SELECT locations.loccode,
					locationname
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1";
	$ResultStkLocs = DB_query($SQL);
	while ($MyRow = DB_fetch_array($ResultStkLocs)) {
		if (isset($_POST['StockLocation'])) {
			if ($MyRow['loccode'] == $_POST['StockLocation']) {
				echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
			}
		} elseif ($MyRow['loccode'] == $_SESSION['UserStockLocation']) {
			echo '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	echo '</select> ' . _('Order Status') . ':<select name="Status">';
	if (!isset($_POST['Status'])) {
		$_POST['Status'] = 'Pending';
	}
	if (!isset($_POST['Status']) or $_POST['Status'] == 'Pending_Authorised_Completed') {
		echo '<option selected="selected" value="Pending_Authorised_Completed">' . _('Pending/Authorised/Completed') . '</option>';
	} else {
		echo '<option value="Pending_Authorised_Completed">' . _('Pending/Authorised/Completed') . '</option>';
	}
	if (isset($_POST['Status']) and $_POST['Status'] == 'Pending') {
		echo '<option selected="selected" value="Pending">' . _('Pending') . '</option>';
	} else {
		echo '<option value="Pending">' . _('Pending') . '</option>';
	}
	if (isset($_POST['Status']) and $_POST['Status'] == 'Authorised') {
		echo '<option selected="selected" value="Authorised">' . _('Authorised') . '</option>';
	} else {
		echo '<option value="Authorised">' . _('Authorised') . '</option>';
	}
	if (isset($_POST['Status']) and $_POST['Status'] == 'Completed') {
		echo '<option selected="selected" value="Completed">' . _('Completed') . '</option>';
	} else {
		echo '<option value="Completed">' . _('Completed') . '</option>';
	}
	if (isset($_POST['Status']) and $_POST['Status'] == 'Cancelled') {
		echo '<option selected="selected" value="Cancelled">' . _('Cancelled') . '</option>';
	} else {
		echo '<option value="Cancelled">' . _('Cancelled') . '</option>';
	}
	if (isset($_POST['Status']) and $_POST['Status'] == 'Rejected') {
		echo '<option selected="selected" value="Rejected">' . _('Rejected') . '</option>';
	} else {
		echo '<option value="Rejected">' . _('Rejected') . '</option>';
	}
	echo '</select> <input type="submit" name="SearchOrders" value="' . _('Search Purchase Orders') . '" /></td>
		</tr>
		</table>';
}
$SQL = "SELECT categoryid,
			categorydescription
		FROM stockcategory
		ORDER BY categorydescription";
$Result1 = DB_query($SQL);

echo '<div class="page_help_text">' . _('To search for purchase orders for a specific part use the part selection facilities below') . '</div>';

echo '<table class="selection">
		<tr>
			<td><tr>
		<td>' . _('Select a stock category') . ':<select name="StockCat">';
while ($MyRow1 = DB_fetch_array($Result1)) {
	if (isset($_POST['StockCat']) and $MyRow1['categoryid'] == $_POST['StockCat']) {
		echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
	}
}
echo '</select></td>
		<td>' . _('Enter text extracts in the') . ' <b>' . _('description') . '</b>:</td>
		<td><input type="text" name="Keywords" size="20" maxlength="25" /></td>
	</tr>
	<tr>
		<td></td>
		<td><b>' . _('OR') . ' </b>' . _('Enter extract of the') . '<b> ' . _('Stock Code') . '</b>:</td>
		<td><input type="text" name="StockCode" size="15" maxlength="18" /></td>
	</tr>
	<tr>
		<td colspan="3">
			<div class="centre">
				<input type="submit" name="SearchParts" value="' . _('Search Parts Now') . '" />
				<input type="submit" name="ResetPart" value="' . _('Show All') . '" />
			</div>
		</td>
	</tr>
	</table>';

if (isset($StockItemsResult)) {
	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . _('Code') . '</th>
					<th class="SortedColumn">' . _('Description') . '</th>
					<th>' . _('On Hand') . '</th>
					<th>' . _('Orders') . '<br />' . _('Outstanding') . '</th>
					<th>' . _('Units') . '</th>
				</tr>
			</thead>';
	$k = 0; //row colour counter
	echo '<tbody>';
	while ($MyRow = DB_fetch_array($StockItemsResult)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		echo '<td><input type="submit" name="SelectedStockItem" value="' . $MyRow['stockid'] . '"</td>
				<td>' . $MyRow['description'] . '</td>
				<td class="number">' . locale_number_format($MyRow['qoh'], $MyRow['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($MyRow['qord'], $MyRow['decimalplaces']) . '</td>
				<td>' . $MyRow['units'] . '</td>
			</tr>';
	}
	//end of while loop
	echo '</tbody>';
	echo '</table>';
} else {
	//figure out the SQL required from the inputs available
	$Completed = 0;
	if (!isset($_POST['Status']) or $_POST['Status'] == 'Pending_Authorised_Completed') {
		$StatusCriteria = " AND (purchorders.status='Pending' OR purchorders.status='Authorised' OR purchorders.status='Printed' OR purchorders.status='Completed') ";
	} elseif ($_POST['Status'] == 'Authorised') {
		$StatusCriteria = " AND (purchorders.status='Authorised' OR purchorders.status='Printed')";
	} elseif ($_POST['Status'] == 'Pending') {
		$StatusCriteria = " AND purchorders.status='Pending' ";
	} elseif ($_POST['Status'] == 'Rejected') {
		$StatusCriteria = " AND purchorders.status='Rejected' ";
	} elseif ($_POST['Status'] == 'Cancelled') {
		$StatusCriteria = " AND purchorders.status='Cancelled' ";
	} elseif ($_POST['Status'] == 'Completed') {
		$StatusCriteria = " AND purchorders.status='Completed' ";
		$Completed = 1;
	}

	//If searching on supplier code
	if (isset($SelectedSupplier) and $SelectedSupplier != '') {
		$SupplierSearchString = " AND purchorders.supplierno='" . DB_escape_string($SelectedSupplier) . "' ";
	} else {
		$SupplierSearchString = '';
	}
	//If searching on order number
	if (isset($OrderNumber) and $OrderNumber != '') {
		$OrderNumberSearchString = " AND purchorders.orderno='" . $OrderNumber . "' ";
	} else {
		$OrderNumberSearchString = '';
	}
	//If searching on order number
	if (isset($SelectedStockItem) and $SelectedStockItem != '') {
		$StockItemSearchString = " AND purchorderdetails.itemcode='" . $SelectedStockItem . "' ";
	} else {
		$StockItemSearchString = '';
	}
	if (isset($_POST['StockLocation'])) {
		$LocationSearchString = " AND purchorders.intostocklocation = '" . $_POST['StockLocation'] . "' ";
	} else {
		$LocationSearchString = '';
	}

	$SQL = "SELECT purchorders.orderno,
					purchorders.realorderno,
					suppliers.suppname,
					purchorders.orddate,
					purchorders.deliverydate,
					purchorders.initiator,
					purchorders.status,
					purchorders.requisitionno,
					purchorders.allowprint,
					suppliers.currcode,
					currencies.decimalplaces AS currdecimalplaces,
					SUM(purchorderdetails.unitprice*purchorderdetails.quantityord) AS ordervalue
				FROM purchorders
				INNER JOIN purchorderdetails
					ON purchorders.orderno=purchorderdetails.orderno
				INNER JOIN suppliers
					ON purchorders.supplierno = suppliers.supplierid
				INNER JOIN currencies
					ON suppliers.currcode=currencies.currabrev
				WHERE purchorderdetails.completed='" . $Completed . "'
					" . $SupplierSearchString . "
					" . $StockItemSearchString . "
					" . $OrderNumberSearchString . "
					" . $StatusCriteria . "
					" . $LocationSearchString . "
				GROUP BY purchorders.orderno ASC,
						suppliers.suppname,
						purchorders.orddate,
						purchorders.status,
						purchorders.initiator,
						purchorders.requisitionno,
						purchorders.allowprint,
						suppliers.currcode";

	$ErrMsg = _('No orders were returned by the SQL because');
	$PurchOrdersResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($PurchOrdersResult) > 0) {
		/*show a table of the orders returned by the SQL */
		echo '<table cellpadding="2" width="90%" class="selection">
				<thead>
					<tr>
						<th class="SortedColumn">' . _('View') . '</th>
						<th class="SortedColumn">' . _('Supplier') . '</th>
						<th>' . _('Currency') . '</th>
						<th class="SortedColumn">' . _('Requisition') . '</th>
						<th class="SortedColumn">' . _('Order Date') . '</th>
						<th class="SortedColumn">' . _('Delivery Date') . '</th>
						<th class="SortedColumn">' . _('Initiator') . '</th>
						<th>' . _('Order Total') . '</th>
						<th>' . _('Status') . '</th>
					</tr>
				</thead>';
		$k = 0; //row colour counter
		echo '<tbody>';
		while ($MyRow = DB_fetch_array($PurchOrdersResult)) {
			if ($k == 1) {
				/*alternate bgcolour of row for highlighting */
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				++$k;
			}
			$ViewPurchOrder = $RootPath . '/PO_OrderDetails.php?OrderNo=' . $MyRow['orderno'];
			$FormatedOrderDate = ConvertSQLDate($MyRow['orddate']);
			$FormatedDeliveryDate = ConvertSQLDate($MyRow['deliverydate']);
			$FormatedOrderValue = locale_number_format($MyRow['ordervalue'], $MyRow['currdecimalplaces']);

			echo '<td><a href="' . $ViewPurchOrder . '">' . $MyRow['orderno'] . '</a></td>
					<td>' . $MyRow['suppname'] . '</td>
					<td>' . $MyRow['currcode'] . '</td>
					<td>' . $MyRow['requisitionno'] . '</td>
					<td>' . $FormatedOrderDate . '</td>
					<td>' . $FormatedDeliveryDate . '</td>
					<td>' . $MyRow['initiator'] . '</td>
					<td class="number">' . $FormatedOrderValue . '</td>
					<td>' . _($MyRow['status']) . '</td>
					</tr>';
			//$MyRow['status'] is a string which has gettext translations from PO_Header.php script

		}
		//end of while loop
		echo '</tbody>';
		echo '</table>';
	} // end if purchase orders to show
}
echo '</form>';
include('includes/footer.php');
?>