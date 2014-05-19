<?php

CreateTable('config', "CREATE TABLE `config` (
  `confname` varchar(35) NOT NULL DEFAULT '',
  `confvalue` text NOT NULL,
  PRIMARY KEY (`confname`)
)");

NewConfigValue('AllowOrderLineItemNarrative', 1);
NewConfigValue('AllowSalesOfZeroCostItems', 0);
NewConfigValue('AutoAuthorisePO', 1);
NewConfigValue('AutoCreateWOs', 1);
NewConfigValue('AutoDebtorNo', 0);
NewConfigValue('AutoIssue', 1);
NewConfigValue('CheckCreditLimits', 1);
NewConfigValue('Check_Price_Charged_vs_Order_Price', 1);
NewConfigValue('Check_Qty_Charged_vs_Del_Qty', 1);
NewConfigValue('CountryOfOperation', 'KE');
NewConfigValue('CreditingControlledItems_MustExist', 0);

NewConfigValue('DB_Maintenance', 0);
NewConfigValue('DB_Maintenance_LastRun', date('Y-m-d'));
NewConfigValue('DefaultBlindPackNote', 1);
NewConfigValue('DefaultCreditLimit', 1000);
NewConfigValue('DefaultCustomerType', 1);
NewConfigValue('DefaultDateFormat', 'd/m/Y');
NewConfigValue('DefaultDisplayRecordsMax', 50);
NewConfigValue('DefaultFactoryLocation', '');
NewConfigValue('DefaultPriceList', '');
NewConfigValue('DefaultSupplierType', 1);
NewConfigValue('DefaultTaxCategory', 1);
NewConfigValue('Default_Shipper', 1);
NewConfigValue('DefineControlledOnWOEntry', 1);
NewConfigValue('DispatchCutOffTime', 14);
NewConfigValue('DoFreightCalc', 0);
NewConfigValue('EDIHeaderMsgId', 'D:01B:UN:EAN010');
NewConfigValue('EDIReference', $ProjectName);
NewConfigValue('EDI_Incoming_Orders', 'EDI_Incoming_Orders');
NewConfigValue('EDI_MsgPending', 'EDI_MsgPending');
NewConfigValue('EDI_MsgSent', 'EDI_Sent');
NewConfigValue('ExchangeRateFeed', 'ECB');
NewConfigValue('Extended_CustomerInfo', 0);
NewConfigValue('Extended_SupplierInfo', 0);
NewConfigValue('FactoryManagerEmail', 'manager@company.com');
NewConfigValue('FreightChargeAppliesIfLessThan', 1000);
NewConfigValue('FreightTaxCategory', 1);
NewConfigValue('FrequentlyOrderedItems', 0);
NewConfigValue('geocode_integration', 0);
NewConfigValue('HTTPS_Only', 0);
NewConfigValue('InventoryManagerEmail', 'test@company.com');
NewConfigValue('InvoicePortraitFormat', 1);
NewConfigValue('ItemDescriptionLanguages', '');
NewConfigValue('LogPath', '');
NewConfigValue('LogSeverity', 0);
NewConfigValue('MaxImageSize', 300);
NewConfigValue('MonthsAuditTrail', 1);
NewConfigValue('NumberOfMonthMustBeShown', 6);
NewConfigValue('NumberOfPeriodsOfStockUsage', 12);
NewConfigValue('OverChargeProportion', 30);
NewConfigValue('OverReceiveProportion', 20);
NewConfigValue('PackNoteFormat', 1);
NewConfigValue('PageLength', 48);
NewConfigValue('part_pics_dir', 'part_pics');
NewConfigValue('PastDueDays1', 30);
NewConfigValue('PastDueDays2', 60);
NewConfigValue('PO_AllowSameItemMultipleTimes', 1);
NewConfigValue('ProhibitJournalsToControlAccounts', 1);
NewConfigValue('ProhibitNegativeStock', 1);
NewConfigValue('ProhibitPostingsBefore', '1900-01-01');
NewConfigValue('PurchasingManagerEmail', 'test@company.com');
NewConfigValue('QuickEntries', 10);
NewConfigValue('RadioBeaconFileCounter', '/home/RadioBeacon/FileCounter');
NewConfigValue('RadioBeaconFTP_user_name', 'RadioBeacon ftp server user name');
NewConfigValue('RadioBeaconHomeDir', '/home/RadioBeacon');
NewConfigValue('RadioBeaconStockLocation', 'BL');
NewConfigValue('RadioBraconFTP_server', '192.168.2.2');
NewConfigValue('RadioBreaconFilePrefix', 'ORDXX');
NewConfigValue('RadionBeaconFTP_user_pass', 'Radio Beacon remote ftp server password');
NewConfigValue('reports_dir', 'EDI_Sent');
NewConfigValue('RequirePickingNote', 0);
NewConfigValue('RomalpaClause', 'Ownership will not pass to the buyer until the goods have been paid for in full.');
NewConfigValue('ShopAboutUs', '');
NewConfigValue('ShopAdditionalStockLocations', '');
NewConfigValue('ShopAllowBankTransfer', 1);
NewConfigValue('ShopAllowCreditCards', 1);
NewConfigValue('ShopAllowPayPal', 1);
NewConfigValue('ShopAllowSurcharges', 1);
NewConfigValue('ShopBankTransferSurcharge', 0);
NewConfigValue('ShopBranchCode', '');
NewConfigValue('ShopContactUs', '');
NewConfigValue('ShopCreditCardBankAccount', 1030);
NewConfigValue('ShopCreditCardGateway', 'PayFlowPro');
NewConfigValue('ShopCreditCardSurcharge', 0.029);
NewConfigValue('ShopDebtorNo', '');
NewConfigValue('ShopFreightModule', 'ShopFreightMethod');
NewConfigValue('ShopFreightPolicy', '');
NewConfigValue('ShopManagerEmail', '');
NewConfigValue('ShopMode', '');
NewConfigValue('ShopName', '');
NewConfigValue('ShopPayFlowMerchant', '');
NewConfigValue('ShopPayFlowPassword', '');
NewConfigValue('ShopPayFlowUser', '');
NewConfigValue('ShopPayFlowVendor', '');
NewConfigValue('ShopPayPalBankAccount', 1030);
NewConfigValue('ShopPaypalCommissionAccount', 7220);
NewConfigValue('ShopPayPalPassword', '');
NewConfigValue('ShopPayPalProPassword', '');
NewConfigValue('ShopPayPalProSignature', '');
NewConfigValue('ShopPayPalProUser', '');
NewConfigValue('ShopPayPalSignature', '');
NewConfigValue('ShopPayPalSurcharge', 0.034);
NewConfigValue('ShopPayPalUser', '');
NewConfigValue('ShopPrivacyStatement', '');
NewConfigValue('ShopShowInfoLinks', 1);
NewConfigValue('ShopShowLeftCategoryMenu', 1);
NewConfigValue('ShopShowLogoAndShopName', 1);
NewConfigValue('ShopShowOnlyAvailableItems', 0);
NewConfigValue('ShopShowQOHColumn', 1);
NewConfigValue('ShopShowTopCategoryMenu', 1);
NewConfigValue('ShopStockLocations', 1);
NewConfigValue('ShopSurchargeStockID', '');
NewConfigValue('ShopSwipeHQAPIKey', '');
NewConfigValue('ShopSwipeHQMerchantID', '');
NewConfigValue('ShopTermsConditions', '');
NewConfigValue('ShopTitle', 'Shop Home');
NewConfigValue('ShowStockidOnImages', 0);
NewConfigValue('ShowValueOnGRN', 1);
NewConfigValue('Show_Settled_LastMonth', 1);
NewConfigValue('SmtpSetting', 0);
NewConfigValue('SO_AllowSameItemMultipleTimes', 1);
NewConfigValue('StandardCostDecimalPlaces', 2);
NewConfigValue('TaxAuthorityReferenceName', '');
NewConfigValue('UpdateCurrencyRatesDaily', 0);
NewConfigValue('VersionNumber', '13.10.0');
NewConfigValue('vtiger_integration', 0);
NewConfigValue('WeightedAverageCosting', 1);
NewConfigValue('WikiApp', 'Disabled');
NewConfigValue('WikiPath', 'wiki');
NewConfigValue('WorkingDaysWeek', 5);
NewConfigValue('YearEnd', 3);

NewConfigValue('DBUpdateNumber', HighestFileName($PathPrefix));

?>