<?php $rutapie="https://lab.lacallecr.com/VV/utilities/tema/";?>
<!-- Vendor js -->
<script src="<?php echo $rutapie;?>/js/vendor.min.js?v=<?php echo $v;?>"></script>
<!-- Data tables -->
<script src="<?php echo $rutapie;?>libs/datatables/jquery.dataTables.min.js?v=<?php echo $v;?>"></script>
<script src="<?php echo $rutapie;?>libs/datatables/dataTables.bootstrap4.js?v=<?php echo $v;?>"></script>
<script src="<?php echo $rutapie;?>libs/datatables/dataTables.responsive.min.js?v=<?php echo $v;?>"></script>
<script src="<?php echo $rutapie;?>libs/datatables/responsive.bootstrap4.min.js?v=<?php echo $v;?>"></script>
<script src="<?php echo $rutapie;?>libs/datatables/dataTables.buttons.min.js?v=<?php echo $v;?>"></script>
<script src="<?php echo $rutapie;?>libs/datatables/buttons.bootstrap4.min.js?v=<?php echo $v;?>"></script>
<script type="text/javascript" src="<?php echo $rutapie;?>libs/datatables/DataTables/datatables.min.js?v=<?php echo $v;?>"></script>
<!-- Datatables init 
<script src="<?php //echo $rutapie;?>js/pages/datatables.init.js?v=<?php //echo $v;?>"></script>-->


<!-- Date picker-->
<script src="<?php echo $rutapie;?>libs/flatpickr/flatpickr.min.js?v=<?php echo $v;?>"></script>
<script src="<?php echo $rutapie;?>libs/bootstrap-datepicker/bootstrap-datepicker.min.js?v=<?php echo $v;?>"></script>
<!-- Init js
<script src="<?php //echo $rutapie;?>js/pages/form-pickers.init.js?v=<?php //echo $v;?>"></script>-->


<script src="<?php echo $rutapie;?>libs/jquery-nice-select/jquery.nice-select.min.js?v=<?php echo $v;?>"></script>
<script src="<?php echo $rutapie;?>libs/switchery/switchery.min.js?v=<?php echo $v;?>"></script>
<script src="<?php echo $rutapie;?>libs/multiselect/jquery.multi-select.js?v=<?php echo $v;?>"></script>
<script src="<?php echo $rutapie;?>libs/select2/select2.min.js?v=<?php echo $v;?>"></script>
<script src="<?php echo $rutapie;?>libs/jquery-mockjax/jquery.mockjax.min.js?v=<?php echo $v;?>"></script>
<script src="<?php echo $rutapie;?>libs/autocomplete/jquery.autocomplete.min.js?v=<?php echo $v;?>"></script>
<script src="<?php echo $rutapie;?>libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js?v=<?php echo $v;?>"></script>
<script src="<?php echo $rutapie;?>libs/bootstrap-maxlength/bootstrap-maxlength.min.js?v=<?php echo $v;?>"></script>
<!-- Init js
<script src="<?php //echo $rutapie;?>js/pages/form-advanced.init.js?v=<?php //echo $v;?>"></script>-->

<script>
	// Inicializo input de fechas
	$(".fechas").flatpickr();

	// Inicializo selects
	$('.select2').select2({
	  placeholder: 'Elija una opción'
	});

	var elems = document.querySelectorAll('[data-plugin="switchery"]');
	var defaults={
		color:"#09c",
		secondaryColor:"#dfdfdf",
		jackColor:"#fff",
		jackSecondaryColor:null,
		className:"switchery",
		disabled:false,
		disabledOpacity:.5,
		speed:"0.4s",
		size:"small"
	};

	for (var i = 0; i < elems.length; i++) {
	  var switchery = new Switchery(elems[i],defaults);
	}

</script>

<script>
            $(document).ready(function() {
            	    $('#dataTables').DataTable( {
            	        responsive: true,
            	        "pageLength": 50,
            	        dom: 'Blfrtip',//
            	        buttons: ['copy','csv', 'pdf', 'print', 'excel']
            	});
            });
                
        </script>


<!-- Tippy js ::: tooltips, -->
<script src="<?php echo $rutapie;?>libs/tippy-js/tippy.all.min.js?v=<?php echo $v;?>"></script>
<!-- App js -->
<script src="<?php echo $rutapie;?>js/app.min.js?v=<?php echo $v;?>"></script>

<!-- Summernote js -->
<script src="<?php echo $rutapie;?>libs/summernote/summernote-bs4.min.js?v=<?php echo $v;?>"></script>

<!-- Init js -->
<script src="<?php echo $rutapie;?>js/pages/form-summernote.init.js?v=<?php echo $v;?>"></script>