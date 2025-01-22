
<div class="sidebar" style="position: fixed; top: 52px; left: -8px; width: 230px; background: white; height: 100vh; overflow: hidden;">
    
    <div class="sidebar-icons mt-3">
        
        <?php foreach ($icons as $index => $icon) : ?>
            <div class="sidebar-icon align-center d-flex gap-2 align-items-center<?= $icon['selected'] ? 'selected active-side-bar' : '' ?>" data-index="<?= $index ?>">
                <div
                    class="d-flex align-center"
                    data-bs-toggle="tooltip"
                    title="<?= $icon['title'] ?>"
                    data-bs-offset="0, 20"
                    data-bs-placement="right">
                    <a href="<?= $icon['url'] ?>"
                        target="<?= $icon['external'] ? '_blank' : '' ?>"
                        class="btn p-3"
                        <?= $icon['selected'] ? 'selected' : '' ?>>
                        <i class="<?php echo CHtml::encode($icon['ico']); ?>"></i>
                    </a>
                </div>

                <div class="title p-0 m-0 mt-2" onclick="activateTab(<?= $index ?>)"> <?= $icon['title'] ?> </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    // Function to handle active tab change and navigate
    function activateTab(index) {
        // Get all sidebar items
        const items = document.querySelectorAll('.sidebar-icon');

        // Remove 'active' class from all items
        items.forEach((item, i) => {
            item.classList.remove('active');
        });

        // Add 'active' class to the clicked item
        const clickedItem = items[index];
        clickedItem.classList.add('active');

        // Optionally, you can add other logic to highlight the tab visually
        // Example: Change the color of the title on click
        const titles = document.querySelectorAll('.sidebar-icon .title');
        titles.forEach((title, i) => {
            title.style.color = (i === index) ? 'blue' : ''; // Adjust the color to your liking
        });

        // Allow the link to navigate as usual
        const link = clickedItem.querySelector('a');
        if (link) {
            // If it's not an external link, navigate the page
            window.location.href = link.href;
        }
    }
</script>

<style>
    /* CSS for the active tab */
    .sidebar-icon.active {
        background-color:#F2F4F8;
        color: #6EDEEF;
        font-weight: bold;

    }

    .sidebar-icon .title {
        cursor: pointer;
    }

    .active-side-bar {
        background-color: #F2F4F8;
        color: #6EDEEF;
        font-weight: bold;
    }
</style>
