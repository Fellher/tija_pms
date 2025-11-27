<div class="container-fluid">
   <div class="card card-body">
      <h5 class="card-title border-bottom pb-0 mb-3 d-flex align-items-center justify-content-between">
         <span> <?= $selected->title ?> </span>
       
         <a class=" float-end btn btn btn-sm  btn-icon rounded-pill btn-primary-light" 
            href="#manageEntityAddress" 
            data-bs-toggle="modal" 
            role="button" 
            aria-expanded="false" 
            aria-controls="manageEntityAddress">
            <i 
               class="ri-add-line" 
               data-bs-toggle="tooltip" 
               data-bs-placement="top" 
               title="Add Contact and Address"
            >
            </i>
         </a>
      </h5>
      <div class="col-12 d-flex flex-wrap justify-content-end mb-3">
         <?php        
         if(isset($selected->subMenu) && !empty($selected->subMenu)) {            
            // var_dump($selected->subMenu);
            if(is_array($selected->subMenu)) {
               $subMenuPage = $_GET['subMenu'] ?? $selected->subMenu[0]->slug;             
               foreach($selected->subMenu as $subMenuItem) {                 
                  // var_dump($subMenuItem);
                  $isActive = ($subMenuItem->slug == $subMenuPage) ? ' active ' : ' ';                
                        // $isActive ? $subMenuSelected  = $subMenuItem : null;                      
                  echo "<a href='{$base}html/{$getString}&subMenu={$subMenuItem->slug}' class='btn btn-sm btn-outline-primary   mx-1  {$isActive}'> {$subMenuItem->title}</a>";  
               }
            } else {
               $subMenuPage = $_GET['submenu'] ?? $selected->subMenu->slug;
              
               $isActive = ($selected->subMenu->slug == $subMenuPage) ? 'active bg-light-blue' : '';  

               
               echo '<a href="'.$selected->subMenu->link.'" class="btn btn-sm btn-outline-primary mx-1">'.$selected->subMenu->name.'</a>';           
            }
            var_dump($subMenuPage);
         }?>
      </div>
   </div>
</div>