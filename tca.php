<?php


require_once(t3lib_extMgm::extPath('formhandler') . 'Resources/PHP/class.tx_formhandler_tcafuncs.php');

$TCA['tx_formhandler_log'] = array (
	'ctrl' => $TCA['tx_formhandler_log']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'crdate,ip,params,is_spam,key_hash'
	),
	'columns' => array (
		'crdate' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:formhandler/Resources/Language/locallang_db.xml:tx_formhandler_log.submission_date',
			'config' => array (
				'type' => 'input',
				'size' => '10',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'ip' => array (
			'label' => 'LLL:EXT:formhandler/Resources/Language/locallang_db.xml:tx_formhandler_log.ip',
			'config' => array (
				'type' => 'input'
			)
		),
		'params' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:formhandler/Resources/Language/locallang_db.xml:tx_formhandler_log.params',
			'config' => array (
				'type' => 'user',
				'userFunc' => 'tx_formhandler_tcafuncs->user_getParams'
			)
		),
		'is_spam' => array (
			'exclude' => 1,
			'label' => 'LLL:EXT:formhandler/Resources/Language/locallang_db.xml:tx_formhandler_log.is_spam',
			'config' => array (
				'type' => 'check'
			)
		),
		'uid' => array (
			'label' => '',
			'config' => array (
				'type' => 'none'
			)
		),
		'pid' => array (
			'label' => '',
			'config' => array (
				'type' => 'none'
			)
		),
		'tstamp' => array (
			'label' => '',
			'config' => array (
				'type' => 'none'
			)
		),
		'key_hash' => array (
			'label' => '',
			'config' => array (
				'type' => 'none'
			)
		),
		'unique_hash' => array (
			'label' => '',
			'config' => array (
				'type' => 'none'
			)
		)
	),
	'types' => array (
		'0' => array(
			'showitem' => 'crdate,ip,params,is_spam'
		)
	)
);

?>