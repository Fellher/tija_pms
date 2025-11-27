<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>	
        	
        		
<script src="https://cdn.datatables.net/buttons/2.3.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.2/js/buttons.print.min.js"></script>	

<script>
    jQuery(document).ready(function() {   
       
        jQuery('.trial_balance_table').DataTable({
            "paging":   false,
            "ordering": true,
            "info":     true,
            dom: 'Bfrtip',
            buttons: [
                'copy', 'csv', 'excel', 'print'
            ],
            order: [
                [1, 'asc']
            ]
      
        });
     

    });
</script> 