<div id="dminstagram" class="dminstagram-shortcode dminstagram-default">
    <ul>
        <?php
        foreach($data as $img) {
            echo "<li><a href='{$img->images->standard_resolution->url}'>"
            . "<img alt='{$img->caption->text}' class='dminstagram-default-image' src='{$img->images->thumbnail->url}'></a></li>";
        }
        ?>
    </ul>
</div>