    <!-- Quill Editor JS -->
    <!-- <script src="<?php echo "{$base}assets/libs/quill/quill.min.js" ?>"></script> -->
    


<!-- Internal Quill JS -->
<!-- <script src="<?php echo "{$base}assets/js/quill-editor.js"?>"></script> -->



 
    <script>
        // tinymce.init({
        // selector: 'textarea#premiumskinsandicons-borderless',
        // skin: 'borderless',
        // height: 300,
        // plugins: 'advlist autolink link image lists charmap preview',
        // menubar: false,
        // resize: true,
        // });
        // (function () {
        //     'use strict';
        //     /* quill snow editor */
        //     var toolbarOptions = [
        //         [{ header: [1, 2, 3, 4, 5, 6, false] }],
        //         [{ font: [] }],
        //         ['bold', 'italic', 'underline', 'strike'], // toggled buttons
        //         ['blockquote', 'code-block'],

        //         [{ header: 1 }, { header: 2 }], // custom button values
        //         [{ list: 'ordered' }, { list: 'bullet' }],
        //         [{ script: 'sub' }, { script: 'super' }], // superscript/subscript
        //         [{ indent: '-1' }, { indent: '+1' }], // outdent/indent
        //         [{ direction: 'rtl' }], // text direction

        //         [{ size: ['small', false, 'large', 'huge'] }], // custom dropdown

        //         [{ color: [] }, { background: [] }], // dropdown with defaults from theme
        //         [{ align: [] }],

        //         ['image', 'video'],
        //         ['clean'], // remove formatting button
        //     ];
        //     let quill;
         
        //     let qlEditor = document.createElement('div');
        //     // qlEditor.classList.add('d-none');
        //     let editorArr = document.querySelectorAll('.editor');
        //         editorArr.forEach(editor => {  
        //              quill = new Quill('.editor', {
        //                 modules: {
        //                 toolbar: undefined,
        //                 },
        //                 theme: 'snow',
        //             }); 
        //             let newText; 
        //             editor.setAttribute('id', 'editorDiv');                
                  
        //             let editorCurrentContent = editor.querySelector('.ql-editor').innerHTML;
        //             let name = editor.getAttribute('name');
        //             // console.log(name);
        //             // console.log(editor);
        //             // qlEditor.innerHTML = `<textarea class="form-control " name="${editor.getAttribute('name')}"  >${editorCurrentContent}</textarea>`;
        //             editor.parentElement.parentElement.appendChild(qlEditor);
        //             let div = document.createElement('div');
        //             div.classList.add('form-group');
        //             div.classList.add('d-none');
        //             div.classList.add('mt-3');
                   
        //             quill.on('editor-change', function (eventName, ...args) {
               
        //                 if (eventName === 'text-change') {
        //                     console.log(eventName, args);
        //                     newText = editor.querySelector('.ql-editor').innerHTML;
        //                     console.log(newText);
        //                     div.innerHTML = `<textarea class="form-control " name="jobDescription"  >${newText}</textarea>`;
        //                     // args[0] will be delta
        //                     // qlEditor.innerHTML = `<div class="form-group row mt-3"><textarea class="form-control " name="${editor.getAttribute('name')}"  >${newText}</textarea></div>`;
        //                 } 
        //             });
        //             editor.parentElement.parentElement.parentElement.appendChild(div);
        //         });                
        // })();

        // let jobTitleEditModal = document.querySelectorAll('.jobTitleEditModal');
        // jobTitleEditModal.forEach(modal => {
        //     modal.addEventListener('click', function(e){
        //         let jobTitleID = modal.getAttribute('data-id');
        //         let jobTitle = document.querySelector('#jobTitle');
        //         let jobCategoryID = document.querySelector('#jobCategoryID');
        //         let jobDescription = document.querySelector('.editor');
        //         let jobDescriptionDoc = document.querySelector('#formFile');
        //         let jobTitleIDInput = document.querySelector('#jobTitleID');
        //         let jobDescriptionInput = document.querySelector('textarea[name="jobDescription"]');
        //         let jobTitleInput = document.querySelector('input[name="jobTitle"]');
        //         let jobCategoryIDInput = document.querySelector('select[name="jobCategoryID"]');
        //         let jobDescriptionDocInput = document.querySelector('input[name="jobDescriptionDoc"]');
        //         let jobTitleIDValue = jobTitleIDInput.value;
        //         let jobTitleValue = jobTitleInput.value;
        //         let jobCategoryIDValue = jobCategoryIDInput.value;
        //         let jobDescriptionValue ="";
        //         let jobDescriptionDocValue = jobDescriptionDocInput.value;
        //         console.log(jobTitleIDValue, jobTitleValue, jobCategoryIDValue, jobDescriptionValue, jobDescriptionDocValue);
        //         console.log(jobTitleID, jobTitle, jobCategoryID, jobDescription, jobDescriptionDoc);
        //         jobTitleIDInput.value = jobTitleID;
        //         jobTitleInput.value = jobTitleValue;
        //         jobCategoryIDInput.value = jobCategoryIDValue;
        //         // jobDescriptionInput.value = jobDescriptionValue;
        //         jobDescriptionDocInput.value = jobDescriptionDocValue;
        //     });
        // });
       
        
       /* let editorArr = document.querySelectorAll('.editor');
        editorArr.forEach(editor => {

            let qlEditor = document.createElement('div');
            let editorCurrentContent = editor.querySelector('.ql-editor').innerHTML;
            console.log(editorCurrentContent);
          
          



                 
            editor.parentElement.appendChild(qlEditor);
           console.log(editor);


           quill.on('editor-change', function (eventName, ...args) {
            if (eventName === 'text-change') {
                // args[0] will be delta
                qlEditor.innerHTML = editor.querySelector('.ql-editor').innerHTML;
            } 
            });



       }); */

    </script>