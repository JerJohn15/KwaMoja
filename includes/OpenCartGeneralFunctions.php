<?php

function GetServerTimeNow($TimeDifference){
	// webERP DB and OpenCart DB triggers happens on server time, not local time,
	// so when checking if a row has been updated or created in webERP or OC, we need to check the timestamp against ServerTime :-)
	// 4 hours of my life were invested finding it out...
	$Now = Date('Y-m-d H:i:s');
	$ServerNow = date('Y-m-d H:i:s', strtotime( $Now . $TimeDifference . ' hours')); 
	return $ServerNow;
}

function CheckLastTimeRun($Script, $db){
	if ($Script == 'OpenCartToWeberp'){
		$ConfigName = 'OpenCartToWeberp_LastRun';
	}elseif ($Script == 'WeberpToOpenCartHourly'){
		$ConfigName = 'WeberpToOpenCartHourly_LastRun';
	}elseif ($Script == 'WeberpToOpenCartDaily'){
		$ConfigName = 'WeberpToOpenCartDaily_LastRun';
	}
	$sql = "SELECT confvalue
			FROM config
			WHERE confname = '". $ConfigName ."'";
	$result = DB_query($sql, $db);
	if (DB_num_rows($result)==0){
		return  "2999-12-31"; // Error, so we will not change anything
	} else {
		$myrow = DB_fetch_array($result);
		return  $myrow['confvalue'];
	}
}

function SetLastTimeRun($Script, $db){
	$ServerNow = GetServerTimeNow(SERVER_TO_LOCAL_TIME_DIFFERENCE);
	if ($Script == 'OpenCartToWeberp'){
		$_SESSION['OpenCartToWeberp_LastRun'] = $ServerNow;
		$sql = "UPDATE config
				SET confvalue = '" . $ServerNow ."'
				WHERE confname = 'OpenCartToWeberp_LastRun'";
	}elseif ($Script == 'WeberpToOpenCartHourly'){
		$_SESSION['WeberpToOpenCartHourly_LastRun'] = $ServerNow;
		$sql = "UPDATE config
				SET confvalue = '" . $ServerNow ."'
				WHERE confname = 'WeberpToOpenCartHourly_LastRun'";
	}elseif ($Script == 'WeberpToOpenCartDaily'){
		$_SESSION['WeberpToOpenCartDaily_LastRun'] = $ServerNow;
		$sql = "UPDATE config
				SET confvalue = '" . $ServerNow ."'
				WHERE confname = 'WeberpToOpenCartDaily_LastRun'";
	}
	$ErrMsg =_('Could not update Last Run Time of this script because');
	$result = DB_query($sql,$db,$ErrMsg);
}

function DataExistsInOpenCart($db_oc, $table, $f1, $v1, $f2 = '', $v2 = ''){
	if ($f2 == ''){
		/* Primary key is 1 field only */
		$SQL = "SELECT COUNT(*)
				FROM " . $table . "
				WHERE " . $f1 . " = '" . $v1 . "'";
	}else{
		/* Primary key is 2 fields */
		$SQL = "SELECT COUNT(*)
				FROM " . $table . "
				WHERE " . $f1 . " = '" . $v1 . "'
					AND " . $f2 . " = '" . $v2 . "'";
	}
	$ErrMsg =_('Could not check existence of data in OpenCart because');
	$result = DB_query($SQL,$db_oc,$ErrMsg);
	
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$Exists = ($myrow[0] > 0);
	}else{
		$Exists = false;
	}
	return $Exists;
}

function DataExistsInWebERP($db, $table, $f1, $v1, $f2 = '', $v2 = ''){
	if ($f2 == ''){
		/* Primary key is 1 field only */
		$SQL = "SELECT COUNT(*)
				FROM " . $table . "
				WHERE " . $f1 . " = '" . $v1 . "'";
	}else{
		/* Primary key is 2 fields */
		$SQL = "SELECT COUNT(*)
				FROM " . $table . "
				WHERE " . $f1 . " = '" . $v1 . "'
					AND " . $f2 . " = '" . $v2 . "'";
	}
	$ErrMsg =_('Could not check existence of data in webERP because');
	$result = DB_query($SQL,$db,$ErrMsg);
	
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		$Exists = ($myrow[0] > 0);
	}else{
		$Exists = false;
	}
	return $Exists;
}


