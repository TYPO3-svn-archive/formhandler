<?php
namespace Typoheads\Formhandler\Controller;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ModuleController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * The Formhandler component manager
	 *
	 * @access protected
	 * @var \Typoheads\Formhandler\Component\Manager
	 */
	protected $componentManager;

	/**
	 * The Formhandler utility funcs
	 *
	 * @access protected
	 * @var \\Typoheads\Formhandler\Utils\UtilityFuncs
	 */
	protected $utilityFuncs;

	/**
	 * @var \Typoheads\Formhandler\Domain\Repository\LogDataRepository
	 * @inject
	 */
	protected $logDataRepository;

	/**
	 * @var \TYPO3\CMS\Core\Page\PageRenderer
	 */
	protected $pageRenderer;

	/**
	 * init all actions
	 * @return void
	 */
	public function initializeAction() {
		$this->id = intval($_GET['id']);
		$tsconfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getModTSconfig($this->id, 'module.tx_formhandler');
		if(!empty($tsconfig['properties']['settings.'])) {
			$this->settings = $tsconfig['properties']['settings.'];
		} else {
			$tsconfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getModTSconfig($this->id, 'tx_formhandler_mod1');
			$this->settings = $tsconfig['properties']['config.'];
		}

		$this->componentManager = \Typoheads\Formhandler\Component\Manager::getInstance();
		$this->utilityFuncs = \Typoheads\Formhandler\Utils\UtilityFuncs::getInstance();
		$this->pageRenderer = $this->objectManager->get(\TYPO3\CMS\Core\Page\PageRenderer::class);
		$this->pageRenderer->loadRequireJsModule('TYPO3/CMS/Backend/DateTimePicker');

		if (!isset($this->settings['dateFormat'])) {
			$this->settings['dateFormat'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] ? 'm-d-Y' : 'd-m-Y';
		}
		if (!isset($this->settings['timeFormat'])) {
			$this->settings['timeFormat'] = $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'];
		}
	}

	/**
	 * Displays log data

	 * @return void
	 */
	public function indexAction(\Typoheads\Formhandler\Domain\Model\Demand $demand = NULL) {
		if($demand === NULL) {
			$demand = $this->objectManager->get(\Typoheads\Formhandler\Domain\Model\Demand::class);
			$demand->setPid(intval($_GET['id']));
		}
		$logDataRows = $this->logDataRepository->findDemanded($demand);
		$this->view->assign('demand', $demand);
		$this->view->assign('dateFormat', $this->settings['dateFormat']);
		$this->view->assign('timeFormat', $this->settings['timeFormat']);
		$this->view->assign('logDataRows', $logDataRows);
	}

	public function viewAction(\Typoheads\Formhandler\Domain\Model\LogData $logDataRow = NULL) {

		if($logDataRow !== NULL) {
			$logDataRow->setParams(unserialize($logDataRow->getParams()));
			$this->view->assign('dateFormat', $this->settings['dateFormat']);
			$this->view->assign('timeFormat', $this->settings['timeFormat']);
			$this->view->assign('data', $logDataRow);
		}
	}

	/**
	 * Displays fields selector
	 * @param array rows to export
	 * @param string export file type (PDF || CSV)
	 * @return void
	 */
	public function selectFieldsAction(array $logDataUids = NULL, $filetype = '') {
		if($logDataUids !== NULL) {
			$uids = implode(',', $logDataUids);
			$logDataRows = $this->logDataRepository->findByUids($uids);
			$fields = array(
				'global' => array(
					'pid',
					'ip',
					'submission_date'
				),
				'system' => array(
					'randomID',
					'removeFile',
					'removeFileField',
					'submitField',
					'submitted'
				),
				'custom' => array()
			);
			foreach($logDataRows as $logDataRow) {
				$rowFields = array_keys(unserialize($logDataRow->getParams()));
				foreach($rowFields as $idx =>$rowField) {
					if(in_array($rowField, $fields['system'])) {
						unset($rowFields[$idx]);
					} elseif(substr($rowField, 0, 5) === 'step-') {
						unset($rowFields[$idx]);
						if(!in_array($rowField, $fields['system'])) {
							$fields['system'][] = $rowField;
						}
					}
				}
				$fields['custom'] = array_merge($fields['custom'], $rowFields);
			}
			$this->view->assign('fields', $fields);
			$this->view->assign('logDataUids', $uids);
			$this->view->assign('filetype', $filetype);
		}
	}

	/**
	 * Exports given rows as file
	 * @param string uids to export
	 * @param array fields to export
	 * @param string export file type (PDF || CSV)
	 * @return void
	 */
	public function exportAction($logDataUids = NULL, array $fields, $filetype = '') {
		if($logDataUids !== NULL && !empty($fields)) {
			$logDataRows = $this->logDataRepository->findByUids($logDataUids);
			$convertedLogDataRows = array();
			foreach($logDataRows as $idx => $logDataRow) {
				$convertedLogDataRows[] = array(
					'pid' => $logDataRow->getPid(),
					'ip' => $logDataRow->getIp(),
					'crdate' => $logDataRow->getCrdate(),
					'params' => unserialize($logDataRow->getParams())
				);
			}
			if($filetype === 'pdf') {
				$className = '\Typoheads\Formhandler\Generator\TCPDF';
				if($this->settings['generators.']['pdf']) {
					$className = $this->utilityFuncs->prepareClassName($this->settings['generators.']['pdf']);
				}
				$generator = $this->componentManager->getComponent($className);
				if(!$this->settings['pdf.']['outputFileName']) {
					$this->settings['pdf.']['outputFileName'] = 'formhandler.pdf';
				}
				$generator->generateModulePDF(
					$convertedLogDataRows,
					$fields,
					$this->settings['pdf.']['outputFileName']
				);
			} elseif($filetype === 'csv') {
				$className = '\Typoheads\Formhandler\Generator\CSV';
				if($this->settings['generators.']['csv']) {
					$className = $this->utilityFuncs->prepareClassName($this->settings['generators.']['csv']);
				}
				$generator = $this->componentManager->getComponent($className);

				if(!$this->settings['csv.']['delimiter']) {
					$this->settings['csv.']['delimiter'] = ',';
				}
				if(!$this->settings['csv.']['enclosure']) {
					$this->settings['csv.']['enclosure'] = '"';
				}
				if(!$this->settings['csv.']['encoding']) {
					$this->settings['csv.']['encoding'] = 'utf-8';
				}
				if(!$this->settings['csv.']['outputFileName']) {
					$this->settings['csv.']['outputFileName'] = 'formhandler.csv';
				}
				$generator->generateModuleCSV(
					$convertedLogDataRows,
					$fields,
					$this->settings['csv.']['delimiter'],
					$this->settings['csv.']['enclosure'],
					$this->settings['csv.']['encoding'],
					$this->settings['csv.']['outputFileName']
				);
			}
		}
		return '';
	}

	/**
	 * Displays log data

	 * @return void
	 */
	public function clearLogsAction() {
		if($GLOBALS['BE_USER']->user['admin'] || intval($this->settings['enableClearLogs']) === 1) {
			$this->view->assign('test', 'test');
		} else {
			return '';
		}
	}

}
