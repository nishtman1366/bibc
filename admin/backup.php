<?php
	include_once('../common.php');
	
	if(!isset($generalobjAdmin)){
		require_once(TPATH_CLASS."class.general_admin.php");
		$generalobjAdmin = new General_admin();
	}
	$generalobjAdmin->check_member_login();
	
	//Delete
	$hdn_del_id 	= isset($_POST['hdn_del_id'])?$_POST['hdn_del_id']:'';
	$method 	= isset($_POST['method'])?$_POST['method']:'';
	$old_fileName 	= isset($_POST['fileName'])?$_POST['fileName']:'';
	$fileURL = $tconfig["tsite_upload_files_db_backup"];
	$tbl_name 		= 'backup_database';
	$script 		= 'Back-up';
	$success = 		isset($_REQUEST['success'])?$_REQUEST['success']:'';
	
	//get Back-up
	if(SITE_TYPE !='Demo'){
		if($method != '' && $method == 'backupNow'){
			$tables = array();
			$result = mysqli_query($conn,'SHOW TABLES');
			while($row = mysql_fetch_row($result))
			{
				$tables[] = $row[0];
			}
			
			foreach($tables as $table)
			{
				$result = mysqli_query($condbc,'SELECT * FROM '.$table);
				$num_fields = mysqli_num_fields($result);
				
				$return.= 'DROP TABLE '.$table.';';
				$row2 = mysqli_fetch_row(mysqli_query($condbc,'SHOW CREATE TABLE '.$table));
				$return.= "\n\n".$row2[1].";\n\n";
				
				for ($i = 0; $i < $num_fields; $i++) 
				{
					while($row = mysql_fetch_row($result))
					{
						$return.= 'INSERT INTO '.$table.' VALUES(';
						for($j=0; $j<$num_fields; $j++) 
						{
							$row[$j] = addslashes($row[$j]);
							$row[$j] = ereg_replace("\n","\\n",$row[$j]);
							if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
							if ($j<($num_fields-1)) { $return.= ','; }
						}
						$return.= ");\n";
					}
				}
				$return.="\n\n\n";
			}

			//save file
			$backuppath = $tconfig["tsite_upload_files_db_backup_path"];
			$filename = 'backup_'.date("Y_m_d").'_'.date("H_i").'.sql';
			$outputfilename = $backuppath.$filename;
			$handle = fopen($outputfilename,'w+');
			fwrite($handle,$return);
			fclose($handle);
			
			$q = "insert";
			$query = $q ." `".$tbl_name."` SET
				`vFile` = '".$filename."',
				`eType` = 'Manual',
				`dDate` = '".date('Y-m-d h:i:s')."'";
			$id = $obj->sql_query($query);
			$success = 1;
		}
	}else {
		$success = 2;
	}
	
	//delete record
	if($hdn_del_id != ''){
		$sqls = "DELETE FROM $tbl_name WHERE iBackupId='$hdn_del_id'";
		$did = $obj->MySQLSelect($sqls);
		
		$backuppaths = $tconfig["tsite_upload_files_db_backup_path"];
		@unlink($backuppaths.$old_fileName);
	}
	
	$sql = "SELECT * FROM ".$tbl_name." WHERE 1=1 ORDER BY dDate";
	$db_data = $obj->MySQLSelect($sql);
	
	$backupEn = $BACKUP_ENABLE;
	$backupTm = $BACKUP_TIME;
	
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
	
	<!-- BEGIN HEAD-->
	<head>
		<meta charset="UTF-8" />
		<title>Admin | Back-up</title>
		<meta content="width=device-width, initial-scale=1.0" name="viewport" />
		<?php include_once('global_files.php');?>
		
		<link href="../assets/plugins/dataTables/dataTables.bootstrap.css" rel="stylesheet" />
		<script type="text/javascript">
			function confirm_delete()
			{
				var confirm_ans = confirm("Are You sure You want to Delete Banner?");
				return confirm_ans;
				//document.getElementById(id).submit();
			}
		</script>
	</head>
	<!-- END  HEAD-->
	<!-- BEGIN BODY-->
	<body class="padTop53 " >
		
		<!-- MAIN WRAPPER -->
		<div id="wrap">
			<?php include_once('header.php'); ?>
			<?php include_once('left_menu.php'); ?>
			
			<!--PAGE CONTENT -->
			<div id="content">
				<div class="inner">
					<div class="row">
						<div class="col-lg-12">
							<h2>Back-up</h2>
						</div>
					</div>
					<hr />
					
					<?php if($success == 1) { ?>
					<div class="alert alert-success alert-dismissable msgs_hide">
						  <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
						  Back-up took successfully.
					</div><br/>
					<?php } if($success == 3) { ?>
					<div class="alert alert-success alert-dismissable msgs_hide">
						  <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
						  Schedule set successfully.
					</div><br/>
					<?php }elseif ($success == 2) { ?>
					   <div class="alert alert-danger alert-dismissable">
							<button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
							"This Feature" has been disabled on the Demo Admin Panel. This feature will be enabled on the main script we will provide you.
					</div><br/>
					<?php } ?>
					<div class="bkp001">
					<div class="right_bkp001 bkp0011">
					<form name="_backup_form" id="_backup_form" method="post" >
					<p><input type="checkbox" name="BACKUP_ENABLE" id="backupEn" <?php if($backupEn == 'Yes') echo 'checked'; ?> value="Yes">&nbsp;&nbsp;&nbsp;Take schedule backup everyday at &nbsp;
					<select class="form-control bkpSelectTime" name="BACKUP_TIME">
					<?php for($i = 0; $i < 24; $i++): ?>
					  <option value="<?php echo $i; ?>" <?php if($backupTm == $i) echo "selected"; ?>><?php echo $i % 12 ? $i % 12 : 12 ?>:00 <?php echo $i >= 12 ? 'pm' : 'am' ?></option>
					<?php endfor; ?>
					</select>
					<a href="javascript:void(0);" onClick="saveSchedule()" class="btn btn-success">Save</a>
					</p>
					</form>
					</div>
					<form method="post" action="">
					<input type="hidden" name="method" value="backupNow" >
					<div class="left_bkp001 bkp0011"><button type="submit" class="btn btn-primary">Take Back-up Now</button></div>
					</form>
					</div>
					
					<div class="table-list">
						<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-default">
									<div class="panel-heading">
										Back-up
									</div>
									<div class="panel-body">
										<div class="table-responsive">
											<table class="table table-striped table-bordered table-hover" id="dataTables-example">
												<thead>
													<tr>
														<th>File Name</th>
														<th>Date</th>
														<th>Type</th>
														<th>Download</th>
														<th>Delete</th>
													</tr>
												</thead>
												<tbody>
													<?php
														$count_all = count($db_data);
														if($count_all > 0) {
															for($i=0;$i<$count_all;$i++) {
															?>
															<tr class="gradeA">
																<td><?php echo $db_data[$i]['vFile']; ?></td>
																<td><?php echo $db_data[$i]['dDate']; ?></td>
																<td><?php echo $db_data[$i]['eType']; ?></td>
																<?php if(SITE_TYPE != 'Demo') { ?>
																<td align='center'><a href="download_file.php?file=<?php echo $db_data[$i]['vFile']; ?>" target="_blank" ><img src="img/download.png" alt="Delete"></a></td>
																<?php }else { ?>
																<td><a href="javascript:void(0);" title="You can not download in demo version."></a></td>
																<?php } ?>
																<td width="10%" align="center">
																	<!-- <a href="languages.php?id=<?php echo $id;?>&action=delete"><i class="icon-trash"></i> Delete</a>-->
																	<form name="delete_form" id="delete_form" method="post" action="" onSubmit="return confirm_delete()" class="margin0">
																		<input type="hidden" name="hdn_del_id" id="hdn_del_id" value="<?php echo $db_data[$i]['iBackupId']; ?>">
																		<input type="hidden" name="fileName" value="<?php echo $db_data[$i]['vFile']; ?>" >
																		<button class="remove_btn001" data-toggle="tooltip" title="Delete BackUp">
																			<img src="img/delete-icon.png" alt="Delete">
																		</button>
																	</form>
																</td>
															</tr>
														<?php } } ?>
												</tbody>
											</table>
										</div>
										
									</div>
								</div>
							</div> <!--TABLE-END-->
						</div>
					</div> 
				</div>
			</div>
			<!--END PAGE CONTENT -->
		</div>
		<!--END MAIN WRAPPER -->
		
		<?php include_once('footer.php');?>
		
		<script src="../assets/plugins/dataTables/jquery.dataTables.js"></script>
		<script src="../assets/plugins/dataTables/dataTables.bootstrap.js"></script>
		<script>
		
			function saveSchedule() {
				var formData = $("#_backup_form").serialize();
				$.ajax({
					type: 'post',
					url: 'save_backup_schedule.php',
					data: formData,
					success: function(response) {
						window.location.href="backup.php?success=3";
					},
					error: function(response) {
						
					}
				});
			}
		
			$(document).ready(function () {
				$('#dataTables-example').dataTable( {"bSort": true } );
			});
		</script>
	</body>
	<!-- END BODY-->    
</html>