function GetLenghtClassId($webERPDimensions, $language_id, $db_oc, $oc_tableprefix){
	$SQL = "SELECT length_class_id
			FROM " . $oc_tableprefix . "length_class_description
			WHERE unit = '" . $webERPDimensions . "'
				AND language_id = '" . $language_id . "'";
	$ErrMsg =_('Could not get the LenghtClassId in OpenCart because');
	$result = DB_query($SQL,$db_oc,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}

function GetOpenCartProductId($model, $db_oc, $oc_tableprefix){
	$SQL = "SELECT product_id
			FROM " . $oc_tableprefix . "product
			WHERE model = '" . $model . "'";
	$ErrMsg =_('Could not get the ProductId in OpenCart because');
	$result = DB_query($SQL,$db_oc,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}

function GetWeberpCustomerIdFromEmail($email, $db){
	$SQL = "SELECT debtorno
			FROM custbranch
			WHERE email = '" . $email . "'";
	$ErrMsg =_('Could not get the CustomerId in webERP because');
	$result = DB_query($SQL,$db,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}

function GetWeberpCustomerIdFromCurrency($Currency, $db){
	return WEBERP_ONLINE_CUSTOMER_CODE_PREFIX . $Currency;
}

function GetWeberpGLAccountFromCurrency($Currency, $db){
	if($Currency == "AUD"){
		return WEBERP_GL_PAYPAL_ACCOUNT_AUD;
	}else	if($Currency == "EUR"){
		return WEBERP_GL_PAYPAL_ACCOUNT_EUR;
	}else	if($Currency == "USD"){
		return WEBERP_GL_PAYPAL_ACCOUNT_USD;
	}
	// in Paypal there is no IDR yet, so we pay by bank trasnfer and record payment manually in webERP
}

function GetWeberpGLCommissionAccountFromCurrency($Currency, $db){
	if($Currency == "AUD"){
		return WEBERP_GL_PAYPAL_COMMISSION_AUD;
	}else	if($Currency == "EUR"){
		return WEBERP_GL_PAYPAL_COMMISSION_EUR;
	}else	if($Currency == "USD"){
		return WEBERP_GL_PAYPAL_COMMISSION_USD;
	}
	// in Paypal there is no IDR yet, so we pay by bank trasnfer and record payment manually in webERP
}

function GetWeberpOrderNo($CustomerId, $OrderId, $db){
	$SQL = "SELECT orderno
			FROM salesorders
			WHERE debtorno = '" . $CustomerId . "'
				AND branchcode = '" . $CustomerId . "'
				AND customerref = '" . $OrderId . "'";
	$ErrMsg =_('Could not get the OrderNo in webERP because');
	$result = DB_query($SQL,$db,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}


function GetWeberpCustomerCurrency($CustomerId, $db){
	$SQL = "SELECT currcode
			FROM debtorsmaster 
			WHERE debtorno = '" . $CustomerId . "'";
	$ErrMsg =_('Could not get the CustomerCurrency in webERP because');
	$result = DB_query($SQL,$db,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}

function GetWeberpCurrencyRate($CurrencyCode, $db){
	$SQL = "SELECT rate
			FROM currencies 
			WHERE currabrev = '" . $CurrencyCode . "'";
	$ErrMsg =_('Could not get the Currency Rate in webERP because');
	$result = DB_query($SQL,$db,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return '';
	}
}

function GetTotalTitleFromOrder($Concept, $OrderId, $db_oc, $oc_tableprefix){
	$SQL = "SELECT title
			FROM " . $oc_tableprefix . "order_total
			WHERE order_id = '" . $OrderId . "'
				AND code = '" . $Concept . "'";
	$ErrMsg =_('Could not get the '. $Concept . ' title from OpenCart because');
	$result = DB_query($SQL,$db_oc,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return 0;
	}
}

function GetTotalFromOrder($Concept, $OrderId, $db_oc, $oc_tableprefix){
	$SQL = "SELECT SUM(value)
			FROM " . $oc_tableprefix . "order_total
			WHERE order_id = '" . $OrderId . "'
				AND code = '" . $Concept . "'";
	$ErrMsg =_('Could not get the '. $Concept . ' total from OpenCart because');
	$result = DB_query($SQL,$db_oc,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return 0;
	}
}

function GetOnlineQOH($StockId, $db){
	$SQL = "SELECT SUM(quantity)
			FROM locstock
			WHERE stockid = '" . $StockId . "'
			AND loccode IN ('" . str_replace(',', "','", $_SESSION['ShopStockLocations']) . "')";
	$ErrMsg =_('Could not get the QOH available in webERP for OpenCart because');
	$result = DB_query($SQL,$db,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return 0;
	}
}

function GetOnlinePriceList($db){
	$SQL = "SELECT debtorsmaster.currcode,
				debtorsmaster.salestype
			FROM debtorsmaster
			WHERE debtorsmaster.debtorno = '" . $_SESSION['ShopDebtorNo'] . "'";	
	$result = DB_query($SQL, $db);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return array($myrow['salestype'], $myrow['currcode']);
	}else{
		return array(0,0);
	}
}

function GetDiscount($DiscountCategory, $Quantity, $PriceList, $db){
	/* Select the disount rate from the discount Matrix */
	$result = DB_query("SELECT MAX(discountrate) AS discount
						FROM discountmatrix
						WHERE salestype='" .  $PriceList . "'
						AND discountcategory ='" . $DiscountCategory . "'
						AND quantitybreak <= '" .$Quantity ."'",$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]==NULL){
		$DiscountMatrixRate = 0;
	} else {
		$DiscountMatrixRate = $myrow[0];
	}
	return $DiscountMatrixRate;
}

function MaintainOpenCartDiscountForItem($ProductId, $Price, $DiscountCategory, $PriceList, $db, $db_oc, $oc_tableprefix){

	$CustomerGroupId = 1;
	$Priority = 1;

	if ($DiscountCategory == ''){
		// ProductId has no discount in webERP
		// so we delete it in OpenCart
		$SQL = "DELETE FROM " . $oc_tableprefix . WEBERP_DISCOUNTS_IN_OPENCART_TABLE . "
				WHERE product_id = '" . $ProductId . "'";
		$DeleteErrMsg = _('The SQL to delete the product discount in Opencart table ') . ' ' . WEBERP_DISCOUNTS_IN_OPENCART_TABLE . ' ' . ('failed');
		$resultDelete = DB_query($SQL,$db_oc,$DeleteErrMsg,$DbgMsg,true);
	}else{
		// ProductId has some discount in webERP
		// so replicate all the discounts in OpenCart
		$SQL = "SELECT quantitybreak,
						discountrate
				FROM discountmatrix
				WHERE salestype = '" . $PriceList . "'
					AND discountcategory = '" . $DiscountCategory . "'
				ORDER BY quantitybreak";
		$ErrMsg =_('Could not get the discount matrix in webERP because');
		$result = DB_query($SQL,$db,$ErrMsg);
		if(DB_num_rows($result) != 0){
			while ($myrow = DB_fetch_array($result)){
				$DiscountedPrice = round($Price * (1 - $myrow['discountrate']),2);
				UpdateDiscountInOpenCart($ProductId, $CustomerGroupId, $myrow['quantitybreak'], $Priority, $DiscountedPrice, $db_oc, $oc_tableprefix);
			}
		}		
	}
}

function UpdateDiscountInOpenCart($ProductId, $CustomerGroupId, $Quantity, $Priority, $DiscountedPrice, $db_oc, $oc_tableprefix){
	if (WEBERP_DISCOUNTS_IN_OPENCART_TABLE == 'product_discount'){
		/* use the table product_discount */ 
		$SQL = "SELECT product_discount_id
				FROM " . $oc_tableprefix . "product_discount
				WHERE productid = '" . $ProductId . "'
					AND quantity = '" . $Quantity . "' 
					AND customer_group_id = '" . $CustomerGroupId ."'";
					
		$ErrMsg =_('Could not get the product discount in OpenCart because');
		$result = DB_query($SQL,$db,$ErrMsg);
		if(DB_num_rows($result) != 0){
			// There is already a discount, so we need to update it
			$SQL = "UPDATE " . $oc_tableprefix . "product_discount
					SET quantity = '" . $Quantity . "'
						priority = '" . $Priority . "'
						price = '" . $DiscountedPrice . "'
					WHERE product_id = '" . $ProductId . "'	
						AND quantity = '" . $Quantity . "' 
						AND customer_group_id = '" . $CustomerGroupId ."'";
			$UpdateErrMsg = _('The SQL to update the product discount in Opencart failed');
			$resultUpdate = DB_query($SQL,$db_oc,$UpdateErrMsg,$DbgMsg,true);
		}else{
			// there is no discount in OpenCart yet, so we need to create one
			$SQL = "INSERT INTO " . $oc_tableprefix . "product_discount
						(product_id,
						customer_group_id,
						quantity,
						priority,
						price)
					VALUES (
						'" . $ProductId . "',
						'" . $CustomerGroupId . "',
						'" . $Quantity . "',
						'" . $Priority . "',
						'" . $DiscountedPrice . "'
					)";
			$InsertErrMsg = _('The SQL to insert the product discount in Opencart failed');
			$resultUpdate = DB_query($SQL,$db_oc,$InsertErrMsg,$DbgMsg,true);
		}
	}else{
		/* use the table product_special */ 
		$SQL = "SELECT product_special_id
				FROM " . $oc_tableprefix . "product_special
				WHERE productid = '" . $ProductId . "'
					AND customer_group_id = '" . $CustomerGroupId ."'";
					
		$ErrMsg =_('Could not get the product special in OpenCart because');
		$result = DB_query($SQL,$db,$ErrMsg);
		if(DB_num_rows($result) != 0){
			// There is already a special, so we need to update it
			$SQL = "UPDATE " . $oc_tableprefix . "product_special
					SET priority = '" . $Priority . "'
						price = '" . $DiscountedPrice . "'
					WHERE product_id = '" . $ProductId . "'	
						AND customer_group_id = '" . $CustomerGroupId ."'";
			$UpdateErrMsg = _('The SQL to update the product special in Opencart failed');
			$resultUpdate = DB_query($SQL,$db_oc,$UpdateErrMsg,$DbgMsg,true);
		}else{
			// there is no special in OpenCart yet, so we need to create one
			$SQL = "INSERT INTO " . $oc_tableprefix . "product_special
						(product_id,
						customer_group_id,
						priority,
						price)
					VALUES (
						'" . $ProductId . "',
						'" . $CustomerGroupId . "',
						'" . $Priority . "',
						'" . $DiscountedPrice . "'
					)";
			$InsertErrMsg = _('The SQL to insert the product special in Opencart failed');
			$resultUpdate = DB_query($SQL,$db_oc,$InsertErrMsg,$DbgMsg,true);
		}
	}
}

function GetOpenCartSettingId($Store, $Group, $Key, $db_oc, $oc_tableprefix){
	$SQL = "SELECT setting_id
			FROM " . $oc_tableprefix . "setting
			WHERE store_id = '" . $Store . "'
				AND `group` = '" . $Group . "'
				AND `key` = '" . $Key . "'";
	$ErrMsg =_('Could not get the SettingId in OpenCart because');
	$result = DB_query($SQL,$db_oc,$ErrMsg);
	if(DB_num_rows($result) != 0){
		$myrow = DB_fetch_array($result);
		return $myrow[0];
	}else{
		return 0;
	}
}

function UpdateSettingValueOpenCart($SettingId, $Value, $db_oc, $oc_tableprefix){
	$DbgMsg = _('The SQL statement that failed was');
	$UpdateErrMsg = _('The SQL to update setting value in Opencart failed');
	$sqlUpdate = "UPDATE " . $oc_tableprefix . "setting 
					SET	value = '" . $Value . "'
				WHERE setting_id = '" . $SettingId . "'";
	$resultUpdate = DB_query($sqlUpdate,$db_oc,$UpdateErrMsg,$DbgMsg,true);
}

function CreateMetaDescription($Group, $Item){
	$MetaDescription = $_SESSION['ShopName'] . ' ' . $Group . ' ' . $Item;
	return $MetaDescription;
}

function CreateMetaKeyword($Group, $Item){
	$MetaKeyword = $_SESSION['ShopName'] . ' ' . $Group . ' ' . $Item;
	$MetaKeyword = str_ireplace(' ', ',', $MetaKeyword);
	$MetaKeyword = str_ireplace(',', ',', $MetaKeyword);
	$MetaKeyword = str_ireplace(';', ',', $MetaKeyword);
	$MetaKeyword = str_ireplace('.', ',', $MetaKeyword);
	return $MetaKeyword;
}

function CreateSEOKeyword($KeyWord){
	$SEOKeyword =trim($KeyWord);
	$SEOKeyword = str_ireplace(' ', '-', $SEOKeyword);
	$SEOKeyword = str_ireplace(',', '-', $SEOKeyword);
	$SEOKeyword = str_ireplace(';', '-', $SEOKeyword);
	$SEOKeyword = str_ireplace('.', '-', $SEOKeyword);
	return $SEOKeyword;
}


Function GetNextSequenceNo ($SequenceType){

	global $db;
/* SQL to get the next transaction number these are maintained in the table SysTypes - Transaction Types
Also updates the transaction number

10 sales invoice
11 sales credit note
12 sales receipt
etc
*
*/

	DB_query("LOCK TABLES systypes WRITE",$db);

	$SQL = "SELECT typeno FROM systypes WHERE typeid = '" . $SequenceType . "'";

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': <BR>' . _('The next transaction number could not be retrieved from the database because');
	$DbgMsg =  _('The following SQL to retrieve the transaction number was used');
	$GetTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	$myrow = DB_fetch_row($GetTransNoResult);

	$SQL = "UPDATE systypes SET typeno = '" . ($myrow[0] + 1) . "' WHERE typeid = '" . $SequenceType . "'";
	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The transaction number could not be incremented');
	$DbgMsg =  _('The following SQL to increment the transaction number was used');
	$UpdTransNoResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	DB_query("UNLOCK TABLES",$db);

	return $myrow[0] + 1;
}

function InsertCustomerReceipt ($CustomerCode, $AmountPaid, $CustomerCurrency, $Rate, $BankAccount, $PaymentSystem, $TransactionID, $OrderNo, $PeriodNo, $db) {
	
	$CustomerReceiptNo = GetNextSequenceNo(12);
	
	$HeaderSQL = "INSERT INTO debtortrans (transno,
											type,
											debtorno,
											branchcode,
											trandate,
											inputdate,
											prd,
											reference,
											order_,
											rate,
											ovamount,
											invtext )
							VALUES ('". $CustomerReceiptNo  . "',
									'12', 
									'" . $CustomerCode . "',
									'" . $CustomerCode . "',
									'" . Date('Y-m-d H:i') . "',
									'" . Date('Y-m-d H:i') . "',
									'" . $PeriodNo . "',
									'" . $TransactionID ."',
									'". $OrderNo . "',
									'" . $Rate . "',
									'" . round(-$AmountPaid,2) . "',
									'" . $PaymentSystem . _(' OC Payment') . "')";

	$DbgMsg = _('The SQL that failed was');
	$ErrMsg = _('The customer receipt cannot be added because');
	$InsertQryResult = DB_query($HeaderSQL,$db,$ErrMsg,$DbgMsg);

	$SQL = "UPDATE debtorsmaster
				SET lastpaiddate = '" . Date('Y-m-d') . "',
				lastpaid='" . $AmountPaid ."'
			WHERE debtorsmaster.debtorno='" . $CustomerCode . "'";

	$DbgMsg = _('The SQL that failed to update the date of the last payment received was');
	$ErrMsg = _('Cannot update the customer record for the date of the last payment received because');
	$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	/*now enter the BankTrans entry */
	//First get the currency and rate for the bank account
	$BankResult = DB_query("SELECT rate FROM bankaccounts INNER JOIN currencies ON bankaccounts.currcode=currencies.currabrev WHERE accountcode='" . $BankAccount . "'",$db);
	$BankRow = DB_fetch_array($BankResult);
	$FunctionalRate = $BankRow['rate'];

	$SQL="INSERT INTO banktrans (type,
								transno,
								bankact,
								ref,
								exrate,
								functionalexrate,
								transdate,
								banktranstype,
								amount,
								currcode)
		VALUES (12,
			'" . $CustomerReceiptNo . "',
			'" . $BankAccount . "',
			'" . _('OC Receipt') . ' ' . $CustomerCode . ' ' . $TransactionID  . "',
			'" . $Rate / $FunctionalRate  . "',
			'" . $FunctionalRate . "',
			'" . Date('Y-m-d') . "',
			'" . $PaymentSystem . ' ' . _('online') . "',
			'" . ($AmountPaid * $Rate / $FunctionalRate) . "',
			'" . $CustomerCurrency . "'
		)";
	$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
	$ErrMsg = _('Cannot insert a bank transaction');
	$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


	// Insert GL entries too if integration enabled
	
	if ($_SESSION['CompanyRecord']['gllink_debtors']==1){ /* then enter GLTrans records for discount, bank and debtors */
		/* Bank account entry first */
		$Narrative = $CustomerCode . ' ' . _('payment for order') . ' ' . $OrderNo . ' ' . _('Transaction ID') . ': ' . $TransactionID;
		$SQL="INSERT INTO gltrans (	type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
				VALUES (12,
						'" . $CustomerReceiptNo . "',
						'" . Date('Y-m-d') . "',
						'" . $PeriodNo . "',
						'" . $BankAccount . "',
						'" . $Narrative . "',
						'" . $AmountPaid /$Rate . "'
					)";
		$DbgMsg = _('The SQL that failed to insert the GL transaction for the bank account debit was');
		$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	/* Now Credit Debtors account with receipts + discounts */
		$SQL="INSERT INTO gltrans ( type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
					VALUES (12,
							'" . $CustomerReceiptNo . "',
							'" . Date('Y-m-d') . "',
							'" . $PeriodNo . "',
							'". $_SESSION['CompanyRecord']['debtorsact'] . "',
							'" . $Narrative . "',
							'" . -($AmountPaid /$Rate). "' )";
		$DbgMsg = _('The SQL that failed to insert the GL transaction for the debtors account credit was');
		$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		EnsureGLEntriesBalanceOpenCart(12,$CustomerReceiptNo);
	} //end if there is GL work to be done - ie config is to link to GL
}

function EnsureGLEntriesBalanceOpenCart ($TransType, $TransTypeNo) {
	/*Ensures general ledger entries balance for a given transaction */
	global $db;

	$result = DB_query("SELECT SUM(amount)
						FROM gltrans
						WHERE type = '" . $TransType . "'
						AND typeno = '" . $TransTypeNo . "'",
						$db);
	$myrow = DB_fetch_row($result);
	$Difference = $myrow[0];
	if (abs($Difference)!=0){
		if (abs($Difference)>0.1){
			message_log(_('The general ledger entries created do not balance. See your system administrator'),'error');
		} else {
			$result = DB_query("SELECT counterindex,
										MAX(amount)
								FROM gltrans
								WHERE type = '" . $TransType . "'
								AND typeno = '" . $TransTypeNo . "'
								GROUP BY counterindex",
								$db);
			$myrow = DB_fetch_array($result);
			$TransToAmend = $myrow['counterindex'];
			$result = DB_query("UPDATE gltrans SET amount = amount - " . $Difference . "
								WHERE counterindex = '" . $TransToAmend . "'",
								$db);

		}
	}
}

function TransactionCommissionGL ($CustomerCode, $BankAccount, $CommissionAccount, $Commission, $Currency, $Rate, $PaymentSystem, $TransactionID, $PeriodNo, $db) {
	
	$PaymentNo = GetNextSequenceNo(1);

	/*now enter the BankTrans entry */
	//First get the currency and rate for the bank account
	$BankResult = DB_query("SELECT rate FROM bankaccounts INNER JOIN currencies ON bankaccounts.currcode=currencies.currabrev WHERE accountcode='" . $BankAccount . "'",$db);
	$BankRow = DB_fetch_array($BankResult);
	$FunctionalRate = $BankRow['rate'];

	$SQL="INSERT INTO banktrans (type,
								transno,
								bankact,
								ref,
								exrate,
								functionalexrate,
								transdate,
								banktranstype,
								amount,
								currcode)
						VALUES (1,
							'" . $PaymentNo . "',
							'" . $BankAccount . "',
							'" . $PaymentSystem . ' ' . _('Transaction Fees') . ' ' . $CustomerCode . ' ' . $TransactionID  . "',
							'" . $Rate / $FunctionalRate  . "',
							'" . $FunctionalRate . "',
							'" . Date('Y-m-d') . "',
							'" . $PaymentSystem . ' ' . _('Transaction Fees') . "',
							'" . -($Commission * $Rate / $FunctionalRate) . "',
							'" .$Currency . "'
						)";
	$DbgMsg = _('The SQL that failed to insert the bank account transaction was');
	$ErrMsg = _('Cannot insert a bank transaction');
	$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);


	// Insert GL entries too if integration enabled
	
	if ($_SESSION['CompanyRecord']['gllink_debtors']==1){ /* then enter GLTrans records for discount, bank and debtors */
		/* Bank account entry first */
		$Narrative = $CustomerCode . ' ' . $PaymentSystem . ' ' . _('Fees for Transaction ID') . ': ' . $TransactionID;
		$SQL="INSERT INTO gltrans (	type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
				VALUES (1,
						'" . $PaymentNo . "',
						'" . Date('Y-m-d') . "',
						'" . $PeriodNo . "',
						'" . $BankAccount . "',
						'" . $Narrative . "',
						'" . -($Commission /$Rate) . "'
					)";
		$DbgMsg = _('The SQL that failed to insert the Paypal transaction fee from the bank account debit was');
		$ErrMsg = _('Cannot insert a GL transaction for the bank account debit');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	/* Now Credit Debtors account with receipts + discounts */
		$SQL="INSERT INTO gltrans ( type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
					VALUES (1,
							'" . $PaymentNo . "',
							'" . Date('Y-m-d') . "',
							'" . $PeriodNo . "',
							'". $CommissionAccount . "',
							'" . $Narrative . "',
							'" . ($Commission /$Rate). "' )";
		$DbgMsg = _('The SQL that failed to insert the Paypal transaction fee for the commission account credit was');
		$ErrMsg = _('Cannot insert a GL transaction for the debtors account credit');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		EnsureGLEntriesBalanceOpenCart(1,$PaymentNo);
	} //end if there is GL work to be done - ie config is to link to GL
}

