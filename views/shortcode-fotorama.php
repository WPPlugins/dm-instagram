<div id="dminstagram" class="fotorama"<?php echo $config; ?>>
    <?php
    if($caption == 'false') {
        foreach($data as $img) {
            echo "<img src='{$img->images->standard_resolution->url}'/>";
        }
    } else {
        foreach($data as $img) {
            echo "<img src='{$img->images->standard_resolution->url}' data-caption='{$img->caption->text}'/>";
        }
    }

    ?>
</div>