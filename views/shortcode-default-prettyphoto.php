<div id="dminstagram" class="dminstagram-shortcode dminstagram-default-prettyphoto">
    <ul>
        <?php
        // For random prettyphoto gallery
        $rand = rand(1,999);
        foreach($data as $img) {
            echo "<li><a href='{$img->images->standard_resolution->url}' rel='prettyPhoto[dm_{$rand}]'>"
                . "<img alt='{$img->caption->text}' class='dminstagram-default-image' src='{$img->images->thumbnail->url}'></a></li>";
        }
        ?>
    </ul>
</div>