function ChangeOrderQuotationFlag($OrderNo, $Flag, $db){
	$DbgMsg = _('The SQL that failed was');
	$ErrMsg = _('The Change of quotation flag in salesorders table');
	$sqlUpdate = "UPDATE salesorders
					SET quotation = " . $Flag . " 
					WHERE orderno = '" . $OrderNo . "'";
	$resultUpdate = DB_query($sqlUpdate,$db,$ErrMsg,$DbgMsg,true);
}

function GetPaypalReturnDataInArray($RawData){
	$ResponseArray = Array();
	$MainArray = explode(',', str_replace(array('{', '}', '"'), "", $RawData));
	foreach ($MainArray as $i => $value) {
		$TmpArray = explode(':', $value);
		if(sizeof($TmpArray) > 1) {
			$ResponseArray[$TmpArray[0]] = $TmpArray[1];
		}
	}
	return $ResponseArray;
}

function MaintainUrlAlias($SEOQuery, $SEOKeyword, $db_oc, $oc_tableprefix){
	// search if we already have it
	$SQL = "SELECT url_alias_id
			FROM " . $oc_tableprefix . "url_alias
			WHERE query = '" . $$SEOQuery . "'";
	$ErrMsg =_('Could not get the UrlAlias in Opencart because');
	$result = DB_query($SQL,$db_oc,$ErrMsg);
	if(DB_num_rows($result) != 0){
		// if we have it, we update it
		$myrow = DB_fetch_array($result);
		$AliasId = $myrow['url_alias_id'];
		$DbgMsg = _('The SQL that failed was');
		$ErrMsg = _('The MaintainUrlAlias function failed');
		$sqlUpdate = "UPDATE " . $oc_tableprefix . "url_alias SET
						keyword ='" . $SEOKeyword . "'
					WHERE url_alias_id = '" . $$AliasId . "'";
		$resultUpdate = DB_query($sqlUpdate,$db_oc,$ErrMsg,$DbgMsg,true);
	}else{
		// otherwise we insert it
		$DbgMsg = _('The SQL that failed was');
		$ErrMsg = _('The MaintainUrlAlias function failed');
		$sqlInsert = "INSERT INTO " . $oc_tableprefix . "url_alias
						(query,
						keyword)
					VALUES
						('" . $SEOQuery . "',
						'" . $SEOKeyword . "'
						)";
		$resultInsert = DB_query($sqlInsert,$db_oc,$ErrMsg,$DbgMsg,true);	
	}
}

