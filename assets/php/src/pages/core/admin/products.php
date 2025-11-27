    <!-- Quill Editor JS -->
    <!-- <script src="<?php echo "{$base}assets/libs/quill/quill.min.js" ?>"></script> -->

    <!-- Internal Quill JS -->
    <!-- <script src="<?php echo "{$base}assets/js/quill-editor.js"?>"></script> -->

    <!-- Custom JS -->
    <!-- <script src="<?Php echo "{$base}assets/js/custom.js"?>"></script> -->
    


  <script>

   document.querySelectorAll('.productEdit').forEach(item => {
    console.log(item);
    item.addEventListener('click', event => {
        let productID = item.dataset.id;
        console.log(productID);
      // Handle click
 
      let form =item.parentElement.parentElement.parentElement.parentElement;
        console.log(form);
        
      let editForm =form.querySelector('.editForm');
      console.log(editForm)
        editForm.classList.toggle('d-none');
        item.innerHTML = '<i class="ri-close-circle-line"></i>';
         
    
      console.log('clicked');
      console.log(item);
    });
});
  </script>