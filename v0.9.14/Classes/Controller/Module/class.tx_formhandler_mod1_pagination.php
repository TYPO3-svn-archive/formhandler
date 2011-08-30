<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Felix Nagel <f.nagel@paints.de>
 *
 *  Based upon bepagination extension, original written by
 *  (c) 2008 cherpit laurent <laurent@eosgarden.com>
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
/**
 *
 * @author  Felix Nagel <f.nagel@paints.de>
 * @package TYPO3
 * @subpackage  formhandler
 */


class tx_formhandler_mod1_pagination {

	// Script name String from GLOBALS ENV
	private $scriptName = '';
	 
	// LLang obj
	protected $lang = NULL;
	 
	// New line character
	protected $NL = '';

	// page TS config
	protected $tsConfig = '';
	 
	// parent Object
	protected $pObj = NULL;
	 
	// page id
	protected $id = 0;

	// interval pointer
	private $pointer = 0;

	// high pointer value
	protected $totalPages = 0;
	
	// current record
	protected $currentRec = 0;
	
	// Number total of records
	protected $nbItems = 0;
	
	// max results records to display per page
	protected $maxResPerPage = 10;
	
	// max results records to display per page
	protected $maxBrowsePages = 5;
	
	// browseBox html code
	protected $browseBox = '';

	/**
	 * @param integer total number of records to pagine
	 * @param Tx_Formhandler_Controller_Backend parent Object
	 */
	public function __construct($totalCount, $pObj) {
		
		// Sets script name url
		$this->scriptName = t3lib_div::getIndpEnv('SCRIPT_NAME');

		// Sets Lang
		$this->setLangLabels();

		// Sets the new line character
		$this->NL = chr(10);

		if (is_object($pObj)) {
			$this->pObj = $pObj;
		} else {
			print 'the constructor need the parent object. ex. $Browser = new ' . __CLASS__ . '($totalCount, $this)';
			exit;
		}

		// Sets the values and cast to integer
		$this->totalItemsCount = ($totalCount ? intval($totalCount) : 0);

		// cast to integer
		$this->id = intval($this->pObj->getId());

		// get GetPost array Vars
		$GPvars = t3lib_div::_GP('formhandler');

		// max results records to display per page
		if ($GPvars['howmuch']) {
			$this->maxResPerPage = intval($GPvars['howmuch']);
		} elseif ($totalCount > 10000) {
			$this->maxResPerPage = 1000;
		} elseif ($totalCount > 1000) {
			$this->maxResPerPage = 100;
		} elseif ($totalCount > 100) {
			$this->maxResPerPage  = 10;
		}

		// number of pages
		$this->totalPages = ceil($this->totalItemsCount / $this->maxResPerPage);
		 
		// interval pointer
		$this->pointer = ($GPvars['pointer'] ? intval($GPvars['pointer']) : 0);

		if ($this->pointer > ($this->totalPages - 1) && $this->pointer > 0) {
			$this->pointer = ($this->totalPages - 1);
		} else if ($this->pointer < 0) {
			$this->pointer = 0;
		}

		// current record
		$this->currentRec = ($this->pointer * $this->maxResPerPage);

		// tell the browserBox
		$this->buildPager();
	}

	/**
	 * Display the page browser
	 *
	 * @return string HTML
	 */
	public function displayBrowseBox() {
		return $this->browseBox;
	}

	/**
	 * Get current max entries per page
	 *
	 * @return int
	 */
	public function getMaxResPerPage() {
		return $this->maxResPerPage;
	}

	/**
	 * add this to the sql query to return the set interval offset of elements
	 *
	 * @return string Limit sql clause
	 */
	public function getSqlLimitClause() {
		return ($this->totalPages > 1 ? $this->currentRec . ',' . $this->maxResPerPage : '');
	}

