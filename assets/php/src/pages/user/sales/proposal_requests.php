

  <!-- Prism JS -->
  <script src="<?= $base ?>assets/libs/prismjs/prism.js"></script>
    <script src="<?= $base ?>assets/js/prism-custom.js"></script>

 <!-- Filepond JS -->
 <script src="<?= $base ?>assets/libs/filepond/filepond.min.js"></script>
    <script src="<?= $base ?>assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js"></script>
    <script src="<?= $base ?>assets/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js"></script>
    <script src="<?= $base ?>assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js"></script>
    <script src="<?= $base ?>assets/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js"></script>
    <script src="<?= $base ?>assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.js"></script>
    <script src="<?= $base ?>assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
    <script src="<?= $base ?>assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js"></script>
    <script src="<?= $base ?>assets/libs/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js"></script>
    <script src="<?= $base ?>assets/libs/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js"></script>
    <script src="<?= $base ?>assets/libs/filepond-plugin-image-transform/filepond-plugin-image-transform.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page with filepod loaded successfully');
    });
    // Initialize FilePond plugins
    FilePond.registerPlugin(
        FilePondPluginImagePreview,
        FilePondPluginImageExifOrientation,
        FilePondPluginFileValidateSize,
        FilePondPluginFileEncode,
        FilePondPluginImageEdit,
        FilePondPluginFileValidateType,
        FilePondPluginImageCrop,
        FilePondPluginImageResize,
        FilePondPluginImageTransform,        
    );
    

    // Set up the filepond instance
    const inputElement = document.querySelector('.proposalChecklistItemUploadfile');
    const pond = FilePond.create(inputElement
        // Set options for the filepond instance, 
       /* {
            allowMultiple: true,
            maxFiles: 5,
            allowRevert: true,
            allowReplace: true,
            allowImagePreview: true,
            allowImageCrop: true,
            allowImageResize: true,
            allowImageTransform: true,
            allowFileEncode: true,
            allowFileValidateType: true,
            allowFileValidateSize: true,
            acceptedFileTypes: ['image/*', 'application/pdf', 'text/plain', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],

            // acceptedFileTypes: ['.doc', '.docx', '.pdf', '.txt', '.xls', '.xlsx', '.ppt', '.pptx', '.jpg', '.jpeg', '.png', '.gif'],
            imagePreviewHeight: 170,
            imageResizeTargetWidth: 800,
            imageResizeTargetHeight: 600
        }*/
    );

    // Optional: Set up server options if needed
    pond.setOptions({
        server: {
            process: '/upload',
            revert: '/revert',
            restore: '/restore',
            load: '/load',
            fetch: null
        }
    });

(function () {
  "use strict";




  

  const SingleElement = document.querySelector('.basic-filepond');
  const MultipleElement = document.querySelector('.multiple-filepond');
  const CircularElement = document.querySelector('.circular-filepond');
  
  /* default input */
  FilePond.create(SingleElement);
  FilePond.create(MultipleElement);
  FilePond.create(CircularElement,
    {
      labelIdle: `<span class="filepond--label-action">Choose File</span>`,
      imagePreviewHeight: 170,
      imageCropAspectRatio: '1:1',
      imageResizeTargetWidth: 200,
      imageResizeTargetHeight: 200,
      stylePanelLayout: 'compact circle',
      styleLoadIndicatorPosition: 'center bottom',
      styleProgressIndicatorPosition: 'right bottom',
      styleButtonRemoveItemPosition: 'left bottom',
      styleButtonProcessItemPosition: 'right bottom',
    }
    );
})();
</script>