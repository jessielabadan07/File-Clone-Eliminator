<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

//error_reporting(0);

class Main extends CI_Controller {
	
	# define private files
	private $system_path; 	# our system path
	
	private $file_results;	# file results array(..,..,)
	
	private $is_multi;		# is multi directory???
	
	private $data;			# holds the data object
	
	private $error; 		# holds the errors
	
	private $page_title;
	
	# array fields;
	private $results = array();		# array to hold the all final values
	private $keys = array();		# to hold keys
	
	# exclude other protected windows system folders
	var $protectedFolders = array('System Volume Information','$Recycle.Bin','$RECYCLE.BIN','desktop.ini','DESKTOP.INI');		# add more dirs here...
	
	# constructor
	function __construct() {
		parent::__construct();														# call parent constructor of default CI Controller
		$this->load->model('file_model');											# load model so we can use it everywhere inside the main controller
	}	
	
	# load index
	public function index()
	{						
		if ( ! isset($_POST['location'])) {											# If location is not set or not posted
			$data['drives'] = $this->getDrives();									# get all available pc drives
			$data['extensions'] = $this->file_model->getExtensionList();			# get extension list
			$data['page_title'] = "Home - File Clone Eliminator";					# set main page title
			$this->load->view('template/header',$data);								# load header view
			$this->load->view('main_view');											# then display the main page
			$this->load->view('template/footer');									# load footer view
		}
		else {																		# If form was submit, then...
			$location = $this->input->post('location');								# get the location
			$file_option = "";//$this->input->post('file_options');					# type of the exteion like doc,music,video,etc...
			$file_ext = $this->input->post('file_options');							# get file extension options
			if($this->trim_all(preg_match("/^[a-zA-Z]$/", $location)) == 1) {		# If it is a single drive like C, D, etc...
				$this->is_multi = false;
				$this->singleDrive($file_option, $location[0], $file_ext);			# then call singleDrive function
			} else {																# Call multi level directory.sub-directories like C:/hello,etc..
				if(strlen($location) <= 3) {										# If a location is less than= 3 chars like c:/, d:/, etc..
					if (preg_match("/^[a-zA-Z]$/", $location[0])) {					# get first character of location if it is a valid char like c,d,etc..
						if($location[1] == ":" && @$location[2] == "/") {			# if index[1] is : and index[2] location is / then
							$this->is_multi = false;
							$this->singleDrive($file_option, $location[0], $file_ext);			# call single drive for c:/, d:/, etc...
						} else {
							redirect("?error=true", 'location');					# not a valid drive
						}
					} 
				} else {															# If it is a multi-level dir like, c:/jess,d:/hew/werw,etc..
					$this->is_multi = true;
					$this->multiDirectory($file_option, $location, $file_ext);					# then invoke multiDir function
				}
			}			
			if(strlen($this->system_path) > 0) {									# if the return system path is greater than 0, then
				$parent_id = $this->file_model->saveParentDir($this->system_path);	# save parent path to database
				$this->extractFiles($parent_id,$this->file_results);				# save files to database
				redirect("/main/view/$parent_id/", 'location');						# redirect to view page with id
			} else {
				redirect("?error=true&message=".urlencode($this->error)."", 'location');	# there is an error of dir or files.
			} 
		}		
	} # end index function
	