function UpdateOpenCartOrderStatus($OrderId, $Value, $db_oc, $oc_tableprefix){
	$DbgMsg = _('The SQL statement that failed was');
	$UpdateErrMsg = _('The SQL to Update OpenCart Order Status failed');
	$sqlUpdate = "UPDATE " . $oc_tableprefix . "order 
					SET	order_status_id = '" . $Value . "'
				WHERE order_id = '" . $OrderId . "'";
	$resultUpdate = DB_query($sqlUpdate,$db_oc,$UpdateErrMsg,$DbgMsg,true);
}

function RoundPriceFromCart($value, $currency){
	// copied and adapted from opencart/system/library/currency.php lines 74 to 106 approx.
	
	switch ($currency){
	case 'AUD':
		$round = 0.05;
		$step = 0;
		break;
	case 'IDR':
		$round = 5000;
		$step = 0;
		break;
	case 'USD':
		$round = 0.05;
		$step = 0;
		break;
	case 'EUR':
		$round = 0.05;
		$step = 0;
		break;
	default:
		 $round = 1;
		 $step = 0;
		 break;
	}
   
	if ($round) {
		$value = round($value / $round ) * $round;
	}

	if ($step) {
		$value -= $step;
	}
	
	return $value;
}

function GetWeberpShippingMethod($OpenCartShippingMethod){
	if (strpos($OpenCartShippingMethod, SHIPMENT01_OPENCART_TEXT) > 0){
		$WeberpShipping = SHIPMENT01_WEBERP_CODE;
	}elseif (strpos($OpenCartShippingMethod, SHIPMENT02_OPENCART_TEXT) > 0){
		$WeberpShipping = SHIPMENT02_WEBERP_CODE;
	}elseif (strpos($OpenCartShippingMethod, SHIPMENT03_OPENCART_TEXT) > 0){
		$WeberpShipping = SHIPMENT03_WEBERP_CODE;
	}else{
		$WeberpShipping = OPENCART_DEFAULT_SHIPVIA;
	}
	return $WeberpShipping;
} 

function GetGoogleProductFeedStatus($StockId, $SalesCategory, $Quantity){
	$Status = 0;
	if ((strpos(WEBERP_CATEGORIES_FOR_GOOGLE_PRODUCT_FEED, $SalesCategory) > 0) 
		AND ($Quantity > 0)){
		$Status = 1;
	}
	return $Status;
}

function GetGoogleProductFeedCategory($StockId, $SalesCategory){
	if (isRing($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Rings";
	}elseif (isToeRing($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Rings";
	}elseif (isEarring($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Earrings";
	}elseif (isBracelet($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Bracelets";
	}elseif (isAnklet($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Anklets";
	}elseif (isPendant($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Necklaces";
	}elseif (isNecklace($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Necklaces";
	}elseif (isPlasticBag($StockId)){
		$Category = "Clothing & Accessories > Handbags, Wallets & Cases > Handbags";
	}elseif (isTali($StockId)){
		$Category = "Clothing & Accessories > Jewellery & Watches > Necklaces";
	}else{
		$Category = "Clothing & Accessories > Jewellery & Watches";
	}
	return $Category;
}

?>
