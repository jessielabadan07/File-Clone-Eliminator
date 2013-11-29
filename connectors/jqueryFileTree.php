<?php
$path  = urldecode($_POST['dir']);							# get directory
$protectedFolders = array('System Volume Information','$Recycle.Bin','$RECYCLE.BIN','desktop.ini','DESKTOP.INI');
$folderList = array();										# initialize folder list array
try {														# put try
	foreach (new DirectoryIterator($path) as $fileInfo) {	# scan folder
		if($fileInfo->isDot()) continue;					# if it is a .. file directory	then continue
		if($fileInfo->isDir() && !in_array($fileInfo->getFilename(), $protectedFolders)) {							# If it is a directory, then show only dirs...
			$folderList[] = $fileInfo->getFilename();		# get directory filename
		}	
	}
	echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";	
	foreach($folderList as $folder)	 {
		echo "<li class=\"directory collapsed\"><a class=\"each_folder\" href=\"#\" rel=\"" . htmlentities($path. $folder) . "/\">" . htmlentities($folder) . "</a></li>";
	}
	echo "</ul>";
} catch(Exception $e) {										# if error directory, protected directory, etc..
	die;													# don't display warnings and errors
}
?>