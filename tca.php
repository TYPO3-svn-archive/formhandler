<?php


require_once(t3lib_extMgm::extPath('formhandler') . 'Resources/PHP/class.tx_formhandler_tcafuncs.php');

$TCA['tx_formhandler_log'] = Array (
	'ctrl' => $TCA['tx_formhandler_log']['ctrl'],
	'interface' => Array (
		'showRecordFieldList' => 'crdate,ip,params,is_spam,key_hash'
	),
	'columns' => Array (
		'crdate' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.crdate',
			'config' => Array (
				'type' => 'input',
				'size' => '10',
				'max' => '20',
				'eval' => 'datetime',
				'checkbox' => '0',
				'default' => '0',
                'readOnly' => TRUE
			)
		),
 		'ip' => Array (
 			'label' => 'LLL:EXT:formhandler/Resources/Language/locallang_db.xml:tx_formhandler_log.ip',
 			'config' => Array (
 				'type' => 'input',
                'readOnly' => TRUE
 			)
 		),
		'params' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:formhandler/Resources/Language/locallang_db.xml:tx_formhandler_log.params',
			'config' => Array (
				'type' => 'user',
                'userFunc' => 'tx_formhandler_tcafuncs->user_getParams'
			)
		),
		'is_spam' => Array (
			'exclude' => 1,
			'label' => 'LLL:EXT:formhandler/Resources/Language/locallang_db.xml:tx_formhandler_log.is_spam',
			'config' => Array (
				'type' => 'check',
                'readOnly' => TRUE
			)
		),
		'uid' => Array (
			'label' => '',
			'config' => Array (
				'type' => 'none'
			)
		),
		'pid' => Array (
			'label' => '',
			'config' => Array (
				'type' => 'none'
			)
		),
		'tstamp' => Array (
			'label' => '',
			'config' => Array (
				'type' => 'none'
			)
		)
	),
	'types' => Array (
		'0' => Array(
            'showitem' => 'crdate,ip,params,is_spam'
        )
	)
);

?>