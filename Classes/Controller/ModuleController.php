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
		$this->view->assign('dateFormat', $GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] ? 'm-d-Y' : 'd-m-Y');
		$this->view->assign('timeFormat', $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']);
		$this->view->assign('logDataRows', $logDataRows);
	}

	public function viewAction(\Typoheads\Formhandler\Domain\Model\LogData $logDataRow = NULL) {

		if($logDataRow !== NULL) {
			$logDataRow->setParams(unserialize($logDataRow->getParams()));
			$this->view->assign('dateFormat', $GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] ? 'm-d-Y' : 'd-m-Y');
			$this->view->assign('timeFormat', $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']);
			$this->view->assign('data', $logDataRow);
		}
	}

	public function pdfAction(\Typoheads\Formhandler\Domain\Model\LogData $logDataRow = NULL) {

		if($logDataRow !== NULL) {
			$logDataRow->setParams(unserialize($logDataRow->getParams()));
			$this->view->assign('dateFormat', $GLOBALS['TYPO3_CONF_VARS']['SYS']['USdateFormat'] ? 'm-d-Y' : 'd-m-Y');
			$this->view->assign('timeFormat', $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm']);
			$this->view->assign('data', $logDataRow);
		}
	}

	/**
	 * Displays log data

	 * @return void
	 */
	public function clearLogsAction() {

		$this->view->assign('test', 'test');
	}

}
