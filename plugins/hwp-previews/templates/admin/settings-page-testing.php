<?php

$title = (string) ( $args['title'] ?? '' );
$description = (string) ( $args['description'] ?? '' );
$content = (string) ( $args['content'] ?? '' );

?>

<div class="wrap">
	<h1><?php echo esc_html($title); ?></h1>
	<h2><?php echo esc_html($description); ?></h2>
	<?php echo $content; ?>
</div>