	/**
	 * Results stats of browseBox
	 * return obj with properties
	 * ->fromRec
	 * ->toRec
	 * ->totalRec
	 * ->curPage
	 * ->totalPage
	 *
	 * @return Obj
	 */
	public function getResultBox() {
		
		// new obj.
		$resBox = new stdClass();

		// and Pages
		if ($this->totalPages < 2) {
			$resBox->totalPage = sprintf($this->lang->totalPage, $this->totalPages);
		} else {
			$resBox->curPage = sprintf($this->lang->curPage, $this->pointer + 1);
			$resBox->totalPage = sprintf($this->lang->totalPages, $this->totalPages);
			
			// Results number from item
			$resBox->fromRec = sprintf($this->lang->fromRec, ($this->currentRec + 1));
		}

		// Results number to item
		$toRec = ((($this->pointer * $this->maxResPerPage) + $this->maxResPerPage) > $this->totalItemsCount ? $this->totalItemsCount : ($this->pointer * $this->maxResPerPage) + $this->maxResPerPage);

		// to records
		$resBox->toRec = sprintf($this->lang->toRec, $toRec);

		// Results total number of items
		$resBox->totalRec = sprintf($this->lang->totalRec, $this->totalItemsCount);

		// return obj
		return $resBox;
	}

	/**
	 * Pager browse box: inspired from browseBox method in tx_apimacmade
	 *
	 * This function constructs a browse box for use in backend modules as pagination records list browser.
	 * Output is valid XHTML.
	 *
	 *
	 * @return void
	 */
	protected function buildPager() {
		
		// pointers list for MOD_MENU
		$pointers = array();

		$GPvars = t3lib_div::_GP('formhandler');
		if (!$GPvars) $GPvars = array();
		if ($GPvars['pointer']) unset($GPvars['pointer']);
		if (!$GPvars['pidFilter'] && $this->id) $GPvars['pidFilter'] = intval($this->id);

		$GP_url = t3lib_div::implodeArrayForUrl('formhandler', $GPvars);

		// Storage
		$htmlCode = array();

		// Check for multiple pages
		if ($this->totalPages > 1) {
			
			// Number of pages to show
			$showPages = ($this->totalPages < $this->maxBrowsePages) ? $this->totalPages : $this->maxBrowsePages;

			// Start list
			$htmlCode[] = '<ul class="pages">';

			// Check pointer
			if ($this->pointer > 0) {
				
				// go to first link
				$firstLink = '<a href="' . $this->scriptName . '?formhandler[pointer]=0' . $GP_url . '"' .
                              ' title="' . $this->lang->first_tt . '">' . $this->lang->first_lbl . '</a>';

				// Add go to first link
				$htmlCode[] = '<li class="first">' . $firstLink . '</li>';

				// Previous link
				$prevLink = '<a href="' . $this->scriptName . '?formhandler[pointer]=' . ($this->pointer - 1) . $GP_url .
                              '" title="' . $this->lang->previous_tt . '">' . $this->lang->previous_lbl . '</a>';
					
				// Add next link
				$htmlCode[] = '<li class="previous">' . $prevLink . '</li>';
			}

			// calc. the current page to set the right style after
			if ($this->pointer <= ($this->totalPages - $showPages) + 1) {
				$currentPage = ($this->pointer > floor($showPages / 2)) ? $this->pointer - floor($showPages / 2) : 0 ;
			} else {
				$currentPage = ($this->totalPages - $showPages);
			}

			// Build pages links and  add the current page pointer to $i
			for($i = $currentPage; $i < ($showPages + $currentPage); $i++) {

				/* calc the from rec. - to rec.per page offset */
				$from = (($i * $this->maxResPerPage) + 1);
				$to = ((($i * $this->maxResPerPage) + $this->maxResPerPage) > $this->totalItemsCount ? $this->totalItemsCount : ($i * $this->maxResPerPage) + $this->maxResPerPage);
				$class = 'page';
				$linkTitle = sprintf($this->lang->page_tt, ($i + 1));
				$linkLabel = sprintf($this->lang->page_lbl, $from, $to);
				$href = $this->scriptName . '?formhandler[pointer]=' . $i . $GP_url;

				// apply tilte and css Class to current page
				if ($i == $this->pointer) {
					$class = 'cur';
					$linkTitle = sprintf($this->lang->current_tt, ($i + 1));
					$linkLabel = sprintf($this->lang->current_lbl, $from, $to);
					$href = '#';
				}

				// Page link
				$pageLink = '<a href="' . $href . '" title="' . $linkTitle . '">' .$linkLabel . '</a>';

				// add current pointer to pointers list
				$pointers[] = $i;

				// Add list item
				$htmlCode[] = '<li class="' . $class . '">' . $pageLink .  '</li>';
			}

			// Check pointer
			if ($this->pointer < $this->totalPages - 1) {
				// Next link
				$nextLink = '<a href="' . $this->scriptName . '?formhandler[pointer]=' . ($this->pointer + 1) . $GP_url .
                              '" title="' . $this->lang->next_tt . '">' . $this->lang->next_lbl . '</a>';

				// Add next link
				$htmlCode[] = '<li class="next">' . $nextLink . '</li>';

				// go to last link
				$lastLink = '<a href="' . $this->scriptName . '?formhandler[pointer]=' . ($this->totalPages - 1) . $GP_url .
                              '" title="' . $this->lang->last_tt . '">' . $this->lang->last_lbl . '</a>';

				// Add go to last link
				$htmlCode[] = '<li class="last">' . $lastLink . '</li>';
			}

			// End list
			$htmlCode[] = '</ul>';

		}
		// get the processed browseBox
		$this->browseBox = implode($this->NL, $htmlCode);
	}

