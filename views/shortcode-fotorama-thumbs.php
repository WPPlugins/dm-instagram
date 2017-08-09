<div id="dminstagram" class="fotorama" data-nav="thumbs"<?php echo $config; ?>>
    <?php
    if($caption == 'false') {
        foreach($data as $img) {
            echo "<a href='{$img->images->standard_resolution->url}'><img src='{$img->images->thumbnail->url}'/></a>";
        }
    } else {
        foreach($data as $img) {
            echo "<a href='{$img->images->standard_resolution->url}'><img src='{$img->images->thumbnail->url}' data-caption='{$img->caption->text}'/></a>";
        }
    }
    ?>
</div>