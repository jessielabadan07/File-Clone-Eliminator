<?php 

# Our class File_model extends the base/parent class CI_Model, in order to use and inherit existing methods.
class File_model extends CI_Model {
	
	# default constructor
	function __construct() {
		parent::__construct();	# call the base constructor of CI_Model
	}
	
	# Function to save the parent directory or the default scan dir, Ex..c:/hello, D:/, c:/test, etc..
	function saveParentDir($parent_dir) {													
		$query_str = "INSERT INTO parent_directory (root_directory, dateadded) VALUES 
								(?, now() )";												
		$this->db->query($query_str, array($parent_dir));	
		return $this->db->insert_id();							# return the last parent id
	} # end function
	
	# Function to save all the scanned files.
	function saveFile($parent_id,$filePath,$file_size,$date_modified) {
		$query_str = "INSERT INTO file_list (parent_id, file_path, generated_filename, file_size, hash_key, is_deleted, date_modified, date_added) VALUES 
								(?,?,?,?,?,0,?,now())";				
		$hash_key = hash_file('md5', $filePath);			# get the hashkey of the complete filename and directory
		$gen_name = md5(uniqid(rand(), true));				# generate random name in order to avoid conflict with deleting the file.
		$this->db->query($query_str, array($parent_id,$filePath,$gen_name,$file_size,$hash_key,$date_modified));	# do the save query
	} # end function
	
	# Function to determine if temporary delete option button or restore option button is selected.
	function updateFile($id,$option_type) {
		$option_type = ($option_type == "delete_option") ? 1 : 0;		# If it is temporary delete option then perform temporary delete option, else perform restore option
		$data = array(
					'is_deleted'=>$option_type,					
				  );
		$this->db->where('id',$id);
		$this->db->update('file_list',$data);  
		if($option_type == 1) {
			$this->deleteFileById($id);									# If 1, call function delete file by id, temporary delete only.
		} else {
			$this->restoreFileById($id);								# Else call function to restore the temporary delete file(s).
		}
	} #end function
	
	# Function to permanentyl delete the file in the temp files and in the db record.
	function deleteForever($id) {
		$sql = "SELECT * FROM file_list WHERE id = ?";
		$rst = $this->db->query($sql, array($id))->result();	
		$temp_Name = "temp_files/".$rst[0]->generated_filename;			# get the random filename of the file
		if (is_file($temp_Name))										# If it is a file and exists.
		{
			unlink($temp_Name);											# delete it completely
		}
		$this->db->where('id', $id);					
		$this->db->delete('file_list'); 								# delete it also in the file_list(record) table
	}	# end function
	
	# A function to delete the file temporary, this gonna happen in updateFile function...see line 29.
	function deleteFileById($id) {
		$sql = "SELECT * FROM file_list WHERE id = ?";					# select first the record by id
		$rst = $this->db->query($sql, array($id))->result();			# get the result
		$path = $rst[0]->file_path;										# get the file path directory
		$temp_Name = $rst[0]->generated_filename;						# get the generated filename
		$newfile = "temp_files/$temp_Name";								# assign generated filename to temp_files directory
		if (!copy($path, $newfile)) {									# perform the transferring of the file
			echo "failed to copy $file...\n";							# It  will only display if the operation did not succeed.
		}		 
		if (is_file($path))												# get the original path of directory
		{
			unlink($path);												# and delete it in original directory
		}
	} # end function
	
	# A function to restore the temporary delted file, this gonna happen in updateFile function...see line 29.
	function restoreFileById($id) {
		$sql = "SELECT * FROM file_list WHERE id = ?";					# select first the record by id
		$rst = $this->db->query($sql, array($id))->result();			# get the result
		$path = $rst[0]->file_path;										# get the original file path directory
		$temp_Name = $rst[0]->generated_filename;						# get the generated filename
		$newfile = "temp_files/$temp_Name";								# get the temporary delete gen filename in temp_files
		if (!copy($newfile, $path)) {									# and restore it in the original file path directory
			echo "failed to copy $file...\n";							# It  will only display if the operation did not succeed.
		}		
		if (is_file($newfile))											# if still exist
		{
			unlink($newfile);											# delete the file in the temp_files
		}
	} # end function
	
	# function to display the scanned results history
	function getAllHistory() {
		$query = $this->db->get('parent_directory');					# simply query the parent_directory tbl
        return $query->result();										# return the results
	} # end function
	
	function getParentDir($pid) {
		$this->db->where('id',$pid);
		$query = $this->db->get('parent_directory');
		return $query->result();
	}
	
	# Simple query to get all the files by id
	function getFiles($id) {
		$sql = "SELECT parent_directory.id as p_id,
				parent_directory.root_directory,
				parent_directory.dateadded,				
				file_list.id,
				file_list.file_path,
				file_list.generated_filename,
				file_list.file_size,
				file_list.hash_key,
				file_list.is_deleted,
				file_list.date_modified,
				file_list.date_added
				FROM parent_directory
				INNER JOIN file_list ON parent_directory.id = file_list.parent_id
				WHERE parent_directory.id = ? ORDER BY file_list.hash_key";
		return $this->db->query($sql, array($id))->result();				# return the results
	} # end function
	
	# function to simply get the temporary deleted files
	function getTempFiles($id) {
		$isDeleted = 1;														# filter by assigning isDeleted == 1??
		$sql = "SELECT * FROM parent_directory p
				INNER JOIN file_list fl ON p.id = fl.parent_id
				WHERE p.id = ? AND is_deleted = ? ORDER BY fl.hash_key";
		return $this->db->query($sql, array($id, $isDeleted))->result();	# return the results
	}
	
	# get all extension file names
	function getExtensionList() {
		$query = $this->db->get('file_type');					
        return $query->result();
	}
	
} # end class

?>