	# function to get all available drives
	private function getDrives() {	
		$drives = array('A','B','C','D','E','F','G','H','I','J','K','L',				# array of drives
						'M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		$list_drives = array();				
		foreach($drives as $drive) {			
			if(is_dir($drive.'://'))													# if drive exist, then..
				$list_drives[] = $drive;												# store it in the list drive array
		}
		return $list_drives;															# then return the list of drives and display it in  view index
	} # end function getDrives
	
	# view function to display the result with id
	public function view($parent_id) {		
		if(!empty($parent_id)) {														# if parent id is not empty
			$this->session->set_userdata( array('p_id' => $parent_id) );				# set p_id session
			$data['id'] = $parent_id;													# get parent id							
			$datas = $this->file_model->getParentDir($parent_id);						# get all datas from parent
			$isMultiOrSingle = strlen($datas[0]->root_directory) > 3 ? 1 : 0;			# if single or multi level directory
			$this->session->set_userdata(array('is_multi' => $isMultiOrSingle));		# set session 
			$this->data = $this->file_model->getFiles($parent_id);						# get all files by parent_id	
			$this->initializeResults($this->data,$datas[0]->root_directory);			# initialize the result data
			$data['results']= $this->displayResults();									# assign results to data associative array results			
			$data['history_results'] = $this->file_model->getAllHistory();				# get all history results 
			$data['is_multi'] = $this->session->userdata('is_multi');					# set if it is a multi dir			
			$data['keys'] = $this->keys;												# get keys
			$data['page_title'] = "Scanned Results";									# set page title
			$this->load->view('template/header',$data);									# load header view
			$this->load->view('result_view');											# load the result view
			$this->load->view('template/footer',$data);
		}
	} # end function view
		
	# function initialize results
	private function initializeResults($data,$dir) {
		$fullDir = "";
		foreach($data as $result) {		
			$fullDir = $dir.$this->getParentDir( $result->file_path);
			if(is_dir($fullDir)) {
				$this->results[] = array('fileName' => $this->getFile( $result->file_path ), 			# assign filename and parentDir to results array
					 'parentDir' => $this->getParentDir( $result->file_path ));							# get complete path
			} else {
				$this->results[] = array('fileName' => $this->getFile( $result->file_path ), 			# assign filename and parentDir to results array
					 'parentDir' => $dir);																# get single drive path
			}
		}		
	} # end function 

	# function to display the results
	private function displayResults() {
		$result_to_array = array();									# initialize result to array		
		if($this->session->userdata('is_multi') == 1) {				# if it is a multi directory then...
			if( sizeof($this->data) == 1 && $this->data[0]->is_deleted == 0) {		# if there is one data and that data is not deleted
				return $this->data;									# then assign that data to result to array				
			} else {												# if data is more than one
				foreach($this->data as $key => $result) {			# loop data inside
					if(!isset($result_to_array)) {					# if empty result_to_array
						$result_to_array[$key] = array();			# set it to empty array
					} 
					$result_to_array[$result->hash_key][] = $result; # assign results to their keys
				}
				return $result_to_array;							# get the results
			}
		} else {													# if drive is C,D,E:/,etc..
			$dir = $this->session->userdata('dir');					# get drive
			$temp_array = array();									# initialize empty temp array
			foreach($this->results as $key => $rst) {				# initialize results				
				if($rst['parentDir'] == $dir) {
					$temp_array[] = @hash_file('md5',$dir.$rst['fileName']);	# store hash key in temp array
				} else {
					$temp_array[] = @hash_file('md5',$dir.$rst['parentDir'].'/'.$rst['fileName']);	# store hash key in temp array				
				} 			
			} 			
			$key_array = $this->get_duplicates($temp_array);		# find hash key that is not unique
			foreach($this->data as $result) {						# get the data results from db
				if($result->is_deleted == 0) {			
					if(in_array(hash_file('md5',$result->file_path), $key_array)) {	# compare file in db and in the hash key
						$result_to_array[] = $result;				# then assign result to array							
					}
				}
			}				
			return $this->getKeysResult($result_to_array);					# get hash keys
		}
	} # end display results
	
	# function to return the array_unique hash key
	private function getKeysResult($input) {
		$output = array();											# initialize output array
		$counter = 0;
		foreach ( $input as $data ) {								# go inside the data
		  foreach ( $data as $key => $value ) {						# loop inside inner data
			if ( !isset($output[$key]) ) {							# if not key found
			  $output[$key] = array();								# set key to empty array
			}			
			$output[$data->hash_key][$counter] = $data;				# get the hash key value with counter
		  }
		  $counter++;												# increment counter for temp key
		}
		return $output;												# return output
	}
	
	# function array not unique
	function array_not_unique( $a = array() ) {
	  return array_diff_key( $a , array_unique( $a ) ); // save reference to array unique
	}
	
	# get top level duplicates
	function get_duplicates( $array ) {
		return array_unique( array_diff_assoc( $array, array_unique( $array ) ) );
	}
	
	## other functions ##
	# function to get the file and extension file
	private function getFile($fileStr) {
		$pos = strripos($fileStr, "/")+1;
		return substr($fileStr, $pos, strlen($fileStr));
	}
	
	# function to get the parent directory
	private function getParentDir($fileStr) {
		$pos = strpos($fileStr, "/");
		$fileStr = substr($fileStr, $pos, strlen($fileStr));
		$pos2 = strpos($fileStr, "/",1)-1;
		return substr($fileStr, 1, $pos2);	
	}
	
	# function to get the full path
	function getFullDir($fileStr) {
		$pos = strripos($fileStr, "/")+1;
		return substr($fileStr, 0, $pos);
	}
	

	# function to iterate through single drive
	private function singleDrive($file_option, $drive, $file_ext) {	
		$path = $drive.':/';			# get drive c:/,d:/,etc..
		$folderList = array();			# initialize folder list array
		$results = array();				# initialize results local array
		try {		
			foreach (new DirectoryIterator($path) as $fileInfo) {	# scan folder
				if($fileInfo->isDot())  continue;	
				if(!in_array($fileInfo->getFilename(), $this->protectedFolders)) {		# don't scan protected windows system folder	
					$folderList[] = $fileInfo->getFilename();							# get folderlist subname
				}
			} 			
			foreach($folderList as $folder) {						# scan folderlist 
				if(is_file($path.$folder)) {						# if it is a file
					$results[] = $path.$folder;						# then save it in array results
				} else {
					$iterator = new RecursiveDirectoryIterator($path.$folder);		# loop through other sub-directories
					foreach ( new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file ) {		# get other files in sub-directories
						if ($file->isFile()) {																		# if it is a file then..
							$thispath = "";
							if($this->isInteger($file_ext) > 0) {
								if($this->getFileType($file_ext, $file->getFilename())) {
									$thispath = str_replace('\\', '/', $file);										# get the filename
									//$thisfile = utf8_encode($file->getFilename());								# encode the filename
									$results = array_merge_recursive($results, $this->pathToArray($thispath)); 	# merge other sub-directories
								}
							} else if($this->getFileType2($file_ext, $file->getFilename())) {	# check file extension	
									$thispath = str_replace('\\', '/', $file);										# get the filename
									//$thisfile = utf8_encode($file->getFilename());								# encode the filename									
									$results = array_merge_recursive($results, $this->pathToArray($thispath)); 				# merge other sub-directories
							}														
						}  #end inner if
					} #end inner foreach
				}  #end else
			} # end outer foreach
			$this->system_path = $path;		# return the system path 
			$this->file_results = $results;		# return the results
			$this->session->set_userdata( array('is_multi' => 0, 'dir' => $path) );	 # set other data session
			return true;			# set return true if all settings are successfuly done.
		} catch(Exception $e) {		
			$this->error = $e->getMessage();	# return error
			return false;			# return false not succeed
		}			
	} # end function single drive
	
	# Check if the value is integer
	private function isInteger($input){
		return(ctype_digit(strval($input)));	# if it is integer, return true or 1
	}
	
	# function for multi level dir...
	private function multiDirectory($file_option, $location, $file_ext) {
		$dir = $this->returnDrive($location);			# get location dir
		$dir .= ":".$this->returnPath($location);		# get the parent path
		$results = array();								# initialize results array
		$pathArray = array();
		if (is_dir($dir)) {										# if it is a directory
			$iterator = new RecursiveDirectoryIterator($dir);	# then assign to iterator
			foreach ( new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST) as $file ) {		# and loop inside sub-directories
				if ($file->isFile()) {							# if it is a file
					$thispath = "";					
					if($this->isInteger($file_ext) > 0) {
						if($this->getFileType($file_ext, $file->getFilename()) && !in_array($file->getFilename(), $this->protectedFolders)) {
							$thispath = str_replace('\\', '/', $file);										# get the filename
							$pathArray = array_merge_recursive($pathArray, $this->pathToArray($this->getHashKey($thispath)));	# assing path array with haskey name
							$results = array_merge_recursive($results, $this->pathToArray($thispath)); 				# merge other sub-directories
						}
					} else if($this->getFileType2($file_ext, $file->getFilename()) && !in_array($file->getFilename(), $this->protectedFolders)) {	# check file extension	
						$thispath = str_replace('\\', '/', $file);										# get the filename								
						$pathArray = array_merge_recursive($pathArray, $this->pathToArray($this->getHashKey($thispath)));	# assing path array with haskey name
						$results = array_merge_recursive($results, $this->pathToArray($thispath)); 				# merge other sub-directories
					}																	
				} #end outer if
			} # end foreach
			/*foreach($results as $key => $result) {		# loop results array
				if(!in_array($this->getHashKey($result),$this->array_not_unique($pathArray))) {		# if not found hashkey
					//unset($results[$key]);															# then unset the key value array
				} #end if
			} */#end foreach
			$this->system_path = $dir;								# assign dir to system path
			$this->file_results = $results;							# assign results
			$this->session->set_userdata( array('is_multi' => 1) );	# set other data sessions
			return true;											# return successuly
		} else {
			return false;											# return failed
		}		
	} # end function
	
