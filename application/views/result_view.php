<div id="container" class="resultContainer">
<a href="<?php echo site_url(); ?>" style="margin-left:10px; font-size:12pt;">Home</a>
<h1>Results of directory: <?php if(count($results) > 0) {  } ?></h1>	<?php // if results array is not empty or greater than 0 then display the parent root directory ?>
<div class="options_div">
<form id="submit_options" method="POST" action="/filelist/main/check">
<div class="left_div">
Show history by date: 
<select id="history" name="history">
	<option value="0">---Select here---</option><?php # history_results to display all the history scanned dirs. ?>
	<?php foreach($history_results as $val): ?>			<?php // get the history results array ?>
	<option value="<?php echo $val->id; ?>" <?php echo ($val->id == $id ) ? "selected" : "";?>><?php echo $val->dateadded; ?></option>	<?php // display the history by date and value is id ?>
	<?php endforeach; ?>
</select>
<a class="showTemp" href="/filelist/main/temporary_files/<?php echo $id; ?>">Show temporary deleted file(s)</a>		<?php // link to display the temporary deleted file(s) ?>
</div>
<div class="right_div">
	<input type="submit" class="button-link" id="submit_option" name="delete_option" value="DELETE" />		<?php // delete option button to temporary delete the file(s) ?>
</div>

</div>
<div id="selectDiv" class="resultDiv">
<table id="tbl_results">
<tr>
	<th class="fileName">Filename</th>
	<th class="fileSize">File size</th>
	<th class="dateMod">Date Modified</th>
	<th class="thOption">&nbsp;</th>
</tr>
</table>
<?php
$dataArray = array();
foreach($results as $key => $val) {		// extract the results array	
	if(!empty($val) && sizeof($val) > 1) {		
		echo '<div class="divKey" style="background-color:#'.random_color().';">';
		foreach($val as $rst) {					
?>		
			<table class="<?php echo $key; ?>"><tr>
			<td class="rstFile"><?php echo $rst->file_path; ?></td>				<?php // display the file path ?>
			<td class="rstSize"><?php echo sizeFilter($rst->file_size); ?></td>	<?php // display the file size ?>
			<td class="rstDate"><?php echo $rst->date_modified; ?></td>			<?php // display date modified ?>
			<td class="rstCheck"><input type="checkbox" id="item" value="<?php echo $rst->id; ?>" name="items[]"></td>				<?php // checkbox item and get the id ?>		
			</tr></table>
<?php					
		}		
		echo '</div><div class="spaceBottom"></div>';
	}
}
# function sizeFilter
function sizeFilter( $bytes ) {
	$label = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );			# store the file array
	for( $i = 0; $bytes >= 1024 && $i < ( count( $label ) -1 ); $bytes /= 1024, $i++ );	# do the calculations by file size
	return( round( $bytes, 2 ) . " " . $label[$i] );					# and return the result with label
}
# function to generate a random hex color
function random_color_part() {
    return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
}
# function to get the 3 generated random_color_part hex colors
function random_color() {
    return random_color_part() . random_color_part() . random_color_part();
}
?>
</div>
</form>
<div class="footerCopy" style="padding-left:10px;">Clone File Eliminator &copy; <?php echo date("Y"); ?> </div>
</div>
<script type="text/javascript">
$(document).ready(function() {			// load jquery document
	$('#history').change(function() {	// If select history has changed
		if($(this).val() > 0) {			// if value id is greater than 0
			window.location = '/filelist/main/view/'+$(this).val();		// redirect to page view with id
		}		
	});	
	var checkCount = 0;
	var tblLength = 0;
	var totalCheck = 0;
	$('.divKey input:checkbox').each(function(idx) {	
		if($('.divKey:eq('+idx+') input:checkbox').size() == 1) {
			$('.divKey:eq('+idx+') input:checkbox').attr("disabled", true);
		}
		$(this).click(function() {	
			tblLength = $(this).parents('div.divKey').children('table').length;
			checkCount = $(this).parents('div.divKey').children('table').children().find('td.rstCheck').children('input[type=checkbox]:checked').size();			
			if(checkCount == 0) {
				$(this).parents('div.divKey').children('table').children().find('td.rstCheck').children('input[type=checkbox]').prop('disabled',false);
			}			
			totalCheck = tblLength - checkCount;
			$(this).parents('div.divKey').children('table').each(function() {			
				if(!$(this).children().children().find('td.rstCheck').children('#item').prop('checked') && totalCheck == 1) {
					$(this).children().children().find('td.rstCheck').children('#item').attr("disabled", true);
				} else {
					$(this).children().children().find('td.rstCheck').children('#item').attr("disabled", false);
				}
			});
		});	
	});
});
</script>