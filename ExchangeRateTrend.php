<?php

include('includes/session.inc');
$Title = _('View Currency Trends');

include('includes/header.inc');

$FunctionalCurrency = $_SESSION['CompanyRecord']['currencydefault'];

if (isset($_GET['CurrencyToShow'])) {
	$CurrencyToShow = $_GET['CurrencyToShow'];
} elseif (isset($_POST['CurrencyToShow'])) {
	$CurrencyToShow = $_POST['CurrencyToShow'];
}

// ************************
// SHOW OUR MAIN INPUT FORM
// ************************

echo '<form onSubmit="return VerifyForm(this);" method="post" class="noPrint" id="update" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text noPrint" >
		<img src="' . $RootPath . '/css/' . $Theme . '/images/money_add.png" title="' . _('View Currency Trend') . '" alt="" />' . ' ' . _('View Currency Trend') . '
	</p>';
echo '<table>'; // First column

$SQL = "SELECT currabrev,
				currency
			FROM currencies";
$result = DB_query($SQL);

// CurrencyToShow Currency Picker
echo '<tr>
		<td>
			<select minlength="0" name="CurrencyToShow" onchange="ReloadForm(update.submit)">';

while ($MyRow = DB_fetch_array($result)) {
	if ($MyRow['currabrev'] != $_SESSION['CompanyRecord']['currencydefault']) {
		if ($CurrencyToShow == $MyRow['currabrev']) {
			echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . _($MyRow['currency']) . '&nbsp;(' . $MyRow['currabrev'] . ')' . '</option>';
		} else {
			echo '<option value="' . $MyRow['currabrev'] . '">' . _($MyRow['currency']) . '&nbsp;(' . $MyRow['currabrev'] . ')' . '</option>';
		}
	}
}
echo '</select>
			</td>
		</tr>
	</table>
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Accept') . '" />
	</div>
</form>';

// **************
// SHOW OUR GRAPH
// **************
$image = 'http://www.google.com/finance/getchart?q=' . $FunctionalCurrency . $CurrencyToShow . '&amp;x=CURRENCY&amp;p=3M&amp;i=86400';

echo '<table class="selection">
		<tr>
			<th>
				<div class="centre">
					<b>' . $FunctionalCurrency . ' / ' . $CurrencyToShow . '</b>
				</div>
			</th>
		</tr>
		<tr>
			<td><img src="' . $image . '" alt="' . _('Trend Currently Unavailable') . '" /></td>
		</tr>
	</table>';

include('includes/footer.inc');
?>