<?php
if(empty($obj) || !isset($obj->categoriesTopButtonsFluid)){
    $obj = AVideoPlugin::loadPlugin('Layout');
}
?>
<style>
    .categoriesTobButtons{
        border: none;
    }
    .categoriesTobButtons li a{
        border-radius:23px;
    }
</style>
<div class="container-fluid">
    <div class="row">
        <div class="<?php echo empty($obj->categoriesTopButtonsFluid) ? '' : 'col-lg-10 col-lg-offset-1'; ?>">
            
    <center>
        <ul class="nav nav-tabs nav-tabs-horizontal categoriesTobButtons" >
            <?php
            global $advancedCustom;
            $_rowCount = getRowCount();
            $current = getCurrentPage();
            $_REQUEST['rowCount'] = 1000;
            $_REQUEST['current'] = 1;
            $parsed_cats = array();
            $categories = Category::getAllCategories();
            foreach ($categories as $value) {
                if ($value['parentId']) {
                    continue;
                }
                if ($advancedCustom->ShowAllVideosOnCategory) {
                    $total = $value['fullTotal'];
                } else {
                    $total = $value['total'];
                }
                if (empty($total)) {
                    continue;
                }
                if (in_array($value['id'], $parsed_cats)) {
                    continue;
                }
                ?>
                <li data-toggle="tooltip" title="<?php echo __($value['name']); ?>" data-placement="bottom"
                       class="<?php echo ($value['clean_name'] == @$_REQUEST['catName'] ? "active" : ""); ?>">
                    <a href="<?php echo Category::getCategoryLinkFromName($value['clean_name']); ?>" >
                           <?php
                           echo '<i class="' . (empty($value['iconClass']) ? "fa fa-folder" : $value['iconClass']) . '"></i>  '
                           . '<span class="">' . __($value['name']) . '</span>';
                           if (!empty($obj->categoriesTopButtonsShowVideosCount)) {
                               echo ' <span class="badge">' . $total . '</span>';
                           }
                           ?>
                    </a>
                </li>
                <?php
            }
            $_REQUEST['rowCount'] = $_rowCount;
            $_REQUEST['current'] = $current;
            /*
            for ($i = 0; $i < 100; $i++) {
                ?> <li data-toggle="tooltip" title="<?php echo __($i); ?>" data-placement="bottom"> <a href="#"> <?php echo '<i class="fa fa-folder"></i>  <span class="hidden-xs">' . $i . '</span>';
            if (!empty($obj->categoriesTopButtonsShowVideosCount)) {
                echo ' <span class="badge">' . $i . '</span>';
            } ?> </a> </li> <?php } 
            */
            ?>
        </ul>
    </center>
        </div>
    </div>
</div>