<?php
if (isset($_SESSION['FlashMessages'])) {
  $messages = unserialize($_SESSION['FlashMessages']);
  for ($i = 0; $i < count($messages); $i++) {
    if (isset($messages[$i]['Type'])) {
      $category = $messages[$i]['Type'];
      if ($messages[$i]['Type'] == 'danger') {
        $icon = 'icon-exclamation-sign';
      } elseif ($messages[$i]['Type'] == 'success') {
        $icon = 'icon-ok-sign';
      } else {
        $icon = 'icon-info-sign';
      }
    } else {
      $category = 'info';
      $icon = 'icon-info-sign';
    } ?>
    <div class="alert mb-0 alert-<?php echo $category; ?> alert-dismissible fade show" role="alert">
        <!-- <i class="<?php echo $icon; ?> mx-3"></i> -->
       
        <?php echo $messages[$i]['Text'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php
  }
  unset($_SESSION['FlashMessages']);
}
?>