	protected function setLangLabels()  {
		$this->lang = new stdClass();

		$this->lang->fromRec = $GLOBALS['LANG']->getLL('pagination_fromRec');
		$this->lang->toRec = $GLOBALS['LANG']->getLL('pagination_toRec');
		$this->lang->totalRec = $GLOBALS['LANG']->getLL('pagination_totalRec');
		$this->lang->curPage = $GLOBALS['LANG']->getLL('pagination_curPage');
		$this->lang->totalPage = $GLOBALS['LANG']->getLL('pagination_totalPage');
		$this->lang->totalPages = $GLOBALS['LANG']->getLL('pagination_totalPages');
		$this->lang->page = $GLOBALS['LANG']->getLL('pagination_page');
		$this->lang->pages = $GLOBALS['LANG']->getLL('pagination_pages');
		$this->lang->first_tt = $GLOBALS['LANG']->getLL('pagination_first_tt');
		$this->lang->previous_tt = $GLOBALS['LANG']->getLL('pagination_previous_tt');
		$this->lang->current_tt = $GLOBALS['LANG']->getLL('pagination_current_tt');
		$this->lang->page_tt = $GLOBALS['LANG']->getLL('pagination_page_tt');
		$this->lang->next_tt = $GLOBALS['LANG']->getLL('pagination_next_tt');
		$this->lang->last_tt = $GLOBALS['LANG']->getLL('pagination_last_tt');
		$this->lang->first_lbl = $GLOBALS['LANG']->getLL('pagination_first_lbl');
		$this->lang->previous_lbl = $GLOBALS['LANG']->getLL('pagination_previous_lbl');
		$this->lang->current_lbl = $GLOBALS['LANG']->getLL('pagination_current_lbl');
		$this->lang->page_lbl = $GLOBALS['LANG']->getLL('pagination_page_lbl');
		$this->lang->next_lbl = $GLOBALS['LANG']->getLL('pagination_next_lbl');
		$this->lang->last_lbl = $GLOBALS['LANG']->getLL('pagination_last_lbl');
	}
}

/**
 * XCLASS inclusion
 */
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/formhandler/Classes/Controller/Module/class.tx_formhandler_mod1_pagination.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/formhandler/Classes/Controller/Module/class.tx_formhandler_mod1_pagination.php']);
}

?>