	# 
	private function getFileType2($type, $fileName) {
		$type = strtolower($this->trim_all($type));
		$fileName = strtolower($this->trim_all($this->getExtension($fileName)));
		if($type == $fileName) {
			return true;
		} else if($type == 1) {
			return true;
		} 
		return false;
	}
	
	# function to return the file type option	
	private function getFileType($type,$fileName) {
		$fileName = utf8_encode($fileName);
		if($type == 2) {	# Music type
			return $this->tryAudioFormats($fileName);
		} elseif($type == 3) {	# Video type
			return $this->tryVideoFormats($fileName);
		} elseif($type == 4) {	# Docs type
			return $this->tryDocsFormats($fileName);
		} elseif($type == 5) {	# xls type
			return $this->tryXlsFormats($fileName);
		} elseif($type == 6) {	# pdf type
			return $this->tryPdfFormat($fileName);
		} elseif($type == 7) {	# img type
			return $this->tryImageFormats($fileName);
		} else {		# default is all file types
			return true;
		}
	} # end function

	# get hash
	function getHashKey($fileName) {
		return hash_file('md5', $fileName);																# return the hash of the filename and its contents
	} 
	
	# function to scan the file type image option
	private function tryImageFormats($fileName) {
		$image_list_formats = array('jpg','jpeg','gif','png','bmp');									# add more image formats here
		$fileName = strtolower( $this->trim_all( $this->getExtension($fileName) ) );				
		if( in_array($fileName, $image_list_formats) ) 
			return true;
		return false;
	}
	
