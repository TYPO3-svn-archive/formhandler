<?php

class tx_formhandler_clearCache {

	public function clearCache($params) {
		unlink(PATH_site . 'typo3temp/' . 'formhandlerClassesCache.txt');
	}

}

?>