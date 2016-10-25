<?php

if (!isset($GLOBALS['TCA']['be_users']['ctrl']['type'])) {
	if (file_exists($GLOBALS['TCA']['be_users']['ctrl']['dynamicConfigFile'])) {
		require_once($GLOBALS['TCA']['be_users']['ctrl']['dynamicConfigFile']);
	}
	// no type field defined, so we define it here. This will only happen the first time the extension is installed!!
	$GLOBALS['TCA']['be_users']['ctrl']['type'] = 'tx_extbase_type';
	$tempColumnstx_beuserbatch_be_users = array();
	$tempColumnstx_beuserbatch_be_users[$GLOBALS['TCA']['be_users']['ctrl']['type']] = array(
		'exclude' => 1,
		'label'   => 'LLL:EXT:beuserbatch/Resources/Private/Language/locallang_db.xlf:tx_beuserbatch.tx_extbase_type',
		'config' => array(
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => array(
				array('BackendUser','Tx_Beuserbatch_BackendUser')
			),
			'default' => 'Tx_Beuserbatch_BackendUser',
			'size' => 1,
			'maxitems' => 1,
		)
	);
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users', $tempColumnstx_beuserbatch_be_users, 1);
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
	'be_users',
	$GLOBALS['TCA']['be_users']['ctrl']['type'],
	'',
	'after:' . $GLOBALS['TCA']['be_users']['ctrl']['label']
);

/* inherit and extend the show items from the parent class */

if(isset($GLOBALS['TCA']['be_users']['types']['1']['showitem'])) {
	$GLOBALS['TCA']['be_users']['types']['Tx_Beuserbatch_BackendUser']['showitem'] = $GLOBALS['TCA']['be_users']['types']['1']['showitem'];
} elseif(is_array($GLOBALS['TCA']['be_users']['types'])) {
	// use first entry in types array
	$be_users_type_definition = reset($GLOBALS['TCA']['be_users']['types']);
	$GLOBALS['TCA']['be_users']['types']['Tx_Beuserbatch_BackendUser']['showitem'] = $be_users_type_definition['showitem'];
} else {
	$GLOBALS['TCA']['be_users']['types']['Tx_Beuserbatch_BackendUser']['showitem'] = '';
}
$GLOBALS['TCA']['be_users']['types']['Tx_Beuserbatch_BackendUser']['showitem'] .= ',--div--;LLL:EXT:beuserbatch/Resources/Private/Language/locallang_db.xlf:tx_beuserbatch_domain_model_backenduser,';
$GLOBALS['TCA']['be_users']['types']['Tx_Beuserbatch_BackendUser']['showitem'] .= '';

$GLOBALS['TCA']['be_users']['columns'][$GLOBALS['TCA']['be_users']['ctrl']['type']]['config']['items'][] = array('LLL:EXT:beuserbatch/Resources/Private/Language/locallang_db.xlf:be_users.tx_extbase_type.Tx_Beuserbatch_BackendUser','Tx_Beuserbatch_BackendUser');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
	'',
	'EXT:/Resources/Private/Language/locallang_csh_.xlf'
);