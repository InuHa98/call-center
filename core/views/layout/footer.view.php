<?php

	//echo round(memory_get_usage() / 1024 / 1024, 2) . ' MB / '.round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB<br>';


?>
		</div></div>
		</div>
		<div id="back-to-top" class="back-to-top">
			<i class="fas fa-arrow-up"></i>
		</div>
		<script type="text/javascript" src="<?=APP_URL;?>/assets/js/main.js?v=<?=$_version;?>"></script>
		<script type="text/javascript" src="<?=APP_URL;?>/assets/js/app.js?v=<?=$_version;?>"></script>

		<link rel="stylesheet" href="<?=APP_URL;?>/assets/css/toast-dialog.css?v=<?=$_version;?>">
		<script type="text/javascript" src="<?=APP_URL;?>/assets/js/toast-dialog.js?v=<?=$_version;?>"></script>
		<script type="text/javascript" src="<?=APP_URL;?>/assets/js/custom-select.js?v=<?=$_version;?>"></script>

		<script type="text/javascript">
			(function(){
				const alerts = <?=json_encode(Alert::show());?>;
				for(let alert of alerts)
				{
					$.toastShow(alert.message, {
						type: alert.type,
						timeout: alert.timeout || 3000
					});				
				}
			})();
		</script>
	</body>
</html>
<?php
	Alert::clear();
?>