	# function to scan the file type audio format
	private function tryAudioFormats($fileName) {				
		$audio_list_formats = array('3gp', 'act', 'aif', 'aiff', 'aac', 'alac', 'amr', 'atrac',
								'au', 'awb', 'dct', 'dss', 'dvf', 'flac', 'gsm',
								'iklax', 'ivs', 'm4a', 'm4p', 'mid', 'mmf', 'mp3', 'mpc',
								'msv', 'ogg', 'Opus', 'raw', 'tta', 'vox', 'wav', 'wma');					# add more audio formats here
		$fileName = strtolower( $this->trim_all( $this->getExtension($fileName) ) );				
		if( in_array($fileName, $audio_list_formats) ) 
			return true;
		return false;
	}
	
	# function to scan the file type video format
	private function tryVideoFormats($fileName) {
		$video_list_formats = array('flv', 'avi', 'mov', 'mpg', 'mpeg', 'mp4', 'wmv', '3gp', 'asf', 'rm', 'swf');	# add more video formats here
		$fileName = strtolower( $this->trim_all( $this->getExtension($fileName) ) );			
		if( in_array($fileName, $video_list_formats) ) 
			return true;
		return false;
	}
	
	# function to scan the file type xls format
	private function tryXlsFormats($fileName) {				
		$xls_file_formats = array('xls', 'xlsx', 'xlsm', 'xlsb', 'xltm', 'xlam');							# add more xls formats here
		$fileName = strtolower( $this->trim_all( $this->getExtension($fileName) ) );						
		if( in_array($fileName, $xls_file_formats) ) 
			return true;
		return false;
	}
	
