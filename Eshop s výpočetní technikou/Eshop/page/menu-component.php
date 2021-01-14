<!-- využívá soubor CSS index.css -->
<nav id="menu" class="preload">
<?php
    // Reakce na GET
    if(isset($_GET['cat'])) {

    }

    $data = Category::getCategories();
    foreach ($data as $item) {
        echo '<a href="./?page=catalog&c=' . $item['category_id'] . '">' . $item['category_name'] . '</a>';
    }
?>
</nav>

<script>
    $(window).load(function() {
        $("menu").removeClass("preload");
        $("menu a").removeClass("preload");
    });
</script>