	# function to scan the file type docs format
	private function tryDocsFormats($fileName) {				
		$docs_file_formats = array('doc', 'docx');															# add more docs formats here
		$fileName = strtolower( $this->trim_all( $this->getExtension($fileName) ) );												
		if( in_array($fileName, $docs_file_formats) ) 
			return true;
		return false;
	}
	
	# function to scan the file type pdf format
	private function tryPdfFormat($fileName) {																# add more pdf formats here
		$fileName = strtolower( $this->trim_all( $this->getExtension($fileName) ) );						
		if($fileName == 'pdf') 
			return true;
		return false;
	}
	
	# get the extension of the file
	private function getExtension($fileName) {
		/*
		 * "." for extension should be available and not be the first character
		 * so position should not be false or 0.
		 */
		$lastDotPos = strrpos($fileName, '.');
		if ( !$lastDotPos ) return false;
		return substr($fileName, $lastDotPos+1);
	}
	
	# trim all strings and characters to remove other spaces and whitespaces
	private function trim_all( $str,$what=NULL,$with='')
	{
		if( $what === NULL )
		{
			//	Character      Decimal      Use
			//	"\0"            0           Null Character
			//	"\t"            9           Tab
			//	"\n"           10           New line
			//	"\x0B"         11           Vertical Tab
			//	"\r"           13           New Line in Mac
			//	" "            32           Space
			
			$what = "\\x00-\\x20";	//all white-spaces and control chars
		}		
		return trim( preg_replace("/[".$what."]+/",$with,$str),$what );
	}
	
	# function to display the temporary deleted files
	public function temporary_files($parent_id) {
		if(!empty($parent_id)) {											# if parent is not empty
			$data['id'] = $parent_id;										# get parent id
			$data['results'] = $this->file_model->getTempFiles($parent_id);	# get data is deleted = 1
			$data['page_title'] = "Results: Temporary deleted files";		# set page title
			$this->load->view('template/header',$data);						# load header view
			$this->load->view('result_temp_view');							# and return the results assign to view
			$this->load->view('template/footer');							# load footer view
		}
	}
	
	# check type of button option
	public function check() {
		$p_id = intval($this->session->userdata('p_id'));		
		if(isset($_POST['delete_option'])) {								# if deleted option button
			if(count($_POST['items']) > 0) {
				foreach($this->input->post('items') as $id) {				# get all items
					$this->file_model->updateFile($id,'delete_option');		# and call the method updateFile
				}			
			}
		} elseif(isset($_POST['delete_forever'])) {							# id permanent delete option button
			if(count($_POST['items']) > 0) {
				foreach($this->input->post('items') as $id) {				# get items by id
					$this->file_model->deleteForever($id);					# and invoke deletedForever method
				}			
			}
			redirect("/main/temporary_files/$p_id", 'refresh'); 			# redirect to page
		} else {	// restore												# if restore button
			if(count($_POST['items']) > 0) {			
				foreach($this->input->post('items') as $id) {				# get items by id
					$this->file_model->updateFile($id,'restore_option');	# and restore the file
				}	
			}
		}		
		redirect("/main/view/$p_id", 'refresh');							# redirect to view page
	}
	
	# function to extract the files and save into db
	function extractFiles($parent_id,$results) {	
		foreach($results as $result) {														# loop through results
			$file_size = filesize($result);													# get filesize
			$last_modified = date ("F d Y H:i:s.", filemtime($result));						# validate date
			$this->file_model->saveFile($parent_id, $result, $file_size, $last_modified);	# save to db
		}
	}
	
	# get driver path
	function returnDrive($path) {			
		return $path[0];			# c,d,etc..
	}
	
	function returnPath($path) {	# return last path
		$str = explode(":",$path);
		return $str[1];		
	}
	
	# extract path to array
	function pathToArray($path , $separator = '/') {
		return array($path);
	}
		